<?php

namespace Tests\Unit\Imports;

use App\Imports\VendorImport;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use ReflectionProperty;
use Tests\TestCase;

class VendorImportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** model() must return null and log an error when two consecutive
	 ** empty cells appear before any header is detected.
	 **/
	public function model_logs_error_and_returns_null_if_header_not_found(): void
	{
		Log::shouldReceive('error')
			->once()
			->withArgs(fn ($msg) => str_contains($msg, 'VendorImport::model header row not found'));

		$importer = new VendorImport();
		$row     = [null, '', 'foo', 'bar'];

		$result = $importer->model($row);
		$this->assertNull($result, 'Should return null when header not found.');

		$refFound = new ReflectionProperty(VendorImport::class, 'headerFound');
		$refFound->setAccessible(true);
		$this->assertFalse($refFound->getValue($importer), 'headerFound should remain false.');
	}

	/**
	 ** @test
	 **
	 ** On the first invocation with a valid header row, model() must
	 ** detect the header start index, set headerFound=true, and return null.
	 **/
	public function model_detects_header_on_first_call(): void
	{
		$headerRow = ['vendor_id', 'name', 'email'];

		$importer = new VendorImport();
		$this->assertNull($importer->model($headerRow), 'First call should return null.');

		$refStart = new ReflectionProperty(VendorImport::class, 'headerStartIndex');
		$refStart->setAccessible(true);
		$this->assertSame(0, $refStart->getValue($importer), 'headerStartIndex should be 0.');

		$refFound = new ReflectionProperty(VendorImport::class, 'headerFound');
		$refFound->setAccessible(true);
		$this->assertTrue($refFound->getValue($importer), 'headerFound should be true.');
	}

	/**
	 ** @test
	 **
	 ** After header detection, a second invocation with a data row must
	 ** create and return a Vendor, mapping fields correctly and hashing
	 ** the password.
	 **/
	public function model_creates_vendor_after_header_detection(): void
	{
		$importer = new VendorImport();

		// 1) Header detection
		$importer->model(['vendor_id', 'name', 'email', 'password']);

		// 2) Data row: supply at least first four columns
		$dataRow = ['V123', 'Acme Co', 'acme@example.com', 's3cret'];
		$vendor = $importer->model($dataRow);

		$this->assertInstanceOf(Vendor::class, $vendor, 'Should return a Vendor instance.');

		$this->assertDatabaseHas('vendors', [
			'vendor_id' => 'V123',
			'name'      => 'Acme Co',
			'email'     => 'acme@example.com',
		]);

		// Password must be hashed
		$this->assertTrue(
			Hash::check('s3cret', $vendor->password),
			'Password should be stored hashed.'
		);
	}
}
