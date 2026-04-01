<?php

namespace Tests\Unit\Models;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}

	/**
	 ** @test
	 **
	 ** The $fillable array must list id, code, full_name.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'code',
			'full_name',
			'created_by',
		];
		$this->assertSame($expected, (new Language)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** languageData() should cache and return a Language instance.
	 **/
	public function language_data_returns_cached_model(): void
	{
		$lang = new Language;
		$lang->id = 1;
		$lang->code = 'en';
		$lang->full_name = 'English';

		// Stub Cache::remember to return our fake $lang
		Cache::shouldReceive('remember')
			->once()
			->andReturn($lang);

		$result = Language::languageData('en');
		$this->assertSame($lang, $result);
	}
}
