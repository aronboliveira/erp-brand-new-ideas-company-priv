<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC, UsersConstants as UC};
use App\Enums\{TransactionType, TransferType, UserType};
use App\Traits\{DefinesDates, DescribesCompanyBranch, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, StoresManyRefJson, UsesUuids};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class JournalItem extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;
    use DescribesCompanyBranch;
    use FiltersSecureAttachments;
    use StoresManyRefJson;
    use DefinesDates;
    use SoftDeletes;

    public const JIT_PATTERN = '/^JIT\-[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}\-\d+$/i';


    protected $table = DC::TABLE_JRN_IT;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        'code',
        'journal',
        'account',
        'line',
        'posting_type',
        'debit',
        'credit',
        'currency',
        BC::COL_EXC_RT,
        BC::COL_BNK_ACC,
        BC::COL_BNK_EXT_DT,
        'transaction',
        BC::COL_TRS_TP,
        'transfer',
        'payment',
        BC::COL_TRF_TP,
        BC::COL_PIX_KEY,
        BC::COL_CHK_NM,
        BC::COL_TED_DOC_N,
        'company',
        'branch',
        'department',
        'project',
        'entity',
        'description',
        'memo',
        'notes',
        BC::COL_IS_RCC,
        BC::COL_RCC_DT,
        BC::COL_RCC_DOC,

        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,

        'attachments',
        'taxes',
        'categories',
        'metadata',
    ];

    protected $hidden = [
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_PIX_KEY,
        BC::COL_CHK_NM,
        BC::COL_TED_DOC_N,
    ];

    protected $with = [
        'journalEntry',
        'accountCoa',
        'companyUser',
    ];

    protected $appends = [
        'direction',
        'is_debit',
        'is_credit',
        'signed_amount',
        'tax_total',
        'category_labels',
    ];

    protected $casts = [
        'code' => 'string',
        'line' => 'integer',
        'posting_type' => 'string',
        'debit' => 'float',
        'credit' => 'float',
        'currency' => 'string',

        BC::COL_EXC_RT => 'decimal:6',
        BC::COL_BNK_EXT_DT => 'date',
        BC::COL_TRS_TP => TransactionType::class,
        BC::COL_TRF_TP => TransferType::class,

        BC::COL_IS_RCC => 'boolean',
        BC::COL_RCC_DT => 'datetime',
        BC::COL_NFE_AUTH_AT => 'datetime',

        'attachments' => 'array',
        'taxes' => 'array',
        'categories' => 'array',
        'metadata' => 'array',
    ];

    private const ATTEMPT_CAP = 32;

    private static array $journalCurrencyCache = [];
    private static array $journalExchangeRateCache = [];
    private static ?string $paymentTransferTypeColumn = null;

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $model->normalizeBeforeSave();
                $pattern = self::JIT_PATTERN;
                if (!preg_match($pattern, (string) ($model->getAttribute('code') ?? ''))) {
                    $maxAttempts = 64;
                    do {
                        $maxAttempts--;
                        $uuid = Str::uuid();
                        $timestamp = CarbonImmutable::now()->timestamp;
                        $code = "JIT-{$uuid}-{$timestamp}";
                    } while (JournalItem::query()->where('code', $code)->exists() && $maxAttempts > 0);
                    if ($maxAttempts > 0) $model->setAttribute('code', $code);
                    else throw new \RuntimeException('Failed to generate unique code for JournalItem after maximum attempts.');
                }
                $model->filterTaxes($model);
            } catch (\Throwable $e) {
                Log::error(self::class . ' normalizeBeforeSave failed: ' . $e->getMessage(), [
                    'id' => $model->getAttribute('id'),
                    'journal' => $model->getAttribute('journal'),
                    'account' => $model->getAttribute('account'),
                ]);
                throw $e;
            }
        });
    }

    private function filterTaxes(Model $m): void
    {
        $taxes = $m->getAttribute('taxes') ?? [];

        if (!is_array($taxes) || $taxes === []) {
            $m->setAttribute('taxes', []);
            return;
        }

        $taxes = array_values(array_filter(
            $taxes,
            static function ($it): bool {
                $q = DB::table(DC::TABLE_TAXES);
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

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal', 'id');
    }

    public function accountCoa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account', 'id');
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project', 'id');
    }

    public function entityUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entity', 'id');
    }

    public function reconciliationDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, BC::COL_RCC_DOC, 'id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction', 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment', 'id');
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class, 'transfer', 'id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, BC::COL_BNK_ACC, 'id');
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->direction() === 'debit';
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->direction() === 'credit';
    }

    public function getDirectionAttribute(): string
    {
        return $this->direction();
    }

    public function getSignedAmountAttribute(): float
    {
        $debit = $this->safeFloat($this->getAttribute('debit'));
        $credit = $this->safeFloat($this->getAttribute('credit'));
        $net = $debit - $credit;

        return $this->direction() === 'credit' ? -abs($net) : abs($net);
    }

    public function getTaxTotalAttribute(): float
    {
        return $this->computeTaxTotal();
    }

    public function getCategoryLabelsAttribute(): array
    {
        return $this->computeCategoryLabels();
    }

    public function scopeForCompany($query, string $companyId)
    {
        return $query->where('company', $companyId);
    }

    public function scopeDebit($query)
    {
        return $query->where('posting_type', 'debit');
    }

    public function scopeCredit($query)
    {
        return $query->where('posting_type', 'credit');
    }

    public function scopeReconciled($query)
    {
        return $query->where(BC::COL_IS_RCC, true);
    }

    public function refreshJournalCaches(): void
    {
        $jid = (string) ($this->getAttribute('journal') ?? '');
        if ($jid === '') return;

        unset(self::$journalCurrencyCache[$jid], self::$journalExchangeRateCache[$jid]);
        $this->loadJournalCurrency($jid);
        $this->loadJournalExchangeRate($jid);
    }

    private function normalizeBeforeSave(): void
    {
        $this->assertRequiredUuids();

        $this->normalizePostingTypeAndAmounts();
        $this->normalizeLine();
        $this->normalizeCurrencyAndExchangeRate();
        $this->normalizeTransactionTypeFromTransaction();
        $this->normalizeTransferTypeFromPayment();
        $this->normalizeReconciliationFields();
        $this->normalizeNfeFields();

        $this->normalizeJsonFields([
            'attachments',
            'taxes',
            'categories',
            'metadata',
        ]);

        $this->enforceCompanyType();
    }

    private function assertRequiredUuids(): void
    {
        $journal = (string) ($this->getAttribute('journal') ?? '');
        $account = (string) ($this->getAttribute('account') ?? '');
        $company = (string) ($this->getAttribute('company') ?? '');

        if ($journal === '') {
            Log::error(self::class . ' missing required journal FK');
            throw new \RuntimeException('JournalItem requires a journal FK.');
        }

        if ($account === '') {
            Log::error(self::class . ' missing required account FK');
            throw new \RuntimeException('JournalItem requires an account FK.');
        }

        if ($company === '') {
            if (defined(DC::class . '::DEFAULT_UUID')) {
                $this->setAttribute('company', DC::DEFAULT_UUID);
                Log::warning(self::class . ' missing company; defaulted to DEFAULT_UUID', ['journal' => $journal]);
                return;
            }
            Log::error(self::class . ' missing required company FK', ['journal' => $journal]);
            throw new \RuntimeException('JournalItem requires a company FK.');
        }
    }

    private function normalizePostingTypeAndAmounts(): void
    {
        $rawType = $this->getAttribute('posting_type');
        $type = is_string($rawType) ? strtolower(trim($rawType)) : null;

        if (!in_array($type, ['debit', 'credit'], true)) {
            if ($type !== null)
                Log::warning(self::class . ' invalid posting_type; defaulting to debit', ['posting_type' => $rawType]);
            $type = 'debit';
            $this->setAttribute('posting_type', $type);
        }

        $debit = $this->safeFloat($this->getAttribute('debit'));
        $credit = $this->safeFloat($this->getAttribute('credit'));

        if ($debit < 0.0) {
            Log::warning(self::class . ' negative debit coerced to 0', ['debit' => $debit]);
            $debit = 0.0;
        }
        if ($credit < 0.0) {
            Log::warning(self::class . ' negative credit coerced to 0', ['credit' => $credit]);
            $credit = 0.0;
        }

        $debit = $this->round6($debit);
        $credit = $this->round6($credit);

        if ($type === 'debit' && $credit > 0.0 && $debit <= 0.0) {
            $debit = $credit;
            $credit = 0.0;
        } elseif ($type === 'credit' && $debit > 0.0 && $credit <= 0.0) {
            $credit = $debit;
            $debit = 0.0;
        }

        if ($type === 'debit' && $credit > 0.0) $credit = 0.0;
        if ($type === 'credit' && $debit > 0.0) $debit = 0.0;

        $this->setAttribute('debit', $debit);
        $this->setAttribute('credit', $credit);
    }

    private function normalizeLine(): void
    {
        $line = $this->getAttribute('line');

        if ($line !== null) {
            $lineInt = (int) $line;
            if ($lineInt <= 0) {
                Log::warning(self::class . ' invalid line coerced to null', ['line' => $line]);
                $this->setAttribute('line', null);
            } else {
                $this->setAttribute('line', $lineInt);
            }
            return;
        }

        $journalId = (string) ($this->getAttribute('journal') ?? '');
        if ($journalId === '') return;

        $attempts = 0;
        while ($attempts++ < self::ATTEMPT_CAP) {
            try {
                $row = DB::select(
                    'select max(line) as m from ' . $this->table . ' where journal = ?',
                    [$journalId]
                );
                $max = (int) (($row[0]->m ?? 0) ?: 0);
                $next = max(1, $max + 1);

                $this->setAttribute('line', $next);
                return;
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed computing next line; retrying', [
                    'journal' => $journalId,
                    'attempt' => $attempts,
                    'err' => $e->getMessage(),
                ]);
            }
        }

        Log::warning(self::class . ' line attempts exhausted; leaving as null', ['journal' => $journalId]);
    }

    private function normalizeCurrencyAndExchangeRate(): void
    {
        $journalId = (string) ($this->getAttribute('journal') ?? '');
        if ($journalId === '') return;

        $journalCurrency = $this->loadJournalCurrency($journalId);
        $journalExcRt = $this->loadJournalExchangeRate($journalId);

        $rawCurrency = $this->getAttribute('currency');
        $currency = is_string($rawCurrency) ? strtoupper(trim($rawCurrency)) : null;

        if ($currency === null || $currency === '') $currency = $journalCurrency ?: (string) SC::DEF_SITE_CURRENCY_ID;
        if ($journalCurrency && $currency !== $journalCurrency) {
            Log::warning(self::class . ' currency mismatch; coerced to journal currency', [
                'journal' => $journalId,
                'item_currency' => $currency,
                'journal_currency' => $journalCurrency,
            ]);
            $currency = $journalCurrency;
        }

        $this->setAttribute('currency', $currency);

        $excRaw = $this->getAttribute(BC::COL_EXC_RT);
        $exc = $this->safeFloat($excRaw);
        if ($exc <= 0.0) $exc = $journalExcRt > 0.0 ? $journalExcRt : 1.0;

        $this->setAttribute(BC::COL_EXC_RT, $this->round6($exc));
    }

    private function normalizeTransactionTypeFromTransaction(): void
    {
        $txId = (string) ($this->getAttribute('transaction') ?? '');
        if ($txId === '') return;

        if (!Schema::hasTable(DC::TABLE_TRS) || !Schema::hasColumn(DC::TABLE_TRS, BC::COL_PAY_TP)) {
            Log::warning(self::class . ' cannot enforce transaction type; missing transactions table/column', [
                'table' => DC::TABLE_TRS,
                'col' => BC::COL_PAY_TP,
            ]);
            return;
        }

        try {
            $row = DB::select(
                'select ' . BC::COL_PAY_TP . ' as tp from ' . DC::TABLE_TRS . ' where id = ? limit 1',
                [$txId]
            );

            $tp = (string) (($row[0]->tp ?? '') ?: '');
            if ($tp === '') return;

            $normalized = TransactionType::normalize($tp) ?? TransactionType::Other;
            $this->setAttribute(BC::COL_TRS_TP, $normalized->value);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed enforcing transaction type from transaction', [
                'transaction' => $txId,
                'err' => $e->getMessage(),
            ]);
        }
    }

    private function normalizeTransferTypeFromPayment(): void
    {
        $payId = (string) ($this->getAttribute('payment') ?? '');
        if ($payId === '') return;

        if (!Schema::hasTable(DC::TABLE_PAY)) {
            Log::warning(self::class . ' cannot enforce transfer type; payments table missing', ['table' => DC::TABLE_PAY]);
            return;
        }

        $col = $this->resolvePaymentTransferTypeColumn();
        if ($col === null) {
            Log::warning(self::class . ' cannot enforce transfer type; no suitable column found in payments table', [
                'table' => DC::TABLE_PAY,
                'candidates' => [BC::COL_TRF_TP, BC::COL_PAY_TP],
            ]);
            return;
        }

        try {
            $row = DB::select(
                'select ' . $col . ' as tp from ' . DC::TABLE_PAY . ' where id = ? limit 1',
                [$payId]
            );

            $tp = (string) (($row[0]->tp ?? '') ?: '');
            if ($tp === '') return;

            $normalized = TransferType::normalize($tp);
            $this->setAttribute(BC::COL_TRF_TP, $normalized->value);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed enforcing transfer type from payment', [
                'payment' => $payId,
                'col' => $col,
                'err' => $e->getMessage(),
            ]);
        }
    }

    private function normalizeReconciliationFields(): void
    {
        $is = $this->getAttribute(BC::COL_IS_RCC);
        $this->setAttribute(BC::COL_IS_RCC, (bool) $is);

        $flag = (bool) $this->getAttribute(BC::COL_IS_RCC);
        $dt = $this->getAttribute(BC::COL_RCC_DT);

        if (!$flag && $dt !== null) {
            $this->setAttribute(BC::COL_RCC_DT, null);
            $this->setAttribute(BC::COL_RCC_DOC, null);
            return;
        }

        if ($flag && $dt === null) {
            $this->setAttribute(BC::COL_RCC_DT, CarbonImmutable::now()->toDateTimeString());
            return;
        }
    }

    private function normalizeNfeFields(): void
    {
        $key = $this->getAttribute(BC::COL_NFE_KEY);
        if ($key === null) return;

        $keyStr = preg_replace('/\D+/', '', (string) $key);
        if ($keyStr === '') {
            $this->setAttribute(BC::COL_NFE_KEY, null);
            return;
        }

        if (strlen($keyStr) !== 44) {
            Log::warning(self::class . ' invalid NFe key length; nullified', [
                'len' => strlen($keyStr),
                'key' => $keyStr,
            ]);
            $this->setAttribute(BC::COL_NFE_KEY, null);
            return;
        }

        $this->setAttribute(BC::COL_NFE_KEY, $keyStr);
    }

    private function normalizeJsonFields(array $fields): void
    {
        foreach ($fields as $field) {
            try {
                $raw = $this->getRawOriginal($field);

                if ($raw === null) {
                    $this->setAttribute($field, null);
                    continue;
                }

                if (is_string($raw) && trim($raw) === '') {
                    $this->setAttribute($field, null);
                    continue;
                }

                $normalized = self::normalizeArrayField($raw);
                $this->setAttribute($field, $normalized ?: null);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed normalizing json field; leaving as-is', [
                    'field' => $field,
                    'err' => $e->getMessage(),
                ]);
            }
        }
    }

    private function enforceCompanyType(): void
    {
        $companyId = (string) ($this->getAttribute('company') ?? '');
        if ($companyId === '') return;

        if (!Schema::hasTable(DC::TABLE_USERS) || !Schema::hasColumn(DC::TABLE_USERS, 'type')) return;

        try {
            $row = DB::select('select type as t from ' . DC::TABLE_USERS . ' where id = ? limit 1', [$companyId]);
            $t = (string) (($row[0]->t ?? '') ?: '');
            if ($t === '') return;

            $enum = UserType::normalize($t);
            $allowed = in_array($enum, [UserType::Company, UserType::Vendor], true);

            if (!$allowed)
                Log::warning(self::class . ' company user type not allowed for JournalItem company', [
                    'company' => $companyId,
                    'type_raw' => $t,
                    'type_norm' => $enum->value,
                ]);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed validating company type', [
                'company' => $companyId,
                'err' => $e->getMessage(),
            ]);
        }
    }

    private function direction(): string
    {
        $raw = $this->getAttribute('posting_type');
        $t = is_string($raw) ? strtolower(trim($raw)) : 'debit';

        return in_array($t, ['debit', 'credit'], true) ? $t : 'debit';
    }

    private function computeTaxTotal(): float
    {
        $raw = $this->getAttribute('taxes');
        if (!is_array($raw) || !$raw) return 0.0;

        $lines = $raw['lines'] ?? null;
        if (!is_array($lines)) return 0.0;

        $total = 0.0;
        foreach ($lines as $line) {
            if (!is_array($line)) continue;

            $amount = null;

            if (array_key_exists('tax_amount', $line)) $amount = $line['tax_amount'];
            elseif (array_key_exists('amount', $line)) $amount = $line['amount'];
            elseif (array_key_exists('base', $line) && array_key_exists('rate', $line)) {
                $base = $this->safeFloat($line['base']);
                $rate = $this->safeFloat($line['rate']);
                $amount = $base * $rate;
            }

            $total += $this->safeFloat($amount);
        }

        return $this->round6(max(0.0, $total));
    }

    private function computeCategoryLabels(): array
    {
        $raw = $this->getAttribute('categories');
        if (!is_array($raw) || !$raw) return [];

        $out = [];
        foreach ($raw as $v) {
            if (is_string($v)) {
                $s = trim($v);
                if ($s !== '') $out[] = $s;
                continue;
            }
            if (is_array($v) && isset($v['name']) && is_string($v['name'])) {
                $s = trim($v['name']);
                if ($s !== '') $out[] = $s;
            }
        }

        $out = array_values(array_unique($out));
        return $out;
    }

    private function loadJournalCurrency(string $journalId): ?string
    {
        if (isset(self::$journalCurrencyCache[$journalId]))
            return self::$journalCurrencyCache[$journalId];

        if (!Schema::hasTable(DC::TABLE_JOURNAL_ENTRIES)) {
            self::$journalCurrencyCache[$journalId] = null;
            return null;
        }

        try {
            $row = DB::select(
                'select currency as c from ' . DC::TABLE_JOURNAL_ENTRIES . ' where id = ? limit 1',
                [$journalId]
            );

            $c = (string) (($row[0]->c ?? '') ?: '');
            $c = strtoupper(trim($c));
            self::$journalCurrencyCache[$journalId] = $c !== '' ? $c : null;

            return self::$journalCurrencyCache[$journalId];
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed loading journal currency', [
                'journal' => $journalId,
                'err' => $e->getMessage(),
            ]);
            self::$journalCurrencyCache[$journalId] = null;
            return null;
        }
    }

    private function loadJournalExchangeRate(string $journalId): float
    {
        if (isset(self::$journalExchangeRateCache[$journalId]))
            return (float) self::$journalExchangeRateCache[$journalId];

        if (!Schema::hasTable(DC::TABLE_JOURNAL_ENTRIES) || !Schema::hasColumn(DC::TABLE_JOURNAL_ENTRIES, BC::COL_EXC_RT)) {
            self::$journalExchangeRateCache[$journalId] = 1.0;
            return 1.0;
        }

        try {
            $row = DB::select(
                'select ' . BC::COL_EXC_RT . ' as r from ' . DC::TABLE_JOURNAL_ENTRIES . ' where id = ? limit 1',
                [$journalId]
            );

            $r = $this->safeFloat($row[0]->r ?? 1.0);
            $r = $r > 0.0 ? $r : 1.0;
            self::$journalExchangeRateCache[$journalId] = $this->round6($r);

            return (float) self::$journalExchangeRateCache[$journalId];
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed loading journal exchange rate', [
                'journal' => $journalId,
                'err' => $e->getMessage(),
            ]);
            self::$journalExchangeRateCache[$journalId] = 1.0;
            return 1.0;
        }
    }

    private function resolvePaymentTransferTypeColumn(): ?string
    {
        if (self::$paymentTransferTypeColumn !== null)
            return self::$paymentTransferTypeColumn !== '' ? self::$paymentTransferTypeColumn : null;

        $candidates = [BC::COL_TRF_TP, BC::COL_PAY_TP];
        foreach ($candidates as $col) {
            if (Schema::hasColumn(DC::TABLE_PAY, $col)) {
                self::$paymentTransferTypeColumn = $col;
                return $col;
            }
        }

        self::$paymentTransferTypeColumn = '';
        return null;
    }

    private function safeFloat(mixed $value): float
    {
        if ($value === null) return 0.0;
        if (is_bool($value)) return $value ? 1.0 : 0.0;
        if (is_int($value) || is_float($value)) return (float) $value;

        if (is_string($value)) {
            $v = trim($value);
            if ($v === '') return 0.0;

            $v = str_replace([' ', ','], ['', '.'], $v);
            return is_numeric($v) ? (float) $v : 0.0;
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function round6(float $v): float
    {
        return (float) number_format($v, 6, '.', '');
    }
}
