<?php

namespace Tests\Unit\app\Models\Aug;

use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class UserEmailTemplateLangTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/** @test */
	public function model_class_pending_implementation(): void
	{
		$this->markTestSkipped("UserEmailTemplateLang model not yet implemented — stub placeholder.");
	}
}
