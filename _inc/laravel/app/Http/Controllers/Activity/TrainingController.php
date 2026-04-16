<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{
    Branch,
    Employee,
    Trainer,
    Training,
    TrainingType,
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Crypt,
    DB,
    Log,
    Route,
    Validator,
    View as ViewFacade
};

use function App\Http\Controllers\Helpers\defaultUndefinedException;
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class TrainingController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::TNR . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TNR . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, PermissionsConstants::MNG_TNG, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            try {
                $buildStart = microtime(true);
                $creatorId = $user?->creatorId();
                $query = Training::with(['branches', 'types'])->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
                $this->logExecutionTime($buildStart, $action, 'buildQuery');
                $fetchStart = microtime(true);
                $trainings = $query->get();
                $this->logExecutionTime($fetchStart, $action, 'fetchTrainings');
                $status = Training::$status;
                Log::debug("[{$base}::{$action}] fetched", ['count' => $trainings->count()]);
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['trainings', 'status']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('trainings', 'status'));
                $this->logExecutionTime($renderStart, $action, 'renderIndex');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TNR . '.create';
        return $this->measureProfile($action, function () use ($req, $action, $method, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create training', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", [UsersConstants::COL_USER_ID => $user?->id, 'method' => $method]);
            $creatorId = $user?->creatorId();
            $listsStart = microtime(true);
            $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $trainingTypes = TrainingType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
            $trainers = Trainer::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('firstname', 'id');
            $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(UsersConstants::COL_NM, 'id');
            $options = Training::$options;
            $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['branches', 'trainingTypes', 'trainers', 'employees', 'options']]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('branches', 'trainingTypes', 'trainers', 'employees', 'options'));
            $this->logExecutionTime($renderStart, $action, 'renderCreate');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'create training', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['input' => $req->all(), 'method' => $method]);
            $rules = [
                'branch' => 'required',
                'training_type' => 'required',
                'training_cost' => 'required|numeric',
                'employee' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date'
            ];
            $valStart = microtime(true);
            $validator = Validator::make($req->all(), $rules);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("[{$base}::{$action}] validation failed", ['message' => $msg]);
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
                return redirect()->back()->with('error', $msg);
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $user, $action) {
                    $createStart = microtime(true);
                    Training::create([
                        'branch' => $req->branch,
                        'trainer_option' => $req->trainer_option,
                        'training_type' => $req->training_type,
                        'trainer' => $req->trainer,
                        'training_cost' => $req->training_cost,
                        'employee' => $req->employee,
                        'start_date' => $req->start_date,
                        'end_date' => $req->end_date,
                        'description' => $req->description,
                        DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()
                    ]);
                    $this->logExecutionTime($createStart, $action, 'createTraining');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] committed");
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Training successfully created.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $request, string $id): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TNR . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'view training', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['encrypted_id' => $id, 'user_id' => $user?->id, 'method' => $method]);
            $decStart = microtime(true);
            try {
                $traId = Crypt::decrypt($id);
                $this->logExecutionTime($decStart, $action, 'decryptId');
            } catch (\Throwable $e) {
                Log::warning("[{$base}::{$action}] decrypt failed", ['encrypted_id' => $id]);
                Log::debug("[{$base}::{$action}] decrypt context", ['exception' => get_class($e), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
                return redirect()->back()->with('error', __('Training Not Found.'));
            }
            try {
                $fetchStart = microtime(true);
                $training = Training::findOrFail($traId);
                $this->logExecutionTime($fetchStart, $action, 'fetchTraining');
                $performance = Training::$performance;
                $status = Training::$status;
                if (!ViewFacade::exists($viewPath)) {
                    Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                    Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['training', 'performance', 'status']]);
                    return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('training', 'performance', 'status'));
                $this->logExecutionTime($renderStart, $action, 'renderShow');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'encrypted_id' => $id]);
    }

    public function edit(Request $request, Training $training): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        $viewPath = ViewsConstants::TNR . '.' . $action;
        return $this->measureProfile($action, function () use ($req, $training, $action, $method, $base, $viewPath) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'edit training', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['trainingId' => $training->id, 'user_id' => $user?->id, 'method' => $method]);
            $creatorId = $user?->creatorId();
            $listsStart = microtime(true);
            $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(CompaniesConstants::COL_BRC_NM, 'id');
            $trainingTypes = TrainingType::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
            $trainers = Trainer::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck('firstname', 'id');
            $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)->pluck(UsersConstants::COL_NM, 'id');
            $options = Training::$options;
            $this->logExecutionTime($listsStart, $action, 'loadSelectLists');
            if (!ViewFacade::exists($viewPath)) {
                Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
                Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['branches', 'trainingTypes', 'trainers', 'employees', 'options', 'training']]);
                return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('branches', 'trainingTypes', 'trainers', 'employees', 'options', 'training'));
            $this->logExecutionTime($renderStart, $action, 'renderEdit');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'training_id' => $training->id]);
    }

    public function update(Request $request, Training $training): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $training, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'edit training', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['trainingId' => $training->id, 'input' => $req->all(), 'method' => $method, 'user_id' => $user?->id]);
            $rules = ['branch' => 'required', 'training_type' => 'required', 'training_cost' => 'required|numeric', 'employee' => 'required', 'start_date' => 'required|date', 'end_date' => 'required|date'];
            $valStart = microtime(true);
            $validator = Validator::make($req->all(), $rules);
            $this->logExecutionTime($valStart, $action, 'buildValidator');
            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("[{$base}::{$action}] validation failed", ['message' => $msg]);
                Log::debug("[{$base}::{$action}] validation context", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all()), 'training_id' => $training->id]);
                return redirect()->back()->with('error', $msg);
            }
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $training, $action) {
                    $updStart = microtime(true);
                    $training->update([
                        'branch' => $req->branch,
                        'trainer_option' => $req->trainer_option,
                        'training_type' => $req->training_type,
                        'trainer' => $req->trainer,
                        'training_cost' => $req->training_cost,
                        'employee' => $req->employee,
                        'start_date' => $req->start_date,
                        'end_date' => $req->end_date,
                        'description' => $req->description
                    ]);
                    $this->logExecutionTime($updStart, $action, 'updateTraining');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] committed", ['trainingId' => $training->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Training successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'training_id' => $training->id]);
    }

    public function destroy(Request $request, Training $training): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $training, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'delete training', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['trainingId' => $training->id, 'user_id' => $user?->id, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($training, $action) {
                    $delStart = microtime(true);
                    $training->delete();
                    $this->logExecutionTime($delStart, $action, 'deleteTraining');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] committed", ['trainingId' => $training->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Training successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'training_id' => $training->id]);
    }

    public const UPD_STT = 'updateStatus';
    public function updateStatus(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class  = static::class;
        $base   = class_basename($class);
        $req    = $request;
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($req, 'edit training', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("[{$base}::{$action}] called", ['input' => $req->all(), 'user_id' => $user?->id, 'method' => $method]);
            try {
                $txnStart = microtime(true);
                DB::transaction(function () use ($req, $action) {
                    $fetchStart = microtime(true);
                    $t = Training::findOrFail($req->id);
                    $this->logExecutionTime($fetchStart, $action, 'fetchTraining');
                    $updStart = microtime(true);
                    $t->update(['performance' => $req->performance, 'status' => $req->status, 'remarks' => $req->remarks]);
                    $this->logExecutionTime($updStart, $action, 'updateTrainingStatus');
                });
                $this->logExecutionTime($txnStart, $action, 'transaction');
                Log::info("[{$base}::{$action}] committed", ['trainingId' => $req->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Training status successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::REDIRECT_INDEX));
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }
}
