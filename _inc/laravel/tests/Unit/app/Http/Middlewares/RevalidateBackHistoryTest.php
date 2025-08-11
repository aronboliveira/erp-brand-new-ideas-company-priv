<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\{Log, Route};
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
	 ** When the underlying request throws, middleware logs a warning
	 ** and returns a 500 JSON response with 'Internal Server Error'.
	 **/
	public function exceptions_are_caught_logged_and_500_returned()
	{
		Log::shouldReceive('warning')
			->once()
			->with(
				RevalidateBackHistory::class . '::handle failed to set headers',
				\Mockery::on(function ($context) {
					return isset($context['message'], $context['uri'])
						&& $context['message'] === 'boom'
						&& str_starts_with($context['uri'], '/test-cors-error');
				})
			);

		$response = $this->get('/test-cors-error');

		$response->assertStatus(500)
			->assertSee('Internal Server Error');
	}
}
