<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{
    Branch,
    Employee,
    GoalTracking,
    GoalType
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Log,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
final class GoalTrackingController extends Controller
{
    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;
    private const REDIRECT_INDEX = '/';

    public function index(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TRC . '.index';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($resp = self::guard($request, PermissionsConstants::MNG_GTR, self::REDIRECT_INDEX)) !== true) return $resp;
                $user = $userOrRedirect;
                $goalTrackings = strtolower($user[UsersConstants::COL_TP]) === 'employee'
                    ? GoalTracking::with(['goalType', 'branch'])
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where('branch', Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value(CompaniesConstants::COL_BRC_ID))
                    ->get()
                    : GoalTracking::with(['goalType', 'branch'])
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->get();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                return view($view, compact('goalTrackings'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function create(Request $request): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TRC . '.create';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($resp = self::guard($request, 'create goal tracking', self::REDIRECT_INDEX)) !== true) return $resp;
                $user = $userOrRedirect;
                $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Branch', '');
                $goalTypes = GoalType::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Goal Type', '');
                $status = GoalTracking::$status;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                return view($view, compact('branches', 'goalTypes', 'status'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($resp = self::guard($request, 'create goal tracking', self::REDIRECT_INDEX)) !== true) return $resp;
                if ($err = self::validateInput($request, [
                    'branch'     => 'required',
                    'goal_type'  => 'required',
                    'start_date' => 'required|date',
                    'end_date'   => 'required|date|after_or_equal:start_date',
                    'subject'    => 'required',
                ])) return $err;
                $user = $userOrRedirect;

                GoalTracking::create([
                    'branch'             => $request->branch,
                    'goal_type'          => $request->goal_type,
                    'start_date'         => $request->start_date,
                    'end_date'           => $request->end_date,
                    'subject'            => $request->subject,
                    'target_achievement' => $request->target_achievement,
                    'description'        => $request->description,
                    DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);

                return redirect()->route(ViewsConstants::GL_TRC . '.index')
                    ->with('success', __('Goal tracking successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function show(Request $request, GoalTracking $goalTracking): RedirectResponse|View|null
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TRC . '.show';

        return $this->measureProfile($action, function () use ($request, $goalTracking, $view, $action) {
            Log::info("$action started", [UsersConstants::COL_USER_ID => $request->user()->id, 'goal_tracking_id' => $goalTracking->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($request, 'view goal tracking', self::REDIRECT_INDEX)) !== true) {
                Log::warning("$action permission denied", [UsersConstants::COL_USER_ID => $user?->id]);
                return $resp;
            }
            if ($goalTracking[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("$action ownership denied", [UsersConstants::COL_USER_ID => $user?->id, 'goal_tracking_id' => $goalTracking->id]);
                return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX));
            }
            try {
                Log::info("$action rendering view", ['goal_tracking_id' => $goalTracking->id]);
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                return view($view, compact('goalTracking'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage(), 'goal_tracking_id' => $goalTracking->id]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, int|string $id): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::GL_TRC . '.edit';

        return $this->measureProfile($action, function () use ($request, $id, $view, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($resp = self::guard($request, 'edit goal tracking', self::REDIRECT_INDEX)) !== true) return $resp;
                $user = $userOrRedirect;
                $goalTracking = GoalTracking::findOrFail($id);
                if ($goalTracking[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $action);
                $branches = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend('Select Branch', '');
                $goalTypes = GoalType::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->pluck('name', 'id')->prepend('Select Goal Type', '');
                $status = GoalTracking::$status;
                $ratings = json_decode($goalTracking->rating, true);
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action);
                return view($view, compact('branches', 'goalTypes', 'goalTracking', 'ratings', 'status'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($resp = self::guard($request, 'edit goal tracking', self::REDIRECT_INDEX)) !== true) return $resp;
                if ($err = self::validateInput($request, [
                    'branch'     => 'required',
                    'goal_type'  => 'required',
                    'start_date' => 'required|date',
                    'end_date'   => 'required|date|after_or_equal:start_date',
                    'subject'    => 'required',
                ])) return $err;
                $user = $userOrRedirect;

                $gt = GoalTracking::findOrFail($id);
                if ($gt[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $action);

                $gt->fill([
                    'branch'             => $request->branch,
                    'goal_type'          => $request->goal_type,
                    'start_date'         => $request->start_date,
                    'end_date'           => $request->end_date,
                    'subject'            => $request->subject,
                    'target_achievement' => $request->target_achievement,
                    'status'             => $request->status,
                    'progress'           => $request->progress,
                    'description'        => $request->description,
                    'rating'             => json_encode($request->rating),
                ])->save();

                return redirect()->route(ViewsConstants::GL_TRC . '.index')
                    ->with('success', __('Goal tracking successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($resp = self::guard($request, 'delete goal tracking', self::REDIRECT_INDEX)) !== true) return $resp;
                $user = $userOrRedirect;

                $gt = GoalTracking::findOrFail($id);
                if ($gt[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId())
                    return defaultPermissionDenial($request, new AuthorizationException(), $action);

                $gt->delete();
                return redirect()->route(ViewsConstants::GL_TRC . '.index')
                    ->with('success', __('Goal tracking successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    private static function validateInput(Request $request, array $rules): ?RedirectResponse
    {
        $v = Validator::make($request->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->errors()->first())
            : null;
    }
}
