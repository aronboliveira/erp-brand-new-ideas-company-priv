<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\Auth\EmailVerificationPromptController;

class EmailVerificationPromptControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** When a user’s email is not verified, invoking the controller
	 ** should display the email verification prompt view.
	 **/
	public function invoke_shows_verify_view_when_email_not_verified()
	{
		$user = User::factory()->unverified()->create();

		$response = $this->actingAs($user)
			->get(action([EmailVerificationPromptController::class, '__invoke']));

		$response->assertOk();
		$response->assertViewIs('auth.verify');
	}

	/**
	 ** @test
	 **
	 ** When a user’s email is already verified, invoking the controller
	 ** should redirect them to the home route.
	 **/
	public function invoke_redirects_home_when_email_is_verified()
	{
		$user = User::factory()->create([
			'email_verified_at' => now(),
		]);

		$response = $this->actingAs($user)
			->get(action([EmailVerificationPromptController::class, '__invoke']));

		$response->assertRedirect(RouteServiceProvider::HOME);
	}

	/**
	 ** @test
	 **
	 ** Passing a locale parameter to showVerifyForm should set the
	 ** application locale accordingly and render the verify view
	 ** with the chosen language.
	 **/
	public function show_verify_form_sets_locale_to_passed_lang()
	{
		$lang = 'pt';

		$response = $this->get(action(
			[EmailVerificationPromptController::class, 'showVerifyForm'],
			['lang' => $lang]
		));

		$response->assertOk();
		$response->assertViewIs('auth.verify');
		$response->assertViewHas('lang', $lang);
		$this->assertEquals($lang, App::getLocale());
	}
}
