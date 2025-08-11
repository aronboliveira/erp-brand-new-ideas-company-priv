<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{JobApplication, JobApplicationNote, User};

class JobApplicationNoteTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'application_id', 'note_created', 'note', 'created_by'
		];
		$this->assertEquals($expected, (new JobApplicationNote())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** noteCreated() relation returns the User who created the note.
	 **/
	public function note_created_relation_returns_user()
	{
		$user = User::factory()->create();
		$app = JobApplication::factory()->create();
		$note = JobApplicationNote::factory()->create([
			'application_id' => $app->id,
			'note_created'   => $user?->id
		]);

		$this->assertInstanceOf(User::class, $note->noteCreated);
		$this->assertEquals($user?->id, $note->noteCreated->id);
	}
}
