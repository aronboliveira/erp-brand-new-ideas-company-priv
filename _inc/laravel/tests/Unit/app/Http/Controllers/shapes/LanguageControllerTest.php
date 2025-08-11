<?php

namespace Tests\Unit;

use App\Http\Controllers\LanguageController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class LanguageControllerTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** buildArray should serialize a flat array of strings into a PHP array syntax string.
	 **/
	public function build_array_handles_flat_arrays()
	{
		$controller = new LanguageController();
		$method = new ReflectionMethod(LanguageController::class, 'buildArray');
		$method->setAccessible(true);

		$input = ['hello' => 'world', 'foo' => 'bar'];
		$expected = "'hello'=>'world','foo'=>'bar',";

		$this->assertSame(
			$expected,
			$method->invoke($controller, $input)
		);
	}

	/**
	 ** @test
	 **
	 ** buildArray should correctly handle nested arrays recursively.
	 **/
	public function build_array_handles_nested_arrays()
	{
		$controller = new LanguageController();
		$method = new ReflectionMethod(LanguageController::class, 'buildArray');
		$method->setAccessible(true);

		$input = [
			'a' => '1',
			'nested' => [
				'b' => '2',
				'inner' => [
					'c' => '3'
				],
			],
			'd' => '4'
		];
		// Expect: "'a'=>'1','nested'=>['b'=>'2','inner'=>['c'=>'3',],],'d'=>'4',"
		$expected = "'a'=>'1','nested'=>['b'=>'2','inner'=>['c'=>'3',],],'d'=>'4',";

		$this->assertSame(
			$expected,
			$method->invoke($controller, $input)
		);
	}

	/**
	 ** @test
	 **
	 ** buildArray should escape single quotes and backslashes in values.
	 **/
	public function build_array_escapes_special_characters()
	{
		$controller = new LanguageController();
		$method = new ReflectionMethod(LanguageController::class, 'buildArray');
		$method->setAccessible(true);

		$input = ["quote" => "O'Reilly", "backslash" => "C:\\path"];
		$expected = "'quote'=>'O\\'Reilly','backslash'=>'C:\\\\path',";

		$this->assertSame(
			$expected,
			$method->invoke($controller, $input)
		);
	}
}
