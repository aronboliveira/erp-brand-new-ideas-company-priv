<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    CompaniesConstants as CC,
    DatabaseConstants as DC
};
use App\Enums\PaymentPatternType;
use App\Traits\{HasAuditFields, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Collection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property float|int|string|null $discount
 * @property string|null $discount_type
 * @property array|string|null $excluded_roles
 * @property bool|null $is_active
 * @property bool|null $must_be_verified
 * @property string|\Illuminate\Support\Carbon|null $valid_from
 * @property string|\Illuminate\Support\Carbon|null $valid_to
 * @property array|string|null $applicable_categories
 * @property string|null $date_limit_to_user
 * @property array|string|null $excluded_categories
 * @property array|string|null $excluded_products
 * @property int|null $limit
 * @property string|null $code
 * @property string|null $name

 * @property mixed $used_coupon
 */
class Coupon extends Model
{
    use HasFactory;
    use HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_COUPONS;

    private const FK_COUPON = 'coupon';

    protected $fillable = [
        'code',
        'name',
        'discount',
        BC::COL_DSC_TP,
        CC::COL_GIVEN_BY,
        BC::COL_MIN_UNLCK,
        BC::COL_MAX_DSC,
        'limit',
        'description',
        AC::COL_IA,
        BC::COL_VLD_FRM,
        BC::COL_VLD_TO,
        BC::COL_DT_LMT_TO_USER,
        'stackable',
        BC::COL_MUST_BE_VRF,
        BC::COL_MIN_PRV_ORD,
        BC::COL_MAX_PRV_ORD,
        BC::COL_CAN_BE_GIFT,
        BC::COL_EXC_RLS,
        BC::COL_EXC_PRD,
        BC::COL_EXC_CAT,
        BC::COL_APL_CAT,
        'rules',
    ];

    protected $casts = [
        'discount'                 => 'float',
        BC::COL_MIN_UNLCK         => 'float',
        BC::COL_MAX_DSC           => 'float',
        'limit'                    => 'integer',
        AC::COL_IA                 => 'boolean',
        'stackable'                => 'boolean',
        BC::COL_MUST_BE_VRF       => 'boolean',
        BC::COL_MIN_PRV_ORD       => 'integer',
        BC::COL_MAX_PRV_ORD       => 'integer',
        BC::COL_CAN_BE_GIFT       => 'boolean',
        BC::COL_VLD_FRM            => 'datetime',
        BC::COL_VLD_TO             => 'datetime',
        BC::COL_DT_LMT_TO_USER     => 'date',
        BC::COL_EXC_PRD            => 'array',
        BC::COL_EXC_CAT            => 'array',
        BC::COL_APL_CAT            => 'array',
        BC::COL_EXC_RLS            => 'array',
        'rules'                    => 'array',
        BC::COL_DSC_TP             => PaymentPatternType::class,
    ];

    protected $with = [
        'givenBy',
        'createdBy',
        'updatedBy',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $coupon): void {
            $coupon->setAttribute('code', strtoupper(trim((string) $coupon->getAttribute('code'))));
            $coupon->setAttribute('discount', max(0.0, (float) $coupon->getAttribute('discount')));
            $coupon->setAttribute('limit', max(1, (int) ($coupon->getAttribute('limit') ?: 1)));
            $pattern = PaymentPatternType::normalize($coupon->getAttribute(BC::COL_DSC_TP) ?? null)
                ?? PaymentPatternType::Fixed;
            $coupon->setAttribute(BC::COL_DSC_TP, $pattern);

            if ($coupon->getAttribute(BC::COL_MIN_UNLCK) === null) $coupon->setAttribute(BC::COL_MIN_UNLCK, 0.0);
            if ($coupon->getAttribute(BC::COL_MAX_DSC)   === null) $coupon->setAttribute(BC::COL_MAX_DSC, 0.0);
            if ($coupon->getAttribute(AC::COL_IA)        === null) $coupon->setAttribute(AC::COL_IA, true);
            if ($coupon->getAttribute(BC::COL_VLD_FRM)   === null) $coupon->setAttribute(BC::COL_VLD_FRM, now());
            if ($coupon->getAttribute(BC::COL_VLD_TO)    === null) $coupon->setAttribute(BC::COL_VLD_TO, now()->addMonth());
            if ($coupon->getAttribute('stackable')           === null) $coupon->setAttribute('stackable', true);
            if ($coupon->getAttribute(BC::COL_MUST_BE_VRF) === null) $coupon->setAttribute(BC::COL_MUST_BE_VRF, false);
            if ($coupon->getAttribute(BC::COL_CAN_BE_GIFT) === null) $coupon->setAttribute(BC::COL_CAN_BE_GIFT, false);

            foreach ([BC::COL_EXC_PRD, BC::COL_EXC_CAT, BC::COL_APL_CAT] as $attr)
                $coupon->setAttribute($attr, self::normalizeArrayAttribute($coupon->getAttribute($attr)));
            $productIds = array_filter($coupon->getAttribute(BC::COL_EXC_PRD) ?? [], fn($v) => $v !== '');
            $coupon->setAttribute(BC::COL_EXC_PRD, $productIds
                ? array_values(ProductService::query()
                    ->whereIn('id', $productIds)
                    ->pluck('id')
                    ->all())
                : []);
            $categoryAttrs   = [BC::COL_EXC_CAT, BC::COL_APL_CAT];
            $allCategoryIds  = [];
            foreach ($categoryAttrs as $attr)
                $allCategoryIds = array_merge(
                    $allCategoryIds,
                    array_filter($coupon->getAttribute($attr) ?? [], fn($v) => $v !== '')
                );
            if (!empty($allCategoryIds)) {
                $validCategoryIds = ProductServiceCategory::query()
                    ->whereIn('id', array_unique($allCategoryIds))
                    ->pluck('id')
                    ->all();
                foreach ($categoryAttrs as $attr) {
                    $attrIds  = array_filter($coupon->getAttribute($attr) ?? [], fn($v) => $v !== '');
                    $validIds = array_values(array_intersect($attrIds, $validCategoryIds));
                    $coupon->setAttribute($attr, $validIds);
                }
            } else
                foreach ($categoryAttrs as $attr)
                    $coupon->setAttribute($attr, []);
            $minPrev = (int) ($coupon->getAttribute(BC::COL_MIN_PRV_ORD) ?? 0);
            $maxPrev = (int) ($coupon->getAttribute(BC::COL_MAX_PRV_ORD) ?? 2);
            if ($minPrev < 0) $minPrev = 0;
            if ($maxPrev < 0) $maxPrev = 0;
            if ($maxPrev < $minPrev) $maxPrev = $minPrev;
            $coupon->setAttribute(BC::COL_MIN_PRV_ORD, $minPrev);
            $coupon->setAttribute(BC::COL_MAX_PRV_ORD, $maxPrev);
            $coupon->setAttribute(BC::COL_EXC_RLS, self::normalizeRoleArray($coupon->getAttribute(BC::COL_EXC_RLS)));
        });
    }

    protected static function normalizeArrayAttribute(mixed $value): ?array
    {
        if ($value === null || $value === '') return null;

        if (is_array($value))
            return array_values(array_filter(
                array_map('strval', $value),
                fn($v) => $v !== ''
            ));

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                return self::normalizeArrayAttribute($decoded);

            $parts = array_map('trim', explode(',', $value));
            $parts = array_filter($parts, fn($v) => $v !== '');
            return $parts ? array_values($parts) : null;
        }

        return null;
    }

    protected static function normalizeIdArray(mixed $value): array
    {
        $arr = self::normalizeArrayAttribute($value) ?? [];
        return array_values(array_unique($arr));
    }

    protected static function normalizeRoleArray(mixed $value): ?array
    {
        $base = self::normalizeArrayAttribute($value);
        if ($base === null) return null;
        $result = [];
        foreach ($base as $item) {
            $enum = \App\Enums\UserType::normalize($item);
            if ($enum) $result[] = $enum->value;
            else $result[] = strtolower(trim((string) $item));
        }
        return array_values(array_unique($result));
    }

    public function givenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, CC::COL_GIVEN_BY, 'id');
    }

    public function userCoupons(): HasMany
    {
        return $this->hasMany(UserCoupon::class, self::FK_COUPON, 'id');
    }

    public function excludedProducts(): Collection
    {
        $ids = $this->excludedProductIds();
        return $ids
            ? ProductService::whereIn('id', $ids)->get()
            : collect();
    }

    public function excludedCategories(): Collection
    {
        $ids = $this->excludedCategoryIds();
        return $ids
            ? ProductServiceCategory::whereIn('id', $ids)->get()
            : collect();
    }

    public function applicableCategories(): Collection
    {
        $ids = $this->applicableCategoryIds();
        return $ids
            ? ProductServiceCategory::whereIn('id', $ids)->get()
            : collect();
    }

    public function excludedProductIds(): array
    {
        return self::normalizeIdArray($this->{BC::COL_EXC_PRD});
    }

    public function excludedCategoryIds(): array
    {
        return self::normalizeIdArray($this->{BC::COL_EXC_CAT});
    }

    public function applicableCategoryIds(): array
    {
        return self::normalizeIdArray($this->{BC::COL_APL_CAT});
    }

    public function excludedRoles(): array
    {
        return self::normalizeRoleArray($this->{BC::COL_EXC_RLS}) ?? [];
    }

    public function usedCoupon(): int
    {
        return $this->userCoupons()->count();
    }

    public function used_coupon(): int
    {
        return $this->usedCoupon();
    }

    public function discountType(): PaymentPatternType
    {
        $type = $this->{BC::COL_DSC_TP};

        if ($type instanceof PaymentPatternType) return $type;

        return PaymentPatternType::normalize($type) ?? PaymentPatternType::Fixed;
    }

    public function isActive(?Carbon $at = null): bool
    {
        if (!(bool) $this->{AC::COL_IA}) return false;

        $at   ??= now();
        $from = $this->{BC::COL_VLD_FRM} ? Carbon::parse($this->{BC::COL_VLD_FRM}) : null;
        $to   = $this->{BC::COL_VLD_TO}  ? Carbon::parse($this->{BC::COL_VLD_TO})  : null;

        if ($from && $at->lt($from)) return false;
        if ($to && $at->gt($to))     return false;

        return true;
    }

    public function meetsMinimum(float $amount): bool
    {
        $min = (float) ($this->{BC::COL_MIN_UNLCK} ?? 0.0);
        if ($min <= 0.0) return true;
        return $amount >= $min;
    }

    public function withinPreviousOrderRange(int $count): bool
    {
        $min = (int) ($this->{BC::COL_MIN_PRV_ORD} ?? 0);
        $max = (int) ($this->{BC::COL_MAX_PRV_ORD} ?? 0);
        if ($count < $min) return false;
        if ($max > 0 && $count > $max) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if (!$this->isActive() || !$this->meetsMinimum($amount)) return 0.0;

        $type = $this->discountType();

        $discount = match ($type) {
            PaymentPatternType::Fixed      => (float) $this->discount,
            PaymentPatternType::Percentage => $amount * ((float) $this->discount / 100),
            default                        => 0.0,
        };

        $discount = max(0.0, $discount);

        $max = (float) ($this->{BC::COL_MAX_DSC} ?? 0.0);
        if ($max > 0.0 && $discount > $max) $discount = $max;

        return $discount;
    }

    public function excludesProduct(string $productId): bool
    {
        return in_array($productId, $this->excludedProductIds(), true);
    }

    public function excludesCategory(string $categoryId): bool
    {
        return in_array($categoryId, $this->excludedCategoryIds(), true);
    }

    public function excludesUserType(string|\App\Enums\UserType $type): bool
    {
        if ($type instanceof \App\Enums\UserType) $value = $type->value;
        else {
            $enum = \App\Enums\UserType::normalize($type);
            $value = $enum ? $enum->value : strtolower(trim((string) $type));
        }
        $roles = $this->excludedRoles();
        return in_array($value, $roles, true);
    }

    public function excludesUser(User $user): bool
    {
        $col = \App\Config\Constants\UsersConstants::COL_TP;
        $value = $user->{$col} ?? null;
        if ($value === null) return false;
        return $this->excludesUserType($value);
    }

    public function canBeUsedBy(User $user, int $previousOrders = 0, ?Carbon $at = null): bool
    {
        if (!$this->isActive($at)) return false;
        if ($this->{BC::COL_MUST_BE_VRF}) {
            $verifiedCol = \App\Config\Constants\UsersConstants::COL_EM_V_AT;
            if (!$user->{$verifiedCol}) return false;
        }
        if ($this->excludesUser($user)) return false;
        if (!$this->withinPreviousOrderRange($previousOrders)) return false;
        if ($this->{BC::COL_DT_LMT_TO_USER}) {
            $limit = Carbon::parse($this->{BC::COL_DT_LMT_TO_USER});
            $at ??= now();
            if ($at->gt($limit)) return false;
        }
        return true;
    }

    public function appliesToProduct(?ProductService $product, ?ProductServiceCategory $category = null): bool
    {
        if (!$product) return false;

        if ($this->excludesProduct($product->id)) return false;

        $categoryId = $category?->id ?? $product->category_id ?? null;

        if ($categoryId && $this->excludesCategory($categoryId)) return false;

        $allowed = $this->applicableCategoryIds();
        if (!$categoryId) return !$allowed;

        return !$allowed || in_array($categoryId, $allowed, true);
    }
}
