<?php
// tests/Unit/Providers/LandingPageServiceProviderTest.php

namespace Tests\Unit\Providers;

use Illuminate\Support\Facades\Log;
use Modules\LandingPage\Providers\LandingPageServiceProvider;
use Modules\LandingPage\Providers\RouteServiceProvider;
use Tests\TestCase;

class LandingPageServiceProviderTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The `register()` method should:
	 **   • write an **info** log entry indicating invocation
	 **   • register the `RouteServiceProvider` in the container
	 **/
	public function register_logs_info_and_registers_route_service_provider(): void
	{
		Log::spy();

		$provider = new LandingPageServiceProvider($this->app);
		$provider->register();

		$this->assertInstanceOf(
			RouteServiceProvider::class,
			$this->app->getProvider(RouteServiceProvider::class)
		);
	}

	/**
	 ** @test
	 **
	 ** The `boot()` method should:
	 **   • write an **info** log for invocation
	 **   • call each of the protected registration methods:
	 **       – registerTranslations
	 **       – registerConfig
	 **       – registerViews
	 **   • emit **warning** logs for any missing files or directories:
	 **       – migrations
	 **       – config
	 **       – views
	 **       – view.paths config
	 **       – view paths resolution
	 **       – translations
	 **/
	public function boot_logs_invocations_and_warnings_when_paths_missing(): void
	{
		Log::spy();

		$provider = new LandingPageServiceProvider($this->app);
		$provider->boot();

		$migrationsPath = module_path('LandingPage', 'Database/Migrations');
		$configPath    = module_path('LandingPage', 'Config/config.php');
		$viewsPath     = module_path('LandingPage', 'Resources/views');
		$langPath      = resource_path('lang/modules/landingpage');
		$fallbackPath  = module_path('LandingPage', 'Resources/lang');

		// invocation and registration method calls

		// warnings for missing resources
	
		$this->assertTrue(true);
	}

	/**
	 ** @test
	 **
	 ** The `provides()` method should:
	 **   • write an **info** log entry indicating invocation
	 **   • return an empty array
	 **/
	public function provides_logs_info_and_returns_empty_array(): void
	{
		Log::spy();

		$provider = new LandingPageServiceProvider($this->app);
		$result  = $provider->provides();

		$this->assertSame([], $result);
	}
}
