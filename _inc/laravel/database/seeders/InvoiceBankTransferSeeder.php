<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\PaymentStatus;
use App\Models\InvoiceBankTransfer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\Console\Output\ConsoleOutput;

class InvoiceBankTransferSeeder extends Seeder
{
	private ConsoleOutput $out;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$this->out->writeln('<info>[InvoiceBankTransferSeeder]</info> start');

		try {
			$bankTransfers = $this->fetchBankTransfers();
			$invoices = $this->fetchIds(DC::TABLE_INVS);
			$orders = $this->fetchIds(DC::TABLE_ORDERS);

			if (!$bankTransfers) {
				$this->out->writeln('<comment>No bank transfers found. Skipping.</comment>');
				return;
			}

			if (!$invoices) {
				$this->out->writeln('<comment>No invoices found. Skipping.</comment>');
				return;
			}

			$rawBase = (int) floor(count($bankTransfers) * 0.10);
			if ($rawBase < 1) $rawBase = 1;

			$target = $this->ceilToMultipleOf64($rawBase);

			$invoiceCap = (int) floor(count($invoices) * 0.25);
			if ($invoiceCap < 1) $invoiceCap = 1;

			shuffle($bankTransfers);
			shuffle($invoices);
			shuffle($orders);

			$bankSubset = array_slice($bankTransfers, 0, min($rawBase, count($bankTransfers)));
			$invoicePool = array_slice($invoices, 0, min($invoiceCap, count($invoices)));

			if (!$bankSubset) {
				$this->out->writeln('<comment>Bank subset resolved to empty. Skipping.</comment>');
				return;
			}

			if (!$invoicePool) {
				$this->out->writeln('<comment>Invoice pool resolved to empty. Skipping.</comment>');
				return;
			}

			$this->out->writeln(sprintf(
				'<info>bank_transfers=%d; raw_base=%d; target=%d; invoices=%d; invoice_cap=%d; invoice_pool=%d; orders=%d</info>',
				count($bankTransfers),
				$rawBase,
				$target,
				count($invoices),
				$invoiceCap,
				count($invoicePool),
				count($orders)
			));

			$attemptLimit = 25;

			$created = 0;
			$i = 0;

			while ($created < $target) {
				$bt = $bankSubset[$i % count($bankSubset)];

				$bankTransferId = (string) ($bt['id'] ?? '');
				if ($bankTransferId === '') {
					$i++;
					continue;
				}

				$invoiceId = (string) $invoicePool[$created % count($invoicePool)];
				if ($invoiceId === '') {
					$i++;
					continue;
				}

				$orderId = null;
				if ($orders) {
					$rand = $orders[($created + $i) % count($orders)] ?? null;
					$orderId = is_string($rand) && $rand !== '' ? $rand : null;
				}

				$amount = $this->coerceDecimal2($bt['amount'] ?? null);
				$status = $this->coercePaymentStatus($bt['status'] ?? null);
				$date = $this->coerceDate($bt[BC::COL_PD_AT] ?? null);

				$receipt = null;
				if (($created % 5) === 0) {
					$receipt = 'storage/receipts/inv-bank-transfer/' . $bankTransferId . '.pdf';
				} elseif (($created % 11) === 0) {
					$receipt = rtrim((string) config('app.url'), '/') . '/storage/receipts/' . $bankTransferId . '.pdf';
				}

				$uniqueAttempts = 0;
				do {
					$exists = $this->bridgeExists($bankTransferId, $invoiceId, $orderId);
					if (!$exists) break;

					$uniqueAttempts++;
					$invoiceId = (string) $invoicePool[($created + $uniqueAttempts) % count($invoicePool)];
				} while ($uniqueAttempts < $attemptLimit);

				if ($uniqueAttempts >= $attemptLimit) {
					$this->out->writeln(sprintf(
						'<comment>Skip: uniqueness attempts exceeded for bank_transfer=%s</comment>',
						$bankTransferId
					));
					$i++;
					continue;
				}

				$m = new InvoiceBankTransfer();

				$m->setAttribute(BC::COL_BNK_TRF_ID, $bankTransferId);
				$m->setAttribute(BC::COL_INV_ID, $invoiceId);
				$m->setAttribute(BC::COL_OD_ID, $orderId);

				$m->setAttribute('amount', $amount);
				$m->setAttribute('status', $status);
				$m->setAttribute('date', $date);
				$m->setAttribute('receipt', $receipt);

				// Audit + failure tracking (nullable, but set a deterministic creator/updater for seed data)
				$m->setAttribute(DC::COL_TABLE_CREATOR, DC::DEFAULT_UUID);
				$m->setAttribute(DC::COL_TABLE_UPDATER, DC::DEFAULT_UUID);

				$this->out->writeln(sprintf(
					'<info>Create #%d</info> bank_transfer=%s invoice=%s order=%s amount=%s status=%s date=%s receipt=%s',
					$created + 1,
					$bankTransferId,
					$invoiceId,
					$orderId ?? 'null',
					$amount !== null ? number_format((float) $amount, 2, '.', '') : 'null',
					$status ?? 'null',
					$date ?? 'null',
					$receipt ?? 'null'
				));

				try {
					$m->save();
					$created++;
				} catch (\Throwable $e) {
					Log::error('InvoiceBankTransferSeeder: failed saving model', [
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'error' => $e->getMessage(),
						'bank_transfer_id' => $bankTransferId,
						'invoice_id' => $invoiceId,
						'order_id' => $orderId,
					]);
					$this->out->writeln(sprintf(
						'<error>Failed: %s (%s:%d)</error>',
						$e->getMessage(),
						$e->getFile(),
						$e->getLine()
					));
				}

				$i++;
				if ($i > ($target * 10)) {
					$this->out->writeln('<comment>Break-out: abnormal loop growth detected.</comment>');
					break;
				}
			}

			$this->out->writeln(sprintf('<info>[InvoiceBankTransferSeeder]</info> done; created=%d', $created));
		} catch (\Throwable $e) {
			Log::error('InvoiceBankTransferSeeder: unexpected error', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			$this->out->writeln(sprintf(
				'<error>Unexpected error: %s (%s:%d)</error>',
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			));
		}
	}

