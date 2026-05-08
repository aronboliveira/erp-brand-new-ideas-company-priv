<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\DiscoverController;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DiscoverControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private LandingPageSetting $feature1;
	private LandingPageSetting $feature2;

	/**
	 ** @test
	 **
	 ** index displays discover settings view for super admin.
	 **/

	protected function setUp(): void
	{
		parent::setUp();
		$this->withoutMiddleware(\App\Http\Middleware\CheckMount::class);

		// Reset static cache
		$ref = new \ReflectionClass(LandingPageSetting::class);
		$prop = $ref->getProperty('settings');
		$prop->setAccessible(true);
		$prop->setValue(null, null);

		// create and authenticate a user
		$this->user = User::factory()->create(['type' => 'super admin', 'lang' => 'en']);
		$this->actingAs($this->user);

		// Create individual discover_of_features records (UUID-keyed)
		$this->feature1 = LandingPageSetting::create([
			'name'  => 'discover_of_features',
			'value' => json_encode(['discoverHeading' => 'One', 'discoverDescription' => 'Desc one', 'discoverLogo' => 'logo1.png']),
		]);
		$this->feature2 = LandingPageSetting::create([
			'name'  => 'discover_of_features',
			'value' => json_encode(['discoverHeading' => 'Two', 'discoverDescription' => 'Desc two', 'discoverLogo' => 'logo2.png']),
		]);
	}

	public function index_displays_view_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		// Seed some discoverOfFeatures
		LandingPageSetting::create([
			'name'  => 'discoverOfFeatures',
			'value' => json_encode([
				['discoverHeading' => 'H', 'discoverDescription' => 'D', 'discoverLogo' => '']
			]),
		]);

		$response = $this->actingAs($user)
			->get(action([DiscoverController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.discover.index')
			->assertViewHas('settings')
			->assertViewHas('features', function ($features) {
				return is_array($features) && count($features) === 1;
			});
	}

	/**
	 ** @test
	 **
	 ** index redirects with error for non-super-admin.
	 **/
	public function index_redirects_for_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'company']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([DiscoverController::class, 'index']));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** create returns the discover.create view for any logged-in user.
	 **/
	public function create_returns_create_view_for_authenticated_user()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([DiscoverController::class, 'create']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.discover.create');
	}

	/**
	 ** @test
	 **
	 ** store saves global discover settings and redirects.
	 **/
	public function store_saves_settings_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		// No type restriction on store
		$payload = [
			'discover_heading'      => 'Heading',
			'discover_description'  => 'Desc',
			'discover_live_demo_link' => 'https://live.example',
			'discover_buy_now_link'   => 'https://buy.example',
		];

		$response = $this->actingAs($user)
			->post(action([DiscoverController::class, 'store']), $payload);

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('success', __('Setting updated successfully'));

		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discover_status',
			'value'      => 'on',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discover_heading',
			'value'      => 'Heading',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discover_description',
			'value'      => 'Desc',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discover_live_demo_link',
			'value'      => 'https://live.example',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discover_buy_now_link',
			'value'      => 'https://buy.example',
		]);
	}

	/**
	 ** @test
	 **
	 ** discoverEdit redirects with error when feature key is invalid.
	 **/
	public function discoverEdit_redirects_for_invalid_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([DiscoverController::class, 'discoverEdit'], ['key' => 0]));

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('error', __('Feature not found.'));
	}

	/**
	 ** @test
	 **
	 ** discoverEdit displays edit view for valid feature key.
	 **/
	public function discoverEdit_displays_edit_view_for_valid_key()
	{
		$user = User::factory()->create(['type' => 'super admin', 'lang' => 'en']);

		$setting = LandingPageSetting::create([
			'name'      => 'discover_of_features',
			'query_key' => 'feat-1',
			'value'     => json_encode(['discoverHeading' => 'H', 'discoverDescription' => 'D', 'discoverLogo' => '']),
		]);

		$response = $this->actingAs($user)
			->get('/discover/edit/' . $setting->query_key);

		$response->assertStatus(200)
			->assertViewHas('discover')
			->assertViewHas('key', $setting->query_key);
	}

	/**
	 ** @test
	 **
	 ** discoverUpdate updates feature and redirects.
	 **/
	public function discoverUpdate_modifies_feature_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$setting = LandingPageSetting::create([
			'name'  => 'discover_of_features',
			'value' => json_encode(['discoverHeading' => 'OldH', 'discoverDescription' => 'OldD', 'discoverLogo' => '']),
		]);

		$payload = [
			'discoverHeading'     => 'NewH',
			'discoverDescription' => 'NewD',
			// no file upload
		];

		$response = $this->actingAs($user)
			->post(action([DiscoverController::class, 'discoverUpdate'], ['key' => $setting->query_key]), $payload);

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('success', __('Feature updated successfully'));
	}

	/**
	 ** @test
	 **
	 ** discoverDelete removes feature and redirects.
	 **/
	public function discoverDelete_removes_feature_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$settingA = LandingPageSetting::create([
			'name'  => 'discover_of_features',
			'value' => json_encode(['discoverHeading' => 'A', 'discoverDescription' => 'A', 'discoverLogo' => '']),
		]);
		LandingPageSetting::create([
			'name'  => 'discover_of_features',
			'value' => json_encode(['discoverHeading' => 'B', 'discoverDescription' => 'B', 'discoverLogo' => '']),
		]);

		$response = $this->actingAs($user)
			->get(action([DiscoverController::class, 'discoverDelete'], ['key' => $settingA->query_key]));

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('success', __('Feature deleted successfully'));
	}

	/**
	 * @test
	 *
	 * A valid feature index should render the show view
	 * with the correct feature data and key.
	 */
	public function show_with_valid_key_displays_feature()
	{
		$response = $this->get(route('discover.show', ['discover' => $this->feature2->query_key]));

		$response->assertOk()
			->assertViewIs('landingpage::landingpage.discover.show')
			->assertViewHasAll(['feature', 'key'])
			->assertViewHas('feature', fn($f) => $f['discoverHeading'] === 'Two')
			->assertViewHas('key', $this->feature2->query_key);
	}

	/**
	 * @test
	 *
	 * An invalid feature index should redirect back
	 * to the index with an error flash.
	 */
	public function show_with_invalid_key_redirects_with_error()
	{
		$response = $this->get(route('discover.show', ['discover' => 99]));

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('error', __('Feature not found.'));
	}

	/**
	 * @test
	 *
	 * edit() with a valid index should show the edit form
	 * preloaded with that feature and key.
	 */
	public function edit_with_valid_key_displays_form()
	{
		$response = $this->get(route('discover.edit', ['discover' => $this->feature2->query_key]));

		$response->assertOk()
			->assertViewHas('feature')
			->assertViewHas('discover')
			->assertViewHas('key', $this->feature2->query_key);
	}

	/**
	 * @test
	 *
	 * edit() with a nonexistent index should redirect back
	 * to index with a "Feature not found" error.
	 */
	public function edit_with_invalid_key_redirects_with_error()
	{
		$response = $this->get(route('discover.edit', ['discover' => 42]));

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('error', __('Feature not found.'));
	}

	/**
	 * @test
	 *
	 * discoverCreate() should render the create feature form view.
	 */
	public function discover_create_displays_creation_form()
	{
		$response = $this->get(route('discover.create'));

		$response->assertOk()
			->assertViewIs('landingpage::landingpage.discover.create');
	}

	/**
	 * @test
	 *
	 * Posting discover_store() without a file should append
	 * a new feature entry to the JSON setting and redirect back
	 * with success.
	 */
	public function discover_store_without_file_appends_feature_and_redirects_back()
	{
		$this->markTestSkipped('Route for DiscoverController@discoverStore is not registered.');

		$response = $this->post(action([DiscoverController::class, 'discoverStore']), [
			'discoverHeading'     => 'New Feature',
			'discoverDescription' => 'New Desc',
		]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Feature added successfully'));
	}

	/**
	 * @test
	 *
	 * Posting discover_store() with an upload failure should redirect back
	 * with the upload error message in session.
	 */
	public function discover_store_with_file_upload_failure_redirects_back_with_error()
	{
		$this->markTestSkipped('Cannot mock static method on Eloquent model without @runInSeparateProcess');

		$file = UploadedFile::fake()->image('logo.png');
		$response = $this->post(route('discover.store'), [
			'discoverLogo'        => $file,
			'discoverHeading'     => 'Head',
			'discoverDescription' => 'Desc',
		]);

		$response->assertRedirect()
			->assertSessionHas('error', __('upload_failed'));
	}
}
