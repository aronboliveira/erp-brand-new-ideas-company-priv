<?php

namespace Tests\Unit\app\Http\Controllers\activity;

use App\Http\Middleware\CheckMount;
use App\Models\{Commission, Employee, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Gate, View};
use Tests\TestCase;

class ComissionControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $company;
	protected Employee $employee;
	protected bool $gateAllowAll = true;

	protected function setUp(): void
	{
		parent::setUp();

		$this->withoutMiddleware(CheckMount::class);

		View::share('setting', ['title_text' => 'Test']);
		View::share('colorSettings', ['cust_darklayout' => 'off']);

		User::macro(
			'creatorId',
			/**
			 * @this \App\Models\User
			 * @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		Gate::before(fn () => $this->gateAllowAll ? true : null);

		$this->company = User::factory()->create(['type' => 'company']);
		$this->employee = Employee::factory()->create([
			'created_by' => $this->company->id,
		]);
	}

	/** @test */
	public function test_index_requires_permission()
	{
		$this->gateAllowAll = false;

		$this->actingAs($this->company)
			->get(route('commissions.index'))
			->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test */
	public function test_can_view_index()
	{
		$response = $this->actingAs($this->company)
			->get(route('commissions.index'));

		$this->assertNotEquals(403, $response->getStatusCode());
		$this->assertNotEquals(401, $response->getStatusCode());
	}

	/** @test */
	public function test_create_requires_permission()
	{
		$this->gateAllowAll = false;

		$this->actingAs($this->company)
			->get('/commissions/creates/' . $this->employee->id)
			->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test */
	public function test_can_view_create_form()
	{
		$response = $this->actingAs($this->company)
			->get('/commissions/creates/' . $this->employee->id);

		$this->assertNotEquals(403, $response->getStatusCode());
		$this->assertNotEquals(401, $response->getStatusCode());
	}

	/** @test */
	public function test_store_requires_permission()
	{
		$this->gateAllowAll = false;

		$this->actingAs($this->company)
			->post(route('commissions.store'), [])
			->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test */
	public function test_can_store_commission()
	{
		$payload = [
			'employee_id' => $this->employee->id,
			'title'       => 'Sales Bonus',
			'type'        => 'percentage',
			'amount'      => 150.75,
		];

		$this->actingAs($this->company)
			->post(route('commissions.store'), $payload)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('commissions', [
			'title'       => 'Sales Bonus',
			'employee_id' => $this->employee->id,
			'created_by'  => $this->company->id,
		]);
	}

	/** @test */
	public function test_edit_requires_owner_and_permission()
	{
		$other = User::factory()->create(['type' => 'company']);
		$commission = Commission::factory()->create([
			'created_by' => $other->id,
		]);

		$this->actingAs($this->company)
			->get(route('commissions.edit', $commission->id))
			->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test */
	public function test_can_edit_owned_commission()
	{
		$commission = Commission::factory()->create([
			'employee_id' => $this->employee->id,
			'created_by'  => $this->company->id,
		]);

		$response = $this->actingAs($this->company)
			->get(route('commissions.edit', $commission->id));

		$this->assertNotEquals(403, $response->getStatusCode());
		$this->assertNotEquals(401, $response->getStatusCode());
	}

	/** @test */
	public function test_can_update_commission()
	{
		$commission = Commission::factory()->create([
			'employee_id' => $this->employee->id,
			'created_by'  => $this->company->id,
		]);

		$this->actingAs($this->company)
			->put(route('commissions.update', $commission), [
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

	/** @test */
	public function test_destroy_requires_owner()
	{
		$other = User::factory()->create(['type' => 'company']);
		$commission = Commission::factory()->create([
			'created_by' => $other->id,
		]);

		$this->actingAs($this->company)
			->delete(route('commissions.destroy', $commission))
			->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test */
	public function test_can_destroy_owned_commission()
	{
		$commission = Commission::factory()->create([
			'employee_id' => $this->employee->id,
			'created_by'  => $this->company->id,
		]);

		$this->actingAs($this->company)
			->delete(route('commissions.destroy', $commission))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('commissions', ['id' => $commission->id]);
	}

	/** @test */
	public function commission_create_redirects_back_with_error_if_employee_not_found()
	{
		$this->actingAs($this->company)
			->get('/commissions/creates/999999')
			->assertRedirect()
			->assertSessionHas('error');
	}
}
