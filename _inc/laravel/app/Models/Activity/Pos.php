<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\{PosStatus, PosType, TransactionType};
use App\Traits\{
    ChecksLogin,
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne
};
use Illuminate\Http\RedirectResponse;

class Pos extends Model
{
    use ChecksLogin;
    use HasAuditFields;
    use HasFactory;
    use NormalizesAddresses;
    use UsesUuids;

    protected $table = DC::TABLE_POS;
    protected $fillable = [
        BC::COL_POS_ID,
        BC::COL_POS_DT,

        BC::COL_DVC_SR,
        BC::COL_MAC_ADR,
        BC::COL_IP_ADR,
        BC::COL_DVC_MD,
        BC::COL_OPS_SYS,

        CC::COL_CP_ID,
        UC::COL_BRC_ID,
        BC::COL_WRH_ID,
        UC::COL_DEP_ID,
        BC::COL_CST_ID,
        BC::COL_CAT_ID,
        CC::COL_MNF_ID,
        CC::COL_MNF_NM,
        UC::COL_VD_ID,
        CC::COL_VD_NM,

        'type',
        'status',
        BC::COL_SHIP_DSP,
        BC::COL_STT_LB,

        BC::COL_IO,
        BC::COL_ACP_CRD,
        BC::COL_ACP_DBT,
        BC::COL_ACP_PIX,
        BC::COL_ACP_CSH,
        BC::COL_PIX_QR,
        BC::COL_ACP_FLG,

        BC::COL_LST_TRS,
        DC::COL_LA,
        BC::COL_TRS_CNT,
        BC::COL_ACC_TTL,
        BC::COL_SVC_FEE,

        // Billing
        BC::COL_BL_NAME,
        BC::COL_BL_EMAIL,
        BC::COL_BL_TEL,
        BC::COL_BL_ZIP,
        BC::COL_BL_ADR,
        BC::COL_BL_ST,
        BC::COL_BL_CTY,
        BC::COL_BL_CTR,
        BC::COL_BL_DTL,

        // Rastreamento de falhas
        DC::COL_FL_AT,
        DC::COL_FLD_RS,
        DC::COL_RTR_CT,
        DC::COL_LST_RTR_AT,
        DC::COL_ER_LG,
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        BC::COL_POS_DT   => 'date',

        'type'           => PosType::class,
        BC::COL_STT_LB   => PosStatus::class,

        BC::COL_IO       => 'boolean',
        BC::COL_ACP_CRD  => 'boolean',
        BC::COL_ACP_DBT  => 'boolean',
        BC::COL_ACP_PIX  => 'boolean',
        BC::COL_ACP_CSH  => 'boolean',

        BC::COL_PIX_QR   => 'array',
        BC::COL_ACP_FLG  => 'array',

        BC::COL_LST_TRS  => 'datetime',
        DC::COL_LA       => 'datetime',

        BC::COL_TRS_CNT  => 'integer',
        BC::COL_ACC_TTL  => 'decimal:2',
        BC::COL_SVC_FEE  => 'decimal:2',

        DC::COL_FL_AT        => 'datetime',
        DC::COL_LST_RTR_AT   => 'datetime',
        DC::COL_ER_LG        => 'array',
    ];

