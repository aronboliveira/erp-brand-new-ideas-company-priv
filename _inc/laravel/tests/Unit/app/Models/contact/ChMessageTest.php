<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{ChMessage, User};

class ChMessageTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The fillable property includes from_id, to_id, message, and seen.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['from_id', 'to_id', 'body', 'seen'];
		$this->assertEquals($expected, (new ChMessage())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** The from() relationship returns the correct user.
	 **/
	public function it_resolves_from_relationship()
	{
		$sender  = User::factory()->create();
		$receiver = User::factory()->create();

		$msg = ChMessage::create([
			'from_id' => $sender->id,
			'to_id'   => $receiver->id,
			'message' => 'Hello',
			'seen'    => false,
		]);

		$this->assertInstanceOf(User::class, $msg->from);
		$this->assertEquals($sender->id, $msg->from->id);
	}

	/**
	 ** @test
	 **
	 ** The to() relationship returns the correct user.
	 **/
	public function it_resolves_to_relationship()
	{
		$sender  = User::factory()->create();
		$receiver = User::factory()->create();

		$msg = ChMessage::create([
			'from_id' => $sender->id,
			'to_id'   => $receiver->id,
			'message' => 'Hello again',
			'seen'    => true,
		]);

		$this->assertInstanceOf(User::class, $msg->to);
		$this->assertEquals($receiver->id, $msg->to->id);
	}
}
