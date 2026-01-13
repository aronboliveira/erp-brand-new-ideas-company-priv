<?php

namespace App\Models;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Enums\{AppModuleType, PlanningScheduleType};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
	Builder,
	Factories\HasFactory,
	Model,
	Relations\BelongsTo
};
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class Schedule extends Model
{
	use UsesUuids, HasAuditFields, HasFactory, DefinesDates;

	protected $table = DC::TABLE_SCHEDULES;

	protected $guarded = [
		'id',
		DC::COL_TABLE_CREATOR,
		DC::COL_TABLE_UPDATER,
	];

	protected $fillable = [
		'code',
		'title',
		'note',
		AC::COL_MT,
		AC::COL_MI,
		AC::COL_SCHD_TP,
		PJC::COL_S_DT,
		AC::COL_ST_TIME,
	];

	protected $casts = [
		AC::COL_MT       => AppModuleType::class,
		AC::COL_SCHD_TP   => PlanningScheduleType::class,
		PJC::COL_S_DT     => 'date',
	];

	protected $with = [
		'creator',
	];

	protected $appends = [
		'starts_at',
		'starts_at_iso',
	];

	private static array $schHasColCache = [];

	protected static function booted(): void
	{
		static::saving(function (Model $m): void {
			if (!$m instanceof self) return;

			try {
				self::normalizeScheduleRow($m);
				self::ensureCode($m);
			} catch (\Throwable $e) {
				Log::error(static::class . ' failed normalizing Schedule', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'table' => $m->getTable(),
					'model_id' => $m->getKey(),
				]);
			}
		});

		$scopeName = defined(DC::class . '::ORDER_NEW') ? DC::ORDER_NEW : 'order_new';
		static::addGlobalScope($scopeName, function (Builder $builder): void {
			$builder->orderByDesc('created_at');
		});
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
	}

	public function updater(): BelongsTo
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
	}

	public function getStartsAtAttribute(): ?Carbon
	{
		$date = $this->getAttribute(PJC::COL_S_DT);
		$time = $this->getAttribute(AC::COL_ST_TIME);

		$dateObj = self::tryParseCarbon($date);
		if (!$dateObj) return null;

		$timeObj = self::tryParseCarbon($time ? '1970-01-01 ' . trim((string) $time) : '');
		if (!$timeObj) return $dateObj->copy()->startOfDay();

		return $dateObj->copy()->setTime(
			(int) $timeObj->format('H'),
			(int) $timeObj->format('i'),
			(int) $timeObj->format('s')
		);
	}

	public function getStartsAtIsoAttribute(): ?string
	{
		$dt = $this->getAttribute('starts_at');
		return $dt instanceof Carbon ? $dt->toISOString() : null;
	}

	public function scopeForModule(Builder $q, AppModuleType|string|null $module): Builder
	{
		try {
			$enum = $module instanceof AppModuleType ? $module : AppModuleType::normalize($module);
			return $enum ? $q->where(AC::COL_MT, $enum->value) : $q;
		} catch (\Throwable $e) {
			Log::notice(static::class . ' invalid module filter', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'module' => $module,
			]);
			return $q;
		}
	}

	public function scopeForScheduleType(Builder $q, PlanningScheduleType|string|null $type): Builder
	{
		try {
			$enum = $type instanceof PlanningScheduleType ? $type : PlanningScheduleType::normalize($type);
			return $enum ? $q->where(AC::COL_SCHD_TP, $enum->value) : $q;
		} catch (\Throwable $e) {
			Log::notice(static::class . ' invalid schedule type filter', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'type' => $type,
			]);
			return $q;
		}
	}

	private static function normalizeScheduleRow(self $m): void
	{
		$title = trim((string) $m->getAttribute('title'));
		$title === '' && $title = 'Schedule';
		$m->setAttribute('title', mb_substr($title, 0, 1024));

		$note = $m->getAttribute('note');
		$note = is_scalar($note) ? trim((string) $note) : null;
		$note === '' && $note = null;
		$m->setAttribute('note', $note);

		$mi = $m->getAttribute(AC::COL_MI);
		$mi = is_scalar($mi) ? trim((string) $mi) : null;
		$mi === '' && $mi = null;
		$m->setAttribute(AC::COL_MI, $mi);

		try {
			$mod = $m->getAttribute(AC::COL_MT);
			$modEnum = $mod instanceof AppModuleType ? $mod : AppModuleType::normalize($mod);
			$m->setAttribute(AC::COL_MT, ($modEnum ?? AppModuleType::Other)->value);
		} catch (\Throwable $e) {
			Log::notice(static::class . ' invalid module value', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'module' => $m->getAttribute(AC::COL_MT),
				'model_id' => $m->getKey(),
			]);
			$m->setAttribute(AC::COL_MT, AppModuleType::Other->value);
		}

		try {
			$tp = $m->getAttribute(AC::COL_SCHD_TP);
			$tpEnum = $tp instanceof PlanningScheduleType ? $tp : PlanningScheduleType::normalize($tp);
			$m->setAttribute(AC::COL_SCHD_TP, ($tpEnum ?? PlanningScheduleType::Other)->value);
		} catch (\Throwable $e) {
			Log::notice(static::class . ' invalid schedule type value', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'schedule_type' => $m->getAttribute(AC::COL_SCHD_TP),
				'model_id' => $m->getKey(),
			]);
			$m->setAttribute(AC::COL_SCHD_TP, PlanningScheduleType::Other->value);
		}

		$sd = $m->getAttribute(PJC::COL_S_DT);
		if (is_scalar($sd) && trim((string) $sd) === '') $m->setAttribute(PJC::COL_S_DT, null);

		$st = $m->getAttribute(AC::COL_ST_TIME);
		if (is_scalar($st) && trim((string) $st) === '') $m->setAttribute(AC::COL_ST_TIME, null);
	}

	private static function ensureCode(self $m): void
	{
		$code = $m->getAttribute('code');
		$code = is_scalar($code) ? trim((string) $code) : '';

		app()->runningUnitTests() && $code === '' && $m->setAttribute('code', null);

		if ($code !== '' && self::codeMatchesPattern($code)) return;

		$id = (string) ($m->getAttribute('id') ?? '');
		if ($id === '') {
			$id = (string) Str::uuid();
			$m->setAttribute('id', $id);
		}

		$attempts = 0;
		$maxAttempts = 32;

		do {
			$attempts++;
			$candidate = 'SCHD-' . ($attempts === 1 ? $id : (string) Str::uuid());

			if ($attempts > $maxAttempts) {
				Log::warning(static::class . ' exceeded code generation attempts', [
					'model_id' => $m->getKey(),
					'table' => $m->getTable(),
					'last_candidate' => $candidate,
				]);
				$m->setAttribute('code', $candidate);
				return;
			}
		} while (self::codeExists($m, $candidate));

		$m->setAttribute('code', $candidate);
	}

	private static function codeExists(self $m, string $candidate): bool
	{
		$table = (string) $m->getTable();
		$table === '' && $table = DC::TABLE_SCHEDULES;

		try {
			if (!Schema::hasTable($table)) return false;
			if (!self::hasColumnCached($table, 'code')) return false;

			$id = (string) ($m->getKey() ?? '');
			$q = DB::table($table)->where('code', $candidate);
			$id !== '' && $q->where('id', '!=', $id);
			return $q->exists();
		} catch (\Throwable $e) {
			Log::notice(static::class . ' failed checking schedule code uniqueness', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
				'code' => $candidate,
				'model_id' => $m->getKey(),
			]);
			return false;
		}
	}

	private static function codeMatchesPattern(string $code): bool
	{
		return (bool) preg_match('/^SCHD\-[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/i', $code);
	}

	private static function hasColumnCached(string $table, string $col): bool
	{
		$k = $table . '::' . $col;
		if (array_key_exists($k, self::$schHasColCache)) return self::$schHasColCache[$k];

		try {
			return self::$schHasColCache[$k] = Schema::hasColumn($table, $col);
		} catch (\Throwable) {
			return self::$schHasColCache[$k] = false;
		}
	}

	private static function tryParseCarbon(mixed $v): ?Carbon
	{
		if ($v instanceof Carbon) return $v;
		if (!is_scalar($v)) return null;

		$s = trim((string) $v);
		if ($s === '') return null;

		try {
			return Carbon::parse($s);
		} catch (\Throwable) {
			return null;
		}
	}
}
