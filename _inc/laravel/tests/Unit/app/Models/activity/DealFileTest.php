<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{Deal, DealFile};

class DealFileTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** DealFile is mass assignable for deal_id, file_name, and file_path
	 **/
	public function deal_file_is_fillable()
	{
		$deal = Deal::factory()->create();

		$data = [
			'deal_id'   => $deal->id,
			'file_name' => 'document.pdf',
			'file_path' => '/uploads/document.pdf',
		];

		$dealFile = DealFile::create($data);

		$this->assertEquals($deal->id,             $dealFile->deal_id);
		$this->assertEquals('document.pdf',        $dealFile->file_name);
		$this->assertEquals('/uploads/document.pdf', $dealFile->file_path);
	}

	/**
	 ** @test
	 **
	 ** DealFile uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function deal_file_uses_uuid_for_primary_key()
	{
		$deal = Deal::factory()->create();

		$dealFile = DealFile::create([
			'deal_id'   => $deal->id,
			'file_name' => 'test.txt',
			'file_path' => '/tmp/test.txt',
		]);

		$key = $dealFile->getKey();

		$this->assertIsString($key);
		$this->assertFalse($dealFile->getIncrementing());
		$this->assertSame('string', $dealFile->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** deal() relation should point to App\Models\Deal via deal_id
	 **/
	public function deal_relation_resolves_to_deal_model()
	{
		$relation = (new DealFile)->deal();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Deal::class,            get_class($relation->getRelated()));
		$this->assertSame('deal_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** DealFile model should eager-load the deal relation by default
	 **/
	public function model_eager_loads_deal_by_default()
	{
		$model = new DealFile;
		$this->assertSame(['deal'], $model->getWith());
	}
}
