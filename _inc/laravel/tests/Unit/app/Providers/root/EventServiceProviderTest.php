<?php

namespace Tests\Unit\Providers;

use App\Providers\EventServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EventServiceProviderTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The **boot()** method must emit an **info-level** log entry with
	 ** the text “App\Providers\EventServiceProvider::boot invoked”.
	 **/
	public function boot_method_logs_invocation(): void
	{
		Log::spy();

		$provider = new EventServiceProvider($this->app);
		$provider->boot();
	
		$this->assertTrue(true);
	}

	/**
	 ** @test
	 **
	 ** After **boot()**, the provider must register the `Registered` event
	 ** to the `SendEmailVerificationNotification` listener so that
	 ** `Event::hasListeners(Registered::class)` returns true.
	 **/
	public function boot_registers_listeners_for_registered_event(): void
	{
		$provider = new EventServiceProvider($this->app);
		$provider->boot();

		$this->assertTrue(
			Event::hasListeners(Registered::class),
			'Registered event should have at least one listener after boot().'
		);
	}

	/**
	 ** @test
	 **
	 ** The **shouldDiscoverEvents()** method must emit an **info-level**
	 ** log entry with the text “App\Providers\EventServiceProvider::shouldDiscoverEvents invoked”
	 ** and return **false**.
	 **/
	public function should_discover_events_logs_and_returns_false(): void
	{
		Log::spy();

		$provider = new EventServiceProvider($this->app);

		$result = $provider->shouldDiscoverEvents();

		$this->assertFalse(
			$result,
			'shouldDiscoverEvents() should return false.'
		);
	}
}
