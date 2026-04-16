<?php

namespace Tests\Unit\Http\Middleware;

use Tests\TestCase;
use Illuminate\Http\{JsonResponse, Request, Response};
use App\Http\Middleware\EncryptCookies;

class EncryptCookiesTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** This function should pass the request to the next middleware and return its response when no exception occurs.
	 **/
	public function handle_passes_through_and_returns_next_response()
	{
		// resolve via container so Encrypter & CookieJar are injected
		$middleware = $this->app->make(EncryptCookies::class);

		$request = Request::create('/test', 'GET');
		$next = function ($req) {
			return new Response('NEXT OK', 200);
		};

		$response = $middleware->handle($request, $next);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals('NEXT OK', $response->getContent());
	}

	/**
	 ** @test
	 **
	 ** Exceptions from the next middleware propagate (not caught by EncryptCookies).
	 **/
	public function handle_propagates_exceptions_from_next()
	{
		$middleware = $this->app->make(EncryptCookies::class);

		$request = Request::create('/test', 'GET');
		$next = function ($req) {
			throw new \RuntimeException('boom');
		};

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('boom');

		$middleware->handle($request, $next);
	}
}
