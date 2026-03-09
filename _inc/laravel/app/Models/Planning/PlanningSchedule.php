<?php

namespace App\Models;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Enums\{AppModuleType, PlanningScheduleType};
use App\Traits\{DefinesDates, HasAuditFields, NormalizesArrays, PlansWithSchedule, UsesUuids};
use Carbon\{Carbon};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Factories\{HasFactory};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{DB, Log, Schema};

class PlanningSchedule extends Model
{
	use UsesUuids, HasFactory, HasAuditFields, NormalizesArrays, DefinesDates, PlansWithSchedule;

	protected $table = DC::TABLE_PLN_SCHD;

	protected $guarded = ['id', DC::COL_TABLE_CREATOR];

	protected $fillable = [
		'code',
		'title',
		'note',
		AC::COL_MT,
		AC::COL_MI,
		AC::COL_SCHD_TP,
		PJC::COL_S_DT,
		AC::COL_ST_TIME,

		PJC::COL_E_DT,
		AC::COL_E_TIME,
		'start',

		'events',
		'meetings',
		'tasks',
		'todos',
		'timesheets',
		'notifications',
		'interviews',
		'payments',
		'stages',
		'milestones',
		'reports',
	];

	protected $casts = [
		AC::COL_MT => AppModuleType::class,
		AC::COL_SCHD_TP => PlanningScheduleType::class,
		PJC::COL_S_DT => 'date',
		PJC::COL_E_DT => 'date',
		AC::COL_ST_TIME => 'datetime:H:i:s',
		AC::COL_E_TIME => 'datetime:H:i:s',

		'events' => 'array',
		'meetings' => 'array',
		'tasks' => 'array',
		'todos' => 'array',
		'timesheets' => 'array',
		'notifications' => 'array',
		'interviews' => 'array',
		'payments' => 'array',
		'stages' => 'array',
		'milestones' => 'array',
		'reports' => 'array',
	];

	protected $appends = [
		'start_at',
		'end_at',
		'duration_minutes',
		'linked_counts',
	];

