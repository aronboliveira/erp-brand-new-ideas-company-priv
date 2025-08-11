<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Employee;
use App\Models\Warning;
use App\Models\Plan;
use App\Models\NotificationTemplateLangs;
use App\Models\NotificationTemplates;

class ClientControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index should display clients for authorized users
	 **/
	public function index_displays_clients_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage client');

		// create two client users
		User::create([
			'name'       => 'Client One',
			'email'      => 'one@example.com',
			'password'   => Hash::make('password'),
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);
		User::create([
			'name'       => 'Client Two',
			'email'      => 'two@example.com',
			'password'   => Hash::make('password'),
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('clients.index'));

		$response->assertStatus(200);
		$response->assertViewIs('clients.index');
		$response->assertViewHas('clients', function ($clients) {
			return $clients->count() === 2;
		});
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('clients.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create should display the correct view for authorized users (non-Ajax)
	 **/
	public function create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create client');

		$response = $this->actingAs($user)->get(route('clients.create'));

		$response->assertStatus(200);
		$response->assertViewIs('clients.create');
		$response->assertViewHas('customFields');
	}

	/**
	 ** @test
	 **
	 ** create should return Ajax view when requested via XHR
	 **/
	public function create_returns_ajax_view_when_ajax()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create client');

		$response = $this->actingAs($user)
			->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
			->get(route('clients.create'));

		$response->assertStatus(200);
		$response->assertViewIs('clients.createAjax');
	}

	/**
	 ** @test
	 **
	 ** store should redirect back on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('create client');

		$response = $this->actingAs($user)
			->from(route('clients.create'))
			->post(route('clients.store'), []);

		$response->assertRedirect(route('clients.create'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store should create a new client and redirect on success
	 **/
	public function store_creates_client_and_redirects_on_success()
	{
		// prepare user, plan, and settings
		$user = User::factory()->create();
		User::macro('plan', fn () => 1);
		DB::table('plans')->insert(['id' => 1, 'max_clients' => -1]);
		DB::table('settings')->insert([
			['name' => 'default_language', 'value' => 'en', 'created_by' => $user?->creatorId()],
		]);

		$user?->givePermissionTo('create client');

		$payload = [
			'name'     => 'New Client',
			'email'    => 'newclient@example.com',
			'password' => 'secret123',
		];

		$response = $this->actingAs($user)
			->post(route('clients.store'), $payload);

		$response->assertRedirect(route('clients.index'));
		$this->assertDatabaseHas('users', [
			'email' => 'newclient@example.com',
			'type'  => 'client',
		]);
	}

	/**
	 ** @test
	 **
	 ** show should display client details for authorized users
	 **/
	public function show_displays_client_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('view client');

		$client = User::factory()->create([
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('clients.show', $client));

		$response->assertStatus(200);
		$response->assertViewIs('clients.show');
		$response->assertViewHas('client', $client);
	}

	/**
	 ** @test
	 **
	 ** edit should display the edit form for authorized users
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit client');

		$client = User::factory()->create([
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('clients.edit', $client));

		$response->assertStatus(200);
		$response->assertViewIs('clients.edit');
		$response->assertViewHasAll(['client', 'customFields']);
	}

	/**
	 ** @test
	 **
	 ** update should redirect back on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit client');

		$client = User::factory()->create([
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->from(route('clients.edit', $client))
			->put(route('clients.update', $client), [
				'name'  => '',
				'email' => 'not-an-email',
			]);

		$response->assertRedirect(route('clients.edit', $client));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should modify client and redirect on success
	 **/
	public function update_modifies_client_and_redirects_on_success()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit client');

		$client = User::factory()->create([
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'name'  => 'Updated Name',
			'email' => 'updated@example.com',
		];

		$response = $this->actingAs($user)
			->put(route('clients.update', $client), $payload);

		$response->assertRedirect();
		$this->assertDatabaseHas('users', [
			'id'    => $client->id,
			'name'  => 'Updated Name',
			'email' => 'updated@example.com',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete client and redirect on success
	 **/
	public function destroy_deletes_client_and_redirects_on_success()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('delete client');

		$client = User::factory()->create([
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->delete(route('clients.destroy', $client));

		$response->assertRedirect();
		$this->assertDatabaseMissing('users', ['id' => $client->id]);
	}

	/**
	 ** @test
	 **
	 ** clientPassword should display reset form for authorized users
	 **/
	public function clientPassword_displays_reset_form_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit client');

		$client = User::factory()->create([
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
		]);

		$encryptedId = Crypt::encrypt($client->id);

		$response = $this->actingAs($user)->get(route('clients.password', $encryptedId));

		$response->assertStatus(200);
		$response->assertViewIs('clients.reset');
		$response->assertViewHasAll(['user', 'client']);
	}

	/**
	 ** @test
	 **
	 ** clientPasswordReset should update password and redirect on success
	 **/
	public function clientPasswordReset_updates_password_and_redirects_on_success()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('edit client');

		$client = User::factory()->create([
			'type'       => 'client',
			'created_by' => $user?->creatorId(),
			'password'   => Hash::make('oldpass'),
		]);

		$payload = [
			'password'              => 'newpass123',
			'password_confirmation' => 'newpass123',
		];

		$response = $this->actingAs($user)
			->post(route('clients.password.reset', $client->id), $payload);

		$response->assertRedirect(route('clients.index'));
		$this->assertTrue(Hash::check('newpass123', $client->fresh()->password));
	}
}
