<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\TrainingController;
use App\Models\User;
use App\Models\Branch;
use App\Models\TrainingType;
use App\Models\Trainer;
use App\Models\Employee;
use App\Models\Training;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class TrainingControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Should list all trainings for an authorized user.
	 **/
	public function test_index_lists_all_trainings_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage training']);
		$user?->givePermissionTo('manage training');

		$creatorId = $user?->creatorId();
		Training::factory()->count(2)->create(['created_by' => $creatorId]);
		Training::factory()->create(); // other user's training

		$response = $this->actingAs($user)->get(route('training.index'));

		$response->assertStatus(200)
			->assertViewIs('training.index')
			->assertViewHas('trainings', function ($trainings) use ($creatorId) {
				return $trainings->count() === 2
					&& $trainings->every(fn ($t) => $t->created_by === $creatorId);
			})
			->assertViewHas('status');
	}

	/**
	 ** @test
	 **
	 ** Should show the create form for an authorized user.
	 **/
	public function test_create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create training']);
		$user?->givePermissionTo('create training');

		$creatorId = $user?->creatorId();
		Branch::factory()->create(['created_by' => $creatorId]);
		TrainingType::factory()->create(['created_by' => $creatorId]);
		Trainer::factory()->create(['created_by' => $creatorId]);
		Employee::factory()->create(['created_by' => $creatorId]);

		$response = $this->actingAs($user)->get(route('training.create'));

		$response->assertStatus(200)
			->assertViewIs('training.create')
			->assertViewHasAll(['branches', 'trainingTypes', 'trainers', 'employees', 'options']);
	}

	/**
	 ** @test
	 **
	 ** Should persist a new training and redirect on success.
	 **/
	public function test_store_creates_training_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create training']);
		$user?->givePermissionTo('create training');

		$creatorId = $user?->creatorId();
		$branch = Branch::factory()->create(['created_by' => $creatorId]);
		$type = TrainingType::factory()->create(['created_by' => $creatorId]);
		$trainer = Trainer::factory()->create(['created_by' => $creatorId]);
		$employee = Employee::factory()->create(['created_by' => $creatorId]);

		$data = [
			'branch'         => $branch->id,
			'trainer_option' => 'existing',
			'training_type'  => $type->id,
			'trainer'        => $trainer->id,
			'training_cost'  => 1500,
			'employee'       => $employee->id,
			'start_date'     => Carbon::today()->toDateString(),
			'end_date'       => Carbon::today()->addDay()->toDateString(),
			'description'    => 'Team building session',
		];

		$response = $this->actingAs($user)->post(route('training.store'), $data);

		$response->assertRedirect(route('training.index'))
			->assertSessionHas('success', __('Training successfully created.'));
		$this->assertDatabaseHas('trainings', [
			'branch'        => $branch->id,
			'training_type' => $type->id,
			'trainer'       => $trainer->id,
			'training_cost' => 1500,
			'employee'      => $employee->id,
			'created_by'    => $creatorId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Should fail validation when storing with missing fields.
	 **/
	public function test_store_fails_validation_with_missing_fields()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create training']);
		$user?->givePermissionTo('create training');

		$response = $this->actingAs($user)->post(route('training.store'), [
			'branch'        => '',
			'training_type' => '',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseCount('trainings', 0);
	}

	/**
	 ** @test
	 **
	 ** Should display the training details for a valid encrypted ID.
	 **/
	public function test_show_displays_training_detail_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'view training']);
		$user?->givePermissionTo('view training');

		$creatorId = $user?->creatorId();
		$training = Training::factory()->create(['created_by' => $creatorId]);

		$encrypted = Crypt::encrypt($training->id);
		$response = $this->actingAs($user)->get(route('training.show', ['id' => $encrypted]));

		$response->assertStatus(200)
			->assertViewIs('training.show')
			->assertViewHasAll(['training', 'performance', 'status']);
	}

	/**
	 ** @test
	 **
	 ** Should redirect back with error for invalid encrypted ID.
	 **/
	public function test_show_handles_invalid_encrypted_id()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'view training']);
		$user?->givePermissionTo('view training');

		$response = $this->actingAs($user)->get(route('training.show', ['id' => 'invalid']));

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Should show the edit form for an authorized user.
	 **/
	public function test_edit_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit training']);
		$user?->givePermissionTo('edit training');

		$creatorId = $user?->creatorId();
		$training = Training::factory()->create(['created_by' => $creatorId]);

		$response = $this->actingAs($user)->get(route('training.edit', $training));

		$response->assertStatus(200)
			->assertViewIs('training.edit')
			->assertViewHasAll(['branches', 'trainingTypes', 'trainers', 'employees', 'options', 'training']);
	}

	/**
	 ** @test
	 **
	 ** Should update the training and redirect on success.
	 **/
	public function test_update_modifies_training_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit training']);
		$user?->givePermissionTo('edit training');

		$creatorId = $user?->creatorId();
		$training = Training::factory()->create(['created_by' => $creatorId]);

		$data = [
			'branch'        => $training->branch,
			'training_type' => $training->training_type,
			'training_cost' => 2000,
			'employee'      => $training->employee,
			'start_date'    => Carbon::today()->toDateString(),
			'end_date'      => Carbon::today()->addDays(2)->toDateString(),
			'description'   => 'Updated desc',
		];

		$response = $this->actingAs($user)->put(route('training.update', $training), $data);

		$response->assertRedirect(route('training.index'))
			->assertSessionHas('success', __('Training successfully updated.'));
		$this->assertDatabaseHas('trainings', [
			'id'            => $training->id,
			'training_cost' => 2000,
			'description'   => 'Updated desc',
		]);
	}

	/**
	 ** @test
	 **
	 ** Should delete the training and redirect on success.
	 **/
	public function test_destroy_deletes_training_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete training']);
		$user?->givePermissionTo('delete training');

		$training = Training::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->delete(route('training.destroy', $training));

		$response->assertRedirect(route('training.index'))
			->assertSessionHas('success', __('Training successfully deleted.'));
		$this->assertModelMissing($training);
	}

	/**
	 ** @test
	 **
	 ** Should update training status and redirect on success.
	 **/
	public function test_updateStatus_updates_status_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit training']);
		$user?->givePermissionTo('edit training');

		$training = Training::factory()->create(['created_by' => $user?->creatorId()]);

		$payload = [
			'id'          => $training->id,
			'performance' => 'Excellent',
			'status'      => 'completed',
			'remarks'     => 'Well done',
		];

		$response = $this->actingAs($user)->post(
			action([TrainingController::class, 'updateStatus']),
			$payload
		);

		$response->assertRedirect(route('training.index'))
			->assertSessionHas('success', __('Training status successfully updated.'));
		$this->assertDatabaseHas('trainings', [
			'id'          => $training->id,
			'performance' => 'Excellent',
			'status'      => 'completed',
			'remarks'     => 'Well done',
		]);
	}
}
