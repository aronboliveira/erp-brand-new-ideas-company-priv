<?php

namespace App\Http\Controllers\Planning;

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PMC, PlansConstants as PLC, SettingsConstants as SC, ViewsConstants as VW};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{Plan, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions, ConsoleOutputs};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\{Arr, Facades\Crypt, Facades\DB, Facades\File, Facades\Log, Facades\Redirect, Facades\Validator, Facades\View as ViewFacade, Str};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class PlanController extends Controller
{
    use ChecksLogin, ChecksPermissions, ConsoleOutputs;

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
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PLN . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $plans ??= collect();
            $adminPaymentSetting ??= [];
            try {
                if (($guard = self::guard($request, PMC::MNG_PL, self::SINGULAR . '.index')) instanceof RedirectResponse)
                    return $guard;

                $t = microtime(true);
                $plans = Plan::all() ?? $plans;
                $adminPaymentSetting = Utility::getAdminPaymentSetting() ?? $adminPaymentSetting;
                $adminPaymentSetting = is_array($adminPaymentSetting) ? $adminPaymentSetting : [];
                $this->logExecutionTime($t, $action . '::fetchPlans', 'completed');

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

                return view($viewPath, compact(DC::TABLE_PLANS, 'adminPaymentSetting'));
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

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PLN . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $arrDuration ??= [];
            try {
                if (($guard = self::guard($request, PMC::CR_PL, self::SINGULAR . '.index')) instanceof RedirectResponse)
                    return $guard;

                $arrDuration = [
                    'lifetime' => __('Lifetime'),
                    'month' => __('Per Month'),
                    'year' => __('Per Year'),
                ] ?? $arrDuration;

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

                return view($viewPath, compact('arrDuration'));
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

    public function store(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $adminPaymentSetting ??= [];
            $data ??= [];
            $post ??= [];
            $newFilePath ??= null;
            $inTransaction ??= false;
            try {
                if (($guard = self::guard($request, PMC::CR_PL, self::SINGULAR . '.index')) instanceof RedirectResponse)
                    return $guard;

                $t = microtime(true);
                $adminPaymentSetting = Utility::getAdminPaymentSetting() ?? $adminPaymentSetting;
                $adminPaymentSetting = is_array($adminPaymentSetting) ? $adminPaymentSetting : [];
                if (!self::isAnyPaymentEnabled($adminPaymentSetting))
                    return Redirect::back()->with('error', __('Please set stripe or paypal api key & secret key for add new plan.'));
                $this->logExecutionTime($t, $action . '::fetchPaymentSettings', 'completed');

                $t = microtime(true);
                $validation = [
                    PLC::COL_DUR => 'required',
                    PLC::COL_MAX_CR => 'required|numeric',
                    PLC::COL_MAX_U => 'required|numeric',
                    PLC::COL_MAX_V => 'required|numeric',
                    PLC::COL_NM => 'required|unique:plans',
                    PLC::COL_PC => 'required|numeric|min:0',
                    PLC::COL_SL => 'required|numeric'
                ];
                if ($request->hasFile(PLC::COL_IMG))
                    $validation[PLC::COL_IMG] = 'required|file|max:' . SC::MAX_U_SIZE_DEF;
                $data = Validator::make($request->all(), $validation)->validate() ?? $data;
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $t = microtime(true);
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
                ]) ?? $post;
                $post = array_merge($post, ['id' => $candidateKey]);
                foreach ([PLC::COL_PJ, PLC::COL_CRM, PLC::COL_HRM, PLC::COL_ACC, PLC::COL_POS, PLC::COL_GPT] as $feature)
                    $post[$feature] = isset($data["enable_$feature"]) ? 1 : 0;
                if ($request->hasFile(PLC::COL_IMG)) {
                    $file = $request->file(PLC::COL_IMG);
                    $extension = $file?->getClientOriginalExtension();
                    $extension = is_string($extension) ? $extension : 'bin';
                    $fileName = self::SINGULAR . '_' . time() . '.' . $extension;
                    $dir = storage_path('uploads/' . self::SINGULAR . '/');
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    $file?->storeAs('uploads/' . self::SINGULAR . '/', $fileName);
                    $newFilePath = $dir . $fileName;
                    $post[PLC::COL_IMG] = $fileName;
                }
                $this->logExecutionTime($t, $action . '::preparePayload', 'completed');

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                Plan::create($post);
                DB::commit();
                $inTransaction = false;

                return Redirect::back()->with('success', __('Plan successfully created.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return Redirect::back()->with('error', $e->validator?->errors()->first());
            } catch (QueryException $e) {
                if ($inTransaction) DB::rollBack();
                if ($newFilePath && File::exists($newFilePath)) File::delete($newFilePath);
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
                if ($newFilePath && File::exists($newFilePath)) File::delete($newFilePath);
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
                if ($newFilePath && File::exists($newFilePath)) File::delete($newFilePath);
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

    public function edit(Request $request, string $planId): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PLN . '.edit';

        return $this->measureProfile($action, function () use ($request, $planId, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $plan ??= null;
            $arrDuration ??= null;
            try {
                if (($guard = self::guard($request, PMC::ED_PL, self::SINGULAR . '.index')) instanceof RedirectResponse)
                    return $guard;

                $t = microtime(true);
                $plan = Plan::findOrFail($planId);
                $arrDuration = $plan?->duration ?? $arrDuration;
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

                return view($viewPath, compact(self::SINGULAR, 'arrDuration'));
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

    public function update(Request $request, string $planId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $planId, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $adminPaymentSetting ??= [];
            $data ??= [];
            $post ??= [];
            $newFilePath ??= null;
            $inTransaction ??= false;
            try {
                if (($guard = self::guard($request, PMC::ED_PL, self::SINGULAR . '.index')) instanceof RedirectResponse)
                    return $guard;

                $t = microtime(true);
                $adminPaymentSetting = Utility::getAdminPaymentSetting() ?? $adminPaymentSetting;
                $adminPaymentSetting = is_array($adminPaymentSetting) ? $adminPaymentSetting : [];
                if (!self::isAnyPaymentEnabled($adminPaymentSetting))
                    return Redirect::back()->with('error', __('Please set stripe api key & secret key for add new plan.'));
                $this->logExecutionTime($t, $action . '::fetchPaymentSettings', 'completed');

                $t = microtime(true);
                $plan = Plan::findOrFail($planId);
                $validation = [
                    PLC::COL_DUR => 'required',
                    PLC::COL_MAX_CR => 'required|numeric',
                    PLC::COL_MAX_U => 'required|numeric',
                    PLC::COL_MAX_V => 'required|numeric',
                    PLC::COL_NM => 'required|unique:plans,name,' . $planId,
                    PLC::COL_SL => 'required|numeric'
                ];
                $data = Validator::make($request->all(), $validation)->validate() ?? $data;
                $this->logExecutionTime($t, $action . '::validate', 'completed');

                $t = microtime(true);
                $post = Arr::only($data, [
                    PLC::COL_NM,
                    PLC::COL_DUR,
                    PLC::COL_MAX_U,
                    PLC::COL_MAX_CR,
                    PLC::COL_MAX_V,
                    PLC::COL_SL
                ]) ?? $post;
                foreach ([PLC::COL_PJ, PLC::COL_CRM, PLC::COL_HRM, PLC::COL_ACC, PLC::COL_POS, PLC::COL_GPT] as $feature)
                    $post[$feature] = isset($data["enable_$feature"]) ? 1 : 0;
                if ($request->hasFile(PLC::COL_IMG)) {
                    $file = $request->file(PLC::COL_IMG);
                    $extension = $file?->getClientOriginalExtension();
                    $extension = is_string($extension) ? $extension : 'bin';
                    $fileName = self::SINGULAR . '_' . time() . '.' . $extension;
                    $dir = storage_path('uploads/' . self::SINGULAR . '/');
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    $oldImage = $plan?->image;
                    $oldPath = $oldImage ? $dir . $oldImage : null;
                    if ($oldPath && File::exists($oldPath)) File::delete($oldPath);
                    $file?->storeAs('uploads/' . self::SINGULAR . '/', $fileName);
                    $newFilePath = $dir . $fileName;
                    $post[PLC::COL_IMG] = $fileName;
                }
                $this->logExecutionTime($t, $action . '::preparePayload', 'completed');

                DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                DB::beginTransaction();
                $inTransaction = true;
                $plan?->update($post);
                DB::commit();
                $inTransaction = false;

                return Redirect::back()->with('success', __('Plan successfully updated.'));
            } catch (ValidationException $e) {
                Log::warning($method . ' validation failed', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'errors' => $e->errors() ?? [],
                ]);
                $this->consoleOutput($method . ' validation failed', 'error');
                return Redirect::back()->with('error', $e->validator?->errors()->first());
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
                if ($inTransaction) DB::rollBack();
                if ($newFilePath && File::exists($newFilePath)) File::delete($newFilePath);
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
                if ($newFilePath && File::exists($newFilePath)) File::delete($newFilePath);
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
                if ($newFilePath && File::exists($newFilePath)) File::delete($newFilePath);
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

    public const USR_PLN = 'userPlan';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const EDT = 'edit';
    public const UPD = 'update';

    public function userPlan(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action, $method) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');

            $inTransaction ??= false;
            try {
                $t = microtime(true);
                $code = (string) $request->code;
                $planId = Crypt::decrypt($code);
                $planId = is_scalar($planId) ? (string) $planId : null;
                if (!$planId) return Redirect::back()->with('error', __('Something is wrong.'));
                $plan = Plan::findOrFail($planId);
                $this->logExecutionTime($t, $action . '::fetchPlan', 'completed');

                if (($plan?->price ?? 0) <= 0) {
                    DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                    DB::beginTransaction();
                    $inTransaction = true;
                    $user?->assignPlan($plan?->id);
                    DB::commit();
                    $inTransaction = false;
                    return Redirect::route(self::SINGULAR . '.index')->with('success', __('Plan successfully activated.'));
                }

                return Redirect::back()->with('error', __('Something is wrong.'));
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
                return Redirect::back()->with('error', __('Something is wrong.'));
            } catch (ModelNotFoundException $e) {
                Log::warning($method . ' plan not found', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'action' => $action,
                    'user_id' => $user?->id,
                    'plan_id' => $request->code,
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

    private static function isAnyPaymentEnabled(array $settings): bool
    {
        foreach (self::paymentMethodKeys as $key) if (($settings[$key] ?? '') === 'on') return true;
        return false;
    }

    /**
     * Show a single plan.
     */
    public function show(Request $request, int|string $id): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $viewPath ??= VW::PLN . '.show';
        return $this->measureProfile($action, function () use ($request, $id, $action, $method, $viewPath) {
            $t = microtime(true);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user ??= $userOrRedirect;
            $this->logExecutionTime($t, $action . '::checkLogin', 'completed');
            try {
                if (($guard = self::guard($request, PMC::MNG_PL, self::SINGULAR . '.index')) instanceof RedirectResponse)
                    return $guard;
                $plan = Plan::findOrFail($id);
                $adminPaymentSetting = Utility::getAdminPaymentSetting() ?? [];
                $adminPaymentSetting = is_array($adminPaymentSetting) ? $adminPaymentSetting : [];
                if (!ViewFacade::exists($viewPath)) {
                    $this->consoleOutput($method . ' view missing: ' . $viewPath, 'error');
                    return Redirect::back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                return view($viewPath, compact(self::SINGULAR, 'adminPaymentSetting'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return Redirect::back()->with('error', __('Plan not found.'));
            } catch (\Throwable $e) {
                $this->consoleOutput($method . ' failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }

    public const DEL = 'destroy';
    /**
     * Delete a plan.
     */
    public function destroy(Request $request, int|string $plan): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $plan, $action, $method) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            try {
                if (($guard = self::guard($request, PMC::MNG_PL, self::SINGULAR . '.index')) instanceof RedirectResponse)
                    return $guard;
                $record = Plan::findOrFail($plan);
                $record->delete();
                Log::info($method . ' plan deleted', ['id' => $plan]);
                return redirect()->route(self::SINGULAR . '.index')
                    ->with('success', __('Plan successfully deleted.'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return Redirect::back()->with('error', __('Plan not found.'));
            } catch (\Throwable $e) {
                $this->consoleOutput($method . ' delete failed', 'error');
                return defaultUndefinedException($request, $e, $method);
            }
        });
    }
}
