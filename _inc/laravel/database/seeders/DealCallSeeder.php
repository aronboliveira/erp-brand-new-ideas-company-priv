<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\CallType;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DealCallSeeder extends Seeder
{
	// Probabilidade (0–1) de um campo opcional receber null
	private const OPTIONALITY = 0.35;

	/**
	 * CLI:
	 *  --count=N  Quantidade de registros a gerar (se ausente, usa 64 * nº de deals)
	 */
	public function run(): void
	{
		$faker = fake();

		// ---------- Pré-checagens defensivas ----------
		foreach ([DC::TABLE_DL_CALLS, DC::TABLE_DEALS, DC::TABLE_USERS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("DealCallSeeder: tabela ausente: {$tbl}; abortando.");
				return;
			}
		}

		$dealsCount = (int) DB::table(DC::TABLE_DEALS)->count();
		$usersCount = (int) DB::table(DC::TABLE_USERS)->count();
		if ($dealsCount === 0 || $usersCount === 0) {
			$this->command?->warn('DealCallSeeder: não há deals ou users suficientes; nada a semear.');
			return;
		}

		// ---------- Regra de quantidade do projeto ----------
		$defaultCount = max(64, 64 * $dealsCount);
		$count        = $defaultCount;

		if ($this->command instanceof Command && $this->command->hasOption('count')) {
			try {
				$raw = $this->command->option('count');
				if (is_numeric($raw) && (int) $raw > 0) {
					$count = (int) $raw;
				} else {
					$this->command?->warn(sprintf(
						'DealCallSeeder: valor inválido para --count (%s); usando %d.',
						(string) $raw,
						$defaultCount
					));
				}
			} catch (\Throwable) {
				$this->command?->warn('DealCallSeeder: falha ao ler --count; usando valor padrão.');
				$count = $defaultCount;
			}
		}

		// ---------- Utilitários ----------
		$randBool = function (int $pct) use ($faker): bool {
			return $faker->boolean(max(0, min(100, $pct)));
		};

		$maybe = function (?callable $producer = null) use ($randBool) {
			// true => retorna null (tolerância); false => produz valor
			if ($randBool((int) round(self::OPTIONALITY * 100))) {
				return null;
			}
			return $producer ? $producer() : null;
		};

		$endpoint = function () use ($faker): string {
			// 65% e-mail / 35% telefone E.164
			return $faker->boolean(65) ? $faker->unique()->safeEmail() : $faker->e164PhoneNumber();
		};

		$durationStr = function (int $seconds): string {
			if ($seconds < 0) $seconds = 0;
			$h = intdiv($seconds, 3600);
			$m = intdiv($seconds % 3600, 60);
			$s = $seconds % 60;
			return sprintf('%02d:%02d:%02d', $h, $m, $s);
		};

		$callResults = [
			'completed',
			'no_answer',
			'busy',
			'voicemail',
			'rescheduled',
			'dropped',
			'failed',
			'callback_requested'
		];

		// ---------- Inserção em transação ----------
		DB::transaction(function () use (
			$faker,
			$count,
			$maybe,
			$endpoint,
			$durationStr,
			$callResults
		): void {
			$dealIds = DB::table(DC::TABLE_DEALS)->pluck('id')->all();
			$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();

			// Guardas finais
			if (empty($dealIds) || empty($userIds)) {
				return;
			}

			$callTypes = CallType::values(); // valores válidos do enum

			for ($i = 0; $i < $count; $i++) {
				// Base temporal: até 180 dias atrás
				$startedAt = Carbon::now()
					->subDays($faker->numberBetween(0, 180))
					->subMinutes($faker->numberBetween(0, 1440));

				// Duração esperada: 30s–2h
				$seconds   = $faker->numberBetween(30, 2 * 60 * 60);
				$durHHMMSS = $durationStr($seconds);

				// Campos obrigatórios
				$dealId = $dealIds[array_rand($dealIds)];
				// Na migration, user_id acabou não nulo (nullableUser: true na trait); portanto, sempre preencher:
				$userId = $userIds[array_rand($userIds)];

				// from_id e to_id são opcionais (FK com nullOnDelete)
				$fromUserId = $maybe(fn() => $userIds[array_rand($userIds)]);
				$toUserId   = $maybe(fn() => $userIds[array_rand($userIds)]);

				// Endpoints textuais normalizados
				$fromAddr = $endpoint();
				$toAddr   = $endpoint();

				// Tipo de chamada
				$callType = $callTypes[array_rand($callTypes)];

				// call_datetime (opcional), call_duration (TIME, opcional) e duration (string obrigatória)
				$callDatetime = $maybe(fn() => $startedAt->toDateTimeString());
				$callDuration = $maybe(fn() => $durHHMMSS); // TIME aceita HH:MM:SS
				$duration     = $durHHMMSS;                 // sempre preenchido

				// Resultado e textos opcionais
				$callResult  = $maybe(fn() => $faker->randomElement($callResults));
				$subject     = 'Call: ' . $faker->sentence(5);
				$description = $maybe(fn() => $faker->paragraphs($faker->numberBetween(1, 3), true));
				$notes       = $maybe(fn() => $faker->sentences($faker->numberBetween(1, 2), true));

				// Auditoria (nulos tolerados nas FKs da trait)
				$creatorId = $maybe(fn() => $userIds[array_rand($userIds)]);
				$updaterId = $maybe(fn() => $userIds[array_rand($userIds)]);
				$updatedAt = $startedAt->addMinutes($faker->numberBetween(1, 240));

				DB::table(DC::TABLE_DL_CALLS)->insert([
					'id'                   => (string) Str::uuid(),

					// DealConnected
					AC::COL_DL            => $dealId,

					// IsBusinessContact (básico)
					UC::COL_USER_ID       => $userId,
					'from'                => $fromAddr,
					AC::COL_TO_ID         => $toUserId,
					'to'                  => $toAddr,
					AC::COL_FRM_ID        => $fromUserId,
					'subject'             => mb_substr($subject, 0, 255),
					'description'         => $description,
					'notes'               => $notes,

					// BusinessCall
					AC::COL_CL_TP         => $callType,
					AC::COL_CL_DT         => $callDatetime,
					AC::COL_CL_DUR        => $callDuration,   // TIME (nullable)
					AC::COL_CL_RS         => $callResult,     // TEXT (nullable)
					'duration'            => $duration,       // string(20) obrigatória

					// Auditoria
					DC::COL_TABLE_CREATOR => $creatorId,
					DC::COL_TABLE_UPDATER => $updaterId,
					'created_at'          => $startedAt->toDateTimeString(),
					'updated_at'          => $updatedAt->toDateTimeString(),
				]);
			}
		});
	}
}
