<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\GoalType as GoalTypeEnum;
use App\Models\Goal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class GoalsSeeder extends Seeder
{
	private const SEED = 20251211;

	public function run(): void
	{
		fake()->seed(self::SEED);

		if (!Schema::hasTable(DC::TABLE_GL)) {
			$this->command?->warn('GoalsSeeder: tabela ' . DC::TABLE_GL . ' ausente. Seeder abortado.');
			return;
		}

		// Enum base para tipos de meta
		$goalTypeCases = GoalTypeEnum::cases();
		$baseTypeCount = \count($goalTypeCases);

		if ($baseTypeCount === 0) {
			$this->command?->warn('GoalsSeeder: enum GoalType sem casos. Nada a semear.');
			return;
		}

		// Multiplicador opcional via --count (mantendo regra 64 x N)
		$multiplier = 1;
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				$multiplier = $opt;
			}
		}

		// Regra: TOTAL = 64 x N (N = baseTypeCount * multiplier)
		$totalTypes   = $baseTypeCount * $multiplier;
		$targetTotal  = 64 * $totalTypes;

		// Dados auxiliares de FKs (somente leitura => DB::table)
		$employeesIds = Schema::hasTable(DC::TABLE_EMPLOYEES)
			? DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->map(fn($id) => (string) $id)->all()
			: [];

		$usersTableExists = Schema::hasTable(DC::TABLE_USERS);
		$usersTypeHasCol  = $usersTableExists && Schema::hasColumn(DC::TABLE_USERS, 'type');

		$allUserIds = $usersTableExists
			? DB::table(DC::TABLE_USERS)->pluck('id')->map(fn($id) => (string) $id)->all()
			: [];

		$companyUserIds = [];
		$stakeholderUserIds = [];

		if ($usersTableExists) {
			if ($usersTypeHasCol) {
				$companyUserIds = DB::table(DC::TABLE_USERS)
					->where('type', 'company')
					->pluck('id')
					->map(fn($id) => (string) $id)
					->all();

				$stakeholderUserIds = DB::table(DC::TABLE_USERS)
					->whereIn('type', ['company', 'admin', 'super admin'])
					->pluck('id')
					->map(fn($id) => (string) $id)
					->all();
			} else {
				$companyUserIds      = $allUserIds;
				$stakeholderUserIds  = $allUserIds;
			}
		}

		$goalTrackingIds = Schema::hasTable(DC::TABLE_GL_TRK)
			? DB::table(DC::TABLE_GL_TRK)->pluck('id')->map(fn($id) => (string) $id)->all()
			: [];

		// Labels "humanas" para tipos de meta (usa método labels do enum)
		$typeLabels = GoalTypeEnum::labels();

		// Helper para sortear N elementos de um array (sem array_filter)
		$pickMany = static function (array $source, int $min, int $max): array {
			$count = \count($source);
			if ($count === 0 || $max <= 0) {
				return [];
			}

			$min = max(0, $min);
			$max = max($min, $max);
			$take = fake()->numberBetween($min, min($max, $count));

			if ($take <= 0) {
				return [];
			}

			$result = Arr::random($source, $take);
			return \is_array($result) ? array_values($result) : [$result];
		};

		$baseTagsPool = [
			'finance',
			'sales',
			'marketing',
			'project',
			'hr',
			'it',
			'operations',
			'strategic',
			'compliance',
			'risk',
			'growth',
			'efficiency',
			'retention',
			'performance',
		];

		$metricsUnits = ['BRL', '%', 'hours', 'days', 'count', 'index'];
		$metricsDirections = ['above', 'below', 'equal'];

		$output  = new ConsoleOutput();
		$created = 0;
		$round   = 0;

		while ($created < $targetTotal) {
			$round++;

			foreach ($goalTypeCases as $case) {
				if ($created >= $targetTotal) {
					break;
				}

				$typeValue   = $case->value;
				$typeLabel   = $typeLabels[$typeValue] ?? Str::title(str_replace('_', ' ', $typeValue));
				$remaining   = $targetTotal - $created;

				// Por passagem, cada tipo gera entre 1 e 4 metas, respeitando o teto global
				$perType = min(fake()->numberBetween(1, 4), $remaining);

				for ($i = 0; $i < $perType && $created < $targetTotal; $i++) {
					// Geração de intervalo de datas
					$startBase = Carbon::now()
						->subMonths(fake()->numberBetween(0, 6))
						->startOfMonth();

					$endBase = $startBase->copy()
						->addMonths(fake()->numberBetween(1, 6))
						->endOfMonth();

					// Quantia-alvo (podendo ser ajustada pelas rules em Goal::booted)
					$amount = fake()->randomFloat(2, 500.00, 100000.00);

					// Unicidade contextual de "name" (importante para relatórios/queries)
					$attempt = 0;
					$name    = null;

					do {
						$attempt++;
						$suffix = sprintf(
							' (R%d-%d-%d)',
							$round,
							$i + 1,
							$attempt
						);

						$nameCandidate = sprintf(
							'%s Target%s',
							$typeLabel,
							$suffix
						);

						$exists = DB::table(DC::TABLE_GL)
							->where('name', $nameCandidate)
							->exists();

						if (!$exists) {
							$name = $nameCandidate;
							break;
						}
					} while ($attempt < 256);

					if ($name === null) {
						// Não conseguiu garantir nome único de forma razoável; passa para a próxima
						continue;
					}

					// Descrição opcional
					$description = fake()->boolean(75)
						? fake()->realTextBetween(120, 260)
						: null;

					// Métricas (aplicadas depois por normalizeMetricsArray)
					$metricCount = fake()->numberBetween(1, 4);
					$metrics     = [];
					for ($m = 0; $m < $metricCount; $m++) {
						$metrics[] = [
							'key'       => Str::snake($typeValue . '_metric_' . ($m + 1)),
							'label'     => $typeLabel . ' Metric ' . ($m + 1),
							'target'    => fake()->randomFloat(2, 10.0, 1000.0),
							'unit'      => Arr::random($metricsUnits),
							'weight'    => fake()->randomFloat(1, 1.0, 10.0),
							'direction' => Arr::random($metricsDirections),
						];
					}

					// Trackings (pode ser vazio, Goal::booted faz validação adicional)
					$trackings = $pickMany($goalTrackingIds, 0, 5);

					// Leader (employee) opcional
					$leader = null;
					if (!empty($employeesIds) && fake()->boolean(70)) {
						$leader = Arr::random($employeesIds);
					}

					// Involved (employees)
					$involved = $pickMany($employeesIds, 0, 6);
					if ($leader !== null && !\in_array($leader, $involved, true)) {
						$involved[] = $leader;
					}

					// Sponsors (users tipo company, se existirem; senão, qualquer user)
					$sponsorPool = !empty($companyUserIds) ? $companyUserIds : $allUserIds;
					$sponsors    = $pickMany($sponsorPool, 0, 4);

					// Stakeholders (employees + users com tipos relevantes)
					$stakePool = [...$employeesIds, ...$stakeholderUserIds];
					$stakeholders = $pickMany($stakePool, 0, 8);

					// Tags (Goal::booted vai slugificar/normalizar)
					$tagCount = fake()->numberBetween(1, 5);
					$tags     = [];
					for ($t = 0; $t < $tagCount; $t++) {
						$tags[] = Arr::random($baseTagsPool);
					}

					$isDisplay = fake()->boolean(85);

					// Log em console antes da criação
					$output->writeln(sprintf(
						'Criando Goal "%s" do tipo "%s" com período %s -> %s e alvo %.2f',
						$name,
						$typeValue,
						$startBase->toDateString(),
						$endBase->toDateString(),
						$amount
					));

					// Criação via Model (respeita booted + casts + validações)
					Goal::query()->create([
						'name'         => $name,
						'type'         => $typeValue,
						'from'         => $startBase,
						'to'           => $endBase,
						'amount'       => $amount,
						'description'  => $description,
						'is_display'   => $isDisplay,
						'metrics'      => $metrics,
						'trackings'    => $trackings,
						'leader'       => $leader,
						'involved'     => $involved,
						'sponsors'     => $sponsors,
						'stakeholders' => $stakeholders,
						'tags'         => $tags,
					]);

					$created++;
				}
			}
		}

		$this->command?->info(sprintf(
			'GoalsSeeder: %d metas criadas em %s (64 x %d tipos-base, multiplicador %d).',
			$created,
			DC::TABLE_GL,
			$baseTypeCount,
			$multiplier
		));
	}
}
