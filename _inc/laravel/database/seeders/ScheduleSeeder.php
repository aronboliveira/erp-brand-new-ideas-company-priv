<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, PlanningScheduleType};
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ScheduleSeeder extends Seeder
{
	// private const HARD_CAP = 256;
	private const HARD_CAP = 4;
	// private const SECONDS_LIMIT = 300;
	private const SECONDS_LIMIT = 32;

	private ConsoleOutput $out;

	public function run(): void
	{
		$clock = microtime(true);
		$this->out = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_SCHEDULES)) {
			$this->out->writeln('[ScheduleSeeder] Skipped: schedules table not found.');
			return;
		}

		$userIds = $this->loadUserIds(64);
		$typeValues = array_column(PlanningScheduleType::cases(), 'value');
		$moduleValues = array_column(AppModuleType::cases(), 'value');

		$created = 0;
		$now = Carbon::now();

		for ($i = 0; $i < self::HARD_CAP; $i++) {
			if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
				$this->out->writeln('[ScheduleSeeder] Time limit reached (' . self::SECONDS_LIMIT . 's). Stopping.');
				break;
			}

			$tp = $typeValues[$i % max(1, count($typeValues))] ?? PlanningScheduleType::Other->value;
			$mt = $moduleValues[$i % max(1, count($moduleValues))] ?? AppModuleType::Other->value;

			$startDate = $now->copy()->subDays(random_int(0, 180))->addDays(random_int(0, 365))->startOfDay();
			$startTime = sprintf('%02d:%02d:%02d', random_int(0, 23), random_int(0, 59), random_int(0, 59));

			$title = 'Schedule ' . strtoupper(substr(Str::uuid()->toString(), 0, 8));
			$note = random_int(0, 3) === 0 ? null : 'Mock schedule note ' . random_int(1, 9999);

			$creator = $userIds !== [] ? $userIds[$i % count($userIds)] : null;
			$mi = random_int(0, 2) === 0 ? null : (string) Str::uuid();

			try {
				Schedule::create([
					'title' => $title,
					'note' => $note,
					AC::COL_MT => $mt,
					AC::COL_MI => $mi,
					AC::COL_SCHD_TP => $tp,
					PJC::COL_S_DT => $startDate->format('Y-m-d'),
					AC::COL_ST_TIME => $startTime,
					DC::COL_TABLE_CREATOR => $creator,
					DC::COL_TABLE_UPDATER => null,
				]);
				$created++;
			} catch (\Throwable $e) {
				Log::error(static::class . ' failed creating Schedule', [
					'error' => $e->getMessage(),
					'index' => $i,
				]);
			}
		}

		$elapsed = round(microtime(true) - $clock, 2);
		$this->out->writeln("[ScheduleSeeder] Done. Created: {$created} in {$elapsed}s");
	}

	private function loadUserIds(int $limit): array
	{
		if (!Schema::hasTable(DC::TABLE_USERS)) return [];
		try {
			$rows = DB::select('select id from ' . DC::TABLE_USERS . ' limit ' . (int) $limit);
			$out = [];
			foreach ($rows as $r) {
				$id = is_object($r) && property_exists($r, 'id') ? (string) $r->id : '';
				$id !== '' && $out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed loading user ids', [
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}
}
