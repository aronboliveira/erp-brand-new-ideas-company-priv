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

			$injected = false;

			if ($hasBodyEnd) {
				$content = preg_replace('~</body>~i', $scriptHtml . '</body>', $content, 1, $count);
				if ($count > 0) {
					$injected = true;
					Log::info('CheckMount: Injected before </body>');
				}
			} elseif ($hasHeadEnd) {
				$content = preg_replace('~</head>~i', $scriptHtml . '</head>', $content, 1, $count);
				if ($count > 0) {
					$injected = true;
					Log::info('CheckMount: Injected before </head>');
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
						Log::info('CheckMount: Emergency injected before <html>');
					}
				}

				if (!$injected) {
					$content = $scriptHtml . $content;
					$injected = true;
					Log::info('CheckMount: Emergency injected at start of content');
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
						Log::info('CheckMount: Injected after <head>');
					}
				}

				if (!$injected && preg_match('~<body[^>]*>~i', $content)) {
					$content = preg_replace('~(<body[^>]*>)~i', '$1' . $scriptHtml, $content, 1, $count);
					if ($count > 0) {
						$injected = true;
						Log::info('CheckMount: Injected after <body>');
					}
				}
			}

			if ($injected) {
				$response->setContent($content);
			} else {
				Log::error('CheckMount: Failed to inject script anywhere', [
					'url' => $request->fullUrl(),
					'has_html_tag' => $hasHtmlTag,
					'has_head_end' => $hasHeadEnd,
					'has_body_end' => $hasBodyEnd,
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
}
