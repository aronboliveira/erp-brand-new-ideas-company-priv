<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{SupportReply, User};

class SupportReplyTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The SupportReply model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'support_id', 'user', 'description', 'created_by', 'is_read'
		];
		$this->assertEquals($expected, (new SupportReply())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** users() relationship returns the correct User model.
	 **/
	public function it_resolves_users_relationship()
	{
		$user    = User::factory()->create();
		$reply = SupportReply::create([
			'support_id'  => 1,
			'user'        => $user?->id,
			'description' => 'Helped them out',
			'created_by'  => $user?->id,
			'is_read'     => false,
		]);

		$this->assertInstanceOf(User::class, $reply->users);
		$this->assertEquals($user?->id, $reply->users->id);
	}
}
