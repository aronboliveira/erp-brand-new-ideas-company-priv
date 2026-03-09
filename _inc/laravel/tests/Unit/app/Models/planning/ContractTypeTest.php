<?php

namespace Tests\Unit\app\Models\planning;

use App\Models\ContractType;
use Tests\TestCase;

class ContractTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
/** @test */
public function it_is_instantiable(): void
{
$model = new ContractType();
$this->assertInstanceOf(ContractType::class, $model);
}

/** @test */
public function it_uses_uuid_primary_key(): void
{
$model = new ContractType();
$this->assertFalse($model->getIncrementing());
$this->assertSame('string', $model->getKeyType());
}

/** @test */
public function fillable_contains_expected_fields(): void
{
$fillable = (new ContractType())->getFillable();
$this->assertNotEmpty($fillable);
$this->assertContains('name', $fillable);
$this->assertContains('description', $fillable);
}
}
