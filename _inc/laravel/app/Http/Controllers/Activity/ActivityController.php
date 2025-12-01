<?php

namespace App\Http\Controllers;

require __DIR__ . '/errorHandlers.php';
require __DIR__ . '/http.php';

use App\Config\Constants\{
  ActivitiesConstants,
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  ProjectsConstants,
  ServicesConstants,
  UsersConstants,
};
use App\Models\{
  Activity,
  ActivityLog,
  Email,
  Note,
  Schedule,
  Task
};
use App\Traits\{ChecksPermissions, ChecksLogin};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ActivityController extends Controller
{

  use ChecksLogin, ChecksPermissions;

  private const ENTITY = 'activity';
  private const REDIRECT_INDEX = '/';

  public function __construct()
  {
    $this->middleware(MiddlewaresConstants::AUTH);
  }

  public function activity(Request $request): View|RedirectResponse|null
  {
    $action = __METHOD__;
    return $this->measureProfile($action, function () use ($request, $action) {
      if (($user = self::_checkLogin()) instanceof RedirectResponse) {
        Log::notice("{$action} - unauthenticated user, redirecting");
        return $user;
      }
      if (($redirect = self::guard($request, PermissionsConstants::VW_CRM, self::REDIRECT_INDEX)) !== true) {
        Log::warning("{$action} - insufficient permissions, redirecting", ['guard_redirect' => self::REDIRECT_INDEX]);
        return $redirect;
      }
      Log::info("{$action} started", [ActivitiesConstants::COL_U => $user->id]);
      try {
        $creatorId  = $user->creatorId();
        $models     = [
          DatabaseConstants::TABLE_NOTES      => Note::class,
          DatabaseConstants::TABLE_TASKS      => Task::class,
          DatabaseConstants::TABLE_EMAILS     => Email::class,
          DatabaseConstants::TABLE_LOG_ACTS   => ActivityLog::class,
          DatabaseConstants::TABLE_SCHEDULES  => Schedule::class,
        ];
        $allResults = [];
        foreach ($models as $alias => $modelClass) {
          $items = $modelClass::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
            ->orderBy('id', 'desc')
            ->get();
          Log::debug("{$action} fetched items", ['alias' => $alias, 'count' => $items->count()]);
          $allResults[$alias] = $items->map(fn($item) => $this->formatActivity($alias, $item))->all();
        }
        Log::info("{$action} succeeded", ['sections' => array_keys($models)]);
        return view(ServicesConstants::CRM . '.' . self::ENTITY . '.view', [
          'results'   => $allResults[DatabaseConstants::TABLE_NOTES],
          'results1'  => $allResults[DatabaseConstants::TABLE_TASKS],
          'results2'  => $allResults[DatabaseConstants::TABLE_EMAILS],
          'results3'  => $allResults[DatabaseConstants::TABLE_LOG_ACTS],
          'results4'  => $allResults[DatabaseConstants::TABLE_SCHEDULES],
        ]);
      } catch (\Throwable $e) {
        Log::error("{$action} failed", [
          'error'     => $e->getMessage(),
          'file'      => $e->getFile(),
          'line'      => $e->getLine(),
        ]);
        return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
      }
    });
  }

  public function index(Request $request): View|RedirectResponse|JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $request) {
      Log::info("[$action] start", ['uri' => $request->getRequestUri(), 'method' => $request->getMethod()]);
      $checkStart = microtime(true);
      $userOrRedirect = self::_checkLogin();
      $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
      if ($userOrRedirect instanceof RedirectResponse) {
        Log::warning("[$action] not authenticated", ['uri' => $request->getRequestUri()]);
        Log::notice("[$action] authentication failed", ['uri' => $request->getRequestUri()]);
        return $userOrRedirect;
      }
      $user = $userOrRedirect;
      if (!$user?->can(PermissionsConstants::VW_CRM)) {
        Log::warning("[$action] permission denied", ['user_id' => $user?->id]);
        Log::debug("[$action] user lacks VW_CRM permission", []);
        return defaultPermissionDenial($request, new AuthorizationException(), $action);
      }
      try {
        $fetchStart = microtime(true);
        [$notes, $tasks, $emails, $logActivities, $schedules] = array_map(function ($m) use ($user) {
          return $m::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        }, [Note::class, Task::class, Email::class, ActivityLog::class, Schedule::class]);
        $this->logExecutionTime($fetchStart, $action . '::fetchData', 'completed');
        $processStart = microtime(true);
        $notesProcessed = self::_processItems($notes, function ($note) {
          $data = Activity::getActivity($note[ActivitiesConstants::COL_MT], $note[ActivitiesConstants::COL_MI]);
          $data[ActivitiesConstants::COL_NT] = $note[ActivitiesConstants::COL_NT];
          $data[DatabaseConstants::COL_C_AT] = $note[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          return $data;
        });
        $tasksProcessed = self::_processItems($tasks, function ($task) {
          $data = Activity::getActivity($task[ActivitiesConstants::COL_MT], $task[ActivitiesConstants::COL_MI]);
          $data[ActivitiesConstants::COL_NT] = $task[ActivitiesConstants::COL_DESC];
          $data[DatabaseConstants::COL_C_AT] = $task[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          $data[ActivitiesConstants::COL_A_O_M] = $task[ActivitiesConstants::COL_A_O_M];
          return $data;
        });
        $emailsProcessed = self::_processItems($emails, function ($email) {
          $data = Activity::getActivity($email[ActivitiesConstants::COL_MT], $email[ActivitiesConstants::COL_MI]);
          $data[ActivitiesConstants::COL_NT] = $email[ActivitiesConstants::COL_DESC];
          $data[DatabaseConstants::COL_C_AT] = $email[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          $data[UsersConstants::COL_EM] = $email->email;
          return $data;
        });
        $logActivitiesProcessed = self::_processItems($logActivities, function ($log) {
          $data = Activity::getActivity($log[ActivitiesConstants::COL_MT], $log[ActivitiesConstants::COL_MI]);
          foreach ([ActivitiesConstants::COL_NT, ProjectsConstants::COL_S_DT, ActivitiesConstants::COL_TSK_TIME, ActivitiesConstants::COL_TP] as $key)
            $data[$key] = $log->$key;
          $data[DatabaseConstants::COL_C_AT] = $log[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          return $data;
        });
        $schedulesProcessed = self::_processItems($schedules, function ($schedule) {
          $data = Activity::getActivity($schedule[ActivitiesConstants::COL_MT], $schedule[ActivitiesConstants::COL_MI]);
          foreach ([ActivitiesConstants::COL_NT, ProjectsConstants::COL_S_DT, ActivitiesConstants::COL_TSK_TIME, ActivitiesConstants::COL_TP] as $key)
            $data[$key] = $schedule->$key;
          $data[DatabaseConstants::COL_C_AT] = $schedule[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          return $data;
        });
        $this->logExecutionTime($processStart, $action . '::processItems', 'completed');
        $viewStart = microtime(true);
        return view(ServicesConstants::CRM . '.' . self::ENTITY . '.view', compact('notesProcessed', 'tasksProcessed', 'emailsProcessed', 'logActivitiesProcessed', 'schedulesProcessed'));
      } catch (\Throwable $e) {
        Log::error("[$action] Error in {$action}", ['error' => $e->getMessage()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        return defaultUndefinedException($request, $e, $action);
      }
    }, ['uri' => $request->getRequestUri(), 'method' => $request->getMethod()]);
  }

  public function notes(Request $request): JsonResponse
  {
    $method = __METHOD__;
    Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    return $this->measureProfile($method, function () use ($request, $method) {
      $stepStart = microtime(true);
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
        return $userOrRedirect;
      $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
      $stepStart = microtime(true);
      $user = $userOrRedirect;
      if (!$user?->can(PermissionsConstants::VW_CRM)) {
        Log::warning($method . ' - permission denied', ['user_id' => $user?->id]);
        Log::debug($method . ' - user lacks VW_CRM permission', ['user_id' => $user?->id]);
        return defaultPermissionDenial($request, new AuthorizationException(), $method);
      }
      $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
      try {
        $stepStart = microtime(true);
        $data = Note::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        $this->logExecutionTime($stepStart, 'fetchNotes', 'completed');
        $stepStart = microtime(true);
        $processed = self::_processItems($data, function ($note) {
          $item = Activity::getActivity($note[ActivitiesConstants::COL_MT], $note[ActivitiesConstants::COL_MI]);
          $item[ActivitiesConstants::COL_NT] = $note[ActivitiesConstants::COL_NT];
          $item[DatabaseConstants::COL_C_AT] = $note[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          return $item;
        });
        $this->logExecutionTime($stepStart, 'processItems', 'completed');
        return response()->json($processed, HttpFoundationResponse::HTTP_OK);
      } catch (\Throwable $e) {
        Log::debug($method . ' - exception details', ['exception' => get_class($e), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'uri' => $request->getRequestUri(), 'user_id' => $user->id]);
        Log::error($method . ' - notes retrieval failed', ['uri' => $request->getRequestUri()]);
        return defaultUndefinedException($request, $e, $method);
      }
    }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
  }

  public function tasks(Request $request): JsonResponse
  {
    $action = __CLASS__ . '::' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($request, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::notice("{$action} • unauthenticated, redirecting");
        return $userOrRedirect;
      }
      $user = $userOrRedirect;
      if (!$user->can(PermissionsConstants::VW_CRM)) {
        Log::warning("{$action} • permission denied", ['user_id' => $user->id]);
        return defaultPermissionDenial($request, new AuthorizationException(), $action);
      }
      try {
        Log::debug("{$action} • fetching tasks", ['creator_id' => $user->creatorId()]);
        $data = Task::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        Log::debug("{$action} • processing {$data->count()} tasks");
        $processed = self::_processItems($data, function ($task) {
          $item = Activity::getActivity($task[ActivitiesConstants::COL_MT], $task[ActivitiesConstants::COL_MI]);
          $item[ActivitiesConstants::COL_NT] = $task[ActivitiesConstants::COL_DESC];
          $item[DatabaseConstants::COL_C_AT] = $task[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          $item[ActivitiesConstants::COL_A_O_M] = $task[ActivitiesConstants::COL_A_O_M];
          return $item;
        });
        Log::info("{$action} • returning tasks payload", ['count' => count($processed)]);
        return response()->json($processed, HttpFoundationResponse::HTTP_OK);
      } catch (\Throwable $e) {
        Log::error("{$action} • exception", ['exception' => get_class($e), 'message' => $e->getMessage()]);
        return defaultUndefinedException($request, $e, $action);
      }
    });
  }

  public function emails(Request $request): JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $request) {
      Log::info("[$action] start", ['uri' => $request->getRequestUri(), 'method' => $request->getMethod()]);
      $checkStart = microtime(true);
      $userOrRedirect = self::_checkLogin();
      $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
      if ($userOrRedirect instanceof RedirectResponse) {
        Log::warning("[$action] not authenticated", ['uri' => $request->getRequestUri()]);
        Log::debug("[$action] authentication failed", ['uri' => $request->getRequestUri()]);
        return $userOrRedirect;
      }
      $user = $userOrRedirect;
      if (!$user?->can(PermissionsConstants::VW_CRM))
        return defaultPermissionDenial($request, new AuthorizationException(), $action);
      try {
        $fetchStart = microtime(true);
        $data = Email::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        $this->logExecutionTime($fetchStart, $action . '::fetchEmails', 'completed');
        $processStart = microtime(true);
        $processed = self::_processItems($data, function ($email) {
          $item = Activity::getActivity($email[ActivitiesConstants::COL_MT], $email[ActivitiesConstants::COL_MI]);
          $item[ActivitiesConstants::COL_NT] = $email[ActivitiesConstants::COL_DESC];
          $item[DatabaseConstants::COL_C_AT] = $email[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          $item[UsersConstants::COL_EM] = $email->email;
          return $item;
        });
        $this->logExecutionTime($processStart, $action . '::processItems', 'completed');
        return response()->json($processed, HttpFoundationResponse::HTTP_OK);
      } catch (\Throwable $e) {
        Log::error("[$action] Error in {$action}", ['error' => $e->getMessage()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        return defaultUndefinedException($request, $e, $action);
      }
    }, ['uri' => $request->getRequestUri(), 'method' => $request->getMethod()]);
  }

  public function logActivities(Request $request): JsonResponse
  {
    $method = __METHOD__;
    Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    return $this->measureProfile($method, function () use ($request, $method) {
      $stepStart = microtime(true);
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
        return $userOrRedirect;
      $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
      $stepStart = microtime(true);
      $user = $userOrRedirect;
      if (!$user?->can(PermissionsConstants::VW_CRM)) {
        Log::warning($method . ' - permission denied', ['user_id' => $user?->id]);
        Log::debug($method . ' - lacking VW_CRM permission', ['user_id' => $user?->id]);
        return defaultPermissionDenial($request, new AuthorizationException(), $method);
      }
      $this->logExecutionTime($stepStart, 'checkPermission', 'completed');
      try {
        $stepStart = microtime(true);
        $data = ActivityLog::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        $this->logExecutionTime($stepStart, 'fetchLogs', 'completed');
        $stepStart = microtime(true);
        $processed = self::_processItems($data, function ($log) {
          $item = Activity::getActivity($log[ActivitiesConstants::COL_MT], $log[ActivitiesConstants::COL_MI]);
          foreach ([ActivitiesConstants::COL_NT, ActivitiesConstants::COL_TSK_DATE, ActivitiesConstants::COL_TSK_TIME, ActivitiesConstants::COL_TP] as $key)
            $item[$key] = $log->$key;
          $item[DatabaseConstants::COL_C_AT] = $log[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
          return $item;
        });
        $this->logExecutionTime($stepStart, 'processItems', 'completed');
        return response()->json($processed, HttpFoundationResponse::HTTP_OK);
      } catch (\Throwable $e) {
        Log::debug($method . ' - exception details', ['exception' => get_class($e), 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'uri' => $request->getRequestUri()]);
        Log::error($method . ' - logActivities retrieval failed', ['uri' => $request->getRequestUri()]);
        return defaultUndefinedException($request, $e, $method);
      }
    }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
  }

  public function schedules(Request $request): JsonResponse
  {
    $action = __CLASS__ . '::' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($request, $action) {
      if (
        ($userOrRedirect = self::_checkLogin())
        instanceof RedirectResponse
      ) {
        Log::notice("{$action} • unauthenticated, redirecting", ['uri' => $request->getRequestUri()]);
        return $userOrRedirect;
      }
      $user = $userOrRedirect;
      if (!$user->can(PermissionsConstants::VW_CRM)) {
        Log::warning("{$action} • permission denied for user", ['user_id' => $user->id]);
        return defaultPermissionDenial($request, new AuthorizationException(), $action);
      }
      try {
        Log::debug("{$action} • fetching schedules", ['creator_id' => $user->creatorId()]);
        $data = Schedule::where(DatabaseConstants::COL_TABLE_CREATOR, $user->creatorId())
          ->orderBy('id', 'desc')
          ->get();
        Log::debug("{$action} • processing {$data->count()} schedules", ['count' => $data->count()]);
        $processed = self::_processItems($data, function ($schedule) {
          $item = Activity::getActivity(
            $schedule[ActivitiesConstants::COL_MT],
            $schedule[ActivitiesConstants::COL_MI]
          );
          foreach (
            [
              ActivitiesConstants::COL_NT,
              ProjectsConstants::COL_S_DT,
              ActivitiesConstants::COL_TSK_TIME,
              ActivitiesConstants::COL_TP
            ] as $key
          ) {
            $item[$key] = $schedule->$key;
          }
          $item[DatabaseConstants::COL_C_AT] = $schedule[DatabaseConstants::COL_C_AT]
            ->format('Y-m-d H:i:s');
          return $item;
        });
        Log::info("{$action} • returning schedules payload", ['count' => count($processed)]);
        return response()->json($processed, HttpFoundationResponse::HTTP_OK);
      } catch (\Throwable $e) {
        Log::error("{$action} • exception", [
          'exception' => get_class($e),
          'message'   => $e->getMessage()
        ]);
        return defaultUndefinedException($request, $e, $action);
      }
    });
  }

  protected static function _processItems(array $items, \Closure $processFunc): array
  {
    $results = [];
    foreach ($items as $item) {
      try {
        $results[] = $processFunc($item);
      } catch (\Throwable $e) {
        Log::error(__CLASS__ . '::' . __FUNCTION__ .
          " error processing item: " . $e->getMessage());
      }
    }
    return $results;
  }

  /**
   * Normalize any activity item into the same shape.
   */
  private function formatActivity(string $type, $item): array
  {
    $base = Activity::getActivity($item[ActivitiesConstants::COL_MT], $item[ActivitiesConstants::COL_MI]);
    $base[DatabaseConstants::COL_C_AT] = $item[DatabaseConstants::COL_C_AT]->format('Y-m-d H:i:s');
    return match ($type) {
      DatabaseConstants::TABLE_NOTES          => array_merge(
        $base,
        [ActivitiesConstants::COL_NT => $item[ActivitiesConstants::COL_NT]]
      ),
      DatabaseConstants::TABLE_TASKS          => array_merge($base, [
        ActivitiesConstants::COL_NT             => $item[ActivitiesConstants::COL_DESC],
        ActivitiesConstants::COL_A_O_M => $item[ActivitiesConstants::COL_A_O_M],
      ]),
      DatabaseConstants::TABLE_EMAILS         => array_merge($base, [
        ActivitiesConstants::COL_NT  => $item[ActivitiesConstants::COL_DESC],
        UsersConstants::COL_EM => $item->email,
      ]),
      DatabaseConstants::TABLE_LOG_ACTS => array_merge($base, [
        ActivitiesConstants::COL_NT       => $item[ActivitiesConstants::COL_NT],
        ProjectsConstants::COL_S_DT => $item[ProjectsConstants::COL_S_DT],
        ActivitiesConstants::COL_TSK_TIME       => $item[ActivitiesConstants::COL_TSK_TIME],
        ActivitiesConstants::COL_TP       => $item[ActivitiesConstants::COL_TP],
      ]),
      DatabaseConstants::TABLE_SCHEDULES      => array_merge($base, [
        ActivitiesConstants::COL_NT       => $item[ActivitiesConstants::COL_NT],
        ProjectsConstants::COL_S_DT => $item[ProjectsConstants::COL_S_DT],
        ActivitiesConstants::COL_TSK_TIME       => $item[ActivitiesConstants::COL_ST_TIME],
        ActivitiesConstants::COL_TP       => $item[ActivitiesConstants::COL_SCHD_TP],
      ]),
      default => $base,
    };
  }
}
