<?php

namespace Tests\Unit\Imports;

use App\Imports\VendorImport;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use ReflectionProperty;
use Tests\TestCase;

class VendorImportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** model() must return null and log an error when two consecutive
	 ** empty cells appear before any header is detected.
	 **/
	public function model_logs_error_and_returns_null_if_header_not_found(): void
	{
		Log::spy();

		$importer = new VendorImport();
		$row     = [null, '', 'foo', 'bar'];

		$result = $importer->model($row);
		$this->assertNull($result, 'Should return null when header not found.');

		Log::shouldHaveReceived('error')
			->withArgs(fn($msg) => str_contains($msg, 'VendorImport::model header row not found'))
			->once();

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
		$user = User::factory()->create();
		$this->actingAs($user);

		$importer = new VendorImport();

		// 1) Header detection — provide all 23 FIELDS as header row
		$headerRow = [
			'vendor_id',
			'name',
			'email',
			'password',
			'contact',
			'avatar',
			'is_active',
			'created_by',
			'email_verified_at',
			'billing_name',
			'billing_country',
			'billing_state',
			'billing_city',
			'billing_phone',
			'billing_zip',
			'billing_address',
			'shipping_name',
			'shipping_country',
			'shipping_state',
			'shipping_city',
			'shipping_phone',
			'shipping_zip',
			'shipping_address',
		];
		$importer->model($headerRow);

		// 2) Data row: provide values for all 23 FIELDS positional mapping
		// vendor_id has FK to users.id, so use the authenticated user's ID
		$dataRow = [
			$user->id,           // vendor_id (FK to users.id)
			'Acme Co',           // name
			'acme@example.com',  // email
			's3cret',            // password (will be hashed)
			'1234567890',        // contact
			null,                // avatar
			1,                   // is_active
			null,                // created_by (guarded, filled by HasAuditFields)
			null,                // email_verified_at
			null,
			null,
			null,
			null,
			null,
			null,
			null, // billing fields
			null,
			null,
			null,
			null,
			null,
			null,
			null, // shipping fields
		];
		$vendor = $importer->model($dataRow);

		$this->assertInstanceOf(Vendor::class, $vendor, 'Should return a Vendor instance.');

		$this->assertDatabaseHas('vendors', [
			'vendor_id' => $user->id,
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
