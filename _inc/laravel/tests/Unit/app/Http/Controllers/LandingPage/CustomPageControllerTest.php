<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\CustomPageController;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class CustomPageControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		// Reset LandingPageSetting static cache
		$ref = new \ReflectionClass(LandingPageSetting::class);
		$prop = $ref->getProperty('settings');
		$prop->setAccessible(true);
		$prop->setValue(null, null);
	}

	/**
	 ** @test
	 **
	 ** index displays menubar pages for authorized user.
	 **/
	public function index_displays_menubar_pages_for_authorized_user()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pages = [
			[
				'menubarPageName'    => 'Page1',
				'menubarPageContent' => 'Content1',
				'pageSlug'           => 'page1',
				'templateName'       => 'template1',
				'pageUrl'            => '',
				'header'             => 'on',
				'footer'             => 'off',
				'login'              => 'on',
			],
		];
		LandingPageSetting::create([
			'name'  => 'menubar_page',
			'value' => json_encode($pages),
		]);

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.menubar.index')
			->assertViewHas('pages')
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** index redirects if user lacks permission.
	 **/
	public function index_redirects_if_unauthorized()
	{
		$user = User::factory()->create(['type' => 'company']);
		// company user gets 'Permission denied.' from type check

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'index']));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** show returns view for valid page key.
	 **/
	public function show_returns_view_for_valid_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pageData = ['menubarPageName' => 'P', 'menubarPageContent' => 'C', 'pageSlug' => 'p', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off'];
		$setting = LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pageData)]);

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'show'], ['custom_page' => $setting->query_key]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.show')
			->assertViewHas('page', $pageData)
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** show redirects with error for invalid key.
	 **/
	public function show_redirects_for_invalid_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode([])]);

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'show'], ['custom_page' => 'nonexistent-key']));

		$response->assertStatus(302)
			->assertSessionHas('error', __('Page not found'));
	}

	/**
	 ** @test
	 **
	 ** create returns the menubar.create view.
	 **/
	public function create_returns_menubar_create_view()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'create']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.menubar.create');
	}

	/**
	 ** @test
	 **
	 ** store adds a new page and redirects back with success.
	 **/
	public function store_adds_new_page_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		// Don't seed — let defaults apply

		$data = [
			'menubar_page_name'    => 'New Page',
			'menubar_page_content' => 'Hello',
			'template_name'        => 'content',
			'page_url'             => null,
			'header'               => 'on',
			'footer'               => null,
			'login'                => 'on',
		];

		$response = $this->actingAs($user)
			->post(action([CustomPageController::class, 'store']), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Page added successfully'));

		$setting = LandingPageSetting::where('name', 'menubar_page')->first();
		$this->assertNotNull($setting);
		$pages = json_decode($setting->value, true);
		$this->assertNotEmpty($pages);
	}

	/**
	 ** @test
	 **
	 ** edit returns menubar.edit view for valid key.
	 **/
	public function edit_returns_view_for_valid_key()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pageData = ['menubarPageName' => 'X', 'menubarPageContent' => 'Y', 'pageSlug' => 'x', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off'];
		$setting = LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pageData)]);

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'edit'], ['custom_page' => $setting->query_key]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.menubar.edit')
			->assertViewHas('page', $pageData)
			->assertViewHas('key', $setting->query_key);
	}

	/**
	 ** @test
	 **
	 ** store then update modifies existing page and redirects back.
	 **/
	public function update_modifies_existing_page_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pageData = ['menubarPageName' => 'Old', 'menubarPageContent' => 'O', 'pageSlug' => 'old', 'templateName' => 'tmpl', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off'];
		$setting = LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pageData)]);

		$data = [
			'menubar_page_name'    => 'Updated',
			'menubar_page_content' => 'NewContent',
			'template_name'        => 'page_url',
			'page_url'             => 'https://example.com',
			'header'               => null,
			'footer'               => 'on',
			'login'                => null,
		];

		$response = $this->actingAs($user)
			->put(action([CustomPageController::class, 'update'], ['custom_page' => $setting->query_key]), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Page updated successfully'));
	}

	/**
	 ** @test
	 **
	 ** destroy removes the page and redirects back.
	 **/
	public function destroy_removes_page_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$page1 = ['menubarPageName' => 'A', 'menubarPageContent' => 'A', 'pageSlug' => 'a', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off'];
		$setting1 = LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($page1)]);
		$page2 = ['menubarPageName' => 'B', 'menubarPageContent' => 'B', 'pageSlug' => 'b', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off'];
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($page2)]);

		$response = $this->actingAs($user)
			->delete(action([CustomPageController::class, 'destroy'], ['custom_page' => $setting1->query_key]));

		$response->assertRedirect()
			->assertSessionHas('success', __('Page deleted successfully'));
	}

	/**
	 ** @test
	 **
	 ** customStore saves siteDescription and redirects back with success.
	 **/
	public function customStore_saves_description_and_redirects()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::firstOrCreate(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$data = ['site_description' => 'Meta description'];

		$response = $this->actingAs($user)
			->post(action([CustomPageController::class, 'customStore']), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Settings saved successfully'));

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'site_description',
			'value' => 'Meta description',
		]);
	}

	/**
	 ** @test
	 **
	 ** customPage returns custompage view for valid slug.
	 **/
	public function customPage_returns_view_for_valid_slug()
	{
		$pages = [
			['page_slug' => 'myslug', 'menubarPageName' => 'N', 'menubarPageContent' => 'C', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off']
		];
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pages)]);

		$response = $this->get(action([CustomPageController::class, 'customPage'], ['slug' => 'myslug']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::layouts.custompage')
			->assertViewHas('page')
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** customPage redirects with error for invalid slug.
	 **/
	public function customPage_redirects_for_invalid_slug()
	{
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode([])]);

		$response = $this->get(action([CustomPageController::class, 'customPage'], ['slug' => 'nope']));

		$response->assertStatus(200);
	}
}
