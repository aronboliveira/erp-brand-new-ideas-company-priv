<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Support;
use App\Models\User;
use App\Models\SupportReply;

class SupportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Support is mass assignable for all fillable fields
	 **/
	public function support_is_fillable()
	{
		$user = User::factory()->create();
		$data = [
			'subject'        => 'Help needed',
			'user'           => $user?->id,
			'priority'       => 'High',
			'end_date'       => '2025-05-31',
			'ticket_code'    => 'TCK-001',
			'ticket_created' => $user?->id,
			'status'         => 'Open',
			'created_by'     => $user?->id,
			'attachment'     => '/path/to/file.txt',
			'description'    => 'Detailed description',
		];

		$support = Support::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $support->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Support uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function support_uses_uuid_for_primary_key()
	{
		$support = Support::factory()->create();

		$key = $support->getKey();

		$this->assertIsString($key);
		$this->assertFalse($support->getIncrementing());
		$this->assertSame('string', $support->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** static priority array contains expected levels
	 **/
	public function priority_static_property_contains_expected_levels()
	{
		$expected = ['Low', 'Medium', 'High', 'Critical'];
		$this->assertSame($expected, Support::$priority);
	}

	/**
	 ** @test
	 **
	 ** static status array keys and values match expected
	 **/
	public function status_static_method_returns_expected_translations()
	{
		$arr = Support::status();
		$this->assertArrayHasKey('Open', $arr);
		$this->assertArrayHasKey('Close', $arr);
		$this->assertArrayHasKey('On Hold', $arr);
		// Assuming default locale returns same strings
		$this->assertSame('Open',    $arr['Open']);
		$this->assertSame('Close',   $arr['Close']);
		$this->assertSame('On Hold', $arr['On Hold']);
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to User model via ticket_created
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new Support)->createdBy();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('ticket_created',    $relation->getForeignKeyName());
		$this->assertSame('id',                $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** assignUser() relation should point to User model via user
	 **/
	public function assign_user_relation_resolves_to_user_model()
	{
		$relation = (new Support)->assignUser();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('user',              $relation->getForeignKeyName());
		$this->assertSame('id',                $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** replyUnread() counts only other users' unread replies when current user is Employee
	 **/
	public function reply_unread_counts_only_other_users_for_employee()
	{
		$employee = User::factory()->create(['type' => 'Employee']);
		Auth::login($employee);

		$support = Support::factory()->create([
			'user' => $employee->id,
			'ticket_created' => $employee->id,
		]);

		// One unread from another user
		SupportReply::factory()->create([
			'support_id' => $support->id,
			'user'       => $employee->id + 1,
			'is_read'    => 0,
		]);
		// One unread from self (should be excluded)
		SupportReply::factory()->create([
			'support_id' => $support->id,
			'user'       => $employee->id,
			'is_read'    => 0,
		]);
		// One already read
		SupportReply::factory()->create([
			'support_id' => $support->id,
			'user'       => $employee->id + 2,
			'is_read'    => 1,
		]);

		$this->assertSame(1, $support->replyUnread());
	}

	/**
	 ** @test
	 **
	 ** replyUnread() counts all unread replies for non-Employee users
	 **/
	public function reply_unread_counts_all_unread_for_non_employee()
	{
		$user = User::factory()->create(['type' => 'Admin']);
		Auth::login($user);

		$support = Support::factory()->create([
			'user' => $user?->id,
			'ticket_created' => $user?->id,
		]);

		// Two unread replies
		SupportReply::factory()->count(2)->create([
			'support_id' => $support->id,
			'is_read'    => 0,
		]);
		// One read
		SupportReply::factory()->create([
			'support_id' => $support->id,
			'is_read'    => 1,
		]);

		$this->assertSame(2, $support->replyUnread());
	}
}
