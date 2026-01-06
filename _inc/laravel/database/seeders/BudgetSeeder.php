<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	SettingsConstants as SC
};
use App\Enums\{EvaluationStatus, Frequency, UserType};
use App\Models\Budget;
use Carbon\CarbonImmutable;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\{ConsoleOutput, OutputInterface};

class BudgetSeeder extends Seeder
{
	private const HARD_CAP = 32000;
	private const NULL_P = 0.10;

	private const MAX_UNIQUE_ATTEMPTS = 40;
	private const MAX_DISTRIBUTE_ATTEMPTS = 200000;

	private OutputInterface $out;
	private Faker $faker;

	private string $driver;
	private string $budgetsTable;

	private array $userIds = [];
	private array $adminishUserIds = [];
	private array $projectIds = [];
	private array $contractIds = [];
	private array $companyIds = [];
	private array $branchIds = [];
	private array $departmentIds = [];

	private array $bankTransferIds = [];
	private array $transactionIds = [];
	private array $documentIds = [];

	private int $globalIndex = 0;

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$this->faker = FakerFactory::create(config('app.faker_locale') ?: 'pt_BR');

		$this->driver = (string) DB::getDriverName();
		$this->budgetsTable = (new Budget())->getTable();

		if (!Schema::hasTable($this->budgetsTable)) {
			Log::warning('BudgetSeeder: budgets table not found', ['table' => $this->budgetsTable]);
			return;
		}

		$this->primeCaches();

		$planProjects = $this->planCounts($this->sampleIds($this->projectIds, 0.25), 1, 8, 8);
		$planContracts = $this->planCounts($this->sampleIds($this->contractIds, 0.25), 1, 8, 8);
		$planCompanies = $this->planCounts($this->companyIds, 1, 32, 32);
		$planBranches = $this->planCounts($this->sampleIds($this->branchIds, 0.25), 1, 16, 16);
		$planDepartments = $this->planCounts($this->sampleIds($this->departmentIds, 0.20), 1, 4, 4);

		$rawTotal =
			array_sum($planProjects)
			+ array_sum($planContracts)
			+ array_sum($planCompanies)
			+ array_sum($planBranches)
			+ array_sum($planDepartments);

		$targetTotal = $this->roundTo64($rawTotal, self::HARD_CAP);

		$this->distributeToTarget($targetTotal, [
			'companies' => [&$planCompanies, 32],
			'projects' => [&$planProjects, 8],
			'contracts' => [&$planContracts, 8],
			'branches' => [&$planBranches, 16],
			'departments' => [&$planDepartments, 4],
		]);

		$totalPlanned =
			array_sum($planProjects)
			+ array_sum($planContracts)
			+ array_sum($planCompanies)
			+ array_sum($planBranches)
			+ array_sum($planDepartments);

		$this->out->writeln("BudgetSeeder: planned={$totalPlanned} (raw={$rawTotal}, target={$targetTotal})");

