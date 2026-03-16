<?php

namespace Tests\Unit\app\Models\charts;

use App\Models\ChartOfAccountSubType;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class ChartOfAccountSubTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
/** @test */
public function it_is_instantiable(): void
{
$model = new ChartOfAccountSubType();
$this->assertInstanceOf(ChartOfAccountSubType::class, $model);
}

/** @test */
public function it_uses_uuid_primary_key(): void
{
$model = new ChartOfAccountSubType();
$this->assertFalse($model->getIncrementing());
$this->assertSame('string', $model->getKeyType());
}

/** @test */
public function fillable_contains_expected_fields(): void
{
$fillable = (new ChartOfAccountSubType())->getFillable();
$this->assertNotEmpty($fillable);
$this->assertContains('description', $fillable);
}
}
