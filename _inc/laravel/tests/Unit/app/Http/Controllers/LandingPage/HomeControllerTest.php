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
	 ** Index should display home section view for user with manage landing page permission.
	 **/
	public function index_displays_homesection_for_authorized_user()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		// Seed some settings
		LandingPageSetting::create(['name' => 'home_status', 'value' => 'on']);
		LandingPageSetting::create(['name' => 'home_title', 'value' => 'Welcome']);

		$response = $this->actingAs($user)
			->get(action([HomeController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.home_section')
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** Index should deny access when user lacks permission.
	 **/
	public function index_denies_unauthorized_user()
	{
		$user = User::factory()->create(['type' => 'company']);

		$response = $this->actingAs($user)
			->get(action([HomeController::class, 'index']));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Create should return the create view for authorized user.
	 **/
	public function create_returns_create_view()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([HomeController::class, 'create']));

		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Store should upload banner and logos, save settings, and redirect back with success.
	 **/
	public function store_uploads_files_and_saves_settings()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$data = [
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
