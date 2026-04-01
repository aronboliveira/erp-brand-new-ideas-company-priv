<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase
};
use App\Models\LoginDetail;

class LoginDetailTest extends TestCase
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
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = ['user_id', 'ip', 'date', 'Details', 'created_by'];
		$this->assertEquals($expected, (new LoginDetail())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation returns a HasOne to the User model.
	 **/
	public function created_by_relation_returns_hasone()
	{
		$relation = (new LoginDetail())->createdBy();
		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertEquals('id', $relation->getForeignKeyName());
		$this->assertEquals('ticket_created', $relation->getLocalKeyName());
	}
}
