<?php

namespace Tests\Feature;

use App\Models\DocumentUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentUploadControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Bypass all permission checks
		Gate::before(fn () => true);

		// Ensure creatorId() returns the user's own ID
		User::macro(
			'creatorId',
			/**
			 ** @return int|string
			 ** @this \App\Models\User
			 **
			 ** Return the current user's creator ID (their own id).
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);
	}

	/**
	 ** @test
	 **
	 ** index() should display only documents created by the current company user.
	 **/
	public function index_displays_only_company_documents_for_company_user()
	{
		$company = User::factory()->create(['type' => 'company']);
		$other  = User::factory()->create(['type' => 'company']);

		DocumentUpload::factory()->create(['created_by' => $company->creatorId()]);
		DocumentUpload::factory()->create(['created_by' => $other->creatorId()]);

		$response = $this->actingAs($company)
			->get(route('document-upload.index'));

		$response->assertStatus(200)
			->assertViewIs('documentUpload.index')
			->assertViewHas(
				'documents',
				fn ($docs) =>
				$docs->pluck('created_by')->unique()->all() === [$company->creatorId()]
			);
	}

	/**
	 ** @test
	 **
	 ** create() should show the upload form with the list of roles.
	 **/
	public function create_shows_roles_list()
	{
		$user = User::factory()->create();
		Role::create([
			'name'       => 'Manager',
			'guard_name' => 'web',
			'created_by' => $user?->creatorId()
		]);

		$response = $this->actingAs($user)
			->get(route('document-upload.create'));

		$response->assertStatus(200)
			->assertViewIs('documentUpload.create')
			->assertViewHas(
				'roles',
				fn ($roles) => array_key_exists(Role::first()->id, $roles)
			);
	}

	/**
	 ** @test
	 **
	 ** store() should save a new document record and redirect to index.
	 **/
	public function store_creates_document_and_redirects()
	{
		$user = User::factory()->create();

		$payload = [
			'name'        => 'Test Doc',
			'role'        => '0',
			'description' => 'A description',
		];

		$response = $this->actingAs($user)
			->post(route('document-upload.store'), $payload);

		$response->assertRedirect(route('document-upload.index'));
		$this->assertDatabaseHas('document_uploads', [
			'name'        => 'Test Doc',
			'description' => 'A description',
			'created_by'  => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit() should display the existing document and roles for editing.
	 **/
	public function edit_displays_existing_document_and_roles()
	{
		$user = User::factory()->create();
		$doc = DocumentUpload::factory()->create(['created_by' => $user?->creatorId()]);
		Role::create([
			'name'       => 'Manager',
			'guard_name' => 'web',
			'created_by' => $user?->creatorId()
		]);

		$response = $this->actingAs($user)
			->get(route('document-upload.edit', $doc->id));

		$response->assertStatus(200)
			->assertViewIs('documentUpload.edit')
			->assertViewHasAll(['roles', 'doc']);
	}

	/**
	 ** @test
	 **
	 ** update() should apply changes to the document and redirect to index.
	 **/
	public function update_changes_document_and_redirects()
	{
		$user = User::factory()->create();
		$doc = DocumentUpload::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Old Name',
		]);

		$payload = [
			'name'        => 'New Name',
			'role'        => '0',
			'description' => 'Updated desc',
		];

		$response = $this->actingAs($user)
			->put(route('document-upload.update', $doc->id), $payload);

		$response->assertRedirect(route('document-upload.index'));
		$this->assertDatabaseHas('document_uploads', [
			'id'          => $doc->id,
			'name'        => 'New Name',
			'description' => 'Updated desc',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy() should delete the document and redirect to index.
	 **/
	public function destroy_deletes_document_and_redirects()
	{
		$user = User::factory()->create();
		$doc = DocumentUpload::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->delete(route('document-upload.destroy', $doc->id));

		$response->assertRedirect(route('document-upload.index'));
		$this->assertDatabaseMissing('document_uploads', ['id' => $doc->id]);
	}
}
