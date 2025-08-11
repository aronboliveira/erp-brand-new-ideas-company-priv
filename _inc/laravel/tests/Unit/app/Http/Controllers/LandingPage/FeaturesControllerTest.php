<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\FeaturesController;
use Spatie\Permission\Models\Permission;

class FeaturesControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Index should display features settings for super admin.
	 **/
	public function index_displays_settings_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		LandingPageSetting::create(['name' => 'feature_of_features', 'value' => json_encode([['feature_heading' => 'H', 'feature_description' => 'D']])]);
		LandingPageSetting::create(['name' => 'other_features',       'value' => json_encode([['other_features_heading' => 'OH', 'other_featured_description' => 'OD']])]);

		$response = $this->actingAs($user)->get(action([FeaturesController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.features.index')
			->assertViewHasAll(['settings', 'feature_of_features', 'other_features']);
	}

	/**
	 ** @test
	 **
	 ** Index should deny non-super-admin users.
	 **/
	public function index_denies_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'company']);
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)->get(action([FeaturesController::class, 'index']));

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Create should return the create view.
	 **/
	public function create_returns_create_view()
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)->get(action([FeaturesController::class, 'create']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.features.create');
	}

	/**
	 ** @test
	 **
	 ** Store should save general feature settings and redirect back.
	 **/
	public function store_saves_general_settings_and_redirects()
	{
		$user = User::factory()->create();
		$data = [
			'feature_title'        => 'Title',
			'feature_heading'      => 'Heading',
			'feature_description'  => 'Description',
			'feature_buy_now_link' => 'https://example.com',
		];

		$response = $this->actingAs($user)->post(action([FeaturesController::class, 'store']), $data);

		$response->assertRedirect()
			->assertSessionHas('success', 'Settings updated');

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'feature_status',
			'value' => 'on',
		]);
		foreach (['feature_title', 'feature_heading', 'feature_description', 'feature_buy_now_link'] as $field) {
			$this->assertDatabaseHas('landing_page_settings', [
				'name'  => $field,
				'value' => $data[substr($field, 8)],
			]);
		}
	}

	/**
	 ** @test
	 **
	 ** featureStore should add a new feature and redirect back.
	 **/
	public function featureStore_adds_new_feature_and_redirects()
	{
		$user = User::factory()->create();
		LandingPageSetting::create(['name' => 'feature_of_features', 'value' => json_encode([])]);
		Storage::fake('local');

		$file = UploadedFile::fake()->image('logo.png');
		$data = [
			'feature_heading'     => 'New H',
			'feature_description' => 'New D',
			'feature_logo'        => $file,
		];

		$response = $this->actingAs($user)
			->post(action([FeaturesController::class, 'featureStore']), $data);

		$response->assertRedirect()
			->assertSessionHas('success', 'Feature added');

		$setting = LandingPageSetting::where('name', 'feature_of_features')->first();
		$list = json_decode($setting->value, true);
		$this->assertCount(1, $list);
		$this->assertEquals('New H', $list[0]['feature_heading']);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter->assertExists('uploads/landing_page_image/' . $list[0]['feature_logo']);
	}

	/**
	 ** @test
	 **
	 ** featureUpdate should modify existing feature and redirect back.
	 **/
	public function featureUpdate_modifies_feature_and_redirects()
	{
		$user = User::factory()->create();
		$initial = [['feature_heading' => 'Old', 'feature_description' => 'OldD']];
		LandingPageSetting::create(['name' => 'feature_of_features', 'value' => json_encode($initial)]);

		$data = ['feature_heading' => 'Upd', 'feature_description' => 'UpdD'];
		$response = $this->actingAs($user)
			->put(action([FeaturesController::class, 'featureUpdate'], ['key' => 0]), $data);

		$response->assertRedirect()
			->assertSessionHas('success', 'Feature updated');

		$list = json_decode(LandingPageSetting::where('name', 'feature_of_features')->first()->value, true);
		$this->assertEquals('Upd', $list[0]['feature_heading']);
	}

	/**
	 ** @test
	 **
	 ** featureDelete should remove feature and redirect back.
	 **/
	public function featureDelete_removes_feature_and_redirects()
	{
		$user = User::factory()->create();
		$initial = [['feature_heading' => 'A'], ['feature_heading' => 'B']];
		LandingPageSetting::create(['name' => 'feature_of_features', 'value' => json_encode($initial)]);

		$response = $this->actingAs($user)
			->delete(action([FeaturesController::class, 'featureDelete'], ['key' => 0]));

		$response->assertRedirect()
			->assertSessionHas('success', 'Feature deleted');

		$remaining = json_decode(LandingPageSetting::where('name', 'feature_of_features')->first()->value, true);
		$this->assertCount(1, $remaining);
		$this->assertEquals('B', $remaining[0]['feature_heading']);
	}

	/**
	 ** @test
	 **
	 ** featuresStore should add an other feature and redirect back.
	 **/
	public function featuresStore_adds_other_feature_and_redirects()
	{
		$user = User::factory()->create();
		LandingPageSetting::create(['name' => 'other_features', 'value' => json_encode([])]);
		Storage::fake('local');

		$file = UploadedFile::fake()->image('other.png');
		$data = [
			'other_features_heading'     => 'OH',
			'other_featured_description' => 'OD',
			'other_feature_buy_now_link' => 'https://buy.now',
			'other_features_image'       => $file,
		];

		$response = $this->actingAs($user)
			->post(action([FeaturesController::class, 'featuresStore']), $data);

		$response->assertRedirect()
			->assertSessionHas('success', 'Other feature added');

		$list = json_decode(LandingPageSetting::where('name', 'other_features')->first()->value, true);
		$this->assertCount(1, $list);
		$this->assertEquals('OH', $list[0]['other_features_heading']);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter->assertExists('uploads/landing_page_image/' . $list[0]['other_features_image']);
	}

	/**
	 ** @test
	 **
	 ** featuresUpdate should modify an other feature and redirect back.
	 **/
	public function featuresUpdate_modifies_other_feature_and_redirects()
	{
		$user = User::factory()->create();
		$initial = [['other_features_heading' => 'X', 'other_featured_description' => 'Y']];
		LandingPageSetting::create(['name' => 'other_features', 'value' => json_encode($initial)]);

		$data = ['other_features_heading' => 'NX', 'other_featured_description' => 'NY'];
		$response = $this->actingAs($user)
			->put(action([FeaturesController::class, 'featuresUpdate'], ['key' => 0]), $data);

		$response->assertRedirect()
			->assertSessionHas('success', 'Other feature updated');

		$list = json_decode(LandingPageSetting::where('name', 'other_features')->first()->value, true);
		$this->assertEquals('NX', $list[0]['other_features_heading']);
	}

	/**
	 ** @test
	 **
	 ** featuresDelete should remove an other feature and redirect back.
	 **/
	public function featuresDelete_removes_other_feature_and_redirects()
	{
		$user = User::factory()->create();
		$initial = [['other_features_heading' => 'A'], ['other_features_heading' => 'B']];
		LandingPageSetting::create(['name' => 'other_features', 'value' => json_encode($initial)]);

		$response = $this->actingAs($user)
			->delete(action([FeaturesController::class, 'featuresDelete'], ['key' => 0]));

		$response->assertRedirect()
			->assertSessionHas('success', 'Other feature deleted');

		$remaining = json_decode(LandingPageSetting::where('name', 'other_features')->first()->value, true);
		$this->assertCount(1, $remaining);
		$this->assertEquals('B', $remaining[0]['other_features_heading']);
	}

	/**
	 ** @test
	 **
	 ** update should change a setting by ID and redirect back.
	 **/
	public function update_changes_setting_and_redirects()
	{
		$user = User::factory()->create();
		$setting = LandingPageSetting::create(['name' => 'foo', 'value' => 'bar']);
		$data = ['value' => 'baz'];

		$response = $this->actingAs($user)
			->put(action([FeaturesController::class, 'update'], ['id' => $setting->id]), $data);

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting updated');

		$this->assertDatabaseHas('landing_page_settings', ['id' => $setting->id, 'value' => 'baz']);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete a setting by ID and redirect back.
	 **/
	public function destroy_deletes_setting_and_redirects()
	{
		$user = User::factory()->create();
		$setting1 = LandingPageSetting::create(['name' => 'a', 'value' => '1']);
		$setting2 = LandingPageSetting::create(['name' => 'b', 'value' => '2']);

		$response = $this->actingAs($user)
			->delete(action([FeaturesController::class, 'destroy'], ['id' => $setting1->id]));

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting deleted');

		$this->assertDatabaseMissing('landing_page_settings', ['id' => $setting1->id]);
		$this->assertDatabaseHas('landing_page_settings', ['id' => $setting2->id]);
	}

	/**
	 ** @test
	 **
	 ** index displays features index view for super admin.
	 **/
	public function index_displays_features_index_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		// seed some settings
		LandingPageSetting::create([
			'name'  => 'feature_of_features',
			'value' => json_encode([['feature_heading' => 'H', 'feature_description' => 'D']]),
		]);
		LandingPageSetting::create([
			'name'  => 'other_features',
			'value' => json_encode([['other_features_heading' => 'OH', 'other_featured_description' => 'OD']]),
		]);

		$response = $this->actingAs($user)
			->get(action([FeaturesController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.features.index')
			->assertViewHasAll(['settings', 'feature_of_features', 'other_features']);
	}

	/**
	 ** @test
	 **
	 ** index redirects for non-super-admin.
	 **/
	public function index_redirects_for_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'company']);

		$response = $this->actingAs($user)
			->get(action([FeaturesController::class, 'index']));

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store saves global feature settings and redirects back.
	 **/
	public function store_saves_global_feature_settings()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$payload = [
			'feature_title'         => 'T1',
			'feature_heading'       => 'H1',
			'feature_description'   => 'D1',
			'feature_buy_now_link'  => 'https://example.com',
		];

		$response = $this->actingAs($user)
			->post(action([FeaturesController::class, 'store']), $payload);

		$response->assertStatus(302)
			->assertSessionHas('success', 'Settings updated');

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'feature_title',
			'value' => 'T1',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'feature_heading',
			'value' => 'H1',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'feature_description',
			'value' => 'D1',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'feature_buy_now_link',
			'value' => 'https://example.com',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'feature_status',
			'value' => 'on',
		]);
	}

	/**
	 ** @test
	 **
	 ** show displays a single setting for valid ID.
	 **/
	public function show_displays_single_setting_for_valid_id()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$setting = LandingPageSetting::create([
			'name'  => 'test_setting',
			'value' => 'V1',
		]);

		$response = $this->actingAs($user)
			->get(action([FeaturesController::class, 'show'], ['id' => $setting->id]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.features.show')
			->assertViewHas('setting', function ($s) use ($setting) {
				return $s->id === $setting->id && $s->value === 'V1';
			});
	}

	/**
	 ** @test
	 **
	 ** show redirects for invalid ID.
	 **/
	public function show_redirects_for_invalid_id()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([FeaturesController::class, 'show'], ['id' => 999]));

		$response->assertRedirect()
			->assertSessionHas('error', __('Setting not found'));
	}

	/**
	 ** @test
	 **
	 ** edit displays edit form for valid ID.
	 **/
	public function edit_displays_edit_form_for_valid_id()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$setting = LandingPageSetting::create([
			'name'  => 'test_edit',
			'value' => 'V2',
		]);

		$response = $this->actingAs($user)
			->get(action([FeaturesController::class, 'edit'], ['id' => $setting->id]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.features.edit')
			->assertViewHas('setting', function ($s) use ($setting) {
				return $s->id === $setting->id;
			});
	}

	/**
	 ** @test
	 **
	 ** edit redirects for invalid ID.
	 **/
	public function edit_redirects_for_invalid_id()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([FeaturesController::class, 'edit'], ['id' => 1234]));

		$response->assertRedirect()
			->assertSessionHas('error', __('Setting not found'));
	}
}
