<?php

namespace Tests\Unit\app\Models\Aug;

use App\Models\LeadEmail;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class LeadEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/** @test */
	public function it_is_instantiable(): void
	{
		$model = new LeadEmail();
		$this->assertInstanceOf(LeadEmail::class, $model);
	}

	/** @test */
	public function it_uses_uuid_primary_key(): void
	{
		$model = new LeadEmail();
		$this->assertFalse($model->getIncrementing());
		$this->assertSame('string', $model->getKeyType());
	}
}
