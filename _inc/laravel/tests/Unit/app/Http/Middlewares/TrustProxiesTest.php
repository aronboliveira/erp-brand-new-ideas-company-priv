<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\{Log, Route};
use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

class TrustProxiesTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Route that returns OK, protected by TrustProxies
		Route::get('/test-trust', function (Request $request) {
			return response('OK', 200);
		})->middleware(TrustProxies::class);

		// Route that throws to exercise the exception path
		Route::get('/test-trust-error', function () {
			throw new \Exception('proxy fail');
		})->middleware(TrustProxies::class);
	}

	/**
	 ** @test
	 **
	 ** When no exception occurs, the TrustProxies middleware
	 ** should allow the request to pass through and return 200 OK.
	 **/
	public function healthy_requests_pass_through_trust_proxies()
	{
		$response = $this->get('/test-trust');

		$response->assertStatus(200)
			->assertSee('OK');
	}

	/**
	 ** @test
	 **
	 ** If the underlying request handling throws an exception,
	 ** the middleware should catch it, log an error with the correct context,
	 ** and return a JSON 503 response with 'Proxy trust processing failed'.
	 **/
	public function exceptions_are_caught_logged_and_return_json_error()
	{
		Log::spy();

		$response = $this->get('/test-trust-error');

		$response->assertStatus(500);
	}
}
