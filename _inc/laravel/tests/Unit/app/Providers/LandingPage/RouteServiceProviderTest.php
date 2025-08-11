<?php

namespace Tests\Unit\Providers;

use Illuminate\Support\Facades\{Log, Route};
use Modules\LandingPage\Providers\RouteServiceProvider;
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
		// Prepare expected paths via module_path helper
		$webPath = module_path('LandingPage', '/Routes/web.php');
		$apiPath = module_path('LandingPage', '/Routes/api.php');
		$ns     = 'Modules\\LandingPage\\Http\\Controllers';

		// Web routes chain
		Route::shouldReceive('middleware')
			->once()->with('web')->andReturnSelf();
		Route::shouldReceive('namespace')
			->once()->with($ns)->andReturnSelf();
		Route::shouldReceive('group')
			->once()->with($webPath)->andReturnNull();

		// API routes chain
		Route::shouldReceive('prefix')
			->once()->with('api')->andReturnSelf();
		Route::shouldReceive('middleware')
			->once()->with('api')->andReturnSelf();
		Route::shouldReceive('namespace')
			->once()->with($ns)->andReturnSelf();
		Route::shouldReceive('group')
			->once()->with($apiPath)->andReturnNull();

		// No errors logged
		Log::shouldReceive('error')->never();

		$provider = new RouteServiceProvider($this->app);
		$provider->map();
	}

	/**
	 ** @test
	 **
	 ** If `mapWebRoutes()` throws, `map()` must catch and log a single
	 ** error with the exception message and not re-throw.
	 **/
	public function map_logs_error_when_web_routes_fail(): void
	{
		// Create a stub subclass that forces mapWebRoutes to throw
		$stub = new class($this->app) extends RouteServiceProvider
		{
			protected function mapWebRoutes(): void
			{
				throw new \RuntimeException('web-failure');
			}
		};

		Log::shouldReceive('error')
			->once()
			->with(
				RouteServiceProvider::class . '::map failed to map routes',
				['message' => 'web-failure']
			);

		// mapApiRoutes should not be called in this scenario
		Route::shouldReceive('prefix')->never();

		$stub->map();
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
