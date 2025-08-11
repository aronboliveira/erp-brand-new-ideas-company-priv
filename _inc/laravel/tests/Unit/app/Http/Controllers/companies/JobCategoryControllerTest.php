<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\JobCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\JobCategoryController;

class JobCategoryControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private JobCategory $category;

	protected function setUp(): void
	{
		parent::setUp();

		// define creatorId macro
		User::macro(
			'creatorId',
			/**
			 ** @this \App\Models\User
			 ** @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		// allow or deny in each test
		Gate::before(fn () => true);

		// create and authenticate our user
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		// seed a category
		$this->category = JobCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login, and authenticated users without
	 ** the 'manage job category' permission should be redirected back to index.
	 ** Users with the permission should see the index view with categories.
	 **/
	public function index_redirects_guests_and_requires_manage_permission()
	{
		// Guest → redirect to login
		$resp = $this->get(action([JobCategoryController::class, 'index']));
		$resp->assertRedirect();

		// Authenticated without permission → redirect back to index
		$resp = $this->actingAs($this->user)
			->get(action([JobCategoryController::class, 'index']));
		$resp->assertRedirect(route('jobCategory.index'));

		// With permission and seeded categories → shows view
		$this->user->givePermissionTo('manage job category');
		JobCategory::create([
			'title'      => 'Engineering',
			'created_by' => $this->user->creatorId(),
		]);

		$resp = $this->actingAs($this->user)
			->get(action([JobCategoryController::class, 'index']));

		$resp->assertOk()
			->assertViewIs('jobCategory.index')
			->assertViewHas('categories', function ($cats) {
				return $cats->pluck('title')->contains('Engineering');
			});
	}

	/**
	 ** @test
	 **
	 ** The create route requires 'create job category' permission.
	 ** Without it, users are redirected. With it, they see the create form.
	 **/
	public function create_requires_permission_and_displays_form()
	{
		// Without create permission → redirect
		$resp = $this->actingAs($this->user)
			->get(action([JobCategoryController::class, 'create']));
		$resp->assertRedirect(route('jobCategory.index'));

		// With permission → shows create view
		$this->user->givePermissionTo('create job category');
		$resp = $this->actingAs($this->user)
			->get(action([JobCategoryController::class, 'create']));
		$resp->assertOk()
			->assertViewIs('jobCategory.create');
	}

	/**
	 ** @test
	 **
	 ** The store action validates input and creates a new category
	 ** when the user has 'create job category' permission.
	 **/
	public function store_validates_and_creates_category()
	{
		$this->user->givePermissionTo('create job category');

		// Missing title → error
		$resp = $this->actingAs($this->user)
			->post(action([JobCategoryController::class, 'store']), []);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// Valid payload → created
		$resp = $this->actingAs($this->user)
			->post(action([JobCategoryController::class, 'store']), [
				'title' => 'Marketing',
			]);

		$resp->assertRedirect(route('jobCategory.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('job_categories', [
			'title'      => 'Marketing',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** The edit route requires 'edit job category' permission.
	 ** Authorized users see the edit form populated with the category.
	 **/
	public function edit_requires_permission_and_displays_form()
	{
		$category = JobCategory::create([
			'title'      => 'Sales',
			'created_by' => $this->user->creatorId(),
		]);

		// Without edit permission → redirect
		$resp = $this->actingAs($this->user)
			->get(action([JobCategoryController::class, 'edit'], ['id' => $category->id]));
		$resp->assertRedirect(route('jobCategory.index'));

		// With permission → shows edit view
		$this->user->givePermissionTo('edit job category');
		$resp = $this->actingAs($this->user)
			->get(action([JobCategoryController::class, 'edit'], ['id' => $category->id]));
		$resp->assertOk()
			->assertViewIs('jobCategory.edit')
			->assertViewHas('jobCategory', fn ($c) => $c->id === $category->id);
	}

	/**
	 ** @test
	 **
	 ** The update action validates input and updates the category
	 ** when the user has 'edit job category' permission.
	 **/
	public function update_validates_and_updates_category()
	{
		$category = JobCategory::create([
			'title'      => 'Support',
			'created_by' => $this->user->creatorId(),
		]);
		$this->user->givePermissionTo('edit job category');

		// Missing title → error
		$resp = $this->actingAs($this->user)
			->put(action([JobCategoryController::class, 'update'], ['id' => $category->id]), []);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// Valid update → success
		$resp = $this->actingAs($this->user)
			->put(action([JobCategoryController::class, 'update'], ['id' => $category->id]), [
				'title' => 'Customer Support',
			]);
		$resp->assertRedirect(route('jobCategory.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('job_categories', [
			'id'    => $category->id,
			'title' => 'Customer Support',
		]);
	}

	/**
	 ** @test
	 **
	 ** The destroy action requires 'delete job category' permission and ownership.
	 ** Unauthorized attempts redirect back; authorized deletion removes the record.
	 **/
	public function destroy_requires_permission_owner_and_deletes_category()
	{
		$category = JobCategory::create([
			'title'      => 'Temp',
			'created_by' => $this->user->creatorId(),
		]);

		// Without delete permission → redirect
		$resp = $this->actingAs($this->user)
			->delete(action([JobCategoryController::class, 'destroy'], ['id' => $category->id]));
		$resp->assertRedirect(route('jobCategory.index'));

		// With permission but wrong owner → redirect back
		$this->user->givePermissionTo('delete job category');
		$other = JobCategory::create([
			'title'      => 'Other',
			'created_by' => $this->user->creatorId() + 1,
		]);
		$resp = $this->actingAs($this->user)
			->delete(action([JobCategoryController::class, 'destroy'], ['id' => $other->id]));
		$resp->assertRedirect(route('jobCategory.index'));

		// Correct owner & permission → deleted
		$resp = $this->actingAs($this->user)
			->delete(action([JobCategoryController::class, 'destroy'], ['id' => $category->id]));
		$resp->assertRedirect(route('jobCategory.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('job_categories', ['id' => $category->id]);
	}

	/**
	 ** @test
	 **
	 ** Guests get redirected when accessing the show route.
	 **/
	public function show_redirects_guests_to_login()
	{
		auth()->logout();

		$response = $this->get(route('jobCategory.show', $this->category));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** It denies access if the user lacks the view permission.
	 **/
	public function show_denies_without_permission()
	{
		Gate::before(fn () => false);

		$response = $this->get(route('jobCategory.show', $this->category));

		$response->assertRedirect(route('jobCategory.index'));
	}

	/**
	 ** @test
	 **
	 ** It displays the category when the user has permission.
	 **/
	public function show_displays_view_when_authorized()
	{
		Gate::before(fn () => true);

		$response = $this->get(route('jobCategory.show', $this->category));

		$response->assertOk()
			->assertViewIs('jobCategory.show')
			->assertViewHas('jobCategory', fn ($c) => $c->id === $this->category->id);
	}
}
