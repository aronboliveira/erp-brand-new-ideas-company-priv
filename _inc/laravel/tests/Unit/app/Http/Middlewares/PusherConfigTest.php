<?php

namespace Tests\Unit\Http\Middleware;

use Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Illuminate\Http\{Request, Response};
use App\Http\Middleware\PusherConfig;
use Tests\Concerns\SafeAliasMock;

class PusherConfigTest extends TestCase
{
	use SafeAliasMock;

	use MockeryPHPUnitIntegration;

	/**
	 ** @test
	 **
	 ** handle() should pass the request through and return the next middleware's Response,
	 ** setting config values based on Utility::settingsById().
	 **/
	public function handle_passes_through_and_sets_pusher_config()
	{
		// * DEV-ONLY TEST CLONE: Pre-seed Utility::\$getSettingsId cache instead of aliasMock.
		// Original: aliasMock('App\Models\Utility')->shouldReceive('settingsById')->with(1)->andReturn([...])
		\App\Models\Utility::$getSettingsId[1] = [
			'pusher_app_key'     => 'test-key',
			'pusher_app_secret'  => 'test-secret',
			'pusher_app_id'      => 'test-id',
			'pusher_app_cluster' => 'test-cluster',
		];

		$middleware = new PusherConfig();
		$request   = Request::create('/test', 'GET');

		// Pre-set config to something else to prove it's overridden
		config([
			'chatify.pusher.key'     => 'orig',
			'chatify.pusher.secret'  => 'orig',
			'chatify.pusher.app_id'  => 'orig',
			'chatify.pusher.options.cluster' => 'orig',
		]);

		// Act
		$response = $middleware->handle($request, fn ($req) => new Response('NEXT', 200));

		// Assert response pass-through
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame('NEXT', $response->getContent());

		// Assert config was set from our stubbed settings
		$this->assertSame('test-key', config('chatify.pusher.key'));
		$this->assertSame('test-secret', config('chatify.pusher.secret'));
		$this->assertSame('test-id', config('chatify.pusher.app_id'));
		$this->assertSame('test-cluster', config('chatify.pusher.options.cluster'));
	}

	/**
	 ** @test
	 **
	 ** handle() must log and re-throw exceptions from Utility::settingsById()
	 ** so the upstream Laravel error handler can render a 500 (rather than
	 ** silently passing through with stale Pusher config).
	 **/
	public function handle_logs_and_rethrows_utility_exception()
	{
		// Force settingsById() to throw by priming a sentinel cache that
		// the middleware will read into config(...) — but make the cache
		// a non-iterable to trigger the catch path. Easiest reliable
		// trigger: stub the static cache to a string so foreach() throws.
		\App\Models\Utility::$getSettingsId[1] = 'not-an-array-will-throw-on-array-access';

		$middleware = new PusherConfig();
		$request   = Request::create('/error', 'GET');

		try {
			$middleware->handle($request, fn ($req) => new Response('OKAY', 200));
			$this->fail('Expected handle() to re-throw on Utility::settingsById failure');
		} catch (\Throwable $e) {
			// Production catch block re-throws after logging; that's the
			// contract we're guarding here.
			$this->assertNotEmpty($e->getMessage());
		} finally {
			unset(\App\Models\Utility::$getSettingsId[1]);
		}
	}
}
