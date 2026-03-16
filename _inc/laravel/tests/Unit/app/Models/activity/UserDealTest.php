<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Deal, User, UserDeal};

use Illuminate\Support\Facades\DB;
class UserDealTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** UserDeal is mass assignable for user_id and deal_id
	 **/
	public function user_deal_is_fillable()
	{
		$user = User::factory()->create();
		$deal = Deal::factory()->create();

		$data = [
			'user_id' => $user?->id,
			'deal_id' => $deal->id,
		];

		$userDeal = UserDeal::create($data);

		$this->assertEquals($user?->id, $userDeal->user_id);
		$this->assertEquals($deal->id, $userDeal->deal_id);
	}

	/**
	 ** @test
	 **
	 ** UserDeal uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function user_deal_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();
		$deal = Deal::factory()->create();

		$userDeal = UserDeal::create([
			'user_id' => $user?->id,
			'deal_id' => $deal->id,
		]);

		$key = $userDeal->getKey();

		$this->assertIsString($key);
		$this->assertFalse($userDeal->getIncrementing());
		$this->assertSame('string', $userDeal->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** deal() relation should point to App\Models\Deal via deal_id
	 **/
	public function deal_relation_resolves_to_deal_model()
	{
		$relation = (new UserDeal)->deal();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Deal::class,            get_class($relation->getRelated()));
		$this->assertSame('deal_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** user() relation should point to App\Models\User via user_id
	 **/
	public function user_relation_resolves_to_user_model()
	{
		$relation = (new UserDeal)->user();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('user_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
