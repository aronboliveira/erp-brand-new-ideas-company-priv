<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, SupportsConstants as SC};
use App\Enums\{AppModuleType, CaseStatus, PriorityLevel, Visibility};
use App\Models\Support;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Faker\Factory as FakerFactory;

class SupportSeeder extends Seeder
{
	private ConsoleOutput $out;
	private array $tasksIds;
	private array $emailsIds;
	private array $notificationsIds;

	// private const HARD_CAP = 3200;
	private const HARD_CAP = 2;
	private const MULTIPLE = 64;

	private const CLIENT_ITER_MIN = 1;
	private const CLIENT_ITER_MAX = 16;

	private const MODULE_ITER_MIN = 1;
	private const MODULE_ITER_MAX = 16;

	private const UNIQUE_TKT_ATTEMPTS = 40;
	private const PICK_ATTEMPTS = 80;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	private $counter = 0;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
		$this->tasksIds = DB::table(DC::TABLE_TASKS)
			->selectRaw('id')
			->pluck('id')
			->all();
		$this->emailsIds = DB::table(DC::TABLE_EMAILS)
			->selectRaw('id')
			->pluck('id')
			->all();
		$this->notificationsIds = DB::table(DC::TABLE_NTF)
			->selectRaw('id')
			->pluck('id')
			->all();
	}

	public function run(): void
	{
		$clock = microtime(true);
		$faker = FakerFactory::create(config('app.faker_locale') ?? 'en_US');

		$clientIds = DB::table(DC::TABLE_CLIENTS)->selectRaw('id')->pluck('id')->all();
		$userIds   = DB::table(DC::TABLE_USERS)->selectRaw('id')->pluck('id')->all();
		$bugIds    = DB::table(DC::TABLE_BUGS)->selectRaw('id')->pluck('id')->all();

		if (empty($clientIds) || empty($userIds)) {
			$this->out->writeln('<comment>[SupportSeeder]</comment> Skipped: missing clients or users.');
			return;
		}

		$moduleCases = AppModuleType::cases();

		$priorityValues = array_values(array_filter(
			array_map(fn(PriorityLevel $c) => $c->value, PriorityLevel::cases()),
			fn(string $v) => $v !== PriorityLevel::None->value
		));

		$visibilityValues = array_values(array_map(fn(Visibility $c) => $c->value, Visibility::cases()));
		$statusCases = CaseStatus::cases();
		$statusValues = array_values(array_map(fn(CaseStatus $c) => $c->value, $statusCases));

		$bugCounts = [];
		foreach ($bugIds as $id) $bugCounts[(string) $id] = 0;

		$minBugCoverage = count($bugIds) > 0 ? (int) floor(count($bugIds) / 2) : 0;
		$requiredBugQueue = $minBugCoverage > 0 ? array_slice($bugIds, 0, $minBugCoverage) : [];

		$created = 0;
		$rawTarget = 0;

		$clientIterationMap = [];
		foreach ($clientIds as $cid) $clientIterationMap[(string) $cid] = random_int(self::CLIENT_ITER_MIN, self::CLIENT_ITER_MAX);

		$moduleIterationMap = [];
		foreach ($moduleCases as $m) $moduleIterationMap[$m->value] = random_int(self::MODULE_ITER_MIN, self::MODULE_ITER_MAX);

		foreach ($clientIds as $clientId) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('[SupportSeeder] time limit reached, stopping seeding process.');
				return;
			}
			$clientIter = $clientIterationMap[(string) $clientId] ?? self::CLIENT_ITER_MIN;

			for ($ci = 0; $ci < $clientIter; $ci++) {
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					$this->out->writeln('[SupportSeeder] time limit reached, stopping seeding process.');
					return;
				}
				foreach ($moduleCases as $module) {
					if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
						$this->out->writeln('[SupportSeeder] time limit reached, stopping seeding process.');
						return;
					}
					$moduleIter = $moduleIterationMap[$module->value] ?? self::MODULE_ITER_MIN;

					for ($mi = 0; $mi < $moduleIter; $mi++) {
						if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
							$this->out->writeln('[SupportSeeder] time limit reached, stopping seeding process.');
							return;
						}
						if ($created >= self::HARD_CAP) break 3;

						$rawTarget++;

						$this->createOneSupport(
							$faker,
							(string) $clientId,
							(string) $this->pickOne($userIds),
							(string) $this->pickOne($userIds),
							$module->value,
							$priorityValues,
							$visibilityValues,
							$statusCases,
							$statusValues,
							$userIds,
							$bugIds,
							$bugCounts,
							$requiredBugQueue
						);

						$created++;
					}
				}
			}
		}

		if ($created < $minBugCoverage) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('[SupportSeeder] time limit reached, stopping seeding process.');
				return;
			}
			$need = min($minBugCoverage - $created, self::HARD_CAP - $created);
			for ($i = 0; $i < $need; $i++) {
				$this->createOneSupport(
					$faker,
					(string) $this->pickOne($clientIds),
					(string) $this->pickOne($userIds),
					(string) $this->pickOne($userIds),
					$this->pickOne($moduleCases)->value,
					$priorityValues,
					$visibilityValues,
					$statusCases,
					$statusValues,
					$userIds,
					$bugIds,
					$bugCounts,
					$requiredBugQueue
				);
				$created++;
				if ($created >= self::HARD_CAP) break;
			}
		}

		$pad = $this->padToMultiple($created, self::MULTIPLE);
		$pad = min($pad, self::HARD_CAP - $created);

		for ($i = 0; $i < $pad; $i++) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('[SupportSeeder] time limit reached, stopping seeding process.');
				return;
			}
			$this->createOneSupport(
				$faker,
				(string) $this->pickOne($clientIds),
				(string) $this->pickOne($userIds),
				(string) $this->pickOne($userIds),
				$this->pickOne($moduleCases)->value,
				$priorityValues,
				$visibilityValues,
				$statusCases,
				$statusValues,
				$userIds,
				$bugIds,
				$bugCounts,
				$requiredBugQueue
			);
			$created++;
			if ($created >= self::HARD_CAP) break;
		}

		$final = $created;
		$mod = $final % self::MULTIPLE;

		$this->out->writeln('<info>[SupportSeeder]</info> Done.');
		$this->out->writeln(' - raw planned variations: ' . $rawTarget);
		$this->out->writeln(' - created supports: ' . $final . ' (mod 64 = ' . $mod . ')');
		$this->out->writeln(' - bug coverage target: ' . $minBugCoverage . ' / ' . count($bugIds));
	}

	private function createOneSupport(
		$faker,
		string $clientId,
		string $requesterUserId,
		string $creatorUserId,
		string $moduleValue,
		array $priorityValues,
		array $visibilityValues,
		array $statusCases,
		array $statusValues,
		array $userIds,
		array $bugIds,
		array &$bugCounts,
		array &$requiredBugQueue
	): void {
		$priority = $this->pickOne($priorityValues);
		$visibility = $this->pickOne($visibilityValues);

		$statusLabel = $this->pickOne($statusValues);
		$statusIndex = (string) $this->statusIndexOf($statusCases, $statusLabel);

		$start = $faker->dateTimeBetween('-120 days', 'now')->format('Y-m-d');
		$end = $faker->dateTimeBetween($start, '+60 days')->format('Y-m-d');

		$assignedTo = $faker->boolean(55) ? (string) $this->pickOne($userIds) : null;
		$assignedBy = $assignedTo ? (string) $this->pickOne($userIds) : null;
		$assignedAt = $assignedTo ? $faker->dateTimeBetween($start, $end)->format('Y-m-d') : null;

		$submittedBy = $faker->boolean(75) ? (string) $this->pickOne($userIds) : null;
		$submittedAt = $submittedBy ? $faker->dateTimeBetween($start, $end)->format('Y-m-d') : null;

		$isResolved = in_array($statusLabel, [CaseStatus::Resolved->value, CaseStatus::Closed->value], true);
		$solvedAt = $isResolved ? $faker->dateTimeBetween($start, $end)->format('Y-m-d') : null;

		$isClosed = in_array($statusLabel, [CaseStatus::Closed->value, CaseStatus::Archived->value, CaseStatus::Deleted->value], true);
		$closedBy = $isClosed ? (string) $this->pickOne($userIds) : null;
		$closedAt = $isClosed ? $faker->dateTimeBetween($start, $end)->format('Y-m-d') : null;

		$ticketCode = $faker->boolean(90) ? $this->uniqueTicketCode($faker) : null;

		$bugId = $this->pickBugId($bugIds, $bugCounts, $requiredBugQueue);
		if ($bugId !== null) $bugCounts[(string) $bugId] = ($bugCounts[(string) $bugId] ?? 0) + 1;

		$attachment = null;
		if ($faker->boolean(35)) {
			$attachment = $faker->boolean(50)
				? ('https://example.com/files/' . $faker->uuid . '.pdf')
				: ('storage/support/' . $faker->uuid . '.png');
		}

		$otherAttachments = $faker->boolean(20)
			? [
				'https://example.com/files/' . $faker->uuid . '.log',
				'storage/support/' . $faker->uuid . '.txt',
			]
			: null;

		$otherVendors = null;

		$subject = $faker->sentence(6);
		$desc = $faker->boolean(85) ? $faker->paragraphs(random_int(1, 3), true) : null;

		$payload = [
			'id' => $faker->uuid,

			SC::COL_SBJ => mb_substr($subject, 0, 1024),
			'module' => $faker->boolean(92) ? $moduleValue : null,

			SC::COL_TKT_CR => $creatorUserId,
			SC::COL_TKT_CD => $ticketCode,

			SC::COL_USR => $requesterUserId,
			'client' => $clientId,

			'priority' => $priority,
			'description' => $desc,
			'visibility' => $visibility,

			PJC::COL_S_DT => $start,
			PJC::COL_E_DT => $end,

			'status' => $statusIndex,
			SC::COL_STT_LB => $statusLabel,

			SC::COL_REOPEN_CT => $faker->boolean(10) ? random_int(1, 5) : 0,
			SC::COL_SVD_AT => $solvedAt,

			PJC::COL_SBM_BY => $submittedBy,
			PJC::COL_SBM_AT => $submittedAt,

			SC::COL_ASG_BY => $assignedBy,
			SC::COL_ASG_TO => $assignedTo,
			SC::COL_ASG_AT => $assignedAt,

			SC::COL_CLSD_BY => $closedBy,
			SC::COL_CLSD_AT => $closedAt,

			AC::COL_TTL_TIME => $faker->boolean(70) ? random_int(5, 8 * 60) : 0,

			'email' => $faker->boolean(30) ? (string) $this->pickOne($this->emailsIds) : null,
			'notification' => $faker->boolean(15) ? (string) $this->pickOne($this->notificationsIds) : null,
			'task' => $faker->boolean(8) ? (string) $this->pickOne($this->tasksIds) : null,
			'bug' => $bugId,

			SC::COL_ATC => $attachment,
			SC::COL_OTHER_ATTACHMENTS => $otherAttachments,
			SC::COL_OTHER_VENDORS => $otherVendors,

			DC::COL_TABLE_CREATOR => $creatorUserId,
		];
		$this->out->writeln(sprintf(
			'<comment>[SupportSeeder]</comment> (%d) support: client=%s module=%s prio=%s status=%s bug=%s tkt=%s',
			++$this->counter,
			$clientId,
			$payload['module'] ?? 'null',
			$priority,
			$statusLabel,
			$bugId ?? 'null',
			$ticketCode ?? 'null'
		));

		Model::unguarded(function () use ($payload): void {
			Support::query()->create($payload);
		});
	}

	private function uniqueTicketCode($faker): string
	{
		for ($i = 0; $i < self::UNIQUE_TKT_ATTEMPTS; $i++) {
			$code = 'SUP-TKT-' . strtoupper(substr(str_replace('-', '', $faker->uuid), 0, 12)) . '-' . now()->format('Ymd');

			$exists = DB::table(DC::TABLE_SUPPORTS)
				->selectRaw('1')
				->where(SC::COL_TKT_CD, $code)
				->limit(1)
				->exists();

			if (!$exists) return $code;
		}

		return 'SUP-TKT-' . strtoupper(substr(str_replace('-', '', $faker->uuid), 0, 12)) . '-' . now()->format('Ymd');
	}

	private function pickBugId(array $bugIds, array $bugCounts, array &$requiredBugQueue): ?string
	{
		if (empty($bugIds)) return null;

		if (!empty($requiredBugQueue)) {
			$bugId = array_shift($requiredBugQueue);
			return $bugId ? (string) $bugId : null;
		}

		for ($i = 0; $i < self::PICK_ATTEMPTS; $i++) {
			if (random_int(1, 100) > 55) return null;

			$bugId = (string) $this->pickOne($bugIds);
			$ct = (int) ($bugCounts[$bugId] ?? 0);
			if ($ct < 8) return $bugId;
		}

		return null;
	}

	private function pickOne(array $values)
	{
		$idx = array_rand($values);
		return $values[$idx];
	}

	private function statusIndexOf(array $cases, string $value): int
	{
		$max = count($cases) - 1;
		for ($i = 0; $i <= $max; $i++) {
			$case = $cases[$i] ?? null;
			if ($case && $case->value === $value) return $i;
		}
		return 0;
	}

	private function padToMultiple(int $current, int $multiple): int
	{
		$mod = $current % $multiple;
		if ($mod === 0) return 0;
		return ($multiple - $mod) % $multiple;
	}
}
