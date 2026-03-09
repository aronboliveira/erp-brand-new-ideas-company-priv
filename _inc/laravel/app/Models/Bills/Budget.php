<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{EvaluationStatus, Frequency};
use App\Helpers\ErrorHandler;
use App\Traits\{DefinesDates, DescribesCompanyBranch, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, PlansByHierarchy, UsesUuids};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, SoftDeletes};
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
/**
 * @property array|string|null $expense_data
 * @property array|string|null $income_data
 * @property mixed $from
 * @property mixed $period

 * @property mixed $frequency
 */

class Budget extends Model
{
    use DefinesDates, DescribesCompanyBranch, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, PlansByHierarchy, SoftDeletes, UsesUuids;

    protected $table = DC::TABLE_BDG;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

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
        'exchange_rate',
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
        BC::COL_CARD_NTS,
        'receipts',
        'attachments',
        'metadata',
    ];

    protected $with = ['creator'];

    protected $appends = ['availability_date', 'is_expired'];

    protected $casts = [
        'from' => 'date',
        'to'   => 'date',

        PJC::COL_S_DT => 'date',
        PJC::COL_E_DT => 'date',

        PJC::COL_SBM_AT => 'datetime',
        PJC::COL_APV_AT => 'datetime',
        PJC::COL_REJ_AT => 'datetime',

        'amount'         => 'decimal:2',
        'exchange_rate'  => 'decimal:6',
        BC::COL_WRN_TRSH => 'decimal:2',
        BC::COL_CRT_WRN_TH => 'decimal:2',

        'frequency' => Frequency::class,
        'status'    => EvaluationStatus::class,

        BC::COL_BNK_TRFS => 'array',
        'transactions'   => 'array',
        BC::COL_CARD_NTS => 'array',
        'receipts'       => 'array',
        'attachments'    => 'array',
        'metadata'       => 'array',
    ];

    /** @var array<string,string> */
    public static array $frequency = [
        'monthly'     => 'Monthly',
        'weekly'      => 'Weekly',
        'biweekly'    => 'Biweekly',
        'quaternaly'  => 'Quarterly',
        'semimonthly' => 'Semimonthly',
        'semestral'   => 'Semestral',
        'annual'      => 'Annual',
        'once'        => 'Once',
        'variable'    => 'Variable',
        'hourly'      => 'Hourly',
    ];

    protected array $runtimeCache = [];

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            try {
                $code = trim((string) ($m->getAttribute('code') ?? ''));
                if ($code !== '')
                    return;

                $attempts = 0;
                do {
                    $attempts++;
                    $candidate = 'BDG-' . Str::uuid()->toString();
                    $exists = DB::table($m->getTable())->where('code', $candidate)->exists();
                    if (!$exists) {
                        $m->setAttribute('code', $candidate);
                        return;
                    }
                } while ($attempts < 32);

                $m->setAttribute('code', 'BDG-' . Str::uuid()->toString());
                Log::warning(self::class . ' code generation exceeded attempts', [
                    'table'    => $m->getTable(),
                    'attempts' => $attempts,
                ]);
            } catch (\Throwable $e) {
                Log::error(self::class . ' code generation failed', [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]);
                $m->setAttribute('code', 'BDG-' . Str::uuid()->toString());
            }
        });

        static::saving(function (self $m): void {
            try {
                self::normalizePeriod($m);
                self::syncLegacyDates($m);
                self::normalizeEnumsAndType($m);
                self::normalizeThresholds($m);
                self::normalizeJsonLists($m);
                self::enrichReceiptsFromLinkedEntities($m);
                self::mergeReceiptsIntoAttachments($m);
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving normalization failed', [
                    'id'    => $m->getAttribute('id'),
                    'table' => $m->getTable(),
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]);
            }
        });
    }

    protected static function normalizePeriod(self $m): void
    {
        $raw = $m->getAttribute('period');
        if ($raw === null)
            return;

        $p = trim((string) $raw);
        if ($p === '')
            $m->setAttribute('period', null);
        elseif (preg_match('/\b(19|20)\d{2}\b/', $p) !== 1)
            $m->setAttribute('period', null);
        else
            $m->setAttribute('period', $p);
    }

    protected static function syncLegacyDates(self $m): void
    {
        $hasFrom = Schema::hasColumn($m->getTable(), 'from');
        $hasTo   = Schema::hasColumn($m->getTable(), 'to');

        $start = $m->getAttribute(PJC::COL_S_DT);
        $from  = $hasFrom ? $m->getAttribute('from') : null;

        if ($start === null && $hasFrom && $from !== null)
            $m->setAttribute(PJC::COL_S_DT, $from);
        elseif ($start !== null && $hasFrom)
            $m->setAttribute('from', $start);

        $end = $m->getAttribute(PJC::COL_E_DT);
        $to  = $hasTo ? $m->getAttribute('to') : null;

        if ($end === null && $hasTo && $to !== null)
            $m->setAttribute(PJC::COL_E_DT, $to);
        elseif ($end !== null && $hasTo)
            $m->setAttribute('to', $end);
    }

    protected static function normalizeEnumsAndType(self $m): void
    {
        $freq = Frequency::normalize($m->getAttribute('frequency') ?? null) ?? Frequency::Variable;
        $m->setAttribute('frequency', $freq->value);

        $st = EvaluationStatus::normalize($m->getAttribute('status') ?? null) ?? EvaluationStatus::Pending;
        $m->setAttribute('status', $st->value);

        $t = strtolower(trim((string) ($m->getAttribute('type') ?? '')));
        if ($t === '')
            $m->setAttribute('type', null);
        elseif (!in_array($t, ['revenue', 'expense', 'mixed'], true))
            $m->setAttribute('type', null);
        else
            $m->setAttribute('type', $t);

        $cur = strtoupper(trim((string) ($m->getAttribute('currency') ?? '')));
        if ($cur === '')
            $cur = (string) (defined(SC::class . '::DEF_SITE_CURRENCY_ID') ? SC::DEF_SITE_CURRENCY_ID : 'USD');
        if (strlen($cur) !== 3)
            $cur = substr($cur, 0, 3);
        $m->setAttribute('currency', $cur);
    }

    protected static function normalizeThresholds(self $m): void
    {
        $warn = $m->getAttribute(BC::COL_WRN_TRSH);
        $crit = $m->getAttribute(BC::COL_CRT_WRN_TH);

        $warnN = self::normalizePercentOrNull($warn);
        $critN = self::normalizePercentOrNull($crit);

        if ($warnN !== null)
            $m->setAttribute(BC::COL_WRN_TRSH, $warnN);
        else
            $m->setAttribute(BC::COL_WRN_TRSH, null);

        if ($critN !== null && $warnN !== null && $critN < $warnN)
            $critN = $warnN;

        if ($critN !== null)
            $m->setAttribute(BC::COL_CRT_WRN_TH, $critN);
        else
            $m->setAttribute(BC::COL_CRT_WRN_TH, null);
    }

    protected static function normalizePercentOrNull(mixed $value): ?float
    {
        if ($value === null)
            return null;

        if (!is_numeric($value))
            return null;

        $v = (float) $value;
        if ($v < 0.0) $v = 0.0;
        if ($v > 100.0) $v = 100.0;
        return round($v, 2);
    }

    protected static function normalizeJsonLists(self $m): void
    {
        $m->setAttribute(BC::COL_BNK_TRFS, json_encode($m->normalizeStringList($m->getAttribute(BC::COL_BNK_TRFS)) ?? []));
        $m->setAttribute('transactions', json_encode($m->normalizeStringList($m->getAttribute('transactions')) ?? []));
        $m->setAttribute(BC::COL_CARD_NTS, json_encode($m->normalizeStringList($m->getAttribute(BC::COL_CARD_NTS)) ?? []));
        $m->setAttribute('receipts', json_encode($m->sanitizeAttachmentList($m->normalizeStringList($m->getAttribute('receipts')) ?? [])));
        $m->setAttribute('attachments', json_encode($m->sanitizeAttachmentList($m->normalizeStringList($m->getAttribute('attachments')) ?? [])));
    }

    protected static function enrichReceiptsFromLinkedEntities(self $m): void
    {
        $receipts = $m->normalizeStringList($m->getAttribute('receipts')) ?? [];
        $receipts = array_values(array_unique($receipts));
        $targets = [
            [DC::TABLE_TRS, $m->normalizeStringList($m->getAttribute('transactions')) ?? []],
            [DC::TABLE_BNK_TRF, $m->normalizeStringList($m->getAttribute(BC::COL_BNK_TRFS)) ?? []],
        ];
        if (defined(DC::class . '::TABLE_CR_NOTES'))
            $targets[] = [DC::TABLE_CR_NOTES, []];
        if (defined(DC::class . '::TABLE_DB_NOTES'))
            $targets[] = [DC::TABLE_DB_NOTES, []];
        $cardNoteIds = $m->normalizeStringList($m->getAttribute(BC::COL_CARD_NTS)) ?? [];
        if (defined(DC::class . '::TABLE_CR_NOTES') && $cardNoteIds)
            $targets[] = [DC::TABLE_CR_NOTES, $cardNoteIds];
        if (defined(DC::class . '::TABLE_DB_NOTES') && $cardNoteIds)
            $targets[] = [DC::TABLE_DB_NOTES, $cardNoteIds];
        foreach ($targets as [$table, $ids]) {
            try {
                if (!$ids)
                    continue;
                if (!Schema::hasTable($table))
                    continue;
                $cols = [];
                foreach (['receipt', BC::COL_RCP_MD, 'attachments'] as $c)
                    if (Schema::hasColumn($table, $c))
                        $cols[] = $c;
                if (!$cols)
                    continue;
                $rows = DB::table($table)->select(array_merge(['id'], $cols))->whereIn('id', $ids)->get();
                foreach ($rows as $r)
                    foreach ($cols as $c)
                        $receipts = array_merge($receipts, self::extractReceiptTokens($r->{$c} ?? null));
            } catch (\Throwable $e) {
                ErrorHandler::evaluateExistenceToLogChannel(
                    'budget_errors',
                    candidate: [
                        'message' => "Enriching receipts from table {$table} failed",
                        'context' => [
                            'budget_id' => $m->getAttribute('id'),
                            'table'     => $table,
                            'error'     => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                        ],
                    ],
                );
                continue;
            }
        }
        $receipts = array_values(array_unique($m->sanitizeAttachmentList($receipts)));
        $m->setAttribute('receipts', json_encode($receipts));
    }

    protected static function extractReceiptTokens(mixed $value): array
    {
        if ($value === null)
            return [];
        if (is_array($value))
            return self::handleReceiptArraysRecursively($value);
        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '')
                return [];
            if (self::looksLikeJson($trim)) {
                $decoded = json_decode($trim, true);
                if (is_array($decoded))
                    return self::extractReceiptTokens($decoded);
            }
            return [$trim];
        }
        if (is_scalar($value))
            return [trim((string) $value)];
        return [];
    }
    protected static function handleReceiptArraysRecursively(array $value): array
    {
        $result = [];
        foreach ($value as $item) {
            if (is_array($item))
                $result = array_merge($result, self::handleReceiptArraysRecursively($item));
            elseif (is_scalar($item)) {
                $trimmed = trim((string)$item);
                if ($trimmed !== '')
                    $result[] = $trimmed;
            }
        }
        return $result;
    }

    protected static function mergeReceiptsIntoAttachments(self $m): void
    {
        $receipts    = $m->normalizeStringList($m->getAttribute('receipts')) ?? [];
        $attachments = $m->normalizeStringList($m->getAttribute('attachments')) ?? [];
        $merged = array_values(array_unique(array_merge($attachments, $receipts)));
        $merged = $m->sanitizeAttachmentList($merged);
        $m->setAttribute('attachments', json_encode($merged));
    }

    protected function sanitizeAttachmentList(array $values): array
    {
        $out = [];
        foreach ($values as $v) {
            $san = static::sanitizeAttachmentValue($v, $this);
            if ($san === null)
                continue;
            $out[] = $san;
        }
        return array_values(array_unique($out));
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID);
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company');
    }

    public function branchModel(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch');
    }

    public function departmentModel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_SBM_BY);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_APV_BY);
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_REJ_BY);
    }

    public function getAvailabilityDateAttribute(): string
    {
        $start = $this->getAttribute(PJC::COL_S_DT);
        $end   = $this->getAttribute(PJC::COL_E_DT);

        if ($start === null && $end === null)
            return '';

        $s = $start ? CarbonImmutable::parse($start)->format('Y-m-d') : '';
        $e = $end ? CarbonImmutable::parse($end)->format('Y-m-d') : '';
        return trim($s . ($s && $e ? ' → ' : '') . $e);
    }

    public function getIsExpiredAttribute(): bool
    {
        $end = $this->getAttribute(PJC::COL_E_DT);
        if ($end === null)
            return false;

        try {
            return CarbonImmutable::parse($end)->isPast();
        } catch (\Throwable) {
            return false;
        }
    }

    public static function percentage(float|int|null $actual = null, float|int|null $budget = null): string
    {
        $a = (float) ($actual ?? 0.0);
        $b = (float) ($budget ?? 0.0);
        if ($b <= 0.0)
            return '0.00';
        return number_format(($a * 100.0) / $b, 2, '.', '');
    }

    public function cachedTransactionsSum(string $amountColumn = 'amount'): float
    {
        $cacheKey = 'tx_sum:' . $amountColumn;
        if (array_key_exists($cacheKey, $this->runtimeCache))
            return (float) $this->runtimeCache[$cacheKey];

        $ids = $this->normalizeStringList($this->getAttribute('transactions')) ?? [];
        if (!$ids || !Schema::hasTable(DC::TABLE_TRS) || !Schema::hasColumn(DC::TABLE_TRS, $amountColumn)) {
            $this->runtimeCache[$cacheKey] = 0.0;
            return 0.0;
        }

        try {
            $sum = (float) DB::table(DC::TABLE_TRS)->whereIn('id', $ids)->sum($amountColumn);
            $this->runtimeCache[$cacheKey] = $sum;
            return $sum;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' cachedTransactionsSum failed', [
                'id'    => $this->getAttribute('id'),
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            $this->runtimeCache[$cacheKey] = 0.0;
            return 0.0;
        }
    }
}
