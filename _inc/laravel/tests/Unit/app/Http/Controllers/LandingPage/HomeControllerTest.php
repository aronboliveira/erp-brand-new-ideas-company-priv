<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\LandingPage\Http\Controllers\HomeController;
use Modules\LandingPage\Entities\LandingPageSetting;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class HomeControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Index should display home section view for user with manage landing page permission.
	 **/
	public function index_displays_homesection_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		// Seed some settings
		LandingPageSetting::create(['name' => 'home_status', 'value' => 'on']);
		LandingPageSetting::create(['name' => 'home_title', 'value' => 'Welcome']);

		$response = $this->actingAs($user)
			->get(action([HomeController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.homesection')
			->assertViewHas('settings', function ($settings) {
				return isset($settings['home_status']) && $settings['home_status'] === 'on'
					&& isset($settings['home_title']) && $settings['home_title'] === 'Welcome';
			});
	}

	/**
	 ** @test
	 **
	 ** Index should deny access when user lacks permission.
	 **/
	public function index_denies_unauthorized_user()
	{
		$user = User::factory()->create();
		// no permission granted

		$response = $this->actingAs($user)
			->get(action([HomeController::class, 'index']));

		$response->assertRedirect(route('landingpage.home.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Create should return the create view for authorized user.
	 **/
	public function create_returns_create_view()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([HomeController::class, 'create']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.homesection.create');
	}

	/**
	 ** @test
	 **
	 ** Store should upload banner and logos, save settings, and redirect back with success.
	 **/
	public function store_uploads_files_and_saves_settings()
	{
		Storage::fake('local');
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		// Create existing home_logo setting to test savedlogo behavior
		LandingPageSetting::create([
			'name'  => 'home_logo',
			'value' => 'keep1.png,drop.png'
		]);

		$banner = UploadedFile::fake()->image('banner.jpg');
		$logo1 = UploadedFile::fake()->image('logo1.png');
		$logo2 = UploadedFile::fake()->image('logo2.jpg');

		$data = [
			'home_banner'          => $banner,
			'savedlogo'            => 'keep1.png',
			'home_logo'            => [$logo1, $logo2],
			'home_status'          => 'off',
			'home_offer_text'      => 'Special Offer',
			'home_title'           => 'My Site',
			'home_heading'         => 'Hello',
			'home_description'     => 'Welcome to my site',
			'home_trusted_by'      => 'Clients',
			'home_live_demo_link'  => 'https://demo.example.com',
			'home_buy_now_link'    => 'https://buy.example.com',
		];

		$response = $this->actingAs($user)
			->post(action([HomeController::class, 'store']), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Settings updated successfully'));

		// Assert banner saved
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'home_banner',
			'value' => 'home_banner.jpg',
		]);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter->assertExists('uploads/landing_page_image/home_banner.jpg');

		// Assert home_logo contains new logos and kept one
		$logoSetting = LandingPageSetting::where('name', 'home_logo')->first();
		$this->assertStringContainsString('_logo1.png', $logoSetting->value);
		$this->assertStringContainsString('_logo2.jpg', $logoSetting->value);
		$this->assertStringContainsString('keep1.png', $logoSetting->value);

		// Assert other fields saved
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_status', 'value' => 'off']);
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_offer_text', 'value' => 'Special Offer']);
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_title', 'value' => 'My Site']);
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_heading', 'value' => 'Hello']);
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_description', 'value' => 'Welcome to my site']);
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_trusted_by', 'value' => 'Clients']);
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_live_demo_link', 'value' => 'https://demo.example.com']);
		$this->assertDatabaseHas('landing_page_settings', ['name' => 'home_buy_now_link', 'value' => 'https://buy.example.com']);
	}
}
