<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Models\{Project, ProjectUser, User, Utility, ZoomMeeting};
use App\Traits\{ChecksLogin, ChecksPermissions, ZoomMeetingTrait};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, View as ViewFacade};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class ZoomMeetingController extends Controller
{
    use ChecksLogin, ChecksPermissions, ZoomMeetingTrait;

    private const SINGULAR = 'zoom-meeting';
    private const REDIRECT_INDEX          = self::SINGULAR . '.index';
    private const PERM_VIEW               = 'view zoom meeting';
    private const PERM_CREATE             = 'create zoom meeting';
    private const PERM_DELETE             = 'delete zoom meeting';

    public function index(Request $request): RedirectResponse|View
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action, $function) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            try {
                $t = microtime(true);
                $meetings = ZoomMeeting::when($user?->isClient(), fn($q) => $q->where('client_id', $user?->id))
                    ->unless($user?->isClient(), fn($q) => $q->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()))
                    ->with(ActivitiesConstants::COL_PJ_NM)
                    ->get();
                $this->logExecutionTime($t, $action . '::loadMeetings', 'completed');

                $view = VW::ZMM . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                Log::info("$action loaded", ['count' => $meetings->count()]);
                return view($view, compact(DatabaseConstants::TABLE_MEETINGS));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function create(Request $request): RedirectResponse|View
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action, $function) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            try {
                $t = microtime(true);
                $creatorId = $user?->creatorId();
                $projects  = Project::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck(ProjectsConstants::COL_NM, 'id')->prepend('Select Project', '');
                $users     = User::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck(UsersConstants::COL_NM, 'id');
                $settings  = Utility::settings();
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');

                $view = VW::ZMM . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                Log::info("$action view", [
                    DatabaseConstants::TABLE_PROJECTS => $projects->count(),
                    DatabaseConstants::TABLE_USERS => $users->count()
                ]);
                return view($view, compact(
                    DatabaseConstants::TABLE_PROJECTS,
                    DatabaseConstants::TABLE_USERS,
                    DatabaseConstants::TABLE_SETTINGS
                ));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            $t = microtime(true);
            $data = $request->validate([
                ActivitiesConstants::COL_TT      => 'required|string',
                ProjectsConstants::COL_S_DT      => 'required|date',
                'duration'                       => 'nullable|integer',
                'password'                       => 'nullable|string',
                ProjectsConstants::COL_PJ_ID     => 'nullable|integer',
                'user_ids'                       => 'nullable|array',
                'client_id'                      => 'nullable|integer',
                'synchronize_type'               => 'nullable|string',
            ]);
            $this->logExecutionTime($t, $action . '::validate', 'completed');

            $t = microtime(true);
            $settings = Utility::settingsById($user?->creatorId());
            $this->logExecutionTime($t, $action . '::loadSettings', 'completed');
            if (empty($settings['zoom_account_id']) || empty($settings['zoom_client_id']) || empty($settings['zoom_client_secret'])) {
                Log::warning("$action missing API keys");
                return redirect()->back()->with('error', __('Zoom API credentials not configured.'));
            }

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $zoomReq = [
                    ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                    ActivitiesConstants::COL_ST_TIME => date('Y-m-d H:i:s', strtotime($data[ProjectsConstants::COL_S_DT])),
                    'duration'                        => (int)($data['duration'] ?? 0),
                    'password'                        => $data['password'] ?? '',
                    'host_video'                      => 0,
                    'participant_video'               => 0,
                ];
                $created = $this->createMeeting($zoomReq);
                $this->logExecutionTime($t, $action . '::zoomCreate', 'completed');

                Log::info("$action zoom API response", ['response' => $created]);
                if (empty($created['success'])) throw new \RuntimeException('Zoom API failed');

                $t = microtime(true);
                $meeting = ZoomMeeting::create([
                    ActivitiesConstants::COL_TT          => $data[ActivitiesConstants::COL_TT],
                    'meeting_id'                          => $created['data']['id'] ?? 0,
                    ProjectsConstants::COL_PJ_ID          => $data[ProjectsConstants::COL_PJ_ID] ?? 0,
                    UsersConstants::COL_USER_ID           => implode(',', $data['user_ids'] ?? []),
                    ProjectsConstants::COL_S_DT           => $zoomReq[ActivitiesConstants::COL_ST_TIME],
                    'duration'                            => $zoomReq['duration'],
                    'start_url'                           => $created['data']['start_url'] ?? '',
                    'join_url'                            => $created['data']['join_url']  ?? '',
                    ActivitiesConstants::COL_TSK_STT      => $created['data'][ActivitiesConstants::COL_TSK_STT] ?? '',
                    'client_id'                           => $data['client_id'] ?? 0,
                    DatabaseConstants::COL_TABLE_CREATOR      => $user?->creatorId(),
                ]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                if (($data['synchronize_type'] ?? '') === 'google_calendar') {
                    $t = microtime(true);
                    Utility::addCalendarData($meeting, 'zoom_meeting');
                    $this->logExecutionTime($t, $action . '::syncCalendar', 'completed');
                }

                DB::commit();
                Log::info("$action saved meeting", ['id' => $meeting->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Zoom Meeting successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    public function show(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse|View
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $zoomMeeting, $action, $function) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $ownerOk = $zoomMeeting[DatabaseConstants::COL_TABLE_CREATOR] === $user?->creatorId();
            $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
            if (!$ownerOk) {
                return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX));
            }

            try {
                $view = VW::ZMM . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                Log::info("$action viewing", ['id' => $zoomMeeting->id]);
                return view($view, compact('zoomMeeting'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['meeting_id' => $zoomMeeting->id]);
    }

    public function destroy(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $zoomMeeting, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_DELETE, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            $t = microtime(true);
            $ownerOk = $zoomMeeting[DatabaseConstants::COL_TABLE_CREATOR] === $user?->creatorId();
            $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
            if (!$ownerOk) {
                return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX));
            }

            try {
                $t = microtime(true);
                $zoomMeeting->delete();
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                Log::info("$action deleted", ['id' => $zoomMeeting->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Meeting successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['meeting_id' => $zoomMeeting->id, 'uri' => $request->getRequestUri()]);
    }

    public const PRJ_W_USR = 'projectWiseUser';
    public function projectWiseUser(Request $request, int|string $projectId): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $projectId, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json([], Response::HTTP_UNAUTHORIZED);

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return response()->json([], Response::HTTP_UNAUTHORIZED);

            try {
                $t = microtime(true);
                $userIds = ProjectUser::where(ProjectsConstants::COL_PJ_ID, $projectId)->pluck(UsersConstants::COL_USER_ID);
                $users   = User::whereIn('id', $userIds)
                    ->pluck(UsersConstants::COL_NM, 'id')
                    ->map(fn($name, $id) => ['id' => $id, UsersConstants::COL_NM => $name])
                    ->values();
                $this->logExecutionTime($t, $action . '::query', 'completed');

                Log::info("$action loaded", [ProjectsConstants::COL_PJ_ID => $projectId, 'count' => $users->count()]);
                return response()->json($users);
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage(), ProjectsConstants::COL_PJ_ID => $projectId]);
                return response()->json(['error' => 'Could not load users'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }, ['project_id' => $projectId]);
    }

    public const STT_UPD = 'statusUpdate';
    public function statusUpdate(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);

            try {
                $t = microtime(true);
                $meetingIds = ZoomMeeting::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('meeting_id');
                foreach ($meetingIds as $meetingId) {
                    $data = $this->get($meetingId);
                    $status = $data['data'][ActivitiesConstants::COL_TSK_STT] ?? null;
                    if (!empty($status)) {
                        ZoomMeeting::where('meeting_id', $meetingId)
                            ->update([ActivitiesConstants::COL_TSK_STT => $status]);
                    }
                }
                $this->logExecutionTime($t, $action . '::updateStatuses', 'completed');

                Log::info("$action completed", ['count' => $meetingIds->count()]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Failed to update statuses'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    public function calendar(Request $request): RedirectResponse|View
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action, $function) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            try {
                $t = microtime(true);
                $zoomMeetings = collect();
                if (in_array(strtolower($user[UsersConstants::COL_TP]), array_map(fn($p) => strtolower($p), [
                    PermissionsConstants::CPN,
                    PermissionsConstants::HR,
                    PermissionsConstants::ACT
                ]), true)) {
                    $zoomMeetings = ZoomMeeting::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                }
                $events = $zoomMeetings->map(fn($m) => [
                    'id'        => $m->id,
                    ActivitiesConstants::COL_TT => $m[ActivitiesConstants::COL_TT],
                    'start'     => $m[ProjectsConstants::COL_S_DT],
                    'end'       => $m[ProjectsConstants::COL_E_DT],
                    'className' => 'event-primary',
                    'url'       => route(self::SINGULAR . '.show', $m->id),
                ]);
                $this->logExecutionTime($t, $action . '::composeEvents', 'completed');

                $view = VW::ZMM . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                Log::info("$action loaded calendar", ['count' => $events->count()]);
                return view($view, [
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
        }, ['uri' => $request->getRequestUri()]);
    }

    public const GET_ZMM_D = 'getZoomMeetingData';
    public function getZoomMeetingData(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse)
                return response()->json([], Response::HTTP_UNAUTHORIZED);

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_VIEW, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return response()->json([], Response::HTTP_UNAUTHORIZED);

            try {
                if ($request->input('calendarType') === 'google_calendar') {
                    $t = microtime(true);
                    $data = Utility::getCalendarData('zoom_meeting');
                    $this->logExecutionTime($t, $action . '::googleCalendar', 'completed');

                    Log::info("$action google calendar", ['count' => count($data)]);
                    return response()->json($data);
                }

                $t = microtime(true);
                $meetings = ZoomMeeting::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $payload  = $meetings->map(fn($m) => [
                    'id'        => $m->id,
                    ActivitiesConstants::COL_TT => $m[ActivitiesConstants::COL_TT],
                    'start'     => $m[ProjectsConstants::COL_S_DT],
                    'className' => 'event-primary',
                    'textColor' => '#51459d',
                    'url'       => route(self::SINGULAR . '.show', $m->id),
                ])->values();
                $this->logExecutionTime($t, $action . '::localData', 'completed');

                Log::info("$action local data", ['count' => $payload->count()]);
                return response()->json($payload);
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return response()->json(
                    ['error' => 'Failed to load data'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    public function edit(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse|View
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        $function = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $zoomMeeting, $action, $function) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            $t = microtime(true);
            $ownerOk = $zoomMeeting[DatabaseConstants::COL_TABLE_CREATOR] === $user?->creatorId();
            $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
            if (!$ownerOk) {
                return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX));
            }

            try {
                $t = microtime(true);
                $zoomMeeting->load('project', PermissionsConstants::CL);
                $creatorId = $user?->creatorId();
                $projects  = Project::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck(ProjectsConstants::COL_NM, 'id')->prepend('Select Project', '');
                $users     = User::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck(UsersConstants::COL_NM, 'id');
                $settings  = Utility::settings();
                $this->logExecutionTime($t, $action . '::loadFormData', 'completed');

                $view = VW::ZMM . '.' . $function;
                $t = microtime(true);
                if (!ViewFacade::exists($view)) {
                    $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
                    throw new \RuntimeException("View [$view] not found");
                }
                $this->logExecutionTime($t, $action . '::viewCheck', 'ok');

                Log::info("$action view", ['id' => $zoomMeeting->id]);
                return view($view, compact(
                    'zoomMeeting',
                    DatabaseConstants::TABLE_PROJECTS,
                    DatabaseConstants::TABLE_USERS,
                    DatabaseConstants::TABLE_SETTINGS
                ));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException(
                    $request,
                    $e,
                    $action,
                    route(self::REDIRECT_INDEX)
                );
            }
        }, ['meeting_id' => $zoomMeeting->id]);
    }

    public function update(Request $request, ZoomMeeting $zoomMeeting): RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $zoomMeeting, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;

            $t = microtime(true);
            $guard = self::guard($request, self::PERM_CREATE, self::REDIRECT_INDEX);
            $this->logExecutionTime($t, $action . '::guard', is_bool($guard) && $guard === true ? 'ok' : 'redirect');
            if ($guard !== true) return $guard;

            $t = microtime(true);
            $ownerOk = $zoomMeeting[DatabaseConstants::COL_TABLE_CREATOR] === $user?->creatorId();
            $this->logExecutionTime($t, $action . '::authorizeOwner', $ownerOk ? 'ok' : 'denied');
            if (!$ownerOk) {
                return defaultPermissionDenial($request, new AuthorizationException(), $action, route(self::REDIRECT_INDEX));
            }

            $t = microtime(true);
            $data = $request->validate([
                ActivitiesConstants::COL_TT           => 'required|string',
                ProjectsConstants::COL_S_DT           => 'required|date',
                'duration'                            => 'nullable|integer',
                'password'                            => 'nullable|string',
                ProjectsConstants::COL_PJ_ID          => 'nullable|integer',
                'user_ids'                            => 'nullable|array',
                'client_id'                           => 'nullable|integer',
                'synchronize_type'                    => 'nullable|string'
            ]);
            $this->logExecutionTime($t, $action . '::validate', 'completed');

            $t = microtime(true);
            $settings = Utility::settingsById($user?->creatorId());
            $this->logExecutionTime($t, $action . '::loadSettings', 'completed');
            if (empty($settings['zoom_account_id']) || empty($settings['zoom_client_id']) || empty($settings['zoom_client_secret'])) {
                Log::warning("$action missing API keys");
                return redirect()->back()->with('error', __('Zoom API credentials not configured.'));
            }

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $zoomReq = [
                    ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                    ActivitiesConstants::COL_ST_TIME => date('Y-m-d H:i:s', strtotime($data[ProjectsConstants::COL_S_DT])),
                    'duration'                        => (int)($data['duration'] ?? 0),
                    'password'                        => $data['password'] ?? '',
                    'host_video'                      => 0,
                    'participant_video'               => 0,
                ];
                $updated = $this->updateMeeting($zoomMeeting->meeting_id, $zoomReq);
                $this->logExecutionTime($t, $action . '::zoomUpdate', 'completed');

                Log::info("$action zoom API response", ['response' => $updated]);
                if (empty($updated['success'])) throw new \RuntimeException('Zoom API failed');

                $t = microtime(true);
                $zoomMeeting->fill([
                    ActivitiesConstants::COL_TT      => $data[ActivitiesConstants::COL_TT],
                    ProjectsConstants::COL_PJ_ID     => $data[ProjectsConstants::COL_PJ_ID] ?? 0,
                    UsersConstants::COL_USER_ID      => implode(',', $data['user_ids'] ?? []),
                    ProjectsConstants::COL_S_DT      => $zoomReq[ActivitiesConstants::COL_ST_TIME],
                    'duration'                        => $zoomReq['duration'],
                    'password'                        => $zoomReq['password'],
                    'start_url'                       => $updated['data']['start_url'] ?? '',
                    'join_url'                        => $updated['data']['join_url']  ?? '',
                    ActivitiesConstants::COL_TSK_STT  => $updated['data'][ActivitiesConstants::COL_TSK_STT] ?? '',
                    'client_id'                       => $data['client_id'] ?? 0,
                ])->save();
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                if (($data['synchronize_type'] ?? '') === 'google_calendar') {
                    $t = microtime(true);
                    Utility::addCalendarData($zoomMeeting, 'zoom_meeting');
                    $this->logExecutionTime($t, $action . '::syncCalendar', 'completed');
                }

                DB::commit();
                Log::info("$action saved", ['id' => $zoomMeeting->id]);
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Zoom Meeting successfully updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$action failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['meeting_id' => $zoomMeeting->id, 'uri' => $request->getRequestUri()]);
    }
}
