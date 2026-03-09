<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Models\ContractNotes;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class ContractNoteSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const HARD_CAP = 2056;
	private const MIN_ROWS = 128;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$tz = 'America/Sao_Paulo';
		$faker = FakerFactory::create((string) config('app.faker_locale', 'en_US'));

		if (!$this->hasTableSafe(DC::TABLE_CONTRACTS) || !$this->hasTableSafe(DC::TABLE_CTC_NTS)) {
			$this->out->writeln('<comment>[ContractNoteSeeder]</comment> Missing required tables. Skipping.');
			return;
		}

		$totalContracts = (int) DB::table(DC::TABLE_CONTRACTS)->count();
		if ($totalContracts <= 0) {
			$this->out->writeln('<comment>[ContractNoteSeeder]</comment> No contracts found. Skipping.');
			return;
		}

		$requiredCoverage = (int) ceil($totalContracts * 0.5);
		$coverageContracts = min($requiredCoverage, self::HARD_CAP);

		$targetRows = max(self::MIN_ROWS, $coverageContracts);
		$targetRows = min($targetRows, self::HARD_CAP);

		if ($coverageContracts < $requiredCoverage) {
			$this->out->writeln(sprintf(
				'<comment>[ContractNoteSeeder]</comment> Coverage constraint not fully satisfiable: total_contracts=%d required_coverage=%d cap_distinct=%d (hard_cap=%d)',
				$totalContracts,
				$requiredCoverage,
				$coverageContracts,
				self::HARD_CAP
			));
		}

		$contractIds = $this->fetchContractIds($coverageContracts);
		if (!$contractIds) {
			$this->out->writeln('<comment>[ContractNoteSeeder]</comment> Failed fetching contract ids. Skipping.');
			return;
		}

		shuffle($contractIds);

		$userIds = $this->fetchIdsSafe(DC::TABLE_USERS, 4096);

		$docIds = $this->hasTableSafe(DC::TABLE_DOCS) ? $this->fetchIdsSafe(DC::TABLE_DOCS, 2048) : [];
		$attachmentIds = $this->hasTableSafe(DC::TABLE_CTC_ATC) ? $this->fetchIdsSafe(DC::TABLE_CTC_ATC, 2048) : [];
		$externalNoteIds = $this->hasTableSafe(DC::TABLE_NOTES) ? $this->fetchIdsSafe(DC::TABLE_NOTES, 2048) : [];

		$countsByContract = $this->planCountsByContract($contractIds, $coverageContracts, $targetRows);

		$variants = ['plain', 'uuid', 'safe_url', 'att_path', 'mixed_list'];
		$variantIdx = 0;

		$now = now($tz);
		$rows = [];
		$created = 0;
		$contractsWithNotes = [];

		DB::transaction(function () use (
			$faker,
			$userIds,
			$docIds,
			$attachmentIds,
			$externalNoteIds,
			$variants,
			&$variantIdx,
			$now,
			$countsByContract,
			&$rows,
			&$created,
			&$contractsWithNotes
		): void {
			foreach ($countsByContract as $contractId => $qty) {
				if ($created >= self::HARD_CAP) break;

				$qty = (int) $qty;
				if ($qty <= 0) continue;

				for ($i = 0; $i < $qty; $i++) {
					if ($created >= self::HARD_CAP) break 2;

					$variant = $variants[$variantIdx % count($variants)];
					$variantIdx++;

					$userId = $this->pickUserId($userIds, 70);

					$code = random_int(0, 100) < 60
						? ('CTC-NTS-' . strtoupper((string) Str::uuid()))
						: null;

					$notes = $this->makeNotesValue($faker, $variant, $docIds, $attachmentIds, $externalNoteIds);
					$notes = is_string($notes) ? Str::limit(trim($notes), 240, '') : null;

					$creator = $this->pickCreator($userId, $userIds);
					$updater = (random_int(0, 100) < 20) ? $creator : null;

					$rows[] = [
						'id' => (string) Str::uuid(),
						'code' => $code,
						PJC::COL_CTC_ID => $contractId,
						UC::COL_USER_ID => $userId,
						'notes' => $notes,
						DC::COL_TABLE_CREATOR => $creator,
						DC::COL_TABLE_UPDATER => $updater,
						'created_at' => $now,
						'updated_at' => $now,
					];

					$created++;
					$contractsWithNotes[$contractId] = true;

					if (count($rows) >= 800) {
						DB::table(DC::TABLE_CTC_NTS)->insert($rows);
						$rows = [];
					}
				}
			}

			if ($rows) DB::table(DC::TABLE_CTC_NTS)->insert($rows);
		}, 3);

		$uniqueContractsWithNotes = count($contractsWithNotes);
		$this->out->writeln(sprintf(
			'<info>[ContractNoteSeeder]</info> created=%d unique_contracts_with_notes=%d required_coverage=%d total_contracts=%d target_rows=%d',
			$created,
			$uniqueContractsWithNotes,
			(int) ceil($totalContracts * 0.5),
			$totalContracts,
			$targetRows
		));
	}

	private function fetchContractIds(int $limit): array
	{
		try {
			$limit = max(1, $limit);
			return DB::table(DC::TABLE_CONTRACTS)
				->select('id')
				->orderBy('id')
				->limit($limit)
				->pluck('id')
				->map(fn($v) => is_string($v) ? trim($v) : '')
				->filter(fn($v) => $v !== '')
				->values()
				->all();
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed fetching contract ids', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function planCountsByContract(array $contractIds, int $mustCover, int $targetRows): array
	{
		$contractIds = array_values(array_unique(array_filter($contractIds, fn($v) => is_string($v) && trim($v) !== '')));
		$mustCover = min($mustCover, count($contractIds));

		$counts = [];
		for ($i = 0; $i < $mustCover; $i++) {
			$id = (string) $contractIds[$i];
			$counts[$id] = 1;
		}

		$remaining = $targetRows - $mustCover;
		if ($remaining <= 0) return $counts;

		$maxPerContract = (int) min(64, max(8, (int) ceil($targetRows / max(1, $mustCover)) + 2));
		$keys = array_keys($counts);
		$idx = 0;

		while ($remaining > 0 && $keys) {
			$k = $keys[$idx % count($keys)];
			$cur = (int) ($counts[$k] ?? 0);

			if ($cur < $maxPerContract) {
				$counts[$k] = $cur + 1;
				$remaining--;
			}

			$idx++;

			// Safety break if everything is saturated
			if ($idx > ($mustCover * $maxPerContract * 2)) break;
		}

		// If we still have remaining (e.g., saturated), keep adding to random covered contracts (no per-contract cap)
		$keys = array_keys($counts);
		while ($remaining > 0 && $keys) {
			$k = $keys[random_int(0, count($keys) - 1)];
			$counts[$k] = (int) ($counts[$k] ?? 0) + 1;
			$remaining--;
		}

		return $counts;
	}

	private function fetchIdsSafe(string $table, ?int $limit = 2048): array
	{
		try {
			$q = DB::table($table)->select('id')->orderBy('id');
			if ($limit !== null && $limit > 0) $q->limit($limit);

			return $q->pluck('id')
				->map(fn($v) => is_string($v) ? trim($v) : '')
				->filter(fn($v) => $v !== '')
				->unique()
				->values()
				->all();
		} catch (\Throwable $e) {
			Log::warning(static::class . " failed fetching ids for {$table}", [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
			]);
			return [];
		}
	}

	private function pickUserId(array $userIds, int $chancePct = 70): ?string
	{
		if (!$userIds) return null;
		if (random_int(1, 100) > max(0, min(100, $chancePct))) return null;

		return $userIds[random_int(0, count($userIds) - 1)] ?? null;
	}

	private function pickCreator(?string $userId, array $userIds): ?string
	{
		if ($userId && random_int(0, 100) < 65) return $userId;
		return $userIds ? ($userIds[random_int(0, count($userIds) - 1)] ?? null) : null;
	}

	private function makeNotesValue($faker, string $variant, array $docIds, array $attachmentIds, array $externalNoteIds): ?string
	{
		$uuid = static fn(): string => (string) Str::uuid();

		$attHash = static function (): string {
			$hex = bin2hex(random_bytes(18));
			return 'att://' . $hex;
		};

		$safeUrl = static function () use ($faker): string {
			$hosts = [
				'drive.google.com',
				'storage.googleapis.com',
				'dropbox.com',
				'dl.dropboxusercontent.com',
				's3.amazonaws.com',
				'onedrive.live.com',
				'1drv.ms',
				'box.com',
			];
			$host = $hosts[random_int(0, count($hosts) - 1)];
			$path = $host === 'drive.google.com'
				? '/file/d/' . Str::random(22) . '/view'
				: '/' . Str::random(12) . '/' . Str::random(10) . '.pdf';
			return 'https://' . $host . $path;
		};

		$pickExistingUuid = static function () use ($docIds, $attachmentIds, $externalNoteIds, $uuid): string {
			$pools = [
				$docIds,
				$attachmentIds,
				$externalNoteIds,
			];
			$pool = $pools[random_int(0, count($pools) - 1)] ?? [];
			return $pool ? ($pool[random_int(0, count($pool) - 1)] ?? $uuid()) : $uuid();
		};

		$plain = Str::limit(trim($faker->sentence(random_int(10, 18))), 220, '');

		return match ($variant) {
			'plain' => $plain,
			'uuid' => $pickExistingUuid(),
			'safe_url' => $safeUrl(),
			'att_path' => $attHash(),
			'mixed_list' => (function () use ($plain, $pickExistingUuid, $safeUrl, $attHash): string {
				$items = [$plain, $pickExistingUuid(), $safeUrl(), $attHash()];
				shuffle($items);
				return implode(', ', array_slice($items, 0, random_int(2, 4)));
			})(),
			default => $plain,
		};
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
}
