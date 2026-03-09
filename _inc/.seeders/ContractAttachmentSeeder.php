<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{AttachmentModuleType, EvaluationStatus, MimeType};
use App\Models\ContractAttachment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class ContractAttachmentSeeder extends Seeder
{
	private ConsoleOutput $out;

	// private const HARD_CAP = 2048;
	private const HARD_CAP = 2;
	private const MIN_TOTAL = 128;
	private const COVERAGE_RATIO = 0.80;

	// Keep a sane default, but allow rising to satisfy MIN_TOTAL on small datasets
	private const DEFAULT_MAX_PER_CONTRACT = 8;
	private const ABS_MAX_PER_CONTRACT = 256;
	// private const SECONDS_LIMIT = 6 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		DB::transaction(function (): void {
			$clock = microtime(true);
			$contracts = $this->fetchContracts();
			$contractCount = count($contracts);

			if ($contractCount <= 0) {
				$this->out->writeln('<comment>[ContractAttachmentSeeder]</comment> No contracts found. Skipping.');
				return;
			}

			$userIds = $this->fetchUserIds();
			$docIds  = $this->fetchDocIds();

			$coverageWanted = (int) ceil($contractCount * self::COVERAGE_RATIO);

			// Target total: >= MIN_TOTAL and >= coverageWanted, then clamp to HARD_CAP
			$rawTarget = max(self::MIN_TOTAL, $coverageWanted);
			$target = min(self::HARD_CAP, $this->roundUpTo64($rawTarget));
			if ($target > self::HARD_CAP) $target = self::HARD_CAP;

			if ($target <= 0) {
				$this->out->writeln('<comment>[ContractAttachmentSeeder]</comment> Target total is 0. Skipping.');
				return;
			}

			$maxPerContract = $this->computeMaxPerContract($contractCount, $target);

			$maxPossible = $contractCount * $maxPerContract;
			if ($maxPossible < $target) {
				// Best-effort adjustment: reduce target if truly impossible (very small datasets)
				$target = min(self::HARD_CAP, $maxPossible);
				$this->out->writeln(sprintf(
					'<comment>[ContractAttachmentSeeder]</comment> Max possible (%d) < requested target. Adjusted target to %d (maxPerContract=%d).',
					$maxPossible,
					$target,
					$maxPerContract
				));
			}

			$coveragePossible = min($coverageWanted, $target, $contractCount);
			$coverageAchieved = $contractCount > 0 ? ($coveragePossible / $contractCount) : 0.0;

			if ($coverageAchieved + 1e-9 < self::COVERAGE_RATIO) {
				$this->out->writeln(sprintf(
					'<comment>[ContractAttachmentSeeder]</comment> Coverage cannot be fully satisfied under cap=%d. Desired=%.2f, achieved=%.3f.',
					self::HARD_CAP,
					self::COVERAGE_RATIO,
					$coverageAchieved
				));
			}

			// Index contracts by id for fast lookup and stable behavior
			$contractsById = [];
			$contractIds = [];

			foreach ($contracts as $r) {
				$id = (string) ($r->id ?? '');
				if ($id === '') continue;
				$contractsById[$id] = $r;
				$contractIds[] = $id;
			}

			$contractIds = array_values(array_unique($contractIds));
			if (!$contractIds) {
				$this->out->writeln('<comment>[ContractAttachmentSeeder]</comment> Contracts found, but none had valid ids. Skipping.');
				return;
			}

			$countsByContract = $this->planCountsByContract(
				$contractIds,
				$target,
				$coveragePossible,
				$maxPerContract
			);

			$types = AttachmentModuleType::cases();
			$hasMetadata = $this->hasColumnSafe(DC::TABLE_CTC_ATC, 'metadata');

			$created = 0;
			$attempts = 0;
			$attemptLimit = max(4096, $target * 8);

			foreach ($countsByContract as $contractId => $qty) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					return;
				}
				if ($created >= $target) break;
				if ($qty <= 0) continue;

				$cRow = $contractsById[$contractId] ?? null;
				if (!$cRow) continue;

				for ($k = 0; $k < $qty; $k++) {
					if ($created >= $target) break 2;

					$attempts++;
					if ($attempts > $attemptLimit) {
						$this->out->writeln('<comment>[ContractAttachmentSeeder]</comment> Attempt limit reached. Breaking early.');
						break 2;
					}

					$variation = $created;
					$attrs = $this->buildAttachmentAttrs(
						$variation,
						$cRow,
						$userIds,
						$docIds,
						$types,
						$hasMetadata
					);

					$this->out->writeln(sprintf(
						'<info>[CTC_ATC]</info> %d/%d contract=%s atc_tp=%s main=%s code=%s',
						$created + 1,
						$target,
						$contractId,
						(string) ($attrs[PJC::COL_ATC_TP] ?? 'n/a'),
						(string) ($this->firstFileTokenFromCsv($attrs['files'] ?? null) ?? 'none'),
						(string) ($attrs['code'] ?? 'null')
					));

					try {
						ContractAttachment::create($attrs);
						$created++;
					} catch (\Throwable $e) {
						Log::error(static::class . ' failed creating ContractAttachment', [
							'file' => $e->getFile(),
							'line' => $e->getLine(),
							'error' => $e->getMessage(),
							'contract_id' => $contractId,
							'attrs' => $this->safeLogAttrs($attrs),
						]);
					}
				}
			}

			$this->out->writeln(sprintf(
				'<comment>[ContractAttachmentSeeder]</comment> Done. Created %d rows (target=%d, contracts=%d, maxPerContract=%d).',
				$created,
				$target,
				$contractCount,
				$maxPerContract
			));
		}, 3);
	}

	private function computeMaxPerContract(int $contractCount, int $target): int
	{
		$contractCount = max(1, $contractCount);

		$requiredAvg = (int) ceil($target / $contractCount);
		$maxPer = max(self::DEFAULT_MAX_PER_CONTRACT, $requiredAvg);

		$maxPer = min(self::ABS_MAX_PER_CONTRACT, $maxPer);
		$maxPer = min(self::HARD_CAP, $maxPer);

		return max(1, $maxPer);
	}

	/**
	 * Reading-only via raw SQL, with tolerance for schema variation.
	 *
	 * @return array<int,object>
	 */
	private function fetchContracts(): array
	{
		try {
			$cols = ['id'];

			if ($this->hasColumnSafe(DC::TABLE_CONTRACTS, 'status')) $cols[] = 'status';

			if ($this->hasColumnSafe(DC::TABLE_CONTRACTS, PJC::COL_APV_BY)) $cols[] = PJC::COL_APV_BY . ' AS apv_by';
			if ($this->hasColumnSafe(DC::TABLE_CONTRACTS, PJC::COL_APV_AT)) $cols[] = PJC::COL_APV_AT . ' AS apv_at';
			if ($this->hasColumnSafe(DC::TABLE_CONTRACTS, PJC::COL_REJ_BY)) $cols[] = PJC::COL_REJ_BY . ' AS rej_by';
			if ($this->hasColumnSafe(DC::TABLE_CONTRACTS, PJC::COL_REJ_AT)) $cols[] = PJC::COL_REJ_AT . ' AS rej_at';

			$sql = 'SELECT ' . implode(', ', $cols) . ' FROM ' . DC::TABLE_CONTRACTS;
			return DB::select($sql) ?? [];
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed fetching contracts', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function fetchUserIds(): array
	{
		try {
			$rows = DB::select('SELECT id FROM ' . DC::TABLE_USERS);
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed fetching users', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function fetchDocIds(): array
	{
		try {
			if (!$this->hasTableSafe(DC::TABLE_DOCS)) return [];
			$rows = DB::select('SELECT id FROM ' . DC::TABLE_DOCS);
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed fetching docs', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	/**
	 * Plan counts:
	 * - Guarantee $coverageContracts contracts have at least 1 attachment.
	 * - Fill until reaching $target, respecting $maxPerContract.
	 *
	 * @param array<int,string> $contractIds
	 * @return array<string,int>
	 */
	private function planCountsByContract(array $contractIds, int $target, int $coverageContracts, int $maxPerContract): array
	{
		$contractIds = array_values($contractIds);

		$counts = [];
		foreach ($contractIds as $id) $counts[(string) $id] = 0;

		$pool = collect($contractIds)->shuffle()->values();
		$covered = $pool->take(max(0, $coverageContracts))->values()->all();

		foreach ($covered as $id) {
			$id = (string) $id;
			$counts[$id] = 1;
		}

		$current = count($covered);
		$remaining = max(0, $target - $current);

		$attempts = 0;
		$attemptLimit = max(1024, $target * 6);

		while ($remaining > 0 && $attempts < $attemptLimit) {
			$attempts++;

			$advanced = false;
			$shuffled = collect($contractIds)->shuffle()->values()->all();

			foreach ($shuffled as $id) {
				if ($remaining <= 0) break;

				$id = (string) $id;
				$v = (int) ($counts[$id] ?? 0);
				if ($v >= $maxPerContract) continue;

				$inc = ($remaining >= 2 && random_int(0, 100) < 15) ? 2 : 1;
				$inc = min($inc, $remaining);
				$inc = min($inc, $maxPerContract - $v);
				if ($inc <= 0) continue;

				$counts[$id] = $v + $inc;
				$remaining -= $inc;
				$advanced = true;
			}

			if (!$advanced) break;
		}

		if ($remaining > 0) {
			$this->out->writeln(sprintf(
				'<comment>[ContractAttachmentSeeder]</comment> Planning could not reach target: remaining=%d (attempts=%d, maxPerContract=%d).',
				$remaining,
				$attempts,
				$maxPerContract
			));
		}

		return $counts;
	}

	/**
	 * @param array<int,string> $userIds
	 * @param array<int,string> $docIds
	 * @param array<int,\App\Enums\AttachmentModuleType> $types
	 */
	private function buildAttachmentAttrs(
		int $i,
		object $contractRow,
		array $userIds,
		array $docIds,
		array $types,
		bool $hasMetadata
	): array {
		$contractId = (string) ($contractRow->id ?? '');
		$contractStatus = (string) ($contractRow->status ?? '');

		$tp = $types[$i % max(1, count($types))] ?? AttachmentModuleType::Other;

		if (random_int(0, 100) < 55) {
			$contractLike = [
				AttachmentModuleType::Contract,
				AttachmentModuleType::Agreement,
				AttachmentModuleType::Addendum,
				AttachmentModuleType::Amendment,
				AttachmentModuleType::Schedule,
				AttachmentModuleType::Annex,
				AttachmentModuleType::Exhibit,
				AttachmentModuleType::Appendix,
				AttachmentModuleType::NDA,
				AttachmentModuleType::Disclosure,
				AttachmentModuleType::Waiver,
			];
			$tp = $contractLike[random_int(0, count($contractLike) - 1)];
		}

		$submitter = $this->pickUserId($userIds);
		$approver  = $this->pickUserId($userIds);
		$rejecter  = $this->pickUserId($userIds);

		if (random_int(0, 100) < 18) $submitter = null;

		$now = now('America/Sao_Paulo');
		$submittedAt = random_int(0, 100) < 75
			? $now->copy()->subDays(random_int(0, 120))->subMinutes(random_int(0, 1440))
			: null;

		$scenario = $i % 6;

		$apvBy = null;
		$apvAt = null;
		$rejBy = null;
		$rejAt = null;

		if ($scenario === 1) {
			$apvBy = $approver;
			$apvAt = $submittedAt ? $submittedAt->copy()->addMinutes(random_int(1, 600)) : $now->copy()->subMinutes(random_int(1, 1440));
		} elseif ($scenario === 2) {
			$rejBy = $rejecter;
			$rejAt = $submittedAt ? $submittedAt->copy()->addMinutes(random_int(1, 600)) : $now->copy()->subMinutes(random_int(1, 1440));
		} elseif ($scenario === 3) {
			$apvBy = $approver;
			$apvAt = $now->copy()->subMinutes(120);
			$rejBy = $rejecter;
			$rejAt = $now->copy()->subMinutes(30);
		} elseif ($scenario === 4) {
			$rejBy = $rejecter;
			$rejAt = $now->copy()->subMinutes(120);
			$apvBy = $approver;
			$apvAt = $now->copy()->subMinutes(30);
		} elseif ($scenario === 5) {
			$ts = $now->copy()->subMinutes(45);
			$apvBy = $approver;
			$apvAt = $ts;
			$rejBy = $rejecter;
			$rejAt = $ts;
		}

		// Allow boot/save "import" logic to be exercised by nulling sometimes
		$norm = EvaluationStatus::normalize($contractStatus)->value;

		$isAcceptLike = in_array($norm, [EvaluationStatus::Accept->value, EvaluationStatus::Active->value], true);
		$isRejectLike = in_array($norm, [
			EvaluationStatus::Suspended->value,
			EvaluationStatus::Cancelled->value,
			EvaluationStatus::Expired->value,
			EvaluationStatus::Decline->value,
		], true);

		if ($isAcceptLike && random_int(0, 100) < 40) {
			$apvBy = null;
			$apvAt = null;
		}

		if ($isRejectLike && random_int(0, 100) < 40) {
			$rejBy = null;
			$rejAt = null;
		}

		$filePack = $this->buildFilePack($i, $docIds, $userIds);

		$attrs = [
			'code' => $this->uniqueCodeOrNull(40),
			PJC::COL_CTC_ID => $contractId,
			UC::COL_USER_ID => $submitter,
			PJC::COL_SBM_AT => $submittedAt,
			PJC::COL_APV_BY => $apvBy,
			PJC::COL_APV_AT => $apvAt,
			PJC::COL_REJ_BY => $rejBy,
			PJC::COL_REJ_AT => $rejAt,

			DC::COL_FL_PT => $filePack[DC::COL_FL_PT],
			'url' => $filePack['url'],
			'name' => $filePack['name'],
			'extension' => $filePack['extension'],
			DC::COL_MM_TP => $filePack[DC::COL_MM_TP],
			DC::COL_LA => $filePack[DC::COL_LA],
			'size' => $filePack['size'],
			'description' => $filePack['description'],
			'notes' => $filePack['notes'],
			DC::COL_DL_CT => $filePack[DC::COL_DL_CT],
			DC::COL_FL_SZ => $filePack[DC::COL_FL_SZ],
			DC::COL_PERM_RLS => $filePack[DC::COL_PERM_RLS],
			'executors' => $filePack['executors'],
			'editors' => $filePack['editors'],
			'viewers' => $filePack['viewers'],
			DC::COL_EXP_DT => $filePack[DC::COL_EXP_DT],
			'type' => $filePack['type'],

			PJC::COL_ATC_TP => $tp->value,
			'files' => $filePack['files'],
		];

		if ($hasMetadata) {
			$attrs['metadata'] = [
				'seed' => [
					'seeder' => static::class,
					'variation' => $i,
					'contract_status_snapshot' => $contractStatus,
				],
			];
		}

		return $attrs;
	}

	/**
	 * Builds file columns + "files" CSV (main token first when possible).
	 *
	 * @param array<int,string> $docIds
	 * @param array<int,string> $userIds
	 */
	private function buildFilePack(int $i, array $docIds, array $userIds): array
	{
		$appUrl = (string) config('app.url');
		$now = now('America/Sao_Paulo');

		$mode = $i % 12;

		$url = null;
		$filePath = null;
		$docId = null;

		// Prefer url/file_path to exercise "main file" rule
		if ($mode <= 4 && $appUrl !== '') {
			$url = rtrim($appUrl, '/') . '/storage/mock/contracts/ctc_atc_' . (string) Str::uuid() . '.pdf';
		} elseif ($mode <= 8) {
			$filePath = 'contracts/mock/ctc_atc_' . (string) Str::uuid() . '.pdf';
		} elseif ($docIds) {
			$docId = $docIds[random_int(0, max(0, count($docIds) - 1))] ?? null;
		} elseif ($appUrl !== '') {
			$url = rtrim($appUrl, '/') . '/storage/mock/contracts/ctc_atc_' . (string) Str::uuid() . '.txt';
		} else {
			$filePath = 'contracts/mock/ctc_atc_' . (string) Str::uuid() . '.txt';
		}

		// Main token: url first, then file_path, then doc id
		$mainToken = $url ?: ($filePath ?: $docId);

		$extras = [];
		$extraCount = random_int(0, 3);

		for ($k = 0; $k < $extraCount; $k++) {
			$r = random_int(0, 99);
			if ($r < 34 && $docIds) {
				$extras[] = $docIds[random_int(0, max(0, count($docIds) - 1))] ?? null;
			} elseif ($r < 67 && $appUrl !== '') {
				$extras[] = rtrim($appUrl, '/') . '/storage/mock/contracts/extra_' . (string) Str::uuid() . '.png';
			} else {
				$extras[] = 'contracts/mock/extra_' . (string) Str::uuid() . '.txt';
			}
		}

		$tokens = collect([$mainToken, ...$extras])
			->filter(fn($v) => is_string($v) && trim($v) !== '')
			->values()
			->all();

		$filesCsv = $tokens ? implode(',', $tokens) : null;

		$extension = $this->inferExtension($url, $filePath)
			?? ($mainToken ? $this->inferExtension(null, (string) $mainToken) : null)
			?? 'pdf';

		$mime = $this->inferMimeTypeFromExtension($extension);

		$name = random_int(0, 100) < 25
			? null
			: 'FILE_' . strtoupper(Str::random(6)) . '_' . $now->format('Ymd_His') . '_' . $i;

		return [
			DC::COL_FL_PT => $filePath,
			'url' => $url,
			'name' => $name,
			'extension' => $extension,
			DC::COL_MM_TP => $mime,
			DC::COL_LA => random_int(0, 100) < 45 ? $now->copy()->subDays(random_int(0, 30)) : null,
			'size' => random_int(0, 100) < 70 ? random_int(512, 15_000_000) : null,
			'description' => random_int(0, 100) < 35 ? fake()->sentence(10) : null,
			'notes' => random_int(0, 100) < 25 ? fake()->sentence(14) : null,
			DC::COL_DL_CT => random_int(0, 100) < 65 ? (string) random_int(0, 5000) : null,
			DC::COL_FL_SZ => random_int(0, 100) < 65 ? number_format((float) random_int(0, 25_000_000), 4, '.', '') : null,
			DC::COL_PERM_RLS => random_int(0, 100) < 85 ? '776444' : null,
			'executors' => $this->randomUserList($userIds, 4),
			'editors' => $this->randomUserList($userIds, 4),
			'viewers' => $this->randomUserList($userIds, 6),
			DC::COL_EXP_DT => random_int(0, 100) < 10 ? $now->copy()->addDays(random_int(1, 365)) : null,
			'type' => random_int(0, 100) < 20 ? 'document' : null,
			'files' => $filesCsv,
		];
	}

	private function uniqueCodeOrNull(int $attemptLimit): ?string
	{
		if (random_int(0, 100) < 18) return null;

		$attempts = 0;

		do {
			$attempts++;
			$code = 'CTC-ATC-' . strtoupper((string) Str::uuid());

			if (!$this->existsBySql(DC::TABLE_CTC_ATC, 'code', $code)) return $code;
		} while ($attempts < max(1, $attemptLimit));

		return null;
	}

	private function existsBySql(string $table, string $column, string $value): bool
	{
		try {
			$rows = DB::select('SELECT 1 AS ok FROM ' . $table . ' WHERE ' . $column . ' = ? LIMIT 1', [$value]);
			return !empty($rows);
		} catch (\Throwable $e) {
			Log::warning(static::class . ' existsBySql failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
				'column' => $column,
			]);
			return false;
		}
	}

	private function pickUserId(array $userIds): ?string
	{
		if (!$userIds) return null;
		return $userIds[random_int(0, max(0, count($userIds) - 1))] ?? null;
	}

	private function randomUserList(array $userIds, int $max): ?string
	{
		if (!$userIds || $max <= 0) return null;

		$n = random_int(0, $max);
		if ($n <= 0) return null;

		$pool = collect($userIds)->shuffle()->values()->take($n)->values()->all();
		$pool = array_values(array_unique(array_map(fn($v) => (string) $v, $pool)));

		return $pool ? implode(',', $pool) : null;
	}

	private function inferExtension(?string $url, ?string $filePath): ?string
	{
		$src = $url ?: $filePath;
		if (!$src) return null;

		$path = parse_url($src, PHP_URL_PATH);
		$path = is_string($path) && $path !== '' ? $path : $src;

		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$ext = is_string($ext) ? strtolower(trim($ext)) : '';

		return $ext !== '' ? $ext : null;
	}

	private function inferMimeTypeFromExtension(string $ext): string
	{
		$ext = strtolower(trim($ext));

		$raw = match ($ext) {
			'pdf' => 'application/pdf',
			'png' => 'image/png',
			'jpg', 'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'svg' => 'image/svg+xml',
			'webp' => 'image/webp',
			'txt', 'log' => 'text/plain',
			'csv' => 'text/csv',
			'json' => 'application/json',
			default => null,
		};

		try {
			if ($raw) {
				$enum = MimeType::normalize($raw);
				return $enum ? $enum->value : MimeType::OTHER->value;
			}
		} catch (\Throwable $e) {
			Log::debug(static::class . ' failed MimeType normalization', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'ext' => $ext,
				'raw' => $raw,
			]);
		}

		return MimeType::OTHER->value;
	}

	private function roundUpTo64(int $n): int
	{
		if ($n <= 0) return 0;
		$r = $n % 64;
		return $r === 0 ? $n : ($n + (64 - $r));
	}

	private function hasTableSafe(string $table): bool
	{
		try {
			return Schema::hasTable($table);
		} catch (\Throwable $e) {
			Log::debug(static::class . ' hasTableSafe failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
			]);
			return false;
		}
	}

	private function hasColumnSafe(string $table, string $column): bool
	{
		try {
			return Schema::hasColumn($table, $column);
		} catch (\Throwable $e) {
			Log::debug(static::class . ' hasColumnSafe failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
				'column' => $column,
			]);
			return false;
		}
	}

	private function firstFileTokenFromCsv(mixed $csv): ?string
	{
		if (!is_string($csv)) return null;
		$parts = explode(',', $csv);
		$first = $parts[0] ?? null;
		$first = is_string($first) ? trim($first) : null;
		return $first !== '' ? $first : null;
	}

	private function safeLogAttrs(array $attrs): array
	{
		$out = $attrs;

		if (isset($out['metadata']) && is_array($out['metadata'])) {
			$out['metadata'] = ['_keys' => array_keys($out['metadata'])];
		}

		return $out;
	}
}
