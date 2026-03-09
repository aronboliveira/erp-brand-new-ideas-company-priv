<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\VerifyCsrfToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

#[CoversClass(VerifyCsrfToken::class)]
#[Group('middleware')]
#[Group('verify-csrf')]
class VerifyCsrfTokenTest extends TestCase
{
	// ───────── parseCookies() ─────────

	#[Test]
	public function parseCookies_returns_cookie_data_from_response(): void
	{
		$middleware = $this->makeMiddleware();

		$response = new Response('ok', 200);
		$response->headers->setCookie(
			Cookie::create('session_id')
				->withValue('abc123')
				->withDomain('localhost')
				->withPath('/')
				->withSecure(true)
				->withHttpOnly(true)
		);

		$result = $this->invokeParseCookies($middleware, $response);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('session_id', $result);
		$this->assertSame('abc123', $result['session_id']['value']);
		$this->assertSame('localhost', $result['session_id']['domain']);
		$this->assertSame('/', $result['session_id']['path']);
		$this->assertTrue($result['session_id']['secure']);
		$this->assertTrue($result['session_id']['httpOnly']);
	}

	#[Test]
	public function parseCookies_handles_multiple_cookies(): void
	{
		$middleware = $this->makeMiddleware();

		$response = new Response('ok', 200);
		$response->headers->setCookie(Cookie::create('a')->withValue('1'));
		$response->headers->setCookie(Cookie::create('b')->withValue('2'));
		$response->headers->setCookie(Cookie::create('c')->withValue('3'));

		$result = $this->invokeParseCookies($middleware, $response);

		$this->assertCount(3, $result);
		$this->assertArrayHasKey('a', $result);
		$this->assertArrayHasKey('b', $result);
		$this->assertArrayHasKey('c', $result);
	}

	#[Test]
	public function parseCookies_returns_failure_for_null_response(): void
	{
		$middleware = $this->makeMiddleware();
		$result    = $this->invokeParseCookies($middleware, null);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('FAILED', $result);
	}

	#[Test]
	public function parseCookies_returns_empty_array_for_response_without_cookies(): void
	{
		$middleware = $this->makeMiddleware();
		$response  = new Response('ok', 200);

		$result = $this->invokeParseCookies($middleware, $response);

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	// ───────── EXEMPT_URIS ─────────

	#[Test]
	public function exempt_uris_contains_paymentwall_patterns(): void
	{
		$reflection = new \ReflectionClass(VerifyCsrfToken::class);
		$constants  = $reflection->getConstants();

		$this->assertArrayHasKey('EXEMPT_URIS', $constants);
		$exempts = $constants['EXEMPT_URIS'];

		$this->assertIsArray($exempts);
		$this->assertContains('plan-pay-with-paymentwall/*', $exempts);
		$this->assertContains('invoice-pay-with-paymentwall/*', $exempts);
	}

	// ───────── cookie metadata extraction ─────────

	#[Test]
	public function parseCookies_extracts_expiry_when_set(): void
	{
		$middleware = $this->makeMiddleware();

		$expires = time() + 3600;
		$response = new Response('ok', 200);
		$response->headers->setCookie(
			Cookie::create('token')
				->withValue('xyz')
				->withExpires($expires)
		);

		$result = $this->invokeParseCookies($middleware, $response);

		$this->assertArrayHasKey('token', $result);
		$this->assertNotNull($result['token']['expires']);
		$this->assertMatchesRegularExpression(
			'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
			$result['token']['expires']
		);
	}

	#[Test]
	public function parseCookies_extracts_samesite_attribute(): void
	{
		$middleware = $this->makeMiddleware();

		$response = new Response('ok', 200);
		$response->headers->setCookie(
			Cookie::create('pref')
				->withValue('dark')
				->withSameSite('Lax')
		);

		$result = $this->invokeParseCookies($middleware, $response);

		$this->assertArrayHasKey('pref', $result);
		$this->assertSame('lax', $result['pref']['sameSite']);
	}

	// ───────── performance ─────────

	#[Test]
	public function parseCookies_executes_within_acceptable_time(): void
	{
		$middleware = $this->makeMiddleware();

		$response = new Response('ok', 200);
		for ($i = 0; $i < 50; $i++) {
			$response->headers->setCookie(
				Cookie::create("cookie_{$i}")->withValue("value_{$i}")
			);
		}

		$start  = hrtime(true);
		$result = $this->invokeParseCookies($middleware, $response);
		$elapsed = (hrtime(true) - $start) / 1e6;

		$this->assertCount(50, $result);
		$this->assertLessThan(25, $elapsed, "parseCookies with 50 cookies should complete under 25ms, took {$elapsed}ms");
	}

	// ─── helpers ───

	private function makeMiddleware(): VerifyCsrfToken
	{
		return new VerifyCsrfToken(
			app(),
			app(\Illuminate\Contracts\Encryption\Encrypter::class)
		);
	}

	private function invokeParseCookies(VerifyCsrfToken $middleware, mixed $response): array
	{
		$ref = new \ReflectionMethod($middleware, 'parseCookies');
		$ref->setAccessible(true);
		return $ref->invoke($middleware, $response);
	}
}
