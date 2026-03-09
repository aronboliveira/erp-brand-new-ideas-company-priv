<?php

namespace App\Traits;

use App\Config\Constants\{
	DatabaseConstants,
	LangsConstants,
	PermissionsConstants,
	UsersConstants
};
use App\Jobs\RedirectWatcherJob;
use App\Models\Utility;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Cache, Gate, Log, Route};
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\{MethodNotAllowedHttpException, NotFoundHttpException};

trait ChecksPermissions
{
	protected static function guard(
		Request $req,
		string $perm,
		?string $redirectRoute = null,
		?\Closure $customAction = null,
		?bool $autoBack = true
	): RedirectResponse|JsonResponse|true {
		$user = $req->user();
		$lang = Utility::fetchUserLang($user, $req);
		$msgs = !empty(LangsConstants::ERROR_MESSAGES[$lang]) ? LangsConstants::ERROR_MESSAGES[$lang] : LangsConstants::DEFAULT_CLIENT_MESSAGES;
		Log::info(static::class . "::" . __FUNCTION__ . " checking permission '{$perm}' for " . ($user?->type ?? 'guest') . ' ' . ($user?->id), [
			'ip'          => $req->ip(),
			'user_id'     => $user?->id,
			'type'        => $user?->type,
			'perm'        => $perm,
			'session_id'  => session()->getId(),
			'referrer'    => Utility::getReferrer($req),
			'route'       => $req->route()?->getName() ?? '#UNIDENTIFIED',
			'is_banned'		=> $user?->is_banned
		]);
		if (!$user) {
			Log::notice(static::class . " user not found, redirecting to login");
			return redirect('/login')->with('error', $msgs['login_required']);
		}
		if ($user[UsersConstants::COL_IB] == 1) {
			Log::warning(static::class . " user {$user->id} is banned, denying '{$perm}'");
			return self::redirectUnauthorized(
				'/login',
				'BannedUser',
				$msgs['banned_user'],
				403
			);
		}
		$denied = true;
		try {
			$hasPermission = $user->can($perm) || Gate::forUser($user)->allows($perm) || (method_exists($user, 'hasPermissionTo')
				&& $user->hasPermissionTo($perm));
		} catch (\Throwable $permErr) {
			$hasPermission = false;
			Log::warning(static::class . " permission check failed for '{$perm}'", ['error' => $permErr->getMessage()]);
		}
		if (!$hasPermission) {
			if ($user[UsersConstants::COL_TP] === PermissionsConstants::SA) {
				$denied = false;
				Log::notice(static::class . " super-admin bypass without '{$perm}' permission", [
					'id' => $user->id,
					'type' => $user[UsersConstants::COL_TP],
					'email' => $user[UsersConstants::COL_EM],
					'session' => session()->getId(),
					'ip' => $req->ip(),
					'referrer' => Utility::getReferrer($req),
				]);
			}
		} else $denied = false;
		if ($denied) {
			if ($autoBack) return redirect()->back()->with(
				'error',
				$msgs['permission_denied'] ?? 'You do not have permission for that. Redirecting shortly...'
			);
			$redirectRoute ??= request()->url() === url('/') ? '/' : url()->previous();
			try {
				if (!str_starts_with($redirectRoute, '/')) {
					Route::has($redirectRoute)
						? $redirectRoute = Route::getRoutes()->match(Request::create(route($redirectRoute)))->getName() ?? url()->previous()
						: $redirectRoute = url()->previous();
				} else if (!Utility::isValidRouteUrl($redirectRoute)) {
					Log::warning(static::class . " invalid redirect route '{$redirectRoute}', using previous URL");
					$redirectRoute = url()->previous();
				}
			} catch (NotFoundHttpException $e) {
				Log::warning(static::class . ' route not found', [
					'exception' => NotFoundHttpException::class,
					'message'   => $e->getMessage(),
					'path'      => $redirectRoute,
				]);
				return redirect()->back()->with('error', !empty($msgs['route_not_found']) ? $msgs['route_not_found'] : 'Route not found. Redirecting shortly...');
			} catch (MethodNotAllowedHttpException $e) {
				Log::warning(static::class . ' method not allowed', [
					'exception' => MethodNotAllowedHttpException::class,
					'message'   => $e->getMessage(),
					'path'      => $redirectRoute,
				]);
				return redirect()->back()->with('error', !empty($msgs['method_not_allowed']) ? $msgs['method_not_allowed'] : 'Method not allowed. Redirecting shortly...');
			} catch (\Throwable $e) {
				Log::error(static::class . ' unexpected error', [
					'exception' => get_class($e),
					'message'   => $e->getMessage(),
					'file'      => $e->getFile(),
					'line'      => $e->getLine(),
					'path'      => $redirectRoute,
				]);
				return redirect()->back()->with('error', !empty($msgs['internal_error']) ? $msgs['internal_error'] : 'An internal error occurred. Redirecting shortly...');
			}
			if (!$customAction)
				return self::redirectUnauthorized(
					$redirectRoute,
					null,
					!empty($msgs['internal_error']) ? $msgs['internal_error'] : 'You do not have permission for that. Redirecting shortly...',
				);
			elseif (is_callable($customAction)) {
				try {
					$result = $customAction($req, $perm);
					if ($result instanceof RedirectResponse) {
						$url = $result->getTargetUrl();
						if (!Utility::isValidRouteUrl($url)) {
							Log::warning(static::class . " custom action returned invalid URL '{$url}', using default");
							return self::redirectUnauthorized(
								$redirectRoute,
								null,
								!empty($msgs['internal_error']) ? $msgs['internal_error'] : 'You do not have permission for that. Redirecting shortly...'
							);
						}
						return self::redirectUnauthorized(
							$url,
							null,
							!empty($msgs['internal_error']) ? $msgs['internal_error'] : 'You do not have permission for that. Redirecting shortly...'
						);
					}
					return $result;
				} catch (\Throwable $e) {
					Log::error(static::class . ' error in custom action', [
						'exception' => get_class($e),
						'message'   => $e->getMessage(),
						'file'      => $e->getFile(),
						'line'      => $e->getLine(),
					]);
					return redirect()->back()
						->with('error', !empty($msgs['internal_error']) ? $msgs['internal_error'] : 'An internal error occurred while processing your request. Redirecting shortly...');
				}
			}
		}
		Log::info(static::class . " granted '{$perm}' to user {$user->id}", [
			'type' => $user[UsersConstants::COL_TP],
			'email' => $user[UsersConstants::COL_EM],
			'session' => session()->getId(),
			'ip' => $req->ip(),
			'referrer' => Utility::getReferrer($req),
		]);
		return true;
	}

