<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use App\Enums\EvaluationStatus;
use App\Enums\PriorityLevel;
use App\Models\Goal;
use App\Models\GoalTracking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class GoalTrackingsSeeder extends Seeder
{
	private const SEED = 20251211;

	public function run(): void
	{
		fake()->seed(self::SEED);

		if (!Schema::hasTable(DC::TABLE_GL_TRK)) {
			$this->command?->warn('GoalTrackingsSeeder: tabela ' . DC::TABLE_GL_TRK . ' ausente. Seeder abortado.');
			return;
		}

		if (!Schema::hasTable(DC::TABLE_GL)) {
			$this->command?->warn('GoalTrackingsSeeder: tabela ' . DC::TABLE_GL . ' ausente. Seeder abortado.');
			return;
		}

		$goalRows = DB::table(DC::TABLE_GL)
			->select('id', 'name', 'type', 'from', 'to', 'leader', 'sponsors')
			->get();

		$goalCount = $goalRows->count();
		if ($goalCount === 0) {
			$this->command?->warn('GoalTrackingsSeeder: nenhuma Goal encontrada em ' . DC::TABLE_GL . '. Nada a semear.');
			return;
		}

		$branchesIds = Schema::hasTable(DC::TABLE_BRANCHES)
			? DB::table(DC::TABLE_BRANCHES)->pluck('id')->map(fn($id) => (string) $id)->all()
			: [];

		$departmentsIds = Schema::hasTable(DC::TABLE_DEPARTMENTS)
			? DB::table(DC::TABLE_DEPARTMENTS)->pluck('id')->map(fn($id) => (string) $id)->all()
			: [];

		$usersTableExists = Schema::hasTable(DC::TABLE_USERS);
		$usersTypeHasCol  = $usersTableExists && Schema::hasColumn(DC::TABLE_USERS, 'type');

		$allUserIds = $usersTableExists
			? DB::table(DC::TABLE_USERS)->pluck('id')->map(fn($id) => (string) $id)->all()
			: [];

		$companyUserIds = [];
		if ($usersTableExists) {
			if ($usersTypeHasCol) {
				$companyUserIds = DB::table(DC::TABLE_USERS)
					->where('type', 'company')
					->pluck('id')
					->map(fn($id) => (string) $id)
					->all();
			} else {
				$companyUserIds = $allUserIds;
			}
		}

		$goalTypesByCategory = [];
		if (Schema::hasTable(DC::TABLE_GOAL_TYPES)) {
			$typeRows = DB::table(DC::TABLE_GOAL_TYPES)
				->select('id', 'category')
				->get();

			foreach ($typeRows as $row) {
				$category = (string) ($row->category ?? '');
				$id       = (string) ($row->id ?? '');
				if ($category === '' || $id === '') {
					continue;
				}
				if (!\array_key_exists($category, $goalTypesByCategory)) {
					$goalTypesByCategory[$category] = [];
				}
				$goalTypesByCategory[$category][] = $id;
			}
		}

		$statusCases   = EvaluationStatus::cases();
		$priorityCases = PriorityLevel::cases();

		$tagsPool = [
			'planning',
			'execution',
			'review',
			'qa',
			'finance',
			'sales',
			'marketing',
			'hr',
			'it',
			'operations',
			'risk',
			'compliance',
			'growth',
			'efficiency',
			'performance',
		];

		$metricUnits      = ['BRL', '%', 'hours', 'days', 'count', 'index'];
		$metricDirections = ['above', 'below', 'equal'];

		$attachmentMimes = [
			'application/pdf',
			'image/png',
			'image/jpeg',
			'text/plain',
		];

		$pickMany = static function (array $source, int $min, int $max): array {
			$total = \count($source);
			if ($total === 0) {
				return [];
			}

			if ($min < 0) {
				$min = 0;
			}

			if ($max < $min) {
				$max = $min;
			}

			if ($min === 0 && $max === 0) {
				return [];
			}

			$max = min($max, $total);
			$take = fake()->numberBetween($min, $max);

			if ($take <= 0) {
				return [];
			}

			$result = Arr::random($source, $take);
			return \is_array($result) ? array_values($result) : [$result];
		};

		$output  = new ConsoleOutput();
		$created = 0;
		$round   = 0;
		foreach ($goalRows as $goalRow) {
			$perGoal   = fake()->numberBetween(1, 8);

			$goalId   = (string) $goalRow->id;
			$goalName = (string) ($goalRow->name ?? ('Goal ' . $goalId));
			$goalType = (string) ($goalRow->type ?? '');

			$goalFrom = null;
			$goalTo   = null;

			if (!empty($goalRow->from)) {
				try {
					$goalFrom = Carbon::parse((string) $goalRow->from)->startOfDay();
				} catch (\Throwable) {
					$goalFrom = null;
				}
			}

			if (!empty($goalRow->to)) {
				try {
					$goalTo = Carbon::parse((string) $goalRow->to)->endOfDay();
				} catch (\Throwable) {
					$goalTo = null;
				}
			}

			if ($goalFrom === null && $goalTo === null) {
				$goalFrom = Carbon::now()->subMonths(fake()->numberBetween(1, 6))->startOfMonth();
				$goalTo   = $goalFrom->copy()->addMonths(fake()->numberBetween(1, 6))->endOfMonth();
			} elseif ($goalFrom !== null && $goalTo === null) {
				$goalTo = $goalFrom->copy()->addMonths(fake()->numberBetween(1, 6))->endOfMonth();
			} elseif ($goalFrom === null && $goalTo !== null) {
				$goalFrom = $goalTo->copy()->subMonths(fake()->numberBetween(1, 6))->startOfMonth();
			}

			if ($goalFrom && $goalTo && $goalTo->lessThan($goalFrom)) {
				$tmp     = $goalFrom;
				$goalFrom = $goalTo;
				$goalTo   = $tmp;
			}

			$goalTypeId = null;
			if ($goalType !== '' && \array_key_exists($goalType, $goalTypesByCategory)) {
				$goalTypeId = Arr::random($goalTypesByCategory[$goalType]);
			}

			$sponsorsDecoded = [];
			if (!empty($goalRow->sponsors)) {
				try {
					$tmp = json_decode((string) $goalRow->sponsors, true);
					if (\is_array($tmp)) {
						foreach ($tmp as $v) {
							if (\is_string($v) && $v !== '') {
								$sponsorsDecoded[] = $v;
							}
						}
					}
				} catch (\Throwable) {
				}
			}

			$branchId = null;
			if (!empty($branchesIds) && fake()->boolean(60)) {
				$branchId = Arr::random($branchesIds);
			}

			$departmentId = null;
			if (!empty($departmentsIds) && fake()->boolean(60)) {
				$departmentId = Arr::random($departmentsIds);
			}

			for ($i = 0; $i < $perGoal; $i++) {
				if ($goalFrom && $goalTo) {
					$diffDays = $goalFrom->diffInDays($goalTo);
					if ($diffDays <= 0) {
						$startDate = $goalFrom->copy();
						$endDate   = $goalTo->copy();
					} else {
						$offsetStart = fake()->numberBetween(0, (int) floor($diffDays / 2));
						$offsetEnd   = fake()->numberBetween($offsetStart + 1, $diffDays);
						$startDate   = $goalFrom->copy()->addDays($offsetStart);
						$endDate     = $goalFrom->copy()->addDays($offsetEnd);
					}
				} else {
					$startDate = Carbon::now()->subDays(fake()->numberBetween(0, 30))->startOfDay();
					$endDate   = $startDate->copy()->addDays(fake()->numberBetween(1, 60))->endOfDay();
				}

				$statusEnum = Arr::random($statusCases);
				$status     = $statusEnum->value;

				$priorityEnum = Arr::random($priorityCases);
				$priority     = $priorityEnum->value;

				if ($statusEnum === EvaluationStatus::NotStarted) {
					$progress = 0.0;
				} elseif ($statusEnum === EvaluationStatus::Completed) {
					$progress = fake()->randomFloat(2, 80.0, 100.0);
				} elseif ($statusEnum === EvaluationStatus::Cancelled || $statusEnum === EvaluationStatus::Expired) {
					$progress = fake()->randomFloat(2, 0.0, 60.0);
				} else {
					$progress = fake()->randomFloat(2, 10.0, 90.0);
				}

				$attempt  = 0;
				$subject  = null;

				do {
					$attempt++;
					$suffix = sprintf(
						' (G%s-R%d-%d-%d)',
						substr($goalId, 0, 8),
						$round,
						$i + 1,
						$attempt
					);

					$candidate = sprintf(
						'%s Tracking%s',
						$goalName,
						$suffix
					);

					$exists = DB::table(DC::TABLE_GL_TRK)
						->where('subject', $candidate)
						->exists();

					if (!$exists) {
						$subject = $candidate;
						break;
					}
				} while ($attempt < 256);

				if ($subject === null) {
					continue;
				}

				$targetAchievement = fake()->boolean(80)
					? fake()->sentence(8)
					: null;

				$description = fake()->boolean(70)
					? fake()->realTextBetween(80, 220)
					: null;

				$ratingNumeric = fake()->numberBetween(1, 5);
				$rating        = (string) $ratingNumeric;

				$metrics = [];
				$metricCount = fake()->numberBetween(0, 3);
				for ($m = 0; $m < $metricCount; $m++) {
					$metrics[] = [
						'key'       => Str::snake($goalType !== '' ? $goalType : 'generic') . '_metric_' . ($m + 1),
						'label'     => 'Metric ' . ($m + 1) . ' for ' . $goalName,
						'target'    => fake()->randomFloat(2, 5.0, 1000.0),
						'unit'      => Arr::random($metricUnits),
						'weight'    => fake()->randomFloat(1, 1.0, 10.0),
						'direction' => Arr::random($metricDirections),
					];
				}

				$attachments = [];
				$attachmentCount = fake()->numberBetween(0, 3);
				for ($a = 0; $a < $attachmentCount; $a++) {
					$fileExt = fake()->randomElement(['pdf', 'png', 'jpg', 'txt']);
					$mime    = Arr::random($attachmentMimes);

					$attachments[] = [
						'path' => 'goals/' . $goalId . '/trackings/' . Str::uuid() . '.' . $fileExt,
						'name' => 'Attachment ' . ($a + 1) . ' - ' . $goalName,
						'mime' => $mime,
						'size' => fake()->numberBetween(10_000, 600_000),
					];
				}

				$tags = [];
				$tagCount = fake()->numberBetween(1, 6);
				for ($t = 0; $t < $tagCount; $t++) {
					$tags[] = Arr::random($tagsPool);
				}
				$tags[] = 'status_' . $statusEnum->value;
				$tags[] = 'priority_' . $priorityEnum->value;

				$steps = [];
				$stepCount = fake()->numberBetween(2, 6);
				for ($s = 0; $s < $stepCount; $s++) {
					$stepStatus = fake()->randomElement(['planned', 'in_progress', 'done']);
					$steps[] = [
						'label'       => 'Step ' . ($s + 1),
						'description' => fake()->sentence(10),
						'status'      => $stepStatus,
					];
				}

				$metadata = [
					'seed_round'    => $round,
					'seeded_at'     => Carbon::now()->toDateTimeString(),
					'seed_source'   => 'GoalTrackingsSeeder',
					'goal_type_raw' => $goalType,
				];

				$sponsorPool = $sponsorsDecoded !== [] ? $sponsorsDecoded : $companyUserIds;
				$companyId   = null;
				if (!empty($sponsorPool) && fake()->boolean(80)) {
					$companyId = Arr::random($sponsorPool);
				}

				$output->writeln(sprintf(
					'Criando GoalTracking "%s" para Goal "%s" (%s) com status "%s" e progresso %.1f%%',
					$subject,
					$goalName,
					$goalId,
					$status,
					$progress
				));

				GoalTracking::query()->create([
					'company'             => $companyId,
					'branch'              => $branchId,
					'department'          => $departmentId,
					PJC::COL_GL_TP        => $goalTypeId,
					'goal'                => $goalId,
					PJC::COL_S_DT         => $startDate,
					PJC::COL_E_DT         => $endDate,
					'subject'             => $subject,
					'rating'              => $rating,
					PJC::COL_TRG_ACHV     => $targetAchievement,
					'description'         => $description,
					'status'              => $status,
					'progress'            => $progress,
					'priority'            => $priority,
					'metrics'             => $metrics,
					'attachments'         => $attachments,
					'tags'                => $tags,
					'steps'               => $steps,
					'metadata'            => $metadata,
				]);

				$created++;
			}
		}

		$this->command?->info(sprintf(
			'GoalTrackingsSeeder: %d registros criados em %s para %d goals.',
			$created,
			DC::TABLE_GL_TRK,
			$goalCount,
		));
	}
}
