<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Enums\{EvaluationStatus, LedgerBookType, PaymentType, UserType};
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{
    Builder,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    SoftDeletes
};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays, SoftDeletes;

    public const JIT_PATTERN = '/^JIT-[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}-\d{10,}$/i';

    protected $table = DC::TABLE_JOURNAL_ENTRIES;

    protected $fillable = [
        'code',
        'name',
        'reference',
        'date',
        AC::COL_PST_DT,
        PJC::COL_RVS_DT,
        'period',

        'author',
        'reviewer',
        BC::COL_ACC_AT,
        BC::COL_REJ_AT,
        BC::COL_REJ_RS,

        'status',
        BC::COL_PAY_TP,
        BC::COL_TTL_DBT,
        BC::COL_TTL_CRT,
        'currency',
        BC::COL_EXC_RT,

        'description',
        'memo',
        'notes',

        'company',
        'branch',
        'department',
        'project',
        'document',

        BC::COL_INV_ID,
        BC::COL_BL_ID,
        BC::COL_OD_ID,
        BC::COL_TRS_ID,
        BC::COL_PAY_ID,
        BC::COL_PSLP_ID,
        BC::COL_EXP_ID,
        BC::COL_POS_ID,
        BC::COL_POS_PAY_ID,
        BC::COL_CRD_NT_ID,
        BC::COL_DBT_NT_ID,
        BC::COL_LN_ID,
        BC::COL_ALW_ID,

        'revenue',
        'contract',
        'deal',
        PJC::COL_JRN_ID,

        BC::COL_IS_RVS,
        BC::COL_RVSING_ID,
        BC::COL_RVSED_ID,

        BC::COL_BK_TP,
        'nire',
        BC::COL_HSH_ECD,
        BC::COL_ECD_TRS,
        BC::COL_ECD_AT,

        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,

        BC::COL_ORG_USER_ID,
        BC::COL_IP_ADR,
        BC::COL_USR_AGT,

        'attachments',
        'tags',
        'metadata',
        'taxes',
        'items',
        'transactions',
    ];

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $with = ['authorUser'];

    protected $hidden = [
        BC::COL_IP_ADR,
        BC::COL_USR_AGT,
        BC::COL_HSH_ECD,
        BC::COL_NFE_XML_PATH,
    ];

    protected $appends = [
        'status_label',
        'payment_type_label',
        'book_type_label',
        'items_total_credit',
        'items_total_debit',
        'is_approved',
        'is_rejected',
    ];

    protected $casts = [
        'date' => 'date',
        AC::COL_PST_DT => 'date',
        PJC::COL_RVS_DT => 'date',

        BC::COL_ACC_AT => 'datetime',
        BC::COL_REJ_AT => 'datetime',
        BC::COL_ECD_AT => 'datetime',
        BC::COL_NFE_AUTH_AT => 'datetime',

        BC::COL_TTL_DBT => 'decimal:6',
        BC::COL_TTL_CRT => 'decimal:6',
        BC::COL_EXC_RT => 'decimal:6',

        BC::COL_ECD_TRS => 'boolean',
        BC::COL_IS_RVS => 'boolean',

        'attachments' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'taxes' => 'array',
        'items' => 'array',
        'transactions' => 'array',

        'deleted_at' => 'datetime',
    ];

    private const COL_CREDIT = 'credit';
    private const COL_DEBIT  = 'debit';

    /** @var array<string, array{credit: float, debit: float}> */
    private static array $itemsTotalsCache = [];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->normalizeOnSave();
                $m->filterItems($m);
                $m->filterTaxes($m);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' normalizeOnSave failed: ' . $e->getMessage(), [
                    'id' => $m->getKey(),
                ]);
            }
        });
    }

    private function filterTransactions(Model $m): void
    {
        $trs = $m->getAttribute('transactions') ?? [];
        if (!is_array($trs) || $trs === []) {
            $m->setAttribute('transactions', []);
            return;
        }
        $q = DB::table(DC::TABLE_TRS);
        $trs = array_values(array_filter(
            $trs,
            static function ($it) use ($q): bool {
                if (is_string($it)) {
                    $v = trim($it);
                    if ($v === '') return false;
                    return $q
                        ->where(function ($w) use ($v) {
                            $w->where('id', $v)->orWhere('name', $v);
                        })
                        ->exists();
                }
                if (is_array($it)) {
                    $id   = isset($it['id']) && is_string($it['id']) ? trim($it['id']) : null;
                    $name = isset($it['name']) && is_string($it['name']) ? trim($it['name']) : null;
                    if (($id === null || $id === '') && ($name === null || $name === ''))
                        return false;
                    return $q
                        ->where(function ($w) use ($id, $name) {
                            if ($id !== null && $id !== '')
                                $w->where('id', $id);
                            if ($name !== null && $name !== '')
                                $w->orWhere('name', $name);
                        })
                        ->exists();
                }
                return false;
            }
        ));
        if ($trs === []) {
            $m->setAttribute('transactions', []);
            return;
        }
        $existingUuids = DB::table(DC::TABLE_TRS)
            ->whereIn('id', $trs)
            ->pluck('id')
            ->all();
        $existingSet = array_fill_keys($existingUuids, true);
        $trs = array_values(array_filter(
            $trs,
            static fn(string $it): bool =>
            isset($existingSet[$it])
        ));
        $m->setAttribute('transactions', $trs);
    }

    private function filterTaxes(Model $m): void
    {
        $taxes = $m->getAttribute('taxes') ?? [];

        if (!is_array($taxes) || $taxes === []) {
            $m->setAttribute('taxes', []);
            return;
        }

        $q = DB::table(DC::TABLE_TAXES);
        $taxes = array_values(array_filter(
            $taxes,
            static function ($it) use ($q): bool {
                if (is_string($it)) {
                    $v = trim($it);
                    if ($v === '') return false;
                    return $q
                        ->where(function ($w) use ($v) {
                            $w->where('id', $v)->orWhere('name', $v);
                        })
                        ->exists();
                }
                if (is_array($it)) {
                    $id   = isset($it['id']) && is_string($it['id']) ? trim($it['id']) : null;
                    $name = isset($it['name']) && is_string($it['name']) ? trim($it['name']) : null;
                    if (($id === null || $id === '') && ($name === null || $name === ''))
                        return false;
                    return $q
                        ->where(function ($w) use ($id, $name) {
                            if ($id !== null && $id !== '')
                                $w->where('id', $id);
                            if ($name !== null && $name !== '')
                                $w->orWhere('name', $name);
                        })
                        ->exists();
                }
                return false;
            }
        ));
        if ($taxes === []) {
            $m->setAttribute('taxes', []);
            return;
        }
        $m->setAttribute('taxes', $taxes);
    }

    private function filterItems(Model $m): void
    {
        $items = $m->getAttribute('items') ?? [];
        if (!is_array($items) || $items === []) {
            $m->setAttribute('items', []);
            return;
        }
        $items = array_values(array_filter(
            $items,
            static fn($it): bool =>
            is_string($it) && trim($it) !== ''
        ));
        $pattern = self::JIT_PATTERN;
        $items = array_values(array_filter(
            $items,
            static fn(string $it): bool =>
            preg_match($pattern, $it) === 1
        ));
        if ($items === []) {
            $m->setAttribute('items', []);
            return;
        }
        $existingCodes = JournalItem::query()
            ->whereIn('code', $items)
            ->pluck('code')
            ->all();
        $existingSet = array_fill_keys($existingCodes, true);
        $items = array_values(array_filter(
            $items,
            static fn(string $it): bool =>
            isset($existingSet[$it])
        ));
        $m->setAttribute('items', $items);
    }

    private function normalizeOnSave(): void
    {
        $this->ensureUniqueCode();
        $this->ensureTotalsAndCurrency();
        $this->ensureDatesAndPeriod();
        $this->ensureEnums();
        $this->ensureUserConstraints();
        $this->ensureCompanyConstraint();
        $this->ensureReversalConsistency();
        $this->ensurePosPaymentConsistency();
        $this->ensureNfeFields();
        $this->ensureJsonAttributesAreEncodedSafe([
            'attachments',
            'tags',
            'metadata',
            'taxes',
            'items',
            'transactions',
        ]);
    }

    private static function looksLikeUuid(?string $value): bool
    {
        if ($value === null) return false;
        $v = trim($value);
        if ($v === '') return false;

        return (bool) preg_match(
            '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[1-5][0-9a-f]{3}\-[89ab][0-9a-f]{3}\-[0-9a-f]{12}$/i',
            $v
        );
    }

    private function ensureUniqueCode(): void
    {
        $existing = (string) ($this->getAttribute('code') ?? '');
        if (trim($existing) !== '') return;

        $attempt = 0;
        $limit = 14;

        do {
            $attempt++;
            $code = 'JE-' . now()->format('YmdHis') . '-' . (string) Str::uuid();

            $exists = false;
            try {
                $row = DB::selectOne(
                    'select 1 as ok from ' . $this->getTable() . ' where code = ? limit 1',
                    [$code]
                );
                $exists = $row !== null;
            } catch (\Throwable $e) {
                Log::debug(self::class . ' ensureUniqueCode existence check failed: ' . $e->getMessage(), [
                    'code' => $code,
                    'id' => $this->getKey(),
                ]);
                $exists = false;
            }

            if (!$exists) {
                $this->setAttribute('code', $code);
                return;
            }
        } while ($attempt < $limit);

        $this->setAttribute('code', 'JE-' . now()->format('YmdHis') . '-' . (string) Str::uuid());
    }

    private function ensureTotalsAndCurrency(): void
    {
        $debit = $this->getAttribute(BC::COL_TTL_DBT);
        if ($debit === null || $debit === '' || !is_numeric($debit))
            $this->setAttribute(BC::COL_TTL_DBT, 0.0);
        elseif ((float) $debit < 0)
            $this->setAttribute(BC::COL_TTL_DBT, 0.0);

        $credit = $this->getAttribute(BC::COL_TTL_CRT);
        if ($credit === null || $credit === '' || !is_numeric($credit))
            $this->setAttribute(BC::COL_TTL_CRT, 0.0);
        elseif ((float) $credit < 0)
            $this->setAttribute(BC::COL_TTL_CRT, 0.0);

        $cur = (string) ($this->getAttribute('currency') ?? '');
        if (trim($cur) === '')
            $this->setAttribute('currency', (string) SC::DEF_SITE_CURRENCY_ID);

        $ex = $this->getAttribute(BC::COL_EXC_RT);
        if ($ex === null || $ex === '' || !is_numeric($ex) || (float) $ex <= 0)
            $this->setAttribute(BC::COL_EXC_RT, 1.0);

        $ecd = $this->getAttribute(BC::COL_ECD_TRS);
        if ($ecd === null) return;

        $this->setAttribute(BC::COL_ECD_TRS, (bool) $ecd);
    }

    private function ensureDatesAndPeriod(): void
    {
        $date = $this->getAttribute('date');

        if ($date === null || (is_string($date) && trim($date) === '')) {
            $this->setAttribute('date', now()->toDateString());
            $date = $this->getAttribute('date');
        }

        $posting = $this->getAttribute(AC::COL_PST_DT);
        if ($posting === null || (is_string($posting) && trim($posting) === ''))
            $this->setAttribute(AC::COL_PST_DT, $date);

        try {
            $d = $this->getAttribute('date');
            $p = $this->getAttribute(AC::COL_PST_DT);

            if ($d && $p) {
                $dStr = is_object($d) && method_exists($d, 'toDateString') ? $d->toDateString() : (string) $d;
                $pStr = is_object($p) && method_exists($p, 'toDateString') ? $p->toDateString() : (string) $p;

                if ($pStr !== '' && $dStr !== '' && $pStr < $dStr)
                    $this->setAttribute(AC::COL_PST_DT, $d);
            }
        } catch (\Throwable $e) {
            Log::debug(self::class . ' ensureDatesAndPeriod posting_date normalization failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
        }

        $period = (string) ($this->getAttribute('period') ?? '');
        if (trim($period) !== '') return;

        try {
            $d = $this->getAttribute('date');
            $year = (int) (is_object($d) && method_exists($d, 'format') ? $d->format('Y') : date('Y', strtotime((string) $d)));
            $month = (int) (is_object($d) && method_exists($d, 'format') ? $d->format('n') : date('n', strtotime((string) $d)));
            $q = intdiv(max(1, $month) - 1, 3) + 1;
            $this->setAttribute('period', $year . '-Q' . $q);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' ensureDatesAndPeriod period derivation failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
        }
    }

    private function ensureEnums(): void
    {
        try {
            $statusRaw = $this->getAttribute('status');
            $status = EvaluationStatus::normalize($statusRaw)->value;
            if ($statusRaw === null || (is_string($statusRaw) && trim($statusRaw) === ''))
                $status = EvaluationStatus::Draft->value;
            $this->setAttribute('status', $status);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' ensureEnums status normalization failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
        }

        try {
            $payRaw = $this->getAttribute(BC::COL_PAY_TP);
            if ($payRaw === null || (is_string($payRaw) && trim($payRaw) === '')) {
                $this->setAttribute(BC::COL_PAY_TP, null);
            } else {
                $this->setAttribute(BC::COL_PAY_TP, PaymentType::normalize((string) $payRaw)->value);
            }
        } catch (\Throwable $e) {
            Log::debug(self::class . ' ensureEnums payment_type normalization failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
        }

        try {
            $bkRaw = $this->getAttribute(BC::COL_BK_TP);
            $bk = LedgerBookType::normalize($bkRaw) ?? LedgerBookType::GeneralLedger;
            $this->setAttribute(BC::COL_BK_TP, $bk->value);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' ensureEnums ledger_book_type normalization failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
        }

        $isRvs = $this->getAttribute(BC::COL_IS_RVS);
        if ($isRvs !== null)
            $this->setAttribute(BC::COL_IS_RVS, (bool) $isRvs);
    }

    private function ensureUserConstraints(): void
    {
        foreach (['author', 'reviewer', BC::COL_ORG_USER_ID] as $col) {
            $v = $this->getAttribute($col);
            if ($v === null) continue;

            $s = is_string($v) ? trim($v) : (string) $v;
            if ($s === '' || !self::looksLikeUuid($s))
                $this->setAttribute($col, null);
        }

        $reviewerId = (string) ($this->getAttribute('reviewer') ?? '');
        if (trim($reviewerId) === '') return;

        $attempt = 0;
        $limit = 6;

        while ($attempt++ < $limit) {
            try {
                $row = DB::selectOne(
                    'select `type` as t from ' . DC::TABLE_USERS . ' where id = ? limit 1',
                    [$reviewerId]
                );

                $type = is_object($row) ? (string) ($row->t ?? '') : '';
                $allowed = in_array($type, [
                    UserType::Accountant->value,
                    UserType::Admin->value,
                    UserType::SuperAdmin->value,
                ], true);

                if (!$allowed) {
                    Log::debug(self::class . ' reviewer rejected by type constraint', [
                        'id' => $this->getKey(),
                        'reviewer' => $reviewerId,
                        'type' => $type,
                    ]);
                    $this->setAttribute('reviewer', null);
                }

                return;
            } catch (\Throwable $e) {
                if ($attempt >= $limit) {
                    Log::warning(self::class . ' reviewer type check failed repeatedly: ' . $e->getMessage(), [
                        'id' => $this->getKey(),
                        'reviewer' => $reviewerId,
                    ]);
                    return;
                }
            }
        }
    }

    private function ensureCompanyConstraint(): void
    {
        $companyId = (string) ($this->getAttribute('company') ?? '');
        if (trim($companyId) === '' || !self::looksLikeUuid($companyId)) {
            $this->setAttribute('company', null);
            return;
        }

        $attempt = 0;
        $limit = 6;

        while ($attempt++ < $limit) {
            try {
                $row = DB::selectOne(
                    'select `type` as t from ' . DC::TABLE_USERS . ' where id = ? limit 1',
                    [$companyId]
                );

                $type = is_object($row) ? (string) ($row->t ?? '') : '';
                $allowed = in_array($type, [
                    UserType::Company->value,
                    UserType::Vendor->value,
                ], true);

                if (!$allowed) {
                    Log::debug(self::class . ' company rejected by type constraint', [
                        'id' => $this->getKey(),
                        'company' => $companyId,
                        'type' => $type,
                    ]);
                    $this->setAttribute('company', null);
                }

                return;
            } catch (\Throwable $e) {
                if ($attempt >= $limit) {
                    Log::warning(self::class . ' company type check failed repeatedly: ' . $e->getMessage(), [
                        'id' => $this->getKey(),
                        'company' => $companyId,
                    ]);
                    return;
                }
            }
        }
    }

    private function ensureReversalConsistency(): void
    {
        $id = (string) ($this->getKey() ?? '');

        $rvsing = (string) ($this->getAttribute(BC::COL_RVSING_ID) ?? '');
        $rvsed  = (string) ($this->getAttribute(BC::COL_RVSED_ID) ?? '');

        $rvsing = trim($rvsing) !== '' && self::looksLikeUuid($rvsing) ? $rvsing : '';
        $rvsed  = trim($rvsed) !== '' && self::looksLikeUuid($rvsed) ? $rvsed : '';

        if ($id !== '') {
            if ($rvsing === $id) $rvsing = '';
            if ($rvsed === $id) $rvsed = '';
        }

        if ($rvsing !== '' && $rvsed !== '' && $rvsing === $rvsed)
            $rvsed = '';

        $this->setAttribute(BC::COL_RVSING_ID, $rvsing !== '' ? $rvsing : null);
        $this->setAttribute(BC::COL_RVSED_ID, $rvsed !== '' ? $rvsed : null);

        $hasAny = ($rvsing !== '' || $rvsed !== '');
        $isRvs = (bool) ($this->getAttribute(BC::COL_IS_RVS) ?? false);

        if ($hasAny && !$isRvs)
            $this->setAttribute(BC::COL_IS_RVS, true);
        elseif (!$hasAny && $isRvs)
            $this->setAttribute(BC::COL_IS_RVS, false);
    }

    private function ensurePosPaymentConsistency(): void
    {
        $posPayId = (string) ($this->getAttribute(BC::COL_POS_PAY_ID) ?? '');
        if (trim($posPayId) === '' || !self::looksLikeUuid($posPayId)) return;

        $posId = (string) ($this->getAttribute(BC::COL_POS_ID) ?? '');
        if (trim($posId) === '' || !self::looksLikeUuid($posId)) return;

        $attempt = 0;
        $limit = 6;

        while ($attempt++ < $limit) {
            try {
                $row = DB::selectOne(
                    'select 1 as ok from ' . DC::TABLE_POS_PAY . ' where id = ? and ' . BC::COL_POS_ID . ' = ? limit 1',
                    [$posPayId, $posId]
                );

                if ($row === null) {
                    Log::debug(self::class . ' nullifying pos_payment_id due to pos_id mismatch', [
                        'id' => $this->getKey(),
                        BC::COL_POS_ID => $posId,
                        BC::COL_POS_PAY_ID => $posPayId,
                    ]);
                    $this->setAttribute(BC::COL_POS_PAY_ID, null);
                }

                return;
            } catch (\Throwable $e) {
                if ($attempt >= $limit) {
                    Log::warning(self::class . ' ensurePosPaymentConsistency failed repeatedly: ' . $e->getMessage(), [
                        'id' => $this->getKey(),
                        BC::COL_POS_ID => $posId,
                        BC::COL_POS_PAY_ID => $posPayId,
                    ]);
                    return;
                }
            }
        }
    }

    private function ensureNfeFields(): void
    {
        $key = $this->getAttribute(BC::COL_NFE_KEY);
        if ($key === null) return;

        $raw = is_string($key) ? trim($key) : (string) $key;
        if ($raw === '') {
            $this->setAttribute(BC::COL_NFE_KEY, null);
            return;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (strlen($digits) !== 44) {
            Log::debug(self::class . ' invalid nfe key length', [
                'id' => $this->getKey(),
                'len' => strlen($digits),
                'raw' => $raw,
            ]);
            return;
        }

        $this->setAttribute(BC::COL_NFE_KEY, $digits);
    }

    private function ensureJsonAttributesAreEncodedSafe(array $jsonFields): void
    {
        foreach ($jsonFields as $field) {
            try {
                $current = $this->getAttribute($field);

                if ($current === null || $current === '') continue;

                if (is_array($current) || is_object($current)) {
                    $this->setAttribute($field, self::encodeJsonValue($current, $field));
                    continue;
                }

                if (is_string($current)) {
                    $trimmed = trim($current);
                    if ($trimmed === '') {
                        $this->setAttribute($field, null);
                        continue;
                    }
                    if (!self::looksLikeJson($trimmed))
                        $this->setAttribute($field, self::encodeJsonValue($trimmed, $field));
                }
            } catch (\Throwable $e) {
                Log::debug(self::class . ' ensureJsonAttributesAreEncodedSafe failed: ' . $e->getMessage(), [
                    'id' => $this->getKey(),
                    'field' => $field,
                ]);
            }
        }
    }

    public function getIsApprovedAttribute(): bool
    {
        $ts = $this->getAttribute(BC::COL_ACC_AT);
        return $ts !== null && (string) $ts !== '';
    }

    public function getIsRejectedAttribute(): bool
    {
        $ts = $this->getAttribute(BC::COL_REJ_AT);
        return $ts !== null && (string) $ts !== '';
    }

    public function getStatusLabelAttribute(): string
    {
        try {
            $v = EvaluationStatus::normalize($this->getAttribute('status'))->value;
            $labels = EvaluationStatus::labels();
            return (string) ($labels[$v] ?? Str::headline($v));
        } catch (\Throwable $e) {
            Log::debug(self::class . ' getStatusLabelAttribute failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
            $raw = (string) ($this->getAttribute('status') ?? '');
            return $raw !== '' ? Str::headline($raw) : '';
        }
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        try {
            $raw = $this->getAttribute(BC::COL_PAY_TP);
            if ($raw === null || (is_string($raw) && trim($raw) === '')) return '';

            $v = PaymentType::normalize((string) $raw)->value;
            $labels = PaymentType::labels();
            return (string) ($labels[$v] ?? Str::headline($v));
        } catch (\Throwable $e) {
            Log::debug(self::class . ' getPaymentTypeLabelAttribute failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
            return '';
        }
    }

    public function getBookTypeLabelAttribute(): string
    {
        try {
            $raw = $this->getAttribute(BC::COL_BK_TP);
            $bk = LedgerBookType::normalize($raw) ?? LedgerBookType::GeneralLedger;
            $labels = LedgerBookType::labels();
            return (string) ($labels[$bk->value] ?? $bk->name);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' getBookTypeLabelAttribute failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
            return '';
        }
    }

    private function sumJournalItemsColumn(string $column): float
    {
        if (!in_array($column, [self::COL_CREDIT, self::COL_DEBIT], true))
            return 0.0;

        $id = (string) $this->getKey();
        if ($id === '') return 0.0;

        if (isset(self::$itemsTotalsCache[$id])) {
            return $column === self::COL_CREDIT
                ? (float) (self::$itemsTotalsCache[$id]['credit'] ?? 0.0)
                : (float) (self::$itemsTotalsCache[$id]['debit'] ?? 0.0);
        }

        $attempt = 0;
        $limit = 6;

        while ($attempt++ < $limit) {
            try {
                $itemsTable = (new JournalItem())->getTable();
                $row = DB::selectOne(
                    'select coalesce(sum(credit), 0) as c, coalesce(sum(debit), 0) as d from ' . $itemsTable . ' where journal_id = ?',
                    [$id]
                );

                $credit = (float) ((is_object($row) ? ($row->c ?? 0) : 0) ?? 0);
                $debit  = (float) ((is_object($row) ? ($row->d ?? 0) : 0) ?? 0);

                self::$itemsTotalsCache[$id] = [
                    'credit' => $credit,
                    'debit' => $debit,
                ];

                return $column === self::COL_CREDIT ? $credit : $debit;
            } catch (\Throwable $e) {
                if ($attempt >= $limit) {
                    Log::error(self::class . ' sumJournalItemsColumn failed repeatedly: ' . $e->getMessage(), [
                        'id' => $this->getKey(),
                    ]);
                    return 0.0;
                }
            }
        }

        return 0.0;
    }

    public function getItemsTotalCreditAttribute(): float
    {
        if ($this->relationLoaded('accounts')) {
            try {
                return (float) $this->accounts->sum(self::COL_CREDIT);
            } catch (\Throwable $e) {
                Log::debug(self::class . ' getItemsTotalCreditAttribute loaded-sum failed: ' . $e->getMessage(), [
                    'id' => $this->getKey(),
                ]);
            }
        }

        return $this->sumJournalItemsColumn(self::COL_CREDIT);
    }

    public function getItemsTotalDebitAttribute(): float
    {
        if ($this->relationLoaded('accounts')) {
            try {
                return (float) $this->accounts->sum(self::COL_DEBIT);
            } catch (\Throwable $e) {
                Log::debug(self::class . ' getItemsTotalDebitAttribute loaded-sum failed: ' . $e->getMessage(), [
                    'id' => $this->getKey(),
                ]);
            }
        }

        return $this->sumJournalItemsColumn(self::COL_DEBIT);
    }

    public function hasNfe(): bool
    {
        $key = (string) ($this->getAttribute(BC::COL_NFE_KEY) ?? '');
        return trim($key) !== '';
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author', 'id');
    }

    public function reviewerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer', 'id')
            ->whereIn(UC::COL_TP, [
                UserType::Accountant->value,
                UserType::Admin->value,
                UserType::SuperAdmin->value,
            ]);
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company', 'id')
            ->whereIn(UC::COL_TP, [
                UserType::Company->value,
                UserType::Vendor->value,
            ]);
    }

    public function originalAuthorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_ORG_USER_ID, 'id');
    }

    public function branchRel(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function departmentRel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function projectRel(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project', 'id');
    }

    public function linkedDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document', 'id');
    }

    public function journalDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, PJC::COL_JRN_ID, 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, BC::COL_INV_ID, 'id');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, BC::COL_BL_ID, 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, BC::COL_OD_ID, 'id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, BC::COL_TRS_ID, 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, BC::COL_PAY_ID, 'id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class, BC::COL_PSLP_ID, 'id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, BC::COL_EXP_ID, 'id');
    }

    public function pos(): BelongsTo
    {
        return $this->belongsTo(Pos::class, BC::COL_POS_ID, 'id');
    }

    public function posPayment(): BelongsTo
    {
        return $this->belongsTo(PosPayment::class, BC::COL_POS_PAY_ID, 'id');
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class, BC::COL_CRD_NT_ID, 'id');
    }

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class, BC::COL_DBT_NT_ID, 'id');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, BC::COL_LN_ID, 'id');
    }

    public function allowance(): BelongsTo
    {
        return $this->belongsTo(Allowance::class, BC::COL_ALW_ID, 'id');
    }

    public function revenueRel(): BelongsTo
    {
        return $this->belongsTo(Revenue::class, 'revenue', 'id');
    }

    public function contractRel(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract', 'id');
    }

    public function dealRel(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal', 'id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(JournalItem::class, 'journal_id', 'id');
    }

    public function scopeForCompany(Builder $q, string $companyId): Builder
    {
        if (!self::looksLikeUuid($companyId))
            return $q->whereRaw('1=0');

        return $q->where('company', $companyId);
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->whereNotNull(BC::COL_ACC_AT)->whereNull(BC::COL_REJ_AT);
    }

    public function scopeRejected(Builder $q): Builder
    {
        return $q->whereNotNull(BC::COL_REJ_AT);
    }
}
