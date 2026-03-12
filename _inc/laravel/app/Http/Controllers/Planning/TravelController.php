<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Employee, Travel, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    DB,
    Log,
    Validator,
    View as ViewFacade
};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class TravelController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::TRV . '.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view = ViewsConstants::TRV . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::MNG_TRV, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("$action start", [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $creatorId = $user?->creatorId();
                $query = Travel::with('employee')->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId);
                if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                    $emp = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first();
                    $query->where(UsersConstants::COL_EMP_ID, $emp->id);
                }
                $travels = $query->get();
                Log::debug("$action fetched", ['count' => $travels->count()]);
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                return view($view, compact('travels'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view = ViewsConstants::TRV . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'create travel', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("$action start", [UsersConstants::COL_USER_ID => $user?->id]);
            $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view, compact('employees'));
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'create travel', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("$action start", ['input' => $request->all()]);
            $rules = [
                UsersConstants::COL_EMP_ID => 'required|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'purpose_of_visit' => 'required|string',
                'place_of_visit' => 'required|string'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("$action validation failed", ['message' => $msg]);
                return redirect()->back()->with('error', $msg);
            }
            try {
                $travel = DB::transaction(function () use ($request, $user) {
                    return Travel::create([
                        UsersConstants::COL_EMP_ID => $request->employee_id,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'purpose_of_visit' => $request->purpose_of_visit,
                        'place_of_visit' => $request->place_of_visit,
                        'description' => $request->description,
                        DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                    ]);
                });
                if (!empty(Utility::settings()['trip_sent'])) {
                    $employee = $travel->employee;
                    Utility::sendEmailTemplate('trip_sent', [$employee->id => $employee->email], [
                        'trip_name' => $employee->name,
                        'purpose_of_visit' => $travel->purpose_of_visit,
                        'start_date' => $travel->start_date,
                        'end_date' => $travel->end_date,
                        'place_of_visit' => $travel->place_of_visit,
                        'trip_description' => $travel->description,
                    ]);
                }
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Travel successfully created.'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function show(Request $request, Travel $travel): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () {
            return redirect()->route(self::REDIRECT_INDEX);
        });
    }

    public function edit(Request $request, Travel $travel): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view = ViewsConstants::TRV . '.edit';

        return $this->measureProfile($action, function () use ($request, $travel, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'edit travel', self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($travel[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            Log::info("$action start", ['travelId' => $travel->id]);
            $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view, compact('travel', 'employees'));
        });
    }

    public function update(Request $request, Travel $travel): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $travel, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'edit travel', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::info("$action start", ['travelId' => $travel->id, 'input' => $request->all()]);
            if ($travel[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            $rules = [
                UsersConstants::COL_EMP_ID => 'required|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'purpose_of_visit' => 'required|string',
                'place_of_visit' => 'required|string'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("$action validation failed", ['message' => $msg]);
                return redirect()->back()->with('error', $msg);
            }
            try {
                DB::transaction(fn() => $travel->update([
                    UsersConstants::COL_EMP_ID => $request->employee_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'purpose_of_visit' => $request->purpose_of_visit,
                    'place_of_visit' => $request->place_of_visit,
                    'description' => $request->description
                ]));
                Log::info("$action committed", ['travelId' => $travel->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Travel successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, Travel $travel): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $travel, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'delete travel', self::REDIRECT_INDEX)) !== true) return $redirect;
            if ($travel[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            Log::info("$action start", ['travelId' => $travel->id]);
            try {
                DB::transaction(fn() => $travel->delete());
                Log::info("$action committed", ['travelId' => $travel->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Travel successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                Log::channel(SettingsConstants::ERR_TRACE)->debug("$action failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }
}
