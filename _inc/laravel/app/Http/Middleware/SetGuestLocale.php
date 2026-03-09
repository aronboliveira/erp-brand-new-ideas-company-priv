<?php

namespace App\Http\Middleware;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants};
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{App, Cache, Log};

/**
 * Sets the application locale for all users:
 * - Authenticated users: reads from their DB 'lang' column (with cache/cookie fallback).
 * - Guest users: reads from cookie / route parameter / default.
 *
 * This ensures locale is always set in the web middleware group,
 * regardless of whether route-level middleware (e.g. XSS) is applied.
 */
class SetGuestLocale
{
	private const COOKIE_NAME = 'erp_locale';
	private const SUPPORTED    = [
		'ar',
		'da',
		'de',
		'en',
		'es',
		'fr',
		'he',
		'it',
		'ja',
		'nl',
		'pl',
		'pt-br',
		'pt',
		'ru',
		'tr',
		'zh',
	];

	public function handle(Request $request, Closure $next)
	{
		try {
			if (auth()->check()) {
				// Authenticated: read locale from user's DB 'lang' column
				$user = auth()->user();
				$lang = $user->{UsersConstants::COL_LG}
					?: Cache::get('user_' . $user->id . '_lang')
					?: $request->cookie(self::COOKIE_NAME)
					?: DC::DEFAULT_LANG;

				if (in_array($lang, self::SUPPORTED, true)) {
					App::setLocale($lang);
				}
			} else {
				// Guest: route parameter > cookie > default (DC::DEFAULT_LANG = 'en')
				// NOTE: getPreferredLanguage() was removed because it picks the first
				//       SUPPORTED entry alphabetically ('ar') when the browser sends
				//       no Accept-Language header, causing the login page to render
				//       in Arabic for every new visitor.
				$lang = $request->route('lang')
					?: $request->cookie(self::COOKIE_NAME)
					?: DC::DEFAULT_LANG;

				if (in_array($lang, self::SUPPORTED, true)) {
					App::setLocale($lang);
				}
			}
		} catch (\Throwable $e) {
			Log::debug('SetGuestLocale: ' . $e->getMessage());
		}

		$response = $next($request);

		// Persist the chosen locale in a cookie (1 year)
		try {
			$currentLocale = App::getLocale();
			if (method_exists($response, 'withCookie')) {
				$response->withCookie(cookie(self::COOKIE_NAME, $currentLocale, 525600, '/', null, false, false));
			}
		} catch (\Throwable $e) {
			Log::debug('SetGuestLocale cookie: ' . $e->getMessage());
		}

		return $response;
	}
}
