<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{LeadActivityLog, User};

class LeadActivityLogTest extends TestCase
{
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

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $activity->$field);
		}
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

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                 $relation->getForeignKeyName());
		$this->assertSame('user_id',            $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** getLeadRemark returns the raw remark when log_type is unknown
	 **/
	public function get_lead_remark_returns_raw_remark_for_unknown_type()
	{
		$user = User::factory()->create();

		$raw = 'plain remark';
		$activity = LeadActivityLog::create([
			'user_id'  => $user?->id,
			'lead_id'  => 'lead-789',
			'log_type' => 'Nonexistent',
			'remark'   => $raw,
		]);

		$this->assertSame($raw, $activity->getLeadRemark());
	}

	/**
	 ** @test
	 **
	 ** getLeadRemark builds correct message for Upload File
	 **/
	public function get_lead_remark_builds_upload_file_message()
	{
		$user = User::factory()->create(['name' => 'Alice']);

		$activity = LeadActivityLog::create([
			'user_id'  => $user?->id,
			'lead_id'  => 'lead-001',
			'log_type' => 'Upload File',
			'remark'   => json_encode(['file_name' => 'report.pdf']),
		]);

		$expected = "Alice Upload new file <b>report.pdf</b>";
		$this->assertSame($expected, $activity->getLeadRemark());
	}

	/**
	 ** @test
	 **
	 ** getLeadRemark builds correct message for Move
	 **/
	public function get_lead_remark_builds_move_message()
	{
		$user = User::factory()->create(['name' => 'Bob']);

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

		$expected = "Bob Moved the deal <b>Opportunity X</b> from Pending to Won";
		$this->assertSame($expected, $activity->getLeadRemark());
	}

	/**
	 ** @test
	 **
	 ** logIcon returns the correct icon string for each log_type
	 **/
	public function log_icon_returns_correct_icon_for_each_type()
	{
		$map = [
			'Move'               => 'ti-arrows-maximize',
			'Add Product'        => 'ti-layout-grid-add',
			'Upload File'        => 'ti-cloud-upload',
			'Update Sources'     => 'ti-brand-open-source',
			'Create Lead Call'   => 'ti-phone-plus',
			'Create Lead Email'  => 'ti-mail',
		];

		foreach ($map as $type => $icon) {
			$activity = new LeadActivityLog(['log_type' => $type]);
			$this->assertSame($icon, $activity->logIcon());
		}

		// unknown type returns empty string
		$unknown = new LeadActivityLog(['log_type' => 'Foo']);
		$this->assertSame('', $unknown->logIcon());
	}
}
