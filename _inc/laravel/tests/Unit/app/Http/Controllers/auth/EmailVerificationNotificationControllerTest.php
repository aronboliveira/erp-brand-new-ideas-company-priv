<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationNotificationControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Redirect a user who has already verified their email
	 ** to the home page when requesting a new verification link.
	 **/
	public function verified_user_is_redirected_to_home()
	{
		$user = User::factory()->create();
		$user?->markEmailAsVerified();

		$this->actingAs($user);

		$response = $this->post('/email/verification-notification');

		$response->assertRedirect(RouteServiceProvider::HOME);
	}

	/**
	 ** @test
	 **
	 ** Send a new email verification notification
	 ** to an unverified user and flash the status message.
	 **/
	public function unverified_user_gets_a_verification_notification()
	{
		Notification::fake();

		$user = User::factory()->create([
			'email_verified_at' => null,
		]);

		$this->actingAs($user);

		$response = $this->post('/email/verification-notification');

		$response->assertRedirect(); // back()
		$response->assertSessionHas('status', 'verification-link-sent');

		Notification::assertSentTo($user, VerifyEmail::class);
	}
}
