<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RecordLanding;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(RecordLanding::class)]
#[Group('middleware')]
#[Group('record-landing')]
class RecordLandingTest extends TestCase
{
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	private RecordLanding $middleware;

	protected function setUp(): void
	{
		parent::setUp();
		$this->middleware = new RecordLanding();
	}

	// ───────── handle() passthrough ─────────

	#[Test]
	public function handle_passes_request_through(): void
	{
		$request  = Request::create('/test', 'GET');
		$expected = new Response('ok', 200);

		$result = $this->middleware->handle($request, fn() => $expected);

		$this->assertSame($expected, $result);
	}

	#[Test]
	public function handle_does_not_modify_response(): void
	{
		$request  = Request::create('/test', 'POST');
		$expected = new Response('posted', 201);

		$result = $this->middleware->handle($request, fn() => $expected);

		$this->assertSame('posted', $result->getContent());
		$this->assertSame(201, $result->getStatusCode());
	}

	// ───────── terminate() caching ─────────

	#[Test]
	public function terminate_caches_route_name_for_get_with_success_status(): void
	{
		Cache::shouldReceive('put')
			->once()
			->withArgs(function (string $key, array $value, $ttl) {
				return str_starts_with($key, 'last_route_')
					&& $value === ['name' => 'dashboard']
					&& $ttl !== null;
			});

		$route = new Route('GET', '/dashboard', []);
		$route->name('dashboard');

		$request = Request::create('/dashboard', 'GET');
		$request->setRouteResolver(fn() => $route);

		Session::shouldReceive('getId')->andReturn('sess-abc123');

		$response = new Response('ok', 200);
		$this->middleware->terminate($request, $response);
	}

	#[Test]
	public function terminate_skips_cache_for_non_get_methods(): void
	{
		Cache::shouldReceive('put')->never();

		$route = new Route('POST', '/form', []);
		$route->name('form.submit');

		$request = Request::create('/form', 'POST');
		$request->setRouteResolver(fn() => $route);

		$response = new Response('ok', 200);
		$this->middleware->terminate($request, $response);
	}

	#[Test]
	#[DataProvider('errorStatusCodesProvider')]
	public function terminate_skips_cache_for_error_status(int $status): void
	{
		Cache::shouldReceive('put')->never();

		$route = new Route('GET', '/error', []);
		$route->name('error.page');

		$request = Request::create('/error', 'GET');
		$request->setRouteResolver(fn() => $route);

		$response = new Response('error', $status);
		$this->middleware->terminate($request, $response);
	}

	public static function errorStatusCodesProvider(): array
	{
		return [
			'400 Bad Request'     => [400],
			'401 Unauthorized'    => [401],
			'403 Forbidden'       => [403],
			'404 Not Found'       => [404],
			'419 Token Expired'   => [419],
			'422 Unprocessable'   => [422],
			'500 Server Error'    => [500],
			'502 Bad Gateway'     => [502],
			'503 Unavailable'     => [503],
		];
	}

	#[Test]
	public function terminate_skips_cache_when_route_has_no_name(): void
	{
		Cache::shouldReceive('put')->never();

		$route = new Route('GET', '/unnamed', []);
		// Do NOT call $route->name(...)

		$request = Request::create('/unnamed', 'GET');
		$request->setRouteResolver(fn() => $route);

		$response = new Response('ok', 200);
		$this->middleware->terminate($request, $response);
	}

	#[Test]
	public function terminate_skips_cache_when_no_route_at_all(): void
	{
		Cache::shouldReceive('put')->never();

		$request  = Request::create('/no-route', 'GET');
		$response = new Response('ok', 200);

		$this->middleware->terminate($request, $response);
	}

	#[Test]
	public function terminate_caches_for_2xx_and_3xx_success_codes(): void
	{
		$successCodes = [200, 201, 204, 301, 302, 304];
		$callCount    = 0;

		foreach ($successCodes as $code) {
			Cache::shouldReceive('put')
				->once()
				->withArgs(function (string $key, array $value) {
					return str_starts_with($key, 'last_route_')
						&& isset($value['name']);
				});

			$route = new Route('GET', '/page', []);
			$route->name('page.view');

			$request = Request::create('/page', 'GET');
			$request->setRouteResolver(fn() => $route);

			Session::shouldReceive('getId')->andReturn('sess-xyz');

			$response = new Response('', $code);
			$this->middleware->terminate($request, $response);
			$callCount++;
		}

		$this->assertSame(count($successCodes), $callCount);
	}

	// ───────── performance ─────────

	#[Test]
	public function terminate_executes_within_acceptable_time(): void
	{
		Cache::shouldReceive('put')->zeroOrMoreTimes();
		Session::shouldReceive('getId')->andReturn('sess-perf');

		$route = new Route('GET', '/perf', []);
		$route->name('perf.test');

		$request = Request::create('/perf', 'GET');
		$request->setRouteResolver(fn() => $route);

		$response = new Response('ok', 200);

		$start = hrtime(true);
		$this->middleware->terminate($request, $response);
		$elapsed = (hrtime(true) - $start) / 1e6;

		$this->assertLessThan(50, $elapsed, "terminate() should complete under 50ms, took {$elapsed}ms");
	}
}
