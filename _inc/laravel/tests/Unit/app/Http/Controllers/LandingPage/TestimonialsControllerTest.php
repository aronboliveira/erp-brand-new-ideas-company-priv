<?php

namespace Tests\Feature\Modules\LandingPage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Http\Controllers\TestimonialsController;

class TestimonialsControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		$ref  = new \ReflectionClass(LandingPageSetting::class);
		$prop = $ref->getProperty('settings');
		$prop->setAccessible(true);
		$prop->setValue(null, null);
		LandingPageSetting::where('name', 'testimonials')->delete();
		LandingPageSetting::where('name', 'testimonials_status')->delete();
		LandingPageSetting::where('name', 'testimonials_heading')->delete();
		LandingPageSetting::where('name', 'testimonials_description')->delete();
	}

	protected function tearDown(): void
	{
		LandingPageSetting::where('name', 'testimonials')->delete();
		LandingPageSetting::where('name', 'testimonials_status')->delete();
		LandingPageSetting::where('name', 'testimonials_heading')->delete();
		LandingPageSetting::where('name', 'testimonials_description')->delete();
		parent::tearDown();
	}

	/** @test */
	public function index_displays_testimonials_for_super_admin()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$uuid = (string) Str::uuid();
		LandingPageSetting::create([
			'name'      => 'testimonials',
			'query_key' => $uuid,
			'value'     => json_encode(['testimonials_heading' => 'First']),
		]);

		$response = $this->actingAs($user)
			->get(action([TestimonialsController::class, 'index']));

		$response->assertStatus(200)
			->assertViewHas('testimonials', function ($testimonials) {
				if (!is_array($testimonials)) return false;
				$first = array_values($testimonials)[0] ?? null;
				return $first !== null && ($first['testimonials_heading'] ?? null) === 'First';
			})
			->assertViewHas('settings');
	}

	/** @test */
	public function index_returns_200_for_authenticated_user()
	{
		$user = User::factory()->create(['type' => 'client']);

		$response = $this->actingAs($user)
			->get(action([TestimonialsController::class, 'index']));

		$response->assertStatus(200);
	}

	/** @test */
	public function store_updates_testimonial_settings()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$payload = [
			'testimonials_heading'     => 'Our Clients Say',
			'testimonials_description' => 'What they say about us',
		];

		$response = $this->actingAs($user)
			->post(action([TestimonialsController::class, 'store']), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting update successfully');

		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'testimonials_status',
			'value' => 'on',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'testimonials_heading',
			'value' => 'Our Clients Say',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => 'testimonials_description',
			'value' => 'What they say about us',
		]);
	}

	/** @test */
	public function create_displays_form()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$response = $this->actingAs($user)
			->get(action([TestimonialsController::class, TestimonialsController::TTM_CRT]));

		$response->assertStatus(200);
	}

	/** @test */
	public function testimonials_store_adds_new_entry()
	{
		$user = User::factory()->create(['type' => 'super admin']);

		$payload = [
			'testimonials_heading'     => 'Great product!',
			'testimonials_description' => 'Short version',
			'testimonials_user'        => 'Jane Doe',
			'testimonials_designation' => 'CEO',
			'testimonials_star'        => 5,
		];

		$response = $this->actingAs($user)
			->post(action([TestimonialsController::class, TestimonialsController::TTM_STR]), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Testimonial added successfully');

		$setting = LandingPageSetting::where('name', 'testimonials')->first();
		$this->assertNotNull($setting, 'Expected a testimonials row to be created');
		$decoded = json_decode($setting->value, true);
		$this->assertNotEmpty($decoded);
		$found = false;
		foreach (array_values($decoded) as $item) {
			if (is_array($item) && ($item['testimonials_heading'] ?? null) === 'Great product!') {
				$found = true;
				break;
			}
		}
		$this->assertTrue($found, 'Expected stored testimonial heading');
	}

	/** @test */
	public function testimonials_edit_displays_edit_form()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$uuid = (string) Str::uuid();
		$itemData = ['testimonials_heading' => 'T1', 'testimonials_user' => 'Alice'];
		LandingPageSetting::create([
			'name'      => 'testimonials',
			'query_key' => $uuid,
			'value'     => json_encode($itemData),
		]);

		$response = $this->actingAs($user)
			->get(action([TestimonialsController::class, TestimonialsController::TTM_EDT], ['key' => $uuid]));

		$response->assertStatus(200)
			->assertViewHas('testimonial', $itemData)
			->assertViewHas('key', $uuid);
	}

	/** @test */
	public function testimonials_update_modifies_existing_entry()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		$uuid = (string) Str::uuid();
		LandingPageSetting::create([
			'name'      => 'testimonials',
			'query_key' => $uuid,
			'value'     => json_encode(['testimonials_heading' => 'Old', 'testimonials_user' => 'Bob', 'testimonials_designation' => 'Dev', 'testimonials_star' => 3]),
		]);

		$payload = ['testimonials_heading' => 'Updated', 'testimonials_user' => 'Bob', 'testimonials_designation' => 'Dev', 'testimonials_star' => 4];

		$response = $this->actingAs($user)
			->post(action([TestimonialsController::class, TestimonialsController::TTM_UPD], ['key' => $uuid]), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', 'Testimonial updated successfully');

		$setting = LandingPageSetting::where('name', 'testimonials')->where('query_key', $uuid)->first();
		$this->assertNotNull($setting, 'Row should still exist after update');
	}

	/** @test */
	public function testimonials_delete_removes_entry()
	{
		$user = User::factory()->create(['type' => 'super admin']);
		LandingPageSetting::create([
			'name'  => 'testimonials',
			'value' => json_encode([
				['testimonials_heading' => 'One', 'testimonials_user' => 'A'],
				['testimonials_heading' => 'Two', 'testimonials_user' => 'B'],
			]),
		]);

		$response = $this->actingAs($user)
			->get(action([TestimonialsController::class, TestimonialsController::TTM_DEL], ['key' => 0]));

		$response->assertRedirect()
			->assertSessionHas('success', 'Testimonial deleted successfully');

		$setting = LandingPageSetting::where('name', 'testimonials')->first();
		$remaining = json_decode($setting->value, true);
		$this->assertCount(1, $remaining);
		$this->assertEquals('Two', $remaining[0]['testimonials_heading']);
	}

	/** @test */
	public function update_modifies_single_setting_value()
	{
		$user    = User::factory()->create(['type' => 'super admin']);
		$setting = LandingPageSetting::create(['name' => 'random_ttm', 'value' => 'old']);

		$payload = ['value' => 'new'];

		$response = $this->actingAs($user)
			->put(
				action([TestimonialsController::class, 'update'], ['testimonial' => $setting->id]),
				$payload
			);

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting updated successfully');

		$this->assertDatabaseHas('landing_page_settings', [
			'id'    => $setting->id,
			'value' => 'new',
		]);
	}

	/** @test */
	public function destroy_deletes_single_setting()
	{
		$user    = User::factory()->create(['type' => 'super admin']);
		$setting = LandingPageSetting::create(['name' => 'ttm_delete', 'value' => 'x']);

		$response = $this->actingAs($user)
			->delete(
				action([TestimonialsController::class, 'destroy'], ['testimonial' => $setting->id])
			);

		$response->assertRedirect()
			->assertSessionHas('success', 'Setting deleted successfully');

		$this->assertDatabaseMissing('landing_page_settings', ['id' => $setting->id]);
	}
}
