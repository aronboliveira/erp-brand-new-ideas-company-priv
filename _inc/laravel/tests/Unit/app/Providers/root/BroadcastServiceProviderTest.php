<?php
// tests/Unit/Providers/BroadcastServiceProviderTest.php

namespace Tests\Unit\Providers;

use App\Providers\BroadcastServiceProvider;
use Illuminate\Support\Facades\{Broadcast, Log};
use Tests\TestCase;

class BroadcastServiceProviderTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The `register()` method should write an **info** log entry
	 ** indicating that the BroadcastServiceProvider has been invoked.
	 **/
	public function register_writes_info_log(): void
	{
		Log::spy();

		(new BroadcastServiceProvider($this->app))->register();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\BroadcastServiceProvider::register called')
			->once();
	}

	/**
	 ** @test
	 **
	 ** When the channels file does **not** exist, `boot()` must:
	 **   • call `Broadcast::routes()`
	 **   • log an **info** entry for `boot called`
	 **   • log an **info** entry for `registered broadcast routes`
	 **   • log a **warning** for missing channels file with the path.
	 **/
	public function boot_registers_routes_and_warns_if_channels_file_missing(): void
	{
		$path = base_path('routes/channels.php');
		if (file_exists($path)) {
			unlink($path);
		}

		Log::spy();
		Broadcast::spy();

		(new BroadcastServiceProvider($this->app))->boot();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\BroadcastServiceProvider::boot called')
			->once();

		Broadcast::shouldHaveReceived('routes')->once();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\BroadcastServiceProvider::boot registered broadcast routes')
			->once();

		Log::shouldHaveReceived('warning')
			->with('App\\Providers\\BroadcastServiceProvider::boot channels file not found', ['path' => $path])
			->once();
	}

	/**
	 ** @test
	 **
	 ** When the channels file **exists**, `boot()` must:
	 **   • call `Broadcast::routes()`
	 **   • log **info** for `boot called` and `registered broadcast routes`
	 **   • load the channels file and log **info** for `loaded channels file` with the path.
	 **/
	public function boot_loads_channels_file_and_logs_if_present(): void
	{
		$path = base_path('routes/channels.php');
		if (!is_dir(dirname($path))) {
			mkdir(dirname($path), 0777, true);
		}
		file_put_contents($path, "<?php\n// channels stub\n");

		Log::spy();
		Broadcast::spy();

		(new BroadcastServiceProvider($this->app))->boot();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\BroadcastServiceProvider::boot called')
			->once();

		Broadcast::shouldHaveReceived('routes')->once();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\BroadcastServiceProvider::boot registered broadcast routes')
			->once();

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\BroadcastServiceProvider::boot loaded channels file', ['path' => $path])
			->once();

		// cleanup
		unlink($path);
	}

	/**
	 ** @test
	 **
	 ** The `provides()` method should write an **info** log entry
	 ** indicating that the provider's `provides` method was called,
	 ** and return an empty array.
	 **/
	public function provides_logs_info_and_returns_empty_array(): void
	{
		Log::spy();

		$provider = new BroadcastServiceProvider($this->app);
		$result  = $provider->provides();

		$this->assertSame([], $result);

		Log::shouldHaveReceived('info')
			->with('App\\Providers\\BroadcastServiceProvider::provides called')
			->once();
	}
}
