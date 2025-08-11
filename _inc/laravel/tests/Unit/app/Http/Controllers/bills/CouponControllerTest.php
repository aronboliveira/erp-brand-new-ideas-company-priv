<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\CouponController;
use App\Models\{Coupon, Plan, User, UserCoupon};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, Route};
use Illuminate\View\View;
use Tests\TestCase;

final class CouponControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $admin;

	protected function setUp(): void
	{
		parent::setUp();
		$this->admin = User::factory()->create();
		$this->actingAs($this->admin);
		// Route bindings
		Route::middleware('web')->group(function () {
			Route::resource('coupons', \App\Http\Controllers\CouponController::class);
		});
	}

	/**
	 ** @test
	 **
	 ** index should display the list of all coupons in the index view.
	 **/
	public function test_index_displays_coupon_list(): void
	{
		Coupon::factory()->count(3)->create();
		$response = $this->get(route('coupons.index'));
		$response->assertStatus(200)->assertViewIs('coupon.index');
	}

	/**
	 ** @test
	 **
	 ** create should render the form for creating a new coupon.
	 **/
	public function test_create_displays_form(): void
	{
		$response = $this->get(route('coupons.create'));
		$response->assertStatus(200)->assertViewIs('coupon.create');
	}

	/**
	 ** @test
	 **
	 ** store should persist a new coupon with the provided data
	 ** and redirect back to the coupons index.
	 **/
	public function test_store_creates_coupon(): void
	{
		$payload = [
			'name'       => 'NewCoupon',
			'discount'   => 20,
			'limit'      => 5,
			'manualCode' => 'SAVE20',
		];
		$response = $this->post(route('coupons.store'), $payload);
		$response->assertRedirect(route('coupons.index'));
		$this->assertDatabaseHas('coupons', ['code' => 'SAVE20']);
	}

	/**
	 ** @test
	 **
	 ** show should render the view of a single coupon’s user assignments.
	 **/
	public function test_show_renders_user_coupon_list(): void
	{
		$coupon  = Coupon::factory()->create();
		$response = $this->get(route('coupons.show', $coupon));
		$response->assertStatus(200)->assertViewIs('coupon.view');
	}

	/**
	 ** @test
	 **
	 ** edit should display the form to edit an existing coupon.
	 **/
	public function test_edit_displays_edit_form(): void
	{
		$coupon  = Coupon::factory()->create();
		$response = $this->get(route('coupons.edit', $coupon));
		$response->assertStatus(200)->assertViewIs('coupon.edit');
	}

	/**
	 ** @test
	 **
	 ** update should apply changes to the coupon and
	 ** redirect back to the coupons index.
	 **/
	public function test_update_modifies_coupon(): void
	{
		$coupon = Coupon::factory()->create(['code' => 'OLD10']);
		$response = $this->put(route('coupons.update', $coupon), [
			'name'     => 'Updated',
			'discount' => 10,
			'limit'    => 100,
			'code'     => 'NEW10',
		]);
		$response->assertRedirect(route('coupons.index'));
		$this->assertDatabaseHas('coupons', ['code' => 'NEW10']);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the coupon record and
	 ** redirect to the coupons index.
	 **/
	public function test_destroy_deletes_coupon(): void
	{
		$coupon  = Coupon::factory()->create();
		$response = $this->delete(route('coupons.destroy', $coupon));
		$response->assertRedirect(route('coupons.index'));
		$this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
	}

	/**
	 ** @test
	 **
	 ** apply should return failure JSON when the plan is invalid
	 ** or the coupon code does not exist.
	 **/
	public function test_apply_coupon_invalid_plan(): void
	{
		$response = $this->postJson(route('coupons.apply'), [
			'plan_id' => Crypt::encrypt(999),
			'coupon'  => 'INVALID',
		]);
		$response->assertJson(['is_success' => false]);
	}

	/**
	 ** @test
	 **
	 ** apply should return success JSON with a discount message
	 ** when a valid coupon is applied to a valid plan.
	 **/
	public function test_apply_coupon_valid_discount(): void
	{
		$plan  = Plan::factory()->create(['price' => 100]);
		$coupon = Coupon::factory()->create([
			'code'     => 'DISCOUNT20',
			'discount' => 20,
			'limit'    => 5,
		]);
		$response = $this->postJson(route('coupons.apply'), [
			'plan_id' => Crypt::encrypt($plan->id),
			'coupon'  => 'DISCOUNT20',
		]);
		$response->assertJson([
			'is_success' => true,
			'message'    => __('Coupon code has applied successfully.'),
		]);
	}

	/**
	 ** @test
	 **
	 ** show() returns a view with an empty userCoupons collection when there are none.
	 **/
	public function show_returns_empty_collection_when_no_user_coupons()
	{
		$coupon = Coupon::factory()->create();

		$controller = new CouponController();
		$view = $controller->show($coupon);

		$this->assertInstanceOf(View::class, $view);
		$this->assertEquals('coupon.view', $view->name());
		$data = $view->getData();
		$this->assertArrayHasKey('userCoupons', $data);
		$this->assertCount(0, $data['userCoupons']);
	}

	/**
	 ** @test
	 **
	 ** show() returns a view with all userCoupons for the given coupon.
	 **/
	public function show_returns_all_user_coupons_for_coupon()
	{
		$coupon = Coupon::factory()->create();
		$users = User::factory()->count(3)->create();
		foreach ($users as $user) {
			UserCoupon::factory()->create([
				'coupon' => $coupon->id,
				'user_id' => $user?->id,
			]);
		}

		$controller = new CouponController();
		$view = $controller->show($coupon);

		$this->assertInstanceOf(View::class, $view);
		$this->assertEquals('coupon.view', $view->name());
		$data = $view->getData();
		$this->assertCount(3, $data['userCoupons']);
		// ensure each has loaded the userDetail relationship
		$data['userCoupons']->each(function ($uc) {
			$this->assertTrue($uc->relationLoaded('userDetail'));
		});
	}
}
