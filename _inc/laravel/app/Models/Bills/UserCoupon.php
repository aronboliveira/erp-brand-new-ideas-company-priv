<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\{
    PaymentMethod,
    PaymentStatus,
    UserType
};
use App\Traits\{
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserCoupon extends Model
{
    use HasAuditFields;
    use UsesUuids;
    use NormalizesAddresses;

    protected $table = DC::TABLE_USR_CPNS;

    private const COL_COUPON = 'coupon';
    private const COL_ORDER  = 'order';
    private const COL_USER   = 'user';

    protected $fillable = [
        self::COL_USER,
        self::COL_COUPON,
        self::COL_ORDER,
    ];

    protected $with = [
        'coupon',
        'order',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $model): void {
            $orderId = $model->getAttribute(self::COL_ORDER) ?? null;
            if ($orderId === null || $orderId === '') {
                $model->setAttribute(self::COL_ORDER, null);
                return;
            }
            try {
                $orderId = trim((string) $orderId);
                if (!self::looksLikeUuid($orderId)) {
                    $model->setAttribute(self::COL_ORDER, null);
                    return;
                }
                if (!Order::query()->whereKey($orderId)->exists()) {
                    $model->setAttribute(self::COL_ORDER, null);
                    return;
                }
                $model->setAttribute(self::COL_ORDER, $orderId);
            } catch (Throwable $e) {
                Log::warning(self::class . ' failed to validate order id for user coupon', [
                    'user_coupon_id' => $model->id ?? null,
                    'order_raw'      => $orderId ?? null,
                    'error'          => $e->getMessage(),
                ]);
                $model->setAttribute(self::COL_ORDER, null);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_USER, 'id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, self::COL_COUPON, 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, self::COL_ORDER, 'id');
    }

    public function hasOrder(): bool
    {
        return !empty($this->{self::COL_ORDER});
    }

    public function attachOrder(?Order $order): self
    {
        $this->{self::COL_ORDER} = $order?->id;
        return $this;
    }

    public static function findFor(
        User|string $user,
        Coupon|string $coupon,
        Order|string|null $order = null
    ): ?self {
        $userId   = $user instanceof User ? $user->getKey() : (string) $user;
        $couponId = $coupon instanceof Coupon ? $coupon->getKey() : (string) $coupon;

        $query = self::query()
            ->where(self::COL_USER, $userId)
            ->where(self::COL_COUPON, $couponId);

        if ($order === null) {
            $query->whereNull(self::COL_ORDER);
        } else {
            $orderId = $order instanceof Order ? $order->getKey() : (string) $order;
            $query->where(self::COL_ORDER, $orderId);
        }

        return $query->first();
    }

    public static function ensure(
        User|string $user,
        Coupon|string $coupon,
        Order|string|null $order = null
    ): self {
        $userId   = $user instanceof User ? $user->getKey() : (string) $user;
        $couponId = $coupon instanceof Coupon ? $coupon->getKey() : (string) $coupon;
        $orderId  = $order instanceof Order ? $order->getKey() : ($order !== null ? (string) $order : null);

        return self::firstOrCreate(
            [
                self::COL_USER   => $userId,
                self::COL_COUPON => $couponId,
                self::COL_ORDER  => $orderId,
            ]
        );
    }

    public function orderPrice(): ?float
    {
        if (!$this->order) return null;

        try {
            $price = $this->order->price ?? null;
            return $price !== null ? (float) $price : null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve order price from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'order_id'       => $this->{self::COL_ORDER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function orderDiscount(): ?float
    {
        if (!$this->order) return null;
        try {
            $discount = $this->order->discount ?? null;
            return $discount !== null ? (float) $discount : null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve order discount from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'order_id'       => $this->{self::COL_ORDER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function orderNetAmount(): ?float
    {
        if (!$this->order) return null;
        try {
            $price    = (float) ($this->order->price ?? 0.0);
            $discount = (float) ($this->order->discount ?? 0.0);
            $net      = $price - $discount;

            return $net >= 0.0 ? $net : 0.0;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve order net amount from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'order_id'       => $this->{self::COL_ORDER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function orderPaymentStatus(): ?PaymentStatus
    {
        if (!$this->order) return null;
        try {
            $raw = $this->order->{BC::COL_PAY_STT} ?? null;
            if ($raw instanceof PaymentStatus)
                return $raw;
            return PaymentStatus::normalize($raw);
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve order payment status from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'order_id'       => $this->{self::COL_ORDER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function orderPaymentMethod(): ?PaymentMethod
    {
        if (!$this->order) return null;
        try {
            $raw = $this->order->{BC::COL_PAY_TP} ?? null;
            if ($raw instanceof PaymentMethod)
                return $raw;
            return PaymentMethod::normalize($raw);
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve order payment method from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'order_id'       => $this->{self::COL_ORDER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function orderIsPaid(): bool
    {
        $status = $this->orderPaymentStatus();
        if ($status === null) return false;
        return in_array($status, [
            PaymentStatus::Completed,
            PaymentStatus::Refunded,
            PaymentStatus::PartiallyRefunded,
        ], true);
    }

    public function orderIsPending(): bool
    {
        $status = $this->orderPaymentStatus();
        if ($status === null) return false;
        return in_array($status, [
            PaymentStatus::Pending,
            PaymentStatus::Processing,
            PaymentStatus::Authorized,
            PaymentStatus::Undefined,
        ], true);
    }

    public function userId(): ?string
    {
        try {
            return $this->{self::COL_USER} ?? null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve user id from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function userEmail(): ?string
    {
        if (!$this->user) return null;

        try {
            return $this->user->{UC::COL_EM} ?? $this->user->email ?? null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve user email from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function userName(): ?string
    {
        if (!$this->user) return null;

        try {
            return $this->user->{UC::COL_NM} ?? $this->user->name ?? null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve user name from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function userType(): ?string
    {
        if (!$this->user) return null;

        try {
            $type = $this->user->{UC::COL_TP} ?? null;
            return $type !== null ? (string) $type : null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve user type from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function userPlanId(): ?string
    {
        if (!$this->user) return null;

        try {
            $planId = $this->user->{UC::COL_PLAN_ID} ?? null;
            return $planId !== null ? (string) $planId : null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve user plan id from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function userPlanName(): ?string
    {
        if (!$this->user) return null;

        try {
            $planName = $this->user->{UC::COL_PLAN_NM} ?? null;
            return $planName !== null ? (string) $planName : null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to resolve user plan name from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function belongsToUser(User|string $user): bool
    {
        try {
            $userId = $user instanceof User ? $user->getKey() : (string) $user;
            return (string) ($this->{self::COL_USER} ?? '') === $userId;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to compare user for user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'candidate_user' => $user instanceof User ? $user->id ?? null : (string) $user,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected static function normalizeRoleArray(mixed $raw): array
    {
        if ($raw === null || $raw === '') return [];

        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                $items = $decoded;
            else
                $items = explode(',', $raw);
        } else {
            return [];
        }

        $items = array_map(
            fn($v) => strtolower(trim((string) $v)),
            $items
        );
        $items = array_filter($items, fn($v) => $v !== '');

        $normalized = [];

        foreach ($items as $item) {
            $t = UserType::normalize($item);
            if ($t !== null) {
                $normalized[] = strtolower($t->value);
                $normalized[] = strtolower($t->name);
            } else {
                $normalized[] = $item;
            }
        }

        return array_values(array_unique($normalized));
    }

    public function userHasAcceptableRoles(): bool
    {
        if (!$this->user || !$this->coupon) return false;

        try {
            $rawRoles = $this->coupon->{BC::COL_EXC_RLS} ?? null;
            $excluded = self::normalizeRoleArray($rawRoles);

            if (!$excluded) return true;

            $userType = UserType::normalize($this->user->{UC::COL_TP} ?? null);
            if ($userType === null) return true;

            $valueKey = strtolower($userType->value);
            $nameKey  = strtolower($userType->name);

            if (in_array($valueKey, $excluded, true)) return false;
            if (in_array($nameKey, $excluded, true))  return false;

            return true;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate user role for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function userIsVerifiedIfRequired(): bool
    {
        if (!$this->user || !$this->coupon) return false;

        try {
            $mustBeVerified = (bool) ($this->coupon->{BC::COL_MUST_BE_VRF} ?? false);
            if (!$mustBeVerified) return true;

            $verifiedAt = $this->user->{UC::COL_EM_V_AT} ?? $this->user->email_verified_at ?? null;

            return $verifiedAt !== null;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate user verification for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function userWithinCouponUserDateLimit(): bool
    {
        if (!$this->user || !$this->coupon) return false;

        try {
            $rawLimit = $this->coupon->{BC::COL_DT_LMT_TO_USER} ?? null;
            if (!$rawLimit) return true;

            $limit = $rawLimit instanceof Carbon
                ? $rawLimit
                : Carbon::parse($rawLimit);

            $rawCreated = $this->user->{UC::COL_C_AT} ?? $this->user->created_at ?? null;
            if (!$rawCreated) return true;

            $created = $rawCreated instanceof Carbon
                ? $rawCreated
                : Carbon::parse($rawCreated);

            return $created->lessThanOrEqualTo($limit);
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate user date limit for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function userPreviousOrdersCount(bool $onlyCompleted = true): int
    {
        if (!$this->user) return 0;

        try {
            $query = Order::query()
                ->where(UC::COL_USER_ID, $this->user->getKey());

            if ($this->order && $this->order->created_at) {
                $query->where('created_at', '<=', $this->order->created_at);
            }

            if ($onlyCompleted) {
                $query->where(BC::COL_PAY_STT, PaymentStatus::Completed->value);
            }

            return (int) $query->count();
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to count user previous orders from user coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return 0;
        }
    }

    public function userPreviousOrdersFitLimits(bool $onlyCompleted = true): bool
    {
        if (!$this->user || !$this->coupon) return false;

        try {
            $min = (int) ($this->coupon->{BC::COL_MIN_PRV_ORD} ?? 0);
            $max = (int) ($this->coupon->{BC::COL_MAX_PRV_ORD} ?? 0);
            $cnt = $this->userPreviousOrdersCount($onlyCompleted);

            if ($min > 0 && $cnt < $min) return false;
            if ($max > 0 && $max >= $min && $cnt > $max) return false;

            return true;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate previous orders limits for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function isGiftUsage(): bool
    {
        if (!$this->coupon || !$this->user) return false;

        try {
            $givenBy = $this->coupon->{CC::COL_GIVEN_BY} ?? null;
            $userId  = $this->user->getKey();

            if ($givenBy === null || $userId === null) return false;

            return (string) $givenBy !== (string) $userId;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate gift usage for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function giftUsageAllowed(): bool
    {
        if (!$this->coupon || !$this->user) return false;

        try {
            $canBeGift = (bool) ($this->coupon->{BC::COL_CAN_BE_GIFT} ?? false);

            if (!$canBeGift && $this->isGiftUsage())
                return false;

            return true;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate gift allowance for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function userUsageCount(): int
    {
        if (!$this->user || !$this->coupon) return 0;

        try {
            return (int) self::query()
                ->where(self::COL_USER, $this->user->getKey())
                ->where(self::COL_COUPON, $this->coupon->getKey())
                ->count();
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to count user coupon usage', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'coupon_id'      => $this->{self::COL_COUPON} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return 0;
        }
    }

    public function couponHasRemainingUses(): bool
    {
        if (!$this->coupon) return false;

        try {
            $globalUsed = $this->coupon->usedCoupon();
            $limit      = (int) ($this->coupon->limit ?? 0);

            if ($limit <= 0) return true;

            return $globalUsed < $limit;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate coupon remaining uses', [
                'user_coupon_id' => $this->id ?? null,
                'coupon_id'      => $this->{self::COL_COUPON} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function userWithinUsageLimit(): bool
    {
        if (!$this->user || !$this->coupon) return false;

        try {
            $userUsage = $this->userUsageCount();
            $limit     = (int) ($this->coupon->limit ?? 0);

            if ($limit <= 0) return true;

            return $userUsage < $limit;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate user-specific coupon usage limit', [
                'user_coupon_id' => $this->id ?? null,
                'user_id'        => $this->{self::COL_USER} ?? null,
                'coupon_id'      => $this->{self::COL_COUPON} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function orderFitsDate(): bool
    {
        if (!$this->order || !$this->coupon) return false;

        try {
            $rawCreated = $this->order->created_at ?? null;

            $at = $rawCreated instanceof Carbon
                ? $rawCreated
                : ($rawCreated ? Carbon::parse($rawCreated) : now());

            return $this->coupon->isActive($at);
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate order date window for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'order_id'       => $this->{self::COL_ORDER} ?? null,
                'coupon_id'      => $this->{self::COL_COUPON} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function orderFitsPaymentCriteria(): bool
    {
        if (!$this->order || !$this->coupon) return false;

        try {
            $rules = $this->coupon->rules ?? null;
            if (!$rules || !is_array($rules)) return true;

            $status = $this->orderPaymentStatus();
            $method = $this->orderPaymentMethod();
            $net    = $this->orderNetAmount();

            if ($net !== null && array_key_exists('min_amount', $rules)) {
                if ($net < (float) $rules['min_amount']) return false;
            }

            if ($net !== null && array_key_exists('max_amount', $rules)) {
                if ($net > (float) $rules['max_amount']) return false;
            }

            if ($status && !empty($rules['required_statuses'] ?? null)) {
                $required = [];
                foreach ((array) $rules['required_statuses'] as $raw) {
                    $required[] = PaymentStatus::normalize($raw)->value;
                }
                $required = array_unique($required);
                if ($required && !in_array($status->value, $required, true))
                    return false;
            }

            if ($status && !empty($rules['forbidden_statuses'] ?? null)) {
                $forbidden = [];
                foreach ((array) $rules['forbidden_statuses'] as $raw) {
                    $forbidden[] = PaymentStatus::normalize($raw)->value;
                }
                $forbidden = array_unique($forbidden);
                if (in_array($status->value, $forbidden, true))
                    return false;
            }

            if ($method && !empty($rules['allowed_payment_methods'] ?? null)) {
                $allowed = [];
                foreach ((array) $rules['allowed_payment_methods'] as $raw) {
                    $allowed[] = PaymentMethod::normalize($raw)->value;
                }
                $allowed = array_unique($allowed);
                if ($allowed && !in_array($method->value, $allowed, true))
                    return false;
            }

            if ($method && !empty($rules['forbidden_payment_methods'] ?? null)) {
                $forbidden = [];
                foreach ((array) $rules['forbidden_payment_methods'] as $raw) {
                    $forbidden[] = PaymentMethod::normalize($raw)->value;
                }
                $forbidden = array_unique($forbidden);
                if (in_array($method->value, $forbidden, true))
                    return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate order payment criteria for coupon', [
                'user_coupon_id' => $this->id ?? null,
                'order_id'       => $this->{self::COL_ORDER} ?? null,
                'coupon_id'      => $this->{self::COL_COUPON} ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function fitsBasicCouponContext(): bool
    {
        if (!$this->user || !$this->coupon) return false;

        try {
            if (!$this->userHasAcceptableRoles())        return false;
            if (!$this->userIsVerifiedIfRequired())      return false;
            if (!$this->userWithinCouponUserDateLimit()) return false;
            if (!$this->userPreviousOrdersFitLimits())   return false;
            if (!$this->giftUsageAllowed())              return false;
            if (!$this->couponHasRemainingUses())        return false;
            if (!$this->userWithinUsageLimit())          return false;

            if ($this->order) {
                if (!$this->orderFitsDate())              return false;
                if (!$this->orderFitsPaymentCriteria())   return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning(self::class . ' failed to evaluate full coupon context', [
                'user_coupon_id' => $this->id ?? null,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }
}
