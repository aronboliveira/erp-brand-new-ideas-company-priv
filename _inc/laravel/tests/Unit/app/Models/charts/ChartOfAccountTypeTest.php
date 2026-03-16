<?php

namespace Tests\Unit\app\Models\charts;

use App\Models\ChartOfAccountType;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class ChartOfAccountTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
/** @test */
public function it_is_instantiable(): void
{
$model = new ChartOfAccountType();
$this->assertInstanceOf(ChartOfAccountType::class, $model);
}

/** @test */
public function it_uses_uuid_primary_key(): void
{
$model = new ChartOfAccountType();
$this->assertFalse($model->getIncrementing());
$this->assertSame('string', $model->getKeyType());
}

/** @test */
public function fillable_contains_expected_fields(): void
{
$fillable = (new ChartOfAccountType())->getFillable();
$this->assertNotEmpty($fillable);
$this->assertContains('category', $fillable);
$this->assertContains('description', $fillable);
}
}
