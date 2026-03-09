<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\{Artisan, Route};
use App\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Http\Request;

class CheckForMaintenanceModeTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Register a test route protected by our middleware
		Route::get('/test-middleware', function (Request $request) {
			return response('OK', 200);
		})->middleware(CheckForMaintenanceMode::class);
	}

	protected function tearDown(): void
	{
		// Always bring the app back up to prevent maintenance mode from
		// leaking to subsequent tests if an assertion fails mid-test.
		Artisan::call('up');
		parent::tearDown();
	}

	/**
	 ** @test
	 **
	 ** When the application is not in maintenance mode,
	 ** the middleware should allow the request to pass through.
	 **/
	public function healthy_passes_through_middleware()
	{
		$response = $this->get('/test-middleware');

		$response->assertStatus(200)
			->assertSee('OK');
	}

	/**
	 ** @test
	 **
	 ** When the application is put into maintenance mode,
	 ** the middleware should catch the HttpException
	 ** and return a JSON 503 response with the correct error message.
	 **/
	public function maintenance_mode_returns_503_json()
	{
		// Enable maintenance mode
		Artisan::call('down');

		$response = $this->get('/test-middleware');

		$response->assertStatus(503)
			->assertJson(['error' => 'Service temporarily unavailable.']);

		// Bring the application back up
		Artisan::call('up');
	}
}
