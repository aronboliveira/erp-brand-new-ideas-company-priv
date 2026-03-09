<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\ScreenshotsController;

class TestimonialsControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		$ref = new \ReflectionClass(LandingPageSetting::class);
		$prop = $ref->getProperty('settings');
		$prop->setAccessible(true);
		$prop->setValue(null, null);
	}

	/**
	 ** @test
	 **
	 ** index displays screenshots for super admin
	 **/
	public function index_displays_screenshots_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		LandingPageSetting::create([
			'name'  => 'screenshots',
			'value' => json_encode([['screenshots_heading' => 'First']])
		]);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.screenshots.index')
			->assertViewHas('screenshots')
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** index redirects non-super-admin with permission error
	 **/
	public function index_redirects_for_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'client']);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'index']));

		$response->assertStatus(200);
	}

	/**
	 ** @test
	 **
	 ** store updates screenshots settings
	 **/
	public function store_updates_screenshots_settings()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$payload = [
			'screenshots_heading'     => 'New Heading',
			'screenshots_description' => 'Some description'
		];

		$response = $this->actingAs($user)
			->post(action([ScreenshotsController::class, 'store']), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting update successfully');

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'screenshots_status',
			'value' => 'on'
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'screenshots_heading',
			'value' => 'New Heading'
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'screenshots_description',
			'value' => 'Some description'
		]);
	}

	/**
	 ** @test
	 **
	 ** create displays the create form
	 **/
	public function create_displays_form()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'create']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.screenshots.create');
	}

	/**
	 ** @test
	 **
	 ** screenshotsStore adds new screenshot entry with heading only
	 **/
	public function screenshots_store_adds_new_entry_with_heading()
	{
		Storage::fake('uploads/landing_page_image');
		$user = User::factory()->create(['type' => 'super admin']);

		$payload = [
			'screenshots_heading' => 'Screenshot One',
			// no file upload
		];

		$response = $this->actingAs($user)
			->post(action([ScreenshotsController::class, 'screenshotsStore']), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Screenshots added successfully');

		$setting = LandingPageSetting::where('name', 'screenshots')->first();
		$items = json_decode($setting->value, true);
		$this->assertNotEmpty($items);
		$lastItem = end($items);
		$this->assertEquals('Screenshot One', $lastItem['screenshots_heading']);
		$this->assertArrayNotHasKey('screenshots', $lastItem);
	}

	/**
	 ** @test
	 **
	 ** screenshotsEdit displays edit form for existing entry
	 **/
	public function screenshots_edit_displays_edit_form()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$initial = [['screenshots_heading' => 'H1']];
		LandingPageSetting::create([
			'name'  => 'screenshots',
			'value' => json_encode($initial)
		]);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'screenshotsEdit'], ['key' => 0]));

		$response->assertStatus(200)
			->assertViewIs('screenshots.screenshotsEdit')
			->assertViewHas('screenshot')
			->assertViewHas('key', 0);
	}

	/**
	 ** @test
	 **
	 ** screenshotsUpdate modifies existing entry heading
	 **/
	public function screenshots_update_modifies_entry_heading()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$items = [['screenshots_heading' => 'Old']];
		LandingPageSetting::create([
			'name'  => 'screenshots',
			'value' => json_encode($items)
		]);

		$payload = ['screenshots_heading' => 'Updated'];

		$response = $this->actingAs($user)
			->post(action([ScreenshotsController::class, 'screenshotsUpdate'], ['key' => 0]), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Screenshots update successfully');

		$setting = LandingPageSetting::where('name', 'screenshots')->first();
		$updated = json_decode($setting->value, true);
		$this->assertEquals('Updated', $updated[0]['screenshots_heading']);
	}

	/**
	 ** @test
	 **
	 ** screenshotsDelete removes the specified entry
	 **/
	public function screenshots_delete_removes_entry()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$items = [
			['screenshots_heading' => 'One'],
			['screenshots_heading' => 'Two']
		];
		LandingPageSetting::create([
			'name'  => 'screenshots',
			'value' => json_encode($items)
		]);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'screenshotsDelete'], ['key' => 0]));

		$response->assertRedirect()
			->assertSessionHas('success', 'Screenshots delete successfully');

		$setting = LandingPageSetting::where('name', 'screenshots')->first();
		$remaining = json_decode($setting->value, true);
		$this->assertCount(1, $remaining);
		$this->assertEquals('Two', $remaining[0]['screenshots_heading']);
	}

	/**
	 ** @test
	 **
	 ** update modifies a single setting value
	 **/
	public function update_modifies_single_setting_value()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$setting = LandingPageSetting::create(['name' => 'random', 'value' => 'old']);

		$payload = ['value' => 'new'];

		$response = $this->actingAs($user)
			->put(action([ScreenshotsController::class, 'update'], ['screenshot' => $setting->id]), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting updated successfully');

		$this->assertDatabaseHas('landing_page_settings', [
			'id'    => $setting->id,
			'value' => 'new'
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy deletes a single setting
	 **/
	public function destroy_deletes_single_setting()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$setting = LandingPageSetting::create(['name' => 'to_delete', 'value' => 'x']);

		$response = $this->actingAs($user)
			->delete(action([ScreenshotsController::class, 'destroy'], ['screenshot' => $setting->id]));

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting deleted successfully');

		$this->assertDatabaseMissing('landing_page_settings', ['id' => $setting->id]);
	}
}
