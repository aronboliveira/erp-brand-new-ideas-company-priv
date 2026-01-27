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
	private ConsoleOutput $out;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_SCHEDULES)) {
			$this->out->writeln('[ScheduleSeeder] Skipped: schedules table not found.');
			return;
		}

		$userIds = $this->loadUserIds(4096);
		$rawTotal = max(64, min(4096, count($userIds) * 2));
		$targetTotal = $this->toNext64Multiple($rawTotal);
		$cap = 256;
		$targetTotal > $cap && $targetTotal = $cap - ($cap % 64);

		$typeValues = array_column(PlanningScheduleType::cases(), 'value');
		$moduleValues = array_column(AppModuleType::cases(), 'value');

		$coverage = max(count($typeValues), count($moduleValues), 2, 64);
		$coverage > $targetTotal && $coverage = $targetTotal;

		$created = 0;
		$now = Carbon::now();

		for ($i = 0; $i < $targetTotal; $i++) {
			$tp = $typeValues[$i % max(1, count($typeValues))] ?? PlanningScheduleType::Other->value;
			$mt = $moduleValues[$i % max(1, count($moduleValues))] ?? AppModuleType::Other->value;

			$startDate = $now->copy()->subDays(random_int(0, 180))->addDays(random_int(0, 365))->startOfDay();
			$startTime = sprintf('%02d:%02d:%02d', random_int(0, 23), random_int(0, 59), random_int(0, 59));

			$title = 'Schedule ' . strtoupper(substr(Str::uuid()->toString(), 0, 8));
			$note = random_int(0, 3) === 0 ? null : 'Mock schedule note ' . random_int(1, 9999);

			$creator = $userIds !== [] ? $userIds[$i % count($userIds)] : null;
			$mi = random_int(0, 2) === 0 ? null : (string) Str::uuid();

			$this->out->writeln("[ScheduleSeeder] Creating: tp={$tp} mt={$mt} s_dt={$startDate->format('Y-m-d')} st_time={$startTime}");

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
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'index' => $i,
				]);
			}
		}

		$created % 64 !== 0 && $this->out->writeln('[ScheduleSeeder] Warning: created count not multiple of 64: ' . $created);
		$this->out->writeln('[ScheduleSeeder] Done. Created: ' . $created);
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
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function toNext64Multiple(int $n): int
	{
		$r = $n % 64;
		return $r === 0 ? $n : ($n + (64 - $r));
	}
}
