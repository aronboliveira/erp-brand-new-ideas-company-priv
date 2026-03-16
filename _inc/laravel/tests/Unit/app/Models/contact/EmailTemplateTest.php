<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase,
	Support\Facades\Auth
};
use App\Models\{EmailTemplate, User};

use Illuminate\Support\Facades\DB;
class EmailTemplateTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
		// Clear the static template cache between tests
		$ref = new \ReflectionProperty(EmailTemplate::class, 'templateData');
		$ref->setAccessible(true);
		$ref->setValue(null, null);
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
		// Seed an EmailTemplate record so emailTemplateData() can find one
		\Illuminate\Database\Eloquent\Model::unguard();
		EmailTemplate::create([
			'title'       => 'Test Template',
			'slug'        => 'test_template',
			'from'        => 'noreply@test.com',
			'created_by'  => $this->user->id,
		]);
		\Illuminate\Database\Eloquent\Model::reguard();

		// Clear the static cache so the freshly-seeded record is found
		$ref = new \ReflectionProperty(EmailTemplate::class, 'templateData');
		$ref->setAccessible(true);
		$ref->setValue(null, null);

		$data1 = EmailTemplate::emailTemplateData();
		$data2 = EmailTemplate::emailTemplateData();

		$this->assertNotNull($data1);
		$this->assertSame($data1->id, $data2->id);
		$this->assertTrue($data1->is($data2));
	}
}
