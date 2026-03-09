<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{JobLevel, WorkContractType, WorkPresence, WorkShift};
use App\Traits\{
    FiltersSecureAttachments,
    HasAuditFields,
    NormalizesArrays,
    StoresManyRefJson,
    UsesUuids
};
use Carbon\{CarbonImmutable};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{Cache, DB, Log};

use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobCategory extends Model
{
    use HasFactory;

    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;
    use StoresManyRefJson;
    use FiltersSecureAttachments;

    protected $table = DC::TABLE_JOB_CATS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        'title',
        'slug',
        'code',
        'field',
        'description',
        'requirements',
        'skills',
        'responsibilities',
        'benefits',
        'incentives',
        'notes',
        AC::COL_IA,

        PJC::COL_ACP_LVLS,
        PJC::COL_ACP_PRS,
        PJC::COL_ACP_CTC_TP,
        PJC::COL_ACP_SHFT,

        'certifications',
        'attachments',
        'metadata',
        'companies',
        'branches',
    ];

    protected $with = [
        'creator',
    ];

    protected $casts = [
        AC::COL_IA => 'boolean',

        PJC::COL_ACP_LVLS => 'array',
        PJC::COL_ACP_PRS => 'array',
        PJC::COL_ACP_CTC_TP => 'array',
        PJC::COL_ACP_SHFT => 'array',

        'certifications' => 'array',
        'attachments' => 'array',
        'metadata' => 'array',
        'companies' => 'array',
        'branches' => 'array',
    ];

    protected $appends = [
        'levels_normalized',
        'presence_normalized',
        'contract_types_normalized',
        'shift_types_normalized',
        'is_ready_for_posting',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->normalizeIsActiveField();
                $m->ensureSlugAndCode();
                $m->normalizeAcceptedEnums();
                $m->normalizeJsonFields();

                $m->ensureJsonAttributesAreEncoded([
                    PJC::COL_ACP_LVLS,
                    PJC::COL_ACP_PRS,
                    PJC::COL_ACP_CTC_TP,
                    PJC::COL_ACP_SHFT,
                    'certifications',
                    'attachments',
                    'metadata',
                    'companies',
                    'branches',
                ]);
            } catch (\Throwable $e) {
                Log::error(self::class . ' failed normalizing before save', [
                    'id' => $m->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    private function normalizeIsActiveField(): void
    {
        try {
            $raw = $this->getAttribute(AC::COL_IA);

            if ($raw === null) {
                $this->setAttribute(AC::COL_IA, true);
                return;
            }

            if (is_bool($raw)) {
                $this->setAttribute(AC::COL_IA, $raw);
                return;
            }

            if (is_int($raw) || is_float($raw)) {
                $this->setAttribute(AC::COL_IA, (bool) $raw);
                return;
            }

            if (is_string($raw)) {
                $v = strtolower(trim($raw));
                $this->setAttribute(AC::COL_IA, in_array($v, ['1', 'true', 'yes', 'y', 'on'], true));
                return;
            }

            $this->setAttribute(AC::COL_IA, true);
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeIsActiveField — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function ensureSlugAndCode(): void
    {
        try {
            $title = trim((string) ($this->getAttribute('title') ?? ''));
            $slug  = trim((string) ($this->getAttribute('slug') ?? ''));
            $code  = trim((string) ($this->getAttribute('code') ?? ''));

            if ($slug === '') {
                $base = $title !== '' ? Str::snake(Str::lower(Str::ascii($title))) : '';
                $base = preg_replace('/[^a-z0-9_]/', '', (string) $base);
                $base = trim((string) $base, '_');

                if ($base === '')
                    $base = 'job_cat_' . CarbonImmutable::now()->format('Ymd_His');

                $candidateUrl = Str::limit($base, 254, '') ?: ('job_cat_' . CarbonImmutable::now()->format('Ymd_His'));
                if (DB::table($this->getTable())->where('slug', $candidateUrl)->where('id', '!=', $this->getAttribute('id') ?? '')->exists()) {
                    $acc = 0;
                    do {
                        $candidateUrl = Str::limit($base, 196, '') . 'job_cat_' . CarbonImmutable::now()->format('Ymd_His') . Str::uuid()->toString();
                        $acc++;
                    } while (DB::table($this->getTable())->where('slug', $candidateUrl)->where('id', '!=', $this->getAttribute('id') ?? '')->exists());
                    if ($acc > 64000) throw new \RuntimeException('Failed to generate unique slug for JobCategory after 64000 attempts');
                }
                $this->setAttribute('slug', $candidateUrl);
            }

            if ($code === '') {
                $uuid = method_exists(Utility::class, 'generateUuid') ? Utility::generateUuid() : (string) Str::uuid();
                $this->setAttribute('code', 'JB-CAT-' . $uuid . '-' . CarbonImmutable::now()->format('YmdHis'));
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::ensureSlugAndCode — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function normalizeAcceptedEnums(): void
    {
        try {
            $this->setAttribute(PJC::COL_ACP_LVLS, $this->normalizeJobLevels($this->getAttribute(PJC::COL_ACP_LVLS)));
            $this->setAttribute(PJC::COL_ACP_PRS, $this->normalizePresenceTypes($this->getAttribute(PJC::COL_ACP_PRS)));
            $this->setAttribute(PJC::COL_ACP_CTC_TP, $this->normalizeContractTypes($this->getAttribute(PJC::COL_ACP_CTC_TP)));
            $this->setAttribute(PJC::COL_ACP_SHFT, $this->normalizeShiftTypes($this->getAttribute(PJC::COL_ACP_SHFT)));
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeAcceptedEnums — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function normalizeJsonFields(): void
    {
        try {
            $this->setAttribute('certifications', $this->normalizeStringOrIdList($this->getAttribute('certifications')));
            $this->setAttribute('attachments', $this->normalizeAttachments($this->getAttribute('attachments')));
            $this->setAttribute('companies', $this->normalizeStringOrIdList($this->getAttribute('companies')));
            $this->setAttribute('branches', $this->normalizeStringOrIdList($this->getAttribute('branches')));

            $meta = self::normalizeArrayField($this->getAttribute('metadata'));
            $this->setAttribute('metadata', $meta ?: null);
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeJsonFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function normalizeJobLevels(mixed $value): ?array
    {
        try {
            $arr = self::normalizeArrayField($value);
            $out = [];

            foreach ($arr as $v) {
                if (!is_scalar($v)) continue;
                $enum = JobLevel::normalize($v);
                if ($enum) $out[] = $enum->value;
            }

            $out = array_values(array_unique($out));
            return $out ?: null;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeJobLevels — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function normalizeContractTypes(mixed $value): ?array
    {
        try {
            $arr = self::normalizeArrayField($value);
            $out = [];

            foreach ($arr as $v) {
                if (!is_scalar($v)) continue;
                $enum = WorkContractType::normalize($v);
                if ($enum) $out[] = $enum->value;
            }

            $out = array_values(array_unique($out));
            return $out ?: null;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeContractTypes — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function normalizePresenceTypes(mixed $value): ?array
    {
        try {
            $arr = self::normalizeArrayField($value);
            $out = [];

            foreach ($arr as $v) {
                if (!is_scalar($v)) continue;
                $enum = WorkPresence::normalize($v);
                if ($enum) $out[] = $enum->value;
            }

            $out = array_values(array_unique($out));
            return $out ?: null;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizePresenceTypes — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function normalizeShiftTypes(mixed $value): ?array
    {
        try {
            $arr = self::normalizeArrayField($value);
            $out = [];

            foreach ($arr as $v) {
                if (!is_scalar($v)) continue;
                $enum = WorkShift::normalize($v);
                if ($enum) $out[] = $enum->value;
            }

            $out = array_values(array_unique($out));
            return $out ?: null;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeShiftTypes — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function normalizeStringOrIdList(mixed $value): ?array
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

            $out = array_values(array_unique($out));
            return $out ?: null;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeStringOrIdList — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function normalizeAttachments(mixed $value): ?array
    {
        try {
            $arr = self::normalizeArrayField($value);
            $out = [];

            foreach ($arr as $it) {
                if (is_scalar($it)) {
                    $s = trim((string) $it);
                    if ($s !== '') $out[] = ['id' => $s];
                    continue;
                }

                if (!is_array($it)) continue;

                $id = isset($it['id']) && is_scalar($it['id']) ? trim((string) $it['id']) : null;
                $name = isset($it['name']) && is_scalar($it['name']) ? trim((string) $it['name']) : null;
                $filePath = isset($it['file_path']) && is_scalar($it['file_path']) ? trim((string) $it['file_path']) : null;

                $payload = [];
                if ($id !== null && $id !== '') $payload['id'] = $id;
                if ($name !== null && $name !== '') $payload['name'] = $name;
                if ($filePath !== null && $filePath !== '') $payload['file_path'] = $filePath;

                if ($payload !== []) $out[] = $payload;
            }

            return $out ?: null;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeAttachments — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    public function getLevelsNormalizedAttribute(): array
    {
        return $this->normalizeJobLevels($this->getAttribute(PJC::COL_ACP_LVLS)) ?? [];
    }

    public function getPresenceNormalizedAttribute(): array
    {
        return $this->normalizePresenceTypes($this->getAttribute(PJC::COL_ACP_PRS)) ?? [];
    }

    public function getContractTypesNormalizedAttribute(): array
    {
        return $this->normalizeContractTypes($this->getAttribute(PJC::COL_ACP_CTC_TP)) ?? [];
    }

    public function getShiftTypesNormalizedAttribute(): array
    {
        return $this->normalizeShiftTypes($this->getAttribute(PJC::COL_ACP_SHFT)) ?? [];
    }

    public function getIsReadyForPostingAttribute(): bool
    {
        try {
            $title = trim((string) ($this->getAttribute('title') ?? ''));
            $desc  = trim((string) ($this->getAttribute('description') ?? ''));

            if ($title === '' || $desc === '') return false;
            if (!((bool) $this->getAttribute(AC::COL_IA))) return false;

            return true;
        } catch (\Throwable $e) {
            Log::error(static::class . '::getIsReadyForPostingAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return false;
        }
    }

    public function setAcceptedLevelsSafe(mixed $value): self
    {
        $this->setAttribute(PJC::COL_ACP_LVLS, $this->normalizeJobLevels($value));
        return $this;
    }

    public function setAcceptedPresenceSafe(mixed $value): self
    {
        $this->setAttribute(PJC::COL_ACP_PRS, $this->normalizePresenceTypes($value));
        return $this;
    }

    public function setAcceptedContractTypesSafe(mixed $value): self
    {
        $this->setAttribute(PJC::COL_ACP_CTC_TP, $this->normalizeContractTypes($value));
        return $this;
    }

    public function setAcceptedShiftTypesSafe(mixed $value): self
    {
        $this->setAttribute(PJC::COL_ACP_SHFT, $this->normalizeShiftTypes($value));
        return $this;
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where(AC::COL_IA, true);
    }

    public function scopeField(Builder $q, ?string $field): Builder
    {
        $field = $field === null ? '' : trim($field);
        if ($field === '') return $q;
        return $q->where('field', $field);
    }

    public function scopeSearchTitle(Builder $q, ?string $term): Builder
    {
        $term = $term === null ? '' : trim($term);
        if ($term === '') return $q;

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
        return $q->where('title', 'like', $like);
    }

    public static function cacheKey(string $suffix, array $parts = []): string
    {
        $payload = $parts ? json_encode($parts, JSON_UNESCAPED_UNICODE) : '';
        return 'job_categories:' . $suffix . ':' . sha1($payload);
    }

    public static function countActiveCached(int $ttlSeconds = 120): int
    {
        try {
            $key = self::cacheKey('countActive');

            return (int) Cache::remember($key, $ttlSeconds, function (): int {
                try {
                    return (int) self::query()->active()->count();
                } catch (\Throwable $e) {
                    Log::error(self::class . ' countActiveCached failed', ['error' => $e->getMessage()]);
                    return 0;
                }
            });
        } catch (\Throwable $e) {
            Log::error(static::class . '::countActiveCached — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return 0;
        }
    }

    public static function countsByFieldCached(int $ttlSeconds = 120): array
    {
        try {
            $key = self::cacheKey('countsByField');

            return Cache::remember($key, $ttlSeconds, function (): array {
                $out = [];
                try {
                    $rows = self::query()
                        ->selectRaw('field, COUNT(*) as aggregate_count')
                        ->groupBy('field')
                        ->get();

                    foreach ($rows as $row) {
                        $field = $row->getAttribute('field');
                        $cnt   = $row->getAttribute('aggregate_count');

                        $fieldStr = is_scalar($field) ? trim((string) $field) : '';
                        $key = $fieldStr !== '' ? $fieldStr : '#NO_FIELD';
                        $out[$key] = is_numeric($cnt) ? (int) $cnt : 0;
                    }
                } catch (\Throwable $e) {
                    Log::error(self::class . ' countsByFieldCached failed', ['error' => $e->getMessage()]);
                }
                return $out;
            });
        } catch (\Throwable $e) {
            Log::error(static::class . '::countsByFieldCached — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }
}
