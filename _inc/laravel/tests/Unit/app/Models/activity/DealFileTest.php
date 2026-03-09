<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{Deal, DealFile};

class DealFileTest extends TestCase
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
	 ** DealFile is mass assignable for deal_id, file_name, and file_path
	 **/
	public function deal_file_is_fillable()
	{
		$data = [
			'file_path' => '/uploads/document.pdf',
			'name'      => 'document.pdf',
			'extension' => 'pdf',
		];

		$dealFile = DealFile::create($data);

		$this->assertEquals('/uploads/document.pdf', $dealFile->getAttributes()['file_path']);
		$this->assertEquals('document.pdf',          $dealFile->getAttributes()['name']);
		$this->assertEquals('pdf',                   $dealFile->getAttributes()['extension']);
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
