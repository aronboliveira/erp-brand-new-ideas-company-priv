<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Providers\RouteServiceProvider;

class RegisteredUserControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// By default, disable recaptcha to avoid validation errors
		config(['app.recaptcha_module' => 'off']);
	}

	/**
	 ** @test
	 **
	 ** When signup is disabled in settings, the registration form route
	 ** should redirect to the login page instead of displaying the form.
	 **/
	public function show_registration_form_redirects_when_signup_disabled()
	{
		// no 'enable_signup' Utility record => signup disabled
		$response = $this->get(action([RegisteredUserController::class, 'showRegistrationForm']));

		$response->assertRedirect('login');
	}

	/**
	 ** @test
	 **
	 ** When signup is enabled and a language parameter is passed,
	 ** the registration form view should display with that locale.
	 **/
	public function show_registration_form_displays_view_with_lang_when_enabled()
	{
		Utility::create(['name' => 'enable_signup', 'value' => 'on']);
		Utility::create(['name' => 'default_language', 'value' => 'pt']);

		$response = $this->get(action(
			[RegisteredUserController::class, 'showRegistrationForm'],
			['lang' => 'pt']
		));

		$response->assertOk();
		$response->assertViewIs('auth.register');
		$response->assertViewHas('lang', 'pt');
		$this->assertEquals('pt', App::getLocale());
	}

	/**
	 ** @test
	 **
	 ** Submitting the registration form without required fields
	 ** should fail validation and return errors in session.
	 **/
	public function store_validates_required_fields()
	{
		Utility::create(['name' => 'enable_signup', 'value' => 'on']);
		Utility::create(['name' => 'default_language', 'value' => 'en']);
		Utility::create(['name' => 'email_verification', 'value' => 'off']);

		$response = $this->post(action([RegisteredUserController::class, 'store']), []);

		$response->assertSessionHasErrors(['name', 'email', 'password']);
	}

	/**
	 ** @test
	 **
	 ** When email verification is turned off, a successful registration
	 ** should create the user and immediately redirect to the home route.
	 **/
	public function store_creates_user_and_redirects_home_when_email_verification_off()
	{
		Utility::create(['name' => 'enable_signup', 'value' => 'on']);
		Utility::create(['name' => 'default_language', 'value' => 'en']);
		Utility::create(['name' => 'email_verification', 'value' => 'off']);

		$response = $this->post(action([RegisteredUserController::class, 'store']), [
			'name'                  => 'Alice Example',
			'email'                 => 'alice@example.com',
			'password'              => 'secret123',
			'password_confirmation' => 'secret123',
		]);

		$response->assertRedirect(RouteServiceProvider::HOME);

		$this->assertDatabaseHas('users', [
			'email' => 'alice@example.com',
		]);

		$user = User::where('email', 'alice@example.com')->first();
		$this->assertNotNull($user?->email_verified_at);
	}

	/**
	 ** @test
	 **
	 ** When email verification is required, after registration
	 ** the user should see the verify email view and not yet be verified.
	 **/
	public function store_shows_verify_view_when_email_verification_on()
	{
		Utility::create(['name' => 'enable_signup', 'value' => 'on']);
		Utility::create(['name' => 'default_language', 'value' => 'en']);
		Utility::create(['name' => 'email_verification', 'value' => 'on']);

		$response = $this->post(action([RegisteredUserController::class, 'store']), [
			'name'                  => 'Bob Example',
			'email'                 => 'bob@example.com',
			'password'              => 'secret123',
			'password_confirmation' => 'secret123',
		]);

		$response->assertOk();
		$response->assertViewIs('auth.verify');

		// user should exist but not yet marked verified
		$this->assertDatabaseHas('users', ['email' => 'bob@example.com']);
		$this->assertNull(User::where('email', 'bob@example.com')->first()->email_verified_at);
	}
}
