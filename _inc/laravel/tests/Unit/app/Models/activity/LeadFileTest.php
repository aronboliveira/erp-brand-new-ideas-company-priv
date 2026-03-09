<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{Lead, LeadFile};

class LeadFileTest extends TestCase
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
	 ** LeadFile is mass assignable for lead_id, file_name, and file_path
	 **/
	public function lead_file_is_fillable()
	{
		$lead = Lead::factory()->create();

		$data = [
			'lead_id'   => $lead->id,
			'file_name' => 'contract.pdf',
			'file_path' => '/uploads/contract.pdf',
		];

		$leadFile = LeadFile::create($data);

		$this->assertEquals($lead->id,             $leadFile->lead_id);
		$this->assertEquals('contract.pdf',        $leadFile->file_name);
		$this->assertEquals('/uploads/contract.pdf', $leadFile->file_path);
	}

	/**
	 ** @test
	 **
	 ** LeadFile uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function lead_file_uses_uuid_for_primary_key()
	{
		$lead = Lead::factory()->create();

		$leadFile = LeadFile::create([
			'lead_id'   => $lead->id,
			'file_name' => 'notes.txt',
			'file_path' => '/tmp/notes.txt',
		]);

		$key = $leadFile->getKey();

		$this->assertIsString($key);
		$this->assertFalse($leadFile->getIncrementing());
		$this->assertSame('string', $leadFile->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** lead() relation should point to App\Models\Lead via lead_id
	 **/
	public function lead_relation_resolves_to_lead_model()
	{
		$relation = (new LeadFile)->lead();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Lead::class,             get_class($relation->getRelated()));
		$this->assertSame('lead_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
