<?php

namespace App\Http\Controllers;

use App\Config\Constants\{MiddlewaresConstants, PermissionsConstants};
use App\Models\{Coupon, Plan, UserCoupon, Utility};
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\{Auth, Crypt, DB, Log, Validator};

final class CouponController extends Controller
{

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH]);
    }

    public function index(Request $r): mixed
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['user' => Auth::id()]);
        if (($auth = self::auth($r, PermissionsConstants::MNG_CPN)) !== true) {
            return $auth;
        }
        $coupons = Coupon::all();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', ['count' => $coupons->count()]);
        return view('coupon.index', compact('coupons'));
    }

    public function create(Request $r): mixed
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['user' => Auth::id()]);
        if (($auth = self::auth($r, 'create coupon')) !== true) {
            return $auth;
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' rendering form');
        return view('coupon.create');
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['user' => Auth::id()]);
        if (($auth = self::auth($r, 'create coupon')) !== true) {
            return $auth;
        }
        if ($resp = self::validateInput($r, [
            'name'     => 'required',
            'discount' => 'required|numeric',
            'limit'    => 'required|numeric',
        ])) {
            return $resp;
        }
        if (!$r->manualCode && !$r->autoCode) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' missing code');
            return redirect()->back()->with('error', __('Coupon code is required'));
        }

        DB::beginTransaction();
        try {
            $coupon = new Coupon();
            foreach (['name', 'discount', 'limit'] as $f) {
                $coupon->$f = $r->input($f);
            }
            $coupon->code = $r->manualCode
                ? strtoupper($r->manualCode)
                : $r->autoCode;
            $coupon->save();
            DB::commit();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' created', [
                'coupon_id' => $coupon->id
            ]);
            return redirect()
                ->route('coupons.index')
                ->with('success', __('Coupon successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $r,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(Coupon $coupon): mixed
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'coupon_id' => $coupon->id
        ]);
        $userCoupons = UserCoupon::where('coupon', $coupon->id)
            ->with('userDetail')->get();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', [
            'count' => $userCoupons->count()
        ]);
        return view('coupon.view', compact('userCoupons'));
    }

    public function edit(Request $r, Coupon $coupon): mixed
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'coupon_id' => $coupon->id, 'user' => Auth::id()
        ]);
        if (($auth = self::auth($r, 'edit coupon')) !== true) {
            return $auth;
        }
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' rendering', [
            'coupon_id' => $coupon->id
        ]);
        return view('coupon.edit', compact('coupon'));
    }

    public function update(Request $r, Coupon $coupon): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'coupon_id' => $coupon->id, 'user' => Auth::id()
        ]);
        if (($auth = self::auth($r, 'edit coupon')) !== true) {
            return $auth;
        }
        if ($resp = self::validateInput($r, [
            'name'     => 'required',
            'discount' => 'required|numeric',
            'limit'    => 'required|numeric',
            'code'     => 'required',
        ])) {
            return $resp;
        }

        DB::beginTransaction();
        try {
            foreach (['name', 'discount', 'limit', 'code'] as $f) {
                $coupon->$f = $r->input($f);
            }
            $coupon->save();
            DB::commit();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' updated', [
                'coupon_id' => $coupon->id
            ]);
            return redirect()
                ->route('coupons.index')
                ->with('success', __('Coupon successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $r,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $r, Coupon $coupon): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'coupon_id' => $coupon->id, 'user' => Auth::id()
        ]);
        if (($auth = self::auth($r, 'delete coupon')) !== true) {
            return $auth;
        }

        DB::beginTransaction();
        try {
            $coupon->delete();
            DB::commit();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' deleted', [
                'coupon_id' => $coupon->id
            ]);
            return redirect()
                ->route('coupons.index')
                ->with('success', __('Coupon successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $r,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function applyCoupon(Request $r): JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'plan_id' => $r->plan_id, 'coupon' => $r->coupon
        ]);
        try {
            $planId = Crypt::decrypt($r->plan_id);
            $plan  = Plan::find($planId);
            if (!$plan || !$r->coupon) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' invalid input');
                return response()->json(['is_success' => false]);
            }

            $origPrice = self::formatPrice($plan->price);
            $coupon   = Coupon::where('code', strtoupper($r->coupon))
                ->where('is_active', '1')->first();
            if (!$coupon) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' not found');
                return response()->json([
                    'is_success' => false,
                    'final_price' => $origPrice,
                    'price' => number_format(
                        $plan->price,
                        Utility::getValByName('decimal_number')
                    ),
                    'message' => __('This coupon code is invalid or has expired.')
                ]);
            }
            if ($coupon->limit == $coupon->used_coupon()) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' exhausted', [
                    'coupon_id' => $coupon->id
                ]);
                return response()->json([
                    'is_success' => false,
                    'final_price' => $origPrice,
                    'price' => number_format(
                        $plan->price,
                        Utility::getValByName('decimal_number')
                    ),
                    'message' => __('This coupon code has expired.')
                ]);
            }

            $discount = ($plan->price / 100) * $coupon->discount;
            $final   = $plan->price - $discount;
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' applied', [
                'coupon_id' => $coupon->id,
                'discount' => $discount,
                'final' => $final
            ]);

            return response()->json([
                'is_success'    => true,
                'discount_price' => '-' . self::formatPrice($discount),
                'final_price'   => self::formatPrice($final),
                'price'         => number_format(
                    $final,
                    Utility::getValByName('decimal_number')
                ),
                'message'       => __('Coupon code has applied successfully.')
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', [
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $r,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                '/',
                false,
                ['error' => __('Server error')]
            );
        }
    }

    public function formatPrice(float|int $price): string
    {
        Log::info('Formating price...');
        $set = Utility::getAdminPaymentSetting();
        $cur = $set['currency'] ?? '$';
        return $cur . number_format(
            $price,
            Utility::getValByName('decimal_number')
        );
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
