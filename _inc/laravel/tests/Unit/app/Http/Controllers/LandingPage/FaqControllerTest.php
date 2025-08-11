<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\FaqController;
use Spatie\Permission\Models\Permission;

class FaqControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index displays FAQ settings for super admin with permission.
	 **/
	public function index_displays_faq_settings_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		// seed some FAQs
		LandingPageSetting::create([
			'name'  => 'faqs',
			'value' => json_encode([
				['faqQuestions' => 'Q1', 'faqAnswer' => 'A1'],
			]),
		]);

		$response = $this->actingAs($user)
			->get(action([FaqController::class, 'index']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.faq.index')
			->assertViewHas('settings')
			->assertViewHas('faqs', function ($faqs) {
				return is_array($faqs) && count($faqs) === 1;
			});
	}

	/**
	 ** @test
	 **
	 ** index redirects with error when missing permission.
	 **/
	public function index_redirects_if_missing_permission()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		// no permission granted

		$response = $this->actingAs($user)
			->get(action([FaqController::class, 'index']));

		$response->assertRedirect(route('landingpage.faq.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** index redirects with error for non-super-admin even with permission.
	 **/
	public function index_redirects_for_non_super_admin()
	{
		$user = User::factory()->create(['type' => 'company']);
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		$response = $this->actingAs($user)
			->get(action([FaqController::class, 'index']));

		$response->assertRedirect(route('landingpage.faq.index'))
			->assertSessionHas('error', 'Only super admin can view FAQs');
	}

	/**
	 ** @test
	 **
	 ** create returns settings view when authorized.
	 **/
	public function create_returns_settings_view()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		$response = $this->actingAs($user)
			->get(action([FaqController::class, 'create']));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.faq.settings');
	}

	/**
	 ** @test
	 **
	 ** store saves FAQ settings and redirects.
	 **/
	public function store_saves_faq_settings_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		$payload = [
			'faqStatus'      => 'on',
			'faqTitle'       => 'My FAQ',
			'faqHeading'     => 'FAQ Heading',
			'faqDescription' => 'Some description',
		];

		$response = $this->actingAs($user)
			->post(action([FaqController::class, 'store']), $payload);

		$response->assertRedirect(route('landingpage.faq.index'))
			->assertSessionHas('success', __('FAQ settings updated successfully'));

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'faq_status',
			'value' => 'on',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'faq_title',
			'value' => 'My FAQ',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'faq_heading',
			'value' => 'FAQ Heading',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'faq_description',
			'value' => 'Some description',
		]);
	}

	/**
	 ** @test
	 **
	 ** show displays an FAQ entry for valid key.
	 **/
	public function show_displays_faq_for_valid_key()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		$faqs = [
			['faqQuestions' => 'Q', 'faqAnswer' => 'A']
		];
		LandingPageSetting::create([
			'name'  => 'faqs',
			'value' => json_encode($faqs),
		]);

		$response = $this->actingAs($user)
			->get(action([FaqController::class, 'show'], ['key' => 0]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.faq.show')
			->assertViewHas('faq', $faqs[0])
			->assertViewHas('key', 0);
	}

	/**
	 ** @test
	 **
	 ** show redirects with error for invalid key.
	 **/
	public function show_redirects_for_invalid_key()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		LandingPageSetting::create([
			'name'  => 'faqs',
			'value' => json_encode([]),
		]);

		$response = $this->actingAs($user)
			->get(action([FaqController::class, 'show'], ['key' => 5]));

		$response->assertRedirect(route('landingpage.faq.index'))
			->assertSessionHas('error', __('FAQ not found'));
	}

	/**
	 ** @test
	 **
	 ** edit displays edit form for valid key.
	 **/
	public function edit_displays_form_for_valid_key()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		$faqs = [
			['faqQuestions' => 'Q2', 'faqAnswer' => 'A2']
		];
		LandingPageSetting::create([
			'name'  => 'faqs',
			'value' => json_encode($faqs),
		]);

		$response = $this->actingAs($user)
			->get(action([FaqController::class, 'edit'], ['key' => 0]));

		$response->assertStatus(200)
			->assertViewIs('landingpage::landingpage.faq.edit')
			->assertViewHas('faq', $faqs[0])
			->assertViewHas('key', 0);
	}

	/**
	 ** @test
	 **
	 ** update modifies FAQ and redirects.
	 **/
	public function update_modifies_faq_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		$faqs = [
			['faqQuestions' => 'OldQ', 'faqAnswer' => 'OldA']
		];
		LandingPageSetting::create([
			'name'  => 'faqs',
			'value' => json_encode($faqs),
		]);

		$payload = [
			'faqQuestions' => 'NewQ',
			'faqAnswer'    => 'NewA',
		];

		$response = $this->actingAs($user)
			->post(action([FaqController::class, 'update'], ['key' => 0]), $payload);

		$response->assertRedirect(route('landingpage.faq.index'))
			->assertSessionHas('success', __('FAQ updated successfully'));

		$updated = json_decode(
			LandingPageSetting::where('name', 'faqs')->first()->value,
			true
		)[0];
		$this->assertEquals('NewQ', $updated['faqQuestions']);
		$this->assertEquals('NewA', $updated['faqAnswer']);
	}

	/**
	 ** @test
	 **
	 ** destroy removes FAQ and redirects.
	 **/
	public function destroy_removes_faq_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage faq']);
		$user?->givePermissionTo('manage faq');

		$faqs = [
			['faqQuestions' => 'A', 'faqAnswer' => 'A'],
			['faqQuestions' => 'B', 'faqAnswer' => 'B'],
		];
		LandingPageSetting::create([
			'name'  => 'faqs',
			'value' => json_encode($faqs),
		]);

		$response = $this->actingAs($user)
			->delete(action([FaqController::class, 'destroy'], ['key' => 0]));

		$response->assertRedirect(route('landingpage.faq.index'))
			->assertSessionHas('success', __('FAQ deleted successfully'));

		$remaining = json_decode(
			LandingPageSetting::where('name', 'faqs')->first()->value,
			true
		);
		$this->assertCount(1, $remaining);
		$this->assertEquals('B', $remaining[0]['faqQuestions']);
	}
}
