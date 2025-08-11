<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\Auth\ConfirmablePasswordController;

class ConfirmablePasswordControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Redirect guests from the password confirmation page to the login screen.
	 **/
	public function show_redirects_guests_to_login()
	{
		$response = $this->get(action([ConfirmablePasswordController::class, 'show']));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Display the password confirmation view for authenticated users.
	 **/
	public function show_displays_confirm_password_view_for_authenticated_users()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->get(action([ConfirmablePasswordController::class, 'show']));

		$response->assertOk();
		$response->assertViewIs('auth.confirm-password');
	}

	/**
	 ** @test
	 **
	 ** When an incorrect password is submitted, redirect back
	 ** to the confirmation form with validation errors.
	 **/
	public function store_with_invalid_password_redirects_back_with_errors()
	{
		$user = User::factory()->create([
			'password' => Hash::make('correct-password'),
		]);

		$response = $this->actingAs($user)
			->from(action([ConfirmablePasswordController::class, 'show']))
			->post(action([ConfirmablePasswordController::class, 'store']), [
				'password' => 'wrong-password',
			]);

		$response->assertRedirect(action([ConfirmablePasswordController::class, 'show']));
		$response->assertSessionHasErrors('password');
	}

	/**
	 ** @test
	 **
	 ** When the correct password is submitted, redirect to HOME 
	 ** and store the confirmation timestamp in session.
	 **/
	public function store_with_valid_password_redirects_home_and_sets_session_timestamp()
	{
		$user = User::factory()->create([
			'password' => Hash::make('secret123'),
		]);

		$response = $this->actingAs($user)
			->from(action([ConfirmablePasswordController::class, 'show']))
			->post(action([ConfirmablePasswordController::class, 'store']), [
				'password' => 'secret123',
			]);

		$response->assertRedirect(RouteServiceProvider::HOME);
		$response->assertSessionHas('auth.password_confirmed_at');
	}
}
