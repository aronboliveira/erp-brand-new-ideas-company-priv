<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, EvaluationStatus, PriorityLevel};
use App\Models\ProjectTask;
use Carbon\CarbonImmutable;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ProjectTaskSeeder extends Seeder
{
	private const HARD_CAP = 128000;

	/** @var ConsoleOutput */
	private ConsoleOutput $out;

	/** @var \Faker\Generator */
	private $faker;

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$this->faker = Faker::create();

		$projectIds = DB::table(DC::TABLE_PROJECTS)->select('id')->pluck('id')->all();
		if ($projectIds === []) {
			$this->out->writeln('<comment>[ProjectTasksSeeder]</comment> No projects found. Skipping.');
			return;
		}

		$users = DB::table(DC::TABLE_USERS)
			->select('id', DC::COL_TABLE_CREATOR, 'name')
			->get()
			->map(fn($r) => [
				'id' => (string) $r->id,
				'name' => is_string($r->name) ? $r->name : null,
			])
			->all();

		$userIds = array_values(array_map(static fn($u) => (string) ($u['id'] ?? ''), $users));
		$userIds = array_values(array_filter($userIds, static fn($v) => is_string($v) && trim($v) !== ''));

		$employees = DB::table(DC::TABLE_EMPLOYEES)
			->select('id', 'name')
			->get()
			->map(fn($r) => [
				'id' => (string) $r->id,
				'name' => is_string($r->name) ? $r->name : null,
			])
			->all();

		$employeeIds = array_values(array_map(static fn($e) => (string) ($e['id'] ?? ''), $employees));
		$employeeIds = array_values(array_filter($employeeIds, static fn($v) => is_string($v) && trim($v) !== ''));

		if ($userIds === []) {
			$this->out->writeln('<comment>[ProjectTasksSeeder]</comment> No users found. Skipping (FK constraints).');
			return;
		}

		$existingCount = (int) DB::table(DC::TABLE_PROJ_TSKS)->count();
		$remainingCap = max(0, self::HARD_CAP - $existingCount);
		if ($remainingCap <= 0) {
			$this->out->writeln('<comment>[ProjectTasksSeeder]</comment> Hard cap reached. Skipping.');
			return;
		}

		$planned = [];
		$rawTotal = 0;

		foreach ($projectIds as $pid) {
			$n = $this->faker->numberBetween(1, 32);
			$planned[(string) $pid] = $n;
			$rawTotal += $n;

			if ($rawTotal >= $remainingCap) {
				break;
			}
		}

		$targetTotal = min($rawTotal, $remainingCap);

		// Enforce multiple-of-64 final count while respecting hard cap
		$mod = $targetTotal % 64;
		if ($mod !== 0) {
			$up = $targetTotal + (64 - $mod);
			if ($up <= $remainingCap) {
				$targetTotal = $up;
			} else {
				$targetTotal = $targetTotal - $mod; // down to previous multiple-of-64
				if ($targetTotal === 0) {
					$targetTotal = min(64, $remainingCap);
				}
			}
		}

		$this->out->writeln(
			"<info>[ProjectTasksSeeder]</info> Projects=" . count($projectIds)
				. " Existing={$existingCount} RemainingCap={$remainingCap}"
				. " PlannedRaw={$rawTotal} Target={$targetTotal}"
		);

		$created = 0;

		// We iterate projects deterministically; if we need to "top up" to reach the 64-multiple,
		// we will keep adding tasks to earlier projects (still within 1..32 guideline per project,
		// but if target > rawTotal we only add extras, not exceeding hard cap).
		$projectQueue = array_keys($planned);
		if ($projectQueue === []) {
			$this->out->writeln('<comment>[ProjectTasksSeeder]</comment> Nothing planned. Skipping.');
			return;
		}

		$queueIdx = 0;

		while ($created < $targetTotal) {
			$pid = $projectQueue[$queueIdx] ?? null;
			if (!is_string($pid) || $pid === '') {
				break;
			}

			$queueIdx++;
			if ($queueIdx >= count($projectQueue)) {
				$queueIdx = 0;
			}

			// Respect the per-project 1..32 guideline when possible; if we must top up,
			// allow extra tasks only up to 32 for the planned projects. If all are at 32,
			// we still stop (cannot exceed target in that case).
			$currentPlanned = (int) ($planned[$pid] ?? 0);
			if ($currentPlanned <= 0) {
				continue;
			}

			// Decrement planned for this project; once it hits 0, it will be skipped.
			$planned[$pid] = $currentPlanned - 1;

			$creatorId = $this->pickUserId($userIds);
			$assignedBy = $this->pickUserId($userIds);
			$responsible = $this->pickUserId($userIds);

			$assignTokens = $this->buildAssignToTokens($userIds, $employeeIds, maxCount: 5);
			$assignTo = implode(',', $assignTokens);

			$involved = $this->buildInvolved($assignTokens, $assignedBy, $assignTo, $responsible);

			$progressInt = $this->faker->numberBetween(0, 100);
			$progress = (string) $progressInt;

			$status = $this->deriveStatusFromProgress($progressInt);

			$priority = Arr::random(PriorityLevel::values());
			$priorityColor = PriorityLevel::colorCodes()[$priority] ?? PriorityLevel::colorCodes()[PriorityLevel::Medium->value];

			$start = $this->faker->boolean(75) ? CarbonImmutable::now()->subDays($this->faker->numberBetween(0, 90))->toDateString() : null;
			$end = $start !== null && $this->faker->boolean(70)
				? CarbonImmutable::parse($start)->addDays($this->faker->numberBetween(0, 60))->toDateString()
				: null;

			$assignedAt = $this->faker->boolean(65) ? CarbonImmutable::now()->subDays($this->faker->numberBetween(0, 30)) : null;

			$data = [
				'id' => (string) Str::uuid(),
				'code' => $this->generateUniqueCodeWithLimit(40),
				'name' => $this->faker->sentence(4, true),
				'description' => $this->faker->boolean(70) ? $this->faker->paragraph(2, true) : null,
				'color' => $this->faker->boolean(70) ? $this->faker->hexColor() : '#ffeee0',
				'responsible' => $this->faker->boolean(75) ? $responsible : null,
				PJC::COL_ASGN => $this->faker->boolean(80) ? $assignTo : null,
				PJC::COL_ASG_BY => $this->faker->boolean(75) ? $assignedBy : null,
				PJC::COL_ASG_AT => $assignedAt,
				PJC::COL_E_HRS => $this->faker->numberBetween(0, 80),
				PJC::COL_ACT_HRS => $this->faker->numberBetween(0, 120),
				PJC::COL_LAST_ACT_AT => $this->faker->boolean(60) ? CarbonImmutable::now()->subDays($this->faker->numberBetween(0, 30)) : null,
				PJC::COL_S_DT => $start,
				PJC::COL_E_DT => $end,
				AC::COL_MT => Arr::random(AppModuleType::values()),
				'priority' => $priority,
				'progress' => $progress,
				'status' => $status,
				PJC::COL_PR_CL => $priorityColor,
				AC::COL_OD => $this->faker->numberBetween(0, 1000),
				'depth' => $this->faker->numberBetween(0, 5),
				PJC::COL_PJ_ID => $pid,
				'parent' => null,
				PJC::COL_ML_ID => null,
				PJC::COL_STAGE_ID => null,
				PJC::COL_IS_FV => $this->faker->boolean(20),
				PJC::COL_IS_CP => $progressInt >= 100,
				PJC::COL_M_AT => $this->faker->boolean(10) ? CarbonImmutable::now()->toDateString() : null,
				'recurring' => $this->faker->boolean(10),
				'attachments' => $this->faker->boolean(10) ? [] : null,
				'involved' => $involved,
				'tags' => $this->faker->boolean(25) ? [$this->faker->word(), $this->faker->word()] : null,
				'metadata' => $this->faker->boolean(20) ? ['seed' => true, 'v' => 1] : null,
				'positioning' => $this->faker->boolean(30) ? ['x' => $this->faker->numberBetween(0, 1200), 'y' => $this->faker->numberBetween(0, 800)] : null,
				'notes' => $this->faker->boolean(15) ? ['note' => $this->faker->sentence(10, true)] : null,
				'rules' => $this->faker->boolean(10) ? ['max_files' => $this->faker->numberBetween(0, 10)] : null,
				'reactions' => $this->faker->boolean(10) ? [] : null,
				DC::COL_TABLE_CREATOR => $creatorId,
			];

			$this->out->writeln(
				"<comment>[ProjectTasksSeeder]</comment> create task"
					. " project={$pid}"
					. " code={$data['code']}"
					. " progress={$progress}"
					. " status={$status}"
					. " assign_to=" . (is_string($data[PJC::COL_ASGN] ?? null) ? 'yes' : 'no')
			);

			// Use model save to preserve casting and any model-level boot/validation hooks.
			$task = new ProjectTask();
			$task->forceFill($data);
			$task->save();

			$created++;
			if ($created >= $targetTotal) {
				break;
			}

			// If we exhausted planned counts but still need to reach target, top-up by re-arming planned counts
			// without exceeding 32 per project; in practice this only happens when we "rounded up" to a 64 multiple.
			if ($created < $targetTotal && $this->allPlannedZero($planned)) {
				foreach ($projectQueue as $p) {
					$planned[$p] = 1; // minimal top-up step; still bounded by targetTotal
				}
			}
		}

		$this->out->writeln("<info>[ProjectTasksSeeder]</info> Created={$created} (multiple of 64=" . (($created % 64) === 0 ? 'yes' : 'no') . ")");
	}

	private function pickUserId(array $userIds): string
	{
		$v = Arr::random($userIds);
		if (!is_string($v) || trim($v) === '') {
			return (string) Arr::first($userIds);
		}
		return $v;
	}

	/**
	 * Builds a comma-separated legacy token list (IDs and/or names) for assign_to.
	 * All tokens are strings and should remain valid after explode(',').
	 */
	private function buildAssignToTokens(array $userIds, array $employeeIds, int $maxCount = 5): array
	{
		$count = $this->faker->numberBetween(1, max(1, $maxCount));
		$tokens = [];

		$attempts = 0;
		$limit = 80;

		while (count($tokens) < $count && $attempts < $limit) {
			$attempts++;

			$poolChoice = $this->faker->numberBetween(1, 100);

			// Prefer actual IDs; occasionally add a readable string.
			if ($poolChoice <= 60) {
				$tokens[] = (string) Arr::random($userIds);
				continue;
			}

			if ($employeeIds !== [] && $poolChoice <= 90) {
				$tokens[] = (string) Arr::random($employeeIds);
				continue;
			}

			$tokens[] = (string) $this->faker->name();
		}

		// Normalize shape and uniqueness (not nullish removal). Keep strings only.
		$tokens = array_values(array_map(static fn($v) => is_string($v) ? trim($v) : '', $tokens));
		$tokens = array_values(array_filter($tokens, static fn($v) => is_string($v) && $v !== ''));

		// Ensure at least 1 token.
		if ($tokens === []) {
			$tokens = [(string) Arr::random($userIds)];
		}

		// Keep at most $maxCount distinct tokens.
		$tokens = array_values(collect($tokens)->unique()->take($maxCount)->all());

		return $tokens;
	}

	/**
	 * Ensures involved includes:
	 * - assigned_by (uuid string)
	 * - assign_to raw string (legacy)
	 * - responsible (uuid string)
	 * plus the exploded assign_to tokens.
	 */
	private function buildInvolved(array $assignTokens, string $assignedBy, ?string $assignTo, string $responsible): array
	{
		$base = [
			$assignedBy,
			$responsible,
			is_string($assignTo) ? $assignTo : null,
		];

		$all = array_merge($base, $assignTokens);

		// Do NOT "array_filter to remove nulls" blindly for nullable columns elsewhere;
		// here we are shaping a list intentionally.
		$all = array_values(array_map(static fn($v) => is_string($v) ? trim($v) : '', $all));
		$all = array_values(array_filter($all, static fn($v) => is_string($v) && $v !== ''));

		return array_values(collect($all)->unique()->values()->all());
	}

	private function deriveStatusFromProgress(int $progress): string
	{
		if ($progress >= 100) return EvaluationStatus::Completed->value;
		if ($progress > 0) return EvaluationStatus::InProgress->value;
		return EvaluationStatus::NotStarted->value;
	}

	private function generateUniqueCodeWithLimit(int $attemptLimit = 40): string
	{
		$attempts = 0;

		do {
			$attempts++;
			$code = 'PRJ-TSK-' . Str::uuid();
			$exists = DB::table(DC::TABLE_PROJ_TSKS)->where('code', $code)->exists();

			if (!$exists) return $code;
		} while ($attempts < $attemptLimit);

		// Break-out fallback (still very unlikely): append timestamp and random suffix.
		$fallback = 'PRJ-TSK-' . Str::uuid() . '-' . CarbonImmutable::now()->timestamp . '-' . Str::random(6);

		$attempts = 0;
		while (DB::table(DC::TABLE_PROJ_TSKS)->where('code', $fallback)->exists() && $attempts < $attemptLimit) {
			$attempts++;
			$fallback = 'PRJ-TSK-' . Str::uuid() . '-' . CarbonImmutable::now()->timestamp . '-' . Str::random(6);
		}

		return $fallback;
	}

	private function allPlannedZero(array $planned): bool
	{
		foreach ($planned as $v) {
			if ((int) $v > 0) return false;
		}
		return true;
	}
}
