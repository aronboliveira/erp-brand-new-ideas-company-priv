<?php

declare(strict_types=1);

namespace Tests\Unit\app\Jobs;

use App\Jobs\RedirectWatcherJob;
use Illuminate\Support\Facades\{Auth, Cache, Log};
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group, Test};
use Tests\TestCase;

#[CoversClass(RedirectWatcherJob::class)]
#[Group('jobs')]
class RedirectWatcherJobTest extends TestCase
{
	/* ──────────────────────── constructor ──────────────────────── */

	#[Test]
	public function constructor_sets_defaults(): void
	{
		$job = new RedirectWatcherJob();
		$this->assertSame('#DEFAULT', $job->sessionId);
		$this->assertSame('#DEFAULT', $job->watcherKey);
		$this->assertSame('/login', $job->redirectTo);
	}

	#[Test]
	public function constructor_accepts_custom_values(): void
	{
		$job = new RedirectWatcherJob('sess-1', 'watcher-abc', '/dashboard');
		$this->assertSame('sess-1', $job->sessionId);
		$this->assertSame('watcher-abc', $job->watcherKey);
		$this->assertSame('/dashboard', $job->redirectTo);
	}

	/* ──────────────────────── handle() ──────────────────────── */

	#[Test]
	public function handle_returns_early_when_cache_missing(): void
	{
		Cache::shouldReceive('get')->with('watcher-key')->once()->andReturnNull();
		Log::shouldReceive('debug')->atLeast()->once();
		Log::shouldReceive('warning')->once();

		$job = new RedirectWatcherJob('s1', 'watcher-key', '/login');
		$job->handle();
		$this->assertTrue(true); // no exception
	}

	#[Test]
	public function handle_returns_early_when_cache_is_non_array(): void
	{
		Cache::shouldReceive('get')->with('watcher-key')->once()->andReturn('string-value');
		Log::shouldReceive('debug')->atLeast()->once();
		Log::shouldReceive('warning')->once();

		$job = new RedirectWatcherJob('s1', 'watcher-key', '/login');
		$job->handle();
		$this->assertTrue(true);
	}

	#[Test]
	public function handle_logouts_and_forgets_when_routes_match(): void
	{
		$watcherData = ['current_route' => '/settings', 'redirect_route' => '/login'];

		Cache::shouldReceive('get')->with('my-watcher')->once()->andReturn($watcherData);
		Cache::shouldReceive('get')->with('last_route_s2')->once()->andReturn('/settings');
		Cache::shouldReceive('forget')->with('my-watcher')->once();
		Auth::shouldReceive('logout')->once();
		Log::shouldReceive('debug')->atLeast()->once();
		Log::shouldReceive('notice')->once();

		$job = new RedirectWatcherJob('s2', 'my-watcher', '/login');
		$job->handle();
		$this->assertTrue(true);
	}

	#[Test]
	public function handle_forgets_only_when_routes_differ(): void
	{
		$watcherData = ['current_route' => '/settings', 'redirect_route' => '/login'];

		Cache::shouldReceive('get')->with('w1')->once()->andReturn($watcherData);
		Cache::shouldReceive('get')->with('last_route_s3')->once()->andReturn('/other-page');
		Cache::shouldReceive('forget')->with('w1')->once();
		Auth::shouldReceive('logout')->never();
		Log::shouldReceive('debug')->twice();

		$job = new RedirectWatcherJob('s3', 'w1', '/login');
		$job->handle();
		$this->assertTrue(true);
	}

	#[Test]
	public function handle_uses_empty_string_when_last_route_cache_null(): void
	{
		$watcherData = ['current_route' => '', 'redirect_route' => '/login'];

		Cache::shouldReceive('get')->with('w2')->once()->andReturn($watcherData);
		Cache::shouldReceive('get')->with('last_route_s4')->once()->andReturnNull();
		// currRoute '' === watcherRoute '' → should logout
		Cache::shouldReceive('forget')->with('w2')->once();
		Auth::shouldReceive('logout')->once();
		Log::shouldReceive('debug')->atLeast()->once();
		Log::shouldReceive('notice')->once();

		$job = new RedirectWatcherJob('s4', 'w2', '/login');
		$job->handle();
		$this->assertTrue(true);
	}

	#[Test]
	public function handle_uses_empty_when_current_route_key_missing(): void
	{
		$watcherData = ['redirect_route' => '/login']; // no 'current_route'

		Cache::shouldReceive('get')->with('w3')->once()->andReturn($watcherData);
		Cache::shouldReceive('get')->with('last_route_s5')->once()->andReturn('');
		// watcherRoute = '' (via ?? ''), currRoute = '' → match → logout
		Cache::shouldReceive('forget')->with('w3')->once();
		Auth::shouldReceive('logout')->once();
		Log::shouldReceive('debug')->atLeast()->once();
		Log::shouldReceive('notice')->once();

		$job = new RedirectWatcherJob('s5', 'w3', '/login');
		$job->handle();
		$this->assertTrue(true);
	}

	/* ──────────── performance ──────────── */

	#[Test]
	public function handle_executes_within_time_budget(): void
	{
		Cache::shouldReceive('get')->andReturnNull();
		Log::shouldReceive('debug')->atLeast()->once();
		Log::shouldReceive('warning')->atLeast()->once();

		$job = new RedirectWatcherJob();
		$start = hrtime(true);
		$job->handle();
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(50, $elapsed, 'handle() should complete under 50ms');
	}

	/* ──────────── serialization ──────────── */

	#[Test]
	public function job_is_serializable(): void
	{
		$job = new RedirectWatcherJob('s', 'k', '/r');
		$serialized = serialize($job);
		$restored = unserialize($serialized);
		$this->assertInstanceOf(RedirectWatcherJob::class, $restored);
		$this->assertSame('s', $restored->sessionId);
		$this->assertSame('k', $restored->watcherKey);
		$this->assertSame('/r', $restored->redirectTo);
	}
}
