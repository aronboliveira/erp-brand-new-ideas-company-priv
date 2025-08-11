<?php

namespace Tests\Unit\Http\Middleware;

use Tests\TestCase;
use Illuminate\Http\{Request, Response};
use App\Http\Middleware\SecureHeaders;

class SecureHeadersTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** SecureHeaders middleware should add strict security headers to the response.
	 **/
	public function secure_headers_are_added_to_response()
	{
		$middleware = new SecureHeaders();
		$request   = Request::create('/test', 'GET');

		$next = function ($req) {
			return new Response('content', 200);
		};

		$response = $middleware->handle($request, $next);

		$this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
		$this->assertEquals('max-age=31536000; includeSubDomains', $response->headers->get('Strict-Transport-Security'));
		$this->assertEquals("default-src 'self'", $response->headers->get('Content-Security-Policy'));
		$this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
	}

	/**
	 ** @test
	 **
	 ** SecureHeaders middleware should not modify the original response content or status code.
	 **/
	public function handle_should_not_modify_response_content_or_status()
	{
		$middleware = new SecureHeaders();
		$request   = Request::create('/another', 'POST');
		$original  = new Response('my body', 201);

		$response = $middleware->handle($request, function ($req) use ($original) {
			return $original;
		});

		$this->assertSame($original->getContent(), $response->getContent());
		$this->assertSame($original->getStatusCode(), $response->getStatusCode());
	}
}
