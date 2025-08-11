<?php

namespace App\Models;

use App\Config\Constants\{
    PermissionsConstants,
    PlansConstants,
    UsersConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\{Eloquent\Model, QueryException};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Validation\ValidationException;

class Plan extends Model
{
    use UsesUuids;

    private const COL_ACCOUNT       = PlansConstants::COL_ACC;
    private const COL_CHATGPT       = PlansConstants::COL_GPT;
    private const COL_CRM           = PlansConstants::COL_CRM;
    private const COL_DESCRIPTION   = PlansConstants::COL_DESC;
    private const COL_DURATION      = PlansConstants::COL_DUR;
    private const COL_HRM           = PlansConstants::COL_HRM;
    private const COL_IMAGE         = PlansConstants::COL_IMG;
    private const COL_MAX_CLIENTS   = PlansConstants::COL_MAX_CL;
    private const COL_MAX_CUSTOMERS = PlansConstants::COL_MAX_CR;
    private const COL_MAX_USERS     = PlansConstants::COL_MAX_U;
    private const COL_MAX_VENDORS   = PlansConstants::COL_MAX_V;
    private const COL_NAME          = PlansConstants::COL_NM;
    private const COL_POS           = PlansConstants::COL_POS;
    private const COL_PRICE         = PlansConstants::COL_PC;
    private const COL_PROJECT       = PlansConstants::COL_PJ;
    private const COL_STORAGE_LIMIT = PlansConstants::COL_SL;
    private const FILLABLE = [
        'id',
        self::COL_NAME,
        self::COL_PRICE,
        self::COL_DURATION,
        self::COL_MAX_USERS,
        self::COL_MAX_CUSTOMERS,
        self::COL_MAX_VENDORS,
        self::COL_MAX_CLIENTS,
        self::COL_DESCRIPTION,
        self::COL_IMAGE,
        self::COL_CRM,
        self::COL_HRM,
        self::COL_ACCOUNT,
        self::COL_PROJECT,
        self::COL_POS,
        self::COL_CHATGPT,
        self::COL_STORAGE_LIMIT,
    ];
    private const DURATION_OPTIONS = [ // ! CHANGED
        'lifetime' => 'Lifetime',
        'month'    => 'Per Month',
        'year'     => 'Per Year',
    ];
    private static ?self $cachedPlan = null; // ! CHANGED
    protected $fillable = self::FILLABLE;

    public static function durations(): array
    {
        return self::DURATION_OPTIONS;
    }

    public function status(): array
    {
        return array_map(fn ($v) => __($v), array_values(self::DURATION_OPTIONS));
    }

    public static function totalPlan(): int
    {
        return self::count();
    }

    public static function mostPurchasedPlan(): ?object
    {
        try {
            return User::select(DB::raw('count(*) as total'))
                ->where(UsersConstants::COL_TP, PermissionsConstants::CPN)
                ->where(
                    strtolower(static::class),
                    '!=',
                    self::where(self::COL_PRICE, '<=', 0)->value('id')
                )
                ->groupBy(strtolower(static::class))
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
    }

    public static function getPlan(string $id): ?self
    {
        return self::$cachedPlan ??= self::find($id);
    }
}
