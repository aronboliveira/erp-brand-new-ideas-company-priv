<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Lead, LeadCall, User};

class LeadCallTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The LeadCall model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'lead_id', 'subject', 'call_type', 'duration',
			'user_id', 'description', 'call_result'
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
		]);

		$this->assertInstanceOf(User::class, $call->getLeadCallUser);
		$this->assertEquals($user?->id, $call->getLeadCallUser->id);
	}
}
