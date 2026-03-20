<?php

namespace App\Http\Middleware;
use App\Helpers\SafeConsoleOutput;

use Closure;
use Illuminate\{
	Http\Request,
	Support\Facades\Log
};
use Symfony\Component\HttpFoundation\Response;

final class SecureHeaders
{
	use MeasuresPerformance;
	private const HEADERS = [
		'X-Content-Type-Options'     => 'nosniff',
		'X-Frame-Options'            => 'DENY',
		// TODO: Add nonce-based CSP to replace 'unsafe-inline' for scripts
		'Strict-Transport-Security'  => 'max-age=31536000; includeSubDomains',
		'Content-Security-Policy' =>
		"default-src 'self'; connect-src 'self' wss://*.pusher.com https://*.pusher.com https://cdn.jsdelivr.net https://unpkg.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://js.pusher.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net data: https://fonts.gstatic.com;",
		// ! ALERT !! // TODO REMOVE LATER AND INCLUDE NONCE !!!
	];

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 * @return Response
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$method = __FUNCTION__;
		return $this->measure($request, function (Request $request) use ($next, $method) {
			$class  = class_basename(static::class);
			$output = SafeConsoleOutput::make();
			$whoIsNext = $this->searchForNext($request);
			Log::debug("{$class}::{$method} start", [
				'uri'     => $request->getRequestUri(),
				'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
				'method'  => $request->getMethod(),
				'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
				'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
				'candidate_headers' => self::HEADERS,
				'next'   => $whoIsNext,
				'bearer_present' => (bool)$request->bearerToken(),
			]);
			$output->writeln("[{$class}] Applying secure headers to {$request->getRequestUri()}");
			try {
				try {
					$response = $next($request);
				} catch (\Throwable $e) {
					Log::error("{$class} encountered downstream error", [
						'exception' => get_class($e),
						'message'   => $e->getMessage(),
					]);
					throw $e;
				}
				foreach (self::HEADERS as $key => $value) {
					try {
						$response->headers->set($key, $value);
					} catch (\Throwable $e) {
						Log::warning("{$class}::{$method} failed to set header {$key} as {$value}", [
							'exception' => $e->getMessage(),
						]);
						throw $e;
					}
				}
				Log::info("{$class}::{$method} secure_headers_set", [
					'uri'       => $request->getRequestUri(),
				]);
				Log::debug("{$class}::{$method} applied", [
					'ip'        => $request->ip(),
					'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
					'headers'   => array_keys(self::HEADERS),
					'method'    => $request->getMethod(),
					'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
					'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
					'full-path' => $request->fullUrl(),
					'params'    => $request->route()?->parameters() ?? [],
					'status'    => $response->getStatusCode(),
					'next'   => $this->searchForNext($request)
				]);
				$output->writeln("[{$class}] Secure headers set successfully");
				return $response;
			# PULL REQUEST START — Remoção do catch genérico \Throwable que mascarava exceções downstream como abort(403)
			// } catch (\Throwable $e) {
			// 	Log::error("{$class}::{$method} unexpected error", [
			// 		'exception' => get_class($e),
			// 		'message'   => $e->getMessage(),
			// 		'uri'       => $request->getRequestUri(),
			// 		'method'    => $request->getMethod(),
			// 		'headers'   => array_keys(self::HEADERS),
			// 		'next'   => $this->searchForNext($request)
			// 	]);
			// 	$msg = "[{$class}] Failed to apply headers: {$e->getMessage()}";
			// 	app()->runningInConsole()
			// 		? $output->writeln("<error> {$msg} </error>")
			// 		: $output->writeln("## HEADERS ERROR: {$msg}");
			// 	Log::debug("{$class} ingested a throwable. Aborting.");
			// 	abort(403, 'Security headers middleware failed');
			// }
			# PULL REQUEST END
			} catch (\Throwable $e) {
				throw $e;
			}
		});
	}
}
