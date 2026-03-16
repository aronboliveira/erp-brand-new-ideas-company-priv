<?php

namespace Tests\Unit\app\Models\info;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\NotificationTemplateLang;

use Illuminate\Support\Facades\DB;
class NotificationTemplateLangsTest extends TestCase
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
	 ** The NotificationTemplateLang model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['parent_id', 'lang', 'content', 'variables', 'translator', 'translator_id', 'metadata'];
		$this->assertEquals($expected, (new NotificationTemplateLang())->getFillable());
	}
}
