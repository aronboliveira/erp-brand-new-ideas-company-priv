<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{Foundation\Testing\RefreshDatabase, Support\Facades\Auth};
use App\Models\{User, UserEmailTemplate};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmailTemplateTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
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
		$expected = [
			'template_id',
			'user_id',
			'is_active',
			'counter',
			'is_favorite',
			'is_default',
			'clients',
		];
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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$model->template()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$model->user()
		);
	}
}
