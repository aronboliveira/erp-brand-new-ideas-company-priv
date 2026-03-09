<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\AppModuleType;
use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class NoteSeeder extends Seeder
{
	private ConsoleOutput $out;

	// hard safety caps
	private const MAX_NOTES_PER_MODULE = 64;
	private const MAX_TOTAL_NOTES = 128000; // original: 128000
	private const HARD_CAP = 4;

	// uniqueness loop limits
	private const TITLE_ATTEMPT_LIMIT = 256;
	private const PICK_USER_ATTEMPT_LIMIT = 64;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$table = DC::TABLE_NOTES;

		// ----------------------------
		// Read-only optimized fetches
		// ----------------------------
		$userTable = DC::TABLE_USERS;

		$userIds = [];
		try {
			if ($this->hasTable($userTable)) {
				// raw read (fast) — no model casting needed
				$rows = DB::select("select id from {$userTable} limit 5000");
				foreach ($rows as $r) {
					$id = is_scalar($r->id ?? null) ? (string) $r->id : '';
					if ($id !== '') $userIds[] = $id;
				}
			}
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed to fetch user ids', [
				'error' => $e->getMessage(),
				'line' => $e->getLine(),
				'file' => $e->getFile(),
			]);
		}

		// fallback: still allow seeding without users
		$userIds = array_values(array_unique($userIds));

		// ----------------------------
		// Per-module randomized counts
		// ----------------------------
		$cases = AppModuleType::cases();
		$plan = [];
		$rawTotal = 0;

		foreach ($cases as $case) {
			// 0..64 (inclusive)
			$n = random_int(0, self::MAX_NOTES_PER_MODULE);
			$plan[$case->value] = $n;
			$rawTotal += $n;
		}

		// ----------------------------
		// Adjust total to a 64-multiple
		// Rule: final count must satisfy (count % 64 == 0)
		// ----------------------------
		$targetTotal = $rawTotal;

		// Round UP to nearest multiple of 64 (safe and deterministic)
		$rem = $targetTotal % 64;
		if ($rem !== 0) $targetTotal += (64 - $rem);

		if ($targetTotal > self::MAX_TOTAL_NOTES) {
			$targetTotal = self::MAX_TOTAL_NOTES - (self::MAX_TOTAL_NOTES % 64); // keep multiple-of-64
		}

		$extraNeeded = min(2, max(0, $targetTotal - $rawTotal)); // capped to max 2 (was unbounded)
		if ($extraNeeded > 0) {
			// distribute extras across random module types while respecting per-module cap (<= 64)
			$moduleKeys = array_keys($plan);
			$attempts = 0;
			while ($extraNeeded > 0 && $attempts < 10) { // was 100000
				$attempts++;
				$k = $moduleKeys[array_rand($moduleKeys)];
				if (($plan[$k] ?? 0) >= self::MAX_NOTES_PER_MODULE) continue;
				$plan[$k]++;
				$extraNeeded--;
			}

			if ($extraNeeded > 0) {
				Log::warning(static::class . ' unable to fully distribute extra notes within per-module cap', [
					'extra_remaining' => $extraNeeded,
					'target_total' => $targetTotal,
					'raw_total' => $rawTotal,
				]);
			}
		}

		$finalTotal = array_sum($plan);
		// Hard guard: keep the contract
		if ($finalTotal % 64 !== 0) {
			$delta = $finalTotal % 64;
			$finalTotal -= $delta;
			// reduce from modules that still have > 0
			foreach ($plan as $k => $v) {
				if ($delta <= 0) break;
				if ($v <= 0) continue;
				$take = min($v, $delta);
				$plan[$k] -= $take;
				$delta -= $take;
			}
		}

		$finalTotal = array_sum($plan);
		if ($finalTotal > self::MAX_TOTAL_NOTES) {
			$finalTotal = self::MAX_TOTAL_NOTES - (self::MAX_TOTAL_NOTES % 64);
		}

		$this->out->writeln("<info>NotesSeeder plan</info>");
		// $this->out->writeln("Raw total: {$rawTotal}");
		// $this->out->writeln("Final total (multiple-of-64): {$finalTotal}");
		// foreach ($plan as $k => $v) {
		// 	$this->out->writeln(" - {$k}: {$v}");
		// }

		// ----------------------------
		// Create notes via Model::save()
		// (avoid raw insert to preserve booted/casts)
		// ----------------------------
		$created = 0;

		foreach ($cases as $case) {
			if ($created >= self::HARD_CAP) break;
			$module = $case->value;
			$count = (int)($plan[$module] ?? 0);
			if ($count <= 0) continue;

			for ($i = 0; $i < $count; $i++) {
				if ($created >= self::HARD_CAP) break;
				$creatorId = $this->pickCreatorId($userIds);

				$title = $this->generateUniqueTitle($table, $module, $i);
				$noteBody = $this->generateNoteBody($module, $i);

				$m = new Note();

				// IMPORTANT: keep nulls for nullable columns (no array_filter)
				// Use setAttribute/getAttribute patterns (casts/events)
				$m->setAttribute('title', $title);
				$m->setAttribute('note', $noteBody);
				$m->setAttribute(AC::COL_MT, $module);     // enum column (string) — model should cast later
				$m->setAttribute(AC::COL_MI, null);        // nullable morph id
				$m->setAttribute('document', null);        // nullable unique FK (avoid missing docs)

				// Audit columns (guarded): forceFill so we can set them without breaking mass assignment rules
				// This still triggers events on save().
				if ($creatorId !== null) {
					try {
						$m->forceFill([
							DC::COL_TABLE_CREATOR => $creatorId,
							DC::COL_TABLE_UPDATER => $creatorId,
						]);
					} catch (\Throwable $e) {
						Log::warning(static::class . ' forceFill audit failed', [
							'error' => $e->getMessage(),
							'line' => $e->getLine(),
							'file' => $e->getFile(),
						]);
					}
				}

				// $this->out->writeln(sprintf(
				// 	"Creating note: module=%s title=%s creator=%s",
				// 	$module,
				// 	$title,
				// 	$creatorId ?? 'NULL'
				// ));

				try {
					$m->save();
					$created++;
				} catch (\Throwable $e) {
					Log::error(static::class . ' failed to save note', [
						'module' => $module,
						'title' => $title,
						'creator' => $creatorId,
						'error' => $e->getMessage(),
						'line' => $e->getLine(),
						'file' => $e->getFile(),
					]);
				}

				if ($created >= $finalTotal) break;
			}

			if ($created >= $finalTotal) break;
		}

		$this->out->writeln("<info>NotesSeeder done</info> Created: {$created}");
	}

	private function pickCreatorId(array $userIds): ?string
	{
		if (!$userIds) return null;

		$attempts = 0;
		while ($attempts < self::PICK_USER_ATTEMPT_LIMIT) {
			$attempts++;
			$id = $userIds[array_rand($userIds)] ?? null;
			if (is_string($id) && trim($id) !== '') return $id;
		}

		return null;
	}

	private function generateUniqueTitle(string $table, string $module, int $i): string
	{
		$attempts = 0;

		$base = 'Note ' . strtoupper(str_replace('_', ' ', $module)) . ' #' . ($i + 1);
		$candidate = $base . ' — ' . Str::uuid()->toString();

		try {
			do {
				$attempts++;
				if ($attempts > self::TITLE_ATTEMPT_LIMIT) {
					// break-out strategy: force high-entropy title
					$candidate = $base . ' — ' . Str::uuid()->toString() . ' — ' . now()->timestamp;
					break;
				}

				$candidate = $base . ' — ' . Str::uuid()->toString();
			} while (
				DB::table($table)
				->where('title', $candidate)
				->exists()
			);
		} catch (\Throwable $e) {
			Log::warning(static::class . ' unique title check failed (fallback to entropy)', [
				'module' => $module,
				'error' => $e->getMessage(),
				'line' => $e->getLine(),
				'file' => $e->getFile(),
			]);
			$candidate = $base . ' — ' . Str::uuid()->toString() . ' — ' . now()->timestamp;
		}

		return $candidate;
	}

	private function generateNoteBody(string $module, int $i): string
	{
		// keep it deterministic-ish but varied; no need for Faker
		$topics = [
			'context',
			'decision',
			'risk',
			'follow-up',
			'summary',
			'implementation',
			'stakeholders',
			'deadline',
			'assumptions',
			'open questions',
		];
		$t1 = $topics[array_rand($topics)];
		$t2 = $topics[array_rand($topics)];

		return trim(
			"Module: {$module}\n" .
				"Sequence: " . ($i + 1) . "\n" .
				"Tags: {$t1}, {$t2}\n" .
				"Body: Generated mock note for validation of module_type normalization and persistence."
		);
	}

	private function hasTable(string $table): bool
	{
		try {
			// Schema facade not required here; raw check is sufficient
			DB::select("select 1 from {$table} limit 1");
			return true;
		} catch (\Throwable) {
			return false;
		}
	}
}
