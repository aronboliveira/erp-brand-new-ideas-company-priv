<?php

namespace Tests\Unit\Models;

use App\Models\FormFieldResponse;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormFieldResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable array must expose only
	 ** the declared fields.
	 **/
	public function fillable_array_is_correct(): void
	{
		$ref     = new \ReflectionClass(FormFieldResponse::class);
		$expected = $ref->getProperty('fillable')->getValue(new FormFieldResponse);

		$this->assertSame($expected, (new FormFieldResponse)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** All BelongsTo relations (form, subjectField,
	 ** nameField, emailField, user, pipeline) must exist.
	 **/
	public function relations_are_belongs_to(): void
	{
		$ffr = new FormFieldResponse;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ffr->form()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ffr->subjectField()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ffr->nameField()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ffr->emailField()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ffr->user()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ffr->pipeline()
		);
	}
}
