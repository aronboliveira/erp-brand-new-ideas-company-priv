<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomFieldController;
use App\Models\{CustomField, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, Gate, Request, Response};
use Tests\TestCase;

class CustomFieldControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// Macro so creatorId() returns the user's own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		// By default grant all permissions
		Gate::before(fn () => true);

		$this->company = User::factory()->create(['type' => 'company']);
		$this->other  = User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** index requires "manage constant custom field" permission.
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('custom-field.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index lists only custom fields created by the user.
	 **/
	public function index_lists_only_user_custom_fields()
	{
		CustomField::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		CustomField::factory()->create(['created_by' => $this->other->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('custom-field.index'));

		$response->assertOk()
			->assertViewIs('customFields.index')
			->assertViewHas('customFields', fn ($list) => $list->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** create requires "create constant custom field" permission.
	 **/
	public function create_requires_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->company)
			->get(route('custom-field.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create displays form with types and modules.
	 **/
	public function create_displays_form_with_types_and_modules()
	{
		$response = $this->actingAs($this->company)
			->get(route('custom-field.create'));

		$response->assertOk()
			->assertViewIs('customFields.create')
			->assertViewHas('types', fn ($t) => is_array($t))
			->assertViewHas('modules', fn ($m) => is_array($m));
	}

	/**
	 ** @test
	 **
	 ** store validates input and creates a custom field.
	 **/
	public function store_validates_and_creates_custom_field()
	{
		// missing required fields
		$this->actingAs($this->company)
			->post(route('custom-field.store'), [])
			->assertRedirect(route('custom-field.index'))
			->assertSessionHas('error');

		$payload = [
			'name'   => 'Field A',
			'type'   => array_key_first(CustomField::$fieldTypes),
			'module' => array_key_first(CustomField::$modules),
		];

		$response = $this->actingAs($this->company)
			->post(route('custom-field.store'), $payload);

		$response->assertRedirect(route('custom-field.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('custom_fields', [
			'name'       => 'Field A',
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit requires permission and forbids non-owner.
	 **/
	public function edit_requires_permission_and_owner()
	{
		$cf = CustomField::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('custom-field.edit', $cf))
			->assertStatus(403);

		// with permission but wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->get(route('custom-field.edit', $cf))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** edit displays form for owner.
	 **/
	public function edit_displays_form_for_owner()
	{
		$cf = CustomField::factory()->create(['created_by' => $this->company->creatorId()]);

		$response = $this->actingAs($this->company)
			->get(route('custom-field.edit', $cf));

		$response->assertOk()
			->assertViewIs('customFields.edit')
			->assertViewHas('customField', fn ($v) => $v->id === $cf->id)
			->assertViewHas('types', fn ($t) => is_array($t))
			->assertViewHas('modules', fn ($m) => is_array($m));
	}

	/**
	 ** @test
	 **
	 ** update validates input and updates the custom field.
	 **/
	public function update_validates_and_updates_custom_field()
	{
		$cf = CustomField::factory()->create([
			'name'       => 'OldName',
			'created_by' => $this->company->creatorId(),
		]);

		// validation failure
		$this->actingAs($this->company)
			->put(route('custom-field.update', $cf), ['name' => ''])
			->assertRedirect(route('custom-field.index'))
			->assertSessionHas('error');

		// success
		$this->actingAs($this->company)
			->put(route('custom-field.update', $cf), ['name' => 'NewName'])
			->assertRedirect(route('custom-field.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('custom_fields', [
			'id'   => $cf->id,
			'name' => 'NewName',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy requires permission and owner, and deletes the custom field.
	 **/
	public function destroy_requires_permission_and_owner_and_deletes()
	{
		$cf = CustomField::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('custom-field.destroy', $cf))
			->assertStatus(403);

		// wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->delete(route('custom-field.destroy', $cf))
			->assertStatus(403);

		// success
		Gate::before(fn () => true);
		$response = $this->actingAs($this->company)
			->delete(route('custom-field.destroy', $cf));

		$response->assertRedirect(route('custom-field.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('custom_fields', ['id' => $cf->id]);
	}

	/**
	 ** @test
	 **
	 ** show() redirects guests (unauthenticated) to login.
	 **/
	public function show_redirects_guests_to_login()
	{
		$field     = CustomField::factory()->create();
		$controller = new CustomFieldController();
		$request   = Request::create("/custom-fields/{$field->id}", 'GET');
		// ensure no user is authenticated
		Auth::logout();
		$request->setUserResolver(fn () => null);

		$response = $controller->show($request, $field);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		// default Laravel login route
		$this->assertStringContainsString(route('login'), $response->headers->get('Location'));
	}

	/**
	 ** @test
	 **
	 ** show() denies users without the 'view constant custom field' permission.
	 **/
	public function show_denies_user_without_permission()
	{
		$user      = User::factory()->create();
		$field     = CustomField::factory()->create(['created_by' => $user?->creatorId()]);
		$controller = new CustomFieldController();

		Auth::login($user);
		$request = Request::create("/custom-fields/{$field->id}", 'GET');
		$request->setUserResolver(fn () => $user);
		$request->setLaravelSession(session());

		$response = $controller->show($request, $field);

		$this->assertInstanceOf(RedirectResponse::class, $response);

		// session should have an error flashed by guard()
		$this->assertTrue(session()->has('error'));
	}

	/**
	 ** @test
	 **
	 ** show() denies non-owners even if they have permission.
	 **/
	public function show_denies_non_owner_even_with_permission()
	{
		$owner     = User::factory()->create();
		$other     = User::factory()->create();
		$other->givePermissionTo('view constant custom field');

		$field     = CustomField::factory()->create(['created_by' => $owner->creatorId()]);
		$controller = new CustomFieldController();

		Auth::login($other);
		$request = Request::create("/custom-fields/{$field->id}", 'GET');
		$request->setUserResolver(fn () => $other);
		$request->setLaravelSession(session());

		$response = $controller->show($request, $field);

		$this->assertInstanceOf(RedirectResponse::class, $response);

		// session should have an error indicating ownership denial
		$this->assertTrue(session()->has('error'));
	}

	/**
	 ** @test
	 **
	 ** show() returns a 200 response with the correct view for an owner with permission.
	 **/
	public function show_returns_view_for_owner_with_permission()
	{
		$user      = User::factory()->create();
		$user?->givePermissionTo('view constant custom field');

		$field     = CustomField::factory()->create(['created_by' => $user?->creatorId()]);
		$controller = new CustomFieldController();

		Auth::login($user);
		$request = Request::create("/custom-fields/{$field->id}", 'GET');
		$request->setUserResolver(fn () => $user);

		$response = $controller->show($request, $field);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(200, $response->getStatusCode());

		// Optionally confirm that the response contains the field's name
		$this->assertStringContainsString($field->name, $response->getContent());
	}
}
