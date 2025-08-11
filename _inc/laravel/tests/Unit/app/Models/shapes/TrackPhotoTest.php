<?php

namespace Tests\Unit\Models;

use App\Models\TrackPhoto;
use Tests\TestCase;

class TrackPhotoTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The $fillable array must match the declared fields.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'track_id',
			'user_id',
			'img_path',
			'time',
			'status',
		];

		$this->assertSame($expected, (new TrackPhoto)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** user() must be a BelongsTo relation mapping user_id → users.id.
	 **/
	public function user_relation_is_belongs_to(): void
	{
		$rel = (new TrackPhoto)->user();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('user_id', $rel->getForeignKeyName());
		$this->assertSame('id',      $rel->getOwnerKeyName());
	}
}