	protected static function booted(): void
	{
		static::saving(function (Model $m): void {
			if (!$m instanceof self) return;

			try {
				self::ensureCode($m);
				self::ensureScheduleType($m);
				self::ensureStartMode($m);
				self::ensureTitle($m);
				self::normalizeJsonLists($m);
				self::enforceTemporalOrder($m);
			} catch (\Throwable $e) {
				Log::error(static::class . ' failed normalizing PlanningSchedule', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'table' => $m->getTable(),
					'model_id' => $m->getKey(),
				]);
			}
		});
	}

	public function getStartAtAttribute(): ?Carbon
	{
		return $this->buildAt(PJC::COL_S_DT, AC::COL_ST_TIME);
	}

	public function getEndAtAttribute(): ?Carbon
	{
		return $this->buildAt(PJC::COL_E_DT, AC::COL_E_TIME);
	}

	public function getDurationMinutesAttribute(): ?int
	{
	    try {
    		$s = $this->getAttribute('start_at');
    		$e = $this->getAttribute('end_at');
    		if (!$s instanceof Carbon || !$e instanceof Carbon) return null;
    		if ($e->lt($s)) return 0;
    		return $s->diffInMinutes($e);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::getDurationMinutesAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return 0;
	    }
	}

	public function getLinkedCountsAttribute(): array
	{
	    try {
    		$cols = [
    			'events',
    			'meetings',
    			'tasks',
    			'todos',
    			'timesheets',
    			'notifications',
    			'interviews',
    			'payments',
    			'stages',
    			'milestones',
    			'reports',
    		];

    		$out = [];
    		foreach ($cols as $c) {
    			$v = $this->getAttribute($c);
    			$out[$c] = is_array($v) ? count($v) : 0;
    		}
    		return $out;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::getLinkedCountsAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	public function hasAnyLinks(): bool
	{
	    try {
    		$counts = $this->getAttribute('linked_counts');
    		if (!is_array($counts) || $counts === []) return false;
    		foreach ($counts as $n) if (is_int($n) && $n > 0) return true;
    		return false;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::hasAnyLinks — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return false;
	    }
	}

	private function buildAt(string $dateCol, string $timeCol): ?Carbon
	{
	    try {
    		$d = $this->getAttribute($dateCol);
    		$t = $this->getAttribute($timeCol);

    		if ($d === null && $t === null) return null;

    		$dd = self::tryParseCarbon(is_scalar($d) ? (string) $d : ($d instanceof \DateTimeInterface ? $d->format('Y-m-d') : ''));
    		if (!$dd && $d instanceof \DateTimeInterface) $dd = Carbon::instance($d)->startOfDay();
    		if (!$dd) return null;

    		$tt = null;
    		if ($t instanceof \DateTimeInterface) $tt = Carbon::instance($t);
    		elseif (is_scalar($t)) $tt = self::tryParseCarbon('1970-01-01 ' . trim((string) $t)) ?? self::tryParseCarbon(trim((string) $t));

    		if (!$tt) return $dd->copy()->startOfDay();

    		return $dd->copy()->setTime((int) $tt->format('H'), (int) $tt->format('i'), (int) $tt->format('s'));
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::buildAt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return null;
	    }
	}

	private static function ensureTitle(self $m): void
	{
	    try {
    		$v = $m->getAttribute('title');
    		$s = is_scalar($v) ? trim((string) $v) : '';
    		$s === '' && $m->setAttribute('title', 'Planning Schedule ' . substr((string) ($m->getKey() ?? Str::uuid()), 0, 8));
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::ensureTitle — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private static function ensureScheduleType(self $m): void
	{
	    try {
    		$v = $m->getAttribute(AC::COL_SCHD_TP);
    		if ($v instanceof PlanningScheduleType) return;

    		$s = is_scalar($v) ? trim((string) $v) : '';
    		$e = $s !== '' ? PlanningScheduleType::normalize($s) : null;
    		$m->setAttribute(AC::COL_SCHD_TP, ($e ?? PlanningScheduleType::Other)->value);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::ensureScheduleType — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private static function ensureStartMode(self $m): void
	{
	    try {
    		$v = $m->getAttribute('start');
    		$s = is_scalar($v) ? strtolower(trim((string) $v)) : '';
    		$m->setAttribute('start', in_array($s, ['single', 'recurring'], true) ? $s : 'single');
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::ensureStartMode — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private static function normalizeJsonLists(self $m): void
	{
	    try {
    		foreach (
    			[
    				'events',
    				'meetings',
    				'tasks',
    				'todos',
    				'timesheets',
    				'notifications',
    				'interviews',
    				'payments',
    				'stages',
    				'milestones',
    				'reports',
    			] as $col
    		) {
    			if (!Schema::hasTable($m->getTable()) || !Schema::hasColumn($m->getTable(), $col)) continue;

    			$raw = $m->getAttribute($col);
    			$arr = self::normalizeArrayField($raw);

    			$out = [];
    			foreach ($arr as $v) {
    				if (!is_scalar($v)) continue;
    				$s = trim((string) $v);
    				$s === '' && $s = '';
    				if ($s === '') continue;
    				$out[] = $s;
    			}

    			$out = array_values(array_unique($out));
    			$m->setAttribute($col, $out === [] ? null : $out);
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeJsonLists — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private static function enforceTemporalOrder(self $m): void
	{
	    try {
    		$s = $m->getAttribute('start_at');
    		$e = $m->getAttribute('end_at');

    		if (!$s instanceof Carbon || !$e instanceof Carbon) return;

    		if ($e->lt($s)) {
    			$m->setAttribute(PJC::COL_E_DT, $m->getAttribute(PJC::COL_S_DT));
    			$m->setAttribute(AC::COL_E_TIME, $m->getAttribute(AC::COL_ST_TIME));
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::enforceTemporalOrder — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private static function ensureCode(self $m): void
	{
	    try {
    		$raw = $m->getAttribute('code');
    		$code = is_scalar($raw) ? trim((string) $raw) : '';

    		if ($code !== '' && preg_match('/^PLN\-SCHD\-[a-f0-9\-]{36}$/i', $code)) return;

    		if (!Schema::hasTable($m->getTable())) return;

    		$attempts = 0;
    		do {
    			$attempts++;
    			$candidate = 'PLN-SCHD-' . (string) Str::uuid();
    			$exists = false;
    			try {
    				$exists = DB::table($m->getTable())
    					->where('code', $candidate)
    					->where('id', '!=', (string) ($m->getKey() ?? ''))
    					->exists();
    			} catch (\Throwable $e) {
    				Log::warning(static::class . ' failed checking PlanningSchedule code uniqueness', [
    					'file' => $e->getFile(),
    					'line' => $e->getLine(),
    					'error' => $e->getMessage(),
    					'table' => $m->getTable(),
    					'model_id' => $m->getKey(),
    					'candidate' => $candidate,
    				]);
    				$exists = false;
    			}
    			if (!$exists) {
    				$m->setAttribute('code', $candidate);
    				return;
    			}
    		} while ($attempts < 32);

    		$m->setAttribute('code', 'PLN-SCHD-' . (string) Str::uuid());
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::ensureCode — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private static function tryParseCarbon(string $v): ?Carbon
	{
	    try {
    		$vv = trim($v);
    		if ($vv === '') return null;
    		try {
    			return Carbon::parse($vv);
    		} catch (\Throwable) {
    			return null;
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::tryParseCarbon — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return null;
	    }
	}
}
