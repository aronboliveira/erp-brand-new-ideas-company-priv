<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\{Facades\Log, Str};

class CheckMount
{
	public function handle(Request $request, Closure $next)
	{
		$response = $next($request);

		try {
			if (!method_exists($response, 'getContent')) {
				return $response;
			}

			$contentType = $response->headers->get('Content-Type', '');
			if (!Str::contains($contentType, ['text/html', 'application/xhtml'])) {
				return $response;
			}

			$content = $response->getContent();

			if (empty($content)) {
				return $response;
			}

			if (stripos($content, 'checkMounted.js') !== false) {
				Log::debug('CheckMount: Script already present');
				return $response;
			}

			$scriptHtml = view('fragments.check_mounted')->render();

			$hasBodyEnd = stripos($content, '</body>') !== false;
			$hasHeadEnd = stripos($content, '</head>') !== false;
			$hasHtmlTag = stripos($content, '<html') !== false;
			$isPartialHtml = $this->isPartialHtml($content);

			$injected = false;

			if ($hasBodyEnd) {
				$content = preg_replace('~</body>~i', $scriptHtml . '</body>', $content, 1, $count);
				if ($count > 0) {
					$injected = true;
					Log::debug('CheckMount: Injected before </body>');
				}
			} elseif ($hasHeadEnd) {
				$content = preg_replace('~</head>~i', $scriptHtml . '</head>', $content, 1, $count);
				if ($count > 0) {
					$injected = true;
					Log::debug('CheckMount: Injected before </head>');
				}
			}

			if (!$injected && preg_match('/^[\s\n\t\r]*[0-9]+</', $content)) {
				Log::warning('CheckMount: Broken HTML detected (starts with number), using emergency injection', [
					'url' => $request->fullUrl(),
					'content_start' => substr($content, 0, 100)
				]);

				if ($hasHtmlTag) {
					$content = preg_replace('~<html~i', $scriptHtml . '<html', $content, 1, $count);
					if ($count > 0) {
						$injected = true;
						Log::debug('CheckMount: Emergency injected before <html>');
					}
				}

				if (!$injected) {
					$content = $scriptHtml . $content;
					$injected = true;
					Log::debug('CheckMount: Emergency injected at start of content');
				}
			}

			if (!$injected && ($hasHtmlTag || stripos($content, '<head') !== false)) {
				Log::warning('CheckMount: No standard injection points found, trying fallback', [
					'url' => $request->fullUrl()
				]);

				if (preg_match('~<head[^>]*>~i', $content)) {
					$content = preg_replace('~(<head[^>]*>)~i', '$1' . $scriptHtml, $content, 1, $count);
					if ($count > 0) {
						$injected = true;
						Log::debug('CheckMount: Injected after <head>');
					}
				}

				if (!$injected && preg_match('~<body[^>]*>~i', $content)) {
					$content = preg_replace('~(<body[^>]*>)~i', '$1' . $scriptHtml, $content, 1, $count);
					if ($count > 0) {
						$injected = true;
						Log::debug('CheckMount: Injected after <body>');
					}
				}
			}

			if (!$injected && $isPartialHtml) {
				Log::debug('CheckMount: Partial HTML detected, trying modal injection', [
					'url' => $request->fullUrl()
				]);

				if (preg_match('~<form[^>]*>~i', $content)) {
					$content = preg_replace('~(<form[^>]*>)~i', '$1' . $scriptHtml, $content, 1, $count);
					if ($count > 0) {
						$injected = true;
						Log::debug('CheckMount: Injected after <form> tag in modal');
					}
				}

				if (!$injected && preg_match('~<[^>]+class=["\'][^"\']*modal-body[^"\']*["\'][^>]*>~i', $content)) {
					$content = preg_replace(
						'~(<[^>]+class=["\'][^"\']*modal-body[^"\']*["\'][^>]*>)~i',
						$scriptHtml . '$1',
						$content,
						1,
						$count
					);
					if ($count > 0) {
						$injected = true;
						Log::debug('CheckMount: Injected before modal-body element');
					}
				}

				if (!$injected && preg_match('~<[^>]+class=["\'][^"\']*modal-header[^"\']*["\'][^>]*>~i', $content)) {
					$content = preg_replace(
						'~(<[^>]+class=["\'][^"\']*modal-header[^"\']*["\'][^>]*>)~i',
						$scriptHtml . '$1',
						$content,
						1,
						$count
					);
					if ($count > 0) {
						$injected = true;
						Log::debug('CheckMount: Injected before modal-header element');
					}
				}

				if (!$injected) {
					$content = $scriptHtml . $content;
					$injected = true;
					Log::debug('CheckMount: Injected at start of partial HTML');
				}
			}

			if ($injected) {
				Log::info('CheckMount: Script injection successful', [
					'url' => $request->fullUrl()
				]);
				$response->setContent($content);
			} else {
				Log::error('CheckMount: Failed to inject script anywhere', [
					'url' => $request->fullUrl(),
					'has_html_tag' => $hasHtmlTag,
					'has_head_end' => $hasHeadEnd,
					'has_body_end' => $hasBodyEnd,
					'is_partial_html' => $isPartialHtml,
					'content_start' => substr($content, 0, 200)
				]);
			}
		} catch (\Throwable $e) {
			Log::error('CheckMount: Exception', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
		}
		return $response;
	}

	/**
	 * Check if the content is partial HTML (modal/AJAX response)
	 */
	private function isPartialHtml(string $content): bool
	{
		$trimmed = trim($content);

		if (preg_match('~^<(form|div|section|article|main|aside|nav|header|footer|table|ul|ol|dl|fieldset|select|input|textarea|button|span|p|h[1-6])~i', $trimmed)) {
			return true;
		}

		$hasDoctype = stripos($trimmed, '<!DOCTYPE') !== false;
		$hasHtmlTag = stripos($trimmed, '<html') !== false;

		if (!$hasDoctype && !$hasHtmlTag) {
			return true;
		}

		return false;
	}
}
