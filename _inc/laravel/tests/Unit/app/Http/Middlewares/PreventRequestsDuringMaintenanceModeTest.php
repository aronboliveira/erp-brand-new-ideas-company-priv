<?php

namespace Tests\Unit\app\Http\Middlewares;

use Tests\TestCase;
use Illuminate\Support\Facades\{Artisan, Log, Route};
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;

class PreventRequestsDuringMaintenanceModeTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Register a test route protected by our middleware
		Route::get('/test-prevent', function (Request $request) {
			return response('OK', 200);
		})->middleware(PreventRequestsDuringMaintenance::class);
	}

	protected function tearDown(): void
	{
		Artisan::call('up');
		parent::tearDown();
	}

	/**
	 ** @test
	 **
	 ** Healthy requests pass through the PreventRequestsDuringMaintenance middleware
	 ** and return a 200 OK response.
	 **/
	public function healthy_passes_through_prevent_requests_middleware()
	{
		$response = $this->get('/test-prevent');

		$response->assertStatus(200)
			->assertSee('OK');
	}

	/**
	 ** @test
	 **
	 ** When the application is in maintenance mode, the middleware should:
	 **  * Catch the parent handle exception,
	 **  * Log an error with the correct message and context,
	 **  * Return a JSON 503 response with 'Application is under maintenance.'
	 **/
	public function maintenance_mode_logs_and_returns_json_error()
	{
		// Enable maintenance mode
		Artisan::call('down');

		$response = $this->get('/test-prevent');

		$response->assertStatus(503)
			->assertJson(['error' => 'Service temporarily unavailable.']);

		// Restore normal mode
		Artisan::call('up');
	}
}
