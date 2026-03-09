<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\DocumentUpload;

class DocumentUploadTest extends TestCase
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
	 ** DocumentUpload is mass assignable for name, role, document, description, and created_by
	 **/
	public function document_upload_is_fillable()
	{
		$data = [
			'name'        => 'Employment Contract',
			'role'        => 'Manager',
			'document'    => '/docs/contract.pdf',
			'description' => 'Signed employment contract',
		];

		$upload = DocumentUpload::create($data);

		$this->assertFillableMatches($data, $upload);
	}

	/**
	 ** @test
	 **
	 ** DocumentUpload uses UUIDs for primary key: string, non-incrementing, valid UUID format
	 **/
	public function document_upload_uses_uuid_for_primary_key()
	{
		$upload = DocumentUpload::create([
			'name'        => 'Policy Document',
			'role'        => 'Employee',
			'document'    => '/docs/policy.pdf',
			'description' => 'Company policy',
		]);

		$key = $upload->getKey();

		$this->assertIsString($key);
		$this->assertFalse($upload->getIncrementing());
		$this->assertSame('string', $upload->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
