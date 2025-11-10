<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PlansConstants as PLC,
    PermissionsConstants,
    SettingsConstants,
    ViewsConstants as VW
};
use App\Models\{Plan, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\{Arr, Str};
use Illuminate\Support\Facades\{Crypt, File, Log, Redirect, Validator, View as ViewFacade};

class PlanController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    private const SINGULAR = 'plan';
    private const paymentMethodKeys = [
        'is_aamarpay_enabled',
        'is_bank_transfer_enabled',
        'is_benefit_enabled',
        'is_cashfree_enabled',
        'is_coingate_enabled',
        'is_flutterwave_enabled',
        'is_iyzipay_enabled',
        'is_manually_payment_enabled',
        'is_mercado_enabled',
        'is_mollie_enabled',
        'is_payfast_enabled',
        'is_paypal_enabled',
        'is_paystack_enabled',
        'is_paytm_enabled',
        'is_paymentwall_enabled',
        'is_paytr_enabled',
        'is_razorpay_enabled',
        'is_sspay_enabled',
        'is_skrill_enabled',
        'is_stripe_enabled',
        'is_toyyibpay_enabled'
    ];

    public function index(Request $request): View|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PLN . '.index';
        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, PermissionsConstants::MNG_PL, self::SINGULAR . '.index')) instanceof RedirectResponse) return $guard;
            try {
                $plans = Plan::all();
                $adminPaymentSetting = Utility::getAdminPaymentSetting();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::SINGULAR . '.index'));
                return view($view, compact(DatabaseConstants::TABLE_PLANS, 'adminPaymentSetting'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PLN . '.create';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, PermissionsConstants::CR_PL, self::SINGULAR . '.index')) instanceof RedirectResponse) return $guard;
            $arrDuration = [
                'lifetime' => __('Lifetime'),
                'month' => __('Per Month'),
                'year' => __('Per Year'),
            ];
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::SINGULAR . '.index'));
            return view($view, compact('arrDuration'));
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, PermissionsConstants::CR_PL, self::SINGULAR . '.index')) instanceof RedirectResponse) return $guard;
            try {
                $adminPaymentSetting = Utility::getAdminPaymentSetting();
                $methods = self::paymentMethodKeys;
                if (empty($adminPaymentSetting) || !collect($methods)->some(fn($m) => $adminPaymentSetting[$m] === 'on'))
                    return Redirect::back()->with('error', __('Please set stripe or paypal api key & secret key for add newself::SINGULAR. .'));
                $validation = [
                    PLC::COL_DUR      => 'required',
                    PLC::COL_MAX_CR   => 'required|numeric',
                    PLC::COL_MAX_U    => 'required|numeric',
                    PLC::COL_MAX_V    => 'required|numeric',
                    PLC::COL_NM       => 'required|unique:plans',
                    PLC::COL_PC       => 'required|numeric|min:0',
                    PLC::COL_SL       => 'required|numeric'
                ];
                if ($request->hasFile(PLC::COL_IMG)) {
                    $validation[PLC::COL_IMG] = 'required|file|max:' . SettingsConstants::MAX_U_SIZE_DEF;
                }
                Validator::make($request->all(), $validation)->validate();
                $data = $request->all();
                do $candidateKey = Str::uuid()->toString();
                while (Plan::where('query_key', $candidateKey)->exists());
                $post = Arr::only($data, [
                    PLC::COL_NM,
                    PLC::COL_PC,
                    PLC::COL_DUR,
                    PLC::COL_MAX_U,
                    PLC::COL_MAX_CR,
                    PLC::COL_MAX_V,
                    PLC::COL_SL
                ]);
                $post = array_merge($post, ['id' => $candidateKey]);
                foreach ([PLC::COL_PJ, PLC::COL_CRM, PLC::COL_HRM, PLC::COL_ACC, PLC::COL_POS, PLC::COL_GPT] as $feature)
                    $post[$feature] = isset($data["enable_$feature"]) ? 1 : 0;
                if ($request->hasFile(PLC::COL_IMG)) {
                    $file = $request->file(PLC::COL_IMG);
                    $fileName = self::SINGULAR . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $dir = storage_path('uploads/' . self::SINGULAR . '/');
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    $file->storeAs('uploads/' . self::SINGULAR . '/', $fileName);
                    $post[PLC::COL_IMG] = $fileName;
                }
                Plan::create($post);
                return Redirect::back()->with('success', __('Plan successfully created.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function edit(Request $request, string $planId): View|RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PLN . '.edit';

        return $this->measureProfile($action, function () use ($request, $planId, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, PermissionsConstants::ED_PL, self::SINGULAR . '.index')) instanceof RedirectResponse) return $guard;
            try {
                $plan = Plan::findOrFail($planId);
                $arrDuration = $plan->duration;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::SINGULAR . '.index'));
                return view($view, compact(self::SINGULAR, 'arrDuration'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function update(Request $request, string $planId): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $planId, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($guard = self::guard($request, PermissionsConstants::ED_PL, self::SINGULAR . '.index')) instanceof RedirectResponse) return $guard;
            try {
                $adminPaymentSetting = Utility::getAdminPaymentSetting();
                if (!self::isAnyPaymentEnabled($adminPaymentSetting)) {
                    return Redirect::back()->with('error', __('Please set stripe api key & secret key for add newself::SINGULAR. .'));
                }
                $plan = Plan::findOrFail($planId);
                $validation = [
                    PLC::COL_DUR      => 'required',
                    PLC::COL_MAX_CR   => 'required|numeric',
                    PLC::COL_MAX_U    => 'required|numeric',
                    PLC::COL_MAX_V    => 'required|numeric',
                    PLC::COL_NM       => 'required|unique:plans,name,' . $planId,
                    PLC::COL_SL       => 'required|numeric'
                ];
                Validator::make($request->all(), $validation)->validate();
                $data = $request->all();
                $post = Arr::only($data, [
                    PLC::COL_NM,
                    PLC::COL_DUR,
                    PLC::COL_MAX_U,
                    PLC::COL_MAX_CR,
                    PLC::COL_MAX_V,
                    PLC::COL_SL
                ]);
                foreach ([PLC::COL_PJ, PLC::COL_CRM, PLC::COL_HRM, PLC::COL_ACC, PLC::COL_POS, PLC::COL_GPT] as $feature) {
                    $post[$feature] = isset($data["enable_$feature"]) ? 1 : 0;
                }
                if ($request->hasFile(PLC::COL_IMG)) {
                    $file = $request->file(PLC::COL_IMG);
                    $fileName = self::SINGULAR . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $dir = storage_path('uploads/' . self::SINGULAR . '/');
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    $oldPath = $dir . $plan->image;
                    if (File::exists($oldPath)) File::delete($oldPath);
                    $file->storeAs('uploads/' . self::SINGULAR . '/', $fileName);
                    $post[PLC::COL_IMG] = $fileName;
                }
                $plan->update($post);
                return Redirect::back()->with('success', __('Plan successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const USR_PLN = 'userPlan';
    public function userPlan(Request $request): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            try {
                $planId = Crypt::decrypt($request->code);
                $plan = Plan::findOrFail($planId);
                if ($plan->price <= 0) {
                    $user?->assignPlan($plan->id);
                    return Redirect::route(self::SINGULAR . '.index')->with('success', __('Plan successfully activated.'));
                }
                return Redirect::back()->with('error', __('Something is wrong.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    private static function isAnyPaymentEnabled(array $settings): bool
    {
        foreach (self::paymentMethodKeys as $key) if (($settings[$key] ?? '') === 'on') return true;
        return false;
    }
}
