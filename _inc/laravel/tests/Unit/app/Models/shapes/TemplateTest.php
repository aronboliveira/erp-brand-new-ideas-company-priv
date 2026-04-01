<?php

namespace Tests\Unit\Models;

use App\Models\Template;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 **
	 ** The $fillable array must include the listed fields.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'template_name',
			'prompt',
			'module',
			'field_json',
			'is_tone',
		];

		$this->assertSame($expected, (new Template)->getFillable());
	}
}
