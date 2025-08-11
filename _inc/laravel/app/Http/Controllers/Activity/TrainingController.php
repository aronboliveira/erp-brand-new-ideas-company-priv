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
    Validator
};

class TrainingController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::TNR . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::MNG_TNG,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $creatorId = $user?->creatorId();
            $trainings = Training::with(['branches', 'types'])
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->get();
            $status = Training::$status;
            Log::debug("$action fetched", ['count' => $trainings->count()]);
            return view(ViewsConstants::TNR . '.' . __FUNCTION__, compact('trainings', 'status'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create training',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", [UsersConstants::COL_USER_ID => $user?->id]);
        $creatorId = $user?->creatorId();
        $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        $trainingTypes = TrainingType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');
        $trainers = Trainer::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck('first_name', 'id');
        $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck(UsersConstants::COL_NM, 'id');
        $options = Training::$options;

        return view(
            ViewsConstants::TNR . '.create',
            compact('branches', 'trainingTypes', 'trainers', 'employees', 'options')
        );
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create training',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", ['input' => $request->all()]);
        $rules = [
            'branch'         => 'required',
            'training_type'  => 'required',
            'training_cost'  => 'required|numeric',
            'employee'       => 'required',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->first();
            Log::warning("$action validation failed", ['message' => $msg]);
            return redirect()
                ->back()
                ->with('error', $msg);
        }

        try {
            DB::transaction(function () use ($request, $user) {
                Training::create([
                    'branch'         => $request->branch,
                    'trainer_option' => $request->trainer_option,
                    'training_type'  => $request->training_type,
                    'trainer'        => $request->trainer,
                    'training_cost'  => $request->training_cost,
                    'employee'       => $request->employee,
                    'start_date'     => $request->start_date,
                    'end_date'       => $request->end_date,
                    'description'    => $request->description,
                    DatabaseConstants::TABLE_CREATOR     => $user?->creatorId()
                ]);
            });
            Log::info("$action committed");
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Training successfully created.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function show(Request $request, string $id): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'view training',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", ['encryptedId' => $id]);
        try {
            $traId = Crypt::decrypt($id);
        } catch (\Throwable $e) {
            Log::warning("$action decrypt failed", ['id' => $id]);
            return redirect()
                ->back()
                ->with('error', __('Training Not Found.'));
        }
        $training = Training::findOrFail($traId);
        $performance = Training::$performance;
        $status = Training::$status;
        return view(
            ViewsConstants::TNR . '.' . __FUNCTION__,
            compact('training', 'performance', 'status')
        );
    }

    public function edit(Request $request, Training $training): View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit training',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", ['trainingId' => $training->id]);
        $creatorId = $user?->creatorId();
        $branches = Branch::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id');
        $trainingTypes = TrainingType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck('name', 'id');
        $trainers = Trainer::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck('first_name', 'id');
        $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->pluck(UsersConstants::COL_NM, 'id');
        $options = Training::$options;
        return view(
            ViewsConstants::TNR . '.' . __FUNCTION__,
            compact('branches', 'trainingTypes', 'trainers', 'employees', 'options', 'training')
        );
    }

    public function update(Request $request, Training $training): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit training',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", [
            'trainingId' => $training->id,
            'input'      => $request->all()
        ]);
        $rules = [
            'branch'         => 'required',
            'training_type'  => 'required',
            'training_cost'  => 'required|numeric',
            'employee'       => 'required',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->first();
            Log::warning("$action validation failed", ['message' => $msg]);
            return redirect()
                ->back()
                ->with('error', $msg);
        }

        try {
            DB::transaction(function () use ($request, $training) {
                $training->update([
                    'branch'         => $request->branch,
                    'trainer_option' => $request->trainer_option,
                    'training_type'  => $request->training_type,
                    'trainer'        => $request->trainer,
                    'training_cost'  => $request->training_cost,
                    'employee'       => $request->employee,
                    'start_date'     => $request->start_date,
                    'end_date'       => $request->end_date,
                    'description'    => $request->description
                ]);
            });
            Log::info("$action committed", ['trainingId' => $training->id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Training successfully updated.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, Training $training): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'delete training',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", ['trainingId' => $training->id]);
        try {
            DB::transaction(fn () => $training->delete());
            Log::info("$action committed", ['trainingId' => $training->id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Training successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function updateStatus(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit training',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info("$action called", ['input' => $request->all()]);
        try {
            DB::transaction(function () use ($request) {
                $t = Training::findOrFail($request->id);
                $t->update([
                    'performance' => $request->performance,
                    'status'      => $request->status,
                    'remarks'     => $request->remarks
                ]);
            });
            Log::info("$action committed", ['trainingId' => $request->id]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Training status successfully updated.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", [
                'error' => $e->getMessage(),
            ]);
            Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
