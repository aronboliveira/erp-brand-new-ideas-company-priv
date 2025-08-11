<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PlansConstants,
    PermissionsConstants,
    SettingsConstants
};
use App\Models\{Plan, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Crypt, File, Log, Redirect, Validator};

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
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, PermissionsConstants::MNG_PL, self::SINGULAR . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        try {
            $plans = Plan::all();
            $adminPaymentSetting = Utility::getAdminPaymentSetting();
            return view(
                self::SINGULAR . '.index',
                compact(DatabaseConstants::TABLE_PLANS, 'adminPaymentSetting')
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, PermissionsConstants::CR_PL, self::SINGULAR . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        $arrDuration = [
            'lifetime' => __('Lifetime'),
            'month' => __('Per Month'),
            'year' => __('Per Year'),
        ];
        return view(self::SINGULAR . '.create', compact('arrDuration'));
    }

    public function store(Request $request): RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, PermissionsConstants::CR_PL, self::SINGULAR . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        try {
            $adminPaymentSetting = Utility::getAdminPaymentSetting();
            $methods = self::paymentMethodKeys;
            if (
                empty($adminPaymentSetting)
                || !collect($methods)
                    ->some(fn ($m) => $adminPaymentSetting[$m] === 'on')
            )
                return Redirect::back()
                    ->with('error', __('Please set stripe or paypal api key & secret key for add newself::SINGULAR. .'));
            $validation = [
                PlansConstants::COL_DUR      => 'required',
                PlansConstants::COL_MAX_CR => 'required|numeric',
                PlansConstants::COL_MAX_U     => 'required|numeric',
                PlansConstants::COL_MAX_V   => 'required|numeric',
                PlansConstants::COL_NM          => 'required|unique:plans',
                PlansConstants::COL_PC         => 'required|numeric|min:0',
                PlansConstants::COL_SL => 'required|numeric'
            ];
            if ($request->hasFile(PlansConstants::COL_IMG)) {
                $validation[PlansConstants::COL_IMG] = 'required|file|max:' . SettingsConstants::MAX_U_SIZE_DEF;
            }
            Validator::make($request->all(), $validation)->validate();
            $data = $request->all();
            $post = Arr::only(
                $data,
                [
                    PlansConstants::COL_NM,
                    PlansConstants::COL_PC,
                    PlansConstants::COL_DUR,
                    PlansConstants::COL_MAX_U,
                    PlansConstants::COL_MAX_CR,
                    PlansConstants::COL_MAX_V,
                    PlansConstants::COL_SL
                ]
            );
            foreach ([
                PlansConstants::COL_PJ,
                PlansConstants::COL_CRM,
                PlansConstants::COL_HRM,
                PlansConstants::COL_ACC,
                PlansConstants::COL_POS,
                PlansConstants::COL_GPT
            ] as $feature) {
                $post[$feature] = isset($data["enable_$feature"]) ? 1 : 0;
            }
            if ($request->hasFile(PlansConstants::COL_IMG)) {
                $file = $request->file(PlansConstants::COL_IMG);
                $fileName = self::SINGULAR . '_' . time() . '.' . $file->getClientOriginalExtension();
                $dir = storage_path('uploads/' . self::SINGULAR . '/');
                if (!file_exists($dir)) mkdir($dir, 0777, true);
                $file->storeAs('uploads/' . self::SINGULAR . '/', $fileName);
                $post[PlansConstants::COL_IMG] = $fileName;
            }
            Plan::create($post);
            return Redirect::back()
                ->with('success', __('Plan successfully created.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function edit(Request $request, string $planId): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, PermissionsConstants::ED_PL, self::SINGULAR . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        try {
            $plan = Plan::findOrFail($planId);
            $arrDuration = $plan->duration;
            return view(self::SINGULAR . '.edit', compact(self::SINGULAR, 'arrDuration'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function update(Request $request, string $planId): RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        if (
            ($guard = self::guard($request, PermissionsConstants::ED_PL, self::SINGULAR . '.index'))
            instanceof RedirectResponse
        ) return $guard;
        try {
            $adminPaymentSetting = Utility::getAdminPaymentSetting();
            if (!self::isAnyPaymentEnabled($adminPaymentSetting)) {
                return Redirect::back()
                    ->with('error', __('Please set stripe api key & secret key for add newself::SINGULAR. .'));
            }
            $plan = Plan::findOrFail($planId);
            $validation = [
                PlansConstants::COL_DUR      => 'required',
                PlansConstants::COL_MAX_CR => 'required|numeric',
                PlansConstants::COL_MAX_U     => 'required|numeric',
                PlansConstants::COL_MAX_V   => 'required|numeric',
                PlansConstants::COL_NM          => 'required|unique:plans,name,' . $planId,
                PlansConstants::COL_SL => 'required|numeric'
            ];
            Validator::make($request->all(), $validation)->validate();
            $data = $request->all();
            $post = Arr::only(
                $data,
                [
                    PlansConstants::COL_NM,
                    PlansConstants::COL_DUR,
                    PlansConstants::COL_MAX_U,
                    PlansConstants::COL_MAX_CR,
                    PlansConstants::COL_MAX_V,
                    PlansConstants::COL_SL
                ]
            );
            foreach ([
                PlansConstants::COL_PJ,
                PlansConstants::COL_CRM,
                PlansConstants::COL_HRM,
                PlansConstants::COL_ACC,
                PlansConstants::COL_POS,
                PlansConstants::COL_GPT
            ] as $feature) {
                $post[$feature] = isset($data["enable_$feature"]) ? 1 : 0;
            }
            if ($request->hasFile(PlansConstants::COL_IMG)) {
                $file = $request->file(PlansConstants::COL_IMG);
                $fileName = self::SINGULAR . '_' . time() . '.' . $file->getClientOriginalExtension();
                $dir = storage_path('uploads/' . self::SINGULAR . '/');
                if (!file_exists($dir)) mkdir($dir, 0777, true);
                $oldPath = $dir . $plan->image;
                if (File::exists($oldPath)) File::delete($oldPath);
                $file->storeAs('uploads/' . self::SINGULAR . '/', $fileName);
                $post[PlansConstants::COL_IMG] = $fileName;
            }
            $plan->update($post);
            return Redirect::back()
                ->with('success', __('Plan successfully updated.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function userPlan(Request $request): RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        try {
            $planId = Crypt::decrypt($request->code);
            $plan = Plan::findOrFail($planId);
            if ($plan->price <= 0) {
                $user?->assignPlan($plan->id);
                return Redirect::route(self::SINGULAR . '.index')
                    ->with('success', __('Plan successfully activated.'));
            }
            return Redirect::back()
                ->with('error', __('Something is wrong.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    private static function isAnyPaymentEnabled(array $settings): bool
    {
        foreach (self::paymentMethodKeys as $key)
            if (($settings[$key] ?? '') === 'on') return true;
        return false;
    }
}
