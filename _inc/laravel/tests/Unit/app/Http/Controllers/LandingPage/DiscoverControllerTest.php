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

	/**
	 ** @test
	 **
	 ** index displays discover settings view for super admin.
	 **/

	protected function setUp(): void
	{
		parent::setUp();

		// create and authenticate a user
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		// make sure the 'discoverOfFeatures' setting exists
		LandingPageSetting::updateOrCreate(
			['name'       => 'discoverOfFeatures', 'created_by' => $this->user->id],
			[
				'value'      => json_encode([
					['discoverHeading' => 'One', 'discoverDescription' => 'Desc one', 'discoverLogo' => 'logo1.png'],
					['discoverHeading' => 'Two', 'discoverDescription' => 'Desc two', 'discoverLogo' => 'logo2.png'],
				]),
				'created_by' => $this->user->id
			]
		);
	}

	public function index_displays_view_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
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
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([DiscoverController::class, 'index']));

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** create returns the discover.create view for any logged-in user.
	 **/
	public function create_returns_create_view_for_authenticated_user()
	{
		$user = User::factory()->create();

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
		$user = User::factory()->create();
		// No type restriction on store
		$payload = [
			'discoverHeading'      => 'Heading',
			'discoverDescription'  => 'Desc',
			'discoverLiveDemoLink' => 'https://live.example',
			'discoverBuyNowLink'   => 'https://buy.example',
		];

		$response = $this->actingAs($user)
			->post(action([DiscoverController::class, 'store']), $payload);

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('success', __('Setting updated successfully'));

		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discoverStatus',
			'value'      => 'on',
			'created_by' => $user?->id,
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discoverHeading',
			'value'      => 'Heading',
			'created_by' => $user?->id,
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discoverDescription',
			'value'      => 'Desc',
			'created_by' => $user?->id,
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discoverLiveDemoLink',
			'value'      => 'https://live.example',
			'created_by' => $user?->id,
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'       => 'discoverBuyNowLink',
			'value'      => 'https://buy.example',
			'created_by' => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** discoverEdit redirects with error when feature key is invalid.
	 **/
	public function discoverEdit_redirects_for_invalid_key()
	{
		$user = User::factory()->create();

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
		$user = User::factory()->create();

		$features = [
			['discoverHeading' => 'H', 'discoverDescription' => 'D', 'discoverLogo' => 'logo.png']
		];
		LandingPageSetting::create([
			'name'  => 'discoverOfFeatures',
			'value' => json_encode($features),
		]);

		$response = $this->actingAs($user)
			->get(action([DiscoverController::class, 'discoverEdit'], ['key' => 0]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.discover.edit')
			->assertViewHas('feature', $features[0])
			->assertViewHas('key', 0);
	}

	/**
	 ** @test
	 **
	 ** discoverUpdate updates feature and redirects.
	 **/
	public function discoverUpdate_modifies_feature_and_redirects()
	{
		$user = User::factory()->create();

		$features = [
			['discoverHeading' => 'OldH', 'discoverDescription' => 'OldD', 'discoverLogo' => '']
		];
		LandingPageSetting::create([
			'name'  => 'discoverOfFeatures',
			'value' => json_encode($features),
		]);

		$payload = [
			'discoverHeading'     => 'NewH',
			'discoverDescription' => 'NewD',
			// no file upload
		];

		$response = $this->actingAs($user)
			->post(action([DiscoverController::class, 'discoverUpdate'], ['key' => 0]), $payload);

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('success', __('Feature updated successfully'));

		$setting = LandingPageSetting::where('name', 'discoverOfFeatures')->first();
		$updated = json_decode($setting->value, true)[0];
		$this->assertEquals('NewH', $updated['discoverHeading']);
		$this->assertEquals('NewD', $updated['discoverDescription']);
	}

	/**
	 ** @test
	 **
	 ** discoverDelete removes feature and redirects.
	 **/
	public function discoverDelete_removes_feature_and_redirects()
	{
		$user = User::factory()->create();

		$features = [
			['discoverHeading' => 'A', 'discoverDescription' => 'A', 'discoverLogo' => ''],
			['discoverHeading' => 'B', 'discoverDescription' => 'B', 'discoverLogo' => ''],
		];
		LandingPageSetting::create([
			'name'  => 'discoverOfFeatures',
			'value' => json_encode($features),
		]);

		$response = $this->actingAs($user)
			->delete(action([DiscoverController::class, 'discoverDelete'], ['key' => 0]));

		$response->assertRedirect(route('discover.index'))
			->assertSessionHas('success', __('Feature deleted successfully'));

		$remaining = json_decode(LandingPageSetting::where('name', 'discoverOfFeatures')->first()->value, true);
		$this->assertCount(1, $remaining);
		$this->assertEquals('B', $remaining[0]['discoverHeading']);
	}

	/**
	 * @test
	 *
	 * A valid feature index should render the show view
	 * with the correct feature data and key.
	 */
	public function show_with_valid_key_displays_feature()
	{
		$response = $this->get(route('discover.show', ['id' => 1]));

		$response->assertOk()
			->assertViewIs('landingpage::landingpage.discover.show')
			->assertViewHasAll(['feature', 'key'])
			->assertViewHas('feature', fn ($f) => $f['discoverHeading'] === 'Two')
			->assertViewHas('key', 1);
	}

	/**
	 * @test
	 *
	 * An invalid feature index should redirect back
	 * to the index with an error flash.
	 */
	public function show_with_invalid_key_redirects_with_error()
	{
		$response = $this->get(route('discover.show', ['id' => 99]));

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
		$response = $this->get(route('discover.edit', ['id' => 0]));

		$response->assertOk()
			->assertViewIs('landingpage::landingpage.discover.edit')
			->assertViewHasAll(['feature', 'key'])
			->assertViewHas('key', 0);
	}

	/**
	 * @test
	 *
	 * edit() with a nonexistent index should redirect back
	 * to index with a "Feature not found" error.
	 */
	public function edit_with_invalid_key_redirects_with_error()
	{
		$response = $this->get(route('discover.edit', ['id' => 42]));

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
		$initial = json_decode(LandingPageSetting::settings()['discoverOfFeatures'], true);
		$this->assertCount(2, $initial);

		$response = $this->post(route('discover.store'), [
			'discoverHeading'     => 'New Feature',
			'discoverDescription' => 'New Desc',
		]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Feature added successfully'));

		$updated = json_decode(LandingPageSetting::settings()['discoverOfFeatures'], true);
		$this->assertCount(3, $updated);
		$this->assertEquals('New Feature',   $updated[2]['discoverHeading']);
		$this->assertEquals('New Desc',      $updated[2]['discoverDescription']);
		$this->assertArrayNotHasKey('discoverLogo', $updated[2]);
	}

	/**
	 * @test
	 *
	 * Posting discover_store() with an upload failure should redirect back
	 * with the upload error message in session.
	 */
	public function discover_store_with_file_upload_failure_redirects_back_with_error()
	{
		Storage::fake('local');

		// Simulate upload failure via the static uploadFile helper
		LandingPageSetting::shouldReceive('uploadFile')
			->once()
			->andReturn(['flag' => 0, 'msg' => 'upload_failed']);

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
