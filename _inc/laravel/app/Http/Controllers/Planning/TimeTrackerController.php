<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants,
    ViewsConstants as VW
};
use App\Models\{TimeTracker, TrackPhoto};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Storage, Validator, View as ViewFacade};
use Illuminate\View\View;

class TimeTrackerController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'time-tracker';
    private const REDIRECT_INDEX = VW::TMT . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($request, 'manage time tracker', self::REDIRECT_INDEX)) !== true) return $resp;
            try {
                Log::info($action . ' called', ['user' => $user?->id]);
                $trackers = TimeTracker::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->id)->get();
                Log::info($action . ' fetched', ['count' => $trackers->count(), 'user' => $user?->id]);
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                return view($view, compact('trackers'));
            } catch (\Throwable $e) {
                Log::error($action . ' unexpected', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action . '', route(self::REDIRECT_INDEX));
            }
        });
    }

    public function create(Request $request): View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info($action . ' called', ['user' => $user?->id]);
            if ($resp = $this->guard($request, 'create time tracker', self::REDIRECT_INDEX)) return $resp;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view);
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info($action . ' called', ['user' => $user?->id, 'input' => $request->all()]);
            if ($resp = $this->guard($request, 'create time tracker', self::REDIRECT_INDEX)) return $resp;
            $v = Validator::make($request->all(), [
                ProjectsConstants::COL_PJ_ID      => 'required|integer|exists:projects,id',
                ActivitiesConstants::COL_TSK_ID   => 'required|integer|exists:project_tasks,id',
                ActivitiesConstants::COL_IA       => 'required|boolean',
                'tag_id'                          => 'nullable|integer|exists:tags,id',
                ProjectsConstants::COL_NM         => 'required|string|max:255',
                'is_billable'                     => 'required|boolean',
                ActivitiesConstants::COL_ST_TIME  => 'required|date',
                ActivitiesConstants::COL_E_TIME   => 'required|date|after_or_equal:start_time',
                ActivitiesConstants::COL_TTL_TIME => 'nullable|integer',
            ]);
            if ($v->fails()) {
                Log::warning($action . ' validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            try {
                DB::transaction(function () use ($request, $user, $action) {
                    $data = $request->only([
                        ProjectsConstants::COL_PJ_ID,
                        ActivitiesConstants::COL_TSK_ID,
                        ActivitiesConstants::COL_IA,
                        'tag_id',
                        ProjectsConstants::COL_NM,
                        'is_billable',
                        ActivitiesConstants::COL_ST_TIME,
                        ActivitiesConstants::COL_E_TIME,
                        ActivitiesConstants::COL_TTL_TIME,
                    ]);
                    $data[DatabaseConstants::COL_TABLE_CREATOR] = $user?->id;
                    $tracker = TimeTracker::create($data);
                    Log::info($action . ' created', ['id' => $tracker->id]);
                });
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Time tracker successfully created.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function show(Request $request, TimeTracker $timeTracker): View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.show';

        return $this->measureProfile($action, function () use ($request, $timeTracker, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info($action . ' called', ['user' => $user?->id, 'tracker' => $timeTracker->id]);
            if ($resp = $this->guard($request, 'view time tracker', self::REDIRECT_INDEX)) return $resp;
            if ($timeTracker[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->id) return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX), false);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view, compact('timeTracker'));
        });
    }

    public function edit(Request $request, TimeTracker $timeTracker): View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.edit';

        return $this->measureProfile($action, function () use ($request, $timeTracker, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info($action . ' called', ['user' => $user?->id, 'tracker' => $timeTracker->id]);
            if ($resp = $this->guard($request, 'edit time tracker', self::REDIRECT_INDEX)) return $resp;
            if ($timeTracker[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->id) return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX), false);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view, compact('timeTracker'));
        });
    }

    public function update(Request $request, TimeTracker $timeTracker): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $timeTracker, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info($action . ' called', ['user' => $user?->id, 'tracker' => $timeTracker->id, 'input' => $request->all()]);
            if ($resp = $this->guard($request, 'edit time tracker', self::REDIRECT_INDEX)) return $resp;
            if ($timeTracker[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->id) return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX), false);
            $v = Validator::make($request->all(), [
                ProjectsConstants::COL_PJ_ID      => 'required|integer|exists:projects,id',
                ActivitiesConstants::COL_TSK_ID   => 'required|integer|exists:project_tasks,id',
                ActivitiesConstants::COL_IA       => 'required|boolean',
                'tag_id'                          => 'nullable|integer|exists:tags,id',
                ProjectsConstants::COL_NM         => 'required|string|max:255',
                'is_billable'                     => 'required|boolean',
                ActivitiesConstants::COL_ST_TIME  => 'required|date',
                ActivitiesConstants::COL_E_TIME   => 'required|date|after_or_equal:start_time',
                ActivitiesConstants::COL_TTL_TIME => 'nullable|integer',
            ]);
            if ($v->fails()) {
                Log::warning($action . ' validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            try {
                DB::transaction(function () use ($request, $timeTracker, $action) {
                    $data = $request->only([
                        ProjectsConstants::COL_PJ_ID,
                        ActivitiesConstants::COL_TSK_ID,
                        ActivitiesConstants::COL_IA,
                        'tag_id',
                        ProjectsConstants::COL_NM,
                        'is_billable',
                        ActivitiesConstants::COL_ST_TIME,
                        ActivitiesConstants::COL_E_TIME,
                        ActivitiesConstants::COL_TTL_TIME,
                    ]);
                    $timeTracker->update($data);
                    Log::info($action . ' updated', ['id' => $timeTracker->id]);
                });
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Time tracker successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, string|int $trackerId): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $trackerId, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) !== true) return $resp;
            try {
                return DB::transaction(fn() => $this->performDestroy($request, $user, $trackerId));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public const GET_TRT_IMG = 'getTrackerImages';
    public function getTrackerImages(Request $request): View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.images';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($resp = self::guard($request, 'manage time tracker', self::REDIRECT_INDEX)) !== true) return $resp;
            try {
                $id = $request->input('id');
                $tracker = TimeTracker::findOrFail($id);
                if ($tracker[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->id) return defaultPermissionDenial($request, new AuthorizationException(), $action);
                $images = TrackPhoto::where('track_id', $id)->where('user_id', $user?->id)->get();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                return view($view, compact('images', 'tracker'));
            } catch (\Throwable $e) {
                Log::error($action . ' unexpected', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public const RM_TRT_IMG = 'removeTrackerImages';
    public function removeTrackerImages(Request $request): JsonResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            if (($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) !== true) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            try {
                $photo = TrackPhoto::findOrFail($request->input('id'));
                if ($photo->user_id !== auth()->id()) {
                    Log::warning($action . ' permission denied', ['photo' => $photo->id]);
                    return response()->json(['error' => 'Permission denied.'], 403);
                }
                Storage::delete($photo->img_path);
                $photo->delete();
                Log::info($action . ' success', ['photo' => $photo->id]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], 500);
            }
        });
    }

    public const RM_TRT = 'removeTracker';
    public function removeTracker(Request $request): JsonResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            if (($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) !== true) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            try {
                $track = TimeTracker::findOrFail($request->input('id'));
                if ($track[DatabaseConstants::COL_TABLE_CREATOR] !== auth()->id()) {
                    Log::warning($action . ' permission denied', ['track' => $track->id]);
                    return response()->json(['error' => 'Permission denied.'], 403);
                }
                $track->delete();
                Log::info($action . ' success', ['track' => $track->id]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], 500);
            }
        });
    }

    private function performDestroy(Request $request, $user, string|int $trackerId): RedirectResponse
    {
        $action = __METHOD__;
        Log::info($action . ' deleting', ['user' => $user?->id, 'tracker' => $trackerId]);
        $tracker = TimeTracker::findOrFail($trackerId);
        if ($tracker[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->id) {
            Log::warning($action . ' permission denied', ['user' => $user?->id]);
            return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX));
        }
        $photos = TrackPhoto::where('track_id', $trackerId)->get();
        foreach ($photos as $photo) {
            Storage::delete($photo->img_path);
            $photo->delete();
        }
        $tracker->delete();
        Log::info($action . ' success', ['tracker' => $trackerId]);
        return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Time tracker successfully deleted.'));
    }
}
