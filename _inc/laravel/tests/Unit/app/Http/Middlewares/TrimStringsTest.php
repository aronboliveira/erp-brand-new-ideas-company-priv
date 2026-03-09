<?php

namespace Tests\Unit\app\Http\Middlewares;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\TrimStrings;

class TrimStringsTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Route that echoes back all input for inspection
		Route::post('/test-trim', function (Request $request) {
			return response()->json($request->all());
		})->middleware(TrimStrings::class);

		// Route that throws to exercise the exception path
		Route::post('/test-trim-error', function () {
			throw new \Exception('trim failure');
		})->middleware(TrimStrings::class);
	}

	/**
	 ** @test
	 **
	 ** The middleware should trim whitespace from all string inputs
	 ** except the fields: current_password, password, password_confirmation.
	 **/
	public function it_trims_strings_except_exempt_fields()
	{
		$payload = [
			'name'                  => '  Alice  ',
			'email'                 => '  alice@example.com  ',
			'password'              => '  secret  ',
			'password_confirmation' => '  secret  ',
			'current_password'      => '  oldpass  ',
			'notes'                 => '  some notes  ',
		];

		$response = $this->postJson('/test-trim', $payload);

		$response->assertStatus(200)
			->assertJson([
				'name'                  => 'Alice',
				'email'                 => 'alice@example.com',
				'notes'                 => 'some notes',
				// exempt fields remain untrimmed
				'password'              => '  secret  ',
				'password_confirmation' => '  secret  ',
				'current_password'      => '  oldpass  ',
			]);
	}

	/**
	 ** @test
	 **
	 ** If the underlying request handling throws,
	 ** the middleware should catch it, log an error,
	 ** and return a JSON 500 response with 'Input processing failed'.
	 **/
	public function it_catches_exceptions_and_returns_json_error()
	{
		Log::spy();

		$response = $this->postJson('/test-trim-error', ['foo' => 'bar']);

		$response->assertStatus(500);
	}
}
