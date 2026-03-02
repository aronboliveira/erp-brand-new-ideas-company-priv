<?php
// tests/Unit/Providers/RouteServiceProviderTest.php

namespace Tests\Unit\Providers\Root;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, RateLimiter, Route};
use Illuminate\Support\RateLimiting\Limit;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class RouteServiceProviderTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The `register()` method should write an **info** log entry
	 ** indicating that RouteServiceProvider has been invoked.
	 **/
	public function register_writes_info_log(): void
	{
		Log::spy();

		(new RouteServiceProvider($this->app))->register();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\RouteServiceProvider::register called')
			->once();
	}

	/**
	 ** @test
	 **
	 ** The protected `configureRateLimiting()` must:
	 **   • call `RateLimiter::for('api', …)` once
	 **   • log an **info** entry for the call and another for setting the limiter
	 **/
	public function configure_rate_limiting_registers_limiter_and_logs(): void
	{
		RateLimiter::spy();
		Log::spy();

		$provider = new RouteServiceProvider($this->app);
		$method  = new ReflectionMethod($provider, 'configureRateLimiting');
		$method->setAccessible(true);
		$method->invoke($provider);

		RateLimiter::shouldHaveReceived('for')
			->withArgs(function ($name, $callback) {
				return $name === 'api' && is_callable($callback);
			})
			->once();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\RouteServiceProvider::configureRateLimiting called')
			->once();

		Log::shouldHaveReceived('info')
			->with(
				'App\\Providers\\RouteServiceProvider::configureRateLimiting set limiter',
				['name' => 'api', 'limit' => 60]
			)
			->once();
	}

	/**
	 ** @test
	 **
	 ** If `RateLimiter::for()` throws, `configureRateLimiting()` should
	 ** catch it and log an **error** with the exception message.
	 **/
	public function configure_rate_limiting_logs_error_on_exception(): void
	{
		RateLimiter::shouldReceive('for')
			->andThrow(new \Exception('fail-limiter'));
		Log::spy();

		$provider = new RouteServiceProvider($this->app);
		$method  = new ReflectionMethod($provider, 'configureRateLimiting');
		$method->setAccessible(true);
		$method->invoke($provider);

		Log::shouldHaveReceived('error')
			->with(
				'App\\Providers\\RouteServiceProvider::configureRateLimiting failed',
				Mockery::subset(['message' => 'fail-limiter'])
			)
			->once();
	}

	/**
	 ** @test
	 **
	 ** The `boot()` method must:
	 **   • log **boot called**  
	 **   • configure rate limiting (calls into RateLimiter)  
	 **   • log **boot configured rate limiting**  
	 **   • register both API and web routes via the `Route` facade  
	 **   • log entries for both route registrations
	 **/
	public function boot_configures_and_registers_routes_and_logs(): void
	{
		// Spy facades
		Log::spy();
		RateLimiter::spy();

		// Stub Route facades for chaining
		Route::shouldReceive('prefix')->with('api')->andReturnSelf();
		Route::shouldReceive('middleware')->with('api')->andReturnSelf();
		Route::shouldReceive('namespace')->with('App\\Http\\Controllers')->andReturnSelf();
		Route::shouldReceive('group')->with(base_path('routes/api.php'))->andReturnNull();

		Route::shouldReceive('middleware')->with('web')->andReturnSelf();
		Route::shouldReceive('namespace')->with('App\\Http\\Controllers')->andReturnSelf();
		Route::shouldReceive('group')->with(base_path('routes/web.php'))->andReturnNull();

		(new RouteServiceProvider($this->app))->boot();

		// Boot log
		Log::shouldHaveReceived('info')
			->with('App\\Providers\\RouteServiceProvider::boot called')
			->once();

		// Rate limiter log
		Log::shouldHaveReceived('info')
			->with('App\\Providers\\RouteServiceProvider::boot configured rate limiting')
			->once();

		// API routes log
		Log::shouldHaveReceived('info')
			->with(
				'App\\Providers\\RouteServiceProvider::routes registered api routes',
				['prefix' => 'api', 'path' => 'routes/api.php']
			)
			->once();

		// Web routes log
		Log::shouldHaveReceived('info')
			->with(
				'App\\Providers\\RouteServiceProvider::routes registered web routes',
				['path' => 'routes/web.php']
			)
			->once();

		// Ensure RateLimiter was invoked through boot
		RateLimiter::shouldHaveReceived('for')->once();
	}

	/**
	 ** @test
	 **
	 ** If route registration throws, `boot()` should catch it
	 ** and log an **error** with the exception message.
	 **/
	public function boot_logs_error_when_routes_registration_throws(): void
	{
		Log::spy();

		// Make configureRateLimiting succeed
		RateLimiter::spy();

		// Stub Route prefix to throw
		Route::shouldReceive('prefix')
			->andThrow(new \Exception('boom-routes'));

		(new RouteServiceProvider($this->app))->boot();

		Log::shouldHaveReceived('error')
			->with(
				'App\\Providers\\RouteServiceProvider::boot failed',
				Mockery::subset(['message' => 'boom-routes'])
			)
			->once();
	}
}
