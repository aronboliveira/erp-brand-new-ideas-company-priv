<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, Frequency, UserType};
use App\Models\Budget;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class BudgetSeeder extends Seeder
{
	private ConsoleOutput $out;
	// private const SECONDS_LIMIT = 6 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	public function run(): void
	{
		$clock = microtime(true);
		$this->out = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_BDG))
			return;

		// $cap = 3200;
		$cap = 2;
		$HARD_CAP = 2;

		$projectIds    = $this->fetchIds(DC::TABLE_PROJECTS);
		$contractIds   = $this->fetchIds(DC::TABLE_CONTRACTS);
		$branchIds     = $this->fetchIds(DC::TABLE_BRANCHES);
		$departmentIds = $this->fetchIds(DC::TABLE_DEPARTMENTS);

		$companyIds = $this->fetchCompanyIds([
			UserType::Company->value,
			UserType::Vendor->value,
		]);

		$txIds   = $this->fetchIds(DC::TABLE_TRS);
		$trfIds  = $this->fetchIds(DC::TABLE_BNK_TRF);
		$docIds  = $this->fetchIds(DC::TABLE_DOCS);

		$crNoteIds = defined(DC::class . '::TABLE_CR_NOTES') ? $this->fetchIds(DC::TABLE_CR_NOTES) : [];
		$dbNoteIds = defined(DC::class . '::TABLE_DB_NOTES') ? $this->fetchIds(DC::TABLE_DB_NOTES) : [];

		$auditUserIds = $this->fetchIds(DC::TABLE_USERS);

		$plans = [];

		$plans = array_merge($plans, $this->buildPlans('project', $projectIds, 0.25, 1, 8));
		$plans = array_merge($plans, $this->buildPlans('contract', $contractIds, 0.25, 1, 8));
		$plans = array_merge($plans, $this->buildPlans('company', $companyIds, 1.00, 1, 32));
		$plans = array_merge($plans, $this->buildPlans('branch', $branchIds, 0.25, 1, 16));
		$plans = array_merge($plans, $this->buildPlans('department', $departmentIds, 0.20, 1, 4));

		$rawTotal = 0;
		foreach ($plans as $p)
			$rawTotal += (int) ($p['count'] ?? 0);

		$target = $this->adjustToMultiple(min($rawTotal, $cap), 64, $cap);
		if ($target <= 0 || $target > $HARD_CAP) $target = $HARD_CAP;
		if ($target <= 0)
			return;

		$types = ['revenue', 'expense', 'mixed'];
		$freqs = array_column(Frequency::cases(), 'value');
		$sts   = array_column(EvaluationStatus::cases(), 'value');

		$typeIdx = 0;
		$freqIdx = 0;
		$stIdx   = 0;

		$created = 0;

		foreach ($plans as $plan) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('[BudgetSeeder] time limit reached, stopping seeding process.');
				return;
			}
			$count = (int) ($plan['count'] ?? 0);
			if ($count <= 0)
				continue;

			for ($i = 0; $i < $count; $i++) {
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					$this->out->writeln('[BudgetSeeder] time limit reached, stopping seeding process.');
					return;
				}
				if ($created >= $target)
					break 2;

				$m = new Budget();

				$creatorId = DC::DEFAULT_UUID;
				$m->setAttribute(DC::COL_TABLE_CREATOR, $creatorId);
				$m->setAttribute(DC::COL_TABLE_UPDATER, $creatorId);

				$assocKind = (string) ($plan['kind'] ?? '');
				$assocId   = (string) ($plan['id'] ?? '');

				if ($assocKind === 'project')
					$m->setAttribute(PJC::COL_PJ_ID, $assocId);
				elseif ($assocKind === 'contract')
					$m->setAttribute(PJC::COL_CTC_ID, $assocId);
				elseif ($assocKind === 'company')
					$m->setAttribute('company', $assocId);
				elseif ($assocKind === 'branch')
					$m->setAttribute('branch', $assocId);
				elseif ($assocKind === 'department')
					$m->setAttribute('department', $assocId);

				$m->setAttribute('name', $this->makeName($assocKind, $assocId, $created));
				$m->setAttribute('type', $types[$typeIdx++ % count($types)]);
				$m->setAttribute('frequency', $freqs[$freqIdx++ % max(1, count($freqs))]);
				$m->setAttribute('status', $sts[$stIdx++ % max(1, count($sts))]);

				$periodYear = (int) date('Y');
				$period = 'FY ' . $periodYear . ' - ' . strtoupper(Str::random(4));
				if ($this->chance(0.10))
					$period = null;
				$m->setAttribute('period', $period);

				$amount = $this->chance(0.10) ? null : random_int(1_000, 500_000);
				$m->setAttribute('amount', $amount);

				$currency = $this->chance(0.10) ? null : 'BRL';
				$m->setAttribute('currency', $currency);

				$exchange = $this->chance(0.10) ? null : (random_int(900000, 1300000) / 1_000_000);
				$m->setAttribute('exchange_rate', $exchange);

				$warn = $this->chance(0.10) ? null : random_int(50, 85);
				$crit = $this->chance(0.10) ? null : random_int(70, 95);
				$m->setAttribute(BC::COL_WRN_TRSH, $warn);
				$m->setAttribute(BC::COL_CRT_WRN_TH, $crit);

				$start = $this->chance(0.10) ? null : now()->subDays(random_int(0, 540));
				$end   = $start === null || $this->chance(0.10) ? null : (clone $start)->addDays(random_int(7, 365));

				$m->setAttribute(PJC::COL_S_DT, $start);
				$m->setAttribute(PJC::COL_E_DT, $end);

				$m->setAttribute('description', $this->chance(0.10) ? null : 'Mock budget seeded variation #' . $created);
				$m->setAttribute('notes', $this->chance(0.10) ? null : 'Seed note ' . strtoupper(Str::random(12)));

				$m->setAttribute(BC::COL_INC_DATA, $this->chance(0.10) ? null : json_encode([
					'sources' => ['sales', 'services', 'subscriptions'],
					'tags'    => ['mock', 'seed'],
				], JSON_UNESCAPED_UNICODE));

				$m->setAttribute(BC::COL_EXP_DATA, $this->chance(0.10) ? null : json_encode([
					'categories' => ['payroll', 'cloud', 'marketing'],
					'tags'       => ['mock', 'seed'],
				], JSON_UNESCAPED_UNICODE));

				$bankTransfers = $this->chance(0.10) ? null : $this->sampleMany($trfIds, 0, 4);
				$transactions  = $this->chance(0.10) ? null : $this->sampleMany($txIds, 0, 8);

				$notePool = array_values(array_unique(array_merge($crNoteIds, $dbNoteIds)));
				$cardNotes = $this->chance(0.10) ? null : $this->sampleMany($notePool, 0, 4);

				$receipts = $this->chance(0.10) ? null : $this->makeReceipts($docIds);
				$attachments = $this->chance(0.10) ? null : $this->makeAttachments($docIds);

				$m->setAttribute(BC::COL_BNK_TRFS, $bankTransfers);
				$m->setAttribute('transactions', $transactions);
				$m->setAttribute(BC::COL_CARD_NTS, $cardNotes);
				$m->setAttribute('receipts', $receipts);
				$m->setAttribute('attachments', $attachments);

				$m->setAttribute('metadata', $this->chance(0.10) ? null : [
					'seed'   => 'BudgetSeeder',
					'kind'   => $assocKind,
					'seq'    => $created,
					'hints'  => ['json_columns' => true, 'receipts_merge' => true],
				]);

				// $this->out->writeln(sprintf(
				// 	'[BudgetSeeder] creating #%d kind=%s type=%s freq=%s status=%s amount=%s receipts=%d attachments=%d',
				// 	$created + 1,
				// 	$assocKind,
				// 	(string) $m->getAttribute('type'),
				// 	(string) ($m->getAttribute('frequency') instanceof Frequency ? $m->getAttribute('frequency')->value : ($m->getAttribute('frequency') ?? 'null')),
				// 	(string) ($m->getAttribute('status') instanceof EvaluationStatus ? $m->getAttribute('status')->value : ($m->getAttribute('status') ?? 'null')),
				// 	(string) ($m->getAttribute('amount') ?? 'null'),
				// 	is_array($m->getAttribute('receipts')) ? count($m->getAttribute('receipts')) : 0,
				// 	is_array($m->getAttribute('attachments')) ? count($m->getAttribute('attachments')) : 0
				// ));

				try {
					$m->save();
					$created++;
				} catch (\Throwable $e) {
					Log::error(BudgetSeeder::class . ' save failed', [
						'kind'  => $assocKind,
						'id'    => $assocId,
						'error' => $e->getMessage(),
						'file'  => $e->getFile(),
						'line'  => $e->getLine(),
					]);
				}
			}
		}
	}

	private function fetchIds(string $table): array
	{
		try {
			if (!Schema::hasTable($table))
				return [];

			$rows = DB::select("select id from {$table}");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning(self::class . ' fetchIds failed', [
				'table' => $table,
				'error' => $e->getMessage(),
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
			]);
			return [];
		}
	}

	private function fetchCompanyIds(array $types): array
	{
		try {
			if (!Schema::hasTable(DC::TABLE_USERS))
				return [];

			$placeholders = implode(',', array_fill(0, count($types), '?'));
			$sql = "select id from " . DC::TABLE_USERS . " where type in ({$placeholders})";
			$rows = DB::select($sql, array_values($types));

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning(self::class . ' fetchCompanyIds failed', [
				'error' => $e->getMessage(),
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
			]);
			return [];
		}
	}

	private function buildPlans(string $kind, array $ids, float $ratio, int $min, int $max): array
	{
		$out = [];
		foreach ($ids as $id) {
			if ($ratio < 1.0 && !$this->chance($ratio))
				continue;

			$count = random_int($min, $max);
			$out[] = ['kind' => $kind, 'id' => $id, 'count' => $count];
		}
		return $out;
	}

	private function adjustToMultiple(int $value, int $multiple, int $cap): int
	{
		if ($value <= 0)
			return 0;

		$value = min($value, $cap);

		$rem = $value % $multiple;
		if ($rem !== 0)
			$value += ($multiple - $rem);

		if ($value > $cap)
			$value -= ($value % $multiple);

		return max(0, $value);
	}

	private function makeName(string $kind, string $id, int $seq): string
	{
		$suffix = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $id), 0, 8));
		return 'Budget ' . strtoupper($kind) . ' ' . $suffix . ' #' . ($seq + 1);
	}

	private function chance(float $p): bool
	{
		if ($p <= 0.0)
			return false;
		if ($p >= 1.0)
			return true;
		return (mt_rand() / mt_getrandmax()) <= $p;
	}

	private function pickOne(array $pool): ?string
	{
		$pool = array_values($pool);
		if (!$pool)
			return null;

		return (string) $pool[array_rand($pool)];
	}

	private function sampleMany(array $pool, int $min, int $max): ?array
	{
		$pool = array_values(array_unique($pool));
		$nPool = count($pool);
		if ($nPool === 0)
			return null;

		$max = max(0, min($max, $nPool));
		$min = max(0, min($min, $max));

		$n = random_int($min, $max);
		if ($n === 0)
			return [];

		$keys = array_rand($pool, $n);
		$keys = is_array($keys) ? $keys : [$keys];

		$out = [];
		foreach ($keys as $k)
			$out[] = (string) $pool[$k];

		return array_values(array_unique($out));
	}

	private function makeReceipts(array $docIds): array
	{
		$out = [];

		$docPick = $this->sampleMany($docIds, 0, 2) ?? [];
		foreach ($docPick as $id)
			$out[] = $id;

		$out[] = 'https://drive.google.com/file/d/' . Str::random(24);
		if ($this->chance(0.30))
			$out[] = 'att://' . bin2hex(random_bytes(12));

		return array_values(array_unique($out));
	}

	private function makeAttachments(array $docIds): array
	{
		$out = [];

		$docPick = $this->sampleMany($docIds, 0, 3) ?? [];
		foreach ($docPick as $id)
			$out[] = $id;

		$out[] = 'https://storage.googleapis.com/' . Str::random(12) . '/' . Str::random(16) . '.pdf';
		if ($this->chance(0.40))
			$out[] = 'att://' . bin2hex(random_bytes(10));

		return array_values(array_unique($out));
	}
}
