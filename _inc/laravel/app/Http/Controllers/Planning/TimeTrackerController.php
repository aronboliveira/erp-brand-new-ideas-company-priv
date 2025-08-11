<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use App\Models\{TimeTracker, TrackPhoto};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Storage, Validator};

class TimeTrackerController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = 'time-tracker';
    private const REDIRECT_INDEX = 'time-tracker.index';

    public function index(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($resp = self::guard($request, 'manage time tracker', self::REDIRECT_INDEX)) return $resp;
        try {
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' called', ['user' => $user?->id]);
            $trackers = TimeTracker::where(DatabaseConstants::TABLE_CREATOR, $user?->id)->get();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' fetched', [
                'count' => $trackers->count(),
                'user' => $user?->id
            ]);
            return view(self::SINGULAR . '.' . __FUNCTION__, compact('trackers'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_INDEX));
        }
    }

    public function create(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' called', ['user' => $user?->id]);
        if ($resp = $this->guard(
            $request,
            'create time tracker',
            self::REDIRECT_INDEX
        )) return $resp;
        return view(self::SINGULAR . '.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(
            __CLASS__ . '::' . __FUNCTION__ . ' called',
            ['user' => $user?->id, 'input' => $request->all()]
        );
        if ($resp = $this->guard(
            $request,
            'create time tracker',
            self::REDIRECT_INDEX
        )) return $resp;
        $v = Validator::make($request->all(), [
            ProjectsConstants::COL_PJ_ID  => 'required|integer|exists:projects,id',
            ActivitiesConstants::COL_TSK_ID     => 'required|integer|exists:project_tasks,id',
            ActivitiesConstants::COL_IA   => 'required|boolean',
            'tag_id'      => 'nullable|integer|exists:tags,id',
            ProjectsConstants::COL_NM        => 'required|string|max:255',
            'is_billable' => 'required|boolean',
            ActivitiesConstants::COL_ST_TIME  => 'required|date',
            ActivitiesConstants::COL_E_TIME    => 'required|date|after_or_equal:start_time',
            ActivitiesConstants::COL_TTL_TIME  => 'nullable|integer',
        ]);
        if ($v->fails()) {
            Log::warning(
                __CLASS__ . '::' . __FUNCTION__ . ' validation failed',
                ['errors' => $v->errors()->all()]
            );
            return redirect()->back()->with(
                'error',
                $v->errors()->first()
            );
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
                Log::info(
                    __CLASS__ . '::' . __FUNCTION__ . ' created',
                    ['id' => $tracker->id]
                );
            });
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Time tracker successfully created.'));
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' failed',
                ['error' => $e->getMessage()]
            );
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function show(Request $request, TimeTracker $timeTracker): \Illuminate\View\View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(
            __CLASS__ . '::' . __FUNCTION__ . ' called',
            ['user' => $user?->id, 'tracker' => $timeTracker->id]
        );
        if ($resp = $this->guard(
            $request,
            'view time tracker',
            self::REDIRECT_INDEX
        )) return $resp;
        if ($timeTracker->created_by !== $user?->id)
            return defaultPermissionDenial(
                $request,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX),
                false
            );
        return view(self::SINGULAR . '.show', compact('timeTracker'));
    }

    public function edit(Request $request, TimeTracker $timeTracker): \Illuminate\View\View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(
            __CLASS__ . '::' . __FUNCTION__ . ' called',
            ['user' => $user?->id, 'tracker' => $timeTracker->id]
        );
        if ($resp = $this->guard(
            $request,
            'edit time tracker',
            self::REDIRECT_INDEX
        )) return $resp;
        if ($timeTracker->created_by !== $user?->id)
            return defaultPermissionDenial(
                $request,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX),
                false
            );
        return view(self::SINGULAR . '.edit', compact('timeTracker'));
    }

    public function update(Request $request, TimeTracker $timeTracker): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(
            __CLASS__ . '::' . __FUNCTION__ . ' called',
            ['user' => $user?->id, 'tracker' => $timeTracker->id, 'input' => $request->all()]
        );
        if ($resp = $this->guard(
            $request,
            'edit time tracker',
            self::REDIRECT_INDEX
        )) return $resp;
        if ($timeTracker->created_by !== $user?->id)
            return defaultPermissionDenial(
                $request,
                new \Illuminate\Auth\Access\AuthorizationException(),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX),
                false
            );
        $v = Validator::make($request->all(), [
            ProjectsConstants::COL_PJ_ID  => 'required|integer|exists:projects,id',
            ActivitiesConstants::COL_TSK_ID     => 'required|integer|exists:project_tasks,id',
            ActivitiesConstants::COL_IA   => 'required|boolean',
            'tag_id'      => 'nullable|integer|exists:tags,id',
            ProjectsConstants::COL_NM        => 'required|string|max:255',
            'is_billable' => 'required|boolean',
            ActivitiesConstants::COL_ST_TIME  => 'required|date',
            ActivitiesConstants::COL_E_TIME    => 'required|date|after_or_equal:start_time',
            ActivitiesConstants::COL_TTL_TIME  => 'nullable|integer',
        ]);
        if ($v->fails()) {
            Log::warning(
                __CLASS__ . '::' . __FUNCTION__ . ' validation failed',
                ['errors' => $v->errors()->all()]
            );
            return redirect()->back()->with(
                'error',
                $v->errors()->first()
            );
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
                Log::info(
                    __CLASS__ . '::' . __FUNCTION__ . ' updated',
                    ['id' => $timeTracker->id]
                );
            });
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Time tracker successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' failed',
                ['error' => $e->getMessage()]
            );
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(Request $request, string|int $trackerId): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) return $resp;
        try {
            return DB::transaction(fn () => $this->performDestroy($request, $user, $trackerId));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_INDEX));
        }
    }

    public function getTrackerImages(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($resp = self::guard($request, 'manage time tracker', self::REDIRECT_INDEX)) return $resp;
        try {
            $id = $request->input('id');
            $tracker = TimeTracker::findOrFail($id);
            if ($tracker->created_by !== $user?->id) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' permission denied', ['user' => $user?->id, 'tracker' => $id]);
                return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
            }
            $images = TrackPhoto::where('track_id', $id)->where('user_id', $user?->id)->get();
            return view(self::SINGULAR . '.images', compact('images', 'tracker'));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function removeTrackerImages(Request $request): JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
        if ($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
        try {
            $photo = TrackPhoto::findOrFail($request->input('id'));
            if ($photo->user_id !== auth()->id()) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' permission denied', ['photo' => $photo->id]);
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            Storage::delete($photo->img_path);
            $photo->delete();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' success', ['photo' => $photo->id]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function removeTracker(Request $request): JsonResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
        if ($resp = self::guard($request, 'delete time tracker', self::REDIRECT_INDEX)) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
        try {
            $track = TimeTracker::findOrFail($request->input('id'));
            if ($track->created_by !== auth()->id()) {
                Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' permission denied', ['track' => $track->id]);
                return response()->json(['error' => 'Permission denied.'], 403);
            }
            $track->delete();
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' success', ['track' => $track->id]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function performDestroy(Request $request, $user, string|int $trackerId): RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' deleting', ['user' => $user?->id, 'tracker' => $trackerId]);
        $tracker = TimeTracker::findOrFail($trackerId);
        if ($tracker->created_by !== $user?->id) {
            Log::warning(__CLASS__ . '::' . __FUNCTION__ . ' permission denied', ['user' => $user?->id]);
            return defaultPermissionDenial($request, new \Illuminate\Auth\Access\AuthorizationException(), __CLASS__ . '::' . __FUNCTION__, route(self::REDIRECT_INDEX));
        }
        $photos = TrackPhoto::where('track_id', $trackerId)->get();
        foreach ($photos as $photo) {
            Storage::delete($photo->img_path);
            $photo->delete();
        }
        $tracker->delete();
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' success', ['tracker' => $trackerId]);
        return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Time tracker successfully deleted.'));
    }
}
