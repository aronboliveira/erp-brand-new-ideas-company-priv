<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, PlanningScheduleType};
use App\Models\PlanningSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class PlanningScheduleSeeder extends Seeder
{
	private const SECONDS_LIMIT = 3 * 10 ** 2;
	private ConsoleOutput $out;

	private array $tableIdCache = [];

	public function run(): void
	{
		$clock = microtime(true);
		$this->out = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_PLN_SCHD)) {
			$this->out->writeln('[PlanningScheduleSeeder] Skipped: planning_schedules table not found.');
			return;
		}

		$userIds = $this->loadUserIds(8192);
		$contractIds = Schema::hasTable(DC::TABLE_CONTRACTS) ? $this->loadIds(DC::TABLE_CONTRACTS, 16384) : [];

		$rawTotal = $contractIds !== []
			? (int) floor(count($contractIds) * 0.5) * 2
			: max(64, min(4096, count($userIds) * 2));

		$targetTotal = $this->toNext64Multiple(max(64, $rawTotal));
		$cap = 32000;
		$targetTotal > $cap && $targetTotal = $cap - ($cap % 64);

		$typeValues = array_column(PlanningScheduleType::cases(), 'value');
		$moduleValues = array_column(AppModuleType::cases(), 'value');
		$startModes = ['single', 'recurring'];

		$coverage = max(count($typeValues), count($moduleValues), count($startModes), 64);
		$coverage > $targetTotal && $coverage = $targetTotal;

		$created = 0;
		$now = Carbon::now();

		for ($i = 0; $i < $targetTotal; $i++) {
			if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
				$this->out->writeln('[PlanningScheduleSeeder] time limit reached, stopping early');
				return;
			}
			$tp = $typeValues[$i % max(1, count($typeValues))] ?? PlanningScheduleType::Other->value;
			$mt = $moduleValues[$i % max(1, count($moduleValues))] ?? AppModuleType::Other->value;
			$startMode = $startModes[$i % 2];

			$ownerId = $contractIds !== [] ? $contractIds[(int) floor($i / 2) % count($contractIds)] : null;
			$creator = $userIds !== [] ? $userIds[$i % count($userIds)] : null;

			$sDate = $now->copy()->subDays(random_int(0, 180))->addDays(random_int(0, 365))->startOfDay();
			$sTime = sprintf('%02d:%02d:%02d', random_int(0, 23), random_int(0, 59), random_int(0, 59));

			$withEnd = random_int(0, 2) !== 0;
			$eDate = $withEnd ? $sDate->copy()->addDays(random_int(0, 90)) : null;
			$eTime = $withEnd ? sprintf('%02d:%02d:%02d', random_int(0, 23), random_int(0, 59), random_int(0, 59)) : null;

			$title = $ownerId ? ('PlanningSchedule for ' . substr($ownerId, 0, 8)) : ('PlanningSchedule ' . strtoupper(substr(Str::uuid()->toString(), 0, 8)));
			$note = random_int(0, 3) === 0 ? null : 'Mock planning schedule note ' . random_int(1, 9999);

			$events = $this->maybeSampleIds($this->constTable('TABLE_EVENTS'), random_int(0, 3));
			$meetings = $this->maybeSampleIds($this->firstExistingConstTable(['TABLE_MEETINGS', 'TABLE_ZM_MT']), random_int(0, 3));
			$tasks = $this->maybeSampleIds($this->firstExistingConstTable(['TABLE_TASKS', 'TABLE_PROJ_TSKS']), random_int(0, 4));
			$todos = $this->maybeSampleIds($this->constTable('TABLE_USR_TD'), random_int(0, 4));
			$timesheets = $this->maybeSampleIds($this->constTable('TABLE_TMS'), random_int(0, 3));
			$notifications = $this->maybeSampleIds($this->constTable('TABLE_NTF'), random_int(0, 3));
			$interviews = $this->maybeSampleIds($this->constTable('TABLE_ITV_SCD'), random_int(0, 2));
			$payments = $this->maybeSampleIds($this->firstExistingConstTable([
				'TABLE_PAY',
				'TABLE_POS_PAY',
				'TABLE_INV_PAY',
				'TABLE_BL_PAY',
				'TABLE_TRS',
				'TABLE_BNK_TRF',
				'TABLE_PRJ_INV',
				'TABLE_PRC_PAY',
			]), random_int(0, 3));
			$stages = $this->maybeSampleIds($this->firstExistingConstTable(['TABLE_GL_TRK', 'TABLE_TSK_STGS', 'TABLE_TM_TRK']), random_int(0, 3));
			$milestones = $this->maybeSampleIds($this->constTable('TABLE_MSS'), random_int(0, 3));
			$reports = $this->maybeSampleIds($this->firstExistingConstTable(['TABLE_STK_RPT', 'TABLE_DOCS']), random_int(0, 3));

			$mi = $ownerId ? $ownerId : (random_int(0, 2) === 0 ? null : (string) Str::uuid());

			$this->out->writeln("[PlanningScheduleSeeder] Creating: tp={$tp} start={$startMode} mt={$mt} s={$sDate->format('Y-m-d')} {$sTime} e=" . ($eDate ? $eDate->format('Y-m-d') : 'null') . ' ' . ($eTime ?? 'null'));

			try {
				PlanningSchedule::create([
					'title' => $title,
					'note' => $note,
					AC::COL_MT => $mt,
					AC::COL_MI => $mi,
					AC::COL_SCHD_TP => $tp,
					PJC::COL_S_DT => $sDate->format('Y-m-d'),
					AC::COL_ST_TIME => $sTime,
					PJC::COL_E_DT => $eDate ? $eDate->format('Y-m-d') : null,
					AC::COL_E_TIME => $eTime,
					'start' => $startMode,

					'events' => $events,
					'meetings' => $meetings,
					'tasks' => $tasks,
					'todos' => $todos,
					'timesheets' => $timesheets,
					'notifications' => $notifications,
					'interviews' => $interviews,
					'payments' => $payments,
					'stages' => $stages,
					'milestones' => $milestones,
					'reports' => $reports,

					DC::COL_TABLE_CREATOR => $creator,
					DC::COL_TABLE_UPDATER => null,
				]);
				$created++;
			} catch (\Throwable $e) {
				Log::error(static::class . ' failed creating PlanningSchedule', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'index' => $i,
				]);
			}
		}

		$created % 64 !== 0 && $this->out->writeln('[PlanningScheduleSeeder] Warning: created count not multiple of 64: ' . $created);
		$this->out->writeln('[PlanningScheduleSeeder] Done. Created: ' . $created);
	}

	private function loadUserIds(int $limit): array
	{
		return Schema::hasTable(DC::TABLE_USERS) ? $this->loadIds(DC::TABLE_USERS, $limit) : [];
	}

	private function loadIds(string $table, int $limit): array
	{
		try {
			$rows = DB::select('select id from ' . $table . ' limit ' . (int) $limit);
			$out = [];
			foreach ($rows as $r) {
				$id = is_object($r) && property_exists($r, 'id') ? (string) $r->id : '';
				$id !== '' && $out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed loading ids', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
			]);
			return [];
		}
	}

	private function maybeSampleIds(?string $table, int $n): ?array
	{
		if ($n <= 0 || !$table) return null;
		if (!Schema::hasTable($table)) return null;

		if (!array_key_exists($table, $this->tableIdCache))
			$this->tableIdCache[$table] = $this->loadIds($table, 512);

		$pool = $this->tableIdCache[$table] ?? [];
		if ($pool === []) return null;

		$max = min($n, count($pool));
		$pick = [];
		$attempts = 0;

		while (count($pick) < $max && $attempts < 64) {
			$attempts++;
			$pick[] = $pool[random_int(0, count($pool) - 1)];
			$pick = array_values(array_unique($pick));
		}

		return $pick === [] ? null : $pick;
	}

	private function constTable(string $const): ?string
	{
		return defined(DC::class . '::' . $const) ? constant(DC::class . '::' . $const) : null;
	}

	private function firstExistingConstTable(array $consts): ?string
	{
		foreach ($consts as $c) {
			$t = $this->constTable((string) $c);
			if ($t && Schema::hasTable($t)) return $t;
		}
		return null;
	}

	private function toNext64Multiple(int $n): int
	{
		$r = $n % 64;
		return $r === 0 ? $n : ($n + (64 - $r));
	}
}
