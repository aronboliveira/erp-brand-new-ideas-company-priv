<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The login page should be visible and render the 'auth.login' view.
	 **/
	public function login_page_displays_correctly()
	{
		$response = $this->get('/login');
		$response->assertStatus(200);
		$response->assertViewIs('auth.login');
	}

	/**
	 ** @test
	 **
	 ** Submitting invalid credentials should fail authentication,
	 ** flash validation errors, and leave the user unauthenticated.
	 **/
	public function user_cannot_login_with_invalid_credentials()
	{
		$response = $this->post('/login', [
			'email'    => 'nonexistent@example.com',
			'password' => 'invalid',
		]);

		$response->assertSessionHasErrors();
		$this->assertGuest();
	}

	/**
	 ** @test
	 **
	 ** A user with valid credentials should be able to log in,
	 ** be redirected to the home route, and be marked as authenticated.
	 **/
	public function user_can_login_with_valid_credentials()
	{
		$user = User::factory()->create([
			'email'         => 'test@example.com',
			'password'      => Hash::make('password'),
			'type'          => 'company',
			'is_active'     => true,
			'delete_status' => true,
		]);

		$response = $this->post('/login', [
			'email'    => 'test@example.com',
			'password' => 'password',
		]);

		$response->assertRedirect(route('home'));
		$this->assertAuthenticatedAs($user);
	}

	/**
	 ** @test
	 **
	 ** An authenticated user should be able to log out,
	 ** be redirected to the root URL, and become a guest.
	 **/
	public function authenticated_user_can_logout()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->post('/logout');

		$response->assertRedirect('/');
		$this->assertGuest();
	}

	/**
	 ** @test
	 **
	 ** The customer login endpoint should authenticate using
	 ** the 'customer' guard and redirect to the customer dashboard.
	 **/
	public function customer_login_route_authenticates_customer_guard()
	{
		$customer = User::factory()->create([
			'email'     => 'customer@example.com',
			'password'  => Hash::make('secret'),
			'type'      => 'customer',
			'is_active' => true,
		]);

		$response = $this->post('/customer-login', [
			'email'    => 'customer@example.com',
			'password' => 'secret',
		]);

		$response->assertRedirect(route('customer.dashboard'));
		$this->assertAuthenticatedAs($customer, 'customer');
	}

	/**
	 ** @test
	 **
	 ** The vendor login endpoint should authenticate using
	 ** the 'vendor' guard and redirect to the vendor dashboard.
	 **/
	public function vendor_login_route_authenticates_vendor_guard()
	{
		$vendor = User::factory()->create([
			'email'     => 'vendor@example.com',
			'password'  => Hash::make('secret'),
			'type'      => 'vendor',
			'is_active' => true,
		]);

		$response = $this->post('/vendor-login', [
			'email'    => 'vendor@example.com',
			'password' => 'secret',
		]);

		$response->assertRedirect(route('vendor.dashboard'));
		$this->assertAuthenticatedAs($vendor, 'vendor');
	}
}
