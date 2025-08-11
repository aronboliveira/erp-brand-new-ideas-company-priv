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

	/**
	 ** @test
	 **
	 ** index displays menubar pages for authorized user.
	 **/
	public function index_displays_menubar_pages_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
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
			->assertViewHas('pages', function ($v) use ($pages) {
				return is_array($v) && $v[0]['pageSlug'] === 'page1';
			})
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** index redirects if user lacks permission.
	 **/
	public function index_redirects_if_unauthorized()
	{
		$user = User::factory()->create();
		// no permission

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'index']));

		$response->assertRedirect(route('landingpage.menubar.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** show returns view for valid page key.
	 **/
	public function show_returns_view_for_valid_key()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pages = [
			['menubarPageName' => 'P', 'menubarPageContent' => 'C', 'pageSlug' => 'p', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off']
		];
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pages)]);

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'show'], ['key' => 0]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.show')
			->assertViewHas('page', $pages[0])
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** show redirects with error for invalid key.
	 **/
	public function show_redirects_for_invalid_key()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode([])]);

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'show'], ['key' => 5]));

		$response->assertRedirect(route('landingpage.menubar.index'))
			->assertSessionHas('error', __('Page not found'));
	}

	/**
	 ** @test
	 **
	 ** create returns the menubar.create view.
	 **/
	public function create_returns_menubar_create_view()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
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
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		// seed empty pages
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode([])]);

		$data = [
			'menubar_page_name'    => 'New Page',
			'menubar_page_contant' => 'Hello',
			'template_name'        => 'content',
			'page_url'             => '',
			'header'               => 'on',
			'footer'               => null,
			'login'                => 'on',
		];

		$response = $this->actingAs($user)
			->post(action([CustomPageController::class, 'store']), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Page added successfully'));

		$setting = LandingPageSetting::where('name', 'menubar_page')->first();
		$pages = json_decode($setting->value, true);
		$this->assertCount(1, $pages);
		$this->assertEquals('New Page', $pages[0]['menubarPageName']);
		$this->assertEquals('Hello', $pages[0]['menubarPageContent']);
		$this->assertEquals('new_page', $pages[0]['pageSlug']);
		$this->assertEquals('content', $pages[0]['templateName']);
		$this->assertEquals('on', $pages[0]['header']);
		$this->assertEquals('off', $pages[0]['footer']);
		$this->assertEquals('on', $pages[0]['login']);
	}

	/**
	 ** @test
	 **
	 ** edit returns menubar.edit view for valid key.
	 **/
	public function edit_returns_view_for_valid_key()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pages = [
			['menubarPageName' => 'X', 'menubarPageContent' => 'Y', 'pageSlug' => 'x', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off']
		];
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pages)]);

		$response = $this->actingAs($user)
			->get(action([CustomPageController::class, 'edit'], ['key' => 0]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.menubar.edit')
			->assertViewHas('page', $pages[0])
			->assertViewHas('key', 0);
	}

	/**
	 ** @test
	 **
	 ** store then update modifies existing page and redirects back.
	 **/
	public function update_modifies_existing_page_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pages = [
			['menubarPageName' => 'Old', 'menubarPageContent' => 'O', 'pageSlug' => 'old', 'templateName' => 'tmpl', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off']
		];
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pages)]);

		$data = [
			'menubar_page_name'    => 'Updated',
			'menubar_page_contant' => 'NewContent',
			'template_name'        => 'page_url',
			'page_url'             => 'https://example.com',
			'header'               => null,
			'footer'               => 'on',
			'login'                => null,
		];

		$response = $this->actingAs($user)
			->put(action([CustomPageController::class, 'update'], ['key' => 0]), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Page updated successfully'));

		$setting = LandingPageSetting::where('name', 'menubar_page')->first();
		$updated = json_decode($setting->value, true)[0];
		$this->assertEquals('Updated', $updated['menubarPageName']);
		$this->assertEquals('', $updated['menubarPageContent']);
		$this->assertEquals('updated', $updated['pageSlug']);
		$this->assertEquals('page_url', $updated['templateName']);
		$this->assertEquals('https://example.com', $updated['pageUrl']);
		$this->assertEquals('off', $updated['header']);
		$this->assertEquals('on', $updated['footer']);
		$this->assertEquals('off', $updated['login']);
	}

	/**
	 ** @test
	 **
	 ** destroy removes the page and redirects back.
	 **/
	public function destroy_removes_page_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
		$user?->givePermissionTo('manage landing page');

		$pages = [
			['menubarPageName' => 'A', 'menubarPageContent' => 'A', 'pageSlug' => 'a', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off'],
			['menubarPageName' => 'B', 'menubarPageContent' => 'B', 'pageSlug' => 'b', 'templateName' => 't', 'pageUrl' => '', 'header' => 'off', 'footer' => 'off', 'login' => 'off'],
		];
		LandingPageSetting::create(['name' => 'menubar_page', 'value' => json_encode($pages)]);

		$response = $this->actingAs($user)
			->delete(action([CustomPageController::class, 'destroy'], ['key' => 0]));

		$response->assertRedirect()
			->assertSessionHas('success', __('Page deleted successfully'));

		$remaining = json_decode(LandingPageSetting::where('name', 'menubar_page')->first()->value, true);
		$this->assertCount(1, $remaining);
		$this->assertEquals('b', $remaining[0]['pageSlug']);
	}

	/**
	 ** @test
	 **
	 ** customStore saves siteDescription and redirects back with success.
	 **/
	public function customStore_saves_description_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage landing page']);
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
			->assertViewHas('page', $pages[0])
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

		$response->assertRedirect()
			->assertSessionHas('error', __('Page not found'));
	}
}
