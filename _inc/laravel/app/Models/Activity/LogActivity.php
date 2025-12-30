<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{ActivityType, AppModuleType};
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Support\Facades\{Cache, Log};

/**
 * ? Legacy model for `log_activities`. Should be used only for cheap logging of activities.
 */
class LogActivity extends Model
{
	use UsesUuids;
	use HasAuditFields;
	use NormalizesArrays;

	protected $table = DC::TABLE_LOG_ACTS;

	protected $guarded = [
		'id',
		DC::COL_TABLE_CREATOR,
	];

	protected $fillable = [
		AC::COL_MD,          // module
		AC::COL_MI,          // module_id
		'type',              // enum ActivityType
		PJC::COL_S_DT,       // start_date
		AC::COL_TSK_TIME,    // time
		'note',
		'metadata',
	];

	protected $with = [
		'creator',
	];

	protected $casts = [
		AC::COL_MD       => AppModuleType::class,
		'type'           => ActivityType::class,
		PJC::COL_S_DT    => 'date:Y-m-d',
		AC::COL_TSK_TIME => 'string',
		'metadata'       => 'array',
	];

	protected $appends = [
		'module_label',
		'module_icon',
		'module_color',
		'type_label',
		'type_action',
		'type_entity',
		'type_icon',
		'type_color',
	];

	protected static function booted(): void
	{
		static::saving(function (self $m): void {
			try {
				$m->normalizeModuleEnum();
				$m->normalizeTypeEnum();
				$m->normalizeStartDateField();
				$m->normalizeTimeField();
				$m->ensureJsonAttributesAreEncoded(['metadata']);
			} catch (\Throwable $e) {
				Log::error(self::class . ' failed normalizing before save', [
					'id'    => $m->getAttribute('id'),
					'error' => $e->getMessage(),
				]);
				throw $e;
			}
		});
	}

	private function normalizeModuleEnum(): void
	{
		$raw = $this->getAttribute(AC::COL_MD);
		if ($raw instanceof AppModuleType)
			$this->setAttribute(AC::COL_MD, $raw->value);
		else {
			$rawStr = is_scalar($raw) ? (string) $raw : null;
			$this->setAttribute(AC::COL_MD, $rawStr !== null && trim($rawStr) !== '' ? AppModuleType::normalize($rawStr)->value : null);
		}
	}

	private function normalizeTypeEnum(): void
	{
		$raw = $this->getAttribute('type');

		if ($raw instanceof ActivityType) {
			$this->setAttribute('type', $raw->value);
			return;
		}

		$rawStr = is_scalar($raw) ? (string) $raw : null;
		$normalized = ActivityType::normalize($rawStr) ?? ActivityType::Other;
		$this->setAttribute('type', $normalized->value);
	}

	private function normalizeStartDateField(): void
	{
		$raw = $this->getAttribute(PJC::COL_S_DT);

		if ($raw instanceof \DateTimeInterface) {
			$this->setAttribute(PJC::COL_S_DT, CarbonImmutable::instance(\DateTime::createFromInterface($raw))->format('Y-m-d'));
			return;
		}

		if (!is_scalar($raw)) {
			// Date is NOT nullable in migration; pick a deterministic fallback.
			$this->setAttribute(PJC::COL_S_DT, CarbonImmutable::now()->format('Y-m-d'));
			return;
		}

		$s = trim((string) $raw);
		if ($s === '') {
			$this->setAttribute(PJC::COL_S_DT, CarbonImmutable::now()->format('Y-m-d'));
			return;
		}

		try {
			$this->setAttribute(PJC::COL_S_DT, CarbonImmutable::parse($s)->format('Y-m-d'));
		} catch (\Throwable $e) {
			Log::warning(self::class . ' invalid start_date; falling back to today', [
				'id'         => $this->getAttribute('id'),
				'start_date' => $s,
				'error'      => $e->getMessage(),
			]);
			$this->setAttribute(PJC::COL_S_DT, CarbonImmutable::now()->format('Y-m-d'));
		}
	}

