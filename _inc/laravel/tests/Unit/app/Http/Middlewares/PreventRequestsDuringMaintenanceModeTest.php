<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\{Artisan, Log, Route};
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;

class PreventRequestsDuringTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Register a test route protected by our middleware
		Route::get('/test-prevent', function (Request $request) {
			return response('OK', 200);
		})->middleware(PreventRequestsDuringMaintenance::class);
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
		// Expect an error log with the middleware class::handle and context keys
		Log::shouldReceive('error')
			->once()
			->with(
				'App\Http\Middleware\PreventRequestsDuringMaintenance::handle failed during maintenance check',
				\Mockery::on(function ($context) {
					return isset($context['exception'], $context['message'], $context['uri']);
				})
			);

		// Enable maintenance mode
		Artisan::call('down');

		$response = $this->get('/test-prevent');

		$response->assertStatus(503)
			->assertJson(['error' => 'Application is under maintenance.']);

		// Restore normal mode
		Artisan::call('up');
	}
}
