<?php

namespace Tests\Feature;

use App\Models\{CustomQuestion, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CustomQuestionControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permission checks by default
		Gate::before(fn () => true);

		// make creatorId() return own ID
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
		$this->company = User::factory()->create(['type' => 'company']);
		$this->other  = User::factory()->create(['type' => 'company']);

		$this->actingAs($this->company);
	}

	/**
	 ** @test
	 **
	 ** index_lists_questions_for_owner_only
	 **
	 ** Only questions created_by the user appear; permission denied otherwise.
	 **/
	public function index_lists_questions_for_owner_only()
	{
		CustomQuestion::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);
		CustomQuestion::factory()->create(['created_by' => $this->other->creatorId()]);

		$response = $this->get(route('customQuestion.index'));
		$response->assertOk()
			->assertViewIs('customQuestion.index')
			->assertViewHas('questions', fn ($q) => $q->count() === 2);

		// deny permission
		Gate::before(fn () => false);
		$this->get(route('customQuestion.index'))->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form_when_authorized
	 **
	 ** Authorized users see the create view.
	 **/
	public function create_displays_form_when_authorized()
	{
		$response = $this->get(route('customQuestion.create'));
		$response->assertOk()
			->assertViewIs('customQuestion.create')
			->assertViewHas('isRequired');

		// deny
		Gate::before(fn () => false);
		$this->get(route('customQuestion.create'))->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates_question
	 **
	 ** Missing 'question' errors; valid input persists.
	 **/
	public function store_validates_and_creates_question()
	{
		// validation fail
		$this->post(route('customQuestion.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		Gate::before(fn () => true);
		$payload = ['question' => 'What is your favorite color?', 'is_required' => 'yes'];
		$this->post(route('customQuestion.store'), $payload)
			->assertRedirect(route('customQuestion.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('custom_questions', [
			'question'   => 'What is your favorite color?',
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show_displays_view_for_authorized
	 **
	 ** Authorized users can view; denied otherwise.
	 **/
	public function show_displays_view_for_authorized()
	{
		$q = CustomQuestion::factory()->create(['created_by' => $this->company->creatorId()]);

		// view
		$this->get(route('customQuestion.show', $q))
			->assertOk()
			->assertViewIs('customQuestion.show')
			->assertViewHas('customQuestion', fn ($cq) => $cq->id === $q->id);

		// deny permission
		Gate::before(fn () => false);
		$this->get(route('customQuestion.show', $q))->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** edit_enforces_permission_and_ownership
	 **
	 ** Only owner with permission sees edit form.
	 **/
	public function edit_enforces_permission_and_ownership()
	{
		$q = CustomQuestion::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->get(route('customQuestion.edit', $q))->assertStatus(403);

		// restore, wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->get(route('customQuestion.edit', $q))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->get(route('customQuestion.edit', $q))
			->assertOk()
			->assertViewIs('customQuestion.edit')
			->assertViewHasAll(['customQuestion', 'isRequired']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves_changes
	 **
	 ** Invalid input errors; valid input updates record.
	 **/
	public function update_validates_and_saves_changes()
	{
		$q = CustomQuestion::factory()->create([
			'question'   => 'Old?',
			'created_by' => $this->company->creatorId()
		]);

		// validation fail
		$this->put(route('customQuestion.update', $q), ['question' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// success
		$payload = ['question' => 'New question?', 'is_required' => 'no'];
		$this->put(route('customQuestion.update', $q), $payload)
			->assertRedirect(route('customQuestion.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('custom_questions', [
			'id'       => $q->id,
			'question' => 'New question?',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_enforces_permission_and_deletes
	 **
	 ** Only owner with permission may delete.
	 **/
	public function destroy_enforces_permission_and_deletes()
	{
		$q = CustomQuestion::factory()->create(['created_by' => $this->company->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->delete(route('customQuestion.destroy', $q))->assertStatus(403);

		// restore, wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->delete(route('customQuestion.destroy', $q))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->delete(route('customQuestion.destroy', $q))
			->assertRedirect(route('customQuestion.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('custom_questions', ['id' => $q->id]);
	}
}
