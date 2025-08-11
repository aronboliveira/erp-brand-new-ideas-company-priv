<?php

namespace Tests\Unit\Providers;

use Modules\LandingPage\Providers\AddMenuProvider;
use Illuminate\Support\Facades\{Log, Route, View};
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AddMenuProviderTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** `boot()` must log its invocation and then—when there are named
	 ** routes matching the module prefix—register a view composer and
	 ** log the registration count.
	 **/
	public function boot_registers_composer_and_logs_when_routes_exist(): void
	{
		// Arrange: stub two routes, one matching prefix, one not
		$matching = new class
		{
			public function getName(): string
			{
				return 'landingpage.home';
			}
		};
		$other   = new class
		{
			public function getName(): string
			{
				return 'other.route';
			}
		};
		Route::shouldReceive('getRoutes')->once()->andReturn([$matching, $other]);

		// Expect logs in sequence
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::boot called');
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes called', ['prefix' => 'landingpage']);
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes matched routes', ['count' => 1]);
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::boot registered composer', ['routes_count' => 1]);

		// Expect the view composer registration for exactly the matching route name
		View::shouldReceive('composer')
			->once()
			->withArgs(function ($routes, $callback) use ($matching) {
				return $routes === [$matching] && is_callable($callback);
			});

		// Act
		$provider = new AddMenuProvider($this->app);
		$provider->boot();
	}

	/**
	 ** @test
	 **
	 ** `boot()` must log a warning and return early when there are no
	 ** named routes at all.
	 **/
	public function boot_logs_warning_and_skips_when_no_routes(): void
	{
		Route::shouldReceive('getRoutes')->once()->andReturn([]);

		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::boot called');
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes called', ['prefix' => 'landingpage']);
		Log::shouldReceive('warning')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes found no routes');
		Log::shouldReceive('warning')
			->once()
			->with(AddMenuProvider::class . '::boot found no named routes', ['prefix' => 'landingpage']);

		View::shouldReceive('composer')->never();

		$provider = new AddMenuProvider($this->app);
		$provider->boot();
	}

	/**
	 ** @test
	 **
	 ** `register()` must log its invocation.
	 **/
	public function register_logs_invocation(): void
	{
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::register called');

		$provider = new AddMenuProvider($this->app);
		$provider->register();
	}

	/**
	 ** @test
	 **
	 ** `provides()` must log its invocation and return an empty array.
	 **/
	public function provides_logs_invocation_and_returns_empty(): void
	{
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::provides called');

		$provider = new AddMenuProvider($this->app);
		$this->assertSame([], $provider->provides());
	}

	/**
	 ** @test
	 **
	 ** `getNamedRoutes()` filters only those route-names beginning with
	 ** the module prefix, logging both entry and match count.
	 **/
	public function get_named_routes_filters_and_logs(): void
	{
		// Arrange: three routes, two matching
		$r1 = new class
		{
			public function getName(): string
			{
				return 'landingpage.one';
			}
		};
		$r2 = new class
		{
			public function getName(): string
			{
				return 'landingpage.two';
			}
		};
		$r3 = new class
		{
			public function getName(): string
			{
				return 'other';
			}
		};

		Route::shouldReceive('getRoutes')->once()->andReturn([$r1, $r2, $r3]);

		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes called', ['prefix' => 'landingpage']);
		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes matched routes', ['count' => 2]);

		$provider = new AddMenuProvider($this->app);
		$method  = new ReflectionMethod(AddMenuProvider::class, 'getNamedRoutes');
		$method->setAccessible(true);

		/** @var array<int,string> $result */
		$result = $method->invoke($provider, 'landingpage');

		$this->assertSame(['landingpage.one', 'landingpage.two'], $result);
	}

	/**
	 ** @test
	 **
	 ** `getNamedRoutes()` logs a warning when no matches are found.
	 **/
	public function get_named_routes_logs_warning_when_no_matches(): void
	{
		$r1 = new class
		{
			public function getName(): string
			{
				return 'foo';
			}
		};
		$r2 = new class
		{
			public function getName(): string
			{
				return 'bar';
			}
		};

		Route::shouldReceive('getRoutes')->once()->andReturn([$r1, $r2]);

		Log::shouldReceive('info')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes called', ['prefix' => 'landingpage']);
		Log::shouldReceive('warning')
			->once()
			->with(AddMenuProvider::class . '::getNamedRoutes no matches', ['prefix' => 'landingpage']);

		$provider = new AddMenuProvider($this->app);
		$method  = new ReflectionMethod(AddMenuProvider::class, 'getNamedRoutes');
		$method->setAccessible(true);

		$this->assertSame([], $method->invoke($provider, 'landingpage'));
	}
}
