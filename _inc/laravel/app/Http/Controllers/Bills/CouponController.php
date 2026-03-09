<?php

namespace App\Http\Controllers\Bills;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{MiddlewaresConstants as MWC, PermissionsConstants as PMC, ViewsConstants as VW};
use App\Models\{Coupon, Plan, UserCoupon, Utility};
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\{Auth, Crypt, DB, Log, Route, Validator, View as ViewFacade};
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

final class CouponController extends Controller
{

    public function __construct()
    {
        $this->middleware([MWC::AUTH]);
    }

    public function index(Request $r): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $r;
        $viewPath = VW::CPN . '.index';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['user' => Auth::id(), 'method' => $method]);
            if (($auth = self::auth($req, PMC::MNG_CPN)) !== true) return $auth;
            try {
                $fetchStart = microtime(true);
                $coupons = Coupon::all();
                $this->logExecutionTime($fetchStart, $action, 'fetchCoupons');
                Log::info("[{$base}::{$action}] fetched", ['count' => $coupons->count()]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('coupons'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $r): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $r;
        $viewPath = VW::CPN . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['user' => Auth::id(), 'method' => $method]);
            if (($auth = self::auth($req, 'create coupon')) !== true) return $auth;
            try {
                Log::info("[{$base}::{$action}] rendering form");
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath);
                $this->logExecutionTime($renderStart, $action, 'renderCreate');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $r;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['user' => Auth::id(), 'method' => $method]);
            if (($auth = self::auth($req, 'create coupon')) !== true) return $auth;
            if ($resp = self::validateInput($req, ['name' => 'required', 'discount' => 'required|numeric', 'limit' => 'required|numeric'])) return $resp;
            if (!$req->manualCode && !$req->autoCode) {
                Log::warning("[{$base}::{$action}] missing code");
                return redirect()->back()->with('error', __('Coupon code is required'));
            }
            try {
                $createdId = null;
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action, &$createdId) {
                    $createStart = microtime(true);
                    $coupon = new Coupon();
                    foreach (['name', 'discount', 'limit'] as $f) $coupon->$f = $req->input($f);
                    $coupon->code = $req->manualCode ? strtoupper($req->manualCode) : $req->autoCode;
                    $coupon->save();
                    $createdId = $coupon->id;
                    $this->logExecutionTime($createStart, $action, 'createCoupon');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] created", ['coupon_id' => $createdId]);
                return redirect()->route('coupons.index')->with('success', __('Coupon successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Coupon $coupon): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $viewPath = VW::CPN . '.view';
        return $this->measureProfile($action, function () use ($coupon, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['coupon_id' => $coupon->id, 'method' => $method]);
            try {
                $fetchStart = microtime(true);
                $userCoupons = UserCoupon::where('coupon', $coupon->id)->with('userDetail')->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchUserCoupons');
                Log::info("[{$base}::{$action}] fetched", ['count' => $userCoupons->count()]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('userCoupons'));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'coupon_id' => $coupon->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException(request(), $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'coupon_id' => $coupon->id]);
    }

    public function edit(Request $r, Coupon $coupon): mixed
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $r;
        $viewPath = VW::CPN . '.edit';
        return $this->measureProfile($action, function () use ($req, $coupon, $action, $method, $class, $base, $viewPath) {
            Log::info("[{$base}::{$action}] start", ['coupon_id' => $coupon->id, 'user' => Auth::id(), 'method' => $method]);
            if (($auth = self::auth($req, 'edit coupon')) !== true) return $auth;
            try {
                Log::info("[{$base}::{$action}] rendering", ['coupon_id' => $coupon->id]);
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('coupon'));
                $this->logExecutionTime($renderStart, $action, 'renderEdit');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'coupon_id' => $coupon->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'coupon_id' => $coupon->id]);
    }

    public function update(Request $r, Coupon $coupon): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $r;
        return $this->measureProfile($action, function () use ($req, $coupon, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['coupon_id' => $coupon->id, 'user' => Auth::id(), 'method' => $method]);
            if (($auth = self::auth($req, 'edit coupon')) !== true) return $auth;
            if ($resp = self::validateInput($req, ['name' => 'required', 'discount' => 'required|numeric', 'limit' => 'required|numeric', 'code' => 'required'])) return $resp;
            try {
                $updatedId = $coupon->id;
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $coupon, $action, &$updatedId) {
                    $updStart = microtime(true);
                    foreach (['name', 'discount', 'limit', 'code'] as $f) $coupon->$f = $req->input($f);
                    $coupon->save();
                    $this->logExecutionTime($updStart, $action, 'updateCoupon');
                    $updatedId = $coupon->id;
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] updated", ['coupon_id' => $updatedId]);
                return redirect()->route('coupons.index')->with('success', __('Coupon successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'coupon_id' => $coupon->id]);
    }

    public function destroy(Request $r, Coupon $coupon): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $r;
        return $this->measureProfile($action, function () use ($req, $coupon, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['coupon_id' => $coupon->id, 'user' => Auth::id(), 'method' => $method]);
            if (($auth = self::auth($req, 'delete coupon')) !== true) return $auth;
            try {
                $deletedId = $coupon->id;
                $txnStart = microtime(true);
                DB::transaction(function () use ($coupon, $action, &$deletedId) {
                    $delStart = microtime(true);
                    $coupon->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteCoupon');
                    $deletedId = $coupon->id ?? $deletedId;
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] deleted", ['coupon_id' => $deletedId]);
                return redirect()->route('coupons.index')->with('success', __('Coupon successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'coupon_id' => $coupon->id]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'coupon_id' => $coupon->id]);
    }

    public const AP_CPN = 'applyCoupon';
    public function applyCoupon(Request $r): JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $r;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            Log::info("[{$base}::{$action}] start", ['plan_id' => $req->plan_id, 'coupon' => $req->coupon, 'method' => $method]);
            try {
                $decStart = microtime(true);
                $planId = Crypt::decrypt($req->plan_id);
                $this->logExecutionTime($decStart, $action, 'decryptPlanId');
                $planFetch = microtime(true);
                $plan = Plan::find($planId);
                $this->logExecutionTime($planFetch, $action, 'fetchPlan');
                if (!$plan || !$req->coupon) {
                    Log::warning("[{$base}::{$action}] invalid input", ['plan_exists' => (bool)$plan, 'has_coupon' => (bool)$req->coupon]);
                    return response()->json(['is_success' => false]);
                }
                $origPrice = self::formatPrice($plan->price);
                $coupFetch = microtime(true);
                $coupon = Coupon::where('code', strtoupper($req->coupon))->where('is_active', '1')->first();
                $this->logExecutionTime($coupFetch, $action, 'fetchCoupon');
                if (!$coupon) {
                    Log::warning("[{$base}::{$action}] coupon not found");
                    return response()->json([
                        'is_success'  => false,
                        'final_price' => $origPrice,
                        'price'       => number_format($plan->price, Utility::getValByName('decimal_number')),
                        'message'     => __('This coupon code is invalid or has expired.')
                    ]);
                }
                if ($coupon->limit == $coupon->used_coupon()) {
                    Log::warning("[{$base}::{$action}] coupon exhausted", ['coupon_id' => $coupon->id]);
                    return response()->json([
                        'is_success'  => false,
                        'final_price' => $origPrice,
                        'price'       => number_format($plan->price, Utility::getValByName('decimal_number')),
                        'message'     => __('This coupon code has expired.')
                    ]);
                }
                $calcStart = microtime(true);
                $discount = ($plan->price / 100) * $coupon->discount;
                $final = $plan->price - $discount;
                $this->logExecutionTime($calcStart, $action, 'calculateDiscount');
                Log::info("[{$base}::{$action}] applied", ['coupon_id' => $coupon->id, 'discount' => $discount, 'final' => $final]);
                return response()->json([
                    'is_success'     => true,
                    'discount_price' => '-' . self::formatPrice($discount),
                    'final_price'    => self::formatPrice($final),
                    'price'          => number_format($final, Utility::getValByName('decimal_number')),
                    'message'        => __('Coupon code has applied successfully.')
                ]);
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, '/', false, ['error' => __('Server error')]);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public const FMT_PRC = 'formatPrice';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function formatPrice(float|int $price): string
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        return $this->measureProfile($action, function () use ($price, $action, $method, $base) {
            Log::info("[{$base}::{$action}] start", ['price' => $price, 'method' => $method]);
            $setStart = microtime(true);
            $set = Utility::getAdminPaymentSetting();
            $this->logExecutionTime($setStart, $action, 'fetchSettings');
            $cur = $set['currency'] ?? '$';
            $fmtStart = microtime(true);
            $formatted = $cur . number_format($price, Utility::getValByName('decimal_number'));
            $this->logExecutionTime($fmtStart, $action, 'formatNumber');
            Log::info("[{$base}::{$action}] complete", ['formatted' => $formatted]);
            return $formatted;
        }, ['method' => $method, 'class' => $base]);
    }

    private static function auth(Request $r, string $perm): bool|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' checking permission', [
            'user_id'    => $r->user()->id,
            'permission' => $perm,
        ]);
        if (!$r->user()->can($perm)) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' denied', [
                'user_id'    => $r->user()->id,
                'permission' => $perm,
            ]);
            return defaultPermissionDenial(
                $r,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
            );
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' granted', [
            'user_id'    => $r->user()->id,
            'permission' => $perm,
        ]);
        return true;
    }

    private static function validateInput(Request $r, array $rules): ?RedirectResponse
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' validating input', [
            'rules' => $rules
        ]);
        $v = Validator::make($r->all(), $rules);
        if ($v->fails()) {
            $msg = $v->getMessageBag()->first();
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $msg]);
            return redirect()->back()->with('error', $msg);
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' passed');
        return null;
    }
}
