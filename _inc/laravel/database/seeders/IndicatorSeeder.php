<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Enums\IndicatorTechnicalLevel;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class IndicatorSeeder extends Seeder
{
	public function run(): void
	{
		$this->seedIndicators();
	}

	protected function seedIndicators(): void
	{
		try {
			$output = new \Symfony\Component\Console\Output\ConsoleOutput();
			$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();
			if (empty($userIds)) {
				Log::warning(static::class . ' skipping: no users found for indicators');
				return;
			}

			$branchIds = DB::table(DC::TABLE_BRANCHES)->pluck('id')->all();
			if (empty($branchIds)) {
				Log::warning(static::class . ' skipping: no branches found for indicators');
				return;
			}

			$departmentIds  = DB::table(DC::TABLE_DEPARTMENTS)->pluck('id')->all();
			$designationIds = DB::table(DC::TABLE_DESIGNS)->pluck('id')->all();
			$projectIds     = DB::table(DC::TABLE_PROJECTS)->pluck('id')->all();
			$employeeIds    = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();
			$appraisalIds   = DB::table(DC::TABLE_APR)->pluck('id')->all();

			$levels = IndicatorTechnicalLevel::cases();

			// base “n”: combinações nível × branch (para seguir o loop especificado)
			$pairs = count($levels) * count($branchIds);
			if ($pairs === 0) {
				Log::warning(static::class . ' skipping: no level/branch pairs found');
				return;
			}

			// original: $targetTotal = $this->resolveTargetTotal($pairs);
			$HARD_CAP = 2;
			$targetTotal = min($HARD_CAP, $this->resolveTargetTotal($pairs));

			$created = 0;
			$buffer  = [];

			foreach ($levels as $levelEnum) {
				foreach ($branchIds as $branchId) {
					// para cada par <level, branch>, cria de 1 a 16 indicadores
					$instances = random_int(1, 16);

					for ($i = 0; $i < $instances; $i++) {
						if ($created >= $targetTotal) {
							break 3; // sai de todos os loops
						}

						$creatorId = (string) $userIds[array_rand($userIds)];
						$companyId = (string) $userIds[array_rand($userIds)];

						$employeeId = null;
						if (!empty($employeeIds)) {
							$employeeId = (string) $employeeIds[array_rand($employeeIds)];
						}

						$projectId = null;
						if (!empty($projectIds) && random_int(0, 1) === 1) {
							$projectId = (string) $projectIds[array_rand($projectIds)];
						}

						$departmentId = null;
						if (!empty($departmentIds) && random_int(0, 1) === 1) {
							$departmentId = (string) $departmentIds[array_rand($departmentIds)];
						}

						$designationId = null;
						if (!empty($designationIds) && random_int(0, 1) === 1) {
							$designationId = (string) $designationIds[array_rand($designationIds)];
						}

						$scores = $this->randomScoreSetForLevel($levelEnum);

						$sources = [];
						if (!empty($appraisalIds) && random_int(0, 100) < 60) {
							$take = random_int(1, min(3, count($appraisalIds)));
							$keys = (array) array_rand($appraisalIds, $take);
							foreach ($keys as $k) {
								$sources[] = (string) $appraisalIds[$k];
							}
						}

						$buffer[] = [
							'id'                  => (string) Str::uuid(),
							'company'             => $companyId,
							'branch'              => (string) $branchId,
							'employee'            => $employeeId,

							'rating'              => $scores['label'],
							'attendance'          => $scores['attendance'],
							'administration'      => $scores['administration'],
							PJC::COL_CST_EXP      => $scores['customer_experience'],
							'integrity'           => $scores['integrity'],
							'marketing'           => $scores['marketing'],
							'professionalism'     => $scores['professionalism'],

							'department'          => $departmentId,
							'designation'         => $designationId,
							'project'             => $projectId,

							DC::COL_CRT_USR       => $creatorId,
							'level'               => $levelEnum->value,

							// migration é json, modelo usa NormalizesArrays,
							// aqui já gravamos string JSON bem formada
							'sources'             => $sources === []
								? json_encode([], JSON_UNESCAPED_UNICODE)
								: json_encode(array_values(array_unique($sources)), JSON_UNESCAPED_UNICODE),

							DC::COL_C_AT          => now(),
							DC::COL_U_AT          => now(),
							DC::COL_TABLE_CREATOR => $creatorId,
							DC::COL_TABLE_UPDATER => $creatorId,
						];

						// $output->writeln("Created indicator: Level {$levelEnum->value}, Branch {$branchId}");
						$created++;
						if (count($buffer) >= 500) {
							DB::table(DC::TABLE_IND)->insert($buffer);
							$buffer = [];
						}
					}
				}
			}

			if (!empty($buffer)) {
				$output->writeln("Inserting remaining " . count($buffer) . " indicators...");
				DB::table(DC::TABLE_IND)->insert($buffer);
			}

			Log::info(static::class . ' seeded indicators', [
				'created' => $created,
				'target'  => $targetTotal,
				'pairs'   => $pairs,
			]);
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed seeding indicators', [
				'error' => $e->getMessage(),
			]);
		}
	}

	/**
	 * Aplica a regra:
	 * - se houver --count, usa o valor informado (no mínimo 1, no máximo não controlamos);
	 * - se não houver, garante pelo menos 64 * basePairs.
	 */
	protected function resolveTargetTotal(int $basePairs): int
	{
		$minTotal = max(64 * $basePairs, $basePairs);

		/** @var Command|null $command */
		$command = $this->command instanceof Command ? $this->command : null;

		if ($command && $command->hasOption('count')) {
			$value = (int) $command->option('count');
			if ($value > 0) {
				// aqui permitimos reduzir abaixo de 64 * n se o dev quiser,
				// mas nunca abaixo da quantidade de combinações (basePairs)
				return max($value, $basePairs);
			}
		}

		return $minTotal;
	}

	/**
	 * Gera um conjunto de notas coerente com o nível técnico.
	 * Notas sempre entre 0 e 10, coerentes com a lógica de clamp do modelo.
	 */
	protected function randomScoreSetForLevel(IndicatorTechnicalLevel $level): array
	{
		$base = match ($level) {
			IndicatorTechnicalLevel::None         => 1,
			IndicatorTechnicalLevel::Beginner     => 3,
			IndicatorTechnicalLevel::Intermediate => 5,
			IndicatorTechnicalLevel::Advanced     => 7,
			IndicatorTechnicalLevel::Expert       => 9,
		};

		$label = match ($level) {
			IndicatorTechnicalLevel::None         => 'No technical competency',
			IndicatorTechnicalLevel::Beginner     => 'Basic technical competency',
			IndicatorTechnicalLevel::Intermediate => 'Solid technical competency',
			IndicatorTechnicalLevel::Advanced     => 'Advanced technical competency',
			IndicatorTechnicalLevel::Expert       => 'Expert technical competency',
		};

		$scores = [];

		foreach (
			[
				'attendance',
				'administration',
				'customer_experience',
				'integrity',
				'marketing',
				'professionalism',
			] as $name
		) {
			$variance = random_int(-2, 2);
			$value    = $base + $variance;
			if ($value < 0)  $value = 0;
			if ($value > 10) $value = 10;
			$scores[$name] = $value;
		}

		return [
			'label'               => $label,
			'attendance'          => $scores['attendance'],
			'administration'      => $scores['administration'],
			'customer_experience' => $scores['customer_experience'],
			'integrity'           => $scores['integrity'],
			'marketing'           => $scores['marketing'],
			'professionalism'     => $scores['professionalism'],
		];
	}
}
