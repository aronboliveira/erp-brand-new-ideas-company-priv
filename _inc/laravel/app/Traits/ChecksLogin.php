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
		$lang = DatabaseConstants::DEFAULT_LANG;
		$msgs = LangsConstants::DEFAULT_CLIENT_MESSAGES;
		try {
			/** @var User|null $user */
			$user = Auth::user();
			$lang = Utility::fetchUserLang(user: $user);
			$msgs = !empty(LangsConstants::ERROR_MESSAGES[$lang]) ? LangsConstants::ERROR_MESSAGES[$lang] : LangsConstants::DEFAULT_CLIENT_MESSAGES;
			if (is_null($user)) {
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
			return $user;
		} catch (\Throwable $e) {
			Log::notice('[ChecksLogin] Exception during authentication check', [
				'error' => $e->getMessage(),
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
							body.textContent = "{$msg}";
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
			return redirect()
				->route('login')
				->with('error', __(!empty($msgs['internal_error']) ? $msgs['internal_error'] : 'An internal error occurred. Please try again later.'));
		}
	}
}
