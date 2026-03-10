<?php

namespace App\Http\Controllers\Planning;

use App\Config\Constants\{PermissionsConstants as PMC, ViewsConstants as VW};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{Order, Plan, PlanRequest, User, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions, ConsoleOutputs};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Crypt, DB, Log, Redirect, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class PlanRequestController extends Controller
{
    use ChecksLogin, ChecksPermissions, ConsoleOutputs;

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PLN_RQ . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $planRequests ??= collect();
            try {
                if (($redirect = self::guard($request, PMC::VW_PL_RQ, VW::PLN_RQ . '.index')) instanceof RedirectResponse)
                    return $redirect;

                $t = microtime(true);
                $planRequests = PlanRequest::all() ?? $planRequests;
                $this->logExecutionTime($t, $action . '::fetchRequests', 'completed');

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('planRequests'));
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const RQ_VW = 'requestView';
    public function requestView(Request $request, string $planId): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PLN_RQ . '.show';

        return $this->measureProfile($action, function () use ($request, $planId, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $plan ??= null;
            try {
                if (($redirect = self::guard($request, PMC::VW_PL_DT, VW::PLN_RQ . '.index')) instanceof RedirectResponse)
                    return $redirect;

                $t = microtime(true);
                $decodedId = Crypt::decrypt((string) $planId);
                $decodedId = is_scalar($decodedId) ? (string) $decodedId : null;
                if (!$decodedId) return Redirect::back()->with('error', __('Plan not found.'));
                $plan = Plan::findOrFail($decodedId);
                $this->logExecutionTime($t, $action . '::fetchPlan', 'completed');

                $t = microtime(true);
                $exists = ViewFacade::exists($viewPath);
                $this->logExecutionTime($t, $action . '::viewExistsCheck', 'completed');
                if (!$exists) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    Log::error($method . ' view not found', [
                        'error' => 'view_missing',
                        'error_class' => \RuntimeException::class,
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'action' => $action,
                        'view' => $viewPath,
                        'user_id' => $user?->id,
                    ]);
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }

                return view($viewPath, compact('plan'));
            } catch (AuthorizationException $e) {
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (DecryptException $e) {
                Log::warning($method . ' decrypt failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' decrypt failed', 'error');
                return Redirect::back()->with('error', __('Plan not found.'));
            } catch (ModelNotFoundException $e) {
                Log::warning($method . ' plan not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'plan_id' => $planId,
                ]);
                $this->consoleOutput($method . ' plan not found', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (QueryException $e) {
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const USR_RQ = 'userRequest';
    public function userRequest(Request $request, string $planId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $planId, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                if (($redirect = self::guard($request, PMC::RQ_PL, VW::PLN_RQ . '.index')) instanceof RedirectResponse)
                    return $redirect;

                $requestedPlan = (int) ($user?->requested_plan ?? 0);
                if ($requestedPlan !== 0)
                    return Redirect::back()->with('error', __('You already send request to another plan.'));

                $t = microtime(true);
                $decodedId = Crypt::decrypt((string) $planId);
                $decodedId = is_scalar($decodedId) ? (int) $decodedId : null;
                if (!$decodedId) return Redirect::back()->with('error', __('Plan not found.'));
                $plan = Plan::findOrFail($decodedId);
                $this->logExecutionTime($t, $action . '::fetchPlan', 'completed');

                $createData = [
                    'user_id' => $user?->id,
                    'plan_id' => $decodedId,
                    'duration' => $plan?->duration,
                ];

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                PlanRequest::create($createData);
                $user?->update(['requested_plan' => $decodedId]);
                DB::commit();
                $inTransaction = false;

                return Redirect::back()->with('success', __('Request Send Successfully.'));
            } catch (AuthorizationException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (DecryptException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' decrypt failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' decrypt failed', 'error');
                return Redirect::back()->with('error', __('Plan not found.'));
            } catch (ModelNotFoundException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' plan not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'plan_id' => $planId,
                ]);
                $this->consoleOutput($method . ' plan not found', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const AC_RQ = 'acceptRequest';
    public function acceptRequest(Request $request, int $id, int $response): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $response, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $planReq ??= null;
            $targetUser ??= null;
            $plan ??= null;
            $assign ??= [];
            $inTransaction ??= false;
            try {
                if (($redirect = self::guard($request, PMC::AC_PL_RQ, VW::PLN_RQ . '.index')) instanceof RedirectResponse)
                    return $redirect;

                $t = microtime(true);
                $planReq = PlanRequest::findOrFail($id);
                $targetUser = User::findOrFail($planReq?->user_id);
                $plan = Plan::find($planReq?->plan_id);
                $this->logExecutionTime($t, $action . '::fetchRequest', 'completed');

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;

                if ($response === 1) {
                    $targetUser?->update(['requested_plan' => 0, 'plan' => $planReq?->plan_id]);
                    $assign = $targetUser?->assignPlan($planReq?->plan_id, $targetUser?->id) ?? $assign;
                    $assignSuccess = (bool) ($assign['is_success'] ?? false);
                    if (!$assignSuccess || !$plan) {
                        DB::rollBack();
                        $inTransaction = false;
                        return Redirect::back()->with('error', __('Plan fail to upgrade.'));
                    }
                    if ($targetUser?->payment_subscription_id) {
                        try {
                            $targetUser?->cancel_subscription($targetUser?->id);
                        } catch (\Throwable $e) {
                            Log::warning($method . ' cancel subscription failed', [
                                'error' => $e->getMessage(),
                                'error_class' => get_class($e),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'action' => $action,
                                'user_id' => $targetUser?->id,
                            ]);
                            $this->consoleOutput($method . ' cancel subscription failed', 'error');
                        }
                    }
                    $adminPaymentSetting = Utility::getAdminPaymentSetting();
                    $currency = is_array($adminPaymentSetting) ? ($adminPaymentSetting['currency'] ?? 'USD') : 'USD';
                    $orderData = [
                        'order_id' => strtoupper(str_replace('.', '', uniqid('', true))),
                        'name' => null,
                        'email' => null,
                        'card_number' => null,
                        'card_exp_month' => null,
                        'card_exp_year' => null,
                        'plan_name' => $plan?->name,
                        'plan_id' => $plan?->id,
                        'price' => $plan?->price,
                        'price_currency' => $currency,
                        'tax_id' => '',
                        'payment_type' => __('Manually Upgrade By Super Admin'),
                        'payment_status' => 'success',
                        'receipt' => null,
                        'user_id' => $targetUser?->id
                    ];
                    Order::create($orderData);
                    $planReq?->delete();
                    DB::commit();
                    $inTransaction = false;
                    return Redirect::back()->with('success', __('Plan successfully upgraded.'));
                }

                $targetUser?->update(['requested_plan' => 0]);
                $planReq?->delete();
                DB::commit();
                $inTransaction = false;
                return Redirect::back()->with('success', __('Request Rejected Successfully.'));
            } catch (AuthorizationException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' permission denied', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' permission denied', 'error');
                return defaultPermissionDenial($request, $e, $method);
            } catch (ModelNotFoundException $e) {
                if ($inTransaction) DB::rollBack();
                Log::warning($method . ' request not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'request_id' => $id,
                ]);
                $this->consoleOutput($method . ' request not found', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const CC_RQ = 'cancelRequest';
    public const IDX = 'index';

    public function cancelRequest(Request $request, int $id): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $id, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                User::find($id)?->update(['requested_plan' => 0]);
                PlanRequest::where('user_id', $id)->delete();
                DB::commit();
                $inTransaction = false;
                return Redirect::back()->with('success', __('Request Canceled Successfully.'));
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' query failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' query failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Exception $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            } catch (\Throwable $e) {
                if ($inTransaction) DB::rollBack();
                Log::error($method . ' throwable', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                ]);
                $this->consoleOutput($method . ' throwable', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }
}
