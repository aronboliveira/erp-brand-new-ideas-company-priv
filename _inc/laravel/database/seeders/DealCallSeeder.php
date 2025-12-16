<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\CallType;
use App\Models\DealCall;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DealCallSeeder extends Seeder
{
	// Probabilidade (0–1) de um campo opcional receber null
	private const OPTIONALITY = 0.35;

	// Calls adicionais por deal (além da obrigatória)
	private const MIN_CALLS_PER_DEAL = 1;  // mínimo garantido
	private const MAX_CALLS_PER_DEAL = 8;  // máximo possível

	/**
	 * CLI:
	 *  --min-calls=N  Mínimo de calls por deal (padrão: 1)
	 *  --max-calls=N  Máximo de calls por deal (padrão: 8)
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

		// ---------- Parâmetros CLI ----------
		$minCalls = self::MIN_CALLS_PER_DEAL;
		$maxCalls = self::MAX_CALLS_PER_DEAL;

		if ($this->command instanceof Command) {
			if ($this->command->hasOption('min-calls')) {
				$raw = $this->command->option('min-calls');
				if (is_numeric($raw) && (int) $raw > 0) {
					$minCalls = (int) $raw;
				}
			}
			if ($this->command->hasOption('max-calls')) {
				$raw = $this->command->option('max-calls');
				if (is_numeric($raw) && (int) $raw >= $minCalls) {
					$maxCalls = (int) $raw;
				}
			}
		}

		// ---------- Utilitários ----------
		$randBool = function (int $pct) use ($faker): bool {
			return $faker->boolean(max(0, min(100, $pct)));
		};

		$maybe = function (?callable $producer = null) use ($randBool) {
			if ($randBool((int) round(self::OPTIONALITY * 100))) {
				return null;
			}
			return $producer ? $producer() : null;
		};

		$endpoint = function () use ($faker): string {
			return $faker->boolean(65) ? $faker->safeEmail() : $faker->e164PhoneNumber();
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

		// ---------- Criar chamadas para cada deal ----------
		DB::transaction(function () use (
			$faker,
			$minCalls,
			$maxCalls,
			$maybe,
			$endpoint,
			$durationStr,
			$callResults
		): void {
			$dealIds = DB::table(DC::TABLE_DEALS)->pluck('id')->all();
			$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();

			if (empty($dealIds) || empty($userIds)) {
				return;
			}

			$callTypes = CallType::values();
			$totalInserted = 0;

			// Para cada deal, criar entre minCalls e maxCalls chamadas
			foreach ($dealIds as $dealId) {
				$numCalls = $faker->numberBetween($minCalls, $maxCalls);

				for ($i = 0; $i < $numCalls; $i++) {
					try {
						$startedAt = Carbon::now()
							->subDays($faker->numberBetween(0, 180))
							->subMinutes($faker->numberBetween(0, 1440));

						$seconds   = $faker->numberBetween(30, 2 * 60 * 60);
						$durHHMMSS = $durationStr($seconds);

						$userId = $userIds[array_rand($userIds)];
						$fromUserId = $maybe(fn() => $userIds[array_rand($userIds)]);
						$toUserId   = $maybe(fn() => $userIds[array_rand($userIds)]);

						$fromAddr = $endpoint();
						$toAddr   = $endpoint();
						$callType = $callTypes[array_rand($callTypes)];

						$callDatetime = $maybe(fn() => $startedAt->toDateTimeString());
						$callDuration = $maybe(fn() => $durHHMMSS);
						$duration     = $durHHMMSS;

						$callResult  = $maybe(fn() => $faker->randomElement($callResults));
						$subject     = 'Call: ' . $faker->sentence(5);
						$description = $maybe(fn() => $faker->paragraphs($faker->numberBetween(1, 3), true));
						$notes       = $maybe(fn() => $faker->sentences($faker->numberBetween(1, 2), true));

						$creatorId = $maybe(fn() => $userIds[array_rand($userIds)]);
						$updaterId = $maybe(fn() => $userIds[array_rand($userIds)]);
						$updatedAt = $startedAt->addMinutes($faker->numberBetween(1, 240));
						(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando registro de Chamada sobre Acordo de Negócios {$dealId} de {$fromAddr} para {$toAddr} sobre o assunto '{$subject}'");
						DealCall::query()->create([
							AC::COL_DL            => $dealId,
							UC::COL_USER_ID       => $userId,
							'from'                => $fromAddr,
							AC::COL_TO_ID         => $toUserId,
							'to'                  => $toAddr,
							AC::COL_FRM_ID        => $fromUserId,
							'subject'             => mb_substr($subject, 0, 255),
							'description'         => $description,
							'notes'               => $notes,
							AC::COL_CL_TP         => $callType,
							AC::COL_CL_DT         => $callDatetime,
							AC::COL_CL_DUR        => $callDuration,
							AC::COL_CL_RS         => $callResult,
							'duration'            => $duration,
							DC::COL_TABLE_CREATOR => $creatorId,
							DC::COL_TABLE_UPDATER => $updaterId,
							'created_at'          => $startedAt->toDateTimeString(),
							'updated_at'          => $updatedAt->toDateTimeString(),
						]);

						$totalInserted++;
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed for deal ' . $dealId . ': ' . $e->getMessage());
						continue;
					}
				}
			}

			// Log final
			if (method_exists($this, 'command') && $this->command) {
				$avgPerDeal = $totalInserted / max(1, count($dealIds));
				$this->command->info(sprintf(
					'DealCallSeeder: %d calls inserted for %d deals (avg: %.1f per deal)',
					$totalInserted,
					count($dealIds),
					$avgPerDeal
				));
			}
		});
	}
}
