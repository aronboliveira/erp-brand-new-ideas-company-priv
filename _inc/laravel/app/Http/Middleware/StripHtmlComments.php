<?php

namespace App\Http\Middleware;

use Closure;
use App\Config\Constants\SettingsConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StripHtmlComments
{
	use MeasuresPerformance;
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request   $request
	 * @param  \Closure                   $next
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$startTime = microtime(true);
		$class = class_basename(static::class);
		try {
			$response = null;
			Log::debug($class . ' middleware started', [
				'url' => $request->fullUrl(),
				'method' => $request->method(),
				'user_agent' => $request->userAgent(),
				'ip' => $request->ip(),
				'timestamp' => now()->toISOString(),
				'next'   => $this->searchForNext($request)
			]);
			try {
				/** @var Response $response */
				$response = $next($request);
			} catch (\Throwable $e) {
				$errCtx = ['exception' => get_class($e), 'message' => $e->getMessage()];
				Log::error(get_class($this) . " encountered downstream error", $errCtx);
				Log::channel(SettingsConstants::ERR_TRACE)->debug(
					get_class($this) . " encountered downstream error",
					array_merge($errCtx, ['trace' => $e->getTraceAsString()])
				);
				throw $e;
			}
			$status = $response->getStatusCode();
			if ($status >= 400) throw new \RuntimeException(
				"Failed response status: {$response->getStatusCode()}"
			);
			Log::debug('Response obtained from next middleware', [
				'status_code' => $status,
				'content_type' => $response->headers->get('Content-Type', 'unknown'),
				'content_length_before' => strlen($response->getContent() ?? ''),
				'has_content' => !empty($response->getContent())
			]);
			$contentType = $response->headers->get('Content-Type', '');
			if (!str_contains($contentType, 'text/html')) {
				Log::debug('Skipping HTML comment stripping - not HTML content', [
					'content_type' => $contentType,
					'status_code' => $response->getStatusCode()
				]);
				$this->logExecutionTime($startTime, 'skipped');
				return $response;
			}
			Log::debug('Processing HTML content for comment removal', [
				'content_type' => $contentType,
				'status_code' => $response->getStatusCode()
			]);
			$originalContent = $response->getContent();
			if (empty($originalContent)) {
				Log::warning('Response content is empty, skipping processing');
				$this->logExecutionTime($startTime, 'empty_content');
				return $response;
			}
			$commentCount = preg_match_all('/<!--.*?-->/s', $originalContent);
			Log::debug('HTML comments analysis', [
				'original_content_length' => strlen($originalContent),
				'comment_count' => $commentCount,
				'has_comments' => $commentCount > 0
			]);
			if ($commentCount === 0) {
				Log::debug('No HTML comments found, skipping processing');
				$this->logExecutionTime($startTime, 'no_comments');
				return $response;
			}
			$cleanContent = preg_replace('/<!--.*?-->/s', '', $originalContent);
			if ($cleanContent === null) {
				Log::error('preg_replace returned null - regex processing failed', [
					'preg_last_error' => preg_last_error(),
					'preg_last_error_msg' => $this->getPregErrorMessage(preg_last_error()),
					'original_content_length' => strlen($originalContent)
				]);
				$this->logExecutionTime($startTime, 'regex_failed');
				return $response;
			}
			$response->setContent($cleanContent);
			$bytesRemoved = strlen($originalContent) - strlen($cleanContent);
			$compressionRatio = $bytesRemoved > 0 ? round(($bytesRemoved / strlen($originalContent)) * 100, 2) : 0;
			Log::debug('HTML comments successfully stripped', [
				'original_length' => strlen($originalContent),
				'cleaned_length' => strlen($cleanContent),
				'bytes_removed' => $bytesRemoved,
				'compression_ratio_percent' => $compressionRatio,
				'comments_removed' => $commentCount,
				'processing_successful' => true,
				'next'   => $this->searchForNext($request)
			]);
			$this->logExecutionTime($startTime, 'success');
			return $response;
		} catch (Throwable $e) {
			Log::notice($class . ' middleware failed. This is not fatal, but be sure to check the client.', [
				'error_message' => $e->getMessage(),
				'error_code' => $e->getCode(),
				'error_file' => $e->getFile(),
				'error_line' => $e->getLine(),
				'url' => $request->fullUrl() ?? 'unknown',
				'method' => $request->method() ?? 'unknown',
				'user_agent' => $request->userAgent() ?? 'unknown',
				'ip' => $request->ip() ?? 'unknown'
			]);
			$this->logExecutionTime($startTime, 'error');
			try {
				Log::debug($class . '- moving to next...');
				return $response ?? $next($request);
			} catch (Throwable $fallbackError) {
				Log::critical($class . ' middleware detected downstream errors', [
					'original_error' => $e->getMessage(),
					'fallback_error' => $fallbackError->getMessage(),
					'url' => $request->fullUrl() ?? 'unknown'
				]);
				Log::debug("{$class} ingested a throwable. Throwing to upstream...");
				throw $e;
			}
		}
	}

	private function getPregErrorMessage(int $errorCode): string
	{
		$errors = [
			PREG_NO_ERROR => 'No error',
			PREG_INTERNAL_ERROR => 'Internal error',
			PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exceeded',
			PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exceeded',
			PREG_BAD_UTF8_ERROR => 'Bad UTF8 error',
			PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF8 offset error',
			PREG_JIT_STACKLIMIT_ERROR => 'JIT stack limit error'
		];

		return $errors[$errorCode] ?? 'Unknown error';
	}
}
