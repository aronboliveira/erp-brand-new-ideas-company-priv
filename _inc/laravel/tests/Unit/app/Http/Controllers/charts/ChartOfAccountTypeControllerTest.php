<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChartOfAccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\ChartOfAccountTypeController;

class ChartOfAccountTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		// create a user for authentication
		$this->user = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Guests attempting to access the index should be
	 ** redirected to the login page.
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(action([ChartOfAccountTypeController::class, 'index']));
		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** Authenticated users need the manage permission to
	 ** view the index. With permission, the index lists types.
	 **/
	public function index_requires_manage_permission_and_lists_types()
	{
		// without permission
		$response = $this->actingAs($this->user)
			->get(action([ChartOfAccountTypeController::class, 'index']));
		$response->assertRedirect(route('chartOfAccountType.index'));

		// grant permission & seed a type
		$this->user->givePermissionTo('manage constant chart of account type');
		ChartOfAccountType::create([
			'name'       => 'Equity',
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->actingAs($this->user)
			->get(action([ChartOfAccountTypeController::class, 'index']));

		$response->assertOk()
			->assertViewIs('chartOfAccountType.index')
			->assertViewHas('types', fn ($types) => $types->pluck('name')->contains('Equity'));
	}

	/**
	 ** @test
	 **
	 ** The create route requires the create permission,
	 ** and with it displays the creation form.
	 **/
	public function create_requires_permission_and_displays_form()
	{
		// without permission
		$response = $this->actingAs($this->user)
			->get(action([ChartOfAccountTypeController::class, 'create']));
		$response->assertRedirect(route('chartOfAccountType.index'));

		// with permission
		$this->user->givePermissionTo('create constant chart of account type');

		$response = $this->actingAs($this->user)
			->get(action([ChartOfAccountTypeController::class, 'create']));

		$response->assertOk()
			->assertViewIs('chartOfAccountType.create');
	}

	/**
	 ** @test
	 **
	 ** The store route validates the name field and,
	 ** on success, creates a new chart of account type.
	 **/
	public function store_validates_name_and_creates_type()
	{
		$this->user->givePermissionTo('create constant chart of account type');

		// missing name → error
		$response = $this->actingAs($this->user)
			->post(action([ChartOfAccountTypeController::class, 'store']), []);
		$response->assertRedirect()
			->assertSessionHas('error');

		// valid request → created
		$response = $this->actingAs($this->user)
			->post(action([ChartOfAccountTypeController::class, 'store']), [
				'name' => 'Liability',
			]);

		$response->assertRedirect(route('chartOfAccountType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('chart_of_account_types', [
			'name' => 'Liability',
		]);
	}

	/**
	 ** @test
	 **
	 ** The show route should always redirect back
	 ** to the index page.
	 **/
	public function show_always_redirects_to_index()
	{
		$type = ChartOfAccountType::create([
			'name'       => 'Asset',
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->actingAs($this->user)
			->get(action(
				[ChartOfAccountTypeController::class, 'show'],
				['chartOfAccountType' => $type->id]
			));

		$response->assertRedirect(route('chartOfAccountType.index'));
	}

	/**
	 ** @test
	 **
	 ** The edit route requires the edit permission,
	 ** and with it displays the edit form for the given type.
	 **/
	public function edit_requires_permission_and_displays_form()
	{
		$type = ChartOfAccountType::create([
			'name'       => 'Expense',
			'created_by' => $this->user->creatorId(),
		]);

		// without permission
		$response = $this->actingAs($this->user)
			->get(action(
				[ChartOfAccountTypeController::class, 'edit'],
				['chartOfAccountType' => $type->id]
			));
		$response->assertRedirect(route('chartOfAccountType.index'));

		// with permission
		$this->user->givePermissionTo('edit constant chart of account type');
		$response = $this->actingAs($this->user)
			->get(action(
				[ChartOfAccountTypeController::class, 'edit'],
				['chartOfAccountType' => $type->id]
			));

		$response->assertOk()
			->assertViewIs('chartOfAccountType.edit')
			->assertViewHas('chartOfAccountType', fn ($t) => $t->id === $type->id);
	}

	/**
	 ** @test
	 **
	 ** The update route validates the name and,
	 ** on valid input, updates the existing type.
	 **/
	public function update_validates_and_updates_type()
	{
		$type = ChartOfAccountType::create([
			'name'       => 'Initial',
			'created_by' => $this->user->creatorId(),
		]);

		$this->user->givePermissionTo('edit constant chart of account type');

		// missing name
		$response = $this->actingAs($this->user)
			->put(action(
				[ChartOfAccountTypeController::class, 'update'],
				['chartOfAccountType' => $type->id]
			), []);
		$response->assertRedirect()
			->assertSessionHas('error');

		// valid update
		$response = $this->actingAs($this->user)
			->put(action(
				[ChartOfAccountTypeController::class, 'update'],
				['chartOfAccountType' => $type->id]
			), ['name' => 'Updated']);

		$response->assertRedirect(route('chartOfAccountType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('chart_of_account_types', [
			'id'   => $type->id,
			'name' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** The destroy route requires delete permission
	 ** and deletes the specified type on success.
	 **/
	public function destroy_requires_permission_and_deletes_type()
	{
		$type = ChartOfAccountType::create([
			'name'       => 'Temp',
			'created_by' => $this->user->creatorId(),
		]);

		// without permission
		$response = $this->actingAs($this->user)
			->delete(action(
				[ChartOfAccountTypeController::class, 'destroy'],
				['chartOfAccountType' => $type->id]
			));
		$response->assertRedirect(route('chartOfAccountType.index'));

		// with permission
		$this->user->givePermissionTo('delete constant chart of account type');
		$response = $this->actingAs($this->user)
			->delete(action(
				[ChartOfAccountTypeController::class, 'destroy'],
				['chartOfAccountType' => $type->id]
			));

		$response->assertRedirect(route('chartOfAccountType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('chart_of_account_types', [
			'id' => $type->id,
		]);
	}
}
