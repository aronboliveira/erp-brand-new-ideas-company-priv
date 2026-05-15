<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    PlansConstants as PLC,
    UsersConstants as UC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\{Eloquent\Model, Eloquent\ModelNotFoundException, QueryException};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Validation\ValidationException;

use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property int|string $id
 * @property string|null $name
 * @property float|null $price
 * @property float|null $storage_limit
 * @property string|null $duration
 * @property string|null $description
 * @property int|string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|int|string|null $max_clients
 * @property float|int|string|null $max_customers
 * @property float|int|string|null $max_users
 * @property float|int|string|null $max_vendors
 * @property string|null $image
 */
class Plan extends Model
{
    use HasFactory;
    use UsesUuids, HasAuditFields;

    private const DURATION_OPTIONS = [
        'lifetime' => 'Lifetime',
        'month'    => 'Per Month',
        'semimonthly' => 'Semi Monthly',
        'quarterly' => 'Quarterly',
        'semiannual' => 'Semi Annual',
        'year'     => 'Per Year',
    ];
    private static ?self $cachedPlan = null;
    protected $fillable = [
        'query_key',
        PLC::COL_NM,
        PLC::COL_PC,
        PLC::COL_DUR,
        PLC::COL_MAX_U,
        PLC::COL_MAX_CR,
        PLC::COL_MAX_V,
        PLC::COL_MAX_CL,
        PLC::COL_DESC,
        PLC::COL_IMG,
        PLC::COL_CRM,
        PLC::COL_HRM,
        PLC::COL_ACC,
        PLC::COL_PJ,
        PLC::COL_POS,
        PLC::COL_GPT,
        PLC::COL_SL,
    ];
    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    public static function durations(): array
    {
        return self::DURATION_OPTIONS;
    }

    public function getStatus(): array
    {
        return array_map(fn($v) => __($v), array_values(self::DURATION_OPTIONS));
    }

    public static function totalPlan(): int
    {
        return self::count();
    }

    public static function mostPurchasedPlan(): object|null
    {
        try {
            $freePlanIds = Plan::query()
                ->where(PLC::COL_PC, '<=', 0)
                ->pluck('id');
            // users plan FK is UC::COL_PL ('plan'); UC::COL_PLAN_ID ('plan_id') is unused on this table.
            return User::query()->select([UC::COL_PL, DB::raw('COUNT(*) as total')])
                ->where(UC::COL_TP, PMC::CPN)
                ->whereNotNull(UC::COL_PL)
                ->when($freePlanIds->isNotEmpty(), fn($q) =>
                $q->whereNotIn(UC::COL_PL, $freePlanIds))
                ->groupBy(UC::COL_PL)
                ->orderByDesc('total')
                ->first();
        } catch (QueryException $e) {
            Log::error('Query Exception');
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
        } catch (ValidationException $e) {
            Log::error('Validation Exception');
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::error('Unexpected Exception');
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
        }
        return null;
    }

    public static function getPlan(string $id): ?self
    {
        if (self::$cachedPlan?->id === $id && Plan::where('id', self::$cachedPlan->id)->exists())
            return self::$cachedPlan;
        try {
            return self::$cachedPlan ??= self::where('id', $id)
                ->orWhere('query_key', $id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::notice("Plan not found with id: {$id}. Attempting to query by query_key.", [
                'message' => $e->getMessage(),
            ]);
            try {
                return self::$cachedPlan ??= self::where('query_key', $id)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                Log::warning("Plan not found with query_key: {$id}.", [
                    'message' => $e->getMessage(),
                ]);
                try {
                    return self::$cachedPlan ??= self::where('name', 'Free')->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    Log::warning("Free plan not found", [
                        'message' => $e->getMessage(),
                    ]);
                    return null;
                }
            }
            return self::$cachedPlan = null;
        }
    }

    /**
     * Returns translated duration labels.
     */
    public function status(): array
    {
        return array_values(static::durations());
    }
}
