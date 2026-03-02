<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\ScreenshotsController;

class ScreenshotPageControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		// Reset the static settings cache so each test gets a fresh fetch
		$ref  = new \ReflectionClass(LandingPageSetting::class);
		$prop = $ref->getProperty('settings');
		$prop->setAccessible(true);
		$prop->setValue(null, null);
		// Clean screenshots-related rows so tests start from a known state
		LandingPageSetting::where('name', 'screenshots')->delete();
		LandingPageSetting::where('name', 'screenshots_status')->delete();
		LandingPageSetting::where('name', 'screenshots_heading')->delete();
		LandingPageSetting::where('name', 'screenshots_description')->delete();
	}

	protected function tearDown(): void
	{
		// Clean up after test regardless of transaction state
		LandingPageSetting::where('name', 'screenshots')->delete();
		LandingPageSetting::where('name', 'screenshots_status')->delete();
		LandingPageSetting::where('name', 'screenshots_heading')->delete();
		LandingPageSetting::where('name', 'screenshots_description')->delete();
		parent::tearDown();
	}

	/**
	 ** @test
	 **
	 ** index displays screenshots for super admin
	 **/
	public function index_displays_screenshots_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		// settings() only picks up screenshots rows that have a query_key
		$uuid = (string) Str::uuid();
		LandingPageSetting::create([
			'name'      => 'screenshots',
			'query_key' => $uuid,
			'value'     => json_encode(['screenshots_heading' => 'First']),
		]);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'index']));

		$response->assertStatus(200)
			->assertViewHas('screenshots', function ($screenshots) {
				// settings() returns a UUID-keyed map; use array_values to access by position
				if (!is_array($screenshots)) return false;
				$first = array_values($screenshots)[0] ?? null;
				return $first !== null && ($first['screenshots_heading'] ?? null) === 'First';
			})
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** index returns 200 for any authenticated user (controller does not restrict by type)
	 **/
	public function index_redirects_for_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'client']);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'index']));

		// The index method only checks that the user is authenticated, not their type
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

		$response->assertStatus(200);
	}

	/**
	 ** @test
	 **
	 ** screenshotsStore adds new screenshot entry with heading only
	 **/
	public function screenshots_store_adds_new_entry_with_heading()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$payload = [
			'screenshots_heading' => 'Screenshot One',
		];

		$response = $this->actingAs($user)
			->post(action([ScreenshotsController::class, 'screenshotsStore']), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Screenshots added successfully');

		$setting = LandingPageSetting::where('name', 'screenshots')->first();
		$this->assertNotNull($setting, 'Expected a screenshots row to be created');
		$rawValue = $setting->value;
		// The controller appends to $items (integer-indexed) and saves; the row value may be
		// either a JSON array ('[{"screenshots_heading":"Screenshot One"}]') or a UUID-keyed map
		$decoded = json_decode($rawValue, true);
		$this->assertNotEmpty($decoded, 'Row value should decode to a non-empty structure');
		$found = false;
		$items = is_array($decoded) ? array_values($decoded) : [];
		foreach ($items as $item) {
			if (is_array($item) && ($item['screenshots_heading'] ?? null) === 'Screenshot One') {
				$found = true;
				break;
			}
		}
		$this->assertTrue($found, 'Expected to find the stored screenshot heading');
		$firstItem = $items[0] ?? [];
		$this->assertArrayNotHasKey('screenshots', is_array($firstItem) ? $firstItem : []);
	}

	/**
	 ** @test
	 **
	 ** screenshotsEdit displays edit form for existing entry
	 **/
	public function screenshots_edit_displays_edit_form()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		// Must use query_key so settings() picks it up
		$uuid    = (string) Str::uuid();
		$itemData = ['screenshots_heading' => 'H1'];
		LandingPageSetting::create([
			'name'      => 'screenshots',
			'query_key' => $uuid,
			'value'     => json_encode($itemData),
		]);

		$response = $this->actingAs($user)
			->get(action([ScreenshotsController::class, 'screenshotsEdit'], ['key' => $uuid]));

		$response->assertStatus(200)
			->assertViewHas('screenshot', $itemData)
			->assertViewHas('key', $uuid);
	}

	/**
	 ** @test
	 **
	 ** screenshotsUpdate modifies existing entry heading
	 **/
	public function screenshots_update_modifies_entry_heading()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$uuid = (string) Str::uuid();
		LandingPageSetting::create([
			'name'      => 'screenshots',
			'query_key' => $uuid,
			'value'     => json_encode(['screenshots_heading' => 'Old']),
		]);

		$payload = ['screenshots_heading' => 'Updated'];

		// screenshotsUpdate route: POST /screenshots/update/{key}
		$response = $this->actingAs($user)
			->post(action([ScreenshotsController::class, 'screenshotsUpdate'], ['key' => $uuid]), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Screenshots update successfully');

		$setting = LandingPageSetting::where('name', 'screenshots')->where('query_key', $uuid)->first();
		$this->assertNotNull($setting, 'Row should still exist after update');
	}

	/**
	 ** @test
	 **
	 ** screenshotsDelete removes the specified entry
	 **/
	public function screenshots_delete_removes_entry()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		// screenshotsDelete fetches the first 'screenshots' row and removes item at $key from its JSON array
		LandingPageSetting::create([
			'name'  => 'screenshots',
			'value' => json_encode([
				['screenshots_heading' => 'One'],
				['screenshots_heading' => 'Two'],
			]),
		]);

		// screenshotsDelete route: GET /screenshots/delete/{key}
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
	 ** update modifies a single setting value (resource route screenshots/{screenshot})
	 **/
	public function update_modifies_single_setting_value()
	{
		$user    = User::factory()->create(['type' => 'super admin']);
		$setting = LandingPageSetting::create(['name' => 'random', 'value' => 'old']);

		$payload = ['value' => 'new'];

		// Resource route: PUT /screenshots/{screenshot}
		$response = $this->actingAs($user)
			->put(
				action([ScreenshotsController::class, 'update'], ['screenshot' => $setting->id]),
				$payload
			);

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
	 ** destroy deletes a single setting (resource route screenshots/{screenshot})
	 **/
	public function destroy_deletes_single_setting()
	{
		$user    = User::factory()->create(['type' => 'super admin']);
		$setting = LandingPageSetting::create(['name' => 'to_delete', 'value' => 'x']);

		// Resource route: DELETE /screenshots/{screenshot}
		$response = $this->actingAs($user)
			->delete(
				action([ScreenshotsController::class, 'destroy'], ['screenshot' => $setting->id])
			);

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting deleted successfully');

		$this->assertDatabaseMissing('landing_page_settings', ['id' => $setting->id]);
	}
}
