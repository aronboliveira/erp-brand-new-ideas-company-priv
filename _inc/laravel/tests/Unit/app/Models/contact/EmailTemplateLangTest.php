<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EmailTemplateLang;

use Illuminate\Support\Facades\DB;
class EmailTemplateLangTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The EmailTemplateLang model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'parent_id',
			'lang',
			'subject',
			'content',
			'translator',
			'translator_id',
			'variables',
			'metadata',
		];
		$this->assertEquals($expected, (new EmailTemplateLang())->getFillable());
	}
}
