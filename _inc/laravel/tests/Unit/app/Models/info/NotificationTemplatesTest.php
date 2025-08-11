<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\NotificationTemplates;

class NotificationTemplatesTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The NotificationTemplates model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['name', 'type', 'slug', 'created_by'];
		$this->assertEquals($expected, (new NotificationTemplates())->getFillable());
	}
}
