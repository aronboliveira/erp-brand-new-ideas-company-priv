<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, Visibility};
use App\Traits\{
	DefinesDates,
	HasAuditFields,
	NormalizesArrays,
	PlansByHierarchy,
	StoresManyRefJson,
	UsesUuids
};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Factories\{HasFactory};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{DB, Log};

class Competency extends Model
{
	use UsesUuids, HasAuditFields, HasFactory, NormalizesArrays, PlansByHierarchy, StoresManyRefJson, DefinesDates;

	protected $table = DC::TABLE_CMPT;

	protected $guarded = [
		'id',
		DC::COL_TABLE_CREATOR,
	];

	protected $fillable = [
		'code',
		'name',
		'module',
		'category',
		'family',
		'description',
		'notes',
		'type',
		'visibility',
		PJC::COL_SBM_BY,
		PJC::COL_SBM_AT,
		PJC::COL_APV_BY,
		PJC::COL_APV_AT,
		PJC::COL_REJ_BY,
		PJC::COL_REJ_AT,
		'jobs',
		'projects',
		'companies',
		'branches',
		'departments',
		'levels',
		'skills',
		'certifications',
		'requirements',
		'behaviors',
		'tags',
	];

	protected $casts = [
		PJC::COL_SBM_AT => 'date',
		PJC::COL_APV_AT => 'datetime',
		PJC::COL_REJ_AT => 'datetime',

		'jobs'           => 'array',
		'projects'       => 'array',
		'companies'      => 'array',
		'branches'       => 'array',
		'departments'    => 'array',
		'levels'         => 'array',
		'skills'         => 'array',
		'certifications' => 'array',
		'requirements'   => 'array',
		'behaviors'      => 'array',
		'tags'           => 'array',
	];

	protected $appends = [
		'module_label',
		'visibility_label',
		'jobs_count',
		'projects_count',
		'tags_count',
	];

	protected $with = [
		'creator',
	];

	private array $memo = [];

	protected static function booted(): void
	{
		static::creating(function (self $m): void {
			try {
				if (trim((string) $m->getAttribute('code')) !== '')
					return;

				$attempts = 0;
				do {
					$attempts++;
					$candidate = 'CMPT-' . (string) Str::uuid();
					$exists = DB::table($m->getTable())
						->where('code', $candidate)
						->exists();

					if (!$exists) {
						$m->setAttribute('code', $candidate);
						break;
					}
				} while ($attempts < 128);

				Log::warning(self::class . ' failed generating unique competency code (attempt limit hit)', [
					'table' => $m->getTable(),
					'model_id' => $m->getAttribute('id') ?? null,
				]);
			} catch (\Throwable $e) {
				Log::error(self::class . ' failed generating competency code', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'table' => $m->getTable(),
					'model_id' => $m->getAttribute('id') ?? null,
				]);
			}
		});

