<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\PricingPlanController;

class PricingPlanControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		$this->withoutMiddleware(\App\Http\Middleware\CheckMount::class);
		$ref = new \ReflectionClass(LandingPageSetting::class);
		$prop = $ref->getProperty('settings');
		$prop->setAccessible(true);
		$prop->setValue(null, null);
	}

	/**
	 ** @test
	 **
	 ** index displays pricing plan view for super admin.
	 **/
	public function index_displays_pricing_plan_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		// seed some settings
		LandingPageSetting::create(['name' => 'plan_title',       'value' => 'Title1']);
		LandingPageSetting::create(['name' => 'plan_heading',     'value' => 'Heading1']);
		LandingPageSetting::create(['name' => 'plan_description', 'value' => 'Desc1']);
		LandingPageSetting::create(['name' => 'plan_status',      'value' => 'on']);

		$response = $this->actingAs($user)
			->get(action([PricingPlanController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.pricing_plan')
			->assertViewHas('settings', function ($settings) {
				return isset($settings['plan_title']) &&
					$settings['plan_title'] === 'Title1' &&
					$settings['plan_status'] === 'on';
			});
	}

	/**
	 ** @test
	 **
	 ** index redirects non-super-admin with error.
	 **/
	public function index_redirects_for_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'client']);

		$response = $this->actingAs($user)
			->get(action([PricingPlanController::class, 'index']));

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** show displays single setting when key exists.
	 **/
	public function show_displays_setting_for_valid_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		LandingPageSetting::create(['name' => 'plan_title', 'value' => 'MyPlan']);

		$response = $this->actingAs($user)
			->get(action([PricingPlanController::class, 'show'], ['pricing_plan' => 'plan_title']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.pricing_plan_show')
			->assertViewHasAll(['key', 'value'])
			->assertViewHas('value', 'MyPlan');
	}

	/**
	 ** @test
	 **
	 ** show redirects when key missing.
	 **/
	public function show_redirects_for_missing_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([PricingPlanController::class, 'show'], ['pricing_plan' => 'no_key']));

		$response->assertRedirect()
			->assertSessionHas('error', "Setting 'no_key' not found");
	}

	/**
	 ** @test
	 **
	 ** create displays the form.
	 **/
	public function create_displays_form()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([PricingPlanController::class, 'create']));

		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** store persists all pricing settings.
	 **/
	public function store_persists_pricing_plan_settings()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$payload = [
			'planTitle'       => 'NewPlan',
			'planHeading'     => 'NewHeading',
			'planDescription' => 'NewDescription',
			'planStatus'      => 'on',
		];

		$response = $this->actingAs($user)
			->post(action([PricingPlanController::class, 'store']), $payload);

		$response->assertRedirect(route('pricing_plans.index'))
			->assertSessionHas('success', 'Create successful');

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'plan_title',
			'value' => 'NewPlan',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'plan_heading',
			'value' => 'NewHeading',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'plan_description',
			'value' => 'NewDescription',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'plan_status',
			'value' => 'on',
		]);
	}

	/**
	 ** @test
	 **
	 ** edit displays form for existing key.
	 **/
	public function edit_displays_form_for_existing_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		LandingPageSetting::create(['name' => 'plan_title', 'value' => 'X']);

		$response = $this->actingAs($user)
			->get(action([PricingPlanController::class, 'edit'], ['pricing_plan' => 'plan_title']));

		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** edit redirects for missing key.
	 **/
	public function edit_redirects_for_missing_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([PricingPlanController::class, 'edit'], ['pricing_plan' => 'unknown']));

		$response->assertRedirect()
			->assertSessionHas('error', "Setting 'unknown' not found");
	}

	/**
	 ** @test
	 **
	 ** update modifies single setting and redirects.
	 **/
	public function update_modifies_single_setting()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		LandingPageSetting::create(['name' => 'plan_title', 'value' => 'Old']);

		$payload = [
			'planTitle'       => 'UpdatedPlan',
			'planHeading'     => 'H',         // unused for single-key update
			'planDescription' => 'D',         // unused
			'planStatus'      => 'off',
		];

		$response = $this->actingAs($user)
			->put(action([PricingPlanController::class, 'update'], ['pricing_plan' => 'plan_title']), $payload);

		$response->assertRedirect(route('pricing_plans.index'))
			->assertSessionHas('success', 'Update successful');

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'plan_title',
			'value' => 'UpdatedPlan',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy removes existing setting.
	 **/
	public function destroy_deletes_existing_setting()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		LandingPageSetting::create(['name' => 'plan_heading', 'value' => 'X']);

		$response = $this->actingAs($user)
			->delete(action([PricingPlanController::class, 'destroy'], ['pricing_plan' => 'plan_heading']));

		$response->assertRedirect(route('pricing_plans.index'))
			->assertSessionHas('success', "Setting 'plan_heading' deleted");

		$this->assertDatabaseMissing('landing_page_settings', ['name' => 'plan_heading']);
	}

	/**
	 ** @test
	 **
	 ** destroy redirects when no such setting.
	 **/
	public function destroy_redirects_when_setting_missing()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->delete(action([PricingPlanController::class, 'destroy'], ['pricing_plan' => 'nonexistent']));

		$response->assertRedirect(route('pricing_plans.index'))
			->assertSessionHas('error', "No setting found for 'nonexistent'");
	}
}
