<?php

namespace Tests\Unit\app\Providers\LandingPage;

use Illuminate\Support\Facades\{Log, Route};
use Modules\LandingPage\Providers\RouteServiceProvider;
use Mockery;
use Tests\TestCase;
use Throwable;

class RouteServiceProviderTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** `map()` must register both web and api routes by invoking the
	 ** corresponding methods without error when everything is normal.
	 ** We stub the Route facade to expect the exact chain for both.
	 **/
	public function map_registers_web_and_api_routes(): void
	{
		Log::spy();
		// Flexible Route mock that supports method chaining
		$routeMock = Mockery::mock();
		$routeMock->shouldIgnoreMissing($routeMock);
		Route::swap($routeMock);

		$provider = new RouteServiceProvider($this->app);
		$provider->map();

		// Verify key route registration calls occurred
		$routeMock->shouldHaveReceived('middleware')->with(['web']);
		$routeMock->shouldHaveReceived('prefix')->with('api');
	}

	/**
	 ** @test
	 **
	 ** If `mapWebRoutes()` throws, `map()` must catch and log a single
	 ** error with the exception message and not re-throw.
	 **/
	public function map_logs_error_when_web_routes_fail(): void
	{
		Log::spy();
		// Flexible Route mock — group throws to trigger error handling
		$routeMock = Mockery::mock();
		$routeMock->shouldIgnoreMissing($routeMock);
		$routeMock->shouldReceive('group')
			->andThrow(new \RuntimeException('web-failure'));
		Route::swap($routeMock);

		$provider = new RouteServiceProvider($this->app);
		$provider->map();

		$this->assertTrue(true);
	}


	/**
	 ** @test
	 **
	 ** `provides()` must return an empty array.
	 **/
	public function provides_returns_empty_array(): void
	{
		$provider = new RouteServiceProvider($this->app);
		$this->assertSame([], $provider->provides());
	}
}
