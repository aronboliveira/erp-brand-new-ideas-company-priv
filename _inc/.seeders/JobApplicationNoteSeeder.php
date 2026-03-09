<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Models\{JobApplicationNote, Note, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class JobApplicationNoteSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const MAX_NOTES_PER_APP = 8;
	// private const HARD_CAP = 16000;
	private const HARD_CAP = 2;

	/**
	 * Attempts limits (escape hatches)
	 */
	private const ADJUST_ATTEMPT_LIMIT = 1600;
	private const PICK_USER_ATTEMPT_LIMIT = 64;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_JOB_APPS)) {
			$this->out->writeln('<comment>[JobApplicationNoteSeeder]</comment> Missing table: ' . DC::TABLE_JOB_APPS);
			return;
		}

		if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_JB_AP_NTS)) {
			$this->out->writeln('<comment>[JobApplicationNoteSeeder]</comment> Missing table: ' . DC::TABLE_JB_AP_NTS);
			return;
		}

		if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_USERS)) {
			$this->out->writeln('<comment>[JobApplicationNoteSeeder]</comment> Missing table: ' . DC::TABLE_USERS);
			return;
		}

        // 1) READ (optimized via RAW SQL methods)
		/** @var string[] $appIds */
		$appIds = DB::table(DC::TABLE_JOB_APPS)->select('id')->orderBy('id')->pluck('id')->all();

		if (!$appIds) {
			$this->out->writeln('<comment>[JobApplicationNoteSeeder]</comment> No job applications found.');
			return;
		}

		/** @var string[] $userIds */
		$userIds = DB::table(DC::TABLE_USERS)->select('id')->orderBy('id')->pluck('id')->all();

		if (!$userIds) {
			$this->out->writeln('<comment>[JobApplicationNoteSeeder]</comment> No users found. Cannot create author/reviewer references.');
			return;
		}

        // Optional: names map (RAW read). Keep it light.
		/** @var array<string,string> $userNamesById */
		$userNamesById = DB::table(DC::TABLE_USERS)
			->select('id', 'name')
			->whereIn('id', $userIds)
			->pluck('name', 'id')
			->map(fn($v) => is_scalar($v) ? trim((string) $v) : '')
			->all();

		// 2) PLAN counts with inverse quadratic distribution
		// countsByIndex uses numeric indexes (same order as $appIds)
		$countsByIndex = [];
		$rawTotal = 0;

		foreach ($appIds as $i => $appId) {
			$n = $this->drawInverseQuadraticCount(self::MAX_NOTES_PER_APP);
			$countsByIndex[$i] = $n;
			$rawTotal += $n;

			if ($rawTotal >= self::HARD_CAP) {
				// stop early if we already hit the cap (remaining apps get 0)
				$countsByIndex[$i] = max(0, $n - ($rawTotal - self::HARD_CAP));
				$rawTotal = self::HARD_CAP;

				for ($j = $i + 1; $j < count($appIds); $j++) {
					$countsByIndex[$j] = 0;
				}
				break;
			}
		}

		// 3) Adjust to multiple of 64, without exceeding hard cap.
		$targetTotal = $this->adjustToMultipleOf64WithinCap($rawTotal, self::HARD_CAP);

		if ($targetTotal !== $rawTotal) {
			$this->out->writeln(
				sprintf(
					'<info>[JobApplicationNoteSeeder]</info> Adjusting total from %d to %d (multiple of 64; cap=%d)',
					$rawTotal,
					$targetTotal,
					self::HARD_CAP
				)
			);

			$this->adjustCountsToTarget($countsByIndex, $targetTotal);
		}

		// Recompute final total (defensive)
		$finalTotal = 0;
		foreach ($countsByIndex as $v) {
			$finalTotal += (int) $v;
		}

		// Ensure final total multiple of 64 (defensive)
		if (($finalTotal % 64) !== 0) {
			$fixed = $finalTotal - ($finalTotal % 64);
			if ($fixed < 0) $fixed = 0;
			$this->out->writeln(
				sprintf(
					'<comment>[JobApplicationNoteSeeder]</comment> Defensive fix: total %d not multiple of 64, trimming to %d.',
					$finalTotal,
					$fixed
				)
			);
			$this->adjustCountsToTarget($countsByIndex, $fixed);
			$finalTotal = $fixed;
		}

		// 4) Create rows (Eloquent) to respect $casts and booted() logic
		$this->out->writeln(
			sprintf(
				'<info>[JobApplicationNoteSeeder]</info> Creating %d JobApplicationNote rows (%d applications scanned).',
				$finalTotal,
				count($appIds)
			)
		);

		$created = 0;
		$attemptGuard = 0;

		foreach ($appIds as $i => $appId) {
			$n = (int) ($countsByIndex[$i] ?? 0);
			if ($n <= 0) continue;

			for ($k = 0; $k < $n; $k++) {
				// Escape hatch for abnormal loops
				if ($attemptGuard++ > (self::HARD_CAP * 3)) {
					$this->out->writeln('<error>[JobApplicationNoteSeeder]</error> Abort: excessive create attempts guard triggered.');
					return;
				}

				// Choose author/reviewer patterns with nullable behavior preserved.
				// author (string) + author_id (uuid) are independently nullable.
				// reviewer column is treated as user id (uuid), consistent with foreign key and model relation.
				[$authorId, $authorName] = $this->pickAuthor($userIds, $userNamesById);
				$reviewerId = $this->pickReviewer($userIds, $authorId);

				// Create a Note row referenced by AC::COL_NOTE_CREATED
				// Prefer Eloquent Note::create if available; fallback to raw insert if class is absent.
				$noteText = $this->fakeNoteText($appId, $k);

				$noteId = $this->createNoteRow($noteText);
				if (!is_string($noteId) || trim($noteId) === '') {
					$this->out->writeln('<comment>[JobApplicationNoteSeeder]</comment> Skipping: failed to create Note row.');
					continue;
				}

				// Sometimes keep JobApplicationNote.note NULL (redundant column) so accessor can resolve from Note row.
				$inlineNote = $this->maybeInlineNoteText($noteText);

				// Console detail right before create (as required)
				$this->out->writeln(sprintf(
					'[JobApplicationNoteSeeder] create note: app=%s author_id=%s author="%s" reviewer_id=%s note_row=%s inline_note=%s',
					(string) $appId,
					$authorId !== null ? $authorId : 'NULL',
					$authorName !== null ? $authorName : 'NULL',
					$reviewerId !== null ? $reviewerId : 'NULL',
					(string) $noteId,
					$inlineNote !== null ? 'YES' : 'NO'
				));

				// IMPORTANT: do NOT array_filter nulls out; Eloquent will handle nullable fields.
				JobApplicationNote::create([
					AC::COL_APLN_ID => (string) $appId,
					AC::COL_NOTE_CREATED => (string) $noteId,

					'author' => $authorName,            // nullable
					AC::COL_AUTHOR_ID => $authorId,     // nullable

					// Model booted() will manage AC::COL_WRT_AT based on author/author_id presence
					AC::COL_WRT_AT => null,

					'note' => $inlineNote,              // nullable (redundant)
					'reviewer' => $reviewerId,          // nullable (uuid id)
					// Model booted() will manage AC::COL_RVW_AT based on reviewer presence
					AC::COL_RVW_AT => null,
				]);

				$created++;
				if ($created >= self::HARD_CAP) {
					$this->out->writeln('<comment>[JobApplicationNoteSeeder]</comment> Hard cap reached. Stopping.');
					return;
				}
			}
		}

		$this->out->writeln(sprintf(
			'<info>[JobApplicationNoteSeeder]</info> Done. Created=%d.',
			$created
		));
	}

	private function drawInverseQuadraticCount(int $max): int
	{
		// Inverse quadratic skew towards 0:
		// r in [0,1), r^2 skews closer to 0 than r.
		$r = mt_rand() / mt_getrandmax();
		$x = $r * $r;

		$n = (int) floor($x * ($max + 1));
		if ($n < 0) $n = 0;
		if ($n > $max) $n = $max;
		return $n;
	}

	private function adjustToMultipleOf64WithinCap(int $rawTotal, int $cap): int
	{
		if ($rawTotal <= 0) return 0;

		$mod = $rawTotal % 64;
		if ($mod === 0) return $rawTotal;

		$up = $rawTotal + (64 - $mod);
		if ($up <= $cap) return $up;

		$down = $rawTotal - $mod;
		if ($down < 0) $down = 0;
		return $down;
	}

	/**
	 * Adjust counts array to reach $targetTotal.
	 * Uses attempt limits to avoid pathological loops.
	 *
	 * @param array<int,int> $countsByIndex
	 */
	private function adjustCountsToTarget(array &$countsByIndex, int $targetTotal): void
	{
		$current = 0;
		foreach ($countsByIndex as $v) $current += (int) $v;

		if ($current === $targetTotal) return;

		$attempts = 0;

		if ($current < $targetTotal) {
			$need = $targetTotal - $current;

			while ($need > 0 && $attempts++ < self::ADJUST_ATTEMPT_LIMIT) {
				$idx = array_rand($countsByIndex);
				$v = (int) $countsByIndex[$idx];

				if ($v >= self::MAX_NOTES_PER_APP) {
					continue;
				}

				$countsByIndex[$idx] = $v + 1;
				$need--;
			}

			if ($need > 0) {
				$this->out->writeln(sprintf(
					'<comment>[JobApplicationNoteSeeder]</comment> Could not fully increase to target; remaining=%d after %d attempts.',
					$need,
					$attempts
				));
			}

			return;
		}

		// current > target: reduce
		$excess = $current - $targetTotal;

		while ($excess > 0 && $attempts++ < self::ADJUST_ATTEMPT_LIMIT) {
			$idx = array_rand($countsByIndex);
			$v = (int) $countsByIndex[$idx];

			if ($v <= 0) {
				continue;
			}

			$countsByIndex[$idx] = $v - 1;
			$excess--;
		}

		if ($excess > 0) {
			$this->out->writeln(sprintf(
				'<comment>[JobApplicationNoteSeeder]</comment> Could not fully reduce to target; remaining=%d after %d attempts.',
				$excess,
				$attempts
			));
		}
	}

	/**
	 * Returns [authorId|null, authorName|null]
	 *
	 * - Sometimes sets only authorId (author string null)
	 * - Sometimes sets only author string (authorId null)
	 * - Sometimes sets both
	 * - Sometimes sets both null (to allow "no author" scenario)
	 *
	 * @param string[] $userIds
	 * @param array<string,string> $userNamesById
	 * @return array{0:?string,1:?string}
	 */
	private function pickAuthor(array $userIds, array $userNamesById): array
	{
		$mode = mt_rand(1, 100);

		// 35%: no author at all (forces written_at null via model booted)
		if ($mode <= 35) {
			return [null, null];
		}

		// pick user
		$uid = $this->pickUserId($userIds);
		$name = $uid !== null ? ($userNamesById[$uid] ?? null) : null;
		$name = is_string($name) && trim($name) !== '' ? trim($name) : null;

		// 25%: id only
		if ($mode <= 60) {
			return [$uid, null];
		}

		// 20%: name only
		if ($mode <= 80) {
			return [null, $name];
		}

		// 20%: both
		return [$uid, $name];
	}

	/**
	 * reviewer is a user id (uuid) and nullable.
	 *
	 * @param string[] $userIds
	 */
	private function pickReviewer(array $userIds, ?string $authorId): ?string
	{
		// 60%: no reviewer (reviewed_at null via model booted)
		if (mt_rand(1, 100) <= 60) return null;

		// Try not to pick same as author (not required, but improves realism)
		$attempts = 0;
		do {
			$rid = $this->pickUserId($userIds);
			if ($rid === null) return null;
			if ($authorId === null || $rid !== $authorId) return $rid;
		} while ($attempts++ < self::PICK_USER_ATTEMPT_LIMIT);

		return null;
	}

	/**
	 * @param string[] $userIds
	 */
	private function pickUserId(array $userIds): ?string
	{
		if (!$userIds) return null;
		$idx = array_rand($userIds);
		$id = $userIds[$idx] ?? null;
		return is_string($id) && trim($id) !== '' ? trim($id) : null;
	}

	private function fakeNoteText(string $appId, int $k): string
	{
		// Sem Faker obrigatório; texto simples, consistente e variado.
		$samples = [
			'Triagem inicial: aderência geral aos requisitos.',
			'Pontos fortes: comunicação clara e boa organização.',
			'Atenção: validar disponibilidade e expectativa salarial.',
			'Sugerido aprofundar em experiência prática e projetos recentes.',
			'Feedback do recrutador: perfil promissor; agendar entrevista técnica.',
			'Observação: confirmar autorização de trabalho e prazo de início.',
			'Pendência: anexos incompletos; solicitar atualização.',
			'Notas internas: avaliar fit cultural e maturidade para o nível.',
		];

		$base = $samples[mt_rand(0, count($samples) - 1)];
		$ref = strtoupper(substr(Str::ascii($appId), 0, 8));
		return "[APP {$ref} #{$k}] {$base}";
	}

	private function maybeInlineNoteText(string $noteText): ?string
	{
		// 55% deixa NULL (redundância); 45% replica o texto no campo 'note'
		return (mt_rand(1, 100) <= 55) ? null : $noteText;
	}

	/**
	 * Creates a row in notes table and returns id.
	 * Prefers Note::create() to respect model logic; fallback to raw insert if Note model is unavailable.
	 */
	private function createNoteRow(string $text): ?string
	{
		$id = (string) Str::uuid();

		// Prefer Eloquent if possible
		if (class_exists(Note::class)) {
			try {
				/** @var \Illuminate\Database\Eloquent\Model $row */
				$row = Note::create([
					'id' => $id,
					'note' => $text,
				]);

				$rid = $row->getAttribute('id');
				return is_scalar($rid) && trim((string) $rid) !== '' ? trim((string) $rid) : $id;
			} catch (\Throwable $t) {
				// fallback below
			}
		}

		// Fallback: raw insert (kept minimal). Avoid any json decoding/casts complexity.
		try {
			if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_NOTES)) return null;

			DB::table(DC::TABLE_NOTES)->insert([
				'id' => $id,
				'note' => $text,
				'created_at' => now(),
				'updated_at' => now(),
			]);

			return $id;
		} catch (\Throwable) {
			return null;
		}
	}
}
