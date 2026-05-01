<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ViewsConstants as VW;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * @group products
 * @group products-returns
 * @group hardening
 */
class ProductControlRouteReturnTest extends TestCase
{
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
		$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first();
		if ($this->admin) {
			$this->actingAs($this->admin);
		}
	}

	protected function assertNot500(TestResponse $r, string $ctx = ''): void
	{
		$this->assertNotEquals(500, $r->getStatusCode(), "HTTP 500 on [{$ctx}]");
	}

	protected function assertSuccessOrRedirect(TestResponse $r, string $ctx = ''): void
	{
		$code = $r->getStatusCode();
		$this->assertTrue(
			$code >= 200 && $code < 400,
			"Expected 2xx/3xx, got {$code} on [{$ctx}]"
		);
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 1 — PRODUCT SERVICES (CRUD + extras)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider productServiceIndexProvider
	 */
	public function test_product_service_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function productServiceIndexProvider(): array
	{
		return [
			'index' => ['/' . VW::PRD_SV . '/index', VW::PRD_SV . ' index'],
			'create' => ['/' . VW::PRD_SV . '/create', VW::PRD_SV . ' create'],
			'export' => ['/' . VW::PRD_SV . '/export', VW::PRD_SV . ' export'],
		];
	}

	/**
	 * @dataProvider productServiceCrudFakeIdProvider
	 */
	public function test_product_service_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function productServiceCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::PRD_SV . "/{$fk}", VW::PRD_SV . ' show'],
			'edit' => ['GET', '/' . VW::PRD_SV . "/{$fk}/edit", VW::PRD_SV . ' edit'],
			'update' => ['PUT', '/' . VW::PRD_SV . "/{$fk}", VW::PRD_SV . ' update'],
			'delete' => ['DELETE', '/' . VW::PRD_SV . "/{$fk}", VW::PRD_SV . ' destroy'],
			'store_empty' => ['POST', '/' . VW::PRD_SV, VW::PRD_SV . ' store'],
			'detail' => ['GET', '/' . VW::PRD_SV . "/{$fk}/detail", VW::PRD_SV . ' detail'],
		];
	}

	/**
	 * @dataProvider productServiceCartProvider
	 */
	public function test_product_service_cart_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function productServiceCartProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'empty_cart' => ['POST', '/empty-cart', 'empty cart'],
			'warehouse_empty_cart' => ['POST', '/warehouse-empty-cart', 'warehouse empty cart'],
			'add_to_cart' => ['GET', "/add-to-cart/{$fk}/pos", 'add to cart'],
			'update_cart' => ['PATCH', '/update-cart', 'update cart'],
			'remove_cart' => ['DELETE', '/remove-from-cart', 'remove from cart'],
		];
	}

	/**
	 * @dataProvider productServiceSearchProvider
	 */
	public function test_product_service_search_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function productServiceSearchProvider(): array
	{
		return [
			'search_products' => ['/search-products', 'search products'],
			'name_search_products' => ['/name-search-products', 'name search products'],
			'product_categories' => ['/product-categories', 'product categories'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — PRODUCT SERVICE CATEGORIES (CRUD)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider productServiceCategoryResourceProvider
	 */
	public function test_product_service_category_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function productServiceCategoryResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PRD_SV_CAT, VW::PRD_SV_CAT . ' index'],
			'create' => ['GET', '/' . VW::PRD_SV_CAT . '/create', VW::PRD_SV_CAT . ' create'],
			'store_empty' => ['POST', '/' . VW::PRD_SV_CAT, VW::PRD_SV_CAT . ' store'],
			'show' => ['GET', '/' . VW::PRD_SV_CAT . "/{$fk}", VW::PRD_SV_CAT . ' show'],
			'edit' => ['GET', '/' . VW::PRD_SV_CAT . "/{$fk}/edit", VW::PRD_SV_CAT . ' edit'],
			'update' => ['PUT', '/' . VW::PRD_SV_CAT . "/{$fk}", VW::PRD_SV_CAT . ' update'],
			'delete' => ['DELETE', '/' . VW::PRD_SV_CAT . "/{$fk}", VW::PRD_SV_CAT . ' destroy'],
		];
	}

	public function test_product_service_category_get_account(): void
	{
		$r = $this->post('/' . VW::PRD_SV_CAT . '/get-account');
		$this->assertNot500($r, VW::PRD_SV_CAT . ' get_account');
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — PRODUCT SERVICE UNITS (CRUD)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider productServiceUnitResourceProvider
	 */
	public function test_product_service_unit_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function productServiceUnitResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PRD_SV_UNT, VW::PRD_SV_UNT . ' index'],
			'create' => ['GET', '/' . VW::PRD_SV_UNT . '/create', VW::PRD_SV_UNT . ' create'],
			'store_empty' => ['POST', '/' . VW::PRD_SV_UNT, VW::PRD_SV_UNT . ' store'],
			'show' => ['GET', '/' . VW::PRD_SV_UNT . "/{$fk}", VW::PRD_SV_UNT . ' show'],
			'edit' => ['GET', '/' . VW::PRD_SV_UNT . "/{$fk}/edit", VW::PRD_SV_UNT . ' edit'],
			'update' => ['PUT', '/' . VW::PRD_SV_UNT . "/{$fk}", VW::PRD_SV_UNT . ' update'],
			'delete' => ['DELETE', '/' . VW::PRD_SV_UNT . "/{$fk}", VW::PRD_SV_UNT . ' destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 4 — PRODUCT STOCKS (CRUD)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider productStockResourceProvider
	 */
	public function test_product_stock_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function productStockResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PRD_STK, VW::PRD_STK . ' index'],
			'create' => ['GET', '/' . VW::PRD_STK . '/create', VW::PRD_STK . ' create'],
			'store_empty' => ['POST', '/' . VW::PRD_STK, VW::PRD_STK . ' store'],
			'show' => ['GET', '/' . VW::PRD_STK . "/{$fk}", VW::PRD_STK . ' show'],
			'edit' => ['GET', '/' . VW::PRD_STK . "/{$fk}/edit", VW::PRD_STK . ' edit'],
			'update' => ['PUT', '/' . VW::PRD_STK . "/{$fk}", VW::PRD_STK . ' update'],
			'delete' => ['DELETE', '/' . VW::PRD_STK . "/{$fk}", VW::PRD_STK . ' destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — WAREHOUSES (CRUD - uses DBC::TABLE_WHS)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider warehouseResourceProvider
	 */
	public function test_warehouse_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function warehouseResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$table = DC::TABLE_WHS;
		return [
			'index' => ['GET', "/{$table}", $table . ' index'],
			'create' => ['GET', "/{$table}/create", $table . ' create'],
			'store_empty' => ['POST', "/{$table}", $table . ' store'],
			'show' => ['GET', "/{$table}/{$fk}", $table . ' show'],
			'edit' => ['GET', "/{$table}/{$fk}/edit", $table . ' edit'],
			'update' => ['PUT', "/{$table}/{$fk}", $table . ' update'],
			'delete' => ['DELETE', "/{$table}/{$fk}", $table . ' destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 6 — WAREHOUSE TRANSFERS (CRUD + extras)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider warehouseTransferResourceProvider
	 */
	public function test_warehouse_transfer_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function warehouseTransferResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::WRH_TRF, VW::WRH_TRF . ' index'],
			'create' => ['GET', '/' . VW::WRH_TRF . '/create', VW::WRH_TRF . ' create'],
			'store_empty' => ['POST', '/' . VW::WRH_TRF, VW::WRH_TRF . ' store'],
			'show' => ['GET', '/' . VW::WRH_TRF . "/{$fk}", VW::WRH_TRF . ' show'],
			'edit' => ['GET', '/' . VW::WRH_TRF . "/{$fk}/edit", VW::WRH_TRF . ' edit'],
			'update' => ['PUT', '/' . VW::WRH_TRF . "/{$fk}", VW::WRH_TRF . ' update'],
			'delete' => ['DELETE', '/' . VW::WRH_TRF . "/{$fk}", VW::WRH_TRF . ' destroy'],
		];
	}

	/**
	 * @dataProvider warehouseTransferAjaxProvider
	 */
	public function test_warehouse_transfer_ajax_routes(string $uri, string $label): void
	{
		$r = $this->post($uri);
		$this->assertNot500($r, $label);
	}

	public static function warehouseTransferAjaxProvider(): array
	{
		return [
			'get_product' => ['/' . VW::WRH_TRF . '/get-product', VW::WRH_TRF . ' get product'],
			'get_quantity' => ['/' . VW::WRH_TRF . '/get-quantity', VW::WRH_TRF . ' get quantity'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 7 — PROPOSAL PRODUCTS (CRUD - used by proposals)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider proposalProductResourceProvider
	 */
	public function test_proposal_product_resource_routes(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function proposalProductResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'index' => ['GET', '/' . VW::PPS_PRD, VW::PPS_PRD . ' index'],
			'create' => ['GET', '/' . VW::PPS_PRD . '/create', VW::PPS_PRD . ' create'],
			'store_empty' => ['POST', '/' . VW::PPS_PRD, VW::PPS_PRD . ' store'],
			'show' => ['GET', '/' . VW::PPS_PRD . "/{$fk}", VW::PPS_PRD . ' show'],
			'edit' => ['GET', '/' . VW::PPS_PRD . "/{$fk}/edit", VW::PPS_PRD . ' edit'],
			'update' => ['PUT', '/' . VW::PPS_PRD . "/{$fk}", VW::PPS_PRD . ' update'],
			'delete' => ['DELETE', '/' . VW::PPS_PRD . "/{$fk}", VW::PPS_PRD . ' destroy'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 8 — INDEX CONTENT KEYWORDS
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider indexContentKeywordProvider
	 */
	public function test_index_contains_expected_keyword(string $uri, string $keyword, string $label): void
	{
		$r = $this->get($uri);
		if ($r->getStatusCode() === 200) {
			$r->assertSee($keyword, false);
		} else {
			$this->assertNot500($r, $label);
		}
	}

	public static function indexContentKeywordProvider(): array
	{
		return [
			'product_services' => ['/' . VW::PRD_SV . '/index', 'product', VW::PRD_SV . ' index'],
			'categories' => ['/' . VW::PRD_SV_CAT, 'categor', VW::PRD_SV_CAT . ' index'],
			'units' => ['/' . VW::PRD_SV_UNT, 'unit', VW::PRD_SV_UNT . ' index'],
			'stocks' => ['/' . VW::PRD_STK, 'stock', VW::PRD_STK . ' index'],
			'warehouses' => ['/' . DC::TABLE_WHS, 'warehouse', DC::TABLE_WHS . ' index'],
			'transfers' => ['/' . VW::WRH_TRF, 'transfer', VW::WRH_TRF . ' index'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 9 — PRODUCT IMPORT/EXPORT
	// ══════════════════════════════════════════════════════════════════════

	public function test_product_service_export_returns_file(): void
	{
		$r = $this->get('/' . VW::PRD_SV . '/export');
		$this->assertNot500($r, VW::PRD_SV . ' export');
		if ($r->getStatusCode() === 200) {
			$this->assertTrue(
				$r->headers->has('content-disposition') || $r->headers->has('Content-Disposition'),
				'Export should return a downloadable file'
			);
		}
	}

	public function test_product_service_import_post_empty(): void
	{
		$r = $this->post('/' . VW::PRD_SV . '/import');
		$this->assertNot500($r, VW::PRD_SV . ' import');
	}
}
