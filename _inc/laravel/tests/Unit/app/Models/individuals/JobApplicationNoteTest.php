<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{JobApplication, JobApplicationNote, Note, User};

class JobApplicationNoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'author',
			'author_id',
			'written_at',
			'application_id',
			'note_created',
			'note',
			'reviewer',
			'reviewed_at',
		];
		$this->assertEquals($expected, (new JobApplicationNote())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** noteCreated() relation returns the Note model referenced by note_created.
	 **/
	public function note_created_relation_returns_note()
	{
		$app = JobApplication::factory()->create();
		$noteRecord = new Note();
		$noteRecord->title = 'Test Note';
		$noteRecord->note  = 'Test body';
		$noteRecord->save();

		$janNote = JobApplicationNote::factory()->create([
			'application_id' => $app->id,
			'note_created'   => $noteRecord->id,
		]);

		$resolved = $janNote->noteCreated()->first();
		$this->assertInstanceOf(Note::class, $resolved);
		$this->assertEquals($noteRecord->id, $resolved->id);
	}
}
