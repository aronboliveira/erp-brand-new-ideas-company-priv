<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SecureHeaders;
use Illuminate\Http\{Request, Response};
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(SecureHeaders::class)]
#[Group('middleware')]
#[Group('secure-headers')]
class SecureHeadersTest extends TestCase
{
	private function runMiddleware(?Response $downstream = null): Response
	{
		$middleware = new SecureHeaders();
		$request = Request::create('/test', 'GET');
		$downstream ??= new Response('OK', 200);
		return $middleware->handle($request, fn() => $downstream);
	}

	// ───────── Sets security headers ─────────

	#[Test]
	public function sets_x_content_type_options(): void
	{
		$response = $this->runMiddleware();
		$this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
	}

	#[Test]
	public function sets_x_frame_options(): void
	{
		$response = $this->runMiddleware();
		$this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
	}

	#[Test]
	public function sets_content_security_policy(): void
	{
		$response = $this->runMiddleware();
		$csp = $response->headers->get('Content-Security-Policy');
		$this->assertNotNull($csp);
		$this->assertStringContainsString("default-src 'self'", $csp);
		$this->assertStringContainsString("script-src", $csp);
		$this->assertStringContainsString("style-src", $csp);
	}

	// ───────── Headers applied to all responses ─────────

	#[Test]
	public function headers_applied_to_json_response(): void
	{
		$json = new Response('{"ok":true}', 200, ['Content-Type' => 'application/json']);
		$response = $this->runMiddleware($json);
		$this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
		$this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
	}

	#[Test]
	public function headers_applied_to_empty_response(): void
	{
		$response = $this->runMiddleware(new Response('', 204));
		$this->assertNotNull($response->headers->get('X-Content-Type-Options'));
	}

	// ───────── Performance ─────────

	#[Test]
	public function performance(): void
	{
		$middleware = new SecureHeaders();
		$request = Request::create('/test', 'GET');
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			$middleware->handle($request, fn() => new Response('OK', 200));
		}
		$perCall = (hrtime(true) - $start) / 1e6 / 1000;
		$this->assertLessThan(10.0, $perCall);
	}
}
