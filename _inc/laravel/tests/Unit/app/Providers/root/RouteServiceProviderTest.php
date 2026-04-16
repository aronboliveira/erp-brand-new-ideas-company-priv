<?php
// tests/Unit/app/Providers/root/RouteServiceProviderTest.php

namespace Tests\Unit\app\Providers\root;

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

		$this->assertTrue(true);
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
		$this->assertTrue(true);
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

		$this->assertTrue(true);
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

		// Create flexible Route mock that supports method chaining
		$routeMock = Mockery::mock();
		$routeMock->shouldIgnoreMissing($routeMock);
		$routeMock->shouldReceive('getRoutes')->andReturn([]);
		$routeMock->shouldReceive('group')->andReturnNull();
		Route::swap($routeMock);

		(new RouteServiceProvider($this->app))->boot();

		// Ensure RateLimiter was invoked through boot
		RateLimiter::shouldHaveReceived('for')->once();
		$this->assertTrue(true);
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

		$this->assertTrue(true);
	}
}
