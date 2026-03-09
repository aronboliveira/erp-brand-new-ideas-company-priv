<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Abstracts\Controller;

require_once __DIR__ . '/errorHandlers.php';
require_once __DIR__ . '/http.php';

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, MiddlewaresConstants as MWC, PermissionsConstants as PMC, ProjectsConstants as PJC, ServicesConstants as SVC, UsersConstants as UC};
use App\Models\{Activity, ActivityLog, Email, Note, Schedule, Task};
use App\Traits\{ChecksLogin, ChecksPermissions, ConsoleOutputs};
use Illuminate\Auth\Access\{AuthorizationException};
use Illuminate\Contracts\View\{View};
use Illuminate\Database\{QueryException};
use Illuminate\Database\Eloquent\{ModelNotFoundException};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, View as ViewFacade};
use Symfony\Component\HttpFoundation\{Response as HttpFoundationResponse};
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

class ActivityController extends Controller
{
	use ChecksLogin, ChecksPermissions, ConsoleOutputs;

	private const ENTITY = 'activity';
	private const REDIRECT_INDEX = '/';
	public const IDX = 'index';


	public function __construct()
	{
		$this->middleware(MWC::AUTH);
	}

	public function activity(Request $request): View|RedirectResponse|null
	{
		$action ??= __FUNCTION__;
		$method ??= __METHOD__;
		$class ??= static::class;
		$req ??= $request;
		$viewPath ??= SVC::CRM . '.' . self::ENTITY . '.view';
		return $this->measureProfile($action, function () use ($req, $action, $method, $class, $viewPath) {
			$user ??= null;
			$creatorId ??= null;
			$models ??= [];
			$allResults ??= [];
			try {
				$checkStart = microtime(true);
				$userOrRedirect = self::_checkLogin();
				$this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
				if ($userOrRedirect instanceof RedirectResponse) {
					Log::notice("[{$class}::{$action}] unauthenticated", ['method' => $method, 'uri' => $req->getRequestUri()]);
					$this->consoleOutput("{$action} unauthenticated", 'warning');
					return $userOrRedirect;
				}
				$user = $userOrRedirect;
				if (empty($user))
					return redirect()->back()->with('error', __('User not resolved.'));
				$guardStart = microtime(true);
				$redirect = self::guard($req, PMC::VW_CRM, self::REDIRECT_INDEX);
				$this->logExecutionTime($guardStart, $action . '::guard', 'completed');
				if ($redirect instanceof RedirectResponse) {
					Log::warning("[{$class}::{$action}] permission denied", [UC::COL_USER_ID => $user?->id, 'method' => $method]);
					$this->consoleOutput("{$action} permission denied", 'warning');
					return $redirect;
				}
				Log::info("[{$class}::{$action}] start", [AC::COL_U => $user?->id, 'method' => $method]);
				$creatorId = $this->resolveInt($user?->creatorId());
				if (empty($creatorId))
					return redirect()->back()->with('error', __('Creator not resolved.'));
				$models = [
					DC::TABLE_EMAILS => Email::class,
					DC::TABLE_LOG_ACTS => ActivityLog::class,
					DC::TABLE_NOTES => Note::class,
					DC::TABLE_SCHEDULES => Schedule::class,
					DC::TABLE_TASKS => Task::class
				];
				foreach ($models as $alias => $modelClass) {
					$fetchStart = microtime(true);
					$items = $modelClass::where(DC::COL_TABLE_CREATOR, $creatorId)->orderBy('id', 'desc')->get();
					$this->logExecutionTime($fetchStart, $action . '::fetch_' . $alias, 'completed');
					$allResults[$alias] = $this->processItems($items, fn($item) => $this->formatActivity($alias, $item));
				}
				Log::info("[{$class}::{$action}] ready", ['sections' => array_keys($models)]);
				return $this->renderViewChecked($req, $viewPath, [
					'results' => $allResults[DC::TABLE_NOTES] ?? [],
					'results1' => $allResults[DC::TABLE_TASKS] ?? [],
					'results2' => $allResults[DC::TABLE_EMAILS] ?? [],
					'results3' => $allResults[DC::TABLE_LOG_ACTS] ?? [],
					'results4' => $allResults[DC::TABLE_SCHEDULES] ?? []
				], $action);
			} catch (AuthorizationException $e) {
				$this->logFailure($class, $action, $e, ['user_id' => $user?->id, 'method' => $method]);
				$this->consoleOutput("{$action} authorization failed", 'error');
				return defaultPermissionDenial($req, $e, $class . '::' . $action);
			} catch (ModelNotFoundException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method], self::REDIRECT_INDEX);
			} catch (QueryException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method], self::REDIRECT_INDEX);
			} catch (\RuntimeException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method], self::REDIRECT_INDEX);
			} catch (\Throwable $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method], self::REDIRECT_INDEX);
			}
		});
	}

	public function index(Request $request): View|RedirectResponse|JsonResponse
	{
		$action ??= class_basename(static::class) . '@' . __FUNCTION__;
		$method ??= __METHOD__;
		$class ??= static::class;
		$req ??= $request;
		$viewPath ??= SVC::CRM . '.' . self::ENTITY . '.view';
		return $this->measureProfile($action, function () use ($action, $method, $class, $req, $viewPath) {
			$user ??= null;
			$creatorId ??= null;
			try {
				Log::info("[{$class}::{$action}] start", ['uri' => $req->getRequestUri(), 'method' => $req->getMethod()]);
				$checkStart = microtime(true);
				$userOrRedirect = self::_checkLogin();
				$this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
				if ($userOrRedirect instanceof RedirectResponse) {
					Log::warning("[{$class}::{$action}] unauthenticated", ['uri' => $req->getRequestUri()]);
					$this->consoleOutput("{$action} unauthenticated", 'warning');
					return $userOrRedirect;
				}
				$user = $userOrRedirect;
				if (empty($user))
					return redirect()->back()->with('error', __('User not resolved.'));
				$creatorId = $this->resolveInt($user?->creatorId());
				if (empty($creatorId))
					return redirect()->back()->with('error', __('Creator not resolved.'));
				if (!$user?->can(PMC::VW_CRM)) {
					Log::warning("[{$class}::{$action}] permission denied", ['user_id' => $user?->id]);
					$this->consoleOutput("{$action} permission denied", 'warning');
					return defaultPermissionDenial($req, new AuthorizationException(), $action);
				}
				$fetchStart = microtime(true);
				[$notes, $tasks, $emails, $logActivities, $schedules] = array_map(function ($model) use ($creatorId) {
					return $model::where(DC::COL_TABLE_CREATOR, $creatorId)->orderBy('id', 'desc')->get();
				}, [Note::class, Task::class, Email::class, ActivityLog::class, Schedule::class]);
				$this->logExecutionTime($fetchStart, $action . '::fetchData', 'completed');
				$processStart = microtime(true);
				$notesProcessed = $this->processItems($notes, function ($note) {
					$data = Activity::getActivity($this->resolveScalar($this->resolveField($note, AC::COL_MT)), $this->resolveScalar($this->resolveField($note, AC::COL_MI)));
					$data[AC::COL_NT] = $this->resolveScalar($this->resolveField($note, AC::COL_NT));
					$data[DC::COL_C_AT] = $this->formatDate($this->resolveField($note, DC::COL_C_AT));
					return $data;
				});
				$tasksProcessed = $this->processItems($tasks, function ($task) {
					$data = Activity::getActivity($this->resolveScalar($this->resolveField($task, AC::COL_MT)), $this->resolveScalar($this->resolveField($task, AC::COL_MI)));
					$data[AC::COL_NT] = $this->resolveScalar($this->resolveField($task, AC::COL_DESC));
					$data[DC::COL_C_AT] = $this->formatDate($this->resolveField($task, DC::COL_C_AT));
					$data[AC::COL_A_O_M] = $this->resolveScalar($this->resolveField($task, AC::COL_A_O_M));
					return $data;
				});
				$emailsProcessed = $this->processItems($emails, function ($email) {
					$data = Activity::getActivity($this->resolveScalar($this->resolveField($email, AC::COL_MT)), $this->resolveScalar($this->resolveField($email, AC::COL_MI)));
					$data[AC::COL_NT] = $this->resolveScalar($this->resolveField($email, AC::COL_DESC));
					$data[DC::COL_C_AT] = $this->formatDate($this->resolveField($email, DC::COL_C_AT));
					$data[UC::COL_EM] = $this->resolveScalar($this->resolveField($email, UC::COL_EM_KEY));
					return $data;
				});
				$logActivitiesProcessed = $this->processItems($logActivities, function ($log) {
					$data = Activity::getActivity($this->resolveScalar($this->resolveField($log, AC::COL_MT)), $this->resolveScalar($this->resolveField($log, AC::COL_MI)));
					foreach ([AC::COL_NT, PJC::COL_S_DT, AC::COL_TSK_TIME, AC::COL_TP] as $key)
						$data[$key] = $this->resolveScalar($this->resolveField($log, $key));
					$data[DC::COL_C_AT] = $this->formatDate($this->resolveField($log, DC::COL_C_AT));
					return $data;
				});
				$schedulesProcessed = $this->processItems($schedules, function ($schedule) {
					$data = Activity::getActivity($this->resolveScalar($this->resolveField($schedule, AC::COL_MT)), $this->resolveScalar($this->resolveField($schedule, AC::COL_MI)));
					foreach ([AC::COL_NT, PJC::COL_S_DT, AC::COL_TSK_TIME, AC::COL_TP] as $key)
						$data[$key] = $this->resolveScalar($this->resolveField($schedule, $key));
					$data[DC::COL_C_AT] = $this->formatDate($this->resolveField($schedule, DC::COL_C_AT));
					return $data;
				});
				$this->logExecutionTime($processStart, $action . '::processItems', 'completed');
				return $this->renderViewChecked($req, $viewPath, compact('notesProcessed', 'tasksProcessed', 'emailsProcessed', 'logActivitiesProcessed', 'schedulesProcessed'), $action);
			} catch (AuthorizationException $e) {
				$this->logFailure($class, $action, $e, ['user_id' => $user?->id, 'method' => $method]);
				$this->consoleOutput("{$action} authorization failed", 'error');
				return defaultPermissionDenial($req, $e, $class . '::' . $action);
			} catch (QueryException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\RuntimeException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\Throwable $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			}
		}, ['uri' => $req->getRequestUri(), 'method' => $req->getMethod()]);
	}

	public function notes(Request $request): JsonResponse
	{
		$action ??= __FUNCTION__;
		$method ??= __METHOD__;
		$class ??= static::class;
		$req ??= $request;
		return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
			$user ??= null;
			try {
				Log::info("[{$class}::{$action}] start", ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
				$stepStart = microtime(true);
				$userOrRedirect = self::_checkLogin();
				$this->logExecutionTime($stepStart, $action . '::_checkLogin', 'completed');
				if ($userOrRedirect instanceof RedirectResponse) {
					Log::warning("[{$class}::{$action}] unauthenticated", ['uri' => $req->getRequestUri()]);
					$this->consoleOutput("{$action} unauthenticated", 'warning');
					return response()->json(['error' => __('Unauthenticated.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				}
				$user = $userOrRedirect;
				if (empty($user))
					return response()->json(['error' => __('User not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				if (!$user?->can(PMC::VW_CRM)) {
					Log::warning("[{$class}::{$action}] permission denied", ['user_id' => $user?->id]);
					$this->consoleOutput("{$action} permission denied", 'warning');
					return defaultPermissionDenial($req, new AuthorizationException(), $action);
				}
				$creatorId = $this->resolveInt($user?->creatorId());
				if (empty($creatorId))
					return response()->json(['error' => __('Creator not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				$stepStart = microtime(true);
				$data = Note::where(DC::COL_TABLE_CREATOR, $creatorId)->orderBy('id', 'desc')->get();
				$this->logExecutionTime($stepStart, $action . '::fetchNotes', 'completed');
				$stepStart = microtime(true);
				$processed = $this->processItems($data, function ($note) {
					$item = Activity::getActivity($this->resolveScalar($this->resolveField($note, AC::COL_MT)), $this->resolveScalar($this->resolveField($note, AC::COL_MI)));
					$item[AC::COL_NT] = $this->resolveScalar($this->resolveField($note, AC::COL_NT));
					$item[DC::COL_C_AT] = $this->formatDate($this->resolveField($note, DC::COL_C_AT));
					return $item;
				});
				$this->logExecutionTime($stepStart, $action . '::processItems', 'completed');
				return response()->json($processed, HttpFoundationResponse::HTTP_OK);
			} catch (AuthorizationException $e) {
				$this->logFailure($class, $action, $e, ['user_id' => $user?->id, 'method' => $method]);
				$this->consoleOutput("{$action} authorization failed", 'error');
				return response()->json(['error' => __('Permission denied.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
			} catch (QueryException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['uri' => $req->getRequestUri(), 'user_id' => $user?->id, 'method' => $method]);
			} catch (\RuntimeException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['uri' => $req->getRequestUri(), 'user_id' => $user?->id, 'method' => $method]);
			} catch (\Throwable $e) {
				return $this->handleFailure($req, $e, $class, $action, ['uri' => $req->getRequestUri(), 'user_id' => $user?->id, 'method' => $method]);
			}
		}, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
	}

	public function tasks(Request $request): JsonResponse
	{
		$action ??= __FUNCTION__;
		$method ??= __METHOD__;
		$class ??= static::class;
		$req ??= $request;
		return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
			$user ??= null;
			try {
				$checkStart = microtime(true);
				$userOrRedirect = self::_checkLogin();
				$this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
				if ($userOrRedirect instanceof RedirectResponse) {
					Log::notice("[{$class}::{$action}] unauthenticated", ['method' => $method, 'uri' => $req->getRequestUri()]);
					$this->consoleOutput("{$action} unauthenticated", 'warning');
					return response()->json(['error' => __('Unauthenticated.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				}
				$user = $userOrRedirect;
				if (empty($user))
					return response()->json(['error' => __('User not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				if (!$user?->can(PMC::VW_CRM)) {
					Log::warning("[{$class}::{$action}] permission denied", ['user_id' => $user?->id]);
					$this->consoleOutput("{$action} permission denied", 'warning');
					return defaultPermissionDenial($req, new AuthorizationException(), $action);
				}
				$creatorId = $this->resolveInt($user?->creatorId());
				if (empty($creatorId))
					return response()->json(['error' => __('Creator not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId]);
				$data = Task::where(DC::COL_TABLE_CREATOR, $creatorId)->orderBy('id', 'desc')->get();
				$processed = $this->processItems($data, function ($task) {
					$item = Activity::getActivity($this->resolveScalar($this->resolveField($task, AC::COL_MT)), $this->resolveScalar($this->resolveField($task, AC::COL_MI)));
					$item[AC::COL_NT] = $this->resolveScalar($this->resolveField($task, AC::COL_DESC));
					$item[DC::COL_C_AT] = $this->formatDate($this->resolveField($task, DC::COL_C_AT));
					$item[AC::COL_A_O_M] = $this->resolveScalar($this->resolveField($task, AC::COL_A_O_M));
					return $item;
				});
				Log::info("[{$class}::{$action}] ready", ['count' => count($processed)]);
				return response()->json($processed, HttpFoundationResponse::HTTP_OK);
			} catch (AuthorizationException $e) {
				$this->logFailure($class, $action, $e, ['user_id' => $user?->id, 'method' => $method]);
				$this->consoleOutput("{$action} authorization failed", 'error');
				return response()->json(['error' => __('Permission denied.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
			} catch (QueryException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\RuntimeException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\Throwable $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			}
		});
	}

	public function emails(Request $request): JsonResponse
	{
		$action ??= class_basename(static::class) . '@' . __FUNCTION__;
		$method ??= __METHOD__;
		$class ??= static::class;
		$req ??= $request;
		return $this->measureProfile($action, function () use ($action, $method, $class, $req) {
			$user ??= null;
			try {
				Log::info("[{$class}::{$action}] start", ['uri' => $req->getRequestUri(), 'method' => $req->getMethod()]);
				$checkStart = microtime(true);
				$userOrRedirect = self::_checkLogin();
				$this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
				if ($userOrRedirect instanceof RedirectResponse) {
					Log::warning("[{$class}::{$action}] unauthenticated", ['uri' => $req->getRequestUri()]);
					$this->consoleOutput("{$action} unauthenticated", 'warning');
					return response()->json(['error' => __('Unauthenticated.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				}
				$user = $userOrRedirect;
				if (empty($user))
					return response()->json(['error' => __('User not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				if (!$user?->can(PMC::VW_CRM)) {
					Log::warning("[{$class}::{$action}] permission denied", ['user_id' => $user?->id]);
					$this->consoleOutput("{$action} permission denied", 'warning');
					return defaultPermissionDenial($req, new AuthorizationException(), $action);
				}
				$creatorId = $this->resolveInt($user?->creatorId());
				if (empty($creatorId))
					return response()->json(['error' => __('Creator not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				$fetchStart = microtime(true);
				$data = Email::where(DC::COL_TABLE_CREATOR, $creatorId)->orderBy('id', 'desc')->get();
				$this->logExecutionTime($fetchStart, $action . '::fetchEmails', 'completed');
				$processStart = microtime(true);
				$processed = $this->processItems($data, function ($email) {
					$item = Activity::getActivity($this->resolveScalar($this->resolveField($email, AC::COL_MT)), $this->resolveScalar($this->resolveField($email, AC::COL_MI)));
					$item[AC::COL_NT] = $this->resolveScalar($this->resolveField($email, AC::COL_DESC));
					$item[DC::COL_C_AT] = $this->formatDate($this->resolveField($email, DC::COL_C_AT));
					$item[UC::COL_EM] = $this->resolveScalar($this->resolveField($email, UC::COL_EM_KEY));
					return $item;
				});
				$this->logExecutionTime($processStart, $action . '::processItems', 'completed');
				return response()->json($processed, HttpFoundationResponse::HTTP_OK);
			} catch (AuthorizationException $e) {
				$this->logFailure($class, $action, $e, ['user_id' => $user?->id, 'method' => $method]);
				$this->consoleOutput("{$action} authorization failed", 'error');
				return response()->json(['error' => __('Permission denied.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
			} catch (QueryException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\RuntimeException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\Throwable $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			}
		}, ['uri' => $req->getRequestUri(), 'method' => $req->getMethod()]);
	}

	public function logActivities(Request $request): JsonResponse
	{
		$action ??= __FUNCTION__;
		$method ??= __METHOD__;
		$class ??= static::class;
		$req ??= $request;
		return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
			$user ??= null;
			try {
				Log::info("[{$class}::{$action}] start", ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
				$stepStart = microtime(true);
				$userOrRedirect = self::_checkLogin();
				$this->logExecutionTime($stepStart, $action . '::_checkLogin', 'completed');
				if ($userOrRedirect instanceof RedirectResponse) {
					Log::warning("[{$class}::{$action}] unauthenticated", ['uri' => $req->getRequestUri()]);
					$this->consoleOutput("{$action} unauthenticated", 'warning');
					return response()->json(['error' => __('Unauthenticated.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				}
				$user = $userOrRedirect;
				if (empty($user))
					return response()->json(['error' => __('User not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				if (!$user?->can(PMC::VW_CRM)) {
					Log::warning("[{$class}::{$action}] permission denied", ['user_id' => $user?->id]);
					$this->consoleOutput("{$action} permission denied", 'warning');
					return defaultPermissionDenial($req, new AuthorizationException(), $action);
				}
				$creatorId = $this->resolveInt($user?->creatorId());
				if (empty($creatorId))
					return response()->json(['error' => __('Creator not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				$stepStart = microtime(true);
				$data = ActivityLog::where(DC::COL_TABLE_CREATOR, $creatorId)->orderBy('id', 'desc')->get();
				$this->logExecutionTime($stepStart, $action . '::fetchLogs', 'completed');
				$stepStart = microtime(true);
				$processed = $this->processItems($data, function ($log) {
					$item = Activity::getActivity($this->resolveScalar($this->resolveField($log, AC::COL_MT)), $this->resolveScalar($this->resolveField($log, AC::COL_MI)));
					foreach ([AC::COL_NT, AC::COL_TSK_DATE, AC::COL_TSK_TIME, AC::COL_TP] as $key)
						$item[$key] = $this->resolveScalar($this->resolveField($log, $key));
					$item[DC::COL_C_AT] = $this->formatDate($this->resolveField($log, DC::COL_C_AT));
					return $item;
				});
				$this->logExecutionTime($stepStart, $action . '::processItems', 'completed');
				return response()->json($processed, HttpFoundationResponse::HTTP_OK);
			} catch (AuthorizationException $e) {
				$this->logFailure($class, $action, $e, ['user_id' => $user?->id, 'method' => $method]);
				$this->consoleOutput("{$action} authorization failed", 'error');
				return response()->json(['error' => __('Permission denied.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
			} catch (QueryException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['uri' => $req->getRequestUri(), 'user_id' => $user?->id, 'method' => $method]);
			} catch (\RuntimeException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['uri' => $req->getRequestUri(), 'user_id' => $user?->id, 'method' => $method]);
			} catch (\Throwable $e) {
				return $this->handleFailure($req, $e, $class, $action, ['uri' => $req->getRequestUri(), 'user_id' => $user?->id, 'method' => $method]);
			}
		}, ['uri' => $req->getRequestUri(), 'ip' => $req->ip()]);
	}

	public function schedules(Request $request): JsonResponse
	{
		$action ??= __FUNCTION__;
		$method ??= __METHOD__;
		$class ??= static::class;
		$req ??= $request;
		return $this->measureProfile($action, function () use ($req, $action, $method, $class) {
			$user ??= null;
			try {
				$checkStart = microtime(true);
				$userOrRedirect = self::_checkLogin();
				$this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
				if ($userOrRedirect instanceof RedirectResponse) {
					Log::notice("[{$class}::{$action}] unauthenticated", ['uri' => $req->getRequestUri(), 'method' => $method]);
					$this->consoleOutput("{$action} unauthenticated", 'warning');
					return response()->json(['error' => __('Unauthenticated.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				}
				$user = $userOrRedirect;
				if (empty($user))
					return response()->json(['error' => __('User not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				if (!$user?->can(PMC::VW_CRM)) {
					Log::warning("[{$class}::{$action}] permission denied", ['user_id' => $user?->id]);
					$this->consoleOutput("{$action} permission denied", 'warning');
					return defaultPermissionDenial($req, new AuthorizationException(), $action);
				}
				$creatorId = $this->resolveInt($user?->creatorId());
				if (empty($creatorId))
					return response()->json(['error' => __('Creator not resolved.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
				Log::info("[{$class}::{$action}] start", ['creator_id' => $creatorId]);
				$data = Schedule::where(DC::COL_TABLE_CREATOR, $creatorId)
					->orderBy('id', 'desc')
					->get();
				$processed = $this->processItems($data, function ($schedule) {
					$item = Activity::getActivity($this->resolveScalar($this->resolveField($schedule, AC::COL_MT)), $this->resolveScalar($this->resolveField($schedule, AC::COL_MI)));
					foreach ([AC::COL_NT, PJC::COL_S_DT, AC::COL_TSK_TIME, AC::COL_TP] as $key)
						$item[$key] = $this->resolveScalar($this->resolveField($schedule, $key));
					$item[DC::COL_C_AT] = $this->formatDate($this->resolveField($schedule, DC::COL_C_AT));
					return $item;
				});
				Log::info("[{$class}::{$action}] ready", ['count' => count($processed)]);
				return response()->json($processed, HttpFoundationResponse::HTTP_OK);
			} catch (AuthorizationException $e) {
				$this->logFailure($class, $action, $e, ['user_id' => $user?->id, 'method' => $method]);
				$this->consoleOutput("{$action} authorization failed", 'error');
				return response()->json(['error' => __('Permission denied.')], HttpFoundationResponse::HTTP_UNAUTHORIZED);
			} catch (QueryException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\RuntimeException $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			} catch (\Throwable $e) {
				return $this->handleFailure($req, $e, $class, $action, ['user_id' => $user?->id, 'method' => $method]);
			}
		});
	}

	private function processItems(iterable $items, \Closure $processFunc): array
	{
		$action ??= __FUNCTION__;
		$class ??= static::class;
		$results ??= [];
		foreach ($items as $item) {
			$itemClass = is_object($item) ? $item::class : gettype($item);
			try {
				$results[] = $processFunc($item);
			} catch (\Error $e) {
				$this->logFailure($class, $action, $e, ['item_class' => $itemClass]);
				$this->consoleOutput("{$action} failed", 'error');
			} catch (\Exception $e) {
				$this->logFailure($class, $action, $e, ['item_class' => $itemClass]);
				$this->consoleOutput("{$action} failed", 'error');
			} catch (\Throwable $e) {
				$this->logFailure($class, $action, $e, ['item_class' => $itemClass]);
				$this->consoleOutput("{$action} failed", 'error');
			}
		}
		return $results;
	}

	private function formatActivity(string $type, mixed $item): array
	{
		$base = $this->normalizeArray(Activity::getActivity(
			$this->resolveScalar($this->resolveField($item, AC::COL_MT)),
			$this->resolveScalar($this->resolveField($item, AC::COL_MI))
		));
		$base[DC::COL_C_AT] = $this->formatDate($this->resolveField($item, DC::COL_C_AT));
		$emailValue = $this->resolveScalar($this->resolveField($item, UC::COL_EM_KEY)) ?? $this->resolveScalar($this->resolveField($item, UC::COL_EM));
		return match ($type) {
			DC::TABLE_NOTES => array_merge($base, [AC::COL_NT => $this->resolveScalar($this->resolveField($item, AC::COL_NT))]),
			DC::TABLE_TASKS => array_merge($base, [
				AC::COL_NT => $this->resolveScalar($this->resolveField($item, AC::COL_DESC)),
				AC::COL_A_O_M => $this->resolveScalar($this->resolveField($item, AC::COL_A_O_M))
			]),
			DC::TABLE_EMAILS => array_merge($base, [
				AC::COL_NT => $this->resolveScalar($this->resolveField($item, AC::COL_DESC)),
				UC::COL_EM => $emailValue
			]),
			DC::TABLE_LOG_ACTS => array_merge($base, [
				AC::COL_NT => $this->resolveScalar($this->resolveField($item, AC::COL_NT)),
				PJC::COL_S_DT => $this->resolveScalar($this->resolveField($item, PJC::COL_S_DT)),
				AC::COL_TSK_TIME => $this->resolveScalar($this->resolveField($item, AC::COL_TSK_TIME)),
				AC::COL_TP => $this->resolveScalar($this->resolveField($item, AC::COL_TP))
			]),
			DC::TABLE_SCHEDULES => array_merge($base, [
				AC::COL_NT => $this->resolveScalar($this->resolveField($item, AC::COL_NT)),
				PJC::COL_S_DT => $this->resolveScalar($this->resolveField($item, PJC::COL_S_DT)),
				AC::COL_TSK_TIME => $this->resolveScalar($this->resolveField($item, AC::COL_ST_TIME)),
				AC::COL_TP => $this->resolveScalar($this->resolveField($item, AC::COL_SCHD_TP))
			]),
			default => $base
		};
	}

	private function renderViewChecked(Request $request, string $view, array $data, string $action): View|RedirectResponse
	{
		if (!ViewFacade::exists($view))
			return $this->viewMissingRedirect($request, $view, $action);
		return view($view, $data);
	}

	private function viewMissingRedirect(Request $request, string $view, string $action, array $context = []): RedirectResponse
	{
		Log::warning("{$action} view missing", array_merge($context, [
			'view' => $view,
			'uri' => $request->getRequestUri(),
			'route' => $request->route()?->getName(),
			'source_file' => __FILE__
		]));
		$this->consoleOutput("{$action} view missing: {$view}", 'error');
		return redirect()->back()->with('error', "HTTP 404: Page {$view} not found!");
	}

	private function handleFailure(Request $request, \Throwable $e, string $class, string $action, array $context = [], ?string $route = null, int $status = 500): RedirectResponse|JsonResponse
	{
		$this->logFailure($class, $action, $e, array_merge($context, ['uri' => $request->getRequestUri(), 'route' => $request->route()?->getName()]));
		$this->consoleOutput("{$action} failed", 'error');
		if ($request->expectsJson() || $request->wantsJson())
			return response()->json(new \stdClass(), $status);
		$destination = $route ?? self::REDIRECT_INDEX;
		return defaultUndefinedException($request, $e, $action, $destination);
	}

	private function logFailure(string $class, string $action, \Throwable $e, array $context = []): void
	{
		Log::error("[{$class}::{$action}] failed", array_merge($context, [
			'error_class' => $e::class,
			'error_file' => $e->getFile(),
			'error_line' => $e->getLine(),
			'error_message' => $e->getMessage()
		]));
	}

	private function normalizeArray(mixed $value): array
	{
		if (is_array($value))
			return $value;
		if (is_object($value) && method_exists($value, 'toArray'))
			return $value->toArray();
		if (is_object($value))
			return (array) $value;
		return [];
	}

	private function flattenScalarArray(array $values): array
	{
		$result ??= [];
		foreach ($values as $value) {
			if (is_array($value)) {
				foreach ($this->flattenScalarArray($value) as $inner)
					$result[] = $inner;
				continue;
			}
			if (is_object($value)) {
				foreach ($this->flattenScalarArray($this->normalizeArray($value)) as $inner)
					$result[] = $inner;
				continue;
			}
			if (is_scalar($value))
				$result[] = $value;
		}
		return $result;
	}

	private function resolveScalar(mixed $value): string|int|float|bool|null
	{
		if (is_scalar($value))
			return $value;
		if (is_array($value)) {
			$flat = $this->flattenScalarArray($value);
			return $flat[0] ?? null;
		}
		if (is_object($value) && method_exists($value, '__toString'))
			return (string) $value;
		if (is_object($value)) {
			$flat = $this->flattenScalarArray($this->normalizeArray($value));
			return $flat[0] ?? null;
		}
		return null;
	}
	private function resolveInt(mixed $value): int|string|null
	{
		$scalar = $this->resolveScalar($value);
		if ($scalar === null || $scalar === "" || $scalar === false)
			return null;
		return is_numeric($scalar) ? (int) $scalar : (string) $scalar;
	}

	private function resolveField(mixed $item, string $key): mixed
	{
		if (is_array($item) && array_key_exists($key, $item))
			return $item[$key];
		if (is_object($item)) {
			if (isset($item->$key))
				return $item->$key;
			if ($item instanceof \ArrayAccess && isset($item[$key]))
				return $item[$key];
			if (method_exists($item, 'getAttribute'))
				return $item->getAttribute($key);
		}
		return null;
	}

	private function formatDate(mixed $value): ?string
	{
		if (is_object($value) && method_exists($value, 'format'))
			return $value->format('Y-m-d H:i:s');
		$scalar = $this->resolveScalar($value);
		return is_string($scalar) ? $scalar : null;
	}
}
