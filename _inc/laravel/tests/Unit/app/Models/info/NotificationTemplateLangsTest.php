<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\NotificationTemplateLangs;

class NotificationTemplateLangsTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The NotificationTemplateLangs model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['parent_id', 'lang', 'content', 'variables', 'created_by'];
		$this->assertEquals($expected, (new NotificationTemplateLangs())->getFillable());
	}
}
