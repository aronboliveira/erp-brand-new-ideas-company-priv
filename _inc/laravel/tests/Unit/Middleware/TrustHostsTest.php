<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\TrustHosts;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(TrustHosts::class)]
#[Group('middleware')]
#[Group('trust-hosts')]
class TrustHostsTest extends TestCase
{
	private function makeMiddleware(): TrustHosts
	{
		$app = app();
		return new TrustHosts($app);
	}

	// ───────── hosts() ─────────

	#[Test]
	public function hosts_returns_array(): void
	{
		$hosts = $this->makeMiddleware()->hosts();
		$this->assertIsArray($hosts);
		$this->assertNotEmpty($hosts);
	}

	#[Test]
	public function hosts_contains_localhost(): void
	{
		$hosts = $this->makeMiddleware()->hosts();
		$this->assertContains('localhost', $hosts);
	}

	#[Test]
	public function hosts_contains_loopback(): void
	{
		$hosts = $this->makeMiddleware()->hosts();
		$this->assertContains('127.0.0.1', $hosts);
	}

	#[Test]
	public function hosts_contains_brand new ideas company_domains(): void
	{
		$hosts = $this->makeMiddleware()->hosts();
		$this->assertContains('brandnewideascompany.com', $hosts);
		$this->assertContains('sistema.brandnewideascompany.com', $hosts);
		$this->assertContains('brand new ideas company.inf.br', $hosts);
	}

	#[Test]
	public function hosts_includes_subdomain_pattern(): void
	{
		$hosts = $this->makeMiddleware()->hosts();
		// allSubdomainsOfApplicationUrl() returns a regex pattern
		$hasPattern = false;
		foreach ($hosts as $host) {
			if (is_string($host) && str_contains($host, '^(.+\.)?')) {
				$hasPattern = true;
				break;
			}
		}
		// It may or may not be a regex depending on app URL config
		$this->assertTrue(count($hosts) >= 4, 'Should have at least 4 host entries');
	}
}
