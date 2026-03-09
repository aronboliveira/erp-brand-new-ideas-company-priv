<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\PaymentStatus;
use App\Models\ProjectInvoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectInvoiceSeeder extends Seeder
{
	// private const SECONDS_LIMIT = 300;
	private const SECONDS_LIMIT = 32;
	// private const HARD_CAP = 1024;
	private const HARD_CAP = 2;
	private const PROJECT_FRACTION = 0.20;
	private const INVOICE_FRACTION = 0.20;

	private const MAX_EXISTS_ATTEMPTS = 96;
	private const MAX_FILL_ATTEMPTS = 8192;

	public function run(): void
	{
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$clock = microtime(true);
		$cap = self::HARD_CAP;

		if (!Schema::hasTable(DC::TABLE_PROJECTS) || !Schema::hasTable(DC::TABLE_INVS) || !Schema::hasTable(DC::TABLE_PRJ_INV)) {
			$out->writeln('<comment>[ProjectInvoiceSeeder]</comment> Missing required tables. Aborting.');
			return;
		}

		$projectIds = $this->fetchProjectIdsRaw();
		$invoiceRows = $this->fetchInvoiceRowsRaw();

		if (count($projectIds) < 1 || count($invoiceRows) < 1) {
			$out->writeln('<comment>[ProjectInvoiceSeeder]</comment> No projects or invoices found.');
			return;
		}

		$pickedProjects = $this->pickFraction($projectIds, self::PROJECT_FRACTION);
		$pickedInvoices = $this->pickFraction($invoiceRows, self::INVOICE_FRACTION);

		if (count($pickedProjects) < 1 || count($pickedInvoices) < 1) {
			$out->writeln('<comment>[ProjectInvoiceSeeder]</comment> Sampling resulted in empty sets.');
			return;
		}

		$capacity = count($pickedProjects) * count($pickedInvoices);
		$target = $this->targetMultipleOf64($capacity);

		if ($target <= 0) {
			$out->writeln(sprintf(
				'<comment>[ProjectInvoiceSeeder]</comment> capacity=%d cannot reach a 64-multiple target.',
				$capacity
			));
			return;
		}

		$out->writeln(sprintf(
			'<info>[ProjectInvoiceSeeder]</info> projects=%d->%d invoices=%d->%d capacity=%d target=%d',
			count($projectIds),
			count($pickedProjects),
			count($invoiceRows),
			count($pickedInvoices),
			$capacity,
			$target
		));

		$created = 0;

		foreach ($pickedProjects as $pjId) {
			foreach ($pickedInvoices as $inv) {
				if ($created >= $target) break 2;

				$invId = (string) ($inv['id'] ?? '');
				if ($invId === '' || $pjId === '') continue;

				if ($this->pairExistsRaw($invId, $pjId)) continue;

				if ($this->createRow($inv, $pjId)) {
					$created++;
					if ($created % 64 === 0) {
						$out->writeln(sprintf('<info>[ProjectInvoiceSeeder]</info> created=%d', $created));
					}
				}
			}
		}

		// Se já existiam muitos pares no banco, tenta preencher até o target com pares aleatórios dentro do MESMO universo amostrado.
		$attempts = 0;
		while ($created < $target && $attempts++ < self::MAX_FILL_ATTEMPTS) {
			if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
				$out->writeln('[ProjectInvoiceSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
				$this->command?->warn('[ProjectInvoiceSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
				return;
			}
			if (--$cap < 0) {
				$out->writeln('[ProjectInvoiceSeeder] Limite máximo de itens atingido, interrompendo a execução do seeder.');
				$this->command?->warn('[ProjectInvoiceSeeder] Limite máximo de itens atingido, interrompendo a execução do seeder.');
				return;
			}
			$pjId = $pickedProjects[random_int(0, count($pickedProjects) - 1)] ?? '';
			$inv  = $pickedInvoices[random_int(0, count($pickedInvoices) - 1)] ?? null;

			if (!is_string($pjId) || $pjId === '' || !is_array($inv)) continue;

			$invId = (string) ($inv['id'] ?? '');
			if ($invId === '') continue;

			$tries = 0;
			do {
				$tries++;
				if ($tries > self::MAX_EXISTS_ATTEMPTS) break;

				$inv = $pickedInvoices[random_int(0, count($pickedInvoices) - 1)] ?? null;
				$pjId = $pickedProjects[random_int(0, count($pickedProjects) - 1)] ?? '';

				if (!is_array($inv) || !is_string($pjId) || $pjId === '') continue;

				$invId = (string) ($inv['id'] ?? '');
				if ($invId === '') continue;
			} while ($this->pairExistsRaw($invId, $pjId));

			if ($tries > self::MAX_EXISTS_ATTEMPTS) continue;
			$out->writeln("[ProjectInvoiceSeeder] {$created}/{$target} Attempting to create pair invoice_id={$invId} project_id={$pjId} after {$tries} tries.");
			if ($this->createRow($inv, $pjId)) {
				$created++;
				if ($created % 64 === 0) {
					$out->writeln(sprintf('<info>[ProjectInvoiceSeeder]</info> created=%d', $created));
				}
			}
		}

		$out->writeln(sprintf('<info>[ProjectInvoiceSeeder]</info> done created=%d', $created));
	}

	private function out(): OutputInterface
	{
		try {
			if ($this->command) return $this->command->getOutput();
		} catch (\Throwable) {
		}
		return new ConsoleOutput();
	}

	/** leitura raw otimizada */
	private function fetchProjectIdsRaw(): array
	{
		$rows = DB::select("select id from " . DC::TABLE_PROJECTS);
		$out = [];
		foreach ($rows as $r) {
			$a = (array) $r;
			$id = is_string($a['id'] ?? null) ? trim((string) $a['id']) : '';
			if ($id !== '') $out[] = $id;
		}
		return $out;
	}

	/** leitura raw otimizada + defensiva quanto a colunas reais do invoice */
	private function fetchInvoiceRowsRaw(): array
	{
		$cols = ['id'];

		if (Schema::hasColumn(DC::TABLE_INVS, BC::COL_BL_ID)) $cols[] = BC::COL_BL_ID;
		if (Schema::hasColumn(DC::TABLE_INVS, BC::COL_DUE_DT)) $cols[] = BC::COL_DUE_DT;
		if (Schema::hasColumn(DC::TABLE_INVS, BC::COL_TAX_ID)) $cols[] = BC::COL_TAX_ID;

		$sql = "select " . implode(', ', $cols) . " from " . DC::TABLE_INVS;
		$rows = DB::select($sql);

		$out = [];
		foreach ($rows as $r) {
			$a = (array) $r;
			$id = is_string($a['id'] ?? null) ? trim((string) $a['id']) : '';
			if ($id === '') continue;

			$out[] = [
				'id' => $id,
				BC::COL_BL_ID => $a[BC::COL_BL_ID] ?? null,
				BC::COL_DUE_DT => $a[BC::COL_DUE_DT] ?? null,
				BC::COL_TAX_ID => $a[BC::COL_TAX_ID] ?? null,
			];
		}

		return $out;
	}

	private function pickFraction(array $list, float $fraction): array
	{
		$fraction = max(0.0, min(1.0, $fraction));
		$n = (int) floor(count($list) * $fraction);
		$n = max(1, $n);

		$tmp = $list;
		shuffle($tmp);

		return array_slice($tmp, 0, min($n, count($tmp)));
	}

	/** capacidade -> target múltiplo de 64 (sem “multiplicar” os critérios) */
	private function targetMultipleOf64(int $capacity): int
	{
		if ($capacity < 64) return 0;

		$mod = $capacity % 64;
		if ($mod === 0) return $capacity;

		$down = $capacity - $mod;
		return max(64, $down);
	}

	private function pairExistsRaw(string $invoiceId, string $projectId): bool
	{
		try {
			$sql = "select 1 as x from " . DC::TABLE_PRJ_INV . " where " . BC::COL_INV_ID . " = ? and " . PJC::COL_PJ_ID . " = ? limit 1";
			return DB::selectOne($sql, [$invoiceId, $projectId]) !== null;
		} catch (\Throwable) {
			return false;
		}
	}

	private function createRow(array $invoiceRow, string $projectId): bool
	{
		$invId = (string) ($invoiceRow['id'] ?? '');
		if ($invId === '' || $projectId === '') return false;

		// COL_BL_ID: “fonte de verdade = invoice”
		$billIdRaw = $invoiceRow[BC::COL_BL_ID] ?? null;
		$billId = (is_string($billIdRaw) && $this->looksLikeUuid($billIdRaw)) ? trim($billIdRaw) : null;

		// opcional: se invoice tiver tax_id válido, replica (senão null)
		$taxIdRaw = $invoiceRow[BC::COL_TAX_ID] ?? null;
		$taxId = (is_string($taxIdRaw) && $this->looksLikeUuid($taxIdRaw)) ? trim($taxIdRaw) : null;

		// due_date: tenta do invoice; se ausente, define para não falhar (model exige)
		$due = $this->parseDateOrNull($invoiceRow[BC::COL_DUE_DT] ?? null);
		if (!$due) {
			$due = Carbon::now('America/Sao_Paulo')->addDays(random_int(-15, 90))->toDateString();
		}

		// status: int válido conforme PaymentStatus::getAllIndexes()
		$idxs = PaymentStatus::getAllIndexes();
		$status = $idxs[random_int(0, count($idxs) - 1)] ?? 1;

		try {
			ProjectInvoice::create([
				BC::COL_INV_ID => $invId,
				PJC::COL_PJ_ID => $projectId,

				BC::COL_BL_ID  => $billId,
				BC::COL_TAX_ID => $taxId,
				BC::COL_DUE_DT => $due,

				'status' => (int) $status,

				DC::COL_TABLE_UPDATER => null,
			]);

			return true;
		} catch (\Throwable $e) {
			Log::debug('[ProjectInvoiceSeeder] create failed', [
				'invoice_id' => $invId,
				'project_id' => $projectId,
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}

	private function parseDateOrNull(mixed $value): ?string
	{
		if ($value === null) return null;

		try {
			if ($value instanceof Carbon) return $value->toDateString();
			$s = is_string($value) ? trim($value) : (is_scalar($value) ? (string) $value : '');
			if ($s === '') return null;
			return Carbon::parse($s)->toDateString();
		} catch (\Throwable) {
			return null;
		}
	}

	private function looksLikeUuid(string $v): bool
	{
		$v = trim($v);
		if ($v === '') return false;

		return (bool) preg_match(
			'/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/',
			$v
		);
	}
}
