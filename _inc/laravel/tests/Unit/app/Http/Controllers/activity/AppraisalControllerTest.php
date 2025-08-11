<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Appraisal, Branch, Competencies, Employee, PerformanceType, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class AppraisalControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $admin;
	protected Employee $employee;
	protected Branch $branch;

	protected function setUp(): void
	{
		parent::setUp();
		Artisan::call('db:seed'); // seed necessary permissions
		$this->admin   = User::factory()->admin()->create();
		$this->employee = Employee::factory()->create(['created_by' => $this->admin->id]);
		$this->branch  = Branch::factory()->create(['created_by' => $this->admin->id]);
	}

	/**
	 ** @test
	 **
	 ** Test that the index route renders the appraisal index view
	 ** for an authenticated admin.
	 **/
	public function test_index_screen_displays(): void
	{
		$this->actingAs($this->admin)
			->get(route('appraisal.index'))
			->assertOk()
			->assertViewIs('appraisal.index');
	}

	/**
	 ** @test
	 **
	 ** Test that the create route renders the appraisal creation form,
	 ** including loading PerformanceType options for the admin.
	 **/
	public function test_create_screen_displays(): void
	{
		PerformanceType::factory()->create(['created_by' => $this->admin->id]);

		$this->actingAs($this->admin)
			->get(route('appraisal.create'))
			->assertOk()
			->assertViewIs('appraisal.create');
	}

	/**
	 ** @test
	 **
	 ** Test that posting to store with valid data creates a new appraisal
	 ** and redirects back to the index route.
	 **/
	public function test_store_creates_appraisal(): void
	{
		PerformanceType::factory()->create(['created_by' => $this->admin->id]);

		$response = $this->actingAs($this->admin)
			->post(route('appraisal.store'), [
				'branch'         => $this->branch->id,
				'employee'       => $this->employee->id,
				'appraisal_date' => now()->toDateString(),
				'rating'         => ['criteria_1' => 5],
			]);

		$response->assertRedirect(route('appraisal.index'));
		$this->assertDatabaseHas('appraisals', [
			'branch'   => $this->branch->id,
			'employee' => $this->employee->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Test that the show route renders the appraisal detail view
	 ** for an existing appraisal owned by the admin.
	 **/
	public function test_show_displays_appraisal(): void
	{
		$appraisal = Appraisal::factory()->create([
			'created_by' => $this->admin->id,
			'employee'   => $this->employee->id,
			'branch'     => $this->branch->id,
			'rating'     => json_encode([]),
		]);

		$this->actingAs($this->admin)
			->get(route('appraisal.show', $appraisal))
			->assertOk()
			->assertViewIs('appraisal.show');
	}

	/**
	 ** @test
	 **
	 ** Test that the edit route renders the appraisal edit form
	 ** when requested by the admin for their own appraisal.
	 **/
	public function test_edit_screen_displays(): void
	{
		$appraisal = Appraisal::factory()->create([
			'created_by' => $this->admin->id,
			'employee'   => $this->employee->id,
			'branch'     => $this->branch->id,
			'rating'     => json_encode([]),
		]);

		$this->actingAs($this->admin)
			->get(route('appraisal.edit', $appraisal))
			->assertOk()
			->assertViewIs('appraisal.edit');
	}

	/**
	 ** @test
	 **
	 ** Test that submitting the update route with valid data
	 ** modifies the existing appraisal record and redirects back to index.
	 **/
	public function test_update_modifies_appraisal(): void
	{
		$appraisal = Appraisal::factory()->create([
			'created_by' => $this->admin->id,
			'employee'   => $this->employee->id,
			'branch'     => $this->branch->id,
			'rating'     => json_encode([]),
		]);

		$this->actingAs($this->admin)
			->put(route('appraisal.update', $appraisal), [
				'branch'         => $this->branch->id,
				'employee'       => $this->employee->id,
				'appraisal_date' => now()->toDateString(),
				'rating'         => ['criteria_1' => 4],
			])
			->assertRedirect(route('appraisal.index'));

		$this->assertDatabaseHas('appraisals', [
			'id'       => $appraisal->id,
			'employee' => $this->employee->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Test that calling the destroy route deletes the specified appraisal
	 ** and redirects back to the index view.
	 **/
	public function test_destroy_deletes_appraisal(): void
	{
		$appraisal = Appraisal::factory()->create([
			'created_by' => $this->admin->id,
			'employee'   => $this->employee->id,
		]);

		$this->actingAs($this->admin)
			->delete(route('appraisal.destroy', $appraisal))
			->assertRedirect(route('appraisal.index'));

		$this->assertDatabaseMissing('appraisals', ['id' => $appraisal->id]);
	}
}
