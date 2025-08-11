<?php

namespace Tests\Unit;

use App\Http\Controllers\FormBuilderController;
use App\Models\FormBuilder;
use App\Models\FormField;
use App\Models\FormFieldResponse;
use App\Models\FormResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use Tests\TestCase;

class FormBuilderControllerTest extends TestCase
{
	use RefreshDatabase;

	private FormBuilderController $controller;
	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// Create and authenticate a user
		$this->user = User::factory()->create();
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
		Auth::login($this->user);

		$this->controller = new FormBuilderController();
	}

	/**
	 ** @test
	 **
	 ** _authorize should return a RedirectResponse when the user lacks permission.
	 **/
	public function authorize_denies_when_user_cannot()
	{
		// Make user->can return false
		User::macro('can', fn ($perm) => false);

		$req = Request::create('/', 'GET');
		$resp = $this->invokeAuthorize($req, 'manage form builder');

		$this->assertInstanceOf(RedirectResponse::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** _authorize should return null when the user has the permission.
	 **/
	public function authorize_allows_when_user_can()
	{
		// Make user->can return true
		User::macro('can', fn ($perm) => true);

		$req = Request::create('/', 'GET');
		$resp = $this->invokeAuthorize($req, 'manage form builder');

		$this->assertNull($resp);
	}

	/**
	 ** @test
	 **
	 ** store should validate, create a FormBuilder, and redirect to index.
	 **/
	public function store_creates_form_and_redirects()
	{
		// Ensure user->can returns true
		User::macro('can', fn ($perm) => true);

		$req = Request::create('/form_builder', 'POST', ['name' => 'My Form', 'is_active' => '1']);
		$req->setUserResolver(fn () => $this->user);

		$resp = $this->controller->store($req);

		$this->assertInstanceOf(RedirectResponse::class, $resp);
		$this->assertDatabaseHas('form_builders', [
			'name'       => 'My Form',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the form and its related fields and responses.
	 **/
	public function destroy_cleans_up_form_and_relations()
	{
		// Prepare user->can
		User::macro('can', fn ($perm) => true);

		// Create a form with fields and responses
		$form = FormBuilder::factory()->create(['created_by' => $this->user->creatorId()]);
		FormField::factory()->count(2)->create(['form_id' => $form->id, 'created_by' => $this->user->creatorId()]);
		FormResponse::factory()->create(['form_id' => $form->id]);
		FormFieldResponse::factory()->create(['form_id' => $form->id]);

		// Call destroy
		$resp = $this->controller->destroy($form);

		$this->assertInstanceOf(RedirectResponse::class, $resp);
		$this->assertDatabaseMissing('form_builders', ['id' => $form->id]);
		$this->assertDatabaseCount('form_fields', 0);
		$this->assertDatabaseCount('form_responses', 0);
		$this->assertDatabaseCount('form_field_responses', 0);
	}

	/**
	 ** Helper to invoke the private _authorize method.
	 **/
	private function invokeAuthorize(Request $req, string $perm)
	{
		$req->setUserResolver(fn () => $this->user);
		$rm = new ReflectionMethod(FormBuilderController::class, '_authorize');
		$rm->setAccessible(true);
		return $rm->invoke($this->controller, $req, $perm);
	}
}