		$this->seedByPlan('project', $planProjects);
		$this->seedByPlan('contract', $planContracts);
		$this->seedByPlan('company', $planCompanies);
		$this->seedByPlan('branch', $planBranches);
		$this->seedByPlan('department', $planDepartments);
	}

	private function primeCaches(): void
	{
		$this->userIds = $this->fetchIdsRaw(DC::TABLE_USERS);
		$this->adminishUserIds = $this->fetchUserIdsByTypesRaw([
			UserType::SuperAdmin->value,
			UserType::Admin->value,
			UserType::Company->value,
			UserType::Vendor->value,
			UserType::Hr->value,
			UserType::Accountant->value,
		]);

		$this->projectIds = $this->fetchIdsRaw(DC::TABLE_PROJECTS);
		$this->contractIds = $this->fetchIdsRaw(DC::TABLE_CONTRACTS);

		$this->companyIds = $this->fetchUserIdsByTypesRaw([
			UserType::Company->value,
			UserType::Vendor->value,
		]);

		$this->branchIds = $this->fetchIdsRaw(DC::TABLE_BRANCHES);
		$this->departmentIds = $this->fetchIdsRaw(DC::TABLE_DEPARTMENTS);

		$this->bankTransferIds = Schema::hasTable(DC::TABLE_BNK_TRF) ? $this->fetchIdsRaw(DC::TABLE_BNK_TRF) : [];
		$this->transactionIds = Schema::hasTable(DC::TABLE_TRS) ? $this->fetchIdsRaw(DC::TABLE_TRS) : [];
		$this->documentIds = Schema::hasTable(DC::TABLE_DOCS) ? $this->fetchIdsRaw(DC::TABLE_DOCS) : [];

		if (!$this->adminishUserIds)
			$this->adminishUserIds = $this->userIds;
	}

	private function seedByPlan(string $context, array $countsById): void
	{
		if (!$countsById)
			return;

		foreach ($countsById as $ownerId => $count) {
			$ownerId = (string) $ownerId;
			$count = (int) $count;
			if ($count <= 0)
				continue;

			for ($i = 0; $i < $count; $i++) {
				$m = $this->buildBudget($context, $ownerId, $i, $count);

				$this->out->writeln(sprintf(
					"BudgetSeeder: create context=%s owner=%s type=%s freq=%s amount=%s currency=%s period=%s",
					$context,
					$ownerId,
					(string) $m->getAttribute('type'),
					(string) $m->getAttribute('frequency'),
					(string) ($m->getAttribute('amount') ?? 'null'),
					(string) ($m->getAttribute('currency') ?? 'null'),
					(string) ($m->getAttribute('period') ?? 'null'),
				));

				try {
					$m->save();
				} catch (\Throwable $e) {
					Log::error('BudgetSeeder: failed to save Budget', [
						'context' => $context,
						'owner_id' => $ownerId,
						'code' => $m->getAttribute('code'),
						'name' => $m->getAttribute('name'),
						'error' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}

				$this->globalIndex++;
			}
		}
	}

	private function buildBudget(string $context, string $ownerId, int $i, int $totalForOwner): Budget
	{
		$m = new Budget();

		$type = $this->pickBudgetType($i, $totalForOwner);
		$frequency = $this->pickFrequency($i);

		$start = $this->randomDateStart();
		$end = $this->deriveEndDate($start, $frequency);

		$period = $this->maybeNull(self::NULL_P) ? null : $this->makePeriodWithYear($start, $frequency);
		$amount = $this->maybeNull(self::NULL_P) ? null : $this->faker->randomFloat(2, 500, 2500000);

		$currency = $this->maybeNull(self::NULL_P) ? null : $this->pickCurrency();
		$exchangeRate = $this->maybeNull(self::NULL_P)
			? null
			: (($currency && $currency !== SC::DEF_SITE_CURRENCY_ID)
				? $this->faker->randomFloat(4, 0.10, 8.50)
				: 1.0000);

		$warn = $this->maybeNull(self::NULL_P) ? null : $this->faker->randomFloat(2, 50, 90);
		$crit = $this->maybeNull(self::NULL_P) ? null : $this->faker->randomFloat(2, 70, 99.5);
		if ($warn !== null && $crit !== null && $crit < $warn)
			$crit = $warn;

		$creatorId = $this->pickId($this->adminishUserIds);
		$updaterId = $this->pickId($this->adminishUserIds);

		$code = $this->maybeNull(self::NULL_P) ? null : $this->makeUniqueCode();
		$name = $this->makeUniqueName($context, $ownerId, $type, $period, $start, $end);

		$m->setAttribute('code', $code);
		$m->setAttribute('name', $name);
		$m->setAttribute('type', $this->maybeNull(self::NULL_P) ? null : $type);
		$m->setAttribute('period', $period);

		$m->setAttribute('frequency', $this->maybeNull(self::NULL_P) ? Frequency::Once->value : $frequency->value);

		$m->setAttribute('from', $this->maybeNull(self::NULL_P) ? null : $start->format('Y-m-d'));
		$m->setAttribute(PJC::COL_S_DT, $start->format('Y-m-d'));
		$m->setAttribute('to', $this->maybeNull(self::NULL_P) ? null : $end->format('Y-m-d'));
		$m->setAttribute(PJC::COL_E_DT, $end->format('Y-m-d'));

		$m->setAttribute('amount', $amount);
		$m->setAttribute('currency', $currency ?? SC::DEF_SITE_CURRENCY_ID);
		$m->setAttribute(BC::COL_EXC_RT, $exchangeRate);
		$m->setAttribute(BC::COL_WRN_TRSH, $warn);
		$m->setAttribute(BC::COL_CRT_WRN_TH, $crit);

		$m->setAttribute('description', $this->maybeNull(self::NULL_P) ? null : $this->faker->sentence(14));
		$m->setAttribute('notes', $this->maybeNull(self::NULL_P) ? null : $this->faker->paragraph(2));

		$this->applyContext($m, $context, $ownerId);

		$this->setBudgetDataPayloads($m, $type, $amount, $currency ?? SC::DEF_SITE_CURRENCY_ID);

		$m->setAttribute(BC::COL_BNK_TRFS, $this->encodeJson($this->pickSomeIds($this->bankTransferIds, 0, 6)));
		$m->setAttribute('transactions', $this->encodeJson($this->pickSomeIds($this->transactionIds, 0, 10)));
		$m->setAttribute('attachments', $this->encodeJson($this->makeAttachmentsPayload()));
		$m->setAttribute('metadata', $this->encodeJson($this->makeMetadataPayload($context)));

		$this->applyWorkflowActors($m);

		$m->setAttribute(DC::COL_TABLE_CREATOR, $creatorId);
		$m->setAttribute(DC::COL_TABLE_UPDATER, $updaterId ?: $creatorId);

		return $m;
	}

	private function applyContext(Budget $m, string $context, string $ownerId): void
	{
		if ($context === 'project')
			$m->setAttribute(PJC::COL_PJ_ID, $ownerId);
		elseif ($context === 'contract')
			$m->setAttribute(PJC::COL_CTC_ID, $ownerId);
		elseif ($context === 'company')
			$m->setAttribute('company', $ownerId);
		elseif ($context === 'branch')
			$m->setAttribute('branch', $ownerId);
		elseif ($context === 'department')
			$m->setAttribute('department', $ownerId);
	}

	private function applyWorkflowActors(Budget $m): void
	{
		$r = random_int(1, 1000);

		$submitter = null;
		$approver = null;
		$rejecter = null;

		if ($r <= 120) {
			$rejecter = $this->pickId($this->adminishUserIds);
			$submitter = $rejecter ?: $this->pickId($this->adminishUserIds);
		} elseif ($r <= 320) {
			$approver = $this->pickId($this->adminishUserIds);
			$submitter = $approver ?: $this->pickId($this->adminishUserIds);
		} elseif ($r <= 620) {
			$submitter = $this->pickId($this->adminishUserIds);
		}

		$submittedAt = $submitter ? $this->faker->dateTimeBetween('-18 months', 'now') : null;
		$approvedAt = $approver ? $this->faker->dateTimeBetween($submittedAt ?: '-18 months', 'now') : null;
		$rejectedAt = $rejecter ? $this->faker->dateTimeBetween($submittedAt ?: '-18 months', 'now') : null;

		$m->setAttribute(PJC::COL_SBM_BY, $this->maybeNull(self::NULL_P) ? null : $submitter);
		$m->setAttribute(PJC::COL_SBM_AT, $this->maybeNull(self::NULL_P) ? null : ($submittedAt ? $submittedAt->format('Y-m-d H:i:s') : null));

		$m->setAttribute(PJC::COL_APV_BY, $this->maybeNull(self::NULL_P) ? null : $approver);
		$m->setAttribute(PJC::COL_APV_AT, $this->maybeNull(self::NULL_P) ? null : ($approvedAt ? $approvedAt->format('Y-m-d H:i:s') : null));

		$m->setAttribute(PJC::COL_REJ_BY, $this->maybeNull(self::NULL_P) ? null : $rejecter);
		$m->setAttribute(PJC::COL_REJ_AT, $this->maybeNull(self::NULL_P) ? null : ($rejectedAt ? $rejectedAt->format('Y-m-d H:i:s') : null));

		$baseStatus = EvaluationStatus::Pending->value;
		if (!empty($m->getAttribute(PJC::COL_REJ_BY)))
			$baseStatus = EvaluationStatus::Decline->value;
		elseif (!empty($m->getAttribute(PJC::COL_APV_BY)))
			$baseStatus = EvaluationStatus::Accept->value;
		elseif (!empty($m->getAttribute(PJC::COL_SBM_BY)))
			$baseStatus = EvaluationStatus::Pending->value;

		$m->setAttribute('status', $this->maybeNull(self::NULL_P) ? null : $baseStatus);
	}

	private function setBudgetDataPayloads(Budget $m, string $type, ?float $amount, string $currency): void
	{
		$cats = ['sales', 'services', 'subscriptions', 'royalties', 'refunds', 'ops', 'payroll', 'taxes', 'infra', 'marketing'];

		$income = [
			'currency' => $currency,
			'expected_total' => $amount,
			'lines' => [
				['category' => 'services', 'pct' => 55],
				['category' => 'sales', 'pct' => 35],
				['category' => 'subscriptions', 'pct' => 10],
			],
			'notes' => $this->faker->sentence(10),
		];

		$expense = [
			'currency' => $currency,
			'expected_total' => $amount,
			'lines' => [
				['category' => 'ops', 'pct' => 30],
				['category' => 'payroll', 'pct' => 45],
				['category' => 'infra', 'pct' => 15],
				['category' => 'taxes', 'pct' => 10],
			],
			'notes' => $this->faker->sentence(10),
		];

		$income['lines'] = array_values($income['lines']);
		$expense['lines'] = array_values($expense['lines']);

		$m->setAttribute(BC::COL_INC_DATA, $this->encodeJson(($type === 'revenue' || $type === 'mixed') ? $income : null));
		$m->setAttribute(BC::COL_EXP_DATA, $this->encodeJson(($type === 'expense' || $type === 'mixed') ? $expense : null));
	}

	private function makeAttachmentsPayload(): ?array
	{
		$out = [];

		if ($this->documentIds && random_int(1, 1000) <= 550)
			$out[] = $this->pickId($this->documentIds);

		if (random_int(1, 1000) <= 280)
			$out[] = 'att://' . sha1((string) Str::uuid());

		if (random_int(1, 1000) <= 120)
			$out[] = 'https://drive.google.com/file/d/' . Str::random(24);

		$out = collect($out)->filter(fn($v) => is_string($v) && trim($v) !== '')->values()->all();
		return $out ?: null;
	}

	private function makeMetadataPayload(string $context): array
	{
		$tags = collect([
			'mock',
			'seed',
			$context,
			'budget',
			$this->faker->randomElement(['ops', 'growth', 'core', 'experimental']),
		])->unique()->values()->all();

		return [
			'tags' => $tags,
			'confidence' => $this->faker->randomFloat(2, 0.4, 0.98),
			'source' => 'BudgetSeeder',
			'seeded_at' => now()->format('c'),
		];
	}

	private function pickBudgetType(int $i, int $totalForOwner): string
	{
		$types = ['revenue', 'expense', 'mixed'];

		if ($totalForOwner >= count($types))
			return $types[$i % count($types)];

		return $types[$i % max(1, $totalForOwner)];
	}

	private function pickFrequency(int $i): Frequency
	{
		$cases = Frequency::cases();
		return $cases[($this->globalIndex + $i) % max(1, count($cases))] ?? Frequency::Once;
	}

	private function randomDateStart(): CarbonImmutable
	{
		$dt = $this->faker->dateTimeBetween('-18 months', '+8 months');
		return CarbonImmutable::instance($dt)->startOfDay();
	}

	private function deriveEndDate(CarbonImmutable $start, Frequency $freq): CarbonImmutable
	{
		return match ($freq) {
			Frequency::Hourly => $start->addDays(random_int(0, 2)),
			Frequency::Weekly => $start->addWeeks(random_int(1, 12)),
			Frequency::Biweekly => $start->addWeeks(2 * random_int(1, 10)),
			Frequency::Semimonthly => $start->addDays(15 * random_int(1, 10)),
			Frequency::Monthly => $start->addMonths(random_int(1, 18)),
			Frequency::Quaternaly => $start->addMonths(4 * random_int(1, 6)),
			Frequency::Semestral => $start->addMonths(6 * random_int(1, 4)),
			Frequency::Annual => $start->addYears(random_int(1, 3)),
			Frequency::Variable => $start->addDays(random_int(7, 420)),
			Frequency::Once => $start->addDays(random_int(0, 90)),
		};
	}

	private function makePeriodWithYear(CarbonImmutable $start, Frequency $freq): string
	{
		$y = (int) $start->format('Y');

		if (in_array($freq, [Frequency::Annual], true))
			return "FY {$y}";

		if (in_array($freq, [Frequency::Quaternaly], true)) {
			$q = (int) ceil(((int) $start->format('n')) / 3);
			return "Q{$q} {$y}";
		}

		if (in_array($freq, [Frequency::Semestral], true)) {
			$h = ((int) $start->format('n')) <= 6 ? 1 : 2;
			return "H{$h} {$y}";
		}

		return $start->format('m') . '-' . $y;
	}

	private function makeUniqueCode(): string
	{
		$attempt = 0;

		do {
			$attempt++;
			$code = 'BDG-' . strtoupper((string) Str::uuid());

			$exists = $this->existsRaw(
				"select 1 from {$this->budgetsTable} where code = ? limit 1",
				[$code]
			);

			if (!$exists)
				return $code;
		} while ($attempt < self::MAX_UNIQUE_ATTEMPTS);

		return 'BDG-' . strtoupper(Str::random(8)) . '-' . time();
	}

	private function makeUniqueName(string $context, string $ownerId, string $type, ?string $period, CarbonImmutable $start, CarbonImmutable $end): string
	{
		$attempt = 0;

		$ctxCol = match ($context) {
			'project' => PJC::COL_PJ_ID,
			'contract' => PJC::COL_CTC_ID,
			'company' => 'company',
			'branch' => 'branch',
			'department' => 'department',
			default => null,
		};

		do {
			$attempt++;

			$label = strtoupper(substr($type, 0, 1));
			$periodPart = $period ?: $this->makePeriodWithYear($start, Frequency::Once);
			$rand = strtoupper(Str::random(5));

			$name = "{$label} Budget {$periodPart} {$rand}";

			$sql = "select 1 from {$this->budgetsTable} where name = ? limit 1";
			$bindings = [$name];

			if ($ctxCol) {
				$sql = "select 1 from {$this->budgetsTable} where name = ? and {$ctxCol} = ? limit 1";
				$bindings = [$name, $ownerId];
			}

			$exists = $this->existsRaw($sql, $bindings);

			if (!$exists)
				return $name;
		} while ($attempt < self::MAX_UNIQUE_ATTEMPTS);

		return "Budget {$periodPart} " . strtoupper(Str::random(10));
	}

	private function planCounts(array $ids, int $min, int $max, int $maxPerEntity): array
	{
		$out = [];
		$ids = collect($ids)->filter(fn($v) => is_string($v) && trim($v) !== '')->values()->all();

		foreach ($ids as $id) {
			$n = random_int($min, $max);

			$types = ['revenue', 'expense', 'mixed'];
			if ($n < count($types) && $maxPerEntity >= count($types) && random_int(1, 1000) <= 700)
				$n = min(count($types), $max);

			$out[(string) $id] = max($min, min($n, $maxPerEntity));
		}

		return $out;
	}

	private function distributeToTarget(int $targetTotal, array $plans): void
	{
		$attempt = 0;

		$total = 0;
		foreach ($plans as $p)
			$total += array_sum($p[0]);

		if ($total === $targetTotal)
			return;

		if ($total > self::HARD_CAP) {
			$this->shrinkToCap($plans, self::HARD_CAP);
			$total = 0;
			foreach ($plans as $p)
				$total += array_sum($p[0]);
		}

		$total = 0;
		foreach ($plans as $p)
			$total += array_sum($p[0]);

		while ($total !== $targetTotal && $attempt < self::MAX_DISTRIBUTE_ATTEMPTS) {
			$attempt++;

			if ($total < $targetTotal) {
				$changed = false;

				foreach ($plans as $tuple) {
					[$ref, $maxPerEntity] = $tuple;
					$map = &$ref;

					foreach ($map as $k => $v) {
						if ($v < $maxPerEntity) {
							$map[$k] = $v + 1;
							$total++;
							$changed = true;
							if ($total >= $targetTotal)
								break 2;
						}
					}
				}

				if (!$changed)
					break;
			} else {
				$changed = false;

				foreach (array_reverse($plans, true) as $tuple) {
					[$ref, $maxPerEntity] = $tuple;
					$map = &$ref;

					foreach ($map as $k => $v) {
						if ($v > 1) {
							$map[$k] = $v - 1;
							$total--;
							$changed = true;
							if ($total <= $targetTotal)
								break 2;
						}
					}
				}

				if (!$changed)
					break;
			}
		}

		if ($total !== $targetTotal) {
			Log::warning('BudgetSeeder: could not reach exact target total', [
				'target' => $targetTotal,
				'final' => $total,
				'attempts' => $attempt,
			]);

			$fixed = $this->roundTo64($total, self::HARD_CAP);
			if ($fixed !== $total)
				$this->distributeToTarget($fixed, $plans);
		}
	}

	private function shrinkToCap(array $plans, int $cap): void
	{
		$total = 0;
		foreach ($plans as $p)
			$total += array_sum($p[0]);

		$over = max(0, $total - $cap);
		if ($over <= 0)
			return;

		foreach (array_reverse($plans, true) as $tuple) {
			[$ref, $maxPerEntity] = $tuple;
			$map = &$ref;

			foreach ($map as $k => $v) {
				while ($v > 1 && $over > 0) {
					$v--;
					$over--;
				}
				$map[$k] = $v;
				if ($over <= 0)
					return;
			}
		}
	}

	private function roundTo64(int $rawTotal, int $cap): int
	{
		$rawTotal = max(0, $rawTotal);
		$cap = max(0, $cap);

		$up = $rawTotal;
		$r = $rawTotal % 64;
		if ($r !== 0)
			$up = $rawTotal + (64 - $r);

		if ($up <= $cap)
			return $up;

		$down = $rawTotal - ($rawTotal % 64);
		return max(0, min($down, $cap - ($cap % 64)));
	}

	private function fetchIdsRaw(string $table): array
	{
		if (!Schema::hasTable($table))
			return [];

		try {
			$rows = DB::select("select id from {$table}");
			return collect($rows)->map(fn($r) => (string) ($r->id ?? ''))->filter(fn($v) => $v !== '')->values()->all();
		} catch (\Throwable $e) {
			Log::error('BudgetSeeder: failed fetching ids', [
				'table' => $table,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function fetchUserIdsByTypesRaw(array $types): array
	{
		if (!Schema::hasTable(DC::TABLE_USERS))
			return [];

		$types = collect($types)->filter(fn($v) => is_string($v) && trim($v) !== '')->unique()->values()->all();
		if (!$types)
			return [];

		try {
			$placeholders = implode(',', array_fill(0, count($types), '?'));
			$rows = DB::select("select id from " . DC::TABLE_USERS . " where type in ({$placeholders})", $types);
			return collect($rows)->map(fn($r) => (string) ($r->id ?? ''))->filter(fn($v) => $v !== '')->values()->all();
		} catch (\Throwable $e) {
			Log::error('BudgetSeeder: failed fetching user ids by type', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'types' => $types,
			]);
			return [];
		}
	}

	private function sampleIds(array $ids, float $ratio): array
	{
		$ratio = max(0.0, min(1.0, $ratio));
		$ids = collect($ids)->filter(fn($v) => is_string($v) && trim($v) !== '')->values();

		if ($ids->isEmpty())
			return [];

		$sampled = $ids->filter(fn() => (random_int(1, 1000) / 1000) <= $ratio)->values();

		if ($sampled->isEmpty())
			$sampled = $ids->take(1)->values();

		return $sampled->all();
	}

	private function existsRaw(string $sql, array $bindings = []): bool
	{
		try {
			return DB::selectOne($sql, $bindings) !== null;
		} catch (\Throwable $e) {
			Log::error('BudgetSeeder: existsRaw failed', [
				'sql' => $sql,
				'bindings' => $bindings,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return false;
		}
	}

	private function maybeNull(float $p): bool
	{
		$p = max(0.0, min(1.0, $p));
		return (random_int(1, 1000) / 1000) <= $p;
	}

	private function pickId(array $ids): ?string
	{
		if (!$ids)
			return null;

		$idx = array_rand($ids);
		$v = (string) ($ids[$idx] ?? '');
		return $v !== '' ? $v : null;
	}

	private function pickSomeIds(array $ids, int $min, int $max): ?array
	{
		$ids = collect($ids)->filter(fn($v) => is_string($v) && trim($v) !== '')->values()->all();
		if (!$ids)
			return null;

		$min = max(0, $min);
		$max = max($min, $max);

		$n = random_int($min, $max);
		if ($n <= 0)
			return null;

		$n = min($n, count($ids));

		$picked = (new Collection($ids))
			->shuffle()
			->take($n)
			->unique()
			->values()
			->all();

		return $picked ?: null;
	}

	private function pickCurrency(): string
	{
		$pool = ['BRL', 'USD', 'EUR', 'GBP', 'CAD'];
		return $this->faker->randomElement($pool) ?: SC::DEF_SITE_CURRENCY_ID;
	}

	private function encodeJson(mixed $value): ?string
	{
		if ($value === null)
			return null;

		if (is_string($value)) {
			$t = trim($value);
			if ($t === '')
				return null;
			$first = $t[0] ?? '';
			if ($first === '{' || $first === '[')
				return $t;
			$value = [$t];
		}

		if (!is_array($value) && !is_object($value))
			$value = [$value];

		try {
			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch (\Throwable $e) {
			Log::warning('BudgetSeeder: encodeJson failed; fallback applied', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return json_encode((array) $value);
		}
	}
}