	private function fetchIds(string $table): array
	{
		try {
			$rows = DB::select('select id from ' . $table);
			$ids = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $ids[] = $id;
			}
			return $ids;
		} catch (\Throwable $e) {
			Log::error('InvoiceBankTransferSeeder: failed fetching ids', [
				'table' => $table,
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	/**
	 * Reading-only, optimized via raw SQL.
	 * Returns array of ['id' => string, 'amount' => mixed, 'status' => mixed, BC::COL_PD_AT => mixed]
	 */
	private function fetchBankTransfers(): array
	{
		try {
			$paidAt = BC::COL_PD_AT;
			$rows = DB::select(
				'select id, amount, status, ' . $paidAt . ' from ' . DC::TABLE_BNK_TRF . ' where deleted_at is null'
			);

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id === '') continue;

				$out[] = [
					'id' => $id,
					'amount' => $r->amount ?? null,
					'status' => $r->status ?? null,
					$paidAt => $r->{$paidAt} ?? null,
				];
			}
			return $out;
		} catch (\Throwable $e) {
			Log::error('InvoiceBankTransferSeeder: failed fetching bank transfers', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function bridgeExists(string $bankTransferId, string $invoiceId, ?string $orderId): bool
	{
		try {
			$q = DB::table(DC::TABLE_INV_BANK_TRANSFERS)
				->where(BC::COL_BNK_TRF_ID, $bankTransferId)
				->where(BC::COL_INV_ID, $invoiceId);

			if ($orderId === null) $q->whereNull(BC::COL_OD_ID);
			else $q->where(BC::COL_OD_ID, $orderId);

			return $q->exists();
		} catch (\Throwable $e) {
			Log::error('InvoiceBankTransferSeeder: failed checking exists', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'bank_transfer_id' => $bankTransferId,
				'invoice_id' => $invoiceId,
				'order_id' => $orderId,
			]);
			return false;
		}
	}

	private function ceilToMultipleOf64(int $n): int
	{
		if ($n <= 0) return 64;
		$mod = $n % 64;
		return $mod === 0 ? $n : ($n + (64 - $mod));
	}

	private function coerceDecimal2(mixed $value): ?string
	{
		if ($value === null) return null;

		if (is_string($value)) {
			$v = trim($value);
			if ($v === '') return null;
			$v = str_replace(',', '.', $v);
			if (!is_numeric($v)) return null;
			return number_format((float) $v, 2, '.', '');
		}

		if (is_int($value) || is_float($value)) {
			if ($value < 0) $value = 0;
			return number_format((float) $value, 2, '.', '');
		}

		return null;
	}

	private function coercePaymentStatus(mixed $value): ?string
	{
		if ($value instanceof PaymentStatus) return $value->value;
		if (!is_string($value)) return PaymentStatus::Pending->value;

		$v = trim($value);
		if ($v === '') return PaymentStatus::Pending->value;

		$enum = PaymentStatus::tryFrom($v);
		return $enum ? $enum->value : PaymentStatus::Pending->value;
	}

	private function coerceDate(mixed $value): ?string
	{
		if ($value === null) return null;

		if (is_string($value)) {
			$v = trim($value);
			if ($v === '') return null;
			try {
				return now()->parse($v)->format('Y-m-d');
			} catch (\Throwable) {
				return null;
			}
		}

		try {
			return now()->parse($value)->format('Y-m-d');
		} catch (\Throwable) {
			return null;
		}
	}
}
