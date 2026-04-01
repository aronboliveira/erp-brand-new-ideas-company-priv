<?php

namespace Tests\Unit\app\Http\Controllers\activity;

use App\Http\Controllers\CommissionController;
use App\Models\{Commission, Employee, User};
use Tests\Unit\Traits\CreatesMockUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, Request};
use Illuminate\View\View;
use Tests\TestCase;

class ComissionControllerTest extends TestCase
{
	use RefreshDatabase, CreatesMockUser;

	protected User $admin;
	protected Employee $employee;

	protected function setUp(): void
	{
		parent::setUp();

		$this->admin = $this->createUserWithPermissions([
			'create commission',
			'view commission',
			'edit commission',
			'delete commission'
		]);
		$this->employee = Employee::factory()->create([
			'created_by' => $this->admin->id
		]);
	}

	/**
	 ** @test
	 **
	 ** Ensure that the commission index endpoint returns 403 Forbidden
	 ** when the user does not have the 'view commission' permission.
	 **/
	public function test_index_requires_permission()
	{
		$user = $this->createUserWithoutPermissions();
		$this->actingAs($user)
			->get(route('commission.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that a user with 'view commission' permission
	 ** can successfully access the commission index.
	 **/
	public function test_can_view_index()
	{
		$this->actingAs($this->admin)
			->get(route('commission.index'))
			->assertStatus(200);
	}

	/**
	 ** @test
	 **
	 ** Ensure that accessing the create form without
	 ** 'create commission' permission returns 403 Forbidden.
	 **/
	public function test_create_requires_permission()
	{
		$user = $this->createUserWithoutPermissions();
		$this->actingAs($user)
			->get(route('commission.create', $this->employee->id))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that a user with 'create commission' permission
	 ** can view the commission creation form.
	 **/
	public function test_can_view_create_form()
	{
		$this->actingAs($this->admin)
			->get(route('commission.create', $this->employee->id))
			->assertStatus(200)
			->assertSee('Commission');
	}

	/**
	 ** @test
	 **
	 ** Ensure that posting to store without 'create commission'
	 ** permission returns 403 Forbidden.
	 **/
	public function test_store_requires_permission()
	{
		$user = $this->createUserWithoutPermissions();
		$this->actingAs($user)
			->post(route('commission.store'), [])
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that a commission can be stored by a user
	 ** with 'create commission' permission and valid data.
	 **/
	public function test_can_store_commission()
	{
		$payload = [
			'employee_id' => $this->employee->id,
			'title'       => 'Sales Bonus',
			'type'        => 'percentage',
			'amount'      => 150.75,
		];

		$this->actingAs($this->admin)
			->post(route('commission.store'), $payload)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('commissions', [
			'title'       => 'Sales Bonus',
			'employee_id' => $this->employee->id,
			'created_by'  => $this->admin->id
		]);
	}

	/**
	 ** @test
	 **
	 ** Ensure that editing a commission not owned by the user
	 ** returns 403 Forbidden, even if they have 'edit commission' permission.
	 **/
	public function test_edit_requires_owner_and_permission()
	{
		$commission = Commission::factory()->create(); // not owned
		$this->actingAs($this->admin)
			->get(route('commission.edit', $commission->id))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that a user can access the edit form for
	 ** their own commission when they have permission.
	 **/
	public function test_can_edit_owned_commission()
	{
		$commission = Commission::factory()->create([
			'employee_id' => $this->employee->id,
			'created_by'  => $this->admin->id,
		]);

		$this->actingAs($this->admin)
			->get(route('commission.edit', $commission->id))
			->assertStatus(200)
			->assertSee($commission->title);
	}

	/**
	 ** @test
	 **
	 ** Verify that an owned commission can be updated
	 ** by a user with 'edit commission' permission.
	 **/
	public function test_can_update_commission()
	{
		$commission = Commission::factory()->create([
			'employee_id' => $this->employee->id,
			'created_by'  => $this->admin->id,
		]);

		$this->actingAs($this->admin)
			->put(route('commission.update', $commission), [
				'title'  => 'Updated Title',
				'type'   => 'fixed',
				'amount' => 99.99,
			])
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('commissions', [
			'id'    => $commission->id,
			'title' => 'Updated Title',
			'type'  => 'fixed',
		]);
	}

	/**
	 ** @test
	 **
	 ** Ensure that deleting a commission not owned by the user
	 ** returns 403 Forbidden, even with 'delete commission' permission.
	 **/
	public function test_destroy_requires_owner()
	{
		$commission = Commission::factory()->create(); // not owned
		$this->actingAs($this->admin)
			->delete(route('commission.destroy', $commission))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that a user can delete their own commission
	 ** when they have 'delete commission' permission.
	 **/
	public function test_can_destroy_owned_commission()
	{
		$commission = Commission::factory()->create([
			'employee_id' => $this->employee->id,
			'created_by'  => $this->admin->id,
		]);

		$this->actingAs($this->admin)
			->delete(route('commission.destroy', $commission))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('commissions', ['id' => $commission->id]);
	}

	/**
	 ** @test
	 **
	 ** commissionCreate() returns the unauthorized response when the user fails authorization.
	 **/
	public function commission_create_returns_unauthorized_if_not_allowed()
	{
		$user = User::factory()->create();
		$employeeId = 123;

		$controller = \Mockery::mock(CommissionController::class . '[_authorize]')
			->makePartial();
		// simulate authorization failure
		$redirect = redirect('/forbidden');
		$controller->shouldReceive('_authorize')
			->once()
			->withArgs(function (Request $req, $perm) use ($user) {
				return $perm === 'create commission' && $req->user()->id === $user?->id;
			})
			->andReturn($redirect);

		Auth::login($user);
		$request = Request::create("/commission/create/{$employeeId}", 'GET');
		$request->setUserResolver(fn () => $user);

		$response = $controller->commissionCreate($request, $employeeId);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertEquals('/forbidden', $response->headers->get('Location'));
	}

	/**
	 ** @test
	 **
	 ** commissionCreate() redirects back with error if the employee is not found.
	 **/
	public function commission_create_redirects_back_with_error_if_employee_not_found()
	{
		$user = User::factory()->create();

		$controller = \Mockery::mock(CommissionController::class . '[_authorize]')
			->makePartial();
		// simulate authorization success
		$controller->shouldReceive('_authorize')
			->once()
			->andReturnNull();

		Auth::login($user);
		$request = Request::create('/commission/create/999', 'GET');
		$request->setUserResolver(fn () => $user);
		// attach session for redirect()->back()
		$request->setLaravelSession(session());

		$response = $controller->commissionCreate($request, '999');

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertTrue(session()->has('error'));
		$this->assertEquals('Employee not found.', session('error'));
	}

	/**
	 ** @test
	 **
	 ** commissionCreate() returns the view with employee and types when authorized and employee exists.
	 **/
	public function commission_create_returns_view_with_employee_and_types_on_success()
	{
		$user = User::factory()->create();
		$employee = Employee::factory()->create();

		$controller = \Mockery::mock(CommissionController::class . '[_authorize]')
			->makePartial();
		// simulate authorization success
		$controller->shouldReceive('_authorize')
			->once()
			->andReturnNull();

		Auth::login($user);
		$request = Request::create("/commission/create/{$employee->id}", 'GET');
		$request->setUserResolver(fn () => $user);

		$response = $controller->commissionCreate($request, $employee->id);

		$this->assertInstanceOf(View::class, $response);
		$this->assertEquals('commission.create', $response->getName());

		$data = $response->getData();
		$this->assertArrayHasKey('employee', $data);
		$this->assertSame($employee->id, $data['employee']->id);

		$this->assertArrayHasKey('types', $data);
		$this->assertSame(Commission::$commissiontype, $data['types']);
	}
}
