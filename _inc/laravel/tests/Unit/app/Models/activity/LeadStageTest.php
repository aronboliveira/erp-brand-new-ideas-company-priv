<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Collection, Facades\Auth};
use Illuminate\Http\RedirectResponse;
use App\Models\{Lead, LeadStage, User};

use Illuminate\Support\Facades\DB;
class LeadStageTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** lead() returns a RedirectResponse when no user is logged in
	 **/
	public function lead_returns_redirect_if_not_logged_in()
	{
		$stage = LeadStage::create([
			'name'         => 'Test Stage',
			'pipeline_id'  => 'pipe-1',
			'created_by'   => 'creator-1',
			'order'        => 1,
		]);

		Auth::logout();
		$result = $stage->lead();

		$this->assertInstanceOf(RedirectResponse::class, $result);
	}

	/**
	 ** @test
	 **
	 ** lead() returns leads for a company user, ordered by the "order" column
	 **/
	public function lead_returns_leads_for_company_user()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		$stage = LeadStage::create([
			'name'         => 'Stage A',
			'pipeline_id'  => 'pipe-2',
			'created_by'   => $company->id,
			'order'        => 10,
		]);

		$leadHigh = Lead::create([
			'name'         => 'High Order',
			'email'        => 'high@example.com',
			'phone'        => '000',
			'subject'      => 'S',
			'user_id'      => $company->id,
			'pipeline_id'  => 'pipe-2',
			'stage_id'     => $stage->id,
			'sources'      => '',
			'products'     => '',
			'notes'        => '',
			'labels'       => '',
			'order'        => 20,
			'created_by'   => $company->id,
			'is_active'    => true,
			'is_converted' => false,
			'date'         => '2025-05-24',
		]);

		$leadLow = Lead::create([
			'name'         => 'Low Order',
			'email'        => 'low@example.com',
			'phone'        => '111',
			'subject'      => 'T',
			'user_id'      => $company->id,
			'pipeline_id'  => 'pipe-2',
			'stage_id'     => $stage->id,
			'sources'      => '',
			'products'     => '',
			'notes'        => '',
			'labels'       => '',
			'order'        => 5,
			'created_by'   => $company->id,
			'is_active'    => true,
			'is_converted' => false,
			'date'         => '2025-05-24',
		]);

		$result = $stage->lead();

		$this->assertInstanceOf(Collection::class, $result);
		$this->assertCount(2, $result);
		$this->assertEquals($leadLow->id, $result->first()->id);
		$this->assertEquals($leadHigh->id, $result->last()->id);
	}

	/**
	 ** @test
	 **
	 ** lead() returns leads via the user_leads pivot for non-company users, ordered by leads.order
	 **/
	public function lead_returns_pivoted_leads_for_non_company_user()
	{
		$employee = User::factory()->create(['type' => 'employee']);
		Auth::login($employee);

		$stage = LeadStage::create([
			'name'         => 'Stage B',
			'pipeline_id'  => 'pipe-3',
			'created_by'   => 'creator-2',
			'order'        => 1,
		]);

		$leadA = Lead::create([
			'name'         => 'A',
			'email'        => 'a@b.com',
			'phone'        => '222',
			'subject'      => '',
			'user_id'      => $employee->id,
			'pipeline_id'  => 'pipe-3',
			'stage_id'     => $stage->id,
			'sources'      => '',
			'products'     => '',
			'notes'        => '',
			'labels'       => '',
			'order'        => 2,
			'created_by'   => 'x',
			'is_active'    => true,
			'is_converted' => false,
			'date'         => '2025-05-24',
		]);

		$leadB = Lead::create([
			'name'         => 'B',
			'email'        => 'b@c.com',
			'phone'        => '333',
			'subject'      => '',
			'user_id'      => $employee->id,
			'pipeline_id'  => 'pipe-3',
			'stage_id'     => $stage->id,
			'sources'      => '',
			'products'     => '',
			'notes'        => '',
			'labels'       => '',
			'order'        => 1,
			'created_by'   => 'y',
			'is_active'    => true,
			'is_converted' => false,
			'date'         => '2025-05-24',
		]);

		// attach via pivot — provide explicit UUIDs for char(36) PK
		\Illuminate\Support\Facades\DB::table('user_leads')->insert([
			['id' => \Illuminate\Support\Str::uuid()->toString(), 'user_id' => $employee->id, 'lead_id' => $leadA->id, 'created_at' => now(), 'updated_at' => now()],
			['id' => \Illuminate\Support\Str::uuid()->toString(), 'user_id' => $employee->id, 'lead_id' => $leadB->id, 'created_at' => now(), 'updated_at' => now()],
		]);

		$result = $stage->lead();

		$this->assertInstanceOf(Collection::class, $result);
		$this->assertCount(2, $result);
		$this->assertEquals($leadB->id, $result->first()->id);
		$this->assertEquals($leadA->id, $result->last()->id);
	}
}
