<?php

namespace Tests\Unit\Http\Middleware;

use Tests\TestCase;
use App\Http\Middleware\Authenticate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** This function should redirect unauthenticated non-JSON requests to the login route and flash an error.
	 **/
	public function redirects_to_login_and_flashes_error_for_non_json()
	{
		// ensure no user is authenticated
		auth()->logout();
		session()->flush();

		$request = Request::create('/protected', 'GET');
		// bind session store to request
		$request->setLaravelSession(session());

		$middleware = new Authenticate(auth());
		$response = $middleware->_handle($request, fn ($req) => 'NEXT');

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertEquals(route('login'), $response->headers->get('Location'));
		$this->assertEquals('Authentication required.', session('error'));
	}

	/**
	 ** @test
	 **
	 ** This function should return a 401 JSON response for unauthenticated JSON requests and flash an error.
	 **/
	public function returns_401_json_for_unauthenticated_json_request()
	{
		auth()->logout();
		session()->flush();

		$request = Request::create('/api/protected', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
		$request->setLaravelSession(session());

		$middleware = new Authenticate(auth());
		$response = $middleware->_handle($request, fn ($req) => 'NEXT');

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertEquals(['error' => 'Unauthorized'], $response->getData(true));
		$this->assertEquals('Authentication required.', session('error'));
	}

	/**
	 ** @test
	 **
	 ** This function should pass the request through when the user is authenticated.
	 **/
	public function passes_through_when_authenticated()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		session()->flush();

		$request = Request::create('/protected', 'GET');
		$request->setLaravelSession(session());

		$middleware = new Authenticate(auth());
		$result = $middleware->_handle($request, fn ($req) => 'OK');

		$this->assertSame('OK', $result);
	}
}
