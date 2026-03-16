<?php

namespace Tests\Unit\app\Models\Apr;

use App\Models\EmailTemplate;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class EmailTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/** @test */
	public function it_is_instantiable(): void
	{
		$model = new EmailTemplate();
		$this->assertInstanceOf(EmailTemplate::class, $model);
	}

	/** @test */
	public function it_uses_uuid_primary_key(): void
	{
		$model = new EmailTemplate();
		$this->assertFalse($model->getIncrementing());
		$this->assertSame('string', $model->getKeyType());
	}
}
