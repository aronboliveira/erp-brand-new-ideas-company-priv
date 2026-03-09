<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase,
	Support\Facades\Auth
};
use App\Models\{EmailTemplate, User};

class EmailTemplateTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
		// authenticate a user for the template() relation
		$this->user = User::factory()->create();
		Auth::login($this->user);
	}
	/**
	 ** @test
	 **
	 ** The EmailTemplate model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'title',
			'from',
			'slug',
			'description',
			'notification',
			'type',
			'available_from',
			'is_disabled',
			'categories',
			'excluded_plans',
			'rules',
			'available_languages',
			'variables',
			'settings',
			'tags',
			'platforms_available',
			'created_by',
			'updated_by',
		];
		$this->assertEquals($expected, (new EmailTemplate())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** template() defines a HasOne relation on UserEmailTemplate for the current user.
	 **/
	public function template_relation_is_has_one_with_user_scope()
	{
		$relation = (new EmailTemplate())->template();
		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame('template_id', $relation->getForeignKeyName());
		$this->assertSame('id', $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** emailTemplateData() returns and caches the first EmailTemplate.
	 **/
	public function email_template_data_returns_and_caches_first()
	{
		$first = EmailTemplate::factory()->create(['title' => 'One']);
		$second = EmailTemplate::factory()->create(['title' => 'Two']);

		$data1 = EmailTemplate::emailTemplateData();
		$data2 = EmailTemplate::emailTemplateData();

		$this->assertSame($first->id, $data1->id);
		$this->assertTrue($data1->is($data2));
	}
}
