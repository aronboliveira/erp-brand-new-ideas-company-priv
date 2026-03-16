<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\UserContact;

use Illuminate\Support\Facades\DB;
class UserContactTest extends TestCase
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
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'user_id',
			'parent_id',
			'name',
			'company',
			'role',
			'email',
			'phone',
			'email_id',
			'address',
			'social_media',
			'notes',
			'avatar',
			'birthday',
			'is_blocked',
			'is_muted',
			'is_favorite',
			'last_contacted_at',
			'tags',
			'templates',
			'updated_by',
		];
		$this->assertEquals($expected, (new UserContact())->getFillable());
	}
}
