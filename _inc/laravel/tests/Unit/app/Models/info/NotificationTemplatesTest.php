<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\NotificationTemplate;

use Illuminate\Support\Facades\DB;
class NotificationTemplatesTest extends TestCase
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
	 ** The NotificationTemplates model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'name',
			'slug',
			'type',
			'description',
			'available_from',
			'is_disabled',
			'categories',
			'excluded_plans',
			'rules',
			'available_languages',
			'created_by',
			'updated_by',
		];
		$this->assertEquals($expected, (new NotificationTemplate())->getFillable());
	}
}
