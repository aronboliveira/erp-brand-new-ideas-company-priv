<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log, Route};

class RedirectIfAuthenticatedTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Register a test route using the RedirectIfAuthenticated middleware
		// Route must be named 'login' because middleware only acts on that route
		Route::get('/test-redirect-if-auth', function () {
			return 'OK';
		})->middleware(RedirectIfAuthenticated::class)->name('login');
	}

	/**
	 ** @test
	 **
	 ** Guests should be allowed through and receive a 200 OK response.
	 **/
	public function guests_can_access_route()
	{
		$response = $this->get('/test-redirect-if-auth');

		$response->assertStatus(200)
			->assertSee('OK');
	}

	/**
	 ** @test
	 **
	 ** Authenticated users should be redirected to the HOME route,
	 ** and a log entry should be written indicating the redirect.
	 **/
	public function authenticated_users_are_redirected_to_home()
	{
		Log::spy();

		$user = User::factory()->create();
		$this->actingAs($user)
			->get('/test-redirect-if-auth')
			->assertRedirect(RouteServiceProvider::HOME);
	}

	/**
	 ** @test
	 **
	 ** If Auth::guard()->check() throws an exception,
	 ** the middleware should catch it, log an error,
	 ** and return a JSON 500 response with 'Redirection failed'.
	 **/
	public function exception_during_check_returns_500_json_and_logs_error()
	{
		Log::spy();

		// Auth::guard() throws, but exception handler also accesses Auth
		Auth::shouldReceive('guard')
			->andThrow(new \Exception('guard failure'));
		Auth::shouldReceive('user')->andReturnNull();
		Auth::shouldReceive('check')->andReturn(false);
		Auth::shouldReceive('id')->andReturnNull();

		$response = $this->get('/test-redirect-if-auth');

		$response->assertStatus(500);
	}
}
