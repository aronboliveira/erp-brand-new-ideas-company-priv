<?php

namespace Tests\Unit\app\Models\Apr;

use App\Models\Email;
use Tests\TestCase;

class EmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/** @test */
	public function it_is_instantiable(): void
	{
		$model = new Email();
		$this->assertInstanceOf(Email::class, $model);
	}

	/** @test */
	public function it_uses_uuid_primary_key(): void
	{
		$model = new Email();
		$this->assertFalse($model->getIncrementing());
		$this->assertSame('string', $model->getKeyType());
	}
}