	private function normalizeTimeField(): void
	{
		$raw = $this->getAttribute(AC::COL_TSK_TIME);

		if (!is_scalar($raw)) {
			$this->setAttribute(AC::COL_TSK_TIME, '00:00:00');
			return;
		}

		$s = trim((string) $raw);
		if ($s === '') {
			$this->setAttribute(AC::COL_TSK_TIME, '00:00:00');
			return;
		}

		if (preg_match('/^\d{2}:\d{2}$/', $s)) {
			$this->setAttribute(AC::COL_TSK_TIME, $s . ':00');
			return;
		}

		if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $s)) {
			Log::warning(self::class . ' invalid time; falling back to 00:00:00', [
				'id'   => $this->getAttribute('id'),
				'time' => $s,
			]);
			$this->setAttribute(AC::COL_TSK_TIME, '00:00:00');
			return;
		}

		$this->setAttribute(AC::COL_TSK_TIME, $s);
	}

	public function getModuleLabelAttribute(): string
	{
		$module = $this->getAttribute(AC::COL_MD);
		$enum = $module instanceof AppModuleType ? $module : (is_string($module) ? AppModuleType::normalize($module) : AppModuleType::Other);
		return $enum->label();
	}

	public function getModuleIconAttribute(): string
	{
		$module = $this->getAttribute(AC::COL_MD);
		$enum = $module instanceof AppModuleType ? $module : (is_string($module) ? AppModuleType::normalize($module) : AppModuleType::Other);
		return $enum->getIcon();
	}

	public function getModuleColorAttribute(): string
	{
		$module = $this->getAttribute(AC::COL_MD);
		$enum = $module instanceof AppModuleType ? $module : (is_string($module) ? AppModuleType::normalize($module) : AppModuleType::Other);
		return $enum->getColor();
	}

	public function getTypeLabelAttribute(): string
	{
		$type = $this->typeEnum();
		$lang = config('app.locale') ?: DC::DEFAULT_LANG;
		$labels = ActivityType::labels($lang);
		return $labels[$type->value] ?? $type->value;
	}

	public function getTypeActionAttribute(): string
	{
		return $this->typeEnum()->getAction();
	}

	public function getTypeEntityAttribute(): string
	{
		return $this->typeEnum()->getEntity();
	}

	public function getTypeIconAttribute(): string
	{
		return $this->typeEnum()->getIcon();
	}

	public function getTypeColorAttribute(): string
	{
		return $this->typeEnum()->getColor();
	}

	public function typeEnum(): ActivityType
	{
		$v = $this->getAttribute('type');
		if ($v instanceof ActivityType)
			return $v;

		if (is_string($v) || is_int($v))
			return ActivityType::normalize($v) ?? ActivityType::Other;

		return ActivityType::Other;
	}

	public function moduleEnum(): ?AppModuleType
	{
		$v = $this->getAttribute(AC::COL_MD);
		if ($v instanceof AppModuleType)
			return $v;

		if (is_string($v) && trim($v) !== '')
			return AppModuleType::normalize($v);

		return null;
	}

	public function getMetadataSafe(): array
	{
		$raw = $this->getAttribute('metadata');
		return self::normalizeArrayField($raw);
	}

	public function setMetadataSafe(mixed $value): self
	{
		$this->setAttribute('metadata', self::normalizeArrayField($value));
		return $this;
	}

	public function scopeOfModule(Builder $q, AppModuleType|string|null $module): Builder
	{
		if ($module === null)
			return $q->whereNull(AC::COL_MD);

		$val = $module instanceof AppModuleType ? $module->value : AppModuleType::normalize((string) $module)->value;
		return $q->where(AC::COL_MD, $val);
	}

	public function scopeOfType(Builder $q, ActivityType|string|null $type): Builder
	{
		$enum = $type instanceof ActivityType ? $type : (ActivityType::normalize($type) ?? ActivityType::Other);
		return $q->where('type', $enum->value);
	}

	public function scopeBetweenDates(Builder $q, string|\DateTimeInterface|null $from, string|\DateTimeInterface|null $to): Builder
	{
		$fromDate = $from instanceof \DateTimeInterface ? CarbonImmutable::instance(\DateTime::createFromInterface($from))->format('Y-m-d') : (is_string($from) ? trim($from) : '');
		$toDate   = $to instanceof \DateTimeInterface ? CarbonImmutable::instance(\DateTime::createFromInterface($to))->format('Y-m-d') : (is_string($to) ? trim($to) : '');

		if ($fromDate !== '' && $toDate !== '')
			return $q->whereBetween(PJC::COL_S_DT, [$fromDate, $toDate]);

		if ($fromDate !== '')
			return $q->whereDate(PJC::COL_S_DT, '>=', $fromDate);

		if ($toDate !== '')
			return $q->whereDate(PJC::COL_S_DT, '<=', $toDate);

		return $q;
	}

	public static function cacheKey(string $suffix, array $parts = []): string
	{
		$payload = $parts ? json_encode($parts, JSON_UNESCAPED_UNICODE) : '';
		return 'log_activities:' . $suffix . ':' . sha1($payload);
	}

	/**
	 * Ccached aggregation: counts by type for a date range.
	 *
	 * @return array<string,int> map[typeValue => count]
	 */
	public static function countByTypeCached(
		string|\DateTimeInterface|null $from = null,
		string|\DateTimeInterface|null $to = null,
		int $ttlSeconds = 120
	): array {
		$key = self::cacheKey('countByType', [
			'from' => $from instanceof \DateTimeInterface ? $from->format('Y-m-d') : (string) $from,
			'to'   => $to instanceof \DateTimeInterface ? $to->format('Y-m-d') : (string) $to,
		]);

		return Cache::remember($key, $ttlSeconds, function () use ($from, $to): array {
			$out = [];
			try {
				$rows = self::query()
					->selectRaw('type, COUNT(*) as aggregate_count')
					->betweenDates($from, $to)
					->groupBy('type')
					->get();

				foreach ($rows as $row) {
					$type = $row->getAttribute('type');
					$cnt  = $row->getAttribute('aggregate_count');

					$typeStr = is_scalar($type) ? (string) $type : ActivityType::Other->value;
					$out[$typeStr] = is_numeric($cnt) ? (int) $cnt : 0;
				}
			} catch (\Throwable $e) {
				Log::error(self::class . ' failed countByTypeCached', [
					'error' => $e->getMessage(),
				]);
			}
			return $out;
		});
	}

	public static function createSafe(array $attr): ?self
	{
		try {
			$m = new self();
			foreach ($attr as $k => $v) {
				if (!is_string($k) || $k === '')
					continue;
				if (in_array($k, ['id', DC::COL_TABLE_CREATOR, DC::COL_TABLE_UPDATER], true))
					continue;
				$m->setAttribute($k, $v);
			}
			$m->save();

			return $m;
		} catch (\Throwable $e) {
			Log::error(self::class . ' createSafe failed', [
				'attr_keys' => array_keys($attr),
				'error'     => $e->getMessage(),
			]);
			return null;
		}
	}
}
