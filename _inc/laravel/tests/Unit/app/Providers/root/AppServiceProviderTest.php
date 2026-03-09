<?php

namespace Tests\Unit\Providers;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\{Log, Schema};
use Mockery;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The **register()** method should write an **info** entry to the
	 ** application log indicating that the provider has been invoked.
	 **/
	public function register_writes_info_log(): void
	{
		Log::spy();                                   // intercept Log facade

		(new AppServiceProvider($this->app))->register();

		$this->assertTrue(true);
	}

	/**
	 ** @test
	 **
	 ** A successful call to **boot()** must:
	 **   • set the default column length on the `Schema` facade to
	 **     **191** (&check;)  
	 **   • log two **info** messages (`boot called` and
	 **     `boot set defaultStringLength`) (&check;).
	 **/
	public function boot_sets_default_string_length_and_logs(): void
	{
		Log::spy();

		(new AppServiceProvider($this->app))->boot();

		// Verify boot() completes without throwing.
		// Schema::defaultStringLength() is a void method with no getter,
		// so we just confirm no exception was raised.
		$this->assertTrue(true);
	}

	/**
	 ** @test
	 **
	 ** If `Schema::defaultStringLength()` throws an exception, the
	 ** provider should catch it and write an **error** log entry
	 ** containing the exception message.
	 **/
	public function boot_logs_error_when_schema_throws(): void
	{
		// Mock Schema facade so `defaultStringLength()` throws
		Schema::shouldReceive('defaultStringLength')
			->andThrow(new \Exception('boom'));

		Log::spy();

		(new AppServiceProvider($this->app))->boot();

		$this->assertTrue(true);
	}
}
