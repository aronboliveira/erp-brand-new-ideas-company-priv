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
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Storage, Validator, View as ViewFacade};

class TimeTrackerController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'time-tracker';
    private const REDIRECT_INDEX = VW::TMT . '.index';

    public function index(Request $request): \Illuminate\View\View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = self::guard($request, 'manage time tracker', self::REDIRECT_INDEX)) return $resp;
            try {
                Log::info(__CLASS__ . '::index called', ['user' => $user?->id]);
                $trackers = TimeTracker::where(DatabaseConstants::TABLE_CREATOR, $user?->id)->get();
                Log::info(__CLASS__ . '::index fetched', ['count' => $trackers->count(), 'user' => $user?->id]);
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                return view($view, compact('trackers'));
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::index unexpected', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __CLASS__ . '::index', route(self::REDIRECT_INDEX));
            }
        });
    }

    public function create(Request $request): \Illuminate\View\View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info(__CLASS__ . '::create called', ['user' => $user?->id]);
            if ($resp = $this->guard($request, 'create time tracker', self::REDIRECT_INDEX)) return $resp;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view);
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info(__CLASS__ . '::store called', ['user' => $user?->id, 'input' => $request->all()]);
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
                Log::warning(__CLASS__ . '::store validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            try {
                DB::transaction(function () use ($request, $user) {
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
                    $data[DatabaseConstants::TABLE_CREATOR] = $user?->id;
                    $tracker = TimeTracker::create($data);
                    Log::info(__CLASS__ . '::store created', ['id' => $tracker->id]);
                });
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Time tracker successfully created.'));
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::store failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __CLASS__ . '::store', route(self::REDIRECT_INDEX));
            }
        });
    }

    public function show(Request $request, TimeTracker $timeTracker): \Illuminate\View\View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.show';

        return $this->measureProfile($action, function () use ($request, $timeTracker, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info(__CLASS__ . '::show called', ['user' => $user?->id, 'tracker' => $timeTracker->id]);
            if ($resp = $this->guard($request, 'view time tracker', self::REDIRECT_INDEX)) return $resp;
            if ($timeTracker->created_by !== $user?->id) return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), __CLASS__ . '::show', route(self::REDIRECT_INDEX), false);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view, compact('timeTracker'));
        });
    }

    public function edit(Request $request, TimeTracker $timeTracker): \Illuminate\View\View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.edit';

        return $this->measureProfile($action, function () use ($request, $timeTracker, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info(__CLASS__ . '::edit called', ['user' => $user?->id, 'tracker' => $timeTracker->id]);
            if ($resp = $this->guard($request, 'edit time tracker', self::REDIRECT_INDEX)) return $resp;
            if ($timeTracker->created_by !== $user?->id) return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), __CLASS__ . '::edit', route(self::REDIRECT_INDEX), false);
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            return view($view, compact('timeTracker'));
        });
    }

    public function update(Request $request, TimeTracker $timeTracker): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $timeTracker) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::info(__CLASS__ . '::update called', ['user' => $user?->id, 'tracker' => $timeTracker->id, 'input' => $request->all()]);
            if ($resp = $this->guard($request, 'edit time tracker', self::REDIRECT_INDEX)) return $resp;
            if ($timeTracker->created_by !== $user?->id) return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), __CLASS__ . '::update', route(self::REDIRECT_INDEX), false);
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
                Log::warning(__CLASS__ . '::update validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }
            try {
                DB::transaction(function () use ($request, $timeTracker) {
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
                    Log::info(__CLASS__ . '::update updated', ['id' => $timeTracker->id]);
                });
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Time tracker successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::update failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __CLASS__ . '::update', route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, string|int $trackerId): RedirectResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $trackerId) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) return $resp;
            try {
                return DB::transaction(fn() => $this->performDestroy($request, $user, $trackerId));
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::destroy failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __CLASS__ . '::destroy', route(self::REDIRECT_INDEX));
            }
        });
    }

    public const GET_TRT_IMG = 'getTrackerImages';
    public function getTrackerImages(Request $request): \Illuminate\View\View|RedirectResponse
    {
        $action = __METHOD__;
        $view = VW::TMT . '.images';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($resp = self::guard($request, 'manage time tracker', self::REDIRECT_INDEX)) return $resp;
            try {
                $id = $request->input('id');
                $tracker = TimeTracker::findOrFail($id);
                if ($tracker->created_by !== $user?->id) return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), __CLASS__ . '::getTrackerImages');
                $images = TrackPhoto::where('track_id', $id)->where('user_id', $user?->id)->get();
                if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                return view($view, compact('images', 'tracker'));
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::getTrackerImages unexpected', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __CLASS__ . '::getTrackerImages');
            }
        });
    }

    public const RM_TRT_IMG = 'removeTrackerImages';
    public function removeTrackerImages(Request $request): JsonResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            if ($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            try {
                $photo = TrackPhoto::findOrFail($request->input('id'));
                if ($photo->user_id !== auth()->id()) {
                    Log::warning(__CLASS__ . '::removeTrackerImages permission denied', ['photo' => $photo->id]);
                    return response()->json(['error' => 'Permission denied.'], 403);
                }
                Storage::delete($photo->img_path);
                $photo->delete();
                Log::info(__CLASS__ . '::removeTrackerImages success', ['photo' => $photo->id]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::removeTrackerImages failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], 500);
            }
        });
    }

    public const RM_TRT = 'removeTracker';
    public function removeTracker(Request $request): JsonResponse
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            if ($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            try {
                $track = TimeTracker::findOrFail($request->input('id'));
                if ($track->created_by !== auth()->id()) {
                    Log::warning(__CLASS__ . '::removeTracker permission denied', ['track' => $track->id]);
                    return response()->json(['error' => 'Permission denied.'], 403);
                }
                $track->delete();
                Log::info(__CLASS__ . '::removeTracker success', ['track' => $track->id]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::removeTracker failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], 500);
            }
        });
    }

    private function performDestroy(Request $request, $user, string|int $trackerId): RedirectResponse
    {
        Log::info(__CLASS__ . '::performDestroy deleting', ['user' => $user?->id, 'tracker' => $trackerId]);
        $tracker = TimeTracker::findOrFail($trackerId);
        if ($tracker->created_by !== $user?->id) {
            Log::warning(__CLASS__ . '::performDestroy permission denied', ['user' => $user?->id]);
            return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), __CLASS__ . '::performDestroy', route(self::REDIRECT_INDEX));
        }
        $photos = TrackPhoto::where('track_id', $trackerId)->get();
        foreach ($photos as $photo) {
            Storage::delete($photo->img_path);
            $photo->delete();
        }
        $tracker->delete();
        Log::info(__CLASS__ . '::performDestroy success', ['tracker' => $trackerId]);
        return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Time tracker successfully deleted.'));
    }
}
