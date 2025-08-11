<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{Foundation\Testing\RefreshDatabase, Support\Facades\Auth};
use App\Models\{User, UserEmailTemplate};

class UserEmailTemplateTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		// authenticate a user for the relation scope (though relation does not filter here)
		Auth::login(User::factory()->create());
	}

	/**
	 ** @test
	 **
	 ** The fillable property contains only template_id, user_id, and is_active.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['template_id', 'user_id', 'is_active'];
		$this->assertEquals($expected, (new UserEmailTemplate())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** template() and user() relationships return HasOne instances.
	 **/
	public function it_defines_template_and_user_relationships()
	{
		$model = new UserEmailTemplate();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$model->template()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$model->user()
		);
	}
}
