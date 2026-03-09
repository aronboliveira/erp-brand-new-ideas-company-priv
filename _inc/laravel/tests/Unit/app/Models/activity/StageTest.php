<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Http\RedirectResponse,
	Support\Collection
};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Support\Str;
use App\Models\{Deal, Stage, User};

class StageTest extends TestCase
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
	 ** Stage is mass assignable for name, pipeline_id, created_by, and order
	 **/
	public function stage_is_fillable()
	{
		$data = [
			'name'         => 'Initial',
			'pipeline_id'  => 'pipe-1',
			'created_by'   => 'user-1',
			'order'        => 5,
		];

		$stage = Stage::create($data);

		$this->assertFillableMatches($data, $stage);
	}

	/**
	 ** @test
	 **
	 ** Stage uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function stage_uses_uuid_for_primary_key()
	{
		$stage = Stage::factory()->create();

		$key = $stage->getKey();

		$this->assertIsString($key);
		$this->assertFalse($stage->getIncrementing());
		$this->assertSame('string', $stage->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** deals() returns RedirectResponse when not logged in
	 **/
	public function deals_returns_redirect_if_not_logged_in()
	{
		$stage = Stage::factory()->create();
		Auth::logout();

		$result = $stage->deals();
		$this->assertInstanceOf(RedirectResponse::class, $result);
	}

	/**
	 ** @test
	 **
	 ** deals() returns client-associated deals ordered by 'order'
	 **/
	public function deals_returns_client_deals_for_client_user()
	{
		$client = User::factory()->create(['type' => 'client']);
		Auth::login($client);

		$stage = Stage::factory()->create();

		// Create two deals in this stage with different order
		$high = Deal::factory()->create([
			'stage_id'  => $stage->id,
			'order'     => 20,
		]);
		$low = Deal::factory()->create([
			'stage_id'  => $stage->id,
			'order'     => 5,
		]);

		// attach in client_deals pivot
		DB::table('client_deals')->insert([
			['id' => (string) Str::uuid(), 'deal_id' => $high->id, 'client_id' => $client->id],
			['id' => (string) Str::uuid(), 'deal_id' => $low->id,  'client_id' => $client->id],
		]);

		$result = $stage->deals();
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertEquals(
			[$low->id, $high->id],
			$result->pluck('id')->all()
		);
	}

	/**
	 ** @test
	 **
	 ** deals() returns user-associated deals for non-client users ordered by 'order'
	 **/
	public function deals_returns_user_deals_for_non_client_user()
	{
		$user = User::factory()->create(['type' => 'employee']);
		Auth::login($user);

		$stage = Stage::factory()->create();

		$a = Deal::factory()->create([
			'stage_id'  => $stage->id,
			'order'     => 2,
		]);
		$b = Deal::factory()->create([
			'stage_id'  => $stage->id,
			'order'     => 1,
		]);

		DB::table('user_deals')->insert([
			['id' => (string) Str::uuid(), 'deal_id' => $a->id, 'user_id' => $user?->id],
			['id' => (string) Str::uuid(), 'deal_id' => $b->id, 'user_id' => $user?->id],
		]);

		$result = $stage->deals();
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertEquals(
			[$b->id, $a->id],
			$result->pluck('id')->all()
		);
	}
}
