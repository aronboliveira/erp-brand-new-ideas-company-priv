<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  ActivitiesConstants,
  DatabaseConstants,
  PermissionsConstants,
  ProjectsConstants,
  UsersConstants
};
use App\Models\{
  Project,
  ProjectTask,
  ProjectUser,
  TimeTracker,
  TrackPhoto,
  User,
  Utility
};
use App\Traits\{ApiResponser, ChecksLogin};
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Log, Validator};
use App\Helpers\SafeConsoleOutput;
use function App\Http\Controllers\defaultUndefinedException;

class ApiController extends Controller
{
  use ApiResponser, ChecksLogin;

  public function login(FormRequest $request): JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($request, $action) {
      $output = SafeConsoleOutput::make();
      $output->writeln("{$action} Starting login");
      try {
        Log::debug("{$action} start", [
          'uri'    => $request->getRequestUri(),
          'method' => $request->getMethod(),
          'email'  => $request->input(UsersConstants::COL_EM, 'n/a'),
          'ip'     => $request->ip(),
        ]);
        $credentials = $request->validated();
        if (!Auth::attempt($credentials)) {
          Log::notice("{$action} authentication failed", [
            'email' => $credentials[UsersConstants::COL_EM] ?? 'n/a',
            'ip'    => $request->ip(),
          ]);
          $output->writeln("{$action} Credentials do not match");
          throw new AuthenticationException('Credentials do not match');
        }
        $user = Auth::user();
        if (!($user instanceof User)) {
          Log::error("{$action} guard returned non-User instance", [
            'email'      => $credentials[UsersConstants::COL_EM] ?? 'n/a',
            'request_ip' => $request->ip(),
          ]);
          $output->writeln("{$action} Unexpected user object");
          throw new \RuntimeException('Authenticated but user retrieval failed');
        }
        Log::info("{$action} authentication succeeded", ['user_id' => $user->id]);
        $output->writeln("{$action} Authenticated user ID {$user->id}");
        $settings = Utility::settings($user->id);
        $token    = $user->createToken('API Token')->plainTextToken;
        if (empty($token)) {
          Log::critical("{$action} token creation failed", ['user_id' => $user->id]);
          $output->writeln("{$action} Token creation failure");
          throw new \RuntimeException('Token generation failed');
        }
        $payload = [
          'token'     => $token,
          UsersConstants::COL_USER_ID => $user->id,
          DatabaseConstants::TABLE_SETTINGS => [
            'shortTime' => $settings[ActivitiesConstants::COL_ITV_TIME] ?? 0.5,
          ],
        ];
        Log::debug("{$action} returning payload", [
          'user_id' => $user->id,
          'token'   => substr($token, 0, 10) . '…',
        ]);
        $output->writeln("{$action} Login successful");
        return $this->success($payload, 'Login successful.');
      } catch (\Throwable $e) {
        $status  = $e instanceof AuthenticationException ? 401 : 500;
        $message = $e instanceof AuthenticationException
          ? $e->getMessage()
          : ($e instanceof \RuntimeException
            ? 'Internal server error. Please try again later.'
            : 'Unexpected error. Please contact support.');
        Log::error("{$action} exception", [
          'exception' => get_class($e),
          'message'   => $e->getMessage(),
          'uri'       => $request->getRequestUri(),
        ]);
        $output->writeln("{$action} Error: {$e->getMessage()}");
        return $this->error($message, $status);
      }
    }, ['uri' => $request->getRequestUri(), 'method' => $request->getMethod(), 'ip' => $request->ip()]);
  }

  public function logout(Request $request): JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $request) {
      $output = SafeConsoleOutput::make();
      $startMsg = 'Starting ' . $action;
      app()->runningInConsole()
        ? $output->writeln('<info> ' . $startMsg . ' </info>')
        : $output->writeln("## {$action}: {$startMsg}");
      Log::debug("[$action] start", ['uri' => $request->getRequestUri(), 'method' => $request->getMethod(), 'ip' => $request->ip()]);
      try {
        $checkStart = microtime(true);
        $userOrRedirect = self::_checkLogin();
        $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
        if ($userOrRedirect instanceof RedirectResponse) {
          Log::warning("[$action] not authenticated", ['uri' => $request->getRequestUri()]);
          Log::debug("[$action] authentication failed", ['uri' => $request->getRequestUri()]);
          $output->writeln("{$action} Not authenticated");
          return $this->error('Not authenticated', 401);
        }
        $user = $userOrRedirect;
        Log::info("[$action] revoking tokens", ['user_id' => $user?->id]);
        $revokeStart = microtime(true);
        $user?->tokens()->delete();
        $this->logExecutionTime($revokeStart, $action . '::revokeTokens', 'completed');
        Log::info("[$action] tokens revoked", ['user_id' => $user?->id]);
        $output->writeln("{$action} Tokens revoked for user ID {$user?->id}");
        return $this->success([], 'Tokens revoked');
      } catch (\Throwable $e) {
        Log::error("[$action] exception", ['exception' => get_class($e), 'message' => $e->getMessage(), 'uri' => $request->getRequestUri()]);
        Log::debug("[$action] Trace for debugging", ['trace' => $e->getTraceAsString()]);
        $output->writeln("{$action} Unexpected error: {$e->getMessage()}");
        return defaultUndefinedException($request, $e, $action);
      }
    }, ['uri' => $request->getRequestUri(), 'method' => $request->getMethod(), 'ip' => $request->ip()]);
  }

  public const GET_PRJ = 'getProjects';
  public function getProjects(Request $request): JsonResponse
  {
    $method = __METHOD__;
    $output = SafeConsoleOutput::make();
    Log::debug($method . ' - start', ['uri' => $request->getRequestUri(), 'method' => $request->getMethod(), 'ip' => $request->ip()]);
    return $this->measureProfile($method, function () use ($request, $output, $method) {
      $stepStart = microtime(true);
      $output->writeln("{$method} Fetching projects");
      try {
        $userOrRedirect = self::_checkLogin();
        $this->logExecutionTime($stepStart, 'checkLogin', 'completed');
        if ($userOrRedirect instanceof RedirectResponse) {
          Log::warning($method . ' - not authenticated', ['uri' => $request->getRequestUri()]);
          Log::debug($method . ' - authentication failed', ['uri' => $request->getRequestUri()]);
          $output->writeln("{$method} Not authenticated");
          return $this->error('Not authenticated', 401);
        }
        $user = $userOrRedirect;
        Log::info($method . ' - authenticated', ['user_id' => $user->id]);
        Log::debug($method . ' - user details', ['user_id' => $user->id]);
        $output->writeln("{$method} User ID {$user->id}");
        $stepStart = microtime(true);
        if ($user->type != PermissionsConstants::CPN) {
          $ids = ProjectUser::where(UsersConstants::COL_USER_ID, $user->id)->pluck(ProjectsConstants::COL_PJ_ID);
          Log::debug($method . ' - project IDs', ['ids' => $ids->toArray()]);
          $projects = Project::with(DatabaseConstants::TABLE_TASKS)->whereIn('id', $ids)->get();
        } else {
          Log::debug($method . ' - fetching own projects', ['user_id' => $user->id]);
          $projects = Project::with(DatabaseConstants::TABLE_TASKS)->where(DatabaseConstants::COL_TABLE_CREATOR, $user->id)->get();
        }
        $this->logExecutionTime($stepStart, 'fetchProjects', 'completed');
        Log::info($method . ' - retrieved', ['count' => $projects->count(), 'user_id' => $user->id]);
        Log::debug($method . ' - projects data', ['projects' => $projects]);
        $output->writeln("{$method} Retrieved {$projects->count()} projects");
        return $this->success([DatabaseConstants::TABLE_PROJECTS => $projects], 'Projects retrieved.');
      } catch (\Throwable $e) {
        Log::debug($method . ' - exception details', ['exception' => get_class($e), 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        Log::error($method . ' - unexpected error', ['uri' => $request->getRequestUri()]);
        $output->writeln("{$method} Unexpected error: {$e->getMessage()}");
        return defaultUndefinedException($request, $e, $method);
      }
    }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
  }

  public const ADD_TRK = 'addTracker';
  public function addTracker(Request $request): JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($request, $action) {
      $output = SafeConsoleOutput::make();
      Log::debug("{$action} start", [
        'uri'    => $request->getRequestUri(),
        'method' => $request->getMethod(),
        'ip'     => $request->ip(),
      ]);
      $output->writeln("{$action} Starting action");
      try {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
          Log::warning("{$action} not authenticated", ['uri' => $request->getRequestUri()]);
          $output->writeln("{$action} Not authenticated");
          return $this->error('Not authenticated', 401);
        }
        $user   = $userOrRedirect;
        $actionName = $request->input('action', 'unknown');
        Log::debug("{$action} action", ['action' => $actionName]);
        $output->writeln("{$action} Action: {$actionName}");
        if ($actionName === 'start') {
          if ($err = self::validateInput($request, ['taskId' => 'required|uuid'])) {
            Log::warning("{$action} validation failed", ['error' => $err]);
            $output->writeln("{$action} Validation error: {$err}");
            return $this->error($err, 422);
          }
          $taskId = $request->input('taskId');
          $task   = ProjectTask::find($taskId);
          if (!$task) {
            Log::warning("{$action} invalid task", ['taskId' => $taskId]);
            $output->writeln("{$action} Invalid task");
            return $this->error('Invalid task', 404);
          }
          TimeTracker::where(DatabaseConstants::COL_TABLE_CREATOR, $user->id)
            ->where(ActivitiesConstants::COL_IA, 1)
            ->update([ActivitiesConstants::COL_E_TIME => now()]);
          $tracker = TimeTracker::create([
            ProjectsConstants::COL_NM        => $request->input('workOn', ''),
            ProjectsConstants::COL_PJ_ID     => $task->project_id,
            'is_billable'                   => $request->input('isBillable', 0),
            'tag_id'                        => $request->input('tagId', ''),
            ActivitiesConstants::COL_ST_TIME => now(),
            ActivitiesConstants::COL_TSK_ID  => $task->id,
            DatabaseConstants::COL_TABLE_CREATOR => $user->id,
          ]);
          $tracker->action = 'start';
          Log::info("{$action} started", ['tracker_id' => $tracker->id, 'user_id' => $user->id]);
          $output->writeln("{$action} Tracker started (ID {$tracker->id})");
          return $this->success($tracker, 'Tracker started.');
        }
        if ($err = self::validateInput($request, ['trackerId' => 'required|uuid'])) {
          Log::warning("{$action} validation failed", ['error' => $err]);
          $output->writeln("{$action} Validation error: {$err}");
          return $this->error($err, 422);
        }
        $trackerId = $request->input('trackerId');
        $tracker   = TimeTracker::find($trackerId);
        if (!$tracker) {
          Log::warning("{$action} not found", ['trackerId' => $trackerId]);
          $output->writeln("{$action} Tracker not found");
          return $this->error('Tracker not found', 404);
        }
        $end = now();
        $tracker->fill([
          ActivitiesConstants::COL_E_TIME   => $end,
          ActivitiesConstants::COL_IA       => 0,
          ActivitiesConstants::COL_TTL_TIME => Utility::diffanceToTime(
            $tracker->{ActivitiesConstants::COL_ST_TIME},
            $end
          ),
        ])->save();
        Log::info("{$action} stopped", ['tracker_id' => $tracker->id, 'user_id' => $user->id]);
        $output->writeln("{$action} Tracker stopped (ID {$tracker->id})");
        return $this->success($tracker, 'Tracker stopped.');
      } catch (\Throwable $e) {
        Log::error("{$action} exception", [
          'exception' => get_class($e),
          'message'   => $e->getMessage(),
          'uri'       => $request->getRequestUri(),
        ]);
        $output->writeln("{$action} Unexpected error: {$e->getMessage()}");
        return defaultUndefinedException($request, $e, $action);
      }
    }, ['uri' => $request->getRequestUri(), 'method' => $request->getMethod(), 'ip' => $request->ip()]);
  }

  public const UP_IMG = 'uploadImage';
  public function uploadImage(Request $request): JsonResponse
  {
    $action = class_basename(static::class) . '@' . __FUNCTION__;
    return $this->measureProfile($action, function () use ($action, $request) {
      $output = SafeConsoleOutput::make();
      app()->runningInConsole()
        ? $output->writeln('<info> Uploading image </info>')
        : $output->writeln("## {$action}: Uploading image");
      Log::debug("[$action] start", ['uri' => $request->getRequestUri(), 'method' => $request->getMethod(), 'ip' => $request->ip()]);
      try {
        $checkStart = microtime(true);
        $userOrRedirect = self::_checkLogin();
        $this->logExecutionTime($checkStart, $action . '::_checkLogin', 'completed');
        if ($userOrRedirect instanceof RedirectResponse) {
          Log::warning("[$action] not authenticated", ['uri' => $request->getRequestUri()]);
          Log::debug("[$action] authentication failed", ['uri' => $request->getRequestUri()]);
          return $this->error('Not authenticated', 401);
        }
        $user = $userOrRedirect;
        $rawName   = basename($request->input('imgName', 'image.png'));
        $fileName  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $rawName);
        $allowedExt = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
          Log::warning("[$action] rejected file extension", ['ext' => $ext, 'fileName' => $fileName]);
          return $this->error('Invalid image file type.', 422);
        }
        $trackerId = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->input('trackerId', ''));
        if ($trackerId === '') {
          Log::warning("[$action] invalid trackerId", ['uri' => $request->getRequestUri()]);
          return $this->error('Invalid tracker ID.', 422);
        }
        $dir = storage_path("uploads/trackerImages/{$trackerId}/");
        $dirStart = microtime(true);
        if (!is_dir($dir)) {
          mkdir($dir, 0755, true);
          Log::debug("[$action] directory created", ['dir' => $dir]);
        }
        $this->logExecutionTime($dirStart, $action . '::mkdir', 'completed');
        $fileStart = microtime(true);
        $decoded = base64_decode($request->input('img'), true);
        if ($decoded === false || strlen($decoded) === 0) {
          Log::warning("[$action] invalid base64 image data");
          return $this->error('Invalid image data.', 422);
        }
        $filePath = $dir . $fileName;
        file_put_contents($filePath, $decoded);
        chmod($filePath, 0644);
        $this->logExecutionTime($fileStart, $action . '::fileSave', 'completed');
        Log::info("[$action] file saved", ['path' => $filePath, 'user_id' => $user?->id]);
        Log::debug("[$action] saved size", ['size' => @filesize($filePath) ?: 0]);
        $createStart = microtime(true);
        $photo = TrackPhoto::create([
          'track_id'                         => $trackerId,
          UsersConstants::COL_USER_ID        => $user?->id,
          'img_path'                         => "uploads/trackerImages/{$trackerId}/{$fileName}",
          ActivitiesConstants::COL_TSK_TIME  => $request->input(ActivitiesConstants::COL_TSK_TIME),
          'status'                           => 1
        ]);
        $this->logExecutionTime($createStart, $action . '::recordCreate', 'completed');
        Log::info("[$action] record created", ['photo_id' => $photo->id]);
        return $this->success($photo, 'Image uploaded.');
      } catch (\Throwable $e) {
        Log::error("[$action] exception", ['exception' => get_class($e), 'message' => $e->getMessage(), 'uri' => $request->getRequestUri()]);
        Log::debug("[$action] exception trace", ['trace' => $e->getTraceAsString()]);
        return defaultUndefinedException($request, $e, $action);
      }
    }, ['uri' => $request->getRequestUri(), 'method' => $request->getMethod(), 'ip' => $request->ip()]);
  }

  private static function validateInput(Request $req, array $rules): ?string
  {
    $output = SafeConsoleOutput::make();
    $class  = class_basename(self::class);
    $method = __FUNCTION__;
    $tag    = "{$class}::{$method}";
    Log::debug("{$tag} start", ['uri' => $req->getRequestUri(), 'rules' => $rules]);
    $output->writeln("{$tag} Validating input");
    $validator = Validator::make($req->all(), $rules);
    if ($validator->fails()) {
      $error = $validator->errors()->first();
      Log::warning("{$tag} failed", ['errors' => $validator->errors()->all()]);
      $output->writeln("{$tag} Validation error: {$error}");
      return $error;
    }
    Log::debug("{$tag} passed");
    $output->writeln("{$tag} Validation passed");
    return null;
  }
}
