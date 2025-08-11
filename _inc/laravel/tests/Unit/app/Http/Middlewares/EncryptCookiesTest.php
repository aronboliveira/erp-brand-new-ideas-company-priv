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
	 ** This function should catch exceptions from the next middleware and return a 500 JSON error.
	 **/
	public function handle_catches_exception_and_returns_json_error()
	{
		$middleware = $this->app->make(EncryptCookies::class);

		$request = Request::create('/test', 'GET');
		$next = function ($req) {
			throw new \RuntimeException('boom');
		};

		$response = $middleware->handle($request, $next);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(500, $response->getStatusCode());
		$this->assertEquals(['error' => 'Cookie encryption failed.'], $response->getData(true));
	}
}