    protected $with = [
        'customer',
        'warehouse',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            $ownerId = $m->{DC::TABLE_CREATOR} ?? $m->{BC::COL_CST_ID} ?? null;

            $m->{BC::COL_BL_EMAIL} = static::normalizeEmail(
                $m->{BC::COL_BL_EMAIL} ?? null,
                'pos_billing',
                $ownerId
            );

            $m->{BC::COL_BL_TEL} = static::normalizePhone(
                $m->{BC::COL_BL_TEL} ?? null,
                'pos_billing',
                $ownerId
            );

            $m->{BC::COL_BL_ZIP} = static::normalizeZip(
                $m->{BC::COL_BL_ZIP} ?? null,
                $m->{BC::COL_BL_CTR} ?? null,
                'pos_billing',
                $ownerId
            );

            foreach ([BC::COL_TRS_CNT, DC::COL_RTR_CT] as $intField) {
                if ($m->{$intField} !== null) {
                    $val = (int) $m->{$intField};
                    if ($val < 0) {
                        $val = 0;
                    }
                    $m->{$intField} = $val;
                }
            }

            foreach ([BC::COL_ACC_TTL, BC::COL_SVC_FEE] as $decField) {
                if ($m->{$decField} !== null) {
                    $val = (float) $m->{$decField};
                    if ($val < 0.0) {
                        $val = 0.0;
                    }
                    $m->{$decField} = $val;
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            BC::COL_CST_ID,
            'id'
        );
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class,
            BC::COL_WRH_ID,
            'id'
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_CP_ID,
            'id'
        );
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(
            Vendor::class,
            UC::COL_VD_ID,
            'id'
        );
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_MNF_ID,
            'id'
        );
    }

    /**
     * Itens da venda associada ao POS.
     *
     * Aqui assumimos que a tabela de itens possui a coluna `pos_id`
     * (BC::COL_POS_ID) referenciando o mesmo campo em `pos`.
     */
    public function items(): HasMany
    {
        return $this->hasMany(
            PosProduct::class,
            BC::COL_POS_ID,
            BC::COL_POS_ID
        );
    }

    /**
     * Pagamento associado a este POS.
     */
    public function posPayment(): HasOne
    {
        return $this->hasOne(
            PosPayment::class,
            BC::COL_POS_ID,
            BC::COL_POS_ID
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_id')
            ->where('payment_type', TransactionType::Pos);
    }

    // ---------------------------------------------------------------------
    // Regras de negócio (totais / relatórios)
    // ---------------------------------------------------------------------

    public function getSubTotal(): float
    {
        return (float) $this->items->sum(
            fn($p) => (float) $p->price * (float) $p->quantity
        );
    }

    public function getTotalDiscount(): float
    {
        return (float) $this->items->sum('discount');
    }

    public function getTotalTax(): float
    {
        return (float) $this->items->sum(function ($p) {
            $rate = (float) Utility::totalTaxRate($p->tax);
            $base = (float) $p->price * (float) $p->quantity;

            return ($rate / 100.0) * $base;
        });
    }

    public function getTotal(): float
    {
        return $this->getSubTotal()
            - $this->getTotalDiscount()
            + $this->getTotalTax();
    }

    /**
     * Total do faturamento via POS (para o usuário logado),
     * opcionalmente filtrado pelo mês atual.
     */
    public static function totalPosAmount(bool $month = false): string|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) {
            return $userOrRedirect;
        }

        $user  = $userOrRedirect;
        $query = self::where(DC::TABLE_CREATOR, $user?->creatorId());

        if ($month) {
            $query->whereRaw('MONTH(created_at) = ?', [date('m')]);
        }

        $total = (float) $query->get()->sum(
            fn(self $p) => $p->getTotal()
        );

        return $user?->priceFormat($total);
    }

    /**
     * Relatório de POS dos últimos 10 dias para gráfico simples.
     *
     * Retorna:
     *  [
     *      'label' => ['Y-m-d', ...],
     *      'value' => [totalDia1, totalDia2, ...]
     *  ]
     */
    public static function getPosReportChart(): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) {
            return $userOrRedirect;
        }

        $user = $userOrRedirect;

        $grouped = self::whereDate(
            'created_at',
            '>',
            Carbon::now()->subDays(10)
        )
            ->where(DC::TABLE_CREATOR, $user?->creatorId())
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn($v) => Carbon::parse($v->created_at)->format('dm'));

        $posesArray = [
            'label' => [],
            'value' => [],
        ];

        $now = Carbon::now();

        for ($i = 0; $i <= 9; $i++) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $key  = Carbon::parse($date)->format('dm');

            $posesArray['label'][] = $date;
            $posesArray['value'][] = isset($grouped[$key])
                ? (float) $grouped[$key]->sum(fn(self $p) => $p->getTotal())
                : 0.0;
        }

        return $posesArray;
    }
}
