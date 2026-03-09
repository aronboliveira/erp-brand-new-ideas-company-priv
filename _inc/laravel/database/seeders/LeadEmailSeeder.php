<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use App\Config\Constants\UsersConstants as UC;
use App\Models\LeadEmail;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeadEmailSeeder extends Seeder
{
	private const OPTIONALITY     = 0.65;   // prob. média de preencher opcionais
	private const PER_LEAD_MIN    = 1;      // e-mails mínimos por lead
	private const PER_LEAD_MAX    = 3;      // e-mails máximos por lead
	private const CHUNK_SIZE      = 1000;

	public function run(): void
	{
		// Sanidade de tabelas base
		foreach ([DC::TABLE_LD_EMAILS, DC::TABLE_LEADS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Tabela ausente: {$tbl}. Seeder abortado.");
				return;
			}
		}

		// Coletas mínimas
		$leads = DB::table(DC::TABLE_LEADS)
			->select('id', 'email', UC::COL_USER_ID)
			->get();

		if ($leads->isEmpty()) {
			$this->command?->warn('Nenhum lead encontrado. Seeder abortado.');
			return;
		}

		$users = Schema::hasTable(DC::TABLE_USERS)
			? DB::table(DC::TABLE_USERS)->select('id', 'email')->get()
			: collect();

		$userIds     = $users->pluck('id')->all();
		$userEmails  = $users->pluck('email')->filter()->map(fn($e) => mb_strtolower((string) $e))->values()->all();

		// Regra global: --count OU (64 * nº_de_leads)
		$perLeadMin = self::PER_LEAD_MIN;
		$perLeadMax = self::PER_LEAD_MAX;

		// $defaultTarget = 64 * max(1, $leads->count()); /* original */
		$defaultTarget = 2; /* HARD_CAP: original was 64 × leads */
		$target = $defaultTarget;
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				$target = min(2, $opt); /* clamp to HARD_CAP */
			}
		}

		$maybe = fn(callable $fn) => fake()->boolean((int) round(self::OPTIONALITY * 100)) ? $fn() : null;
		$json  = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$rows          = [];
		$inserted      = 0;
		$now           = Carbon::now();

		foreach ($leads as $lead) {
			if ($target > 0 && $inserted >= $target) {
				break;
			}

			$emailsForLead = fake()->numberBetween($perLeadMin, $perLeadMax);

			// Ajusta para não ultrapassar target aproximado
			if ($target > 0 && ($inserted + $emailsForLead) > $target) {
				$emailsForLead = max(0, $target - $inserted);
			}
			if ($emailsForLead === 0) {
				continue;
			}

			for ($i = 0; $i < $emailsForLead; $i++) {
				try {
					if ($target > 0 && $inserted >= $target) {
						break 2;
					}

					// Seleção de e-mails (mantendo "to" válido e possível normalização em boot/saving do Model)
					$leadEmail = $lead->email ? mb_strtolower((string) $lead->email) : null;
					$userEmail = $userEmails ? Arr::random($userEmails) : null;

					// from: remetente pode ser alguém do sistema ou caixa genérica
					$from = Arr::random(array_filter([
						$userEmail,
						'sales_' . Str::random(8) . "@" . fake()->domainName(),
						'support_' . Str::random(8) . "@" . fake()->domainName(),
						'noreply_' . Str::random(8) . "@" . fake()->domainName(),
					])) ?: 'noreply_' . Str::uuid()->toString() . '@example.test';
					// to: prioridade: e-mail do lead -> e-mail de usuário -> fallback determinístico
					$to = $leadEmail
						?: ($userEmail ?: ('lead.' . Str::lower(Str::random(8)) . '@example.test'));

					// Datas
					$createdAt = $now->subDays(fake()->numberBetween(0, 120))
						->subMinutes(fake()->numberBetween(0, 1440));
					$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 10080));

					// Assunto e corpo
					$subject = $maybe(fn() => fake()->sentence(6)) ?? 'Follow-up sobre sua demanda';
					$desc    = $maybe(fn() => fake()->paragraphs(fake()->numberBetween(1, 3), true));

					// Contador de mensagens na thread
					$counter = fake()->numberBetween(1, 12);

					// Attachments
					$attCount = fake()->boolean(30) ? fake()->numberBetween(1, 3) : 0;
					$attachments = $attCount > 0 ? array_map(function () {
						$ext   = Arr::random(['pdf', 'png', 'jpg', 'jpeg', 'csv']);
						return [
							'name'    => fake()->lexify('file-????') . '.' . $ext,
							'path'    => 'leads/' . Str::lower(Str::random(16)) . '.' . $ext,
							'mime'    => match ($ext) {
								'pdf'   => 'application/pdf',
								'csv'   => 'text/csv',
								'png'   => 'image/png',
								'jpg', 'jpeg' => 'image/jpeg',
								default => 'application/octet-stream',
							},
							'size_kb' => fake()->numberBetween(12, 4096),
						];
					}, range(1, $attCount)) : null;

					// Regras de filtro de anexo (ex.: apenas imagens OU limite de tamanho)
					$atcRules = $maybe(function () {
						$ruleType = Arr::random(['images_only', 'max_size', 'extensions']);
						return match ($ruleType) {
							'images_only' => ['only_images' => true],
							'max_size'    => ['max_kb' => Arr::random([512, 1024, 2048, 4096])],
							'extensions'  => ['allowed_ext' => Arr::random([['pdf'], ['png', 'jpg'], ['csv', 'pdf']], 1)[0]],
							default       => null,
						};
					});

					// Auditoria (se colunas existirem)
					$creator = $maybe(fn() => $userIds ? Arr::random($userIds) : null);
					$updater = $maybe(fn() => $userIds ? Arr::random($userIds) : null);
					$row = [
						PJC::COL_LD_ID             => $lead->id,
						UC::COL_USER_ID            => $userIds ? Arr::random($userIds) : null,
						'from'                     => $from, // "from" é nullable
						'to'                       => $to,                    // "to" é obrigatório
						'subject'                  => $subject,
						'counter'                  => $maybe(fn() => $counter) ?? 1,
						PJC::COL_IS_FUP            => $maybe(fn() => fake()->boolean(40)) ?? false,
						'description'              => $desc,
						'attachments'              => $json($attachments),
						PJC::COL_ATC_FRULES        => $json($atcRules),
						'created_at'               => $createdAt->toDateTimeString(),
						'updated_at'               => $updatedAt->toDateTimeString(),
					];

					// Adiciona colunas de auditoria, apenas se existirem
					if (Schema::hasColumn(DC::TABLE_LD_EMAILS, DC::COL_TABLE_CREATOR)) {
						$row[DC::COL_TABLE_CREATOR] = $creator;
					}
					if (Schema::hasColumn(DC::TABLE_LD_EMAILS, DC::COL_TABLE_UPDATER)) {
						$row[DC::COL_TABLE_UPDATER] = $updater;
					}
					// (new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando E-mail sobre Lead {$lead->id} de {$from} para {$to} sobre o assunto '{$subject}'");
					// Remove apenas nulls; manter 0/false
					$rows[] = $row;
					$inserted++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}

		if (!$rows) {
			$this->command?->info('LeadEmailSeeder: nada a inserir.');
			return;
		}

		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
				foreach ($chunk as $row) {
					LeadEmail::create($row);
				}
			}
		});

		$this->command?->info("LeadEmailSeeder: {$inserted} e-mails inseridos em " . DC::TABLE_LD_EMAILS . ".");
	}
}
