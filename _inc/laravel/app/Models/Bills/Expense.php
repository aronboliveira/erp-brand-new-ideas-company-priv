<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SettingsConstants as SC
};
use App\Enums\{
    AppModuleType,
    EvaluationStatus,
    MonthName,
    TransferType
};
use App\Traits\{
    FiltersSecureAttachments,
    HasAuditFields,
    HasCreditCardInfo,
    HasNfeColumns,
    HasPaymentColumns,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Log;

class Expense extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;
    use HasPaymentColumns;
    use HasCreditCardInfo;
    use HasNfeColumns;
    use FiltersSecureAttachments;

    protected $table = DC::TABLE_EXP;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $with = [
        'companyUser',
        'branchModel',
        'departmentModel',
        'accountantUser',
        'vendorModel',
        'posModel',
        'billModel',
        'orderModel',
        'projectModel',
        'projectTaskModel',
        'taskModel',
        'invoiceModel',
    ];

    protected $appends = [
        'taxes_count',
        'has_attachment',
        'has_receipt',
    ];

    protected $casts = [
        'module'     => AppModuleType::class,
        'evaluation' => EvaluationStatus::class,
        BC::COL_TTL_AMT => 'decimal:2',
        BC::COL_EXC_RT  => 'decimal:6',
        'date' => 'date:Y-m-d',
        'taxes'    => 'array',
        'metadata' => 'array',
        'exp_month' => MonthName::class,
        BC::COL_TRF_TP => TransferType::class,
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                self::normalizeCoreFields($m);
                self::ensureJsonCoherence($m);
                self::enforceExpenseCrossFieldConsistency($m);
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving normalization failed', [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'id'    => $m->getAttribute('id'),
                ]);
            }
        });
    }

    protected static function normalizeCoreFields(self $m): void
    {
        $name = $m->getAttribute('name');
        if (is_string($name)) {
            $name = trim($name);
            $m->setAttribute('name', $name === '' ? null : $name);
        }
        $module = $m->getAttribute('module');
        if (is_string($module) || $module === null)
            $m->setAttribute('module', AppModuleType::normalize($module)->value);
        $eval = $m->getAttribute('evaluation');
        if (is_string($eval) || $eval === null)
            $m->setAttribute('evaluation', EvaluationStatus::normalize($eval)->value);
        $cur  = $m->getAttribute('currency');
        $curId = $m->getAttribute(BC::COL_CUR_ID);
        $curNorm = is_string($cur) ? strtoupper(trim($cur)) : null;
        $curIdNorm = is_string($curId) ? strtoupper(trim($curId)) : null;
        if ($curNorm === '') $curNorm = null;
        if ($curIdNorm === '') $curIdNorm = null;
        $base = strtoupper(trim((string) (SC::DEF_SITE_CURRENCY_ID ?? 'BRL')));
        if ($curNorm === null && $curIdNorm === null) {
            $m->setAttribute('currency', $base);
            $m->setAttribute(BC::COL_CUR_ID, $base);
        } elseif ($curNorm === null && $curIdNorm !== null)
            $m->setAttribute('currency', $curIdNorm);
        elseif ($curNorm !== null && $curIdNorm === null)
            $m->setAttribute(BC::COL_CUR_ID, $curNorm);
        elseif ($curNorm !== null && $curIdNorm !== null && $curNorm !== $curIdNorm)
            $m->setAttribute(BC::COL_CUR_ID, $curNorm);
        $ex = $m->getAttribute(BC::COL_EXC_RT);
        if ($ex === null || $ex === '') $m->setAttribute(BC::COL_EXC_RT, 1.000000);
        $total = $m->getAttribute(BC::COL_TTL_AMT);
        if ($total === null || $total === '') $m->setAttribute(BC::COL_TTL_AMT, 0.00);
    }

    protected static function ensureJsonCoherence(self $m): void
    {
        $m->ensureJsonAttributesAreEncoded([
            'taxes',
            'metadata',
        ]);
    }

    protected static function enforceExpenseCrossFieldConsistency(self $m): void
    {
        if (property_exists(BC::class, 'COL_DUE_DT')) {
            $due = $m->getAttribute(BC::COL_DUE_DT);
            $date = $m->getAttribute('date');
            if (($date === null || $date === '') && $due) $m->setAttribute('date', $due);
            if (($due === null || $due === '') && $date) $m->setAttribute(BC::COL_DUE_DT, $date);
        }
        foreach (['attachment', 'receipt'] as $k) {
            $v = $m->getAttribute($k);
            if (is_string($v)) {
                $t = trim($v);
                $m->setAttribute($k, $t === '' ? null : $t);
            }
        }
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company', 'id');
    }

    public function branchModel(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function departmentModel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function accountantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountant', 'id');
    }

    public function vendorModel(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor', 'id');
    }

    public function posModel(): BelongsTo
    {
        return $this->belongsTo(Pos::class, 'pos', 'id');
    }

    public function billModel(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill', 'id');
    }

    public function orderModel(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order', 'id');
    }

    public function projectModel(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function projectTaskModel(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, PJC::COL_PJ_TSK_ID, 'id');
    }

    public function taskModel(): BelongsTo
    {
        return $this->belongsTo(Task::class, AC::COL_TSK_ID, 'id');
    }

    public function invoiceModel(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice', 'id');
    }

    public function getTaxesCountAttribute(): int
    {
        try {
            $taxes = $this->getAttribute('taxes');
            if (is_array($taxes)) return count($taxes);
            if (is_string($taxes) && $taxes !== '') {
                $decoded = json_decode($taxes, true);
                return is_array($decoded) ? count($decoded) : 0;
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' taxes_count decode failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $this->getAttribute('id'),
            ]);
        }
        return 0;
    }

    public function getHasAttachmentAttribute(): bool
    {
        $v = $this->getAttribute('attachment');
        return is_string($v) && trim($v) !== '';
    }

    public function getHasReceiptAttribute(): bool
    {
        $v = $this->getAttribute('receipt');
        return is_string($v) && trim($v) !== '';
    }

    /**
     * Normalized taxes array (ids and/or names), always returning a consistent structure.
     *
     * Example return:
     *  - ['tax_uuid_1', 'ISS', 'ICMS']
     */
    public function taxesList(): array
    {
        return self::normalizeArrayField($this->getAttribute('taxes'));
    }

    /**
     * Lightweight cache pattern for repeated access within a request lifecycle.
     * This intentionally avoids Redis/Cache facade to keep it local and deterministic.
     */
    protected static array $localCache = [];

    public function cachedKey(string $suffix): string
    {
        $id = (string) ($this->getAttribute('id') ?? '');
        return static::class . ':' . $id . ':' . $suffix;
    }

    public function totalTaxAmount(): float
    {
        $k = $this->cachedKey('tax_sum');
        if (array_key_exists($k, self::$localCache))
            return (float) self::$localCache[$k];

        $sum = 0.0;

        try {
            $taxes = $this->taxesList();
            if (!$taxes) return self::$localCache[$k] = 0.0;

            if (class_exists(Tax::class)) {
                $ids = array_values(array_filter($taxes, fn($v) => is_string($v) && self::looksLikeUuid($v)));
                $names = array_values(array_filter($taxes, fn($v) => is_string($v) && !self::looksLikeUuid($v)));

                $q = Tax::query();
                if ($ids) $q->orWhereIn('id', $ids);
                if ($names) $q->orWhereIn('name', $names);

                $rows = $q->get(['amount']);
                foreach ($rows as $r) {
                    $a = $r->getAttribute('amount');
                    $sum += is_numeric($a) ? (float) $a : 0.0;
                }
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' totalTaxAmount failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $this->getAttribute('id'),
            ]);
        }

        return self::$localCache[$k] = $sum;
    }

    /**
     * Example reconciler: updates total_amount as "amount + taxSum" if those columns exist.
     * This is intentionally not auto-called to avoid hidden side effects; call from services as needed.
     */
    public function reconcileTotalAmount(): void
    {
        try {
            $amount = $this->getAttribute('amount');
            $base = is_numeric($amount) ? (float) $amount : 0.0;

            $total = $base + $this->totalTaxAmount();
            $this->setAttribute(BC::COL_TTL_AMT, $total);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' reconcileTotalAmount failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'id'    => $this->getAttribute('id'),
            ]);
        }
    }
}
