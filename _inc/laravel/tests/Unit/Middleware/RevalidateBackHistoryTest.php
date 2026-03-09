<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RevalidateBackHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(RevalidateBackHistory::class)]
#[Group('middleware')]
#[Group('revalidate-back-history')]
class RevalidateBackHistoryTest extends TestCase
{
	private RevalidateBackHistory $middleware;

	protected function setUp(): void
	{
		parent::setUp();
		$this->middleware = new RevalidateBackHistory();
	}

	// ───────── CORS headers ─────────

	#[Test]
	public function sets_access_control_allow_origin_header(): void
	{
		$request  = Request::create('/api/data', 'GET');
		$response = $this->middleware->handle($request, fn() => new Response('ok', 200));

		$this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
	}

	#[Test]
	public function sets_access_control_allow_methods_header(): void
	{
		$request  = Request::create('/api/data', 'GET');
		$response = $this->middleware->handle($request, fn() => new Response('ok', 200));

		$methods = $response->headers->get('Access-Control-Allow-Methods');
		$this->assertNotNull($methods);
		$this->assertStringContainsString('POST', $methods);
		$this->assertStringContainsString('GET', $methods);
		$this->assertStringContainsString('OPTIONS', $methods);
		$this->assertStringContainsString('PUT', $methods);
		$this->assertStringContainsString('DELETE', $methods);
	}

	#[Test]
	public function sets_access_control_allow_headers(): void
	{
		$request  = Request::create('/api/data', 'GET');
		$response = $this->middleware->handle($request, fn() => new Response('ok', 200));

		$allowHeaders = $response->headers->get('Access-Control-Allow-Headers');
		$this->assertNotNull($allowHeaders);
		$this->assertStringContainsString('Content-Type', $allowHeaders);
		$this->assertStringContainsString('Accept', $allowHeaders);
		$this->assertStringContainsString('Authorization', $allowHeaders);
		$this->assertStringContainsString('X-Requested-With', $allowHeaders);
		$this->assertStringContainsString('Application', $allowHeaders);
	}

	// ───────── Applied to various responses ─────────

	#[Test]
	public function applies_cors_headers_to_post_request(): void
	{
		$request  = Request::create('/api/data', 'POST');
		$response = $this->middleware->handle($request, fn() => new Response('created', 201));

		$this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
		$this->assertSame(201, $response->getStatusCode());
	}

	#[Test]
	public function applies_cors_headers_to_json_response(): void
	{
		$request = Request::create('/api/data', 'GET');
		$inner   = new Response(
			json_encode(['data' => 'test']),
			200,
			['Content-Type' => 'application/json']
		);
		$response = $this->middleware->handle($request, fn() => $inner);

		$this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
		$this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
	}

	#[Test]
	public function preserves_original_response_content(): void
	{
		$request  = Request::create('/api/data', 'GET');
		$body     = '<html><body>Hello</body></html>';
		$response = $this->middleware->handle($request, fn() => new Response($body, 200));

		$this->assertSame($body, $response->getContent());
		$this->assertSame(200, $response->getStatusCode());
	}

	#[Test]
	public function applies_cors_to_empty_response(): void
	{
		$request  = Request::create('/api/ping', 'OPTIONS');
		$response = $this->middleware->handle($request, fn() => new Response('', 204));

		$this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
		$this->assertSame(204, $response->getStatusCode());
	}

	// ───────── performance ─────────

	#[Test]
	public function handle_executes_within_acceptable_time(): void
	{
		$request = Request::create('/api/perf', 'GET');

		$start = hrtime(true);
		$response = $this->middleware->handle($request, fn() => new Response('ok', 200));
		$elapsed  = (hrtime(true) - $start) / 1e6;

		$this->assertLessThan(50, $elapsed, "handle() should complete under 50ms, took {$elapsed}ms");
		$this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
	}
}