	private static function redirectUnauthorized(string $redirectRoute = '/login', $errorType = 'Unauthorized', $msg = 'You do not have permition for that. Redirecting shortly...', $code = 401, $delay = 3): JsonResponse
	{
		$sessionId = session()->getId();
		$currentRoute = request()->route()?->getName() ?? request()->url();
		do $watcherId = Str::uuid();
		while (Cache::has("redirect_watcher_{$sessionId}_{$watcherId}"));
		$watcherKey = "redirect_watcher_{$sessionId}_{$watcherId}";
		$phpDelay = $delay + 1;
		Cache::put($watcherKey, [
			'current_route' => $currentRoute,
			'redirect_route' => $redirectRoute,
			'created_at' => now(),
			'delay_seconds' => $phpDelay,
		], now()->addSeconds($phpDelay * 2));
		RedirectWatcherJob::dispatch($sessionId, $watcherKey, $redirectRoute)
			->delay(now()->addSeconds($phpDelay));
		$uuid   = Str::uuid();
		$jsDelay = $delay * 1000;
		$safeRedirectRoute = htmlspecialchars($redirectRoute, ENT_QUOTES, 'UTF-8');
		$script = <<<HTML
			<script id="{$uuid}">
				setTimeout(() => {
					window.location.href = "{$safeRedirectRoute}";
				}, {$jsDelay});
			</script>
		HTML;
		return response()->json([
			'error'    => $errorType,
			'message'  => $msg,
			'redirect' => $redirectRoute,
			'delay_ms' => $jsDelay,
			'snippet'  => $script,
		], $code);
	}
}
