<?php

namespace Tests\Unit\Imports;

use App\Imports\CustomerImport;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

class CustomerImportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** When no user is authenticated, `model()` must return a
	 ** `RedirectResponse` so that the import process is halted.
	 **/
	public function model_returns_redirect_response_if_unauthenticated(): void
	{
		$importer = new CustomerImport();
		$row     = ['foo', 'bar'];

		$result = $importer->model($row);

		$this->assertInstanceOf(
			RedirectResponse::class,
			$result,
			'model() should return RedirectResponse when unauthenticated.'
		);
	}

	/**
	 ** @test
	 **
	 ** `detectHeader()` must return `null` if fewer than two fillable
	 ** columns are found in the row.
	 **/
	public function detect_header_returns_null_when_insufficient_matches(): void
	{
		$importer = new CustomerImport();
		$method  = new ReflectionMethod(CustomerImport::class, 'detectHeader');
		$method->setAccessible(true);

		$row = ['not_fillable', 'also_not', '', ''];
		$map = $method->invoke($importer, $row);

		$this->assertNull($map, 'detectHeader should return null with <2 matches.');
	}

	/**
	 ** @test
	 **
	 ** The first call to `model()` with a header row must detect
	 ** the header and return `null`, setting `headerFound` to `true`
	 ** and populating `headerMap`.
	 **/
	public function model_detects_header_and_sets_properties(): void
	{
		$user    = User::factory()->create();
		$this->actingAs($user);

		$importer = new CustomerImport();

		// Prepare a header row matching two of Customer's fillable fields
		$fillable = (new Customer())->getFillable();
		$keys    = array_slice($fillable, 0, 2);
		$header  = [$keys[0], 'irrelevant', $keys[1], '', ''];

		$result = $importer->model($header);
		$this->assertNull($result, 'First header row should return null.');

		$refMap     = new ReflectionProperty(CustomerImport::class, 'headerMap');
		$refMap->setAccessible(true);
		$headerMap  = $refMap->getValue($importer);

		$refFound   = new ReflectionProperty(CustomerImport::class, 'headerFound');
		$refFound->setAccessible(true);
		$headerFound = $refFound->getValue($importer);

		$this->assertTrue($headerFound, 'headerFound should be true after header detection.');
		$this->assertCount(2, $headerMap, 'headerMap should contain exactly 2 entries.');
		$this->assertSame(0, $headerMap[$keys[0]]);
		$this->assertSame(2, $headerMap[$keys[1]]);
	}

	/**
	 ** @test
	 **
	 ** After header detection, calling `model()` with a data row must
	 ** create and return a `Customer` instance with correct fields,
	 ** including `created_by`.
	 **/
	public function model_creates_customer_after_header_detection(): void
	{
		$user    = User::factory()->create();
		$this->actingAs($user);

		$importer = new CustomerImport();

		// Header detection
		$fillable = (new Customer())->getFillable();
		$keys    = array_slice($fillable, 0, 2);
		$header  = [$keys[0], '', $keys[1]];
		$importer->model($header);

		// Data row matching header positions
		$values = ['Alice', 'ignored', 'alice@example.com'];
		$customer = $importer->model($values);

		$this->assertInstanceOf(
			Customer::class,
			$customer,
			'Data row should yield a Customer instance.'
		);

		$this->assertDatabaseHas('customers', [
			$keys[0]      => 'Alice',
			$keys[1]      => 'alice@example.com',
			'created_by'  => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** If `detectHeader()` finds fewer than two matches, `model()`
	 ** must log an error and return null without setting `headerFound`.
	 **/
	public function model_logs_error_and_skips_when_header_not_found(): void
	{
		Log::shouldReceive('error')
			->once()
			->withArgs(function ($msg) {
				return str_contains($msg, 'CustomerImport::model header row not found');
			});

		$user    = User::factory()->create();
		$this->actingAs($user);

		$importer = new CustomerImport();
		$row     = ['only_one_fillable'];

		$result = $importer->model($row);
		$this->assertNull($result, 'Should return null when header not detected.');

		$refFound = (new ReflectionProperty(CustomerImport::class, 'headerFound'));
		$refFound->setAccessible(true);
		$this->assertFalse($refFound->getValue($importer), 'headerFound should remain false.');
	}
}
