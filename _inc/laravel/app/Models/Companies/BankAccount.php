<?php

namespace App\Models;

use App\Config\Constants\{
    BanksConstants as BKC,
    BillsConstants as BLC,
    ChartsConstants as CHTC,
    DatabaseConstants as DC,
    UsersConstants as UC,
    SettingsConstants as SC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};
use Illuminate\Support\Str;

/**
 * @property bool|null $accepts_pix
 * @property int|string|null $currency_id
 * @property array|string|null $debit_cards
 * @property array|string|null $pix_keys
 * @property string|null $profile
 * @property string|null $risk_level
 * @property array|string|null $sync_errors
 * @property array|string|null $vaults
 * @property bool|null $accepts_credit_cards
 * @property bool|null $accepts_debit_cards
 * @property string|null $autoreconcile
 * @property int|null $chart_account_id
 * @property mixed $created_by
 * @property array|string|null $credit_cards
 * @property float|int|null $current_balance
 * @property array|string|null $customField
 * @property bool|null $has_credit_card
 * @property bool|null $has_debit_card
 * @property float|int|null $opening_balance
 * @property array|string|null $reconcile_rules
 * @property int|null $responsible_id
 * @property float|int|null $total_amount_locked
 * @property float|int|null $total_amount_stored

 * @property mixed $custom
 * @property mixed $restrictions
 */
class BankAccount extends Model
{
    use UsesUuids, HasFactory, HasAuditFields;

    public const TABLE = DC::TABLE_BANK_ACC;

    protected $table = self::TABLE;

    protected $fillable = [
        BKC::COL_ACC_N,
        BKC::COL_IS_VRT,
        BLC::COL_AUTORCC,
        BLC::COL_RCC_RL,

        BKC::COL_HD_ID,
        BKC::COL_HNM,
        BKC::COL_CT,
        BKC::COL_HD_ADDR,

        UC::COL_RSP_ID,
        UC::COL_RSP_NM,
        UC::COL_RSP_TEL,
        UC::COL_RSP_EM,
        UC::COL_RSP_ADDR,

        BKC::COL_NM,
        BKC::COL_ADR,
        BKC::COL_BANK_IDF,
        BKC::COL_AG_N,
        BKC::COL_AG_DG,

        BKC::COL_COA,

        BKC::COL_OB,
        CHTC::CUR_BL,
        BKC::COL_AMT_STR,
        BKC::COL_AM_LK,

        'vaults',
        BKC::COL_PIX_KEYS,
        BKC::COL_ACPT_PIX,
        BLC::COL_CUR_ID,
        'restrictions',
        'profile',

        BKC::COL_HAS_CRD,
        BKC::COL_CRD_CD,
        BKC::COL_ACPTS_CRD_CD,
        BKC::COL_HAS_PND_STT,
        BKC::COL_HAS_DBT,
        BKC::COL_DBT_CD,
        BKC::COL_ACPTS_DBT_CD,

        BLC::COL_IS_PRM,
        BKC::COL_RSK,
        UC::COL_IA,
        BKC::COL_INT_PRV,
        BLC::COL_SYNC_ER,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        BKC::COL_IS_VRT        => 'boolean',
        BLC::COL_AUTORCC       => 'boolean',

        BKC::COL_HD_ID         => 'string',
        UC::COL_RSP_ID         => 'string',
        BKC::COL_COA           => 'string',

        BKC::COL_OB            => 'decimal:2',
        CHTC::CUR_BL           => 'decimal:2',
        BKC::COL_AMT_STR       => 'decimal:2',
        BKC::COL_AM_LK         => 'decimal:2',

        'vaults'               => 'array',
        'restrictions'         => 'array',
        'profile'              => 'array',
        BLC::COL_RCC_RL        => 'array',
        BKC::COL_PIX_KEYS      => 'array',
        BKC::COL_CRD_CD        => 'array',
        BKC::COL_DBT_CD        => 'array',
        BLC::COL_SYNC_ER       => 'array',

        BKC::COL_ACPT_PIX      => 'boolean',
        BKC::COL_HAS_CRD       => 'boolean',
        BKC::COL_ACPTS_CRD_CD  => 'boolean',
        BKC::COL_HAS_PND_STT   => 'boolean',
        BKC::COL_HAS_DBT       => 'boolean',
        BKC::COL_ACPTS_DBT_CD  => 'boolean',
        BLC::COL_IS_PRM        => 'boolean',
        UC::COL_IA             => 'boolean',
        BKC::COL_RSK           => 'float',
    ];

    protected $with = [
        'chartAccount',
        'holder',
        'responsible',
    ];

