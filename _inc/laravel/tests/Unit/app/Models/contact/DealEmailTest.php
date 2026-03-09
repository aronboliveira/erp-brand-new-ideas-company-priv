<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Deal, DealEmail};

class DealEmailTest extends TestCase
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
	 ** The fillable property includes deal_id, to, subject, and description.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'deal_id',
			'user_id',
			'from',
			'to',
			'subject',
			'description',
			'notes',
			'counter',
			'is_follow_up',
			'attachments',
			'attachment_filter_rules',
			'created_by',
		];
		$this->assertEquals($expected, (new DealEmail())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** The deal() relationship returns the associated Deal.
	 **/
	public function it_resolves_deal_relationship()
	{
		$deal = Deal::factory()->create();

		$email = DealEmail::create([
			'deal_id'     => $deal->id,
			'to'          => 'user@example.com',
			'from'        => 'sender@example.com',
			'subject'     => 'Test',
			'description' => 'Details',
		]);

		$this->assertEquals($deal->id, $email->deal->id);
	}

	/**
	 ** @test
	 **
	 ** The deal relationship is eager loaded by default.
	 **/
	public function deal_is_eager_loaded()
	{
		$deal = Deal::factory()->create();

		$email = DealEmail::create([
			'deal_id'     => $deal->id,
			'to'          => 'test@example.com',
			'from'        => 'sender@example.com',
			'subject'     => 'Subject',
			'description' => 'Desc',
		]);

		$fresh = DealEmail::first();
		$this->assertTrue($fresh->relationLoaded('deal'));
	}
}
