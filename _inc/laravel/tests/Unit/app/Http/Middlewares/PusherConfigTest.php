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
		// Arrange: stub Utility::settingsById to return our test settings
		$this->aliasMock('App\Models\Utility')
			->shouldReceive('settingsById')
			->with(1)
			->andReturn([
				'pusher_app_key'     => 'test-key',
				'pusher_app_secret'  => 'test-secret',
				'pusher_app_id'      => 'test-id',
				'pusher_app_cluster' => 'test-cluster',
			]);

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
	 ** handle() should swallow exceptions from Utility::settingsById()
	 ** and still return the next middleware's Response.
	 **/
	public function handle_swallows_utility_exception_and_passes_through()
	{
		// Arrange: stub Utility::settingsById to throw
		$this->aliasMock('App\Models\Utility')
			->shouldReceive('settingsById')
			->with(1)
			->andThrow(new \RuntimeException('oops'));

		$middleware = new PusherConfig();
		$request   = Request::create('/error', 'GET');

		// Act
		$response = $middleware->handle($request, fn ($req) => new Response('OKAY', 200));

		// Assert it still passes through
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame('OKAY', $response->getContent());
	}
}