		static::saving(function (self $m): void {
			try {
				$name = trim((string) $m->getAttribute('name'));
				$m->setAttribute('name', $name === '' ? null : $name);

				$m->setAttribute('module', $m->moduleEnum()->value);
				$m->setAttribute('visibility', $m->visibilityEnum()->value);

				$m->ensureJsonAttributesAreEncoded(self::jsonColumns());
			} catch (\Throwable $e) {
				Log::error(self::class . ' saving normalization failed', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'table' => $m->getTable(),
					'model_id' => $m->getAttribute('id') ?? null,
				]);
			}
		});
	}

	public static function jsonColumns(): array
	{
	    try {
    		return [
    			'jobs',
    			'projects',
    			'companies',
    			'branches',
    			'departments',
    			'levels',
    			'skills',
    			'certifications',
    			'requirements',
    			'behaviors',
    			'tags',
    		];
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::jsonColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	public function submitter(): BelongsTo
	{
		return $this->belongsTo(User::class, PJC::COL_SBM_BY, 'id');
	}

	public function approver(): BelongsTo
	{
		return $this->belongsTo(User::class, PJC::COL_APV_BY, 'id');
	}

	public function rejecter(): BelongsTo
	{
		return $this->belongsTo(User::class, PJC::COL_REJ_BY, 'id');
	}

	public function moduleEnum(): AppModuleType
	{
		try {
			$raw = $this->getAttribute('module');
			if ($raw instanceof AppModuleType)
				return $raw;

			return AppModuleType::normalize(is_string($raw) ? $raw : (is_null($raw) ? null : (string) $raw));
		} catch (\Throwable $e) {
			Log::warning(self::class . ' failed normalizing module enum', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'model_id' => $this->getAttribute('id') ?? null,
				'raw' => $this->getAttribute('module'),
			]);
			return AppModuleType::Other;
		}
	}

	public function visibilityEnum(): Visibility
	{
		try {
			$raw = $this->getAttribute('visibility');
			$norm = Visibility::normalize($raw instanceof Visibility ? $raw : $raw);
			return $norm ?? Visibility::Public;
		} catch (\Throwable $e) {
			Log::warning(self::class . ' failed normalizing visibility enum', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'model_id' => $this->getAttribute('id') ?? null,
				'raw' => $this->getAttribute('visibility'),
			]);
			return Visibility::Public;
		}
	}

	public function jobsIds(): array
	{
		return $this->memoize('jobs_ids', fn() => $this->normalizeIdList($this->getAttribute('jobs')));
	}

	public function projectsIds(): array
	{
		return $this->memoize('projects_ids', fn() => $this->normalizeIdList($this->getAttribute('projects')));
	}

	public function companyIds(): array
	{
		return $this->memoize('company_ids', fn() => $this->normalizeIdList($this->getAttribute('companies')));
	}

	public function branchIds(): array
	{
		return $this->memoize('branch_ids', fn() => $this->normalizeIdList($this->getAttribute('branches')));
	}

	public function departmentIds(): array
	{
		return $this->memoize('department_ids', fn() => $this->normalizeIdList($this->getAttribute('departments')));
	}

	public function levelsList(): ?array
	{
	    try {
    		return $this->memoize('levels_list', function () {
    			$list = $this->normalizeStringList($this->getAttribute('levels'));
    			return $list ?: null;
    		});
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::levelsList — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	public function tagsList(): ?array
	{
		return $this->memoize('tags_list', fn() => $this->normalizeStringList($this->getAttribute('tags')));
	}

	protected function normalizeIdList(mixed $value): array
	{
	    try {
    		$arr = self::normalizeArrayField($value);
    		$out = [];
    		foreach ($arr as $v) {
    			if (!is_scalar($v)) continue;
    			$s = trim((string) $v);
    			if ($s === '') continue;
    			$out[] = $s;
    		}
    		return array_values(array_unique($out));
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeIdList — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	protected function memoize(string $key, \Closure $fn): mixed
	{
		return array_key_exists($key, $this->memo) ? $this->memo[$key] : ($this->memo[$key] = $fn());
	}


	public function getModuleLabelAttribute(): string
	{
		try {
			$enum = $this->moduleEnum();
			return method_exists($enum, 'label') ? $enum->label() : $enum->value;
		} catch (\Throwable $e) {
			Log::debug(self::class . ' module label fallback', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'model_id' => $this->getAttribute('id') ?? null,
			]);
			return (string) ($this->getAttribute('module') ?? AppModuleType::Other->value);
		}
	}

	public function getVisibilityLabelAttribute(): string
	{
		try {
			return $this->visibilityEnum()->label();
		} catch (\Throwable $e) {
			Log::debug(self::class . ' visibility label fallback', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'model_id' => $this->getAttribute('id') ?? null,
			]);
			return (string) ($this->getAttribute('visibility') ?? Visibility::Public->value);
		}
	}

	public function getJobsCountAttribute(): int
	{
		return count($this->jobsIds());
	}

	public function getProjectsCountAttribute(): int
	{
		return count($this->projectsIds());
	}

	public function getTagsCountAttribute(): int
	{
		$list = $this->tagsList();
		return is_array($list) ? count($list) : 0;
	}


	public function isApproved(): bool
	{
		return trim((string) $this->getAttribute(PJC::COL_APV_BY)) !== '';
	}

	public function isRejected(): bool
	{
		return trim((string) $this->getAttribute(PJC::COL_REJ_BY)) !== '';
	}

	public function isSubmitted(): bool
	{
		return trim((string) $this->getAttribute(PJC::COL_SBM_BY)) !== '';
	}

	public function markSubmittedBy(string $userId): void
	{
	    try {
    		if (trim($userId) === '') return;
    		$this->setAttribute(PJC::COL_SBM_BY, $userId);
    		if (empty($this->getAttribute(PJC::COL_SBM_AT)))
    			$this->setAttribute(PJC::COL_SBM_AT, now());
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::markSubmittedBy — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	public function addTag(string $tag): void
	{
	    try {
    		$t = trim($tag);
    		if ($t === '') return;

    		$cur = $this->tagsList() ?? [];
    		$cur[] = $t;
    		$this->setAttribute('tags', array_values(array_unique($cur)));
    		unset($this->memo['tags_list']);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addTag — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	public function removeTag(string $tag): void
	{
	    try {
    		$t = trim($tag);
    		if ($t === '') return;

    		$cur = $this->tagsList() ?? [];
    		$out = [];
    		foreach ($cur as $v)
    			if (is_string($v) && $v !== $t)
    				$out[] = $v;

    		$this->setAttribute('tags', $out ?: null);
    		unset($this->memo['tags_list']);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::removeTag — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
