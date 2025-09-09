<?php

namespace App\Http\Controllers;

use App\Config\Constants\{ViewsConstants as VW};
use App\Http\Controllers\Controller;
use App\Models\{Order, Plan, PlanRequest, User, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Crypt, Log, Validator, View as ViewFacade};
use Illuminate\View\View;

class PlanRequestController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PLN_RQ . '.index';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'view plan requests', 'plan-request.index')) instanceof RedirectResponse) return $redirect;
            try {
                $planRequests = PlanRequest::all();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route('plan-request.index'));
                return view($view, compact('planRequests'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed to list plan requests: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const RQ_VW = 'requestView';
    public function requestView(Request $request, string $planId): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PLN_RQ . '.show';

        return $this->measureProfile($action, function () use ($request, $planId, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'view plan details', 'plan-request.index')) instanceof RedirectResponse) return $redirect;
            try {
                $id = Crypt::decrypt($planId);
                $plan = Plan::find($id);
                if (!$plan) return defaultUndefinedException($request, new \Exception('Plan not found'), $action, route('plan-request.index'));
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route('plan-request.index'));
                return view($view, compact('plan'));
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed to show plan details: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const USR_RQ = 'userRequest';
    public function userRequest(Request $request, string $planId): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $planId, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'request plan', 'plan-request.index')) instanceof RedirectResponse) return $redirect;
            try {
                if ($user->requested_plan != 0) return redirect()->back()->with('error', __('You already send request to another plan.'));
                $id = Crypt::decrypt($planId);
                $plan = Plan::find($id);
                if (!$plan) return defaultUndefinedException($request, new \Exception('Plan not found'), $action, route('plan-request.index'));
                $createData = [
                    'user_id'   => $user?->id,
                    'plan_id'   => $id,
                    'duration'  => $plan->duration,
                ];
                PlanRequest::create($createData);
                $user->update(['requested_plan' => $id]);
                return redirect()->back()->with('success', __('Request Send Successfully.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed to request plan: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const AC_RQ = 'acceptRequest';
    public function acceptRequest(Request $request, int $id, int $response): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $response, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($redirect = self::guard($request, 'accept plan request', 'plan-request.index')) instanceof RedirectResponse) return $redirect;
            try {
                $planReq = PlanRequest::find($id);
                if (!$planReq) return defaultUndefinedException($request, new \Exception('Request not found'), $action, route('plan-request.index'));
                $user = User::find($planReq->user_id);
                if ($response === 1) {
                    $user?->update(['requested_plan' => 0, 'plan' => $planReq->plan_id]);
                    $assign = $user?->assignPlan($planReq->plan_id, $user?->id);
                    $plan = Plan::find($planReq->plan_id);
                    $price = $plan->price;
                    if ($assign['is_success'] && $plan) {
                        if ($user?->payment_subscription_id) {
                            try {
                                $user?->cancel_subscription($user?->id);
                            } catch (\Throwable $e) {
                                Log::debug($e->getMessage());
                            }
                        }
                        $orderData = [
                            'order_id'       => strtoupper(str_replace('.', '', uniqid('', true))),
                            'name'           => null,
                            'email'          => null,
                            'card_number'    => null,
                            'card_exp_month' => null,
                            'card_exp_year'  => null,
                            'plan_name'      => $plan->name,
                            'plan_id'        => $plan->id,
                            'price'          => $price,
                            'price_currency' => Utility::getAdminPaymentSetting()['currency'] ?? 'USD',
                            'txn_id'         => '',
                            'payment_type'   => __('Manually Upgrade By Super Admin'),
                            'payment_status' => 'success',
                            'receipt'        => null,
                            'user_id'        => $user?->id
                        ];
                        Order::create($orderData);
                        $planReq->delete();
                        return redirect()->back()->with('success', __('Plan successfully upgraded.'));
                    }
                    return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                }
                $user?->update(['requested_plan' => 0]);
                $planReq->delete();
                return redirect()->back()->with('success', __('Request Rejected Successfully.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed to accept plan request: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public const CC_RQ = 'cancelRequest';
    public function cancelRequest(Request $request, int $id): RedirectResponse|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            try {
                User::find($id)?->update(['requested_plan' => 0]);
                PlanRequest::where('user_id', $id)->delete();
                return redirect()->back()->with('success', __('Request Canceled Successfully.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed to cancel request: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }
}
