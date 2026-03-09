<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\JobStage as JobStageEnum;
use App\Models\JobStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class JobStageSeeder extends Seeder
{
	private const MIN_PER_STAGE = 2;
	// private const MIN_ROWS = 800;
	private const MIN_ROWS = 4; /* original: 800 */
	private const HARD_CAP = 4;

	public function run(): void
	{
		try {
			if (!Schema::hasTable(DC::TABLE_JOB_STG)) {
				Log::warning(static::class . ' skipped: table not found', ['table' => DC::TABLE_JOB_STG]);
				return;
			}
		} catch (\Throwable $e) {
			Log::warning(static::class . ' schema check failed', ['error' => $e->getMessage()]);
			return;
		}

		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		$now = Carbon::now();

		$userId = null;
		try {
			if (defined(DC::class . '::TABLE_USERS')) {
				$userId = DB::table(DC::TABLE_USERS)->inRandomOrder()->value('id');
			} else {
				$userId = DB::table('users')->inRandomOrder()->value('id');
			}
		} catch (\Throwable) {
			$userId = null;
		}

		$usedTitles = [];
		try {
			DB::table(DC::TABLE_JOB_STG)->select('title')->orderBy('title')->chunk(500, function ($rows) use (&$usedTitles): void {
				foreach ($rows as $r) {
					$t = is_string($r->title ?? null) ? $r->title : null;
					if ($t === null || trim($t) === '') continue;
					$usedTitles[$t] = true;
				}
			});
		} catch (\Throwable $e) {
			Log::debug(static::class . ' preload titles failed', ['error' => $e->getMessage()]);
		}

		$created = 0;

		foreach (JobStageEnum::cases() as $case) {
			if ($created >= self::HARD_CAP) break; /* HARD_CAP guard */
			$need = 0;

			try {
				$existing = JobStage::query()->where('status', $case->value)->count();
				if ($existing < self::MIN_PER_STAGE) $need = self::MIN_PER_STAGE - $existing;
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed counting per stage', [
					'status' => $case->value,
					'error' => $e->getMessage(),
				]);
				$need = self::MIN_PER_STAGE;
			}

			for ($i = 1; $i <= $need; $i++) {
				if ($created >= self::HARD_CAP) break; /* HARD_CAP guard */
				$title = $this->uniqueTitleForCase($case, $i, $usedTitles);
				$order = random_int(0, 5000);
				$depth = random_int(0, 8);
				try {
					JobStage::query()->create([
						'title' => $title,
						'slug' => null,
						'status' => $case->value,
						'order' => $order,
						'depth' => $depth,
						'description' => $this->maybeText($case),
						'instructions' => $this->maybeInstructions($case),
						AC::COL_IA => (bool) random_int(0, 1),
						'tags' => $this->randomTags($case),
						'attachments' => $this->randomAttachments(),
						'urls' => $this->randomUrls(),
						'templates' => $this->randomTemplates(),
						'project' => null,
						'goal' => null,
						'training' => null,

						// Audit fields are nullable in HasNullableAuditColumns migrations; do not force if not available.
						'created_at' => $now,
						'updated_at' => $now,
					]);

					// $output->writeln("Created job stage: {$title}, order {$order}, depth {$depth} (status: {$case->value})");
					$created++;
				} catch (\Throwable $e) {
					Log::error(static::class . ' create failed (baseline)', [
						'status' => $case->value,
						'title' => $title,
						'error' => $e->getMessage(),
					]);
				}
			}
		}

		$total = 0;
		try {
			$total = JobStage::query()->count();
		} catch (\Throwable $e) {
			Log::warning(static::class . ' count failed', ['error' => $e->getMessage()]);
			$total = 0;
		}

		$missing = self::MIN_ROWS - $total;
		if ($missing <= 0) return;

		$cases = JobStageEnum::cases();

		for ($n = 1; $n <= $missing; $n++) {
			if ($created >= self::HARD_CAP) break; /* HARD_CAP guard */
			$case = $cases[array_rand($cases)];
			$title = $this->uniqueTitleForCase($case, $n + 1000, $usedTitles);

			try {
				JobStage::query()->create([
					'title' => $title,
					'slug' => null,
					'status' => $case->value,
					'order' => random_int(0, 5000),
					'depth' => random_int(0, 10),
					'description' => $this->maybeText($case),
					'instructions' => $this->maybeInstructions($case),
					AC::COL_IA => (bool) random_int(0, 1),
					'tags' => $this->randomTags($case),
					'attachments' => $this->randomAttachments(),
					'urls' => $this->randomUrls(),
					'templates' => $this->randomTemplates(),
					'project' => null,
					'goal' => null,
					'training' => null,
					'created_at' => $now,
					'updated_at' => $now,
				]);

				$created++;
			} catch (\Throwable $e) {
				Log::error(static::class . ' create failed (fill)', [
					'status' => $case->value,
					'title' => $title,
					'error' => $e->getMessage(),
				]);
			}
		}
	}

	private function uniqueTitleForCase(JobStageEnum $case, int $seq, array &$usedTitles): string
	{
		$base = Str::headline($case->value);
		$base = trim($base) !== '' ? $base : 'Job Stage';

		for ($i = 0; $i < 50; $i++) {
			$suffix = strtoupper(substr((string) Str::uuid(), 0, 8));
			$title = "{$base} — {$seq}-{$suffix}";
			if (!isset($usedTitles[$title])) {
				$usedTitles[$title] = true;
				return $title;
			}
		}

		$title = "{$base} — {$seq}-" . now()->timestamp . '-' . random_int(1000, 9999);
		$usedTitles[$title] = true;
		return $title;
	}

	private function maybeText(JobStageEnum $case): ?string
	{
		if (random_int(1, 100) <= 35) return null;
		$cat = $case->getCategory();
		$hint = Str::headline($case->value);
		return "Stage context: {$cat}. Reference: {$hint}.";
	}

	private function maybeInstructions(JobStageEnum $case): ?string
	{
		if (random_int(1, 100) <= 55) return null;

		$parts = [
			'Collect required documentation.',
			'Ensure candidate communications are recorded.',
			'Update checklist and next steps.',
			'Confirm owner and deadlines.',
		];

		if ($case->requiresManagerApproval()) $parts[] = 'Manager approval required before proceeding.';
		if ($case->isAttentionRequired()) $parts[] = 'Flag for review due to attention-required status.';

		shuffle($parts);
		return implode("\n", array_slice($parts, 0, random_int(2, 4)));
	}

	private function randomTags(JobStageEnum $case): ?array
	{
		if (random_int(1, 100) <= 30) return null;

		$pool = array_values(array_unique([
			$case->getCategory(),
			$case->requiresManagerApproval() ? 'approval' : 'no_approval',
			$case->isRecruitmentStage() ? 'recruitment' : null,
			$case->isActiveEmployment() ? 'employment' : null,
			$case->isTransitionStage() ? 'transition' : null,
			$case->isEndStage() ? 'end' : null,
			$case->isPositiveStage() ? 'positive' : null,
			$case->isAttentionRequired() ? 'attention' : null,
			'workflow',
		]));

		$pool = array_values(array_filter($pool, fn($v) => is_string($v) && trim($v) !== ''));
		shuffle($pool);

		return array_slice($pool, 0, random_int(2, min(6, count($pool))));
	}

	private function randomAttachments(): ?array
	{
		if (random_int(1, 100) <= 70) return null;

		$out = [];
		$n = random_int(1, 3);
		for ($i = 0; $i < $n; $i++) {
			$out[] = [
				'name' => 'doc_' . strtoupper(substr((string) Str::uuid(), 0, 6)),
				'id' => (string) Str::uuid(),
			];
		}
		return $out;
	}

	private function randomUrls(): ?array
	{
		if (random_int(1, 100) <= 60) return null;

		$out = [];
		$n = random_int(1, 3);
		for ($i = 0; $i < $n; $i++) {
			$out[] = 'https://example.test/meet/' . strtolower(substr((string) Str::uuid(), 0, 10));
		}
		return $out;
	}

	private function randomTemplates(): ?array
	{
		if (random_int(1, 100) <= 65) return null;

		$out = [];
		$n = random_int(1, 4);
		for ($i = 0; $i < $n; $i++) {
			$out[] = [
				'type' => ['email', 'notification', 'doc'][array_rand(['email', 'notification', 'doc'])],
				'ref' => 'tpl_' . strtolower(substr((string) Str::uuid(), 0, 8)),
			];
		}
		return $out;
	}
}
