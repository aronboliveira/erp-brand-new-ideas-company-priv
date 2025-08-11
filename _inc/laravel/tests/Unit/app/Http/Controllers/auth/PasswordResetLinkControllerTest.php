<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Auth\PasswordResetLinkController;

class PasswordResetLinkControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Display the password reset request form when visiting the create action.
	 **/
	public function create_displays_password_reset_request_form()
	{
		$response = $this->get(action([PasswordResetLinkController::class, 'create']));

		$response->assertOk();
		$response->assertViewIs('auth.passwords.email');
	}

	/**
	 ** @test
	 **
	 ** Redirect back with validation errors if email is missing in the store action.
	 **/
	public function store_redirects_back_with_validation_error_if_email_is_missing()
	{
		$response = $this->post(action([PasswordResetLinkController::class, 'store']), []);

		$response->assertRedirect();
		$response->assertSessionHasErrors('email');
	}

	/**
	 ** @test
	 **
	 ** Redirect back with validation errors if email is not a valid email address.
	 **/
	public function store_redirects_back_with_validation_error_if_email_is_invalid()
	{
		$response = $this->post(action([PasswordResetLinkController::class, 'store']), [
			'email' => 'not-an-email',
		]);

		$response->assertRedirect();
		$response->assertSessionHasErrors('email');
	}

	/**
	 ** @test
	 **
	 ** Send a password reset link and redirect back with a status message on success.
	 **/
	public function store_sends_reset_link_and_redirects_with_status_on_success()
	{
		User::factory()->create(['email' => 'foo@example.com']);

		Password::shouldReceive('sendResetLink')
			->once()
			->with(['email' => 'foo@example.com'])
			->andReturn(Password::RESET_LINK_SENT);

		$response = $this->post(action([PasswordResetLinkController::class, 'store']), [
			'email' => 'foo@example.com',
		]);

		$response->assertRedirect();
		$response->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
	}

	/**
	 ** @test
	 **
	 ** Redirect back with errors when sending the reset link fails for an unknown user.
	 **/
	public function store_redirects_back_with_errors_when_send_link_fails_for_invalid_user()
	{
		Password::shouldReceive('sendResetLink')
			->once()
			->with(['email' => 'unknown@example.com'])
			->andReturn(Password::INVALID_USER);

		$response = $this->post(action([PasswordResetLinkController::class, 'store']), [
			'email' => 'unknown@example.com',
		]);

		$response->assertRedirect();
		$response->assertSessionHasErrors('email');
	}

	/**
	 ** @test
	 **
	 ** Catch exceptions from the password broker and display a generic SMTP error message.
	 **/
	public function store_catches_exceptions_and_returns_smtp_error_message()
	{
		User::factory()->create(['email' => 'bar@example.com']);

		Password::shouldReceive('sendResetLink')
			->once()
			->with(['email' => 'bar@example.com'])
			->andThrow(new \Exception('SMTP failure'));

		$response = $this->post(action([PasswordResetLinkController::class, 'store']), [
			'email' => 'bar@example.com',
		]);

		$response->assertRedirect();

		$errors = session('errors')->all();
		$this->assertEquals(
			'E-Mail has not been sent due to SMTP configuration',
			$errors[0]
		);
	}
}
