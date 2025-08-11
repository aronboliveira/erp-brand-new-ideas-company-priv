<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Facades\Auth, Str};
use App\Models\{Email, User};

class EmailTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		// create and authenticate a user for the CREATED_BY_FIELD
		$this->user = User::factory()->create();
		Auth::login($this->user);
	}

	/**
	 ** @test
	 **
	 ** The Email model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'id', 'title', 'description', 'notes',
			'email_created_by', 'document_url',
			'attachments', 'email', 'module_type', 'module_id'
		];
		$this->assertEquals($expected, (new Email())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** The Email model uses UUIDs and sets the created_by field on creating.
	 **/
	public function it_generates_uuid_and_sets_created_by_on_create()
	{
		// create without specifying id or email_created_by
		$email = Email::create([
			// omit 'id' and 'email_created_by'
			'title'       => 'Test Title',
			'description' => 'Test Description',
			'notes'       => 'Test Notes',
			'document_url' => null,
			'attachments' => null,
			'email'       => 'foo@example.com',
			'module_type' => 'type',
			'module_id'   => 1,
		]);

		// primary key is a UUID
		$this->assertTrue(Str::isUuid($email->id));
		// created_by was set to authenticated user
		$this->assertEquals($this->user->id, $email->email_created_by);
	}

	/**
	 ** @test
	 **
	 ** The model is non-incrementing with string key type and correct table name.
	 **/
	public function it_has_non_incrementing_string_key_and_correct_table()
	{
		$model = new Email();
		$this->assertFalse($model->incrementing);
		$this->assertSame('string', $model->getKeyType());
		$this->assertSame('email', $model->getTable());
	}

	/**
	 ** @test
	 **
	 ** The default attributes are set on a fresh instance.
	 **/
	public function it_has_expected_default_attributes()
	{
		$model = new Email();
		$this->assertSame('NO GIVEN TITLE', $model->title);
		$this->assertSame('NO GIVEN DESCRIPTION', $model->description);
		$this->assertSame('No notes taken', $model->notes);
	}

	/**
	 ** @test
	 **
	 ** The global scope orders by created_at descending.
	 **/
	public function it_applies_global_scope_ordering()
	{
		// create two records a moment apart
		$first = Email::create([
			'title' => 'First', 'description' => '', 'notes' => '',
			'document_url' => null, 'attachments' => null, 'email' => 'a@a.com', 'module_type' => '', 'module_id' => 0
		]);
		sleep(1);
		$second = Email::create([
			'title' => 'Second', 'description' => '', 'notes' => '',
			'document_url' => null, 'attachments' => null, 'email' => 'b@b.com', 'module_type' => '', 'module_id' => 0
		]);

		$ids = Email::all()->pluck('id')->all();
		$this->assertSame([$second->id, $first->id], $ids);
	}
}
