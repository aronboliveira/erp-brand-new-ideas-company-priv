<?php

namespace Tests\Unit\Models;

use App\Models\{EmailTemplate, Project, ProjectEmailTemplate};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class ProjectEmailTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 **
	 ** The $fillable array should contain 'template_id', 'project_id', and 'is_active'.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'code',
			'name',
			'template_id',
			'project_id',
			'is_active',
			'fonts',
			'colors',
			'tags',
		];
		$this->assertSame($expected, (new ProjectEmailTemplate)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** The is_active attribute should be cast to a boolean.
	 **/
	public function is_active_casts_to_boolean(): void
	{
		$model = new ProjectEmailTemplate;

		// Assign numeric 1
		$model->is_active = 1;
		$this->assertTrue(is_bool($model->is_active));
		$this->assertTrue($model->is_active);

		// Assign string "0"
		$model->is_active = "0";
		$this->assertTrue(is_bool($model->is_active));
		$this->assertFalse($model->is_active);

		// Assign boolean true
		$model->is_active = true;
		$this->assertTrue(is_bool($model->is_active));
		$this->assertTrue($model->is_active);

		// Assign null (nullable boolean stays null)
		$model->is_active = null;
		$this->assertNull($model->is_active);
	}

	/**
	 ** @test
	 **
	 ** The template() relationship should return a BelongsTo pointing to EmailTemplate,
	 ** with foreign key 'template_id' and owner key 'id'.
	 **/
	public function template_relationship_is_belongs_to_email_template(): void
	{
		$relation = (new ProjectEmailTemplate)->template();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertEquals('template_id', $relation->getForeignKeyName());
		$this->assertEquals('id', $relation->getOwnerKeyName());
		$this->assertEquals(EmailTemplate::class, get_class($relation->getRelated()));
	}

	/**
	 ** @test
	 **
	 ** The project() relationship should return a BelongsTo pointing to Project,
	 ** with foreign key 'project_id' and owner key 'id'.
	 **/
	public function project_relationship_is_belongs_to_project(): void
	{
		$relation = (new ProjectEmailTemplate)->project();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertEquals('project_id', $relation->getForeignKeyName());
		$this->assertEquals('id', $relation->getOwnerKeyName());
		$this->assertEquals(Project::class, get_class($relation->getRelated()));
	}
}
