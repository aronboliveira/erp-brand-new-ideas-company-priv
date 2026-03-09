<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\CallType;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeadCallSeeder extends Seeder
{
	private const CHUNK_SIZE   = 1000;
	private const PER_LEAD_MIN = 1;
	private const PER_LEAD_MAX = 3;

	private const HARD_CAP = 2;

	public function run(): void
	{
		foreach ([DC::TABLE_LD_CALLS, DC::TABLE_LEADS, DC::TABLE_USERS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Tabela ausente: {$tbl}. Seeder abortado.");
				return;
			}
		}

		$leads = DB::table(DC::TABLE_LEADS)->select('id')->get();
		if ($leads->isEmpty()) {
			$this->command?->warn('Nenhum lead encontrado. Seeder abortado.');
			return;
		}

		// Precisamos de ao menos 1 usuário por FK obrigatória UC::COL_USER_ID
		$userSelect = ['id'];
		$hasEmail   = Schema::hasColumn(DC::TABLE_USERS, 'email');
		if ($hasEmail) $userSelect[] = 'email';
		$userSelect = array_values(array_unique($userSelect));

		$users = DB::table(DC::TABLE_USERS)->select($userSelect)->get();
		if ($users->isEmpty()) {
			$this->command?->warn('Nenhum usuário encontrado. Seeder abortado (user_id é obrigatório).');
			return;
		}

		$userRows = $users->map(function ($u) use ($hasEmail) {
			$email = $hasEmail ? (is_string($u->email ?? null) ? trim((string) $u->email) : null) : null;
			$phone = $u->phone ?? null;
			return ['id' => $u->id, 'email' => $email, 'phone' => $phone];
		})->all();

		// Quantidade total: --count (se fornecido) senão 16 * nº de leads
		$target = 8 * max(1, $leads->count());
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) $target = $opt;
		}
		$target = min(self::HARD_CAP, $target); /* original: 8 × leads */

		$now      = Carbon::now();
		$rows     = [];
		$inserted = 0;

		$subjects = [
			'Follow-up',
			'Retorno de contato',
			'Alinhamento rápido',
			'Agendamento de reunião',
			'Confirmação',
			'Suporte técnico',
			'Demonstrativo',
			'Proposta',
		];
		$results = [
			'completed',
			'no_answer',
			'busy',
			'rescheduled',
			'left_voicemail',
			'meeting_scheduled',
			'transferred',
			'callback_requested',
			'not_interested',
		];

		foreach ($leads as $lead) {
			if ($inserted >= $target) break;

			$callsForLead = fake()->numberBetween(self::PER_LEAD_MIN, self::PER_LEAD_MAX);
			if ($inserted + $callsForLead > $target) {
				$callsForLead = max(0, $target - $inserted);
			}
			if ($callsForLead === 0) continue;

			for ($i = 0; $i < $callsForLead; $i++) {
				try {
					if ($inserted >= $target) break 2;

					$type = Arr::random(CallType::values());
					$when = $now->subDays(fake()->numberBetween(0, 180))
						->subMinutes(fake()->numberBetween(0, 1440));

					$seconds   = fake()->numberBetween(30, 7200);
					$hhmmss    = self::secondsToHms($seconds);

					// Participantes (usuários) e "owner" (user_id)
					$fromUser = Arr::random($userRows);
					$toUser   = Arr::random($userRows);
					if ($toUser['id'] === $fromUser['id'] && count($userRows) > 1) {
						// Garante usuários distintos quando possível
						do {
							$toUser = Arr::random($userRows);
						} while ($toUser['id'] === $fromUser['id']);
					}
					$ownerUserId = $fromUser['id'];

					// Endpoints "from" e "to" coerentes com o tipo
					$preferPhone = self::typePrefersPhone($type);
					$fromEndpoint = self::pickEndpoint($fromUser, $preferPhone);
					$toEndpoint   = self::pickEndpoint($toUser, $preferPhone);

					$subject = Arr::random($subjects) . ' - ' . self::humanize($type);

					$createdAt = $when->subMinutes(fake()->numberBetween(5, 60));
					$updatedAt = $when->addMinutes(fake()->numberBetween(0, 1440));

					$row = [
						'id'                  => (string) Str::uuid(),
						UC::COL_USER_ID       => $ownerUserId,
						'from'                => $fromEndpoint,
						AC::COL_TO_ID         => $toUser['id'] ?? null,
						'to'                  => $toEndpoint,
						AC::COL_FRM_ID        => $fromUser['id'] ?? null,
						PJC::COL_LD_ID        => $lead->id,
						'subject'             => $subject,
						AC::COL_CL_TP         => $type,
						AC::COL_CL_DT         => $when->toDateTimeString(),
						AC::COL_CL_DUR        => $hhmmss,           // TIME no banco
						'duration'            => $hhmmss,           // string coerente
						'description'         => fake()->boolean(70) ? fake()->sentence(12) : null,
						AC::COL_CL_RS         => Arr::random($results),
						'notes'               => fake()->boolean(30) ? fake()->sentence(10) : null,
						'created_at'          => $createdAt->toDateTimeString(),
						'updated_at'          => $updatedAt->toDateTimeString(),
					];

					// Auditoria, se existirem
					if (Schema::hasColumn(DC::TABLE_LD_CALLS, DC::COL_TABLE_CREATOR)) {
						$row[DC::COL_TABLE_CREATOR] = $fromUser['id'] ?? null;
					}
					if (Schema::hasColumn(DC::TABLE_LD_CALLS, DC::COL_TABLE_UPDATER)) {
						$row[DC::COL_TABLE_UPDATER] = $toUser['id'] ?? null;
					}
					// (new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando registro de Chamada sobre Lead {$lead->id} de {$fromEndpoint} para {$toEndpoint} sobre o assunto '{$subject}'");
					$rows[] = $row;
					$inserted++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}

		if (!$rows) {
			$this->command?->info('LeadCallSeeder: nada a inserir.');
			return;
		}

		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
				DB::table(DC::TABLE_LD_CALLS)->insert($chunk);
			}
		});

		$this->command?->info("LeadCallSeeder: {$inserted} registros inseridos em " . DC::TABLE_LD_CALLS . ".");
	}

	private static function secondsToHms(int $seconds): string
	{
		if ($seconds < 0) $seconds = 0;
		$h = intdiv($seconds, 3600);
		$m = intdiv($seconds % 3600, 60);
		$s = $seconds % 60;
		return sprintf('%02d:%02d:%02d', $h, $m, $s);
	}

	private static function humanize(string $type): string
	{
		return ucfirst(str_replace(['_', '-'], ' ', strtolower($type)));
	}

	private static function typePrefersPhone(string $type): bool
	{
		// Tipos que preferem número telefônico
		$phoneFirst = [
			CallType::Phone->value,
			CallType::VoIP->value,
			CallType::SIP->value,
			CallType::AudioConference->value,
		];
		return in_array($type, $phoneFirst, true);
	}

	private static function pickEndpoint(array $user, bool $preferPhone): string
	{
		// Tenta usar dado real do usuário; caso não exista, gera sintético
		$email = is_string($user['email'] ?? null) && $user['email'] !== '' ? strtolower($user['email']) : null;
		$phone = is_string($user['phone'] ?? null) && $user['phone'] !== '' ? (string) $user['phone'] : null;

		if ($preferPhone) {
			return $phone ? self::normalizePhoneE164($phone) : self::fakeBrPhone();
		}
		// Preferência por e-mail em plataformas de reunião/mensageria corporativa
		if ($email) return $email;

		// Fallback
		return $phone ? self::normalizePhoneE164($phone) : self::fakeBrPhone();
	}

	private static function normalizePhoneE164(string $raw): string
	{
		$digits = preg_replace('/\D+/', '', $raw) ?? '';
		if ($digits === '') return self::fakeBrPhone();
		if (!str_starts_with($digits, '55')) $digits = '55' . $digits;
		return '+' . $digits;
	}

	private static function fakeBrPhone(): string
	{
		// Gera número E.164 brasileiro plausível (+55DD9XXXXYYYY)
		$ddd = fake()->numberBetween(11, 99);
		$n1  = fake()->numberBetween(90000, 99999);
		$n2  = fake()->numberBetween(0000, 9999);
		return sprintf('+55%02d%d%04d', $ddd, $n1, $n2);
	}
}