    protected $appends = [
        'available_balance',
        'has_active_credit_card',
        'has_active_debit_card',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->{BKC::COL_PIX_KEYS}))
                $model->{BKC::COL_PIX_KEYS} = [
                    'primary' => [
                        'alias'        => 'Email',
                        'key'          => 'test@example.com',
                        'type'         => 'email',
                        'use_count'    => 0,
                        'last_used_at' => null,
                    ],
                    'secondary' => [
                        'alias'        => 'CPF',
                        'key'          => '00000000000',
                        'type'         => 'cpf',
                        'use_count'    => 0,
                        'last_used_at' => null,
                    ],
                ];
            if (empty($model->vaults))
                $model->vaults = [
                    'main_vault' => [
                        'name' => 'Main Vault',
                        'code' => Str::uuid()->toString(),
                        'stored' => 0.00,
                        'can_be_retrieved_in' => now()->addDays(30)->format('Y-m-d'),
                    ],
                ];
            if (empty($model->{BKC::COL_CRD_CD}))
                $model->{BKC::COL_CRD_CD} = [
                    'mock_primary' => [
                        'alias'        => 'Main credit card',
                        'masked_pan'   => '0000 **** **** 0000',
                        'brand'        => 'MASTER CARD',
                        'limit'        => 0.00,
                        'closing_day'  => 1,
                        'due_day'      => 10,
                        'currency_id'  => SC::DEF_SITE_CURRENCY_ID,
                        'is_active'    => false,
                    ],
                ];
            if (empty($model->{BKC::COL_DBT_CD}))
                $model->{BKC::COL_DBT_CD} = [
                    'mock_primary' => [
                        'alias'        => 'Main debit card',
                        'masked_pan'   => '0000 **** **** 0000',
                        'brand'        => 'VISA',
                        'daily_limit'  => 0.00,
                        'currency_id'  => SC::DEF_SITE_CURRENCY_ID,
                        'is_active'    => false,
                    ],
                ];
        });
        static::saving(function (self $account): void {
            self::normalizeStrings($account);
            self::normalizeJson($account);
            self::enforceFlagsConsistency($account);
            self::enforceBalanceConsistency($account);
            self::enforceChartConsistency($account);
        });
    }

    /**
     * Normaliza campos string (trim, upper em currency, etc.).
     */
    protected static function normalizeStrings(self $account): void
    {
        foreach (
            [
                BKC::COL_HNM,
                BKC::COL_NM,
                BKC::COL_BANK_IDF,
                BKC::COL_AG_N,
                BKC::COL_AG_DG,
                UC::COL_RSP_NM,
                UC::COL_RSP_TEL,
                UC::COL_RSP_EM,
                BKC::COL_INT_PRV,
                BLC::COL_CUR_ID,
            ] as $field
        ) {
            if (isset($account->{$field}) && is_string($account->{$field}))
                $account->{$field} = trim($account->{$field});
        }

        if (!empty($account->{BLC::COL_CUR_ID}))
            $account->{BLC::COL_CUR_ID} = strtoupper(substr($account->{BLC::COL_CUR_ID}, 0, 3));
    }

    /**
     * Garante que campos JSON sejam arrays coerentes antes de persistir.
     */
    protected static function normalizeJson(self $account): void
    {
        foreach (
            [
                'vaults',
                'restrictions',
                'profile',
                BLC::COL_RCC_RL,
                BKC::COL_PIX_KEYS,
                BKC::COL_CRD_CD,
                BKC::COL_DBT_CD,
                BLC::COL_SYNC_ER,
            ] as $field
        ) {
            $value = $account->{$field} ?? null;

            if ($value === null) {
                $account->{$field} = []; // @phpstan-ignore assign.propertyType
                continue;
            }

            if (!is_array($value))
                $account->{$field} = (array) $value; // @phpstan-ignore assign.propertyType
        }
    }

    /**
     * Reforça coerência entre flags e payloads (PIX, cartões, risco, autoreconcile).
     */
    protected static function enforceFlagsConsistency(self $account): void
    {
        $creditCards = $account->{BKC::COL_CRD_CD} ?? [];
        $hasCredit   = (bool) $account->{BKC::COL_HAS_CRD};

        if ($hasCredit && empty($creditCards))
            $account->{BKC::COL_HAS_CRD} = false;

        if ($account->{BKC::COL_ACPTS_CRD_CD} && !$account->{BKC::COL_HAS_CRD})
            $account->{BKC::COL_ACPTS_CRD_CD} = false;

        $debitCards = $account->{BKC::COL_DBT_CD} ?? [];
        $hasDebit   = (bool) $account->{BKC::COL_HAS_DBT};

        if ($hasDebit && empty($debitCards))
            $account->{BKC::COL_HAS_DBT} = false;

        if ($account->{BKC::COL_ACPTS_DBT_CD} && !$account->{BKC::COL_HAS_DBT})
            $account->{BKC::COL_ACPTS_DBT_CD} = false;

        $pixKeys = $account->{BKC::COL_PIX_KEYS} ?? [];
        if ($account->{BKC::COL_ACPT_PIX} && empty($pixKeys))
            $account->{BKC::COL_ACPT_PIX} = false;

        $rules = $account->{BLC::COL_RCC_RL} ?? [];
        if ($account->{BLC::COL_AUTORCC} && empty($rules))
            $account->{BLC::COL_AUTORCC} = '0';

        $risk = (float) ($account->{BKC::COL_RSK} ?? 0);
        if ($risk < 0)   $risk = 0.0;
        if ($risk > 100) $risk = 100.0;
        $account->{BKC::COL_RSK} = (string)$risk;
    }

    /**
     * Garante consistência numérica entre saldos e travas.
     */
    protected static function enforceBalanceConsistency(self $account): void
    {
        $opening = (float) ($account->{BKC::COL_OB} ?? 0);
        $stored  = (float) ($account->{BKC::COL_AMT_STR} ?? 0);
        $locked  = (float) ($account->{BKC::COL_AM_LK} ?? 0);
        $current = (float) ($account->{CHTC::CUR_BL} ?? 0);

        if ($opening < 0) $opening = 0.0;
        if ($stored < 0)  $stored  = 0.0;
        if ($locked < 0)  $locked  = 0.0;

        if ($locked > $stored)
            $locked = $stored;

        if ($current < 0)
            $current = 0.0;

        if ($current > $stored)
            $stored = $current;

        $account->{BKC::COL_OB}      = $opening;
        $account->{BKC::COL_AMT_STR} = $stored;
        $account->{BKC::COL_AM_LK}   = $locked;
        $account->{CHTC::CUR_BL}     = $current;
    }

    /**
     * Alinha dados sensíveis (moeda, saldos e responsável) com o Chart of Account vinculado.
     */
    protected static function enforceChartConsistency(self $account): void
    {
        $chartId = $account->{BKC::COL_COA} ?? null;
        if (!$chartId) return;

        /** @var ChartOfAccount|null $chart */
        $chart = ChartOfAccount::query()->find($chartId);
        if (!$chart) {
            $account->{BKC::COL_COA} = null;
            return;
        }

        $chartCurrency = $chart->{BLC::COL_CUR_ID} ?? null;
        $accCurrency   = $account->{BLC::COL_CUR_ID} ?? null;

        if ($chartCurrency) {
            $chartCurrency = strtoupper(substr($chartCurrency, 0, 3));
            if (!$accCurrency || strtoupper($accCurrency) !== $chartCurrency)
                $account->{BLC::COL_CUR_ID} = $chartCurrency;
        }

        $chartBalance = $chart->{CHTC::CUR_BL};
        if ($chartBalance !== null)
            $account->{CHTC::CUR_BL} = $chartBalance;

        $chartInit = $chart->{CHTC::INIT_BL};
        if ($chartInit !== null && (float) $account->{BKC::COL_OB} === 0.0)
            $account->{BKC::COL_OB} = $chartInit;

        if (!$account->{UC::COL_RSP_ID} && $chart->{UC::COL_RSP_ID})
            $account->{UC::COL_RSP_ID} = $chart->{UC::COL_RSP_ID};
    }

    public function getAvailableBalanceAttribute(): float
    {
        $stored = (float) ($this->{BKC::COL_AMT_STR} ?? 0);
        $locked = (float) ($this->{BKC::COL_AM_LK} ?? 0);
        $available = $stored - $locked;

        return $available > 0 ? $available : 0.0;
    }

    public function getHasActiveCreditCardAttribute(): bool
    {
        $cards = $this->{BKC::COL_CRD_CD} ?? [];
        if (!is_array($cards)) $cards = (array) $cards;

        foreach ($cards as $card)
            if (is_array($card) && !empty($card['is_active']))
                return true;

        return false;
    }

    public function getHasActiveDebitCardAttribute(): bool
    {
        $cards = $this->{BKC::COL_DBT_CD} ?? [];
        if (!is_array($cards)) $cards = (array) $cards;

        foreach ($cards as $card)
            if (is_array($card) && !empty($card['is_active']))
                return true;

        return false;
    }

    public function chartAccount(): HasOne
    {
        return $this->hasOne(
            ChartOfAccount::class,
            'id',
            BKC::COL_COA
        );
    }

    public function holder(): HasOne
    {
        return $this->hasOne(
            User::class,
            'id',
            BKC::COL_HD_ID
        );
    }

    public function responsible(): HasOne
    {
        return $this->hasOne(
            User::class,
            'id',
            UC::COL_RSP_ID
        );
    }
}
