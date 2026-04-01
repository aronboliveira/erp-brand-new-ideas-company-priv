<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Lead, LeadEmail};

class LeadEmailTest extends TestCase
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
	 ** The LeadEmail model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'lead_id',
			'from',
			'to',
			'subject',
			'counter',
			'is_follow_up',
			'description',
			'attachments',
			'attachment_filter_rules',
			'created_by',
		];
		$this->assertEquals($expected, (new LeadEmail())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** lead() relationship returns the correct Lead model.
	 **/
	public function it_resolves_lead_relationship()
	{
		$lead = Lead::factory()->create();
		$email = LeadEmail::create([
			'lead_id'     => $lead->id,
			'from'        => 'sender@example.com',
			'to'          => 'test@example.com',
			'subject'     => 'Hello',
			'description' => 'Message body',
		]);

		$this->assertEquals($lead->id, $email->lead->id);
	}

	/**
	 ** @test
	 **
	 ** lead relation is eager loaded by default.
	 **/
	public function lead_relation_is_eager_loaded()
	{
		$lead = Lead::factory()->create();
		LeadEmail::create([
			'lead_id'     => $lead->id,
			'from'        => 'bar@baz.com',
			'to'          => 'foo@bar.com',
			'subject'     => 'Subj',
			'description' => 'Desc',
		]);

		$first = LeadEmail::first();
		$this->assertTrue($first->relationLoaded('lead'));
	}
}
