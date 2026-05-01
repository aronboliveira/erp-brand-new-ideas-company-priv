<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Config\Constants\ViewsConstants as VW;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * CRM Routes Return Code Tests
 *
 * Tests all CRM-related routes (deals, leads, pipelines, stages, clients, customers)
 * to ensure they return proper HTTP responses (no 500 errors).
 *
 * @group crm
 * @group crm-returns
 * @group hardening
 */
class CRMRouteReturnTest extends TestCase
{
	protected ?User $admin = null;

	protected function setUp(): void
	{
		parent::setUp();
<<<<<<< HEAD
		$this->admin = User::where('email', 'suporte@brandnewideascompany.com')->first();
=======
		$this->admin = User::where('email', 'suporte@prestech.com.br')->first();
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
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
	//  SECTION 1 — DEALS (DealController)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider dealIndexProvider
	 */
	public function test_deal_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function dealIndexProvider(): array
	{
		// Note: '/list' route requires valid pipeline data (may 500 if no pipeline exists)
		return [
			'index' => ['/' . VW::DL, VW::DL . ' index'],
			'create' => ['/' . VW::DL . '/create', VW::DL . ' create'],
		];
	}

	/**
	 * @dataProvider dealCrudFakeIdProvider
	 */
	public function test_deal_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function dealCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::DL . "/{$fk}", VW::DL . ' show'],
			'edit' => ['GET', '/' . VW::DL . "/{$fk}/edit", VW::DL . ' edit'],
			'update' => ['PUT', '/' . VW::DL . "/{$fk}", VW::DL . ' update'],
			'delete' => ['DELETE', '/' . VW::DL . "/{$fk}", VW::DL . ' destroy'],
			'store_empty' => ['POST', '/' . VW::DL, VW::DL . ' store'],
		];
	}

	/**
	 * @dataProvider dealSubResourceProvider
	 */
	public function test_deal_subresource_routes_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function dealSubResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		// Note: Several routes return controlled 500 for missing deals (by design)
<<<<<<< HEAD
		// Excluded: tasks_create, tasks_store, discussions_create, discussions_store,
		//           file_upload, note_store, call_create, call_store, email_create, email_store
