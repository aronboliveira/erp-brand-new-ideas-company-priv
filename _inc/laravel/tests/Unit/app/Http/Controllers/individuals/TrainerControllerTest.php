<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Branch;
use App\Models\Trainer;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class TrainerControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 *
	 ** index should display the list of trainers for users with 'manage trainer' permission
	 **/
	public function index_displays_trainers_for_authorized_user()
	{
		Permission::create(['name' => 'manage trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage trainer');

		$branch = Branch::create([
			'name'       => 'Main Branch',
			'created_by' => $user?->creatorId(),
		]);

		Trainer::create([
			'branch'     => $branch->id,
			'firstname'  => 'John',
			'lastname'   => 'Doe',
			'contact'    => '123456789',
			'email'      => 'john@example.com',
			'address'    => '123 St.',
			'expertise'  => 'Fitness',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('trainer.index'));

		$response->assertStatus(200);
		$response->assertViewIs('trainer.index');
		$response->assertViewHas('trainers', function ($trainers) use ($user) {
			return $trainers->count() === 1
				&& $trainers->first()->created_by === $user?->creatorId();
		});
	}

	/**
	 ** @test
	 *
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('trainer.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 *
	 ** create should display form for users with 'create trainer' permission
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create trainer');

		Branch::create([
			'name'       => 'Branch A',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('trainer.create'));

		$response->assertStatus(200);
		$response->assertViewIs('trainer.create');
		$response->assertViewHas('branches');
	}

	/**
	 ** @test
	 *
	 ** store should redirect back with error on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create trainer');

		$response = $this->actingAs($user)
			->from(route('trainer.create'))
			->post(route('trainer.store'), [
				'branch'    => null,
				'firstname' => '',
				'lastname'  => '',
				'contact'   => '',
				'email'     => 'not-an-email'
			]);

		$response->assertRedirect(route('trainer.create'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 *
	 ** store should create a new trainer and redirect to index on success
	 **/
	public function store_creates_trainer_and_redirects_on_success()
	{
		Permission::create(['name' => 'create trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create trainer');

		$branch = Branch::create([
			'name'       => 'Branch B',
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'branch'    => $branch->id,
			'firstname' => 'Alice',
			'lastname'  => 'Smith',
			'contact'   => '987654321',
			'email'     => 'alice@example.com',
			'address'   => '456 Ave.',
			'expertise' => 'Yoga',
		];

		$response = $this->actingAs($user)
			->post(route('trainer.store'), $payload);

		$response->assertRedirect(route('trainer.index'));
		$this->assertDatabaseHas('trainers', [
			'firstname'  => 'Alice',
			'lastname'   => 'Smith',
			'email'      => 'alice@example.com',
			'branch'     => $branch->id,
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 *
	 ** show should display a trainer for users with 'view trainer' permission
	 **/
	public function show_displays_trainer_for_authorized_user()
	{
		Permission::create(['name' => 'view trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('view trainer');

		$branch = Branch::create([
			'name'       => 'Branch C',
			'created_by' => $user?->creatorId(),
		]);

		$trainer = Trainer::create([
			'branch'     => $branch->id,
			'firstname'  => 'Bob',
			'lastname'   => 'Jones',
			'contact'    => '5555555',
			'email'      => 'bob@example.com',
			'address'    => null,
			'expertise'  => null,
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('trainer.show', $trainer));

		$response->assertStatus(200);
		$response->assertViewIs('trainer.show');
		$response->assertViewHas('trainer', $trainer);
	}

	/**
	 ** @test
	 *
	 ** edit should display the edit form for users with 'edit trainer' permission
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'edit trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit trainer');

		$branch = Branch::create([
			'name'       => 'Branch D',
			'created_by' => $user?->creatorId(),
		]);

		$trainer = Trainer::create([
			'branch'     => $branch->id,
			'firstname'  => 'Carol',
			'lastname'   => 'White',
			'contact'    => '4444444',
			'email'      => 'carol@example.com',
			'address'    => null,
			'expertise'  => null,
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('trainer.edit', $trainer));

		$response->assertStatus(200);
		$response->assertViewIs('trainer.edit');
		$response->assertViewHasAll(['trainer', 'branches']);
	}

	/**
	 ** @test
	 *
	 ** update should redirect back with error on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'edit trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit trainer');

		$branch = Branch::create([
			'name'       => 'Branch E',
			'created_by' => $user?->creatorId(),
		]);

		$trainer = Trainer::create([
			'branch'     => $branch->id,
			'firstname'  => 'Dave',
			'lastname'   => 'Brown',
			'contact'    => '3333333',
			'email'      => 'dave@example.com',
			'address'    => null,
			'expertise'  => null,
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->from(route('trainer.edit', $trainer))
			->put(route('trainer.update', $trainer), [
				'branch'    => $branch->id,
				'firstname' => '',
				'lastname'  => '',
				'contact'   => '',
				'email'     => 'invalid'
			]);

		$response->assertRedirect(route('trainer.edit', $trainer));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 *
	 ** update should modify the trainer and redirect on success
	 **/
	public function update_modifies_trainer_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit trainer');

		$branch = Branch::create([
			'name'       => 'Branch F',
			'created_by' => $user?->creatorId(),
		]);

		$trainer = Trainer::create([
			'branch'     => $branch->id,
			'firstname'  => 'Eve',
			'lastname'   => 'Davis',
			'contact'    => '2222222',
			'email'      => 'eve@example.com',
			'address'    => null,
			'expertise'  => null,
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'branch'    => $branch->id,
			'firstname' => 'Evelyn',
			'lastname'  => 'Davis',
			'contact'   => '2222222',
			'email'     => 'evelyn@example.com',
			'address'   => '789 Blvd.',
			'expertise' => 'Pilates',
		];

		$response = $this->actingAs($user)
			->put(route('trainer.update', $trainer), $payload);

		$response->assertRedirect(route('trainer.index'));
		$this->assertDatabaseHas('trainers', [
			'id'         => $trainer->id,
			'firstname'  => 'Evelyn',
			'email'      => 'evelyn@example.com',
		]);
	}

	/**
	 ** @test
	 *
	 ** destroy should delete the trainer and redirect for users with 'delete trainer' permission
	 **/
	public function destroy_deletes_trainer_and_redirects_on_success()
	{
		Permission::create(['name' => 'delete trainer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('delete trainer');

		$branch = Branch::create([
			'name'       => 'Branch G',
			'created_by' => $user?->creatorId(),
		]);

		$trainer = Trainer::create([
			'branch'     => $branch->id,
			'firstname'  => 'Frank',
			'lastname'   => 'Moore',
			'contact'    => '1111111',
			'email'      => 'frank@example.com',
			'address'    => null,
			'expertise'  => null,
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->delete(route('trainer.destroy', $trainer));

		$response->assertRedirect(route('trainer.index'));
		$this->assertDatabaseMissing('trainers', ['id' => $trainer->id]);
	}
}
