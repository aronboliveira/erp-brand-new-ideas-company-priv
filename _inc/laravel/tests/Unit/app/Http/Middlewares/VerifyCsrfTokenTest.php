<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\VerifyCsrfToken;

class VerifyCsrfTokenTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Route matching an exempt URI pattern (must match VerifyCsrfToken::EXEMPT_URIS)
		Route::post('/plan-pay-with-paymentwall/123', function () {
			return response('EXEMPT OK', 200);
		})->middleware('web');

		// Non-exempt route
		Route::post('/protected', function () {
			return response('PROTECTED OK', 200);
		})->middleware('web');
	}

	/**
	 ** @test
	 **
	 ** POST to an exempt URI should bypass CSRF verification
	 ** and return 200 with the expected response.
	 **/
	public function exempt_uris_bypass_csrf_protection()
	{
		$response = $this->post('/plan-pay-with-paymentwall/123');

		$response->assertStatus(200)
			->assertSee('EXEMPT OK');
	}

	/**
	 ** @test
	 **
	 ** POST to a non-exempt URI without a CSRF token
	 ** should fail with a 419 status (CSRF token mismatch).
	 **/
	public function non_exempt_uris_require_csrf_token()
	{
		// Laravel's parent VerifyCsrfToken bypasses CSRF when runningUnitTests().
		// Temporarily switch APP_ENV so the bypass is disabled.
		$prev = $this->app['env'];
		$this->app->detectEnvironment(fn () => 'production');

		$response = $this->post('/protected');

		$this->app->detectEnvironment(fn () => $prev);

		$response->assertStatus(419);
	}

	/**
	 ** @test
	 **
	 ** POST to a non-exempt URI with a valid CSRF token
	 ** should succeed and return the expected response.
	 **/
	public function non_exempt_uris_allow_valid_csrf_token()
	{
		$token = csrf_token();
		$response = $this->withSession(['_token' => $token])
			->post('/protected', ['_token' => $token]);

		$response->assertStatus(200)
			->assertSee('PROTECTED OK');
	}
}
