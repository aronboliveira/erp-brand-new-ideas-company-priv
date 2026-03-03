<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants, LangsConstants};
use App\Models\{User, Utility};
use Illuminate\Http\{
	JsonResponse,
	RedirectResponse,
	Response
};
use Illuminate\Support\Facades\{
	Auth,
	Log
};
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Helpers\SafeConsoleOutput;

trait ChecksLogin
{
	/**
	 * Check if the user is logged in.
	 *
	 * @return \App\Models\User|\Illuminate\Http\RedirectResponse
	 *   - Returns the authenticated User model if logged in.
	 *   - Returns a RedirectResponse to login page if not authenticated.
	 */
	protected static function _checkLogin(bool $haltRedirect = false): Response|RedirectResponse|JsonResponse|View|User|false
	{
		$output = SafeConsoleOutput::make();
		$function = __FUNCTION__;
		$msg = 'Checking authentication in ' . $function . ', called by ' . get_called_class() .
			' using ' . (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'UNKNOWN_METHOD');
		app()->runningInConsole() ?
			$output->writeln('<comment> ' . $msg . ' </comment>') :
			$output->writeln("## CHECK-LOGIN: {$msg}");
		Log::debug('[ChecksLogin] Starting authentication check: ' . $msg, ['method' => __METHOD__]);
		$lang = DatabaseConstants::DEFAULT_LANG;
		try {
			/** @var User|null $user */
			$user = Auth::user();
			$lang = Utility::fetchUserLang(user: $user);
			$msgs = !empty(LangsConstants::ERROR_MESSAGES[$lang]) ? LangsConstants::ERROR_MESSAGES[$lang] : LangsConstants::DEFAULT_CLIENT_MESSAGES;
			if (is_null($user)) {
				$failMsg = 'User not authenticated';
				app()->runningInConsole() ?
					$output->writeln('<error> ' . $failMsg . ' </error>') :
					$output->writeln("## CHECK-LOGIN: {$failMsg}");
				Log::debug('[ChecksLogin] User not authenticated in ' . __FUNCTION__, [
					'ip' => request()->ip(),
					'session_id' => session()->getId(),
					'user_agent' => request()->userAgent()
				]);
				$path = trim(request()->path(), '/');
				if (preg_match('#^login(/[^/]+)?$#', $path)) {
					$notFoundMsg = !empty($msgs['invalid_user']) ? $msgs['invalid_user'] : 'User not found.';
					return response()->view('errors.login_error', [
						'message' => $notFoundMsg,
						'title' => 'Login Error'
					], 401);
				}
				if ($haltRedirect) return false;
				Log::notice('[ChecksLogin] redirecting to login...');
				return redirect()
					->route('login')
					->with('error', __(!empty($msgs['must_login']) ? $msgs['must_login'] : 'Login required.'));
			}
			$successMsg = 'User authenticated';
			app()->runningInConsole() ?
				$output->writeln('<info> ' . $successMsg . ' </info>') :
				$output->writeln("## AUTH: {$successMsg}");
			Log::debug('[ChecksLogin] User authenticated successfully in ' . __FUNCTION__, [
				'user_id' => $user?->id,
				'email' => $user?->email ?? 'no_email',
				'session_id' => session()->getId()
			]);
			return $user;
		} catch (\Throwable $e) {
			$errorMsg = 'Authentication check failed in ' . __FUNCTION__;
			app()->runningInConsole() ?
				$output->writeln('<error> ' . $errorMsg . ' </error>') :
				$output->writeln("## CHECK-LOGIN: {$errorMsg}");
			Log::notice('[ChecksLogin] Exception during authentication check in ' . __FUNCTION__, [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'session_id' => session()->getId()
			]);
			$path = trim(request()->path(), '/');
			if (preg_match('#^login(/[^/]+)?$#', $path)) {
				$msg = addslashes(__(!empty($msgs['credential_check_failed']) ? $msgs['credential_check_failed'] : 'An internal error occurred. Please try again later.'));
				$uuid = Str::uuid();
				$snippet = <<<HTML
				<script id="{$uuid}">
					(function() {
							const toast = document.getElementById('loginToast');
							if (!toast) {
									console.warn('Toast element not found');
									return;
							}
							const body = toast.querySelector('.toast-body');
							if (!body) {
									console.warn('Toast body element not found');
									return;
							}
							const delay = 5000;
							const bs = new bootstrap.Toast(toast, { delay });
							body.textContent = {$msg};
							toast.style.display = 'block';
							bs.show();
							const handleHidden = function() {
									body.textContent = '';
									toast.style.display = 'none';
									toast.removeEventListener('hidden.bs.toast', handleHidden);
							};
							toast.addEventListener('hidden.bs.toast', handleHidden);
							setTimeout(function() {
									document.getElementById('{$uuid}')?.remove();
							}, delay * 1.25);
					})();
				</script>
				HTML;
				return response()->json([
					'error'   => "Page failed to load",
					'snippet' => $snippet,
					'status'  => 500,
				]);
			}
			if ($haltRedirect) return false;
			Log::debug('[ChecksLogin] redirecting to login...');
			return redirect()
				->route('login')
				->with('error', __(!empty($msgs['internal_error']) ? $msgs['internal_error'] : 'An internal error occurred. Please try again later.'));
		}
	}
}
