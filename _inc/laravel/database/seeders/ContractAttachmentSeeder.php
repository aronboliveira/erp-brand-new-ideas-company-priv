<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{AttachmentModuleType, EvaluationStatus, MimeType};
use App\Models\ContractAttachment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ContractAttachmentSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const HARD_CAP = 32000;
	private const MAX_PER_CONTRACT = 8;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		$contracts = $this->fetchContracts();
		if (!$contracts) {
			$this->out->writeln('<comment>[ContractAttachmentSeeder]</comment> No contracts found. Skipping.');
			return;
		}

		$userIds = $this->fetchUserIds();
		$docIds  = $this->fetchDocIds();

		$types = AttachmentModuleType::cases();

		$contractsCol = collect($contracts)->shuffle()->values();
		$take = max(1, (int) floor($contractsCol->count() * 0.5));
		$selected = $contractsCol->take($take)->values();

		$plan = $this->planCounts($selected);
		$rawTotal = $plan['raw_total'];
		$countsByContract = $plan['counts'];

		$maxPossible = $selected->count() * self::MAX_PER_CONTRACT;
		$target = $this->computeTargetTotal($rawTotal, $maxPossible);

		if ($target <= 0) {
			$this->out->writeln('<comment>[ContractAttachmentSeeder]</comment> Target total is 0. Skipping.');
			return;
		}

		$countsByContract = $this->adjustCountsToTarget($countsByContract, $target, self::MAX_PER_CONTRACT);

		$hasStatusCol = $this->hasColumnSafe(DC::TABLE_CTC_ATC, 'status');
		$hasMetadataCol = $this->hasColumnSafe(DC::TABLE_CTC_ATC, 'metadata');

		$created = 0;
		$attempts = 0;
		$attemptLimit = max(2000, $target * 4);

		foreach ($selected as $cRow) {
			$contractId = (string) ($cRow->id ?? '');
			if ($contractId === '') continue;

			$qty = (int) ($countsByContract[$contractId] ?? 0);
			if ($qty <= 0) continue;

			for ($k = 0; $k < $qty; $k++) {
				if ($created >= $target) break 2;

				$attempts++;
				if ($attempts > $attemptLimit) {
					$this->out->writeln('<comment>[ContractAttachmentSeeder]</comment> Attempt limit reached. Breaking early.');
					break 2;
				}

				$created++;
				$variationIndex = $created - 1;

				$attrs = $this->buildAttachmentAttrs(
					$variationIndex,
					$cRow,
					$userIds,
					$docIds,
					$types,
					$hasStatusCol,
					$hasMetadataCol
				);

				$this->out->writeln(sprintf(
					'<info>[CTC_ATC]</info> %d/%d contract=%s type=%s apv=%s rej=%s main=%s',
					$created,
					$target,
					$contractId,
					(string) ($attrs[PJC::COL_ATC_TP] ?? 'n/a'),
					empty($attrs[PJC::COL_APV_BY] ?? null) ? '0' : '1',
					empty($attrs[PJC::COL_REJ_BY] ?? null) ? '0' : '1',
					(string) ($this->firstFileTokenFromCsv($attrs['files'] ?? null) ?? 'none')
				));

				try {
					ContractAttachment::create($attrs);
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

		$this->out->writeln("<comment>[ContractAttachmentSeeder]</comment> Done. Created {$created} rows.");
	}

	private function fetchContracts(): array
	{
		try {
			$sql = 'SELECT id, status, '
				. PJC::COL_APV_BY . ' AS apv_by, ' . PJC::COL_APV_AT . ' AS apv_at, '
				. PJC::COL_REJ_BY . ' AS rej_by, ' . PJC::COL_REJ_AT . ' AS rej_at '
				. 'FROM ' . DC::TABLE_CONTRACTS;

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

	private function planCounts($selected): array
	{
		$counts = [];
		$rawTotal = 0;

		foreach ($selected as $cRow) {
			$id = (string) ($cRow->id ?? '');
			if ($id === '') continue;

			$n = random_int(0, self::MAX_PER_CONTRACT);
			$counts[$id] = $n;
			$rawTotal += $n;
		}

		$maxPossible = count($counts) * self::MAX_PER_CONTRACT;

		if ($rawTotal === 0 && $maxPossible > 0) {
			$need = min(64, $maxPossible);
			$keys = array_values(array_keys($counts));
			$i = 0;
			$attempts = 0;
			$attemptLimit = max(256, $need * 8);

			while ($need > 0 && $attempts < $attemptLimit) {
				$attempts++;
				$key = $keys[$i % max(1, count($keys))] ?? null;
				if (!$key) break;

				if (($counts[$key] ?? 0) < self::MAX_PER_CONTRACT) {
					$counts[$key] = (int) ($counts[$key] ?? 0) + 1;
					$rawTotal++;
					$need--;
				}

				$i++;
			}
		}

		return ['raw_total' => $rawTotal, 'counts' => $counts];
	}

	private function computeTargetTotal(int $rawTotal, int $maxPossible): int
	{
		$cap = self::HARD_CAP;
		$cap = $cap - ($cap % 64);

		$maxPossible64 = $this->roundDownTo64($maxPossible);
		if ($maxPossible64 <= 0) return 0;

		$raw = min($rawTotal, $cap);
		$target = $this->roundUpTo64($raw);

		if ($target > $cap) $target = $cap;
		if ($target > $maxPossible64) $target = $maxPossible64;

		return $target;
	}

	private function adjustCountsToTarget(array $counts, int $target, int $maxPer): array
	{
		$current = array_sum($counts);
		$keys = array_values(array_keys($counts));

		$attempts = 0;
		$attemptLimit = max(2048, $target * 8);

		while ($current < $target && $attempts < $attemptLimit) {
			$attempts++;

			$advanced = false;
			foreach ($keys as $k) {
				if ($current >= $target) break;

				$v = (int) ($counts[$k] ?? 0);
				if ($v < $maxPer) {
					$counts[$k] = $v + 1;
					$current++;
					$advanced = true;
				}
			}

			if (!$advanced) break;
		}

		$attempts = 0;
		$attemptLimit = max(2048, $target * 8);

		while ($current > $target && $attempts < $attemptLimit) {
			$attempts++;

			$advanced = false;
			foreach ($keys as $k) {
				if ($current <= $target) break;

				$v = (int) ($counts[$k] ?? 0);
				if ($v > 0) {
					$counts[$k] = $v - 1;
					$current--;
					$advanced = true;
				}
			}

			if (!$advanced) break;
		}

		return $counts;
	}

	private function buildAttachmentAttrs(
		int $i,
		object $contractRow,
		array $userIds,
		array $docIds,
		array $types,
		bool $hasStatusCol,
		bool $hasMetadataCol
	): array {
		$contractId = (string) ($contractRow->id ?? '');
		$contractStatus = (string) ($contractRow->status ?? '');

		$type = $types[$i % max(1, count($types))] ?? AttachmentModuleType::Other;

		$submitter = $this->pickUserId($userIds);
		$approver  = $this->pickUserId($userIds);
		$rejecter  = $this->pickUserId($userIds);

		$now = now();
		$submittedAt = random_int(0, 100) < 70 ? $now->copy()->subDays(random_int(0, 60))->subMinutes(random_int(0, 1440)) : null;

		$scenario = $i % 6;

		$apvBy = null;
		$apvAt = null;
		$rejBy = null;
		$rejAt = null;

		if ($scenario === 0) {
			// pending-like: none set
		} elseif ($scenario === 1) {
			$apvBy = $approver;
			$apvAt = $submittedAt ? $now->copy()->subMinutes(random_int(1, 600)) : $now->copy()->subMinutes(random_int(1, 1440));
		} elseif ($scenario === 2) {
			$rejBy = $rejecter;
			$rejAt = $submittedAt ? $now->copy()->subMinutes(random_int(1, 600)) : $now->copy()->subMinutes(random_int(1, 1440));
		} elseif ($scenario === 3) {
			$apvBy = $approver;
			$apvAt = $now->copy()->subMinutes(120);
			$rejBy = $rejecter;
			$rejAt = $now->copy()->subMinutes(30); // rej later => should win
		} elseif ($scenario === 4) {
			$rejBy = $rejecter;
			$rejAt = $now->copy()->subMinutes(120);
			$apvBy = $approver;
			$apvAt = $now->copy()->subMinutes(30); // apv later => should win
		} else {
			$ts = $now->copy()->subMinutes(45);
			$apvBy = $approver;
			$apvAt = $ts; // same timestamp => rej wins by rule
			$rejBy = $rejecter;
			$rejAt = $ts;
		}

		$isContractAcceptLike = in_array(EvaluationStatus::normalize($contractStatus)->value, [
			EvaluationStatus::Accept->value,
			EvaluationStatus::Active->value,
		], true);

		$isContractRejectLike = in_array(EvaluationStatus::normalize($contractStatus)->value, [
			EvaluationStatus::Suspended->value,
			EvaluationStatus::Cancelled->value,
			EvaluationStatus::Expired->value,
			EvaluationStatus::Decline->value,
		], true);

		if ($isContractAcceptLike && random_int(0, 100) < 35) {
			$apvBy = null;
			$apvAt = null;
		}

		if ($isContractRejectLike && random_int(0, 100) < 35) {
			$rejBy = null;
			$rejAt = null;
		}

		$filePack = $this->buildFilePack($i, $docIds, $userIds);

		$attrs = [
			'code' => $this->uniqueCodeOrNull(30),
			PJC::COL_CTC_ID => $contractId,
			UC::COL_USER_ID => $submitter,
			PJC::COL_SBM_AT => $submittedAt,
			PJC::COL_APV_BY => $apvBy,
			PJC::COL_APV_AT => $apvAt,
			PJC::COL_REJ_BY => $rejBy,
			PJC::COL_REJ_AT => $rejAt,

			DC::COL_FL_PT => $filePack['file_path'],
			'url' => $filePack['url'],
			'name' => $filePack['name'],
			'extension' => $filePack['extension'],
			DC::COL_MM_TP => $filePack['mime_type'],
			DC::COL_LA => $filePack['last_access'],
			'size' => $filePack['size'],
			'description' => $filePack['description'],
			'notes' => $filePack['notes'],
			DC::COL_DL_CT => $filePack['download_count'],
			DC::COL_FL_SZ => $filePack['file_size_bytes'],
			DC::COL_PERM_RLS => $filePack['perm_release'],
			'executors' => $filePack['executors'],
			'editors' => $filePack['editors'],
			'viewers' => $filePack['viewers'],
			DC::COL_EXP_DT => $filePack['expires_at'],
			'type' => $filePack['doc_kind'],

			PJC::COL_ATC_TP => $type->value,
			'files' => $filePack['files_csv'],
		];

		if ($hasStatusCol) {
			$attrs['status'] = match ($scenario) {
				0 => EvaluationStatus::Pending->value,
				1 => EvaluationStatus::Accept->value,
				2 => EvaluationStatus::Suspended->value,
				default => EvaluationStatus::Pending->value,
			};
		}

		if ($hasMetadataCol) {
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

	private function buildFilePack(int $i, array $docIds, array $userIds): array
	{
		$appUrl = (string) config('app.url');
		$now = now();

		$mode = $i % 10;

		$url = null;
		$filePath = null;
		$docId = null;

		if ($mode <= 2 && $docIds) {
			$docId = $docIds[random_int(0, max(0, count($docIds) - 1))] ?? null;
		} elseif ($mode <= 5 && $appUrl !== '') {
			$uuid = (string) Str::uuid();
			$url = rtrim($appUrl, '/') . '/storage/mock/contracts/ctc_atc_' . $uuid . '.pdf';
		} else {
			$filePath = 'contracts/mock/ctc_atc_' . (string) Str::uuid() . '.pdf';
		}

		$main = $url ?: ($docId ?: $filePath);

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

		$tokens = collect([$main, ...$extras])
			->filter(fn($v) => is_string($v) && trim($v) !== '')
			->values()
			->all();

		$filesCsv = $tokens ? implode(',', $tokens) : null;

		$name = 'FILE_' . strtoupper(Str::random(6)) . '_' . $now->format('Ymd_His') . '_' . $i;
		$extension = $this->inferExtension($url, $filePath) ?? 'pdf';

		$mime = $this->inferMimeTypeFromExtension($extension);

		return [
			'file_path' => $filePath,
			'url' => $url,
			'name' => random_int(0, 100) < 20 ? null : $name,
			'extension' => $extension,
			'mime_type' => $mime,
			'last_access' => random_int(0, 100) < 40 ? $now->copy()->subDays(random_int(0, 30)) : null,
			'size' => random_int(0, 100) < 70 ? random_int(512, 15_000_000) : null,
			'description' => random_int(0, 100) < 35 ? fake()->sentence(10) : null,
			'notes' => random_int(0, 100) < 25 ? fake()->sentence(14) : null,
			'download_count' => random_int(0, 100) < 60 ? (string) random_int(0, 5000) : null,
			'file_size_bytes' => random_int(0, 100) < 60 ? (string) number_format((float) random_int(0, 20_000_000), 4, '.', '') : null,
			'perm_release' => random_int(0, 100) < 80 ? '776444' : null,
			'executors' => $this->randomUserList($userIds, 4),
			'editors' => $this->randomUserList($userIds, 4),
			'viewers' => $this->randomUserList($userIds, 6),
			'expires_at' => random_int(0, 100) < 10 ? $now->copy()->addDays(random_int(1, 365)) : null,
			'doc_kind' => random_int(0, 100) < 20 ? 'document' : null,
			'files_csv' => $filesCsv,
		];
	}

	private function uniqueCodeOrNull(int $attemptLimit): ?string
	{
		if (random_int(0, 100) < 15) return null;

		$attempts = 0;

		do {
			$attempts++;
			$code = 'CTC-ATC-' . strtoupper((string) Str::uuid());

			if (!$this->existsBySql(DC::TABLE_CTC_ATC, 'code', $code))
				return $code;
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
		$map = [
			'pdf' => 'application/pdf',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'txt' => 'text/plain',
			'csv' => 'text/csv',
			'json' => 'application/json',
		];

		$raw = $map[$ext] ?? null;

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

	private function roundDownTo64(int $n): int
	{
		if ($n <= 0) return 0;
		return $n - ($n % 64);
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
