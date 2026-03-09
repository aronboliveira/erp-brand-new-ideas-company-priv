<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Designation, Employee, Promotion, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{
    Auth,
    Log,
    Mail,
    Route,
    Validator,
    View as ViewFacade
};
use Symfony\Component\HttpFoundation\Response;

class PromotionController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::PRM . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $req    = $request;

        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, PermissionsConstants::MNG_PRM, self::REDIRECT_INDEX)) !== true) return $resp;

            Log::info("[{$class}::{$action}] start");
            try {
                $buildStart = microtime(true);
                $promotions = Promotion::query()
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->when(
                        strtolower($user[UsersConstants::COL_TP]) === 'employee',
                        function ($q) use ($user) {
                            $empId = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id');
                            return $q->where(UsersConstants::COL_EMP_ID, $empId);
                        }
                    )
                    ->with(['designation', 'employee']);
                $this->logExecutionTime($buildStart, $action, 'buildPromotionQuery');

                $fetchStart = microtime(true);
                $promotions = $promotions->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchPromotions');

                Log::info("[{$class}::{$action}] ready", ['count' => $promotions->count()]);
                return $this->renderViewChecked(self::REDIRECT_INDEX, compact('promotions'), $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", [
                    'route'   => Route::getCurrentRoute()?->getName(),
                    'method'  => $method,
                    'class'   => $class,
                    'user_id' => $user?->id ?? null,
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function create(Request $request): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $req    = $request;
        $viewPath = ViewsConstants::PRM . '.' . $action;

        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'create promotion', self::REDIRECT_INDEX)) !== true) return $resp;

            Log::info("[{$class}::{$action}] start");
            try {
                $desStart = microtime(true);
                $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id');
                $this->logExecutionTime($desStart, $action, 'pluckDesignations');

                $empStart = microtime(true);
                $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id');
                $this->logExecutionTime($empStart, $action, 'pluckEmployees');

                Log::info("[{$class}::{$action}] ready", [
                    'designations' => count($designations),
                    'employees'    => count($employees),
                ]);

                return $this->renderViewChecked($viewPath, [
                    'designations' => $designations,
                    'employees'    => $employees,
                ], $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", [
                    'route'   => Route::getCurrentRoute()?->getName(),
                    'method'  => $method,
                    'class'   => $class,
                    'user_id' => $user?->id ?? null,
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $req    = $request;

        return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'create promotion', self::REDIRECT_INDEX)) !== true) return $resp;

            Log::info("[{$class}::{$action}] start");
            try {
                $valStart = microtime(true);
                $validator = Validator::make($req->all(), [
                    UsersConstants::COL_EMP_ID => 'required',
                    'designation_id'           => 'required',
                    'promotion_title'          => 'required',
                    'promotion_date'           => 'required',
                ]);
                $this->logExecutionTime($valStart, $action, 'buildValidator');

                $failCheckStart = microtime(true);
                if ($validator->fails()) {
                    $this->logExecutionTime($failCheckStart, $action, 'validatorFailsCheck');
                    Log::debug("[{$class}::{$action}] validation failed", [
                        'first_error' => $validator->errors()->first(),
                        'route'       => Route::getCurrentRoute()?->getName(),
                        'method'      => $method,
                        'class'       => $class,
                    ]);
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $data = Arr::only($req->all(), [
                    UsersConstants::COL_EMP_ID,
                    'designation_id',
                    'promotion_title',
                    'promotion_date',
                ]);
                $data['description'] = $req->description ?? '';
                $data[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();

                $createStart = microtime(true);
                $promotion = Promotion::create($data);
                $this->logExecutionTime($createStart, $action, 'createPromotion');

                $settingsStart = microtime(true);
                $settings = Utility::settings();
                $this->logExecutionTime($settingsStart, $action, 'loadSettings');

                if (($settings['promotion_sent'] ?? 0) == 1) {
                    $empFetchStart = microtime(true);
                    $employee    = Employee::find($promotion->employee_id);
                    $designation = Designation::find($promotion->designation_id);
                    $this->logExecutionTime($empFetchStart, $action, 'fetchEmployeeDesignation');

                    $promotionArr = [
                        'employee_name'         => $employee->name,
                        'promotion_designation' => $designation->name,
                        'promotion_title'       => $promotion->promotion_title,
                        'promotion_date'        => $promotion->promotion_date,
                    ];

                    $mailStart = microtime(true);
                    $resp = Utility::sendEmailTemplate('promotion_sent', [$employee->email], $promotionArr);
                    $this->logExecutionTime($mailStart, $action, 'sendPromotionEmail');

                    $msg = __('Promotion successfully created.');
                    $msg .= (!empty($resp) && $resp['is_success'] === false && !empty($resp['error']))
                        ? "<br><span class=\"text-danger\">{$resp['error']}</span>"
                        : '';

                    return redirect()->route(self::REDIRECT_INDEX)->with('success', $msg);
                }

                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Promotion successfully created.'));
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", [
                    'route'      => Route::getCurrentRoute()?->getName(),
                    'method'     => $method,
                    'class'      => $class,
                    'input_keys' => array_keys($req->all()),
                    'user_id'    => $user?->id ?? null,
                    'message'    => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
    }

    public function show(Request $request, Promotion $promotion): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $req    = $request;

        return $this->measureProfile($action, function () use ($req, $promotion, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            Log::info("[{$class}::{$action}] start", ['promotion_id' => $promotion->id]);
            try {
                $redirStart = microtime(true);
                $resp = redirect()->route(self::REDIRECT_INDEX);
                $this->logExecutionTime($redirStart, $action, 'buildRedirect');

                Log::info("[{$class}::{$action}] redirecting to index");
                return $resp;
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", [
                    'promotion_id' => $promotion->id,
                    'route'        => Route::getCurrentRoute()?->getName(),
                    'method'       => $method,
                    'class'        => $class,
                    'message'      => $e->getMessage(),
                    'file'         => $e->getFile(),
                    'line'         => $e->getLine(),
                ]);
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'promotion_id' => $promotion->id]);
    }

    public function edit(Request $request, Promotion $promotion): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $req    = $request;
        $viewPath = ViewsConstants::PRM . '.edit';

        return $this->measureProfile($action, function () use ($req, $promotion, $action, $method, $class, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'edit promotion', self::REDIRECT_INDEX)) !== true) return $resp;
            if ($promotion->created_by !== $user?->creatorId())
                return response()->json(['error' => __('Permission denied.')], Response::HTTP_UNAUTHORIZED);

            Log::info("[{$class}::{$action}] start", ['promotion_id' => $promotion->id]);
            try {
                $desStart = microtime(true);
                $designations = Designation::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id');
                $this->logExecutionTime($desStart, $action, 'pluckDesignations');

                $empStart = microtime(true);
                $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id');
                $this->logExecutionTime($empStart, $action, 'pluckEmployees');

                Log::info("[{$class}::{$action}] ready", [
                    'promotion_id'  => $promotion->id,
                    'designations'  => count($designations),
                    'employees'     => count($employees),
                ]);

                return $this->renderViewChecked($viewPath, [
                    'promotion'    => $promotion,
                    'designations' => $designations,
                    'employees'    => $employees,
                ], $action);
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", [
                    'route'        => Route::getCurrentRoute()?->getName(),
                    'method'       => $method,
                    'class'        => $class,
                    'promotion_id' => $promotion->id,
                    'message'      => $e->getMessage(),
                    'file'         => $e->getFile(),
                    'line'         => $e->getLine(),
                ]);
                Log::error("[{$class}::{$action}] error", ['error' => $e->getMessage()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'promotion_id' => $promotion->id]);
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $req    = $request;

        return $this->measureProfile($action, function () use ($req, $promotion, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'edit promotion', self::REDIRECT_INDEX)) !== true) return $resp;
            if ($promotion->created_by !== $user?->creatorId())
                return defaultPermissionDenial($req, new AuthorizationException($promotion->getKey()), $class . '::' . $action, route(self::REDIRECT_INDEX));

            Log::info("[{$class}::{$action}] start", ['promotion_id' => $promotion->id]);
            try {
                $valStart = microtime(true);
                $validator = Validator::make($req->all(), [
                    UsersConstants::COL_EMP_ID => 'required',
                    'designation_id'           => 'required',
                    'promotion_title'          => 'required',
                    'promotion_date'           => 'required',
                ]);
                $this->logExecutionTime($valStart, $action, 'buildValidator');

                $failCheckStart = microtime(true);
                if ($validator->fails()) {
                    $this->logExecutionTime($failCheckStart, $action, 'validatorFailsCheck');
                    Log::debug("[{$class}::{$action}] validation failed", [
                        'first_error'  => $validator->errors()->first(),
                        'route'        => Route::getCurrentRoute()?->getName(),
                        'method'       => $method,
                        'class'        => $class,
                        'promotion_id' => $promotion->id,
                    ]);
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $data = Arr::only($req->all(), [
                    UsersConstants::COL_EMP_ID,
                    'designation_id',
                    'promotion_title',
                    'promotion_date',
                ]);
                $data['description'] = $req->description ?? '';

                $updStart = microtime(true);
                $promotion->update($data);
                $this->logExecutionTime($updStart, $action, 'updatePromotion');

                Log::info("[{$class}::{$action}] updated", ['promotion_id' => $promotion->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Promotion successfully updated.'));
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", [
                    'promotion_id' => $promotion->id,
                    'route'        => Route::getCurrentRoute()?->getName(),
                    'method'       => $method,
                    'class'        => $class,
                    'input_keys'   => array_keys($req->all()),
                    'user_id'      => $user?->id ?? null,
                    'message'      => $e->getMessage(),
                    'file'         => $e->getFile(),
                    'line'         => $e->getLine(),
                ]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'promotion_id' => $promotion->id]);
    }

    public function destroy(Request $request, Promotion $promotion): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $req    = $request;

        return $this->measureProfile($action, function () use ($req, $promotion, $action, $method, $class) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($req, 'delete promotion', self::REDIRECT_INDEX)) !== true) return $resp;
            if ($promotion->created_by !== $user?->creatorId())
                return defaultPermissionDenial($req, new AuthorizationException($promotion->getKey()), $class . '::' . $action, route(self::REDIRECT_INDEX));

            Log::info("[{$class}::{$action}] start", ['promotion_id' => $promotion->id]);
            try {
                $delStart = microtime(true);
                $promotion->delete();
                $this->logExecutionTime($delStart, $action, 'deletePromotion');

                Log::info("[{$class}::{$action}] deleted", ['promotion_id' => $promotion->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Promotion successfully deleted.'));
            } catch (\Throwable $e) {
                Log::debug("[{$class}::{$action}] debug error context", [
                    'promotion_id' => $promotion->id,
                    'route'        => Route::getCurrentRoute()?->getName(),
                    'method'       => $method,
                    'class'        => $class,
                    'user_id'      => $user?->id ?? null,
                    'message'      => $e->getMessage(),
                    'file'         => $e->getFile(),
                    'line'         => $e->getLine(),
                ]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'promotion_id' => $promotion->id]);
    }

    /**
     * Centralized view existence + render with timings.
     */
    private function renderViewChecked(string $viewPath, array $data, string $action): View|RedirectResponse
    {
        $existsStart = microtime(true);
        $exists = ViewFacade::exists($viewPath);
        $this->logExecutionTime($existsStart, $action, 'viewExistsCheck');

        if (!$exists) {
            Log::warning("[renderViewChecked] view missing", ['view' => $viewPath]);
            return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }

        $renderStart = microtime(true);
        $response = view($viewPath, $data);
        $this->logExecutionTime($renderStart, $action, 'viewRender');

        return $response;
    }
}
