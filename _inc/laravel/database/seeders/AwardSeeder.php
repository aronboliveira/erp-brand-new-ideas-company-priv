<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\{AwardType, Employee};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class AwardSeeder extends Seeder
{
	use EnsuresSystemUser;

	private const MAX_PER_TYPE = 64;

	/** Chunk size for bulk inserts */
	private const CHUNK = 1000;

	/** Coverage target: at least 25% of employees must receive >= 1 award */
	private const COVERAGE_PCT = 0.25;

	public function run(): void
	{
		$faker = fake('pt_BR');
		$out = new ConsoleOutput();

		DB::transaction(function () use ($faker, $out) {
			$systemUserId = $this->ensureSystemUser();

			$typeIds = AwardType::query()->pluck('id')->all();
			if (empty($typeIds)) {
				$defaults = [
					'Funcionário do Mês',
					'Pontualidade',
					'Melhor Atendimento',
					'Produtividade',
					'Tempo de Casa',
					'Inovação',
					'Trabalho em Equipe',
					'Liderança',
					'Cumprimento de Metas',
					'Qualidade',
					'Atendimento ao Cliente',
					'Segurança no Trabalho',
					'Projeto do Ano',
					'Melhor Estagiário',
					'Melhor Novato',
				];

				$defaults = array_values(array_unique(array_filter(array_map('trim', $defaults), fn($v) => $v !== '')));

				$now = now('America/Sao_Paulo');
				$payload = [];

				foreach ($defaults as $name) {
					$out->writeln("<comment>[AwardSeeder] creating AwardType: {$name}</comment>");
					$payload[] = [
						'id' => (string) Str::uuid(),
						'name' => $name,
						DC::COL_TABLE_CREATOR => $systemUserId,
						DC::COL_TABLE_UPDATER => null,
						'created_at' => $now,
						'updated_at' => $now,
					];
				}

				DB::table(DC::TABLE_AWD_TPS)->insert($payload);
				$typeIds = AwardType::query()->pluck('id')->all();
			}

			$employeeIds = Employee::query()->pluck('id')->all();
			if (empty($employeeIds)) {
				Log::notice('[AwardSeeder] No employees found. Skipping awards seeding.');
				return;
			}

			$typeCount = count($typeIds);
			$employeeCount = count($employeeIds);

			$maxPossibleAwards = $typeCount * self::MAX_PER_TYPE;
			$targetCoverage = (int) ceil($employeeCount * self::COVERAGE_PCT);
			$targetCoverage = max(0, min($employeeCount, $targetCoverage, $maxPossibleAwards));

			// Random "0..64 per type", but ensure total awards >= targetCoverage (so coverage is always feasible).
			$iterationsByType = [];
			foreach ($typeIds as $typeId) {
				try {
					$iterationsByType[(string) $typeId] = random_int(0, self::MAX_PER_TYPE);
				} catch (\Throwable) {
					$iterationsByType[(string) $typeId] = ((int) (crc32((string) $typeId) % (self::MAX_PER_TYPE + 1)));
				}
			}

			$totalAwards = array_sum($iterationsByType);
			$needed = max(0, $targetCoverage - $totalAwards);

			if ($needed > 0) {
				// Distribute increments across types without exceeding MAX_PER_TYPE.
				$guard = 0;
				while ($needed > 0 && $guard++ < ($typeCount * (self::MAX_PER_TYPE + 1))) {
					foreach ($typeIds as $typeId) {
						if ($needed <= 0) break;
						$k = (string) $typeId;
						$cur = (int) ($iterationsByType[$k] ?? 0);
						if ($cur >= self::MAX_PER_TYPE) continue;
						$iterationsByType[$k] = $cur + 1;
						$needed--;
					}
					// If we can’t increment any further, break.
					if ($guard > ($typeCount * (self::MAX_PER_TYPE + 1))) break;
				}
			}

			$totalAwards = array_sum($iterationsByType);

			// Coverage set: ensure these employees get at least one award.
			$shuffledEmployees = $employeeIds;
			try {
				shuffle($shuffledEmployees);
			} catch (\Throwable) {
				// no-op
			}

			$coverageEmployees = $targetCoverage > 0 ? array_slice($shuffledEmployees, 0, $targetCoverage) : [];
			$coverageIdx = 0;

			$out->writeln(
				"<info>[AwardSeeder] types={$typeCount} employees={$employeeCount} total_awards={$totalAwards} coverage_target={$targetCoverage}</info>"
			);

			$rows = [];
			$now = now('America/Sao_Paulo');

			foreach ($typeIds as $typeId) {
				$typeId = (string) $typeId;
				$iter = (int) ($iterationsByType[$typeId] ?? 0);

				for ($i = 0; $i < $iter; $i++) {
					$empId = null;

					// First, satisfy coverage employees (each at least once).
					if ($coverageIdx < count($coverageEmployees)) {
						$empId = (string) $coverageEmployees[$coverageIdx];
						$coverageIdx++;
					} else {
						$empId = (string) $faker->randomElement($employeeIds);
					}

					$date = $now->copy()->subDays((int) ($i % 721));
					try {
						$date = $now->copy()->subDays(random_int(0, 720));
					} catch (\Throwable) {
						// keep deterministic fallback above
					}

					$rows[] = [
						'id' => (string) Str::uuid(),
						UC::COL_EMP_ID => $empId,
						UC::COL_AWD_TP => $typeId,
						'date' => $date->format('Y-m-d'),
						'gift' => $faker->optional(0.5)->randomElement([
							'Voucher R$ 200',
							'Day Off',
							'Placa de Reconhecimento',
							'Kit Brinde',
							null,
						]),
						'description' => $faker->optional(0.7)->sentence(12),
						DC::COL_TABLE_CREATOR => $systemUserId,
						DC::COL_TABLE_UPDATER => null,
						'created_at' => $now,
						'updated_at' => $now,
					];

					if (count($rows) >= self::CHUNK) {
						DB::table(DC::TABLE_AWD)->insert($rows);
						$rows = [];
					}
				}
			}

			if (!empty($rows)) DB::table(DC::TABLE_AWD)->insert($rows);

			// Final sanity log (best-effort): did we cover at least targetCoverage distinct employees?
			try {
				$distinctAwarded = DB::table(DC::TABLE_AWD)->distinct()->count(UC::COL_EMP_ID);
				$out->writeln("<info>[AwardSeeder] distinct_employees_awarded={$distinctAwarded}</info>");
			} catch (\Throwable $e) {
				Log::debug('[AwardSeeder] failed computing distinct awarded employees', ['error' => $e->getMessage()]);
			}
		}, 3);
	}
}
