<?php

namespace Tests\Unit\Models;

use App\Models\CustomQuestion;
use Tests\TestCase;

class CustomQuestionTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The static $isRequired array must match
	 ** the declared structure exactly.
	 **/
	public function is_required_array_matches_expected(): void
	{
		$expected = [
			'yes' => 'Yes',
			'no'  => 'No',
		];

		$this->assertSame($expected, CustomQuestion::$isRequired);
	}

	/**
	 ** @test
	 *
	 ** The $fillable array must expose only the
	 ** declared fields for mass-assignment.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['question', 'is_required', 'created_by'];

		$this->assertSame($expected, (new CustomQuestion)->getFillable());
	}
}
