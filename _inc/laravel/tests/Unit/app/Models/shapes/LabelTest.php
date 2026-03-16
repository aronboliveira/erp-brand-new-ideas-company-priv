<?php

namespace Tests\Unit\Models;

use App\Models\Label;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class LabelTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	/**
	 ** @test
	 **
	 ** The model should use UUID (non-incrementing, string key).
	 **/
	public function primary_key_is_uuid_string(): void
	{
		$label = new Label;
		$this->assertFalse($label->getIncrementing());
		$this->assertSame('string', $label->getKeyType());
	}

	/**
	 ** @test
	 **
	 ** The $fillable array must include name, color, pipeline_id, created_by.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'name',
			'color',
			'pipeline_id',
			'created_by',
		];
		$this->assertSame($expected, (new Label)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** The static $colors array must remain unchanged.
	 **/
	public function colors_array_is_intact(): void
	{
		$expected = [
			'primary',
			'secondary',
			'danger',
			'warning',
			'info',
			'success',
		];
		$this->assertSame($expected, Label::$colors);
	}
}
