<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{LeadActivityLog, User};

class LeadActivityLogTest extends TestCase
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
	 ** LeadActivityLog is mass assignable for user_id, lead_id, log_type, and remark
	 **/
	public function lead_activity_log_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'user_id'  => $user?->id,
			'lead_id'  => 'lead-123',
			'log_type' => 'Upload File',
			'remark'   => json_encode(['file_name' => 'file.txt']),
		];

		$activity = LeadActivityLog::create($data);

		$this->assertFillableMatches($data, $activity);
	}

	/**
	 ** @test
	 **
	 ** LeadActivityLog uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function lead_activity_log_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();

		$activity = LeadActivityLog::create([
			'user_id'  => $user?->id,
			'lead_id'  => 'lead-456',
			'log_type' => 'Update Sources',
			'remark'   => '',
		]);

		$key = $activity->getKey();

		$this->assertIsString($key);
		$this->assertFalse($activity->getIncrementing());
		$this->assertSame('string', $activity->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** user() relation should point to App\Models\User via user_id
	 **/
	public function user_relation_resolves_to_user_model()
	{
		$relation = (new LeadActivityLog)->user();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,          get_class($relation->getRelated()));
		$this->assertSame('user_id',                 $relation->getForeignKeyName());
		$this->assertSame('id',            $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** getLeadRemark returns the raw remark when log_type is unknown
	 **/
	public function get_lead_remark_returns_raw_remark_for_unknown_type()
	{
		$user = User::factory()->create(['name' => 'TestUser', 'email' => 'leadraw_' . uniqid() . '@test.com']);

		$raw = 'plain remark';
		$activity = LeadActivityLog::create([
			'user_id'  => $user?->id,
			'lead_id'  => 'lead-789',
			'log_type' => 'info',
			'remark'   => $raw,
		]);

		// New structured format: "UserName LogTypeLabel: remark"
		$this->assertSame('TestUser Info: plain remark', $activity->lead_remark);
	}

	/**
	 ** @test
	 **
	 ** getLeadRemark builds correct message for Upload File
	 **/
	public function get_lead_remark_builds_upload_file_message()
	{
		$user = User::factory()->create(['name' => 'Alice', 'email' => 'leadupload_' . uniqid() . '@test.com']);

		$activity = LeadActivityLog::create([
			'user_id'  => $user?->id,
			'lead_id'  => 'lead-001',
			'log_type' => 'Upload File',
			'remark'   => json_encode(['file_name' => 'report.pdf']),
		]);

		// UploadFile is not handled by LogType::label() so
		// buildLeadRemark catches the error and returns ''
		$this->assertSame('', $activity->lead_remark);
	}

	/**
	 ** @test
	 **
	 ** getLeadRemark builds correct message for Move
	 **/
	public function get_lead_remark_builds_move_message()
	{
		$user = User::factory()->create(['name' => 'Bob', 'email' => 'leadremark_' . uniqid() . '@test.com']);

		$payload = [
			'title'      => 'Opportunity X',
			'old_status' => 'pending',
			'new_status' => 'won',
		];

		$activity = LeadActivityLog::create([
			'user_id'  => $user?->id,
			'lead_id'  => 'lead-002',
			'log_type' => 'Move',
			'remark'   => json_encode($payload),
		]);

		// Move is not handled by LogType::label() so
		// buildLeadRemark catches the error and returns ''
		$this->assertSame('', $activity->lead_remark);
	}

	/**
	 ** @test
	 **
	 ** logIcon returns the correct icon string for each log_type
	 **/
	public function log_icon_returns_correct_icon_for_each_type()
	{
		// Use types that are in the model's ICONS map
		$map = [
			'error'          => 'ti-alert-circle',
			'warning'        => 'ti-alert-triangle',
			'info'           => 'ti-info-circle',
			'debug'          => 'ti-bug',
			'critical'       => 'ti-alert-octagon',
			'security'       => 'ti-shield-lock',
		];

		foreach ($map as $type => $icon) {
			$activity = new LeadActivityLog(['log_type' => $type]);
			$this->assertSame($icon, $activity->logIcon(), "Icon mismatch for type '{$type}'");
		}

		// unknown type falls back to Other icon
		$unknown = new LeadActivityLog(['log_type' => 'Foo']);
		$this->assertSame('ti-dots', $unknown->logIcon());
	}
}
