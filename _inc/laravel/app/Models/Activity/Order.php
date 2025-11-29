<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\{
    MonthName,
    PaymentMethod,
    PaymentStatus
};
use App\Traits\{
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasOne
};
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesAddresses;

    public const TABLE = DC::TABLE_ORDERS;

    protected $table = self::TABLE;

    protected $fillable = [
        UC::COL_USER_ID,
        BC::COL_OD_ID,
        'name',
        'email',
        UC::COL_PLAN_ID,
        UC::COL_PLAN_NM,
        'price',
        'discount',
        BC::COL_PRC_CUR,
        BC::COL_N_INTR,
        BC::COL_CD_FLG,
        BC::COL_CD_NB,
        BC::COL_CD_DG,
        BC::COL_CD_HNM,
        BC::COL_CD_EX_M,
        BC::COL_CD_EX_Y,
        BC::COL_TAX_ID,
        BC::COL_OT_TX_ID,
        BC::COL_PIX_KEY,
        BC::COL_PSLP_ID,
        BC::COL_PAY_STT,
        BC::COL_PAY_TP,
        'receipt',
        BC::COL_RCP_MD,
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $hidden = [
        BC::COL_CD_NB,
        BC::COL_CD_DG,
        BC::COL_CD_HNM,
        BC::COL_PIX_KEY,
        'receipt',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'discount'          => 'decimal:2',
        BC::COL_N_INTR      => 'integer',
        BC::COL_CD_EX_M     => MonthName::class,
        BC::COL_OT_TX_ID    => 'array',
        BC::COL_RCP_MD      => 'array',
        BC::COL_PAY_STT     => PaymentStatus::class,
        BC::COL_PAY_TP      => PaymentMethod::class,
        // criptografia em repouso de dados sensíveis
        BC::COL_CD_NB       => 'encrypted:string',
        BC::COL_CD_DG       => 'encrypted:string',
        BC::COL_CD_HNM      => 'encrypted:string',
        BC::COL_PIX_KEY     => 'encrypted:string',
    ];

    /**
     * Mapa de transições válidas de status de pagamento.
     */
    private const STATUS_TRANSITIONS = [
        'pending' => [
            'pending',
            'processing',
            'authorized',
            'completed',
            'failed',
            'cancelled',
            'expired',
        ],
        'processing' => [
            'processing',
            'authorized',
            'completed',
            'failed',
            'cancelled',
            'expired',
        ],
        'authorized' => [
            'authorized',
            'completed',
            'failed',
            'cancelled',
            'expired',
        ],
        'completed' => [
            'completed',
            'refunded',
            'partially_refunded',
            'disputed',
        ],
        'failed' => [
            'failed',
            'pending',
            'processing',
        ],
        'cancelled' => [
            'cancelled',
        ],
        'refunded' => [
            'refunded',
            'partially_refunded',
        ],
        'partially_refunded' => [
            'partially_refunded',
            'refunded',
        ],
        'expired' => [
            'expired',
        ],
        'declined' => [
            'declined',
        ],
        'disputed' => [
            'disputed',
        ],
        'undefined' => [
            'pending',
            'processing',
            'authorized',
            'completed',
            'failed',
            'cancelled',
            'refunded',
            'partially_refunded',
            'expired',
            'declined',
            'disputed',
            'undefined',
        ],
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            // Normalização básica de strings
            foreach (['name'] as $field)
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});

            // E-mail
            if ($m->email) {
                $normalizedEmail = self::normalizeEmail($m->email, 'Order email', $m->id ?? null);
                $m->email        = $normalizedEmail ?: null;
            }

            $price    = (float) ($m->price ?? 0.0);
            $discount = (float) ($m->discount ?? 0.0);

            if ($price < 0)
                $price = 0.0;

            if ($discount < 0)
                $discount = 0.0;

            if ($discount > $price)
                $discount = $price;

            $m->price    = $price;
            $m->discount = $discount;

            if ($m->{BC::COL_PRC_CUR})
                $m->{BC::COL_PRC_CUR} = strtoupper(trim((string) $m->{BC::COL_PRC_CUR}));

            $installments = $m->{BC::COL_N_INTR};
            if (!is_numeric($installments) || (int) $installments < 1)
                $m->{BC::COL_N_INTR} = 1;
            else
                $m->{BC::COL_N_INTR} = (int) $installments;
            $m->{BC::COL_CD_NB} = self::sanitizeCardNumber($m->{BC::COL_CD_NB} ?? null);
            $m->{BC::COL_CD_DG} = self::sanitizeCardDigits($m->{BC::COL_CD_DG} ?? null, $m->{BC::COL_CD_NB});
            $m->{BC::COL_CD_HNM} = self::sanitizeCardHolder($m->{BC::COL_CD_HNM} ?? null);
            self::normalizeCardExpiration($m);
            try {
                $m->{BC::COL_OT_TX_ID} = self::normalizeOtherTaxes($m->{BC::COL_OT_TX_ID} ?? null);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize other taxes', [
                    'order_id' => $m->id ?? null,
                    'error'    => $e->getMessage(),
                ]);
                $m->{BC::COL_OT_TX_ID} = [];
            }

            try {
                $m->{BC::COL_RCP_MD} = self::normalizeArrayField($m->{BC::COL_RCP_MD} ?? null);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize receipt metadata', [
                    'order_id' => $m->id ?? null,
                    'error'    => $e->getMessage(),
                ]);
                $m->{BC::COL_RCP_MD} = [];
            }

            if ($m->{BC::COL_PIX_KEY}) {
                $pix = self::normalizePixKey($m->{BC::COL_PIX_KEY});
                if ($pix === null) {
                    Log::warning(self::class . ' invalid Pix key, clearing', [
                        'order_id' => $m->id ?? null,
                    ]);
                }
                $m->{BC::COL_PIX_KEY} = $pix;
            }

            $originalStatusRaw = $m->getOriginal(BC::COL_PAY_STT);
            $currentStatus     = $m->exists
                ? PaymentStatus::normalize($originalStatusRaw)
                : null;

            $newStatus = PaymentStatus::normalize($m->{BC::COL_PAY_STT} ?? null);

            // Para novos registros, Undefined vira Pending por padrão
            if (!$m->exists && $newStatus === PaymentStatus::Undefined)
                $newStatus = PaymentStatus::Pending;

            $m->{BC::COL_PAY_STT} = self::assertValidStatusTransition($currentStatus, $newStatus);

            // Método de pagamento
            $m->{BC::COL_PAY_TP} = self::normalizePaymentMethod($m->{BC::COL_PAY_TP} ?? null);

            // Validação dos instrumentos de pagamento
            self::validatePaymentInstrument($m);
        });
    }

    /**
     * Garante que a transição de status é válida; caso não seja,
     * mantém o status anterior e registra em log.
     */
    protected static function assertValidStatusTransition(
        ?PaymentStatus $current,
        PaymentStatus $next
    ): PaymentStatus {
        if ($current === null || $current === PaymentStatus::Undefined)
            return $next;

        $currentKey = $current->value;
        $nextKey    = $next->value;

        $allowed = self::STATUS_TRANSITIONS[$currentKey] ?? [];

        if ($currentKey === $nextKey || in_array($nextKey, $allowed, true))
            return $next;

        Log::warning(self::class . ' invalid payment status transition', [
            'from' => $currentKey,
            'to'   => $nextKey,
        ]);

        return $current;
    }

    /**
     * Normaliza método de pagamento, caindo para Other em caso inválido.
     */
    protected static function normalizePaymentMethod(null|string|PaymentMethod $v): PaymentMethod
    {
        if ($v instanceof PaymentMethod)
            return $v;

        $raw = strtolower(trim((string) $v));

        foreach (PaymentMethod::cases() as $case)
            if ($case->value === $raw)
                return $case;

        return PaymentMethod::Other;
    }

    protected static function sanitizeCardNumber(?string $number): ?string
    {
        if ($number === null)
            return null;

        $digits = preg_replace('/\D+/', '', $number) ?? '';

        // Se não tiver tamanho minimamente plausível, zera
        if ($digits === '' || strlen($digits) < 12 || strlen($digits) > 19)
            return null;

        return $digits;
    }

    protected static function sanitizeCardDigits(?string $digits, ?string $cardNumber): ?string
    {
        $digits = $digits !== null
            ? preg_replace('/\D+/', '', $digits) ?? ''
            : '';

        if ($digits === '' && $cardNumber) {
            $digits = substr($cardNumber, -4);
        }

        return $digits !== '' ? $digits : null;
    }

    protected static function sanitizeCardHolder(?string $name): ?string
    {
        if ($name === null)
            return null;

        $name = trim($name);
        return $name !== '' ? $name : null;
    }

    /**
     * Normaliza expiração do cartão com MonthName + ano >= ano corrente.
     * Se ano for o atual, mês não pode ser anterior ao mês atual.
     */
    protected static function normalizeCardExpiration(self $m): void
    {
        $rawMonth = $m->{BC::COL_CD_EX_M} ?? null;
        $rawYear  = $m->{BC::COL_CD_EX_Y} ?? null;

        if ($rawMonth === null && $rawYear === null)
            return;

        $monthEnum = MonthName::normalize($rawMonth);
        $yearStr   = trim((string) $rawYear);
        $year      = ctype_digit($yearStr) ? (int) $yearStr : null;

        $now         = now();
        $currentYear = (int) $now->format('Y');
        $currentMon  = (int) $now->format('n');

        if ($year === null || $year < $currentYear)
            $year = $currentYear;

        if ($year === $currentYear && $monthEnum->isoIndex() < $currentMon) {
            // força para o mês atual se estiver no passado
            $monthEnum = MonthName::normalize((string) $currentMon);
        }

        $m->{BC::COL_CD_EX_M} = $monthEnum;
        $m->{BC::COL_CD_EX_Y} = (string) $year;
    }

    /**
     * Garante que os IDs em other_taxes_ids referenciam tributos existentes.
     */
    protected static function normalizeOtherTaxes(mixed $raw): array
    {
        $items = self::normalizeArrayField($raw);
        if (!$items)
            return [];

        $ids = array_values(array_filter(
            $items,
            fn($v): bool => is_string($v) && trim($v) !== ''
        ));

        if (!$ids)
            return [];

        $exists = Tax::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        $exists = array_map('strval', $exists);

        return array_values(array_intersect($ids, $exists));
    }

    /**
     * Normaliza Pix key aceitando:
     * - e-mail válido
     * - CPF/CNPJ (somente dígitos, 11 ou 14)
     * - telefone normalizado
     * - chave aleatória (até 36 chars alfanuméricos/hífens)
     */
    protected static function normalizePixKey(?string $key): ?string
    {
        if ($key === null)
            return null;

        $key = trim($key);
        if ($key === '')
            return null;

        // E-mail
        if (str_contains($key, '@')) {
            $email = self::normalizeEmail($key, 'Pix key', null);
            return $email ?: null;
        }

        $digits = preg_replace('/\D+/', '', $key) ?? '';

        // CPF/CNPJ
        if ($digits !== '' && (strlen($digits) === 11 || strlen($digits) === 14))
            return $digits;

        // Telefone
        if ($digits !== '' && strlen($digits) >= 8 && strlen($digits) <= 15)
            return self::normalizePhone($digits, 'Pix key', null) ?? $digits;

        // Chave aleatória (máx. 36 caracteres)
        if (strlen($key) > 36)
            return null;

        if (!preg_match('/^[A-Za-z0-9\-]+$/', $key))
            return null;

        return $key;
    }

    /**
     * Valida se há um instrumento de pagamento coerente com o método escolhido.
     * Dispara exceção em caso de falha, impedindo o save.
     */
    protected static function validatePaymentInstrument(self $m): void
    {
        /** @var PaymentMethod $method */
        $method = $m->{BC::COL_PAY_TP} instanceof PaymentMethod
            ? $m->{BC::COL_PAY_TP}
            : self::normalizePaymentMethod($m->{BC::COL_PAY_TP});

        $hasCard    = !empty($m->{BC::COL_CD_NB}) || !empty($m->{BC::COL_CD_DG}) || !empty($m->{BC::COL_CD_HNM});
        $hasPix     = !empty($m->{BC::COL_PIX_KEY});
        $hasPayslip = !empty($m->{BC::COL_PSLP_ID});

        if (!$hasCard && !$hasPix && !$hasPayslip) {
            Log::error(self::class . ' missing payment instruments for order', [
                'order_id' => $m->id ?? null,
                'method'   => $method->value ?? null,
            ]);
            throw new \InvalidArgumentException('At least one payment instrument (card, Pix or payslip) must be provided.');
        }

        switch ($method) {
            case PaymentMethod::CardCredit:
            case PaymentMethod::CardDebit:
                if (!$hasCard) {
                    Log::error(self::class . ' card data required for card payment', [
                        'order_id' => $m->id ?? null,
                    ]);
                    throw new \InvalidArgumentException('Card data is required for card payments.');
                }
                break;

            case PaymentMethod::Pix:
                if (!$hasPix) {
                    Log::error(self::class . ' Pix key required for Pix payment', [
                        'order_id' => $m->id ?? null,
                    ]);
                    throw new \InvalidArgumentException('Pix key is required for Pix payments.');
                }
                break;

            default:
                // Para outros métodos (cash, bank_transfer etc.) não exigimos nada aqui,
                // apenas o requisito mínimo de ter ao menos um instrumento válido.
                break;
        }
    }

    public static function totalOrders(): int
    {
        return (int) self::count();
    }

    public static function totalOrdersPrice(): float
    {
        return (float) self::sum('price');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, UC::COL_PLAN_ID, 'id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class, BC::COL_PSLP_ID, 'id');
    }

    private const FK_COUPON_ORDER = 'order';
    private const LOCAL_ORDER_ID  = BC::COL_OD_ID;

    public function totalCouponUsed(): HasOne
    {
        return $this->hasOne(
            UserCoupon::class,
            self::FK_COUPON_ORDER,
            self::LOCAL_ORDER_ID
        );
    }
}
