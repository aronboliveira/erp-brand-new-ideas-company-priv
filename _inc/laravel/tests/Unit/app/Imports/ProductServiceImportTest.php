<?php

namespace Tests\Unit\Imports;

use App\Imports\ProductServiceImport;
use App\Models\ProductService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

class ProductServiceImportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** toCamelCase() must strip non-alphanumerics, split words, lowercase
	 ** the first word, and uppercase subsequent words to produce camelCase.
	 **/
	public function to_camel_case_strips_and_formats_text(): void
	{
		$importer = new ProductServiceImport();
		$ref = new ReflectionMethod(ProductServiceImport::class, 'toCamelCase');
		$ref->setAccessible(true);

		$this->assertSame('productName',    $ref->invoke($importer, 'Product Name'));
		$this->assertSame('field123Test',   $ref->invoke($importer, 'Field123! @Test'));
		$this->assertSame('simple',         $ref->invoke($importer, 'SIMPLE'));
	}

	/**
	 ** @test
	 **
	 ** locateHeaderRow() must find the first pair of consecutive non-empty
	 ** cells, return their start index, and map the header cells (camelCased).
	 **/
	public function locate_header_row_detects_start_and_headers(): void
	{
		$importer = new ProductServiceImport();
		$ref = new ReflectionMethod(ProductServiceImport::class, 'locateHeaderRow');
		$ref->setAccessible(true);

		$row = ['Id', ' Service Name ', 'Description', '', ''];
		[$start, $headers] = $ref->invoke($importer, $row);

		$this->assertSame(0, $start);
		$this->assertSame(['id', 'ServiceName', 'description'], $headers);
	}

	/**
	 ** @test
	 **
	 ** model() must on first invocation detect and store headers (returning null),
	 ** then on second invocation create a ProductService with the mapped data
	 ** and the authenticated user as created_by.
	 **/
	public function model_detects_header_then_creates_product_service(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$fillable = (new ProductService())->getFillable();
		if (count($fillable) < 2) {
			$this->markTestSkipped('Not enough fillable fields on ProductService');
		}
		$keys = array_slice($fillable, 0, 2);

		$headerRow = [$keys[0], $keys[1], '', ''];
		$dataRow  = ['FirstVal', 'SecondVal', 'X', 'Y'];

		$importer = new ProductServiceImport();

		// First call: header detection
		$this->assertNull($importer->model($headerRow));

		$startProp = new ReflectionProperty(ProductServiceImport::class, 'headerStartIndex');
		$startProp->setAccessible(true);
		$this->assertSame(0, $startProp->getValue($importer));

		$headersProp = new ReflectionProperty(ProductServiceImport::class, 'headers');
		$headersProp->setAccessible(true);
		$this->assertSame($keys, $headersProp->getValue($importer));

		// Second call: data row
		$service = $importer->model($dataRow);

		$this->assertInstanceOf(ProductService::class, $service);
		$this->assertDatabaseHas('product_services', [
			$keys[0]     => 'FirstVal',
			$keys[1]     => 'SecondVal',
			'created_by' => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** If locateHeaderRow() cannot find a valid header pair, model()
	 ** must log an error and leave header properties null without creating.
	 **/
	public function model_logs_error_and_skips_when_header_not_found(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		Log::spy();

		$importer = new ProductServiceImport();
		$result  = $importer->model(['', 'OnlyOne']);

		$this->assertNull($result);

		Log::shouldHaveReceived('error')
			->withArgs(fn($msg) => str_contains($msg, 'ProductServiceImport::model failed'))
			->once();

		$startProp = new ReflectionProperty(ProductServiceImport::class, 'headerStartIndex');
		$startProp->setAccessible(true);
		$this->assertNull($startProp->getValue($importer));

		$headersProp = new ReflectionProperty(ProductServiceImport::class, 'headers');
		$headersProp->setAccessible(true);
		$this->assertNull($headersProp->getValue($importer));
	}
}
