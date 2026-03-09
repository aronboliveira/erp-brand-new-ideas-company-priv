<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\AwardType;

class AwardTypeTest extends TestCase
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
	 ** AwardType is mass assignable for name and created_by
	 **/
	public function award_type_is_fillable()
	{
		$data = [
			'name'       => 'Holiday Bonus',
		];

		$awardType = AwardType::create($data);

		$this->assertEquals('Holiday Bonus', $awardType->name);
	}

	/**
	 ** @test
	 **
	 ** AwardType uses UUIDs for primary key: string and non-incrementing
	 **/
	public function award_type_uses_uuid_for_primary_key()
	{
		$awardType = AwardType::create([
			'name'       => 'Test Type',
		]);

		$key = $awardType->getKey();

		$this->assertIsString($key);
		$this->assertFalse($awardType->getIncrementing());
		$this->assertSame('string', $awardType->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
