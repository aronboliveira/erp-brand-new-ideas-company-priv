<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RevalidateBackHistory;

class RevalidateBackHistoryTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Route that returns OK, with CORS middleware
		Route::get('/test-cors', function () {
			return response('OK', 200);
		})->middleware(RevalidateBackHistory::class);

		// Route that throws, to exercise the exception path
		Route::get('/test-cors-error', function () {
			throw new \Exception('boom');
		})->middleware(RevalidateBackHistory::class);
	}

	/**
	 ** @test
	 **
	 ** Requests through the middleware should receive CORS headers
	 ** and the original response body when no exception occurs.
	 **/
	public function healthy_requests_get_cors_headers_and_ok()
	{
		$response = $this->get('/test-cors');

		$response->assertStatus(200)
			->assertSee('OK')
			->assertHeader('Access-Control-Allow-Origin', '*')
			->assertHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE')
			->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');
	}

	/**
	 ** @test
	 **
	 ** When the underlying request throws, middleware logs an error
	 ** and returns a redirect back with an error flash.
	 **/
	public function exceptions_are_caught_logged_and_redirect_returned()
	{
		$middleware = new RevalidateBackHistory();
		$request   = \Illuminate\Http\Request::create('/test-cors-error', 'GET');

		// Session needed for ->with() flash
		$request->setLaravelSession(app('session.store'));

		$response = $middleware->handle($request, function () {
			throw new \Exception('boom');
		});

		$this->assertTrue($response->isRedirect());
		$this->assertNotEmpty(session('error'));
	}
}
