<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\LandingPageController;
use Spatie\Permission\Models\Permission;

class LandingPageControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index displays topbar settings view for super admin with permission.
	 **/
	public function index_displays_topbar_view_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([LandingPageController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.topbar');
	}

	/**
	 ** @test
	 **
	 ** index redirects with error for non-super-admin users.
	 **/
	public function index_redirects_for_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'company']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([LandingPageController::class, 'index']));

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** create returns the create settings form view.
	 **/
	public function create_returns_create_form()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([LandingPageController::class, 'create']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.create');
	}

	/**
	 ** @test
	 **
	 ** store saves topbar settings and redirects back with success.
	 **/
	public function store_saves_settings_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$payload = [
			'topbarStatus'          => true,
			'topbarNotificationMsg' => 'Hello World',
		];

		$response = $this->actingAs($user)
			->post(action([LandingPageController::class, 'store']), $payload);

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('success', __('Topbar settings updated successfully'));

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'topbar_status',
			'value' => 'on',
			'created_by' => $user?->id,
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'topbar_notification_msg',
			'value' => 'Hello World',
			'created_by' => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** update modifies a single setting by id and redirects back.
	 **/
	public function update_modifies_setting_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$setting = LandingPageSetting::create([
			'name'       => 'topbar_notification_msg',
			'value'      => 'Old',
			'created_by' => $user?->id,
		]);

		$response = $this->actingAs($user)
			->put(action([LandingPageController::class, 'update'], ['id' => $setting->id]), [
				'value' => 'New message',
			]);

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('success', __('Setting updated successfully'));

		$this->assertDatabaseHas('landing_page_settings', [
			'id'    => $setting->id,
			'value' => 'New message',
		]);
	}

	/**
	 ** @test
	 **
	 ** update returns error when setting not found.
	 **/
	public function update_returns_error_when_setting_not_found()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->put(action([LandingPageController::class, 'update'], ['id' => 999]), [
				'value' => 'Does not matter',
			]);

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('error', __('Setting not found'));
	}

	/**
	 ** @test
	 **
	 ** destroy deletes a setting by id and redirects back.
	 **/
	public function destroy_deletes_setting_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$setting = LandingPageSetting::create([
			'name'       => 'topbar_notification_msg',
			'value'      => 'To be deleted',
			'created_by' => $user?->id,
		]);

		$response = $this->actingAs($user)
			->delete(action([LandingPageController::class, 'destroy'], ['id' => $setting->id]));

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('success', __('Setting deleted successfully'));

		$this->assertDatabaseMissing('landing_page_settings', ['id' => $setting->id]);
	}

	/**
	 ** @test
	 **
	 ** destroy returns error when setting not found.
	 **/
	public function destroy_returns_error_when_setting_not_found()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->delete(action([LandingPageController::class, 'destroy'], ['id' => 999]));

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('error', __('Setting not found'));
	}

	/**
	 ** @test
	 **
	 ** show displays a single setting view for existing id.
	 **/
	public function show_displays_single_setting_view()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$setting = LandingPageSetting::create([
			'name'       => 'topbar_status',
			'value'      => 'on',
			'created_by' => $user?->id,
		]);

		$response = $this->actingAs($user)
			->get(action([LandingPageController::class, 'show'], ['id' => $setting->id]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.show')
			->assertViewHas('setting', function ($s) use ($setting) {
				return $s->id === $setting->id;
			});
	}

	/**
	 ** @test
	 **
	 ** show returns error when setting not found.
	 **/
	public function show_returns_error_when_setting_not_found()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([LandingPageController::class, 'show'], ['id' => 999]));

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('error', __('Setting not found'));
	}

	/**
	 ** @test
	 **
	 ** edit displays edit form for existing setting.
	 **/
	public function edit_displays_edit_form_for_existing_setting()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$setting = LandingPageSetting::create([
			'name'       => 'topbar_status',
			'value'      => 'off',
			'created_by' => $user?->id,
		]);

		$response = $this->actingAs($user)
			->get(action([LandingPageController::class, 'edit'], ['id' => $setting->id]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.edit')
			->assertViewHas('setting', function ($s) use ($setting) {
				return $s->id === $setting->id;
			});
	}

	/**
	 ** @test
	 **
	 ** edit returns error when setting not found.
	 **/
	public function edit_returns_error_when_setting_not_found()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([LandingPageController::class, 'edit'], ['id' => 999]));

		$response->assertRedirect(route('landingpage.topbar'))
			->assertSessionHas('error', __('Setting not found'));
	}
}
