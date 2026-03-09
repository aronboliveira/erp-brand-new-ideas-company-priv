<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\GoalType as GoalTypeEnum;
use App\Models\GoalType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GoalTypesSeeder extends Seeder
{
	private const SEED = 20251211;

	public function run(): void
	{
		fake()->seed(self::SEED);

		if (!Schema::hasTable(DC::TABLE_GOAL_TYPES)) {
			$this->command?->warn('GoalTypesSeeder: tabela ' . DC::TABLE_GOAL_TYPES . ' ausente. Seeder abortado.');
			return;
		}

		$cases = GoalTypeEnum::cases();
		if (empty($cases)) {
			$this->command?->warn('GoalTypesSeeder: enum GoalType sem casos. Nada a semear.');
			return;
		}

		// Quantas "rodadas" completas de foreach(cases) vamos executar
		$rounds = 1;
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$rounds = max(1, (int) $this->command->option('count'));
		}

		// Paletas de cor e ícones
		$baseColors = [
			'#006666',
			'#0d6efd',
			'#198754',
			'#ffc107',
			'#dc3545',
			'#6f42c1',
			'#20c997',
			'#fd7e14',
		];

		$baseIcons = [
			'fa-bullseye',
			'fa-chart-line',
			'fa-coins',
			'fa-users-cog',
			'fa-tasks',
			'fa-rocket',
			'fa-balance-scale',
			'fa-network-wired',
			'fa-shield-alt',
			'fa-clipboard-check',
			'fa-project-diagram',
			'fa-briefcase',
		];

		// Labels "humanas" por categoria
		$labelsEn = GoalTypeEnum::labels(); // default en

		$faker   = fake();
		$created = 0;
		$HARD_CAP = 2; // was unbounded
		$SECONDS_LIMIT = 32;
		$clock = microtime(true);

		for ($round = 0; $round < $rounds; $round++) {
			if ($created >= $HARD_CAP || (microtime(true) - $clock) > $SECONDS_LIMIT) break;
			foreach ($cases as $case) {
				if ($created >= $HARD_CAP || (microtime(true) - $clock) > $SECONDS_LIMIT) break;
				$categoryValue = $case->value;
				$baseLabel     = $labelsEn[$categoryValue] ?? Str::title(str_replace('_', ' ', $categoryValue));

				// Cada categoria terá entre 2 e 16 registros nesta rodada
				$perType = $faker->numberBetween(2, 16);

				for ($i = 0; $i < $perType; $i++) {
					if ($created >= $HARD_CAP || (microtime(true) - $clock) > $SECONDS_LIMIT) break;
					// Garantir unicidade contextual de "name" por categoria com do/while + exists()
					$attempt = 0;
					$name    = null;

					do {
						$attempt++;

						if ($attempt === 1 && $round === 0 && $i === 0) {
							// Primeiro registro desta categoria usa apenas o label base
							$suffix = '';
						} else {
							$suffix = ' #' . ($round + 1) . '-' . ($i + 1) . '-' . $attempt;
						}

						$nameCandidate = $baseLabel . $suffix;

						$exists = DB::table(DC::TABLE_GOAL_TYPES)
							->where('category', $categoryValue)
							->where('name', $nameCandidate)
							->exists();

						if (!$exists) {
							$name = $nameCandidate;
							break;
						}
					} while ($attempt < 256); // proteção de sanidade

					if ($name === null) {
						// Se não conseguir gerar um name único razoavelmente, pula este registro
						continue;
					}

					// Descrição opcional
					$description = $faker->boolean(80)
						? $faker->realTextBetween(80, 180)
						: null;

					// Cor e ícone opcionais (o Model vai validar cor e normalizar)
					$color = $faker->boolean(70)
						? Arr::random($baseColors)
						: '#006666';

					$icon = $faker->boolean(75)
						? Arr::random($baseIcons)
						: null;

					// Regras simulando configuração real de meta
					$minValue    = $faker->numberBetween(1, 100);
					$maxValue    = $faker->numberBetween($minValue + 10, $minValue + 1000);
					$direction   = $faker->randomElement(['above', 'below', 'equal']);
					$aggregation = $faker->randomElement(['sum', 'avg', 'count', 'max', 'min']);
					$period      = $faker->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);

					$rules = [
						'field'         => $faker->randomElement([
							'amount',
							'count',
							'duration',
							'ratio',
							'percentage',
						]),
						'min_value'     => $minValue,
						'max_value'     => $maxValue,
						'aggregation'   => $aggregation,
						'period'        => $period,
						'direction'     => $direction,
						'goal_weight'   => $faker->numberBetween(1, 100),
						'thresholds'    => [
							'warning'  => $faker->numberBetween($minValue, (int) floor(($minValue + $maxValue) / 2)),
							'critical' => $faker->numberBetween((int) floor(($minValue + $maxValue) / 2), $maxValue),
						],
						'allowed_units' => $faker->randomElement([
							['BRL', 'USD'],
							['%', 'index'],
							['hours', 'days'],
						]),
						'track_history' => $faker->boolean(90),
						'lock_edit'     => $faker->boolean(10),
					];

					// Metadata simples, sem array_filter para nulls; o Model faz a limpeza/normalização
					$metadata = [
						'generated_by'    => 'GoalTypesSeeder',
						'seed'            => self::SEED,
						'round'           => $round + 1,
						'category_value'  => $categoryValue,
						'category_label'  => $baseLabel,
						'is_kpi'          => $faker->boolean(80),
						'is_strategic'    => $faker->boolean(40),
						'created_at_iso'  => Carbon::now()->toIso8601String(),
						'importance_rank' => $faker->numberBetween(1, 10),
					];

					// Tags (o Model já filtra/normaliza e garante unicidade)
					$tagsPool = [
						'finance',
						'sales',
						'marketing',
						'project',
						'hr',
						'it',
						'operations',
						'strategic',
						'kpi',
						'compliance',
						'risk',
						'performance',
						'growth',
						'efficiency',
					];

					$tags     = [];
					$tagCount = $faker->numberBetween(1, 5);
					for ($t = 0; $t < $tagCount; $t++) {
						$tags[] = Arr::random($tagsPool);
					}
					// (new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("Generating goal type {$name} in category {$categoryValue}, icon {$icon}, color {$color}");
					// Criação via Model (respeita booted + casts + ensureJsonAttributesAreEncoded)
					GoalType::query()->create([
						'category'    => $categoryValue,
						'name'        => $name,
						'description' => $description,
						'icon'        => $icon,
						'color'       => $color,
						'rules'       => $rules,
						'metadata'    => $metadata,
						'tags'        => $tags,
					]);

					$created++;
				}
			}
		}

		$this->command?->info(sprintf(
			'GoalTypesSeeder: %d registros criados em %s (%d rodada(s) x %d categorias, 2–16 registros por categoria/rodada).',
			$created,
			DC::TABLE_GOAL_TYPES,
			$rounds,
			count($cases)
		));
	}
}
