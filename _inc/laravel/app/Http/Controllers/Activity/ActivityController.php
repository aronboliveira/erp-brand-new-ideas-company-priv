<?php

namespace App\Http\Controllers;

require __DIR__ . '/errorHandlers.php';
require __DIR__ . '/http.php';

use App\Config\Constants\{
  ActivitiesConstants as AC,
  DatabaseConstants as DC,
  MiddlewaresConstants,
  PermissionsConstants,
  ProjectsConstants as PJC,
  ServicesConstants,
  UsersConstants as UC,
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
      Log::info("{$action} started", [AC::COL_U => $user->id]);
      try {
        $creatorId  = $user->creatorId();
        $models     = [
          DC::TABLE_NOTES      => Note::class,
          DC::TABLE_TASKS      => Task::class,
          DC::TABLE_EMAILS     => Email::class,
          DC::TABLE_LOG_ACTS   => ActivityLog::class,
          DC::TABLE_SCHEDULES  => Schedule::class,
        ];
        $allResults = [];
        foreach ($models as $alias => $modelClass) {
          $items = $modelClass::where(DC::COL_TABLE_CREATOR, $creatorId)
            ->orderBy('id', 'desc')
            ->get();
          Log::debug("{$action} fetched items", ['alias' => $alias, 'count' => $items->count()]);
          $allResults[$alias] = $items->map(fn($item) => $this->formatActivity($alias, $item))->all();
        }
        Log::info("{$action} succeeded", ['sections' => array_keys($models)]);
        return view(ServicesConstants::CRM . '.' . self::ENTITY . '.view', [
          'results'   => $allResults[DC::TABLE_NOTES],
          'results1'  => $allResults[DC::TABLE_TASKS],
          'results2'  => $allResults[DC::TABLE_EMAILS],
          'results3'  => $allResults[DC::TABLE_LOG_ACTS],
          'results4'  => $allResults[DC::TABLE_SCHEDULES],
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
          return $m::where(DC::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        }, [Note::class, Task::class, Email::class, ActivityLog::class, Schedule::class]);
        $this->logExecutionTime($fetchStart, $action . '::fetchData', 'completed');
        $processStart = microtime(true);
        $notesProcessed = self::_processItems($notes, function ($note) {
          $data = Activity::getActivity($note[AC::COL_MT], $note[AC::COL_MI]);
          $data[AC::COL_NT] = $note[AC::COL_NT];
          $data[DC::COL_C_AT] = $note[DC::COL_C_AT]->format('Y-m-d H:i:s');
          return $data;
        });
        $tasksProcessed = self::_processItems($tasks, function ($task) {
          $data = Activity::getActivity($task[AC::COL_MT], $task[AC::COL_MI]);
          $data[AC::COL_NT] = $task[AC::COL_DESC];
          $data[DC::COL_C_AT] = $task[DC::COL_C_AT]->format('Y-m-d H:i:s');
          $data[AC::COL_A_O_M] = $task[AC::COL_A_O_M];
          return $data;
        });
        $emailsProcessed = self::_processItems($emails, function ($email) {
          $data = Activity::getActivity($email[AC::COL_MT], $email[AC::COL_MI]);
          $data[AC::COL_NT] = $email[AC::COL_DESC];
          $data[DC::COL_C_AT] = $email[DC::COL_C_AT]->format('Y-m-d H:i:s');
          $data[UC::COL_EM] = $email->{UC::COL_EM_KEY};
          return $data;
        });
        $logActivitiesProcessed = self::_processItems($logActivities, function ($log) {
          $data = Activity::getActivity($log[AC::COL_MT], $log[AC::COL_MI]);
          foreach ([AC::COL_NT, PJC::COL_S_DT, AC::COL_TSK_TIME, AC::COL_TP] as $key)
            $data[$key] = $log->$key;
          $data[DC::COL_C_AT] = $log[DC::COL_C_AT]->format('Y-m-d H:i:s');
          return $data;
        });
        $schedulesProcessed = self::_processItems($schedules, function ($schedule) {
          $data = Activity::getActivity($schedule[AC::COL_MT], $schedule[AC::COL_MI]);
          foreach ([AC::COL_NT, PJC::COL_S_DT, AC::COL_TSK_TIME, AC::COL_TP] as $key)
            $data[$key] = $schedule->$key;
          $data[DC::COL_C_AT] = $schedule[DC::COL_C_AT]->format('Y-m-d H:i:s');
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
        $data = Note::where(DC::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        $this->logExecutionTime($stepStart, 'fetchNotes', 'completed');
        $stepStart = microtime(true);
        $processed = self::_processItems($data, function ($note) {
          $item = Activity::getActivity($note[AC::COL_MT], $note[AC::COL_MI]);
          $item[AC::COL_NT] = $note[AC::COL_NT];
          $item[DC::COL_C_AT] = $note[DC::COL_C_AT]->format('Y-m-d H:i:s');
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
        $data = Task::where(DC::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        Log::debug("{$action} • processing {$data->count()} tasks");
        $processed = self::_processItems($data, function ($task) {
          $item = Activity::getActivity($task[AC::COL_MT], $task[AC::COL_MI]);
          $item[AC::COL_NT] = $task[AC::COL_DESC];
          $item[DC::COL_C_AT] = $task[DC::COL_C_AT]->format('Y-m-d H:i:s');
          $item[AC::COL_A_O_M] = $task[AC::COL_A_O_M];
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
        $data = Email::where(DC::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        $this->logExecutionTime($fetchStart, $action . '::fetchEmails', 'completed');
        $processStart = microtime(true);
        $processed = self::_processItems($data, function ($email) {
          $item = Activity::getActivity($email[AC::COL_MT], $email[AC::COL_MI]);
          $item[AC::COL_NT] = $email[AC::COL_DESC];
          $item[DC::COL_C_AT] = $email[DC::COL_C_AT]->format('Y-m-d H:i:s');
          $item[UC::COL_EM] = $email->{UC::COL_EM_KEY};
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
        $data = ActivityLog::where(DC::COL_TABLE_CREATOR, $user->creatorId())->orderBy('id', 'desc')->get();
        $this->logExecutionTime($stepStart, 'fetchLogs', 'completed');
        $stepStart = microtime(true);
        $processed = self::_processItems($data, function ($log) {
          $item = Activity::getActivity($log[AC::COL_MT], $log[AC::COL_MI]);
          foreach ([AC::COL_NT, AC::COL_TSK_DATE, AC::COL_TSK_TIME, AC::COL_TP] as $key)
            $item[$key] = $log->$key;
          $item[DC::COL_C_AT] = $log[DC::COL_C_AT]->format('Y-m-d H:i:s');
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
        $data = Schedule::where(DC::COL_TABLE_CREATOR, $user->creatorId())
          ->orderBy('id', 'desc')
          ->get();
        Log::debug("{$action} • processing {$data->count()} schedules", ['count' => $data->count()]);
        $processed = self::_processItems($data, function ($schedule) {
          $item = Activity::getActivity(
            $schedule[AC::COL_MT],
            $schedule[AC::COL_MI]
          );
          foreach (
            [
              AC::COL_NT,
              PJC::COL_S_DT,
              AC::COL_TSK_TIME,
              AC::COL_TP
            ] as $key
          ) {
            $item[$key] = $schedule->$key;
          }
          $item[DC::COL_C_AT] = $schedule[DC::COL_C_AT]
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
    $base = Activity::getActivity($item[AC::COL_MT], $item[AC::COL_MI]);
    $base[DC::COL_C_AT] = $item[DC::COL_C_AT]->format('Y-m-d H:i:s');
    return match ($type) {
      DC::TABLE_NOTES          => array_merge(
        $base,
        [AC::COL_NT => $item[AC::COL_NT]]
      ),
      DC::TABLE_TASKS          => array_merge($base, [
        AC::COL_NT             => $item[AC::COL_DESC],
        AC::COL_A_O_M => $item[AC::COL_A_O_M],
      ]),
      DC::TABLE_EMAILS         => array_merge($base, [
        AC::COL_NT  => $item[AC::COL_DESC],
        UC::COL_EM => $item->email,
      ]),
      DC::TABLE_LOG_ACTS => array_merge($base, [
        AC::COL_NT       => $item[AC::COL_NT],
        PJC::COL_S_DT => $item[PJC::COL_S_DT],
        AC::COL_TSK_TIME       => $item[AC::COL_TSK_TIME],
        AC::COL_TP       => $item[AC::COL_TP],
      ]),
      DC::TABLE_SCHEDULES      => array_merge($base, [
        AC::COL_NT       => $item[AC::COL_NT],
        PJC::COL_S_DT => $item[PJC::COL_S_DT],
        AC::COL_TSK_TIME       => $item[AC::COL_ST_TIME],
        AC::COL_TP       => $item[AC::COL_SCHD_TP],
      ]),
      default => $base,
    };
  }
}
