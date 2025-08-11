<?php

namespace Tests\Unit\Http\Middleware;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use App\Http\Middleware\TrustHosts;

class TrustHostsTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** hosts() returns a single pattern matching all subdomains of the configured app URL.
	 **/
	public function hosts_returns_pattern_for_all_subdomains()
	{
		// Arrange
		Config::set('app.url', 'https://example.com');

		/** @var TrustHosts $middleware */
		$middleware = $this->app->make(TrustHosts::class);

		// Act
		$hosts = $middleware->hosts();

		// Assert
		$this->assertIsArray($hosts);
		$this->assertCount(1, $hosts);

		$host = parse_url(Config::get('app.url'), PHP_URL_HOST);
		$expected = '^(.+\\.)?' . preg_quote($host, '/') . '$';
		$this->assertSame($expected, $hosts[0]);
	}

	/**
	 ** @test
	 **
	 ** hosts() adapts correctly when app URL includes port and path segments.
	 **/
	public function hosts_handles_custom_app_url_with_port_and_path()
	{
		// Arrange
		Config::set('app.url', 'http://sub.domain.test:8080/some/path');

		/** @var TrustHosts $middleware */
		$middleware = $this->app->make(TrustHosts::class);

		// Act
		$hosts = $middleware->hosts();

		// Assert
		$this->assertCount(1, $hosts);

		$host = parse_url(Config::get('app.url'), PHP_URL_HOST);
		$expected = '^(.+\\.)?' . preg_quote($host, '/') . '$';
		$this->assertSame($expected, $hosts[0]);
	}
}
