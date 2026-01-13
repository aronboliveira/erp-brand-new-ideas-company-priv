<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Models\ContractNotes;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ContractNoteSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const HARD_CAP = 16000;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$faker = FakerFactory::create(config('app.faker_locale', 'en_US'));

		$contractIds = $this->fetchIds(DC::TABLE_CONTRACTS, null);
		if (!$contractIds) {
			$this->out->writeln('ContractNotesSeeder: no contracts found.');
			return;
		}

		shuffle($contractIds);

		$totalContracts = count($contractIds);
		$targetContracts = max(1, (int) floor($totalContracts * 0.5));
		$selectedContractIds = array_slice($contractIds, 0, $targetContracts);

		$userIds = $this->fetchIds(DC::TABLE_USERS, 2048);

		$docIds = $this->fetchIds(DC::TABLE_DOCS, 1024);
		$attachmentIds = $this->fetchIds(DC::TABLE_CTC_ATC, 1024);
		$externalNoteIds = $this->fetchIds(DC::TABLE_NOTES, 1024);

		$minContractsWithNotes = (int) ceil($totalContracts * 0.2);
		$minContractsWithNotes = min($minContractsWithNotes, self::HARD_CAP, count($selectedContractIds));
		$minContractsWithNotes = max(1, $minContractsWithNotes);

		$forcedIds = array_slice($selectedContractIds, 0, $minContractsWithNotes);
		$restIds = array_slice($selectedContractIds, $minContractsWithNotes);
		$orderedContractIds = array_merge($forcedIds, $restIds);
		$forcedSet = array_fill_keys($forcedIds, true);

		$variants = ['plain', 'uuid', 'safe_url', 'att_path', 'mixed_list'];
		$variantIdx = 0;

		$created = 0;
		$contractsWithNotes = [];

		foreach ($orderedContractIds as $contractId) {
			if ($created >= self::HARD_CAP) break;

			$mustHave = isset($forcedSet[$contractId]);
			$noteCount = $this->pickNoteCount($mustHave);

			if ($mustHave && $noteCount < 1)
				$noteCount = 1;

			if ($noteCount === 0)
				continue;

			for ($i = 0; $i < $noteCount; $i++) {
				if ($created >= self::HARD_CAP) break;

				$variant = $variants[$variantIdx % count($variants)];
				$variantIdx++;

				$userId = $userIds ? $userIds[random_int(0, count($userIds) - 1)] : null;

				$code = $faker->boolean(70) ? $this->uniqueCodeOrNull('CTC-NTS-', DC::TABLE_CTC_NTS, 'code', 24) : null;

				$notes = $this->makeNotesValue($faker, $variant, $docIds, $attachmentIds, $externalNoteIds);
				$notes = $notes === null ? null : Str::limit($notes, 250, '');

				$this->out->writeln("ContractNotesSeeder: contract={$contractId} user=" . ($userId ?? 'NULL') . " variant={$variant} code=" . ($code ?? 'NULL'));

				try {
					$m = new ContractNotes();
					$m->setAttribute(PJC::COL_CTC_ID, $contractId);
					$m->setAttribute(UC::COL_USER_ID, $userId);
					$m->setAttribute('code', $code);
					$m->setAttribute('notes', $notes);

					$creator = $faker->boolean(65) ? $userId : ($userIds ? $userIds[random_int(0, count($userIds) - 1)] : null);
					$updater = $faker->boolean(25) ? $creator : null;

					$m->setAttribute(DC::COL_TABLE_CREATOR, $creator);
					$m->setAttribute(DC::COL_TABLE_UPDATER, $updater);

					$m->save();

					$created++;
					$contractsWithNotes[$contractId] = true;
				} catch (\Throwable $e) {
					Log::error(static::class . ' failed creating ContractNotes', [
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'error' => $e->getMessage(),
						'contract_id' => $contractId,
						'user_id' => $userId,
						'code' => $code,
					]);
				}
			}
		}

		if ($created > 0 && ($created % 64) !== 0) {
			$missing = 64 - ($created % 64);
			$missing = min($missing, self::HARD_CAP - $created);

			for ($i = 0; $i < $missing; $i++) {
				if ($created >= self::HARD_CAP) break;

				$contractId = $orderedContractIds ? $orderedContractIds[random_int(0, count($orderedContractIds) - 1)] : null;
				if (!$contractId) break;

				$userId = $userIds ? $userIds[random_int(0, count($userIds) - 1)] : null;
				$variant = $variants[$variantIdx % count($variants)];
				$variantIdx++;

				$code = $this->uniqueCodeOrNull('CTC-NTS-', DC::TABLE_CTC_NTS, 'code', 24);
				$notes = $this->makeNotesValue($faker, $variant, $docIds, $attachmentIds, $externalNoteIds);
				$notes = $notes === null ? null : Str::limit($notes, 250, '');

				$this->out->writeln("ContractNotesSeeder: [pad] contract={$contractId} user=" . ($userId ?? 'NULL') . " variant={$variant} code=" . ($code ?? 'NULL'));

				try {
					$m = new ContractNotes();
					$m->setAttribute(PJC::COL_CTC_ID, $contractId);
					$m->setAttribute(UC::COL_USER_ID, $userId);
					$m->setAttribute('code', $code);
					$m->setAttribute('notes', $notes);

					$creator = $faker->boolean(65) ? $userId : ($userIds ? $userIds[random_int(0, count($userIds) - 1)] : null);
					$updater = $faker->boolean(25) ? $creator : null;

					$m->setAttribute(DC::COL_TABLE_CREATOR, $creator);
					$m->setAttribute(DC::COL_TABLE_UPDATER, $updater);

					$m->save();

					$created++;
					$contractsWithNotes[$contractId] = true;
				} catch (\Throwable $e) {
					Log::error(static::class . ' failed padding ContractNotes', [
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'error' => $e->getMessage(),
						'contract_id' => $contractId,
						'user_id' => $userId,
						'code' => $code,
					]);
				}
			}
		}

		$forcedOk = count(array_intersect_key(array_fill_keys($forcedIds, true), $contractsWithNotes)) >= min(count($forcedIds), self::HARD_CAP);
		$this->out->writeln('ContractNotesSeeder: created=' . $created . ' contracts_with_notes=' . count($contractsWithNotes) . ' forced_ok=' . ($forcedOk ? 'yes' : 'no'));
	}

	private function pickNoteCount(bool $mustHave): int
	{
		$pool = $mustHave
			? [1, 1, 1, 1, 2, 2, 2, 3, 3, 4, 5, 6, 7, 8]
			: [0, 0, 0, 0, 1, 1, 1, 2, 2, 3, 4, 5, 6, 7, 8];

		return $pool[random_int(0, count($pool) - 1)];
	}

	private function fetchIds(string $table, ?int $limit = 2048): array
	{
		try {
			$sql = "select id from {$table}";
			if ($limit !== null && $limit > 0) $sql .= " limit {$limit}";
			$rows = DB::select($sql);
			$out = [];
			foreach ($rows as $r) {
				$id = is_object($r) ? ($r->id ?? null) : ($r['id'] ?? null);
				if (!is_string($id) || trim($id) === '') continue;
				$out[] = $id;
			}
			return array_values(array_unique($out));
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

	private function uniqueCodeOrNull(string $prefix, string $table, string $column, int $attemptLimit = 24): ?string
	{
		$attempts = 0;

		do {
			$attempts++;
			$code = $prefix . strtoupper((string) Str::uuid());

			try {
				$exists = DB::selectOne("select 1 as x from {$table} where {$column} = ? limit 1", [$code]);
				if (!$exists) return $code;
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed checking code uniqueness', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'table' => $table,
					'column' => $column,
					'code' => $code,
				]);
				return $code;
			}
		} while ($attempts < $attemptLimit);

		return null;
	}

	private function makeNotesValue($faker, string $variant, array $docIds, array $attachmentIds, array $externalNoteIds): ?string
	{
		$uuid = function (): string {
			return (string) Str::uuid();
		};

		$attHash = function (): string {
			$hex = bin2hex(random_bytes(24));
			return 'att://' . $hex;
		};

		$safeUrl = function () use ($faker): string {
			$hosts = [
				'drive.google.com',
				'storage.googleapis.com',
				'dropbox.com',
				'dl.dropboxusercontent.com',
				's3.amazonaws.com',
				'cloudfront.net',
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

		$pickExistingUuid = function () use ($docIds, $attachmentIds, $externalNoteIds, $uuid): string {
			$pools = [
				$docIds,
				$attachmentIds,
				$externalNoteIds,
			];
			$pool = $pools[random_int(0, count($pools) - 1)];
			return $pool ? $pool[random_int(0, count($pool) - 1)] : $uuid();
		};

		$plain = Str::limit(trim($faker->sentence(random_int(8, 14))), 220, '');

		if ($variant === 'plain')
			return $plain;

		if ($variant === 'uuid')
			return $pickExistingUuid();

		if ($variant === 'safe_url')
			return $safeUrl();

		if ($variant === 'att_path')
			return $attHash();

		if ($variant === 'mixed_list') {
			$items = [
				$plain,
				$pickExistingUuid(),
				$safeUrl(),
				$attHash(),
			];
			shuffle($items);
			$take = random_int(2, 4);
			return implode(', ', array_slice($items, 0, $take));
		}

		return $plain;
	}
}
