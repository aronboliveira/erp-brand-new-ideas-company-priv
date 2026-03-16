<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\{ChFavorite, User};
use Illuminate\{Foundation\Testing\RefreshDatabase, Support\Str};

use Illuminate\Support\Facades\DB;
class ChFavoriteTest extends TestCase
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
	 ** The fillable property contains only user_id and favorite_id.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['user_id', 'favorite_id'];
		$this->assertEquals($expected, (new ChFavorite())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** A newly created ChFavorite has a UUID primary key.
	 **/
	public function it_generates_a_uuid_as_id()
	{
		$user = User::factory()->create();
		$fav = ChFavorite::create([
			'user_id'     => $user?->id,
			'favorite_id' => $user?->id,
		]);

		$this->assertTrue(Str::isUuid($fav->id));
	}
}
