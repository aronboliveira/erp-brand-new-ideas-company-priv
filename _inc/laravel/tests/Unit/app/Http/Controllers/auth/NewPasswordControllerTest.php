<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class NewPasswordControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** When visiting the reset-password route with a valid token and email,
	 ** the reset form should display the correct view and include
	 ** the token and email in the request data.
	 **/
	public function reset_form_displays_with_token_and_email()
	{
		$user = User::factory()->create();
		/** @var PasswordBroker $broker */
		$broker = Password::broker();
		$token = $broker->createToken($user);

		$response = $this->get("/reset-password?token={$token}&email={$user?->email}");

		$response->assertStatus(200);
		$response->assertViewIs('auth.passwords.reset');
		$response->assertViewHas('request', function ($req) use ($token, $user) {
			return $req->token === $token
				&& $req->email === $user?->email;
		});
	}

	/**
	 ** @test
	 **
	 ** A user should be able to reset their password when providing
	 ** a valid token, matching email, and correctly confirmed new password.
	 ** After a successful reset, they are redirected to login with a status message,
	 ** and their password is updated in the database.
	 **/
	public function user_can_reset_password_with_valid_token()
	{
		$user = User::factory()->create([
			'password' => Hash::make('old-password'),
		]);
		/** @var PasswordBroker $broker */
		$broker = Password::broker();
		$token = $broker->createToken($user);

		$response = $this->post('/reset-password', [
			'token'                 => $token,
			'email'                 => $user?->email,
			'password'              => 'new-secure-password',
			'password_confirmation' => 'new-secure-password',
		]);

		$response
			->assertRedirect(route('login'))
			->assertSessionHas('status', trans(Password::PASSWORD_RESET));

		$this->assertTrue(
			Hash::check('new-secure-password', $user?->fresh()->password),
			'Password was not updated in the database'
		);
	}

	/**
	 ** @test
	 **
	 ** Submitting the reset form with an invalid token should fail,
	 ** redirect back to the form preserving token and email,
	 ** present validation errors for the email field,
	 ** and leave the user’s existing password unchanged.
	 **/
	public function reset_fails_with_invalid_token()
	{
		$user = User::factory()->create([
			'password' => Hash::make('old-password'),
		]);

		$response = $this->from('/reset-password?token=bad&email=' . $user?->email)
			->post('/reset-password', [
				'token'                 => 'bad-token',
				'email'                 => $user?->email,
				'password'              => 'whatever',
				'password_confirmation' => 'whatever',
			]);

		$response
			->assertRedirect('/reset-password?token=bad&email=' . urlencode($user?->email))
			->assertSessionHasErrors('email');

		$this->assertTrue(
			Hash::check('old-password', $user?->fresh()->password),
			'Password should not have changed on failure'
		);
	}
}
