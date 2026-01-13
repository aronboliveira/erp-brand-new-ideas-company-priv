<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{
    ResultPerformance,
    WorkActivityProgress,
    WorkActivityScope
};
use App\Traits\{
    DefinesDates,
    FiltersSecureAttachments,
    HasAuditFields,
    NormalizesArrays,
    PlansWithSchedule,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Casts\Attribute, Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Validation\ValidationException;

class Training extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use NormalizesArrays;
    use FiltersSecureAttachments;
    use DefinesDates;
    use PlansWithSchedule;

    protected $table = DC::TABLE_TRAINING;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    /**
     * Evita redundância e conflitos com atributos (ex.: branch/trainer são colunas).
     */
    protected $with = [
        'companyUser',
        'branchModel',
        'employee',
        'trainerModel',
        'trainingType',
        'certificateDocument',
    ];

    protected $fillable = [
        'name',
        'company',
        'branch',
        UC::COL_EMP_ID,

        'trainer',
        CC::COL_TRAINER_OPT,
        CC::COL_TRN_TP,
        CC::COL_TRN_CST,

        PJC::COL_MIN_DR,
        PJC::COL_MAX_DR,
        PJC::COL_EXP_DR,

        PJC::COL_S_DT,
        PJC::COL_E_DT,

        'required',
        'description',
        'certificate',

        'performance',
        'status',

        'remarks',
        'attachments',
        CC::COL_RQ_CERT,
        'tags',
        'metadata',
    ];

    protected $casts = [
        PJC::COL_S_DT => 'date',
        PJC::COL_E_DT => 'date',

        CC::COL_TRN_CST => 'decimal:2',
        'required' => 'boolean',

        'attachments' => 'array',
        CC::COL_RQ_CERT => 'array',
        'tags' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'trainer_scope_enum',
        'progress_enum',
        'performance_enum',

        'trainer_scope_label',
        'progress_label',
        'performance_label',

        'type_name',
        'type_module',

        'is_company_wide',
        'date_range_label',
        'expected_duration_minutes',
    ];

    // * legacy (mantidos)
    public static array $options = ['Internal', 'External', 'Hybrid'];
    public static array $performance = ['Not Concluded', 'Satisfactory', 'Average', 'Poor', 'Excellent'];
    public static array $status = ['Pending', 'Started', 'Completed', 'Terminated'];

    /** caches (por request) */
    private static array $cache = [
        'branch_name' => [],
        'company_name' => [],
        'type_meta' => [],
        'company_is_company_type' => [],
        'doc_exists' => [],
    ];

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company', 'id');
    }

    /**
     * Nome do relation não é "branch" para não colidir com a coluna "branch".
     */
    public function branchModel(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    /**
     * Nome do relation não é "trainer" para não colidir com a coluna "trainer".
     */
    public function trainerModel(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer', 'id');
    }

    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class, CC::COL_TRN_TP, 'id');
    }

    public function certificateDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'certificate', 'id');
    }

    protected function trainerOption(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): int => self::clampInt($value, 0, count(WorkActivityScope::cases()) - 1),
            set: function (mixed $value): int {
                $cases = WorkActivityScope::cases();

                if ($value instanceof WorkActivityScope) {
                    $idx = array_search($value, $cases, true);
                    return is_int($idx) ? $idx : 0;
                }

                if (is_numeric($value))
                    return self::clampInt($value, 0, count($cases) - 1);

                $enum = WorkActivityScope::normalize(is_string($value) ? $value : null);
                $idx = array_search($enum, $cases, true);
                return is_int($idx) ? $idx : 0;
            }
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): int => self::clampInt($value, 0, count(WorkActivityProgress::cases()) - 1),
            set: function (mixed $value): int {
                $cases = WorkActivityProgress::cases();

                if ($value instanceof WorkActivityProgress) {
                    $idx = array_search($value, $cases, true);
                    return is_int($idx) ? $idx : 0;
                }

                if (is_numeric($value))
                    return self::clampInt($value, 0, count($cases) - 1);

                $enum = WorkActivityProgress::normalize(is_string($value) ? $value : null);
                $idx = array_search($enum, $cases, true);
                return is_int($idx) ? $idx : 0;
            }
        );
    }

    protected function performance(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value): int => self::clampInt($value, 0, max(ResultPerformance::values())),
            set: function (mixed $value): int {
                if ($value instanceof ResultPerformance)
                    return $value->value;

                if (is_numeric($value))
                    return self::clampInt($value, 0, max(ResultPerformance::values()));

                $enum = ResultPerformance::normalize(is_string($value) ? $value : null);
                return $enum->value;
            }
        );
    }

    public function getTrainerScopeEnumAttribute(): WorkActivityScope
    {
        $idx = (int) ($this->getAttribute(CC::COL_TRAINER_OPT) ?? 0);
        return WorkActivityScope::cases()[$idx] ?? WorkActivityScope::Internal;
    }

    public function getProgressEnumAttribute(): WorkActivityProgress
    {
        $idx = (int) ($this->getAttribute('status') ?? 0);
        return WorkActivityProgress::cases()[$idx] ?? WorkActivityProgress::Pending;
    }

    public function getPerformanceEnumAttribute(): ResultPerformance
    {
        $v = (int) ($this->getAttribute('performance') ?? 0);
        return ResultPerformance::tryFrom($v) ?? ResultPerformance::NotConcluded;
    }

    public function getTrainerScopeLabelAttribute(): string
    {
        return $this->getTrainerScopeEnumAttribute()->label();
    }

    public function getProgressLabelAttribute(): string
    {
        return $this->getProgressEnumAttribute()->label();
    }

    public function getPerformanceLabelAttribute(): string
    {
        return $this->getPerformanceEnumAttribute()->label();
    }

    public function getTypeNameAttribute(): ?string
    {
        $meta = $this->getTrainingTypeMetaCached();
        return $meta['name'] ?? null;
    }

    public function getTypeModuleAttribute(): ?string
    {
        $meta = $this->getTrainingTypeMetaCached();
        return $meta['module'] ?? null;
    }

    public function getIsCompanyWideAttribute(): bool
    {
        return $this->getAttribute('branch') === null;
    }

    public function getDateRangeLabelAttribute(): string
    {
        $s = $this->getAttribute(PJC::COL_S_DT);
        $e = $this->getAttribute(PJC::COL_E_DT);

        try {
            $sd = $s instanceof Carbon ? $s : ($s ? Carbon::parse((string) $s) : null);
            $ed = $e instanceof Carbon ? $e : ($e ? Carbon::parse((string) $e) : null);

            if (!$sd && !$ed) return '';

            if ($sd && $ed && $sd->toDateString() === $ed->toDateString())
                return $sd->toDateString();

            $left = $sd ? $sd->toDateString() : '';
            $right = $ed ? $ed->toDateString() : '';
            return trim($left . ' → ' . $right);
        } catch (\Throwable $ex) {
            Log::warning(static::class . ' failed to build date_range_label', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'error' => $ex->getMessage(),
            ]);
            return '';
        }
    }

    public function getExpectedDurationMinutesAttribute(): ?int
    {
        $exp = $this->getAttribute(PJC::COL_EXP_DR);

        if (!$exp) return null;

        $min = self::timeToMinutesSafe((string) $exp);
        return $min >= 0 ? $min : null;
    }

    public function isFinished(): bool
    {
        return $this->getProgressEnumAttribute()->isFinished();
    }

    public function isCompleted(): bool
    {
        return $this->getProgressEnumAttribute()->isCompleted();
    }

    public function isRequired(): bool
    {
        return (bool) ($this->getAttribute('required') ?? false);
    }

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->normalizeBooleans();
                $m->normalizeMoney();
                $m->normalizeJsonFields();
                $m->enforceCompanyOrBranchAndInferCompany();
                $m->validateDates();
                $m->validateDurationsAgainstSelfAndType();
                $m->ensureName();
            } catch (ValidationException $ex) {
                throw $ex;
            } catch (\Throwable $ex) {
                Log::error(static::class . ' saving() failed', [
                    'id' => (string) ($m->getAttribute('id') ?? ''),
                    'error' => $ex->getMessage(),
                ]);
                throw $ex;
            }
        });
    }

    private function normalizeBooleans(): void
    {
        $req = $this->getAttribute('required');

        if ($req === null) $this->setAttribute('required', false);
        else $this->setAttribute('required', (bool) $req);
    }

    private function normalizeMoney(): void
    {
        $raw = $this->getAttribute(CC::COL_TRN_CST);

        $val = is_numeric($raw) ? (float) $raw : 0.0;
        if ($val < 0) $val = 0.0;

        $this->setAttribute(CC::COL_TRN_CST, $val);
    }

    private function normalizeJsonFields(): void
    {
        $this->setAttribute('attachments', self::normalizeArrayField($this->getAttribute('attachments')));
        $this->setAttribute('tags', self::normalizeArrayField($this->getAttribute('tags')));
        $this->setAttribute('metadata', self::normalizeArrayField($this->getAttribute('metadata')));

        $req = self::normalizeArrayField($this->getAttribute(CC::COL_RQ_CERT));
        $this->setAttribute(CC::COL_RQ_CERT, $this->filterRequiredCertificates($req));
    }

    private function enforceCompanyOrBranchAndInferCompany(): void
    {
        $company = $this->getAttribute('company');
        $branch = $this->getAttribute('branch');
        if (!$company && !$branch)
            throw ValidationException::withMessages([
                'company' => 'A training deve ter company ou branch.',
                'branch' => 'A training deve ter company ou branch.',
            ]);
        if ($branch) {
            try {
                $companyColumn = Schema::hasColumn(DC::TABLE_BRANCHES, 'company') ? 'company' : (Schema::hasColumn(DC::TABLE_BRANCHES, CC::COL_CP_ID)
                    ? CC::COL_CP_ID : null);
                $branchCompany = DB::table(DC::TABLE_BRANCHES)
                    ->where('id', (string) $branch)
                    ->value($companyColumn);
                if ($branchCompany && !$company) {
                    $this->setAttribute('company', (string) $branchCompany);
                    $company = (string) $branchCompany;
                }
                if ($branchCompany && $company && (string) $branchCompany !== (string) $company)
                    throw ValidationException::withMessages([
                        'branch' => 'A branch informada não pertence à company selecionada.',
                    ]);
            } catch (ValidationException $ex) {
                throw $ex;
            } catch (\Throwable $ex) {
                Log::warning(static::class . ' failed to infer/validate company from branch', [
                    'id' => (string) ($this->getAttribute('id') ?? ''),
                    'branch' => (string) $branch,
                    'company' => (string) ($company ?? ''),
                    'error' => $ex->getMessage(),
                ]);
            }
        }

        // Company precisa ser user do tipo "company"
        if ($company) {
            $key = (string) $company;

            if (array_key_exists($key, self::$cache['company_is_company_type']))
                if (self::$cache['company_is_company_type'][$key] !== true)
                    throw ValidationException::withMessages(['company' => 'O usuário vinculado em company não é do tipo Company.']);

            try {
                $row = DB::table(DC::TABLE_USERS)
                    ->select('id', UC::COL_TP)
                    ->where('id', $key)
                    ->first();

                if (!$row)
                    throw ValidationException::withMessages(['company' => 'Company inexistente.']);

                $type = strtolower(trim((string) ($row->{UC::COL_TP} ?? '')));
                $isCompany = ($type === 'company');

                self::$cache['company_is_company_type'][$key] = $isCompany;

                if (!$isCompany)
                    throw ValidationException::withMessages(['company' => 'O usuário vinculado em company não é do tipo Company.']);
            } catch (ValidationException $ex) {
                throw $ex;
            } catch (\Throwable $ex) {
                Log::warning(static::class . ' failed to validate company user type', [
                    'id' => (string) ($this->getAttribute('id') ?? ''),
                    'company' => $key,
                    'error' => $ex->getMessage(),
                ]);
            }
        }
    }

    private function validateDates(): void
    {
        $s = $this->getAttribute(PJC::COL_S_DT);
        $e = $this->getAttribute(PJC::COL_E_DT);

        try {
            $sd = $s instanceof Carbon ? $s : Carbon::parse((string) $s);
            $ed = $e instanceof Carbon ? $e : Carbon::parse((string) $e);

            if ($ed->lt($sd))
                throw ValidationException::withMessages([
                    PJC::COL_E_DT => 'end_date não pode ser menor que start_date.',
                ]);
        } catch (ValidationException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            Log::warning(static::class . ' failed to validate dates', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'start' => (string) ($s ?? ''),
                'end' => (string) ($e ?? ''),
                'error' => $ex->getMessage(),
            ]);
        }
    }

    private function validateDurationsAgainstSelfAndType(): void
    {
        $min = $this->getAttribute(PJC::COL_MIN_DR);
        $max = $this->getAttribute(PJC::COL_MAX_DR);
        $exp = $this->getAttribute(PJC::COL_EXP_DR);

        // coerência interna (comparação lexicográfica segura em HH:MM:SS)
        if ($min && $max && (string) $min > (string) $max)
            throw ValidationException::withMessages([
                PJC::COL_MIN_DR => 'minimum_duration deve ser <= maximum_duration.',
            ]);

        if ($exp && $min && (string) $exp < (string) $min)
            throw ValidationException::withMessages([
                PJC::COL_EXP_DR => 'expected_duration deve ser >= minimum_duration.',
            ]);

        if ($exp && $max && (string) $exp > (string) $max)
            throw ValidationException::withMessages([
                PJC::COL_EXP_DR => 'expected_duration deve ser <= maximum_duration.',
            ]);

        $typeId = $this->getAttribute(CC::COL_TRN_TP);
        if (!$typeId) return;

        $meta = $this->getTrainingTypeMetaCached();
        $typeMin = $meta['min'] ?? null;
        $typeMax = $meta['max'] ?? null;

        if ($typeMin && $min && (string) $min < (string) $typeMin)
            throw ValidationException::withMessages([
                PJC::COL_MIN_DR => 'minimum_duration não pode ser menor que o mínimo do TrainingType.',
            ]);

        if ($typeMax && $max && (string) $max > (string) $typeMax)
            throw ValidationException::withMessages([
                PJC::COL_MAX_DR => 'maximum_duration não pode ser maior que o máximo do TrainingType.',
            ]);
    }

    private function ensureName(): void
    {
        $current = trim((string) ($this->getAttribute('name') ?? ''));
        if ($current !== '') return;

        $place = $this->resolveBranchName() ?? $this->resolveCompanyName() ?? 'Company';

        $s = $this->getAttribute(PJC::COL_S_DT);
        $date = null;

        try {
            $date = $s instanceof Carbon ? $s->toDateString() : ($s ? Carbon::parse((string) $s)->toDateString() : null);
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' failed to parse start_date for name', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'start' => (string) ($s ?? ''),
                'error' => $ex->getMessage(),
            ]);
        }

        $date = $date ?: 'unknown-date';

        $name = "Training — {$place} on {$date}";

        $meta = $this->getTrainingTypeMetaCached();
        if (!empty($meta['name'])) $name .= " — {$meta['name']}";
        if (!empty($meta['module'])) $name .= " ({$meta['module']})";

        $this->setAttribute('name', mb_substr($name, 0, 255));
    }

    private function resolveBranchName(): ?string
    {
        $branchId = $this->getAttribute('branch');
        if (!$branchId) return null;

        $key = (string) $branchId;

        if (array_key_exists($key, self::$cache['branch_name']))
            return self::$cache['branch_name'][$key];

        try {
            $name = DB::table(DC::TABLE_BRANCHES)
                ->where('id', $key)
                ->value(CC::COL_BRC_NM);

            $name = is_string($name) ? trim($name) : null;

            self::$cache['branch_name'][$key] = $name ?: null;
            return self::$cache['branch_name'][$key];
        } catch (\Throwable $ex) {
            Log::warning(static::class . ' failed to resolve branch name', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'branch' => $key,
                'error' => $ex->getMessage(),
            ]);
            self::$cache['branch_name'][$key] = null;
            return null;
        }
    }

    private function resolveCompanyName(): ?string
    {
        $companyId = $this->getAttribute('company');
        if (!$companyId) return null;

        $key = (string) $companyId;

        if (array_key_exists($key, self::$cache['company_name']))
            return self::$cache['company_name'][$key];

        try {
            $name = DB::table(DC::TABLE_USERS)
                ->where('id', $key)
                ->value(UC::COL_NM);

            $name = is_string($name) ? trim($name) : null;

            self::$cache['company_name'][$key] = $name ?: null;
            return self::$cache['company_name'][$key];
        } catch (\Throwable $ex) {
            Log::warning(static::class . ' failed to resolve company name', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'company' => $key,
                'error' => $ex->getMessage(),
            ]);
            self::$cache['company_name'][$key] = null;
            return null;
        }
    }

    private function getTrainingTypeMetaCached(): array
    {
        $typeId = $this->getAttribute(CC::COL_TRN_TP);
        if (!$typeId) return [];

        $key = (string) $typeId;

        if (array_key_exists($key, self::$cache['type_meta']))
            return self::$cache['type_meta'][$key];

        try {
            $row = DB::table(DC::TABLE_TRAINING_TYPES)
                ->select('name', 'module', PJC::COL_MIN_DR, PJC::COL_MAX_DR)
                ->where('id', $key)
                ->first();

            $meta = [
                'name' => is_string($row?->name ?? null) ? trim((string) $row->name) : null,
                'module' => is_string($row?->module ?? null) ? trim((string) $row->module) : null,
                'min' => $row?->{PJC::COL_MIN_DR} ?? null,
                'max' => $row?->{PJC::COL_MAX_DR} ?? null,
            ];

            self::$cache['type_meta'][$key] = $meta;
            return $meta;
        } catch (\Throwable $ex) {
            Log::warning(static::class . ' failed to resolve training type meta', [
                'id' => (string) ($this->getAttribute('id') ?? ''),
                'type' => $key,
                'error' => $ex->getMessage(),
            ]);
            self::$cache['type_meta'][$key] = [];
            return [];
        }
    }

    private function filterRequiredCertificates(array $value): array
    {
        // aceita: ['uuid', 'Nome'] ou [['id'=>'uuid'], ...]
        $items = [];

        foreach ($value as $v) {
            if (is_string($v)) {
                $t = trim($v);
                if ($t !== '') $items[] = $t;
                continue;
            }

            if (is_array($v)) {
                $id = $v['id'] ?? null;
                if (is_string($id) && trim($id) !== '') $items[] = trim($id);
            }
        }

        if (empty($items)) return [];

        $uuids = [];
        $names = [];

        foreach ($items as $it) {
            if (Utility::looksLikeUuid($it)) $uuids[] = $it;
            else $names[] = $it;
        }

        $valid = [];

        if (!empty($uuids)) {
            $uuids = array_values(array_unique($uuids));

            try {
                $existing = DB::table(DC::TABLE_DOCS)
                    ->whereIn('id', $uuids)
                    ->pluck('id')
                    ->all();

                foreach ($existing as $id) {
                    if (is_string($id) && $id !== '') $valid[] = $id;
                }
            } catch (\Throwable $ex) {
                Log::warning(static::class . ' failed validating required certificates against documents', [
                    'id' => (string) ($this->getAttribute('id') ?? ''),
                    'error' => $ex->getMessage(),
                ]);
                // fallback: mantém os UUIDs recebidos (modo compatível/legado)
                $valid = $uuids;
            }
        }

        $names = array_values(array_filter(array_unique(array_map('trim', $names)), fn($s) => $s !== ''));

        $out = array_values(array_unique(array_merge($valid, $names)));
        return array_slice($out, 0, 256);
    }

    private static function clampInt(mixed $value, int $min, int $max): int
    {
        $v = is_numeric($value) ? (int) $value : $min;
        if ($v < $min) return $min;
        if ($v > $max) return $max;
        return $v;
    }

    private static function timeToMinutesSafe(string $time): int
    {
        $t = trim($time);
        if ($t === '') return -1;

        try {
            $parts = explode(':', $t);
            $h = (int) ($parts[0] ?? 0);
            $m = (int) ($parts[1] ?? 0);
            $s = (int) ($parts[2] ?? 0);

            if ($h < 0 || $m < 0 || $s < 0) return -1;
            if ($m > 59 || $s > 59) return -1;

            return ($h * 60) + $m + (int) round($s / 60);
        } catch (\Throwable) {
            return -1;
        }
    }
}
