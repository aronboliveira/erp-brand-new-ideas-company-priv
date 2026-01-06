<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SettingsConstants as SC
};
use App\Enums\{EvaluationStatus, Frequency};
use App\Traits\{
    DefinesDates,
    DescribesCompanyBranch,
    FiltersSecureAttachments,
    HasAuditFields,
    NormalizesArrays,
    PlansByHierarchy,
    UsesUuids
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\{Cache, DB, Log, Schema};
use Illuminate\Support\Str;

class Budget extends Model
{
    use UsesUuids, SoftDeletes, HasAuditFields, DefinesDates, DescribesCompanyBranch, FiltersSecureAttachments, PlansByHierarchy, NormalizesArrays;

    protected $table = DC::TABLE_BDG;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR, DC::COL_TABLE_UPDATER];

    protected $fillable = [
        'code',
        'name',
        'type',
        'period',
        'frequency',
        'from',
        PJC::COL_S_DT,
        'to',
        PJC::COL_E_DT,
        'amount',
        'currency',
        BC::COL_EXC_RT,
        BC::COL_WRN_TRSH,
        BC::COL_CRT_WRN_TH,
        'status',
        PJC::COL_SBM_BY,
        PJC::COL_SBM_AT,
        PJC::COL_APV_BY,
        PJC::COL_APV_AT,
        PJC::COL_REJ_BY,
        PJC::COL_REJ_AT,
        'description',
        'notes',
        BC::COL_INC_DATA,
        BC::COL_EXP_DATA,
        PJC::COL_PJ_ID,
        PJC::COL_CTC_ID,
        'company',
        'branch',
        'department',
        BC::COL_BNK_TRFS,
        'transactions',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'frequency' => Frequency::class,
        'status' => EvaluationStatus::class,

        'amount' => 'decimal:2',
        BC::COL_EXC_RT => 'decimal:4',
        BC::COL_WRN_TRSH => 'decimal:2',
        BC::COL_CRT_WRN_TH => 'decimal:2',

        'from' => 'date:Y-m-d',
        PJC::COL_S_DT => 'date:Y-m-d',
        'to' => 'date:Y-m-d',
        PJC::COL_E_DT => 'date:Y-m-d',

        PJC::COL_SBM_AT => 'datetime',
        PJC::COL_APV_AT => 'datetime',
        PJC::COL_REJ_AT => 'datetime',

        BC::COL_BNK_TRFS => 'array',
        'transactions' => 'array',
        'attachments' => 'array',
        'metadata' => 'array',

        BC::COL_INC_DATA => 'array',
        BC::COL_EXP_DATA => 'array',
    ];

    protected $attributes = [
        'currency' => SC::DEF_SITE_CURRENCY_ID,
        BC::COL_EXC_RT => 1.0,
    ];

    protected $appends = [
        'availability_date',
        'income_total',
        'expense_total',
        'net_total',
        'usage_percent',
        'is_submitted',
        'is_approved',
        'is_rejected',
        'status_label',
    ];

    protected array $memo = [];

    private const CODE_ATTEMPTS = 25;
    private const TYPES = ['revenue', 'expense', 'mixed'];
    private const LIST_JSON_FIELDS = [BC::COL_BNK_TRFS, 'transactions', 'attachments'];
    private const MAP_JSON_FIELDS = ['metadata', BC::COL_INC_DATA, BC::COL_EXP_DATA];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureUniqueCode();
                $m->normalizeTypeField();
                $m->normalizePeriodField();
                $m->mirrorLegacyDates();
                $m->normalizeEnumFields();
                $m->normalizeCurrencyField();
                $m->clampThresholdFields();
                $m->normalizeJsonFields();
                $m->memo = [];
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving normalization failed', [
                    'model_id' => (string) ($m->getAttribute('id') ?? ''),
                    'table' => (string) $m->getTable(),
                    'error' => $e->getMessage(),
                    'e_file' => $e->getFile(),
                    'e_line' => $e->getLine(),
                ]);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_SBM_BY);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_APV_BY);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_REJ_BY);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID);
    }

    public function getAvailabilityDateAttribute(): string
    {
        return $this->getAvailabilityDate();
    }

    public function getIncomeTotalAttribute(): float
    {
        return (float) $this->incomeTotal();
    }

    public function getExpenseTotalAttribute(): float
    {
        return (float) $this->expenseTotal();
    }

    public function getNetTotalAttribute(): float
    {
        return (float) $this->netTotal();
    }

    public function getUsagePercentAttribute(): ?float
    {
        return $this->usagePercent();
    }

    public function getIsSubmittedAttribute(): bool
    {
        return $this->isSubmitted();
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->isApproved();
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->isRejected();
    }

    public function getStatusLabelAttribute(): ?string
    {
        try {
            $lang = (string) config('app.locale', DC::DEFAULT_LANG);
            $raw = $this->getAttribute('status');
            if ($raw === null || trim((string) $raw) === '') return null;
            $enum = EvaluationStatus::normalize($raw);
            return EvaluationStatus::labels($lang)[$enum->value] ?? $enum->value;
        } catch (\Throwable $e) {
            Log::notice(static::class . ' failed resolving status label', [
                'model_id' => (string) ($this->getAttribute('id') ?? ''),
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            return null;
        }
    }

    public function getAvailabilityDate(): string
    {
        $start = $this->getAttribute(PJC::COL_S_DT);
        $end = $this->getAttribute(PJC::COL_E_DT);

        $out = '';
        $fmt = 'M-Y';

        if (!empty($start))
            $out = date($fmt, strtotime((string) $start));

        if (!empty($end))
            $out .= ' - ' . date($fmt, strtotime((string) $end)) . ' ';

        return $out;
    }

    public function isSubmitted(): bool
    {
        return !empty($this->getAttribute(PJC::COL_SBM_BY));
    }

    public function isApproved(): bool
    {
        return !empty($this->getAttribute(PJC::COL_APV_BY));
    }

    public function isRejected(): bool
    {
        return !empty($this->getAttribute(PJC::COL_REJ_BY));
    }

    public function incomeTotal(): float
    {
        return $this->memoizeFloat(__FUNCTION__, function (): float {
            return $this->sumStructuredAmounts($this->getAttribute(BC::COL_INC_DATA));
        });
    }

    public function expenseTotal(): float
    {
        return $this->memoizeFloat(__FUNCTION__, function (): float {
            return $this->sumStructuredAmounts($this->getAttribute(BC::COL_EXP_DATA));
        });
    }

    public function netTotal(): float
    {
        return $this->memoizeFloat(__FUNCTION__, function (): float {
            return (float) $this->incomeTotal() - (float) $this->expenseTotal();
        });
    }

    public function usagePercent(): ?float
    {
        return $this->memoizeNullableFloat(__FUNCTION__, function (): ?float {
            $amount = $this->getAttribute('amount');
            $budget = is_numeric((string) $amount) ? (float) $amount : null;
            if ($budget === null || $budget <= 0) return null;

            $spent = $this->cachedTransactionAmountSum();
            $p = ($spent * 100) / $budget;
            if ($p < 0) $p = 0;
            return round($p, 2);
        });
    }

    public function cachedTransactionAmountSum(string $amountColumn = 'amount', int $ttlSeconds = 60): float
    {
        return $this->memoizeFloat(__FUNCTION__ . ':' . $amountColumn, function () use ($amountColumn, $ttlSeconds): float {
            $id = (string) ($this->getAttribute('id') ?? '');
            if ($id === '') return $this->sumTransactionsAmount($amountColumn);

            $key = 'budget:' . $id . ':trx_sum:' . $amountColumn;

            try {
                return (float) Cache::remember($key, $ttlSeconds, function () use ($amountColumn): float {
                    return $this->sumTransactionsAmount($amountColumn);
                });
            } catch (\Throwable $e) {
                Log::notice(static::class . ' cache remember failed, falling back to direct query', [
                    'model_id' => $id,
                    'error' => $e->getMessage(),
                    'e_file' => $e->getFile(),
                    'e_line' => $e->getLine(),
                ]);
                return $this->sumTransactionsAmount($amountColumn);
            }
        });
    }

    public function sumTransactionsAmount(string $amountColumn = 'amount'): float
    {
        $ids = $this->normalizeStringListField($this->getAttribute('transactions'));
        if (empty($ids)) return 0.0;

        try {
            if (!Schema::hasTable(DC::TABLE_TRS)) return 0.0;
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed checking transactions table', [
                'model_id' => (string) ($this->getAttribute('id') ?? ''),
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            return 0.0;
        }

        try {
            return (float) DB::table(DC::TABLE_TRS)
                ->whereIn('id', $ids)
                ->sum($amountColumn);
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed summing transactions', [
                'model_id' => (string) ($this->getAttribute('id') ?? ''),
                'table' => DC::TABLE_TRS,
                'amount_column' => $amountColumn,
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            return 0.0;
        }
    }

    public static function percentage(float $actual, float $budget): string
    {
        if ($actual == 0.0)
            return number_format(0, 2);

        $percentage = $budget * 100 / $actual;
        return number_format($percentage, 2);
    }

    public static function safeInsert(array $rows): int
    {
        if (empty($rows)) return 0;

        $table = (new self())->getTable();
        $out = [];
        $count = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) continue;

            $m = new self();
            foreach ($row as $k => $v)
                $m->setAttribute((string) $k, $v);

            try {
                $m->ensureUniqueCode();
                $m->normalizeTypeField();
                $m->normalizePeriodField();
                $m->mirrorLegacyDates();
                $m->normalizeEnumFields();
                $m->normalizeCurrencyField();
                $m->clampThresholdFields();
                $m->normalizeJsonFields();
            } catch (\Throwable $e) {
                Log::error(static::class . ' safeInsert row normalization failed', [
                    'table' => (string) $table,
                    'error' => $e->getMessage(),
                    'e_file' => $e->getFile(),
                    'e_line' => $e->getLine(),
                ]);
                continue;
            }

            $attrs = $m->getAttributes();
            if (empty($attrs['id']))
                $attrs['id'] = (string) Str::uuid();

            $out[] = $attrs;
        }

        if (empty($out)) return 0;

        try {
            $ok = DB::table($table)->insert($out);
            $count = $ok ? count($out) : 0;
        } catch (\Throwable $e) {
            Log::error(static::class . ' safeInsert failed', [
                'table' => (string) $table,
                'rows' => count($out),
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            return 0;
        }

        return $count;
    }

    protected function ensureUniqueCode(): void
    {
        $code = trim((string) $this->getAttribute('code'));
        if ($code === '')
            $code = $this->generateCodeCandidate();

        $attempts = 0;
        do {
            $attempts++;

            $exists = false;
            try {
                $exists = DB::table($this->getTable())
                    ->where('code', $code)
                    ->where('id', '!=', (string) ($this->getAttribute('id') ?? ''))
                    ->exists();
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed checking code uniqueness', [
                    'model_id' => (string) ($this->getAttribute('id') ?? ''),
                    'candidate' => $code,
                    'error' => $e->getMessage(),
                    'e_file' => $e->getFile(),
                    'e_line' => $e->getLine(),
                ]);
                $exists = false;
            }

            if (!$exists) break;

            $code = $this->generateCodeCandidate();
        } while ($attempts < self::CODE_ATTEMPTS);

        if ($attempts >= self::CODE_ATTEMPTS) {
            Log::warning(static::class . ' code generation attempt limit reached', [
                'model_id' => (string) ($this->getAttribute('id') ?? ''),
                'last_candidate' => $code,
                'attempts' => $attempts,
            ]);
        }

        $this->setAttribute('code', $code);
    }

    protected function generateCodeCandidate(): string
    {
        return 'BDG-' . (string) Str::uuid();
    }

    protected function normalizeTypeField(): void
    {
        $v = strtolower(trim((string) $this->getAttribute('type')));
        if ($v === '') $this->setAttribute('type', null);
        else $this->setAttribute('type', in_array($v, self::TYPES, true) ? $v : null);
    }

    protected function normalizePeriodField(): void
    {
        $p = trim((string) $this->getAttribute('period'));
        if ($p === '') {
            $this->setAttribute('period', null);
            return;
        }

        $this->setAttribute('period', preg_match('/\b(19|20)\d{2}\b/', $p) === 1 ? $p : null);
    }

    protected function mirrorLegacyDates(): void
    {
        $table = (string) $this->getTable();

        try {
            $hasFrom = Schema::hasColumn($table, 'from');
            $hasTo = Schema::hasColumn($table, 'to');
            $hasStart = Schema::hasColumn($table, PJC::COL_S_DT);
            $hasEnd = Schema::hasColumn($table, PJC::COL_E_DT);
        } catch (\Throwable $e) {
            Log::notice(static::class . ' failed checking legacy date columns', [
                'table' => $table,
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            return;
        }

        $from = $hasFrom ? $this->getAttribute('from') : null;
        $to = $hasTo ? $this->getAttribute('to') : null;
        $start = $hasStart ? $this->getAttribute(PJC::COL_S_DT) : null;
        $end = $hasEnd ? $this->getAttribute(PJC::COL_E_DT) : null;

        if ($hasFrom && $hasStart) {
            if (empty($start) && !empty($from)) $this->setAttribute(PJC::COL_S_DT, $from);
            else if (empty($from) && !empty($start)) $this->setAttribute('from', $start);
            else if (!empty($start) && !empty($from) && (string) $start !== (string) $from) $this->setAttribute('from', $start);
        }

        if ($hasTo && $hasEnd) {
            if (empty($end) && !empty($to)) $this->setAttribute(PJC::COL_E_DT, $to);
            else if (empty($to) && !empty($end)) $this->setAttribute('to', $end);
            else if (!empty($end) && !empty($to) && (string) $end !== (string) $to) $this->setAttribute('to', $end);
        }
    }

    protected function normalizeEnumFields(): void
    {
        $rawStatus = $this->getAttribute('status');
        if ($rawStatus !== null && trim((string) $rawStatus) !== '')
            $this->setAttribute('status', EvaluationStatus::normalize($rawStatus)->value);

        $rawFrequency = $this->getAttribute('frequency');
        if ($rawFrequency !== null && trim((string) $rawFrequency) !== '') {
            try {
                $this->setAttribute('frequency', Frequency::normalize($rawFrequency)?->value);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed normalizing frequency', [
                    'model_id' => (string) ($this->getAttribute('id') ?? ''),
                    'frequency' => (string) $rawFrequency,
                    'error' => $e->getMessage(),
                    'e_file' => $e->getFile(),
                    'e_line' => $e->getLine(),
                ]);
            }
        }
    }

    protected function normalizeCurrencyField(): void
    {
        $cur = strtoupper(trim((string) $this->getAttribute('currency')));
        if ($cur === '') {
            $this->setAttribute('currency', null);
            return;
        }

        $this->setAttribute('currency', preg_match('/^[A-Z]{3}$/', $cur) === 1 ? $cur : null);
    }

    protected function clampThresholdFields(): void
    {
        $warn = $this->clampPercent($this->getAttribute(BC::COL_WRN_TRSH));
        $crit = $this->clampPercent($this->getAttribute(BC::COL_CRT_WRN_TH));

        if ($warn !== null) $this->setAttribute(BC::COL_WRN_TRSH, $warn);
        else $this->setAttribute(BC::COL_WRN_TRSH, null);

        if ($crit !== null && $warn !== null && $crit < $warn) $crit = $warn;
        $this->setAttribute(BC::COL_CRT_WRN_TH, $crit);
    }

    protected function clampPercent(mixed $v): ?float
    {
        if ($v === null) return null;

        $s = trim((string) $v);
        if ($s === '' || !is_numeric($s)) return null;

        $f = (float) $s;
        if ($f < 0) $f = 0;
        if ($f > 100) $f = 100;
        return round($f, 2);
    }

    protected function normalizeJsonFields(): void
    {
        foreach (self::LIST_JSON_FIELDS as $field) {
            $norm = $this->normalizeStringListField($this->getAttribute($field));
            $this->setAttribute($field, $norm);
        }

        foreach (self::MAP_JSON_FIELDS as $field) {
            $raw = $this->getAttribute($field);
            if ($raw === null) {
                $this->setAttribute($field, null);
                continue;
            }

            try {
                $arr = self::normalizeArrayField($raw);
                $this->setAttribute($field, $arr ?: null);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed normalizing json/map field', [
                    'model_id' => (string) ($this->getAttribute('id') ?? ''),
                    'field' => (string) $field,
                    'error' => $e->getMessage(),
                    'e_file' => $e->getFile(),
                    'e_line' => $e->getLine(),
                ]);
            }
        }
    }

    protected function normalizeStringListField(mixed $value): ?array
    {
        $arr = self::normalizeArrayField($value);
        if (is_string($value) && trim($value) !== '' && empty($arr))
            $arr = [trim($value)];

        $out = [];
        foreach ($arr as $v) {
            if (!is_scalar($v)) continue;
            $s = trim((string) $v);
            if ($s === '') continue;
            $out[] = $s;
        }
        $out = array_values(array_unique($out));
        return $out ?: null;
    }

    protected function sumStructuredAmounts(mixed $raw): float
    {
        $data = [];
        try {
            $data = self::normalizeArrayField($raw);
        } catch (\Throwable $e) {
            Log::notice(static::class . ' failed decoding structured amounts', [
                'model_id' => (string) ($this->getAttribute('id') ?? ''),
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            return 0.0;
        }

        $total = 0.0;

        $walk = function (mixed $v) use (&$walk, &$total): void {
            if (is_array($v)) {
                foreach ($v as $k => $vv) {
                    if (is_string($k) && in_array($k, ['amount', 'value', 'total'], true) && is_scalar($vv) && is_numeric((string) $vv))
                        $total += (float) $vv;
                    else
                        $walk($vv);
                }
            }
        };

        $walk($data);

        return round($total, 2);
    }

    protected function memoizeFloat(string $key, \Closure $fn): float
    {
        if (array_key_exists($key, $this->memo))
            return (float) $this->memo[$key];

        $v = 0.0;
        try {
            $v = (float) $fn();
        } catch (\Throwable $e) {
            Log::notice(static::class . ' memoized computation failed', [
                'model_id' => (string) ($this->getAttribute('id') ?? ''),
                'key' => $key,
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            $v = 0.0;
        }

        $this->memo[$key] = $v;
        return $v;
    }

    protected function memoizeNullableFloat(string $key, \Closure $fn): ?float
    {
        if (array_key_exists($key, $this->memo))
            return $this->memo[$key] === null ? null : (float) $this->memo[$key];

        $v = null;
        try {
            $v = $fn();
            if ($v !== null) $v = (float) $v;
        } catch (\Throwable $e) {
            Log::notice(static::class . ' memoized nullable computation failed', [
                'model_id' => (string) ($this->getAttribute('id') ?? ''),
                'key' => $key,
                'error' => $e->getMessage(),
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
            ]);
            $v = null;
        }

        $this->memo[$key] = $v;
        return $v;
    }
}
