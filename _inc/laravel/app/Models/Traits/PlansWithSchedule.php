<?php

namespace App\Traits;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, PlanningScheduleType};
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Database\{Eloquent\Model, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

trait PlansWithSchedule
{
	protected static array $pwsColTypeCache = [];
	protected static array $pwsHasColCache = [];

	protected static function bootPlansWithSchedule(): void
	{
		// todo too heavy for testing, enable only in production
		// static::saving(function (Model $m): void {
		// 	try {
		// 		self::enforcePlanningScheduleBoundaries($m);
		// 	} catch (\Throwable $e) {
		// 		Log::error(static::class . ' PlansWithSchedule failed enforcing boundaries', [
		// 			'file' => $e->getFile(),
		// 			'line' => $e->getLine(),
		// 			'error' => $e->getMessage(),
		// 			'table' => $m->getTable(),
		// 			'model_id' => $m->getKey(),
		// 		]);
		// 	}
		// });
	}

	public function addScheduleColumns(Blueprint $table): void
	{
		$table->string('code')->unique()->nullable();
		$table->string('title', 1024)->index();
		$table->string('note')->nullable();
		$table->enum(AC::COL_MT, array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->nullable()->index();
		$table->string(AC::COL_MI)->index()->nullable();
		$table->enum(AC::COL_SCHD_TP, array_column(PlanningScheduleType::cases(), 'value'))->default(PlanningScheduleType::Other->value)->nullable()->index(); // ? enforced at model level to guard against null as well
		$table->date(PJC::COL_S_DT)->nullable();
		$table->time(AC::COL_ST_TIME)->nullable();
	}

	protected static function enforcePlanningScheduleBoundaries(Model $m): void
	{
		$table = (string) $m->getTable();
		if ($table === '' || !Schema::hasTable($table)) return;
		if (!Schema::hasTable(DC::TABLE_PLN_SCHD)) return;

		$fkCol = PJC::COL_PLN_SCHD_ID;
		if (!self::hasColumnCached($table, $fkCol)) return;

		$raw = $m->getAttribute($fkCol);
		$id = is_scalar($raw) ? trim((string) $raw) : '';
		if ($id === '') return;

		if (!Utility::looksLikeUuid($id)) {
			$m->setAttribute($fkCol, null);
			return;
		}

		$scheduleSelect = self::scheduleSelectColumns();
		if ($scheduleSelect === []) return;

		$row = null;
		try {
			$row = DB::table(DC::TABLE_PLN_SCHD)->where('id', $id)->first($scheduleSelect);
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed loading PlanningSchedule for boundary enforcement', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'pln_schd_id' => $id,
				'model_table' => $table,
				'model_id' => $m->getKey(),
			]);
			return;
		}

		if (!$row) {
			$m->setAttribute($fkCol, null);
			return;
		}

		$schStart = self::buildTemporalBoundary(
			self::readRowValue($row, PJC::COL_S_DT),
			self::colTypeCached(DC::TABLE_PLN_SCHD, PJC::COL_S_DT),
			self::readRowValue($row, AC::COL_ST_TIME),
			self::colTypeCached(DC::TABLE_PLN_SCHD, AC::COL_ST_TIME),
			start: true
		);

		$schEnd = self::buildTemporalBoundary(
			self::readRowValue($row, PJC::COL_E_DT),
			self::colTypeCached(DC::TABLE_PLN_SCHD, PJC::COL_E_DT),
			self::readRowValue($row, AC::COL_E_TIME),
			self::colTypeCached(DC::TABLE_PLN_SCHD, AC::COL_E_TIME),
			start: false
		);

		$entityStart = self::extractEntityBoundary($m, $table, start: true);
		$entityEnd = self::extractEntityBoundary($m, $table, start: false);

		$violates = false;

		$violates = $violates || self::compareStartBoundary($schStart, $entityStart);
		$violates = $violates || self::compareEndBoundary($schEnd, $entityEnd);

		if ($violates)
			$m->setAttribute($fkCol, null);
	}

	protected static function scheduleSelectColumns(): array
	{
		$out = ['id'];

		foreach ([PJC::COL_S_DT, PJC::COL_E_DT, AC::COL_ST_TIME, AC::COL_E_TIME] as $col)
			self::hasColumnCached(DC::TABLE_PLN_SCHD, $col) && $out[] = $col;

		return array_values(array_unique($out));
	}

	protected static function extractEntityBoundary(Model $m, string $table, bool $start): array
	{
		$dateCandidates = $start ? [PJC::COL_S_DT, 'from'] : [PJC::COL_E_DT, 'to'];
		$timeCandidates = $start ? [AC::COL_ST_TIME, 'from'] : [AC::COL_E_TIME, 'to'];

		$dateCol = self::firstTemporalColumn($table, $dateCandidates);
		$timeCol = self::firstTemporalColumn($table, $timeCandidates);

		$dateVal = $dateCol ? $m->getAttribute($dateCol) : null;
		$timeVal = ($timeCol && $timeCol !== $dateCol) ? $m->getAttribute($timeCol) : null;

		$dateType = $dateCol ? self::colTypeCached($table, $dateCol) : null;
		$timeType = $timeCol ? self::colTypeCached($table, $timeCol) : null;

		return self::buildTemporalBoundary($dateVal, $dateType, $timeVal, $timeType, start: $start);
	}

	protected static function firstTemporalColumn(string $table, array $candidates): ?string
	{
		foreach ($candidates as $col) {
			$col = trim((string) $col);
			if ($col === '') continue;
			if (!self::hasColumnCached($table, $col)) continue;
			$t = self::colTypeCached($table, $col);
			if (!self::isTemporalType($t)) continue;
			return $col;
		}
		return null;
	}

	protected static function compareStartBoundary(array $schedule, array $entity): bool
	{
		if (($entity['dt'] ?? null) instanceof Carbon && ($schedule['dt'] ?? null) instanceof Carbon)
			return $schedule['dt']->lt($entity['dt']);

		if (($entity['date'] ?? null) instanceof Carbon) {
			$sd = ($schedule['date'] ?? null) instanceof Carbon
				? $schedule['date']
				: (($schedule['dt'] ?? null) instanceof Carbon ? $schedule['dt']->copy()->startOfDay() : null);
			if ($sd instanceof Carbon) return $sd->lt($entity['date']);
		}

		$et = $entity['time_sec'] ?? null;
		$st = $schedule['time_sec'] ?? null;
		return is_int($et) && is_int($st) ? $st < $et : false;
	}

	protected static function compareEndBoundary(array $schedule, array $entity): bool
	{
		if (($entity['dt'] ?? null) instanceof Carbon && ($schedule['dt'] ?? null) instanceof Carbon)
			return $schedule['dt']->gt($entity['dt']);

		if (($entity['date'] ?? null) instanceof Carbon) {
			$sd = ($schedule['date'] ?? null) instanceof Carbon
				? $schedule['date']
				: (($schedule['dt'] ?? null) instanceof Carbon ? $schedule['dt']->copy()->startOfDay() : null);
			if ($sd instanceof Carbon) return $sd->gt($entity['date']);
		}

		$et = $entity['time_sec'] ?? null;
		$st = $schedule['time_sec'] ?? null;
		return is_int($et) && is_int($st) ? $st > $et : false;
	}

	protected static function buildTemporalBoundary(
		mixed $dateVal,
		?string $dateType,
		mixed $timeVal,
		?string $timeType,
		bool $start
	): array {
		$out = ['dt' => null, 'date' => null, 'time_sec' => null];

		$dateType = $dateType ? strtolower(trim($dateType)) : null;
		$timeType = $timeType ? strtolower(trim($timeType)) : null;

		$dateStr = is_scalar($dateVal) ? trim((string) $dateVal) : '';
		$timeStr = is_scalar($timeVal) ? trim((string) $timeVal) : '';

		$dt = null;

		if ($dateStr !== '' && in_array($dateType, ['datetime', 'timestamp', 'datetimetz'], true))
			$dt = self::tryParseCarbon($dateStr);

		if (!$dt && $dateStr !== '' && $timeStr !== '' && in_array($dateType, ['date'], true)) {
			$d = self::tryParseCarbon($dateStr);
			if ($d) {
				$t = self::tryParseCarbon('1970-01-01 ' . $timeStr) ?? self::tryParseCarbon($timeStr);
				if ($t) {
					$dt = $d->copy()->setTime((int) $t->format('H'), (int) $t->format('i'), (int) $t->format('s'));
				}
			}
		}

		if ($dt instanceof Carbon) {
			$out['dt'] = $dt;
			$out['date'] = $dt->copy()->startOfDay();
			$out['time_sec'] = ((int) $dt->format('H')) * 3600 + ((int) $dt->format('i')) * 60 + ((int) $dt->format('s'));
			return $out;
		}

		if ($dateStr !== '' && in_array($dateType, ['date'], true)) {
			$d = self::tryParseCarbon($dateStr);
			if ($d) $out['date'] = $d->copy()->startOfDay();
		}

		if ($timeStr !== '' && self::isTemporalType($timeType)) {
			$t = self::tryParseCarbon('1970-01-01 ' . $timeStr) ?? self::tryParseCarbon($timeStr);
			if ($t) $out['time_sec'] = ((int) $t->format('H')) * 3600 + ((int) $t->format('i')) * 60 + ((int) $t->format('s'));
		}

		return $out;
	}

	protected static function tryParseCarbon(string $v): ?Carbon
	{
		$vv = trim($v);
		if ($vv === '') return null;
		try {
			return Carbon::parse($vv);
		} catch (\Throwable) {
			return null;
		}
	}

	protected static function readRowValue(object $row, string $col): mixed
	{
		return property_exists($row, $col) ? $row->{$col} : null;
	}

	protected static function isTemporalType(?string $type): bool
	{
		$t = $type ? strtolower(trim($type)) : '';
		return in_array($t, ['date', 'datetime', 'timestamp', 'time', 'datetimetz', 'timetz'], true);
	}

	protected static function hasColumnCached(string $table, string $col): bool
	{
		$k = $table . '::' . $col;
		if (array_key_exists($k, self::$pwsHasColCache)) return self::$pwsHasColCache[$k];

		try {
			return self::$pwsHasColCache[$k] = Schema::hasColumn($table, $col);
		} catch (\Throwable) {
			return self::$pwsHasColCache[$k] = false;
		}
	}

	protected static function colTypeCached(string $table, string $col): ?string
	{
		$k = $table . '::' . $col;
		if (array_key_exists($k, self::$pwsColTypeCache)) return self::$pwsColTypeCache[$k];

		try {
			$t = Schema::getColumnType($table, $col);
			$t = is_string($t) ? strtolower(trim($t)) : null;
			return self::$pwsColTypeCache[$k] = $t;
		} catch (\Throwable) {
			return self::$pwsColTypeCache[$k] = null;
		}
	}
}
