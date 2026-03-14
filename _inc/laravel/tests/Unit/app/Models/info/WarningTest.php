<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Warning;

class WarningTest extends TestCase
{
	use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	/**
	 ** @test
	 **
	 ** The Warning model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'warning_to',
			'warning_by',
			'warning_date',
			'subject',
			'description',
			'employee_id',
		];
		$this->assertEquals($expected, (new Warning())->getFillable());
	}

	/**
	 ** @test
	 **
         ** warningTo() and warningBy() return BelongsTo to Employee.
         **/
        public function it_defines_warning_relations()
        {
                $warning = new Warning();
                $this->assertInstanceOf(
                        \Illuminate\Database\Eloquent\Relations\HasOne::class,
                        $warning->warningTo()
                );
                $this->assertInstanceOf(
                        \Illuminate\Database\Eloquent\Relations\HasOne::class,
                        $warning->warningBy()
                );
        }

	/**
	 ** @test
	 **
	 ** warning($relation) dynamic relation returns a HasOne to Employee.
	 **/
	public function it_defines_dynamic_warning_relation()
	{
		$warning = new Warning();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$warning->warning('warning_by')
		);
	}
}
