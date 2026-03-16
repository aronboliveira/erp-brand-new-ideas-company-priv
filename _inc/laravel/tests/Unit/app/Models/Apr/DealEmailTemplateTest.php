<?php

namespace Tests\Unit\app\Models\Apr;

use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class DealEmailTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
/** @test */
public function model_class_pending_implementation(): void
{
$this->markTestSkipped("DealEmailTemplate model not yet implemented — stub placeholder.");
}
}
