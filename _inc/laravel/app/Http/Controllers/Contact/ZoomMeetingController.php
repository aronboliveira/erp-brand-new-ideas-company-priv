<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Models\{Project, ProjectUser, User, Utility, ZoomMeeting};
use App\Traits\{ChecksLogin, ChecksPermissions, ZoomMeetingTrait};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ZoomMeetingController extends Controller
{
    use ChecksLogin, ChecksPermissions, ZoomMeetingTrait;

    private const SINGULAR = 'zoom-meeting';
    private const REDIRECT_INDEX          = self::SINGULAR . '.index';
    private const PERM_VIEW               = 'view zoom meeting';
    private const PERM_CREATE             = 'create zoom meeting';
    private const PERM_DELETE             = 'delete zoom meeting';

    /**
     * GET /zoom-meeting
     */
    public function index(Request $request): RedirectResponse|View
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if (($redirect = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        try {
            $meetings = ZoomMeeting::when($user?->isClient(), fn ($q) => $q->where('client_id', $user?->id))
                ->unless($user?->isClient(), fn ($q) => $q->where(
                    DatabaseConstants::TABLE_CREATOR,
                    $user?->creatorId()
                ))
                ->with(ActivitiesConstants::COL_PJ_NM)->get();
            Log::info("$action loaded", ['count' => $meetings->count()]);
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(DatabaseConstants::TABLE_MEETINGS));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * GET /zoom-meeting/create
     */
    public function create(Request $request): RedirectResponse|\Illuminate\View\View
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if (($redirect = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        try {
            $creatorId = $user?->creatorId();
            $projects = Project::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(ProjectsConstants::COL_NM, 'id')->prepend('Select Project', '');
            $users    = User::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(UsersConstants::COL_NM, 'id');
            $settings = Utility::settings();
            Log::info("$action view", [
                DatabaseConstants::TABLE_PROJECTS => $projects->count(),
                DatabaseConstants::TABLE_USERS => $users->count()
            ]);
            return view(self::SINGULAR . '.' . __FUNCTION__, compact(
                DatabaseConstants::TABLE_PROJECTS,
                DatabaseConstants::TABLE_USERS,
                DatabaseConstants::TABLE_SETTINGS
            ));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * POST /zoom-meeting
     */
    public function store(Request $request): RedirectResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if (($redirect = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        $data = $request->validate([
            ActivitiesConstants::COL_TT      => 'required|string',
            ProjectsConstants::COL_S_DT  => 'required|date',
            'duration'   => 'nullable|integer',
            'password'   => 'nullable|string',
            ProjectsConstants::COL_PJ_ID  => 'nullable|integer',
            'user_ids'    => 'nullable|array',
            'client_id'   => 'nullable|integer',
            'synchronize_type' => 'nullable|string',
        ]);
        $settings = Utility::settings($user?->creatorId());
        if (
            empty($settings['zoom_account_id'])
            || empty($settings['zoom_client_id'])
            || empty($settings['zoom_client_secret'])
        ) {
            Log::warning("$action missing API keys");
            return redirect()->back()->with('error', __('Zoom API credentials not configured.'));
        }
        DB::beginTransaction();
        try {
            $zoomReq = [
                ActivitiesConstants::COL_TT => $data[ActivitiesConstants::COL_TT],
                ActivitiesConstants::COL_ST_TIME       => date('Y-m-d H:i:s', strtotime($data[ProjectsConstants::COL_S_DT])),
                'duration'         => (int)($data['duration'] ?? 0),
                'password'         => $data['password'] ?? '',
                'host_video'       => 0,
                'participant_video' => 0,
            ];
            $created = $this->createmitting($zoomReq);
            Log::info("$action zoom API response", ['response' => $created]);
            if (empty($created['success']))
                throw new \RuntimeException('Zoom API failed');
            $meeting = ZoomMeeting::create([
                ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                'meeting_id' => $created['data']['id'] ?? 0,
                ProjectsConstants::COL_PJ_ID => $data[ProjectsConstants::COL_PJ_ID] ?? 0,
                UsersConstants::COL_USER_ID    => implode(',', $data['user_ids'] ?? []),
                ProjectsConstants::COL_S_DT => $zoomReq[ActivitiesConstants::COL_ST_TIME],
                'duration'   => $zoomReq['duration'],
                'start_url'  => $created['data']['start_url'] ?? '',
                'join_url'   => $created['data']['join_url']  ?? '',
                ActivitiesConstants::COL_TSK_STT     => $created['data'][ActivitiesConstants::COL_TSK_STT]    ?? '',
                'client_id'  => $data['client_id'] ?? 0,
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);
            if (($data['synchronize_type'] ?? '') === 'google_calendar')
                Utility::addCalendarData($meeting, 'zoom_meeting');
            DB::commit();
            Log::info("$action saved meeting", ['id' => $meeting->id]);
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Zoom Meeting successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * GET /zoom-meeting/{meeting}
     */
    public function show(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse|\Illuminate\View\View
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($zoomMeeting[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
            return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
        Log::info("$action viewing", ['id' => $zoomMeeting->id]);
        return view(self::SINGULAR . '.' . __FUNCTION__, compact('zoomMeeting'));
    }

    /**
     * DELETE /zoom-meeting/{meeting}
     */
    public function destroy(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if (($redirect = self::guard($request, self::PERM_DELETE, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        if ($zoomMeeting[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                $action,
                route(self::REDIRECT_INDEX)
            );
        try {
            $zoomMeeting->delete();
            Log::info("$action deleted", ['id' => $zoomMeeting->id]);
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Meeting successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * GET /zoom-meeting/projectwiseuser/{projectId}
     */
    public function projectWiseUser(Request $request, int|string $projectId): JsonResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        if (($redirect = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX)) !== true)
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        try {
            $userIds = ProjectUser::where(ProjectsConstants::COL_PJ_ID, $projectId)->pluck(UsersConstants::COL_USER_ID);
            $users  = User::whereIn('id', $userIds)
                ->pluck(UsersConstants::COL_NM, 'id')
                ->map(fn ($name, $id) => ['id' => $id, UsersConstants::COL_NM => $name])
                ->values();
            Log::info("$action loaded", [ProjectsConstants::COL_PJ_ID => $projectId, 'count' => $users->count()]);
            return response()->json($users);
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage(), ProjectsConstants::COL_PJ_ID => $projectId]);
            return response()->json(
                ['error' => 'Could not load users'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * POST /zoom-meeting/status-update
     */
    public function statusUpdate(Request $request): JsonResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        if (self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX))
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        try {
            $meetingIds = ZoomMeeting::where(DatabaseConstants::TABLE_CREATOR, $user?->id)->pluck('meeting_id');
            foreach ($meetingIds as $meetingId) {
                $data = $this->get($meetingId);
                if (!empty($data['data'][ActivitiesConstants::COL_TSK_STT]))
                    ZoomMeeting::where('meeting_id', $meetingId)
                        ->update([ActivitiesConstants::COL_TSK_STT => $data['data'][ActivitiesConstants::COL_TSK_STT]]);
            }
            Log::info("$action completed", ['count' => count($meetingIds)]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return response()->json(
                ['error' => 'Failed to update statuses'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * GET /zoom-meeting/calendar/{task_by?}/{project_id?}
     */
    public function calendar(Request $request): RedirectResponse|\Illuminate\View\View
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if (($redirect = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX)) !== true)
            return $redirect;
        try {
            $zoomMeetings = collect();
            if (in_array(strtolower($user[UsersConstants::COL_TP]), array_map(fn ($p) => strtolower($p), [
                PermissionsConstants::CPN,
                PermissionsConstants::HR,
                PermissionsConstants::ACT
            ])))
                $zoomMeetings = ZoomMeeting::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            $events = $zoomMeetings->map(fn ($m) => [
                'id'        => $m->id,
                ActivitiesConstants::COL_TT     => $m[ActivitiesConstants::COL_TT],
                'start'     => $m[ProjectsConstants::COL_S_DT],
                'end'       => $m[ProjectsConstants::COL_E_DT],
                'className' => 'event-primary',
                'url'       => route(self::SINGULAR . '.show', $m->id),
            ]);
            Log::info("$action loaded calendar", ['count' => $events->count()]);
            return view(self::SINGULAR . '.' . __FUNCTION__, [
                'calandEr'     => $events,
                'transdate'    => now()->format('Y-m-d'),
                'zoomMeetings' => $zoomMeetings,
            ]);
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    /**
     * GET /zoom-meeting/data (for calendar JSON)
     */
    public function getZoomMeetingData(Request $request): JsonResponse
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        if (($redirect = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX)) !== true)
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        try {
            if ($request->input('calendarType') === 'google_calendar') {
                $data = Utility::getCalendarData('zoom_meeting');
                Log::info("$action google calendar", ['count' => count($data)]);
                return response()->json($data);
            }
            $meetings = ZoomMeeting::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            $payload = $meetings->map(fn ($m) => [
                'id'        => $m->id,
                ActivitiesConstants::COL_TT     => $m[ActivitiesConstants::COL_TT],
                'start'     => $m[ProjectsConstants::COL_S_DT],
                'className' => 'event-primary',
                'textColor' => '#51459d',
                'url'       => route(self::SINGULAR . '.show', $m->id),
            ])->values();
            Log::info("$action local data", ['count' => $payload->count()]);
            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return response()->json(
                ['error' => 'Failed to load data'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function edit(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse|\Illuminate\View\View
    {
        $action = __METHOD__;
        if (
            ($user = self::_checkLogin()) instanceof RedirectResponse
        ) return $user;
        if (
            $redirect = self::guard(
                $request,
                self::PERM_CREATE,
                self::REDIRECT_INDEX
            )
        ) return $redirect;
        if ($zoomMeeting[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                $action,
                route(self::REDIRECT_INDEX)
            );
        try {
            $zoomMeeting->load('project', PermissionsConstants::CL);
            $creatorId = $user?->creatorId();
            $projects = Project::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(ProjectsConstants::COL_NM, 'id')
                ->prepend('Select Project', '');
            $users    = User::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck(UsersConstants::COL_NM, 'id');
            $settings = Utility::settings();
            Log::info("$action view", ['id' => $zoomMeeting->id]);
            return view(
                self::SINGULAR . '.' . __FUNCTION__,
                compact('zoomMeeting', DatabaseConstants::TABLE_PROJECTS, DatabaseConstants::TABLE_USERS, DatabaseConstants::TABLE_SETTINGS)
            );
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function update(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse
    {
        $action = __METHOD__;
        if (
            ($user = self::_checkLogin()) instanceof RedirectResponse
        ) return $user;
        if (
            $redirect = self::guard(
                $request,
                self::PERM_CREATE,
                self::REDIRECT_INDEX
            )
        ) return $redirect;
        if ($zoomMeeting[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new AuthorizationException(),
                $action,
                route(self::REDIRECT_INDEX)
            );
        $data = $request->validate([
            ActivitiesConstants::COL_TT           => 'required|string',
            ProjectsConstants::COL_S_DT       => 'required|date',
            'duration'        => 'nullable|integer',
            'password'        => 'nullable|string',
            ProjectsConstants::COL_PJ_ID       => 'nullable|integer',
            'user_ids'         => 'nullable|array',
            'client_id'        => 'nullable|integer',
            'synchronize_type' => 'nullable|string'
        ]);
        $settings = Utility::settings($user?->creatorId());
        if (
            empty($settings['zoom_account_id'])
            || empty($settings['zoom_client_id'])
            || empty($settings['zoom_client_secret'])
        ) {
            Log::warning("$action missing API keys");
            return redirect()->back()->with(
                'error',
                __('Zoom API credentials not configured.')
            );
        }
        DB::beginTransaction();
        try {
            $zoomReq = [
                ActivitiesConstants::COL_TT            => $data[ActivitiesConstants::COL_TT],
                ActivitiesConstants::COL_ST_TIME       => date('Y-m-d H:i:s', strtotime($data[ProjectsConstants::COL_S_DT])),
                'duration'         => (int)($data['duration'] ?? 0),
                'password'         => $data['password'] ?? '',
                'host_video'       => 0,
                'participant_video' => 0
            ];
            $updated = $this->updateMeeting($zoomMeeting->meeting_id, $zoomReq);
            Log::info("$action zoom API response", ['response' => $updated]);
            if (empty($updated['success'])) throw new \RuntimeException('Zoom API failed');
            $zoomMeeting->fill([
                ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                ProjectsConstants::COL_PJ_ID => $data[ProjectsConstants::COL_PJ_ID] ?? 0,
                UsersConstants::COL_USER_ID    => implode(',', $data['user_ids'] ?? []),
                ProjectsConstants::COL_S_DT => $zoomReq[ActivitiesConstants::COL_ST_TIME],
                'duration'   => $zoomReq['duration'],
                'password'   => $zoomReq['password'],
                'start_url'  => $updated['data']['start_url'] ?? '',
                'join_url'   => $updated['data']['join_url']  ?? '',
                ActivitiesConstants::COL_TSK_STT     => $updated['data'][ActivitiesConstants::COL_TSK_STT]    ?? '',
                'client_id'  => $data['client_id'] ?? 0
            ])->save();
            if (($data['synchronize_type'] ?? '') === 'google_calendar')
                Utility::addCalendarData($zoomMeeting, 'zoom_meeting');
            DB::commit();
            Log::info("$action saved", ['id' => $zoomMeeting->id]);
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Zoom Meeting successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