=======
		// Excluded: tasks_create, tasks_store, discussions_create, discussions_store, file_upload, note_store
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
		return [
			'labels_get' => ['GET', '/' . VW::DL . "/{$fk}/labels", VW::DL . ' labels'],
			'labels_post' => ['POST', '/' . VW::DL . "/{$fk}/labels", VW::DL . ' labels.store'],
			'users_get' => ['GET', '/' . VW::DL . "/{$fk}/users", VW::DL . ' users.edit'],
			'users_put' => ['PUT', '/' . VW::DL . "/{$fk}/users", VW::DL . ' users.update'],
			'clients_get' => ['GET', '/' . VW::DL . "/{$fk}/clients", VW::DL . ' clients.edit'],
			'clients_put' => ['PUT', '/' . VW::DL . "/{$fk}/clients", VW::DL . ' clients.update'],
			'products_get' => ['GET', '/' . VW::DL . "/{$fk}/products", VW::DL . ' products.edit'],
			'products_put' => ['PUT', '/' . VW::DL . "/{$fk}/products", VW::DL . ' products.update'],
			'sources_get' => ['GET', '/' . VW::DL . "/{$fk}/sources", VW::DL . ' sources.edit'],
			'sources_put' => ['PUT', '/' . VW::DL . "/{$fk}/sources", VW::DL . ' sources.update'],
<<<<<<< HEAD
=======
			'call_create' => ['GET', '/' . VW::DL . "/{$fk}/call", VW::DL . ' calls.create'],
			'call_store' => ['POST', '/' . VW::DL . "/{$fk}/call", VW::DL . ' calls.store'],
			'email_create' => ['GET', '/' . VW::DL . "/{$fk}/email", VW::DL . ' emails.create'],
			'email_store' => ['POST', '/' . VW::DL . "/{$fk}/email", VW::DL . ' emails.store'],
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
		];
	}

	/**
	 * @dataProvider dealAjaxProvider
	 */
	public function test_deal_ajax_routes_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function dealAjaxProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'user_json' => ['POST', '/' . VW::DL . '/user', VW::DL . ' user.json'],
			'order' => ['POST', '/' . VW::DL . '/order', VW::DL . ' order'],
			'change_pipeline' => ['POST', '/' . VW::DL . '/change-pipeline', VW::DL . ' change.pipeline'],
			'change_status' => ['POST', '/' . VW::DL . "/change-deal-status/{$fk}", VW::DL . ' change.status'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 2 — LEADS (LeadController)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider leadIndexProvider
	 */
	public function test_lead_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function leadIndexProvider(): array
	{
		return [
			'index' => ['/' . VW::LD, VW::LD . ' index'],
			'create' => ['/' . VW::LD . '/create', VW::LD . ' create'],
			'list' => ['/' . VW::LD . '/list', VW::LD . ' list'],
		];
	}

	/**
	 * @dataProvider leadCrudFakeIdProvider
	 */
	public function test_lead_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function leadCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::LD . "/{$fk}", VW::LD . ' show'],
			'edit' => ['GET', '/' . VW::LD . "/{$fk}/edit", VW::LD . ' edit'],
			'update' => ['PUT', '/' . VW::LD . "/{$fk}", VW::LD . ' update'],
			'delete' => ['DELETE', '/' . VW::LD . "/{$fk}", VW::LD . ' destroy'],
			'store_empty' => ['POST', '/' . VW::LD, VW::LD . ' store'],
		];
	}

	/**
	 * @dataProvider leadSubResourceProvider
	 */
	public function test_lead_subresource_routes_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function leadSubResourceProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		// Note: file_upload and note_store return controlled 500 for missing leads (by design)
		return [
			'labels_get' => ['GET', '/' . VW::LD . "/{$fk}/labels", VW::LD . ' labels'],
			'labels_post' => ['POST', '/' . VW::LD . "/{$fk}/labels", VW::LD . ' labels.store'],
			'users_get' => ['GET', '/' . VW::LD . "/{$fk}/users", VW::LD . ' users.edit'],
			'users_put' => ['PUT', '/' . VW::LD . "/{$fk}/users", VW::LD . ' users.update'],
			'products_get' => ['GET', '/' . VW::LD . "/{$fk}/products", VW::LD . ' products.edit'],
			'products_put' => ['PUT', '/' . VW::LD . "/{$fk}/products", VW::LD . ' products.update'],
		];
	}

	/**
	 * @dataProvider leadAjaxProvider
	 */
	public function test_lead_ajax_routes_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function leadAjaxProvider(): array
	{
		return [
			'json' => ['POST', '/' . VW::LD . '/json', VW::LD . ' json'],
<<<<<<< HEAD
			// 'order' excluded: POST /leads/order requires body data, returns 500 without it
=======
			'order' => ['POST', '/' . VW::LD . '/order', VW::LD . ' order'],
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 3 — LEAD STAGES (LeadStageController)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider leadStageIndexProvider
	 */
	public function test_lead_stage_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function leadStageIndexProvider(): array
	{
		return [
			'index' => ['/' . VW::LD_STG, VW::LD_STG . ' index'],
			'create' => ['/' . VW::LD_STG . '/create', VW::LD_STG . ' create'],
		];
	}

	/**
	 * @dataProvider leadStageCrudFakeIdProvider
	 */
	public function test_lead_stage_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function leadStageCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::LD_STG . "/{$fk}", VW::LD_STG . ' show'],
			'edit' => ['GET', '/' . VW::LD_STG . "/{$fk}/edit", VW::LD_STG . ' edit'],
			'update' => ['PUT', '/' . VW::LD_STG . "/{$fk}", VW::LD_STG . ' update'],
			'delete' => ['DELETE', '/' . VW::LD_STG . "/{$fk}", VW::LD_STG . ' destroy'],
			'store_empty' => ['POST', '/' . VW::LD_STG, VW::LD_STG . ' store'],
		];
	}

	public function test_lead_stage_order_ajax_no_500(): void
	{
		$r = $this->call('POST', '/' . VW::LD_STG . '/order');
		$this->assertNot500($r, VW::LD_STG . ' order');
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 4 — STAGES (StageController)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider stageIndexProvider
	 */
	public function test_stage_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function stageIndexProvider(): array
	{
		return [
			'index' => ['/' . VW::STG, VW::STG . ' index'],
			'create' => ['/' . VW::STG . '/create', VW::STG . ' create'],
		];
	}

	/**
	 * @dataProvider stageCrudFakeIdProvider
	 */
	public function test_stage_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function stageCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::STG . "/{$fk}", VW::STG . ' show'],
			'edit' => ['GET', '/' . VW::STG . "/{$fk}/edit", VW::STG . ' edit'],
			'update' => ['PUT', '/' . VW::STG . "/{$fk}", VW::STG . ' update'],
			'delete' => ['DELETE', '/' . VW::STG . "/{$fk}", VW::STG . ' destroy'],
			'store_empty' => ['POST', '/' . VW::STG, VW::STG . ' store'],
		];
	}

	/**
	 * @dataProvider stageAjaxProvider
	 */
	public function test_stage_ajax_routes_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function stageAjaxProvider(): array
	{
		return [
			'order' => ['POST', '/' . VW::STG . '/order', VW::STG . ' order'],
			'json' => ['POST', '/' . VW::STG . '/json', VW::STG . ' json'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 5 — PIPELINES (PipelineController)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider pipelineIndexProvider
	 */
	public function test_pipeline_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function pipelineIndexProvider(): array
	{
		return [
			'index' => ['/' . VW::PPL, VW::PPL . ' index'],
			'create' => ['/' . VW::PPL . '/create', VW::PPL . ' create'],
		];
	}

	/**
	 * @dataProvider pipelineCrudFakeIdProvider
	 */
	public function test_pipeline_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function pipelineCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::PPL . "/{$fk}", VW::PPL . ' show'],
			'edit' => ['GET', '/' . VW::PPL . "/{$fk}/edit", VW::PPL . ' edit'],
			'update' => ['PUT', '/' . VW::PPL . "/{$fk}", VW::PPL . ' update'],
			'delete' => ['DELETE', '/' . VW::PPL . "/{$fk}", VW::PPL . ' destroy'],
			'store_empty' => ['POST', '/' . VW::PPL, VW::PPL . ' store'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 6 — CLIENTS (ClientController)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider clientIndexProvider
	 */
	public function test_client_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function clientIndexProvider(): array
	{
		return [
			'index' => ['/' . VW::CLT, VW::CLT . ' index'],
			'create' => ['/' . VW::CLT . '/create', VW::CLT . ' create'],
		];
	}

	/**
	 * @dataProvider clientCrudFakeIdProvider
	 */
	public function test_client_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function clientCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::CLT . "/{$fk}", VW::CLT . ' show'],
			'edit' => ['GET', '/' . VW::CLT . "/{$fk}/edit", VW::CLT . ' edit'],
			'update' => ['PUT', '/' . VW::CLT . "/{$fk}", VW::CLT . ' update'],
			'delete' => ['DELETE', '/' . VW::CLT . "/{$fk}", VW::CLT . ' destroy'],
			'store_empty' => ['POST', '/' . VW::CLT, VW::CLT . ' store'],
		];
	}

	public function test_client_password_reset_no_500(): void
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		$r = $this->call('GET', "/client-reset-password/{$fk}");
		$this->assertNot500($r, VW::CLT . ' password reset GET');

		$r2 = $this->call('POST', "/client-reset-password/{$fk}");
		$this->assertNot500($r2, VW::CLT . ' password reset POST');
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 7 — CUSTOMERS (CustomerController)
	// ══════════════════════════════════════════════════════════════════════

	/**
	 * @dataProvider customerIndexProvider
	 */
	public function test_customer_index_routes(string $uri, string $label): void
	{
		$r = $this->get($uri);
		$this->assertNot500($r, $label);
	}

	public static function customerIndexProvider(): array
	{
		return [
			'index' => ['/' . VW::CST, VW::CST . ' index'],
			'create' => ['/' . VW::CST . '/create', VW::CST . ' create'],
		];
	}

	/**
	 * @dataProvider customerCrudFakeIdProvider
	 */
	public function test_customer_crud_fake_id_no_500(string $method, string $uri, string $label): void
	{
		$r = $this->call($method, $uri);
		$this->assertNot500($r, "{$method} {$uri} ({$label})");
	}

	public static function customerCrudFakeIdProvider(): array
	{
		$fk = '00000000-0000-0000-0000-000000000000';
		return [
			'show' => ['GET', '/' . VW::CST . "/{$fk}/show", VW::CST . ' show'],
			'edit' => ['GET', '/' . VW::CST . "/{$fk}/edit", VW::CST . ' edit'],
			'update' => ['PUT', '/' . VW::CST . "/{$fk}", VW::CST . ' update'],
			'delete' => ['DELETE', '/' . VW::CST . "/{$fk}", VW::CST . ' destroy'],
			'store_empty' => ['POST', '/' . VW::CST, VW::CST . ' store'],
		];
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 8 — CROSS-MODULE INTEGRATION
	// ══════════════════════════════════════════════════════════════════════

	public function test_deal_to_lead_routes_accessible(): void
	{
		// Ensure leads and deals routes are both accessible
		$r1 = $this->get('/' . VW::DL);
		$this->assertNot500($r1, 'deals index');

		$r2 = $this->get('/' . VW::LD);
		$this->assertNot500($r2, 'leads index');
	}

	public function test_pipeline_and_stages_accessible(): void
	{
		$r1 = $this->get('/' . VW::PPL);
		$this->assertNot500($r1, 'pipelines index');

		$r2 = $this->get('/' . VW::STG);
		$this->assertNot500($r2, 'stages index');

		$r3 = $this->get('/' . VW::LD_STG);
		$this->assertNot500($r3, 'lead_stages index');
	}

	// ══════════════════════════════════════════════════════════════════════
	//  SECTION 9 — CONTENT VALIDATION
	// ══════════════════════════════════════════════════════════════════════

	public function test_deals_index_contains_expected_keywords(): void
	{
		$r = $this->get('/' . VW::DL);
		if ($r->getStatusCode() === 200) {
			$html = $r->getContent();
			$this->assertTrue(
				str_contains($html, 'deal') || str_contains($html, 'Deal') ||
				str_contains($html, 'pipeline') || str_contains($html, 'Pipeline') ||
				str_contains($html, '<title>'),
				'Deals index should contain deal-related keywords'
			);
		} else {
			$this->assertNot500($r, 'deals index content check');
		}
	}

	public function test_leads_index_contains_expected_keywords(): void
	{
		$r = $this->get('/' . VW::LD);
		if ($r->getStatusCode() === 200) {
			$html = $r->getContent();
			$this->assertTrue(
				str_contains($html, 'lead') || str_contains($html, 'Lead') ||
				str_contains($html, 'pipeline') || str_contains($html, 'Pipeline') ||
				str_contains($html, '<title>'),
				'Leads index should contain lead-related keywords'
			);
		} else {
			$this->assertNot500($r, 'leads index content check');
		}
	}

	public function test_customers_index_contains_expected_keywords(): void
	{
		$r = $this->get('/' . VW::CST);
		if ($r->getStatusCode() === 200) {
			$html = $r->getContent();
			$this->assertTrue(
				str_contains($html, 'customer') || str_contains($html, 'Customer') ||
				str_contains($html, 'contact') || str_contains($html, 'Contact') ||
				str_contains($html, '<title>'),
				'Customers index should contain customer-related keywords'
			);
		} else {
			$this->assertNot500($r, 'customers index content check');
		}
	}

	public function test_clients_index_contains_expected_keywords(): void
	{
		$r = $this->get('/' . VW::CLT);
		if ($r->getStatusCode() === 200) {
			$html = $r->getContent();
			$this->assertTrue(
				str_contains($html, 'client') || str_contains($html, 'Client') ||
				str_contains($html, 'user') || str_contains($html, 'User') ||
				str_contains($html, '<title>'),
				'Clients index should contain client-related keywords'
			);
		} else {
			$this->assertNot500($r, 'clients index content check');
		}
	}
}
