<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmailTemplateLang;

class EmailTemplateLangTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The EmailTemplateLang model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['parent_id', 'lang', 'subject', 'content'];
		$this->assertEquals($expected, (new EmailTemplateLang())->getFillable());
	}
}
