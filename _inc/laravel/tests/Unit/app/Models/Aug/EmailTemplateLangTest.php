<?php

namespace Tests\Unit\app\Models\Aug;

use App\Models\EmailTemplateLang;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class EmailTemplateLangTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/** @test */
	public function it_is_instantiable(): void
	{
		$model = new EmailTemplateLang();
		$this->assertInstanceOf(EmailTemplateLang::class, $model);
	}

	/** @test */
	public function it_uses_uuid_primary_key(): void
	{
		$model = new EmailTemplateLang();
		$this->assertFalse($model->getIncrementing());
		$this->assertSame('string', $model->getKeyType());
	}

	/** @test */
	public function fillable_contains_expected_fields(): void
	{
		$fillable = (new EmailTemplateLang())->getFillable();
		$this->assertNotEmpty($fillable);
		$this->assertContains('lang', $fillable);
		$this->assertContains('subject', $fillable);
		$this->assertContains('content', $fillable);
	}
}
