<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, EmailsConstants as EC, ProjectsConstants as PJC};
use App\Models\ProjectEmailTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ProjectEmailTemplateSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const MAX_TEMPLATES_PER_PROJECT = 8;
	private const MULTIPLE = 64;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$table = DC::TABLE_PRJ_EM_TMP;

		if (!Schema::hasTable($table)) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Skipped: table '{$table}' does not exist.");
			return;
		}

		if (!Schema::hasTable(DC::TABLE_PROJECTS)) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Skipped: projects table does not exist.");
			return;
		}

		if (!Schema::hasTable(DC::TABLE_EMAIL_TEMPLATES)) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Skipped: email_templates table does not exist.");
			return;
		}

		$projects = $this->fetchIds(DC::TABLE_PROJECTS);
		if (!$projects) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Skipped: no projects found.");
			return;
		}

		$templateMap = $this->fetchEmailTemplateMap();
		if (!$templateMap) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Skipped: no email templates found.");
			return;
		}

		$userIds = Schema::hasTable(DC::TABLE_USERS) ? $this->fetchIds(DC::TABLE_USERS) : [];

		$existing = $this->safeCount($table);
		// $hardCap  = (int) (env('PROJECT_EMAIL_TEMPLATE_HARD_CAP') ?: 65536);
		$hardCap  = (int) (env('PROJECT_EMAIL_TEMPLATE_HARD_CAP') ?: 2);
		$remainingCap = max(0, $hardCap - $existing);

		if ($remainingCap <= 0) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Skipped: hard cap reached ({$hardCap}). Existing={$existing}.");
			return;
		}

		$availableTemplateIds = array_keys($templateMap);
		$maxPerProject = min(self::MAX_TEMPLATES_PER_PROJECT, count($availableTemplateIds));
		if ($maxPerProject <= 0) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Skipped: templates list is empty.");
			return;
		}

		// 1) Precompute counts per project (0..8) with inverse-quadratic bias towards low counts.
		$counts = [];
		foreach ($projects as $pid) {
			$counts[$pid] = $this->drawInverseQuadraticInt(0, $maxPerProject);
		}

		$planned = array_sum($counts);

		// 2) Cap by remaining hard cap (if needed).
		if ($planned > $remainingCap) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Warning: planned={$planned} exceeds remainingCap={$remainingCap}. Reducing.");
			$this->reduceCountsToTotal($counts, $remainingCap);
			$planned = array_sum($counts);
		}

		// 3) Enforce 64-multiple when feasible within 0..maxPerProject per project.
		$maxPossible = $maxPerProject * count($projects);
		if ($maxPossible >= self::MULTIPLE) {
			$this->adjustCountsToNearestMultiple($counts, $maxPerProject, $remainingCap, self::MULTIPLE);
			$planned = array_sum($counts);
		} elseif ($planned % self::MULTIPLE !== 0) {
			$this->out->writeln(
				"<comment>[ProjectEmailTemplateSeeder]</comment> Warning: cannot enforce % "
					. self::MULTIPLE
					. " == 0 (maxPossible={$maxPossible}). Proceeding with planned={$planned}."
			);
		}

		$inserted = 0;
		$attemptCap = 32;

		foreach ($counts as $projectId => $n) {
			if ($inserted >= $remainingCap) break;
			if ($n <= 0) continue;

			$pickCount = min($n, $maxPerProject);
			$pickedTemplateIds = $this->pickUnique($availableTemplateIds, $pickCount);

			$seq = 1;
			foreach ($pickedTemplateIds as $templateId) {
				if ($inserted >= $remainingCap) break 2;

				$t = $templateMap[$templateId] ?? null;
				if (!is_array($t)) {
					Log::debug(self::class . ' missing template payload in map', [
						'template_id' => $templateId,
					]);
					continue;
				}

				$code = $this->makeUniqueCode($table, $attemptCap);
				$name = $this->makeName($t['title'] ?? null, $seq);

				$fonts  = $this->chance(0.70) ? $this->encodeJsonSafe($this->randomFonts()) : null;
				$colors = $this->chance(0.75) ? $this->encodeJsonSafe($this->randomHexColors()) : null;
				$tags   = $this->chance(0.60) ? $this->encodeJsonSafe($this->randomTags()) : null;

				$createdBy = $this->pickId($userIds, 0.25);
				$updatedBy = $this->chance(0.55) ? ($createdBy ?: $this->pickId($userIds, 0.25)) : null;

				$payload = [
					'code' => $code,
					'name' => $name,

					EC::COL_TMP   => $templateId,
					PJC::COL_PJ_ID => $projectId,
					EC::COL_IA    => $this->chance(0.18),

					'fonts'     => $fonts,
					'colors'    => $colors,

					// MUST incorporate, never alter
					'variables' => $t['variables'] ?? null,
					'settings'  => $t['settings'] ?? null,

					'tags'      => $tags,

					DC::COL_TABLE_CREATOR => $createdBy,
					DC::COL_TABLE_UPDATER => $updatedBy,
				];

				// $this->out->writeln(
				// 	"[PRJ-EM-TMP] proj={$projectId} tmp={$templateId} code={$code} active=" . ((int) $payload[EC::COL_IA]) . " seq={$seq}"
				// );

				try {
					$m = new ProjectEmailTemplate();
					$m->forceFill($payload);
					$m->save();
					$inserted++;
				} catch (\Throwable $e) {
					Log::error(self::class . ' failed saving ProjectEmailTemplate: ' . $e->getMessage(), [
						'project_id' => $projectId,
						'template_id' => $templateId,
						'code' => $code,
					]);
				}

				$seq++;
			}
		}

		$this->out->writeln("<info>[ProjectEmailTemplateSeeder]</info> Done. Inserted={$inserted} (existing={$existing}, cap={$hardCap}).");
	}

	private function safeCount(string $table): int
	{
		try {
			$row = DB::select("select count(*) as c from {$table}")[0] ?? null;
			return (int) ($row->c ?? 0);
		} catch (\Throwable $e) {
			Log::debug(self::class . " safeCount failed for {$table}: " . $e->getMessage());
			return 0;
		}
	}

	private function fetchIds(string $table): array
	{
		if (!Schema::hasTable($table))
			return [];

		try {
			$rows = DB::select("select id from {$table} limit 20000");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return array_values($out);
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchIds failed for {$table}: " . $e->getMessage());
			return [];
		}
	}

	/**
	 * Returns map: template_id => ['title' => ?string, 'variables' => ?string, 'settings' => ?string]
	 */
	private function fetchEmailTemplateMap(): array
	{
		$table = DC::TABLE_EMAIL_TEMPLATES;
		if (!Schema::hasTable($table))
			return [];

		try {
			$rows = DB::select("select id, title, variables, settings from {$table} limit 20000");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id === '') continue;

				$title = isset($r->title) ? (string) $r->title : null;

				$variables = null;
				if (isset($r->variables) && is_string($r->variables) && trim($r->variables) !== '')
					$variables = $r->variables;

				$settings = null;
				if (isset($r->settings) && is_string($r->settings) && trim($r->settings) !== '')
					$settings = $r->settings;

				$out[$id] = [
					'title'     => $title,
					'variables' => $variables,
					'settings'  => $settings,
				];
			}
			return $out;
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchEmailTemplateMap failed: " . $e->getMessage());
			return [];
		}
	}

	private function chance(float $p): bool
	{
		return mt_rand() / mt_getrandmax() < max(0.0, min(1.0, $p));
	}

	/**
	 * Inverse-quadratic distribution on [min..max], weights ~ 1/(k+1)^2 (k shifted to start at 0).
	 */
	private function drawInverseQuadraticInt(int $min, int $max): int
	{
		if ($max <= $min) return $min;

		$range = [];
		$weights = [];
		$totalW = 0.0;

		for ($v = $min; $v <= $max; $v++) {
			$k = $v - $min; // 0..(max-min)
			$w = 1.0 / pow(($k + 1), 2);
			$range[] = $v;
			$weights[] = $w;
			$totalW += $w;
		}

		$r = (mt_rand() / mt_getrandmax()) * $totalW;
		$acc = 0.0;

		foreach ($range as $i => $v) {
			$acc += $weights[$i];
			if ($r <= $acc)
				return $v;
		}

		return $min;
	}

	private function reduceCountsToTotal(array &$counts, int $targetTotal): void
	{
		$attempts = 0;
		$attemptCap = 200000;

		$current = array_sum($counts);
		if ($current <= $targetTotal) return;

		$keys = array_keys($counts);

		while ($current > $targetTotal && $attempts < $attemptCap) {
			$attempts++;

			$pid = $keys[array_rand($keys)];
			$cur = (int) ($counts[$pid] ?? 0);

			if ($cur <= 0) continue;

			$counts[$pid] = $cur - 1;
			$current--;
		}

		if ($attempts >= $attemptCap) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Warning: reduceCountsToTotal attempt cap reached.");
		}
	}

	private function adjustCountsToNearestMultiple(
		array &$counts,
		int $maxPerProject,
		int $remainingCap,
		int $multiple
	): void {
		$current = array_sum($counts);
		$maxPossible = $maxPerProject * count($counts);

		$lower = (int) (floor($current / $multiple) * $multiple);
		$upper = (int) (ceil($current / $multiple) * $multiple);

		// Prefer rounding up, but stay feasible and within remainingCap.
		$target = $current;

		$canUp = $upper <= $maxPossible && $upper <= $remainingCap;
		$canDown = $lower >= 0;

		if ($canUp) $target = $upper;
		elseif ($canDown) $target = $lower;

		if ($target === $current) return;

		$keys = array_keys($counts);
		$attempts = 0;
		$attemptCap = 200000;

		while ($current < $target && $attempts < $attemptCap) {
			$attempts++;

			$pid = $keys[array_rand($keys)];
			$cur = (int) ($counts[$pid] ?? 0);

			if ($cur >= $maxPerProject) continue;

			$counts[$pid] = $cur + 1;
			$current++;
		}

		while ($current > $target && $attempts < $attemptCap) {
			$attempts++;

			$pid = $keys[array_rand($keys)];
			$cur = (int) ($counts[$pid] ?? 0);

			if ($cur <= 0) continue;

			$counts[$pid] = $cur - 1;
			$current--;
		}

		if ($attempts >= $attemptCap) {
			$this->out->writeln("<comment>[ProjectEmailTemplateSeeder]</comment> Warning: adjustCountsToNearestMultiple attempt cap reached.");
		}
	}

	private function pickId(array $ids, float $nullChance = 0.1): ?string
	{
		if (!$ids || $this->chance($nullChance))
			return null;

		return (string) $ids[array_rand($ids)];
	}

	private function pickUnique(array $pool, int $count): array
	{
		if (!$pool || $count <= 0) return [];

		$count = min($count, count($pool));
		$keys = array_rand($pool, $count);

		if (!is_array($keys))
			return [(string) $pool[$keys]];

		$out = [];
		foreach ($keys as $k)
			$out[] = (string) $pool[$k];

		return array_values($out);
	}

	private function makeUniqueCode(string $table, int $attemptCap): string
	{
		$attempts = 0;

		do {
			$attempts++;

			$uuid = Str::uuid()->toString();
			$code = 'PRJ-EM-TMP-' . $uuid;

			$exists = false;
			try {
				$row = DB::select("select 1 as e from {$table} where code = ? limit 1", [$code])[0] ?? null;
				$exists = (bool) ($row->e ?? false);
			} catch (\Throwable $e) {
				Log::debug(self::class . ' makeUniqueCode exists-check failed: ' . $e->getMessage(), ['code' => $code]);
				$exists = false;
			}

			if (!$exists) return $code;
		} while ($attempts < $attemptCap);

		// Fallback: include timestamp to reduce collision risk.
		return 'PRJ-EM-TMP-' . Str::uuid()->toString() . '-' . CarbonImmutable::now()->timestamp;
	}

	private function makeName(?string $templateTitle, int $seq): ?string
	{
		$title = is_string($templateTitle) ? trim($templateTitle) : '';
		if ($title === '') $title = 'Project Email Template';
		return $title . ' #' . $seq;
	}

	private function encodeJsonSafe(mixed $value): ?string
	{
		try {
			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch (\Throwable $e) {
			Log::debug(self::class . ' json_encode failed: ' . $e->getMessage());
			return null;
		}
	}

	private function randomFonts(): array
	{
		$pool = [
			'Inter',
			'Roboto',
			'Open Sans',
			'Lato',
			'Montserrat',
			'Source Sans 3',
			'Poppins',
			'Nunito',
			'Merriweather',
			'Ubuntu',
		];

		$count = random_int(1, 4);
		$out = $this->pickUnique($pool, $count);
		return $out ?: ['Inter'];
	}

	private function randomHexColors(): array
	{
		$count = random_int(1, 6);

		$out = [];
		for ($i = 0; $i < $count; $i++) {
			$out[] = sprintf('#%06X', random_int(0, 0xFFFFFF));
		}

		$out = array_values(array_unique($out));
		return $out ?: ['#000000'];
	}

	private function randomTags(): array
	{
		$all = ['project', 'email', 'template', 'branding', 'notify', 'seeded', 'qa', 'ops'];
		$k = random_int(1, 4);

		$out = [];
		for ($i = 0; $i < $k; $i++)
			$out[] = $all[array_rand($all)];

		$out = array_values(array_unique($out));
		return $out ?: ['seeded'];
	}
}
