<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{ActivityType, AppModuleType};
use App\Models\Utility;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Faker\Factory as FakerFactory;

class LogActivitySeeder extends Seeder
{
	/**
	 * Looping logic (as requested):
	 * - foreach (AppModuleType case) 1..(0.5 * count(cases))
	 * - foreach (ActivityType case) 1..32
	 */
	public function run(): void
	{
		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		try {
			$faker = FakerFactory::create();

			$table = DC::TABLE_LOG_ACTS;

			$moduleCases = AppModuleType::cases();
			$typeCases = ActivityType::cases();

			$moduleLoopMax = (int) floor(count($moduleCases) * 0.5);
			if ($moduleLoopMax < 1) $moduleLoopMax = 1;

			// $hardCap = (int) env('SEED_LOG_ACTIVITIES_MAX', 160000);
			$hardCap = (int) env('SEED_LOG_ACTIVITIES_MAX', 2);
			if ($hardCap < 1) $hardCap = 1;

			$chunkSize = (int) env('SEED_LOG_ACTIVITIES_CHUNK', 1000);
			if ($chunkSize < 100) $chunkSize = 100;

			$rows = [];
			$total = 0;
			foreach ($moduleCases as $moduleCase) {
				$outerIterations = random_int(1, $moduleLoopMax);

				for ($i = 0; $i < $outerIterations; $i++) {
					foreach ($typeCases as $typeCase) {
						$innerIterations = random_int(1, 32);

						for ($j = 0; $j < $innerIterations; $j++) {
							if ($total >= $hardCap) break 3;

							$now = CarbonImmutable::now();

							$startDate = $this->safeDateString(
								$faker->boolean(85)
									? CarbonImmutable::instance($faker->dateTimeBetween('-120 days', 'now'))
									: $now
							);

							$time = $this->safeTimeString(
								$faker->boolean(85)
									? CarbonImmutable::instance($faker->dateTimeBetween($now->startOfDay(), $now->endOfDay()))
									: $now
							);

							$moduleId = null;
							if ($faker->boolean(70))
								$moduleId = Utility::generateUuid();

							$metadata = null;
							if ($faker->boolean(60)) {
								$metaPayload = [
									'ip' => $faker->boolean(40) ? $faker->ipv4() : null,
									'ua' => $faker->boolean(40) ? $faker->userAgent() : null,
									'ref' => $faker->boolean(30) ? $faker->url() : null,
									'context' => [
										'from' => $faker->boolean(40) ? $faker->word() : null,
										'to' => $faker->boolean(40) ? $faker->word() : null,
									],
								];
								$metadata = $this->safeJson($metaPayload, $table . '.metadata');
							}

							$rows[] = [
								'id' => Utility::generateUuid(),
								AC::COL_MD => $moduleCase->value,
								AC::COL_MI => $moduleId,
								'type' => $typeCase->value,
								PJC::COL_S_DT => $startDate,
								AC::COL_TSK_TIME => $time,
								'note' => $faker->boolean(70) ? $faker->sentence(12) : null,
								'metadata' => $metadata,

								DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
								DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
								DC::COL_C_AT => $now,
								DC::COL_U_AT => $now,
							];

							// $output->writeln("Prepared log activity entry for module {$moduleCase->value}, type {$typeCase->value}, date {$startDate}, time {$time}");

							$total++;

							if (count($rows) >= $chunkSize) {
								$this->flushChunk($table, $rows);
								$rows = [];
							}
						}
					}
				}
			}

			if ($rows !== [])
				$this->flushChunk($table, $rows);

			Log::info(self::class . ' seeded log_activities', [
				'table' => $table,
				'total' => $total,
				'hard_cap' => $hardCap,
				'chunk' => $chunkSize,
			]);
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed seeding log_activities', [
				'table' => $table,
				'inserted_so_far' => $total,
				'error' => $e->getMessage(),
			]);
			throw $e;
		}
	}

	private function flushChunk(string $table, array $rows): void
	{
		try {
			DB::table($table)->insert($rows);
		} catch (\Throwable $e) {
			Log::error(self::class . ' chunk insert failed', [
				'table' => $table,
				'rows' => count($rows),
				'error' => $e->getMessage(),
			]);
			throw $e;
		}
	}

	private function safeJson(mixed $value, string $context): ?string
	{
		if ($value === null) return null;

		try {
			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch (\Throwable $e) {
			Log::warning(self::class . ' json_encode failed; storing null', [
				'context' => $context,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	private function safeDateString(mixed $value): string
	{
		try {
			if ($value instanceof \DateTimeInterface)
				return CarbonImmutable::instance(\DateTime::createFromInterface($value))->format('Y-m-d');

			if (is_string($value) && trim($value) !== '')
				return CarbonImmutable::parse($value)->format('Y-m-d');
		} catch (\Throwable $e) {
			Log::warning(self::class . ' invalid date; falling back to today', [
				'value' => is_scalar($value) ? (string) $value : gettype($value),
				'error' => $e->getMessage(),
			]);
		}

		return CarbonImmutable::now()->format('Y-m-d');
	}

	private function safeTimeString(mixed $value): string
	{
		try {
			if ($value instanceof \DateTimeInterface)
				return CarbonImmutable::instance(\DateTime::createFromInterface($value))->format('H:i:s');

			if (is_string($value)) {
				$s = trim($value);
				if ($s === '') return '00:00:00';
				if (preg_match('/^\d{2}:\d{2}$/', $s)) return $s . ':00';
				if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $s)) return $s;
				return CarbonImmutable::parse($s)->format('H:i:s');
			}
		} catch (\Throwable $e) {
			Log::warning(self::class . ' invalid time; falling back to 00:00:00', [
				'value' => is_scalar($value) ? (string) $value : gettype($value),
				'error' => $e->getMessage(),
			]);
		}

		return '00:00:00';
	}
}
