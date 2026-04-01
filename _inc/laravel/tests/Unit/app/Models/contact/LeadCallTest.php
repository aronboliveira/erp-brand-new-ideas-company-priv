<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Lead, LeadCall, User};

class LeadCallTest extends TestCase
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
	 ** The LeadCall model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'user_id',
			'from',
			'to_id',
			'to',
			'from_id',
			'lead_id',
			'subject',
			'call_type',
			'call_datetime',
			'call_duration',
			'duration',
			'description',
			'call_result',
			'notes',
		];
		$this->assertEquals($expected, (new LeadCall())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** getLeadCallUser() returns the correct User model.
	 **/
	public function it_resolves_get_lead_call_user_relationship()
	{
		$user  = User::factory()->create();
		$lead  = Lead::factory()->create();
		$call = LeadCall::create([
			'lead_id'     => $lead->id,
			'subject'     => 'Check-in',
			'call_type'   => 'outbound',
			'duration'    => 120,
			'user_id'     => $user?->id,
			'description' => 'Followed up',
			'call_result' => 'connected',
			'from'        => '+5511999990001',
			'to'          => '+5511999990002',
		]);

		$this->assertInstanceOf(User::class, $call->user);
		$this->assertEquals($user?->id, $call->user->id);
	}
}
