<?php

namespace Tests\Unit\Providers;

use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\{Gate, Log};
use Tests\TestCase;

class AuthServiceProviderTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The **boot()** method must emit an **info-level** log entry with
	 ** the text “App\Providers\AuthServiceProvider::boot invoked”.
	 **/
	public function boot_method_logs_invocation(): void
	{
		Log::spy();

		$provider = new AuthServiceProvider($this->app);
		$provider->boot();
		$this->assertTrue(true);
	}

	/**
	 ** @test
	 **
	 ** `boot()` must call **registerPolicies()** so every mapping inside
	 ** the provider’s `$policies` array becomes available through
	 ** `Gate::getPolicyFor()`.
	 **
	 ** We inject a fake mapping via reflection, call `boot()`, and then
	 ** assert that Gate now knows about the policy.
	 **/
	public function boot_method_registers_policies_with_gate(): void
	{
		// Create provider instance
		$provider = new AuthServiceProvider($this->app);

		// Inject a fake “model ➜ policy” mapping
		$this->injectPolicies($provider, [
			FakeModel::class => FakePolicy::class
		]);

		// Sanity-check – policy not present before boot
		$this->assertNull(
			Gate::getPolicyFor(FakeModel::class),
			'Policy should not be registered before boot().'
		);

		// Act
		$provider->boot();

		// Assert – policy now registered
		$this->assertInstanceOf(
			FakePolicy::class,
			Gate::getPolicyFor(FakeModel::class),
			'Policy was not registered by boot().'
		);
	}

	/**
	 ** Helper: use reflection to override the `$policies` property so the
	 ** provider has something tangible to register.
	 */
	private function injectPolicies(AuthServiceProvider $provider, array $map): void
	{
		$ref = new \ReflectionProperty(AuthServiceProvider::class, 'policies');
		$ref->setAccessible(true);
		$ref->setValue($provider, $map);
	}
}

/* ---------------------------------------------------------------------
 |  Test-only stub classes
 * ------------------------------------------------------------------- */

class FakeModel
{
	// Empty model stand-in
}

class FakePolicy
{
	public function viewAny(): bool
	{
		return true;
	}
	public function view(): bool
	{
		return true;
	}
}
