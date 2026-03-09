<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BanksConstants as BKC,
	BillsConstants as BC,
	DatabaseConstants as DC
};
use App\Enums\BillReferenceType;
use App\Models\BillAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Symfony\Component\Console\Output\ConsoleOutput;

class BillAccountSeeder extends Seeder
{
	private ConsoleOutput $out;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_COAS) || !Schema::hasTable(DC::TABLE_BILLS) || !Schema::hasTable(DC::TABLE_BL_ACC)) {
			$this->out->writeln('<comment>BillAccountSeeder skipped: required tables not found.</comment>');
			return;
		}

		$coaCount = (int) DB::table(DC::TABLE_COAS)->count();
		$billCount = (int) DB::table(DC::TABLE_BILLS)->count();

		if ($coaCount <= 0 || $billCount <= 0) {
			$this->out->writeln('<comment>BillAccountSeeder skipped: missing COAs or Bills.</comment>');
			$this->out->writeln("COAs={$coaCount}, Bills={$billCount}");
			return;
		}

		$rawTotal = (int) floor($coaCount * 0.25);
		$rawTotal = min($rawTotal, 1600);
		$total = $this->toNearest64MultipleWithinCap($rawTotal, min(1600, $coaCount));

		if ($total <= 0) {
			$this->out->writeln('<comment>BillAccountSeeder skipped: computed total is 0.</comment>');
			return;
		}

		$this->out->writeln("<info>BillAccountSeeder</info> COAs={$coaCount}, Bills={$billCount}, raw={$rawTotal}, final={$total}");

		$coaIds = DB::table(DC::TABLE_COAS)
			->select('id')
			->orderBy('id')
			->limit($total)
			->pluck('id')
			->all();

		if (count($coaIds) <= 0) {
			$this->out->writeln('<comment>BillAccountSeeder skipped: no COA ids fetched.</comment>');
			return;
		}

		$billIds = DB::table(DC::TABLE_BILLS)
			->select('id')
			->orderBy('id')
			->limit(max(256, min(4096, $billCount)))
			->pluck('id')
			->all();

		if (count($billIds) <= 0) {
			$this->out->writeln('<comment>BillAccountSeeder skipped: no Bill ids fetched.</comment>');
			return;
		}

		$billAmounts = $this->prefetchBillAmounts($billIds);

		$types = array_values(array_map(fn(BillReferenceType $e) => $e->value, BillReferenceType::cases()));
		$typesCount = count($types);

		$seen = []; // in-memory uniqueness guard: "{$coa}|{$bill}|{$type}" => true
		$created = 0;

		foreach ($coaIds as $i => $coaId) {
			if ($created >= 1600) break;

			$attempts = 0;
			$attemptLimit = 40;

			$chosenBillId = null;
			$chosenType = null;

			do {
				$attempts++;

				$chosenBillId = $billIds[($i + $attempts) % count($billIds)] ?? null;
				$chosenType = $this->pickType($types, $i, $typesCount);

				$key = (string) $coaId . '|' . (string) $chosenBillId . '|' . (string) $chosenType;
				if (isset($seen[$key])) continue;

				if ($this->comboExistsInDb((string) $coaId, (string) $chosenBillId, (string) $chosenType)) continue;

				$seen[$key] = true;
				break;
			} while ($attempts < $attemptLimit);

			if ($attempts >= $attemptLimit || !$chosenBillId || !$chosenType) {
				$this->out->writeln("<comment>Skip COA={$coaId}: exhausted attempts ({$attempts}).</comment>");
				continue;
			}

			$price = $this->derivePriceFromBill($chosenBillId, $billAmounts);

			$description = $this->maybeNull(
				"Account entry for bill {$this->short($chosenBillId)} on COA {$this->short((string)$coaId)}"
			);

			$notes = $this->maybeNull(
				$created % 3 === 0 ? 'Seeded mock bill-account linkage.' : null
			);

			$attachments = $this->maybeAttachments($created);
			$metadata = $this->maybeMetadata($chosenType, $created);

			$m = new BillAccount();

			$m->setAttribute(BKC::COL_COA, $coaId);
			$m->setAttribute(BC::COL_REF_ID, $chosenBillId);
			$m->setAttribute('type', $chosenType);
			$m->setAttribute('price', $price);
			$m->setAttribute('description', $description);
			$m->setAttribute('notes', $notes);

			$this->setJsonish($m, 'attachments', $attachments);
			$this->setJsonish($m, 'metadata', $metadata);

			$this->out->writeln(
				"<info>Creating BillAccount</info> coa={$this->short((string)$coaId)} bill={$this->short((string)$chosenBillId)} type={$chosenType} price={$price}"
			);

			try {
				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::error('BillAccountSeeder failed creating BillAccount', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'table' => DC::TABLE_BL_ACC,
					'coa_id' => $coaId,
					'ref_id' => $chosenBillId,
					'type' => $chosenType,
				]);
			}
		}

		$this->out->writeln("<info>BillAccountSeeder done</info> created={$created}");
	}

	private function toNearest64MultipleWithinCap(int $raw, int $cap): int
	{
		if ($raw <= 0) return 0;

		$raw = min($raw, $cap);

		$rem = $raw % 64;
		if ($rem === 0) return $raw;

		$up = $raw + (64 - $rem);
		if ($up <= $cap) return $up;

		$down = $raw - $rem;
		return $down > 0 ? $down : 0;
	}

	private function prefetchBillAmounts(array $billIds): array
	{
		if (!Schema::hasColumn(DC::TABLE_BILLS, 'amount'))
			return [];

		try {
			$rows = DB::table(DC::TABLE_BILLS)
				->select('id', 'amount')
				->whereIn('id', array_values($billIds))
				->get();

			$out = [];
			foreach ($rows as $r) {
				$id = is_string($r->id ?? null) ? (string) $r->id : null;
				if (!$id) continue;
				$out[$id] = $r->amount ?? null;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning('BillAccountSeeder failed prefetching bill amounts', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function derivePriceFromBill(string $billId, array $billAmounts): string
	{
		$raw = $billAmounts[$billId] ?? null;

		if (is_numeric($raw)) {
			$v = (float) $raw;
			if ($v < 0) $v = abs($v);
			return number_format($v, 2, '.', '');
		}

		// fallback: deterministic-ish variation with a bounded range
		$base = ((crc32($billId) % 500000) / 100.0) + 10.00; // 10.00 .. 5010.00
		return number_format($base, 2, '.', '');
	}

	private function pickType(array $types, int $i, int $typesCount): string
	{
		if ($typesCount <= 0) return 'bill';

		// Guarantee at least one occurrence per enum value when possible.
		if ($i < $typesCount) return $types[$i];

		// Then keep rotating deterministically.
		return $types[$i % $typesCount];
	}

	private function comboExistsInDb(string $coaId, string $billId, string $type): bool
	{
		try {
			return DB::table(DC::TABLE_BL_ACC)
				->where(BKC::COL_COA, $coaId)
				->where(BC::COL_REF_ID, $billId)
				->where('type', $type)
				->exists();
		} catch (\Throwable $e) {
			Log::warning('BillAccountSeeder failed checking combo existence', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'coa_id' => $coaId,
				'ref_id' => $billId,
				'type' => $type,
			]);
			return false;
		}
	}

	private function maybeNull(?string $s): ?string
	{
		$s = is_string($s) ? trim($s) : '';
		if ($s === '') return null;

		// keep nullability variation without filtering nulls out of the payload later
		return (crc32($s) % 5 === 0) ? null : $s;
	}

	private function maybeAttachments(int $i): ?array
	{
		if ($i % 4 !== 0) return null;

		return [
			[
				'name' => 'seed-receipt.pdf',
				'path' => 'mock/attachments/seed-receipt.pdf',
				'size' => 123456,
			],
		];
	}

	private function maybeMetadata(string $type, int $i): ?array
	{
		if ($i % 2 !== 0) return null;

		return [
			'seed' => true,
			'kind' => $type,
			'version' => 1,
		];
	}

	private function setJsonish(BillAccount $m, string $key, mixed $value): void
	{
		// nullable JSON columns: keep nulls as nulls (no null-filtering)
		if ($value === null) {
			$m->setAttribute($key, null);
			return;
		}

		// Prefer model casts; otherwise store JSON string safely.
		try {
			if (method_exists($m, 'hasCast') && $m->hasCast($key))
				$m->setAttribute($key, $value);
			else
				$m->setAttribute($key, json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
		} catch (\Throwable) {
			$m->setAttribute($key, json_encode((array) $value, JSON_UNESCAPED_UNICODE));
		}
	}

	private function short(string $uuid): string
	{
		$u = trim($uuid);
		return strlen($u) > 8 ? substr($u, 0, 8) : $u;
	}
}
