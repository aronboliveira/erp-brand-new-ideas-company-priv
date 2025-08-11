<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// allow or deny per-test
		Gate::before(fn () => true);

		// have creatorId() return own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->company = User::factory()->create(['type' => 'company']);
		$this->other  = User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** index_requires_manage_permission_and_lists_documents
	 **/
	public function index_requires_manage_permission_and_lists_documents()
	{
		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('document.index'))
			->assertStatus(302)
			->assertSessionHas('error');

		// with permission
		Gate::before(fn () => true);
		Document::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);

		$resp = $this->actingAs($this->company)
			->get(route('document.index'));
		$resp->assertOk()
			->assertViewIs('document.index')
			->assertViewHas('docs', fn ($docs) => $docs->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** create_requires_permission_and_shows_form
	 **/
	public function create_requires_permission_and_shows_form()
	{
		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('document.create'))
			->assertStatus(302)
			->assertSessionHas('error');

		Gate::before(fn () => true);
		$this->actingAs($this->company)
			->get(route('document.create'))
			->assertOk()
			->assertViewIs('document.create');
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_persists_new_document
	 **/
	public function store_validates_and_persists_new_document()
	{
		$this->actingAs($this->company)
			->post(route('document.store'), [])
			->assertSessionHas('error');

		$payload = ['name' => 'Passport', 'is_required' => true];
		$this->actingAs($this->company)
			->post(route('document.store'), $payload)
			->assertRedirect(route('document.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('documents', [
			'name'       => 'Passport',
			'is_required' => 1,
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show_redirects_to_index
	 **/
	public function show_redirects_to_index()
	{
		$doc = Document::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->actingAs($this->company)
			->get(route('document.show', $doc))
			->assertRedirect(route('document.index'));
	}

	/**
	 ** @test
	 **
	 ** edit_requires_permission_and_owner_and_shows_form
	 **/
	public function edit_requires_permission_and_owner_and_shows_form()
	{
		$doc = Document::factory()->create(['created_by' => $this->company->creatorId()]);

		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->get(route('document.edit', $doc))
			->assertStatus(302)
			->assertSessionHas('error');

		Gate::before(fn () => true);
		// wrong owner
		$this->actingAs($this->other)
			->get(route('document.edit', $doc))
			->assertStatus(302)
			->assertSessionHas('error');

		// success
		$this->actingAs($this->company)
			->get(route('document.edit', $doc))
			->assertOk()
			->assertViewIs('document.edit')
			->assertViewHas('document', $doc);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_updates_document
	 **/
	public function update_validates_and_updates_document()
	{
		$doc = Document::factory()->create([
			'created_by' => $this->company->creatorId(),
			'name'       => 'Old',
			'is_required' => false,
		]);

		// validation fail
		$this->actingAs($this->company)
			->put(route('document.update', $doc), ['name' => ''])
			->assertSessionHas('error');

		// wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->put(route('document.update', $doc), ['name' => 'New', 'is_required' => true])
			->assertStatus(302)
			->assertSessionHas('error');

		// success
		$this->actingAs($this->company)
			->put(route('document.update', $doc), ['name' => 'New', 'is_required' => true])
			->assertRedirect(route('document.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('documents', [
			'id'          => $doc->id,
			'name'        => 'New',
			'is_required' => 1,
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_requires_permission_and_owner_and_deletes
	 **/
	public function destroy_requires_permission_and_owner_and_deletes()
	{
		$doc = Document::factory()->create(['created_by' => $this->company->creatorId()]);

		Gate::before(fn () => false);
		$this->actingAs($this->company)
			->delete(route('document.destroy', $doc))
			->assertStatus(302)
			->assertSessionHas('error');

		Gate::before(fn () => true);
		// wrong owner
		$this->actingAs($this->other)
			->delete(route('document.destroy', $doc))
			->assertStatus(302)
			->assertSessionHas('error');

		// success
		$this->actingAs($this->company)
			->delete(route('document.destroy', $doc))
			->assertRedirect(route('document.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('documents', ['id' => $doc->id]);
	}
}
