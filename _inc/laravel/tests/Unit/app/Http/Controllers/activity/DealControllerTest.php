<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\DealController;
use App\Mail\SendDealEmail;
use App\Models\{
	ClientDeal,
	CustomField,
	Deal,
	DealCall,
	DealFile,
	DealTask,
	Label,
	Pipeline,
	ProductService,
	Source,
	Stage,
	Task,
	User,
	UserDeal
};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{Request, RedirectResponse, UploadedFile};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Gate, Mail, Storage};
use ReflectionMethod;

class DealControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private Deal $deal;

	protected function setUp(): void
	{
		parent::setUp();

		// allow or deny in each test
		Gate::before(fn () => true);

		// creatorId macro if needed elsewhere
		User::macro(
			'creatorId',
			/**
			 ** @this \App\Models\User
			 ** @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		// authenticate a user
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		// create an active deal
		$this->deal = Deal::factory()->create(['is_active' => true]);
	}


	/**
	 ** Invoke a non-public method via reflection.
	 ** 
	 ** @param object $instance  The controller instance.
	 ** @param string $method    The protected/private method name.
	 ** @param array  $args      Arguments to pass to the method.
	 ** @return mixed            The method’s return value.
	 **/
	protected function call_hidden_method(object $instance, string $method, array $args = [])
	{
		$ref = new ReflectionMethod($instance, $method);
		$ref->setAccessible(true);
		return $ref->invokeArgs($instance, $args);
	}

	/**
	 ** @test
	 **
	 ** getDefaultPipeline should return the user's explicitly set default
	 ** pipeline when it exists.
	 **/
	public function test_get_default_pipeline_returns_user_default_if_present()
	{
		$user   = User::factory()->create(['default_pipeline' => null]);
		$first  = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);
		$second = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);
		$user->update(['default_pipeline' > $second->id]);

		$controller = new DealController();
		$pipeline  = $this->call_hidden_method($controller, 'getDefaultPipeline', [$user]);

		$this->assertEquals($second->id, $pipeline->id);
	}

	/**
	 ** @test
	 **
	 ** getDefaultPipeline should fall back to the first pipeline
	 ** when the stored default_pipeline ID is not found.
	 **/
	public function test_get_default_pipeline_falls_back_to_first_if_default_not_found()
	{
		$user = User::factory()->create(['default_pipeline' => 999]);
		$first = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);

		$controller = new DealController();
		$pipeline  = $this->call_hidden_method($controller, 'getDefaultPipeline', [$user]);

		$this->assertEquals($first->id, $pipeline->id);
	}

	/**
	 ** @test
	 **
	 ** getDefaultPipeline should throw a RuntimeException
	 ** when the user has no pipelines at all.
	 **/
	public function test_get_default_pipeline_throws_when_no_pipelines_exist()
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('No pipeline found for user');

		$user      = User::factory()->create(['default_pipeline' => null]);
		$controller = new DealController();
		$this->call_hidden_method($controller, 'getDefaultPipeline', [$user]);
	}

	/**
	 ** @test
	 **
	 ** authorizeOwner should return null (allow) when the user owns the deal.
	 **/
	public function test_authorize_owner_allows_when_owner_matches()
	{
		$user = User::factory()->create();
		$deal = Deal::factory()->create(['created_by' => $user?->ownerId()]);

		$req = \Illuminate\Http\Request::create('/');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $this->call_hidden_method(
			$controller,
			'authorizeOwner',
			[$req, $deal, 'edit deal']
		);

		$this->assertNull($response);
	}

	/**
	 ** @test
	 **
	 ** authorizeOwner should throw AuthorizationException
	 ** when the user does not own the deal.
	 **/
	public function test_authorize_owner_denies_when_owner_mismatch()
	{
		$this->expectException(AuthorizationException::class);

		$user     = User::factory()->create();
		$otherUser = User::factory()->create();
		$deal     = Deal::factory()->create(['created_by' => $otherUser->ownerId()]);

		$req = \Illuminate\Http\Request::create('/');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$this->call_hidden_method(
			$controller,
			'authorizeOwner',
			[$req, $deal, 'edit deal']
		);
	}

	/**
	 ** @test
	 **
	 ** index should render the deals overview with
	 ** pipelines, the selected pipeline, and deal counts.
	 **/
	public function test_index_displays_deals_summary_and_pipelines()
	{
		$user    = User::factory()->create(['type' => 'company']);
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		Stage::factory()->create(['pipeline_id' => $pipeline->id]);

		$deal = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => Stage::first()->id,
			'created_by'  => $ownerId,
		]);
		DB::table('user_deals')->insert([
			'user_id' => $user?->id,
			'deal_id' => $deal->id,
		]);

		$req = Request::create('/deals', 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->index($req);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$data = $response->getData();
		$this->assertArrayHasKey('pipelines', $data);
		$this->assertArrayHasKey('pipeline', $data);
		$this->assertArrayHasKey('cntDeal', $data);
		$this->assertEquals(
			['total' => Deal::getDealSummary(collect([$deal]))],
			$data['cntDeal']
		);
	}

	/**
	 ** @test
	 **
	 ** dealList should render a list of deals ordered
	 ** by the 'order' column ascending.
	 **/
	public function test_deal_list_displays_deals_list()
	{
		$user    = User::factory()->create(['type' => 'company']);
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);

		$deal1 = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'order'       => 10,
			'created_by'  => $ownerId,
		]);
		$deal2 = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'order'       => 5,
			'created_by'  => $ownerId,
		]);
		DB::table('user_deals')->insert([
			['user_id' => $user?->id, 'deal_id' => $deal1->id],
			['user_id' => $user?->id, 'deal_id' => $deal2->id],
		]);

		$req = Request::create('/deals/list', 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->dealList($req);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$data = $response->getData();
		$this->assertArrayHasKey('deals', $data);
		$this->assertCount(2, $data['deals']);
		$this->assertEquals($deal2->id, $data['deals']->first()->id);
	}

	/**
	 ** @test
	 **
	 ** create should render the new-deal form
	 ** including clients and custom fields.
	 **/
	public function test_create_displays_clients_and_custom_fields()
	{
		$user   = User::factory()->create();
		$ownerId = $user?->ownerId();
		$client = User::factory()->create(['type' => 'client', 'created_by' => $ownerId]);
		CustomField::create([
			'module'     => 'deal',
			'created_by' => $ownerId,
		]);

		$req = Request::create('/deals/create', 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->create($req);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$this->assertEquals('deals.create', $response->getName());
		$data = $response->getData();
		$this->assertArrayHasKey('clients', $data);
		$this->assertArrayHasKey('customFields', $data);
		$this->assertTrue($data['clients']->contains($client->id));
		$this->assertCount(1, $data['customFields']);
	}

	/**
	 ** @test
	 **
	 ** store should persist a new deal and
	 ** assign the selected clients and current user.
	 **/
	public function test_store_creates_deal_and_assigns_clients_and_users()
	{
		$user    = User::factory()->create(['type' => 'company']);
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$client  = User::factory()->create(['type' => 'client', 'created_by' => $ownerId]);

		$req = Request::create('/deals', 'POST', [
			'name'    => 'Big Deal',
			'phone'   => '555-1234',
			'price'   => 5000,
			'clients' => [$client->id],
		]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->store($req);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseHas('deals', [
			'name'        => 'Big Deal',
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$dealId = DB::table('deals')->where('name', 'Big Deal')->value('id');
		$this->assertDatabaseHas('client_deals', ['deal_id' => $dealId, 'client_id' => $client->id]);
		$this->assertDatabaseHas('user_deals',   ['deal_id' => $dealId, 'user_id' => $user?->id]);
	}

	/**
	 ** @test
	 **
	 ** edit should render the edit form with pipelines, sources, products, and custom fields.
	 **/
	public function test_edit_displays_edit_form()
	{
		$user     = User::factory()->create();
		$ownerId  = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage    = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal     = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'sources'     => '1,2',
			'products'    => '3,4',
			'created_by'  => $ownerId,
		]);
		Source::factory()->count(2)->create(['created_by' => $ownerId, 'pipeline_id' => $pipeline->id]);
		ProductService::factory()->count(2)->create(['created_by' => $ownerId]);
		\App\Models\CustomField::create(['module' => 'deal', 'created_by' => $ownerId]);

		$req = Request::create("/deals/{$deal->id}/edit", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->edit($req, $deal);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$data = $response->getData();
		$this->assertEquals($deal->id, $data['deal']->id);
		$this->assertArrayHasKey('pipelines', $data);
		$this->assertArrayHasKey('sources', $data);
		$this->assertArrayHasKey('products', $data);
	}

	/**
	 ** @test
	 **
	 ** update should persist changes to the deal and redirect.
	 **/
	public function test_update_changes_deal()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage1  = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$stage2  = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage1->id,
			'name'        => 'Old Name',
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}", 'POST', [
			'name'        => 'New Name',
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage2->id,
			'sources'     => [],
			'products'    => [],
			'notes'       => 'Updated notes',
		]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->update($req, $deal);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseHas('deals', [
			'id'       => $deal->id,
			'name'     => 'New Name',
			'stage_id' => $stage2->id,
			'notes'    => 'Updated notes',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the deal (and related discussions) and redirect.
	 **/
	public function test_destroy_deletes_deal()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		\App\Models\DealDiscussion::factory()->create(['deal_id' => $deal->id]);

		$req = Request::create("/deals/{$deal->id}", 'DELETE');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->destroy($req, $deal);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseMissing('deals', ['id' => $deal->id]);
	}

	/**
	 ** @test
	 **
	 ** order should move deals between stages, reorder them, and return success JSON.
	 **/
	public function test_order_moves_and_reorders_deals()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stageA  = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$stageB  = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal1   = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stageA->id,
			'order'       => 0,
			'created_by'  => $ownerId,
		]);
		$deal2   = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stageA->id,
			'order'       => 1,
			'created_by'  => $ownerId,
		]);

		$req = Request::create('/deals/order', 'POST', [
			'deal_id'  => $deal1->id,
			'stage_id' => $stageB->id,
			'order'    => [$deal2->id, $deal1->id],
		]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$json      = $controller->order($req);

		$this->assertEquals(['success' => true], $json->getData(true));
		$this->assertDatabaseHas('deals', [
			'id'       => $deal2->id,
			'stage_id' => $stageB->id,
			'order'    => 0,
		]);
		$this->assertDatabaseHas('deals', [
			'id'       => $deal1->id,
			'stage_id' => $stageB->id,
			'order'    => 1,
		]);
	}

	/**
	 ** @test
	 **
	 ** labels should render form with all labels and selected ones.
	 **/
	public function test_labels_displays_labels_form()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'labels'      => '5,6',
			'created_by'  => $ownerId,
		]);
		Label::factory()->create(['pipeline_id' => $pipeline->id, 'created_by' => $ownerId]);
		Label::factory()->create(['pipeline_id' => $pipeline->id, 'created_by' => $ownerId]);

		$req = Request::create("/deals/{$deal->id}/labels", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->labels($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$data = $response->getData();
		$this->assertEquals($deal->id, $data['deal']->id);
		$this->assertCount(2, $data['labels']);
		$this->assertEquals([5, 6], $data['selected']);
	}

	/**
	 ** @test
	 **
	 ** labelStore should update deal labels and redirect.
	 **/
	public function test_label_store_updates_labels()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'labels'      => null,
			'created_by'  => $ownerId,
		]);
		Label::factory()->count(3)->create(['pipeline_id' => $pipeline->id, 'created_by' => $ownerId]);

		$req = Request::create("/deals/{$deal->id}/labels", 'POST', ['labels' => [1, 2]]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->labelStore($req, $deal->id);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseHas('deals', ['id' => $deal->id, 'labels' => '1,2']);
	}

	/**
	 ** @test
	 **
	 ** userEdit should render the user-assignment form.
	 **/
	public function test_user_edit_shows_users_form()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		UserDeal::create(['deal_id' => $deal->id, 'user_id' => $user?->id]);
		$other = User::factory()->create(['created_by' => $ownerId]);

		$req = Request::create("/deals/{$deal->id}/users", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->userEdit($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$data = $response->getData();
		$this->assertEquals($deal->id, $data['deal']->id);
		$this->assertArrayHasKey('users', $data);
		$this->assertTrue($data['users']->has($other->id));
	}

	/**
	 ** @test
	 **
	 ** userUpdate should assign selected users to the deal and redirect.
	 **/
	public function test_user_update_assigns_users_to_deal()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create(['pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'created_by' => $ownerId]);
		$newUser = User::factory()->create(['created_by' => $ownerId]);

		$req = Request::create("/deals/{$deal->id}/users", 'POST', ['users' => [$newUser->id]]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->userUpdate($req, $deal->id);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseHas('user_deals', ['deal_id' => $deal->id, 'user_id' => $newUser->id]);
	}

	/** @test
	 **
	 ** Removing a user from a deal should return a RedirectResponse
	 ** and delete the corresponding UserDeal record.
	 **/
	public function test_user_destroy_removes_user_from_deal()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		UserDeal::create(['deal_id' => $deal->id, 'user_id' => $user?->id]);

		$req = Request::create("/deals/{$deal->id}/users/{$user?->id}", 'DELETE');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->userDestroy($req, $deal->id, $user?->id);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseMissing('user_deals', [
			'deal_id' => $deal->id,
			'user_id' => $user?->id,
		]);
	}

	/** @test
	 **
	 ** Displaying the client edit form should return a View
	 ** with the deal and only unassigned clients.
	 **/
	public function test_client_edit_shows_clients_form()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$client  = User::factory()->create(['created_by' => $ownerId, 'type' => 'client']);
		ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $client->id]);
		$other   = User::factory()->create(['created_by' => $ownerId, 'type' => 'client']);

		$req = Request::create("/deals/{$deal->id}/clients", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->clientEdit($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$data = $response->getData();
		$this->assertEquals($deal->id, $data['deal']->id);
		$this->assertTrue($data['clients']->has($other->id));
	}

	/** @test
	 **
	 ** Assigning clients to a deal via clientUpdate should
	 ** return a RedirectResponse and create ClientDeal rows.
	 **/
	public function test_client_update_assigns_clients_to_deal()
	{
		$user     = User::factory()->create();
		$ownerId  = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage    = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal     = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$newClient = User::factory()->create(['created_by' => $ownerId, 'type' => 'client']);

		$req = Request::create(
			"/deals/{$deal->id}/clients",
			'POST',
			['clients' => [$newClient->id]]
		);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->clientUpdate($req, $deal->id);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseHas('client_deals', [
			'deal_id'   => $deal->id,
			'client_id' => $newClient->id,
		]);
	}

	/** @test
	 **
	 ** Removing a client from a deal should return a RedirectResponse
	 ** and delete the corresponding ClientDeal record.
	 **/
	public function test_client_destroy_removes_client_from_deal()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $user?->id]);

		$req = Request::create("/deals/{$deal->id}/clients/{$user?->id}", 'DELETE');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->clientDestroy($req, $deal->id, $user?->id);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$this->assertDatabaseMissing('client_deals', [
			'deal_id'   => $deal->id,
			'client_id' => $user?->id,
		]);
	}

	/** @test
	 **
	 ** The product edit form should return a View
	 ** with only unassigned products for the deal.
	 **/
	public function test_product_edit_shows_products_form()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'products'    => '10,11',
			'created_by'  => $ownerId,
		]);
		ProductService::factory()->count(3)->create(['created_by' => $ownerId]);

		$req = Request::create("/deals/{$deal->id}/products", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->productEdit($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $response);
		$data = $response->getData();
		$this->assertEquals($deal->id, $data['deal']->id);
		$this->assertCount(2, $data['products']);
	}

	/** @test
	 **
	 ** Adding products to a deal via productUpdate should
	 ** return a RedirectResponse and append product IDs.
	 **/
	public function test_product_update_adds_products_to_deal()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'products'    => '20',
			'created_by'  => $ownerId,
		]);
		$new     = ProductService::factory()->create(['created_by' => $ownerId]);

		$req = Request::create(
			"/deals/{$deal->id}/products",
			'POST',
			['products' => [$new->id]]
		);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->productUpdate($req, $deal->id);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$deal->refresh();
		$this->assertStringContainsString((string)$new->id, $deal->products);
	}

	/** @test
	 **
	 ** Removing a product from a deal via productDestroy should
	 ** return a RedirectResponse and remove the product ID.
	 **/
	public function test_product_destroy_removes_product_from_deal()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$pid     = 30;
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'products'    => "{$pid},31",
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}/products/{$pid}", 'DELETE');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->productDestroy($req, $deal->id, $pid);

		$this->assertInstanceOf(RedirectResponse::class, $response);
		$deal->refresh();
		$this->assertStringNotContainsString("{$pid}", $deal->products);
	}

	/** @test
	 **
	 ** Uploading a file to a deal should store the file,
	 ** return JSON with is_success true, and insert a DealFile.
	 **/
	public function test_file_upload_stores_file_and_returns_success_json()
	{
		Storage::fake('deal_files');
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);

		$file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);
		$req = Request::create("/deals/{$deal->id}/file-upload", 'POST', [], [], ['file' => $file]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->fileUpload($req, $deal->id);

		$response->assertJson(['is_success' => true]);
		$this->assertDatabaseCount('deal_files', 1);
	}

	/** @test
	 **
	 ** Downloading a deal file should return a BinaryFileResponse.
	 **/
	public function test_file_download_returns_binary_response()
	{
		Storage::fake('deal_files');
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);

		$file = DealFile::create([
			'deal_id'   => $deal->id,
			'file_name' => 'notes.txt',
			'file_path' => 'notes_dummy.txt',
		]);
		Storage::disk('deal_files')->put('notes_dummy.txt', 'content');

		$req = Request::create("/deals/{$deal->id}/file-download/{$file->id}", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->fileDownload($req, $deal->id, $file->id);

		$this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $resp);
	}

	/** @test
	 **
	 ** Deleting a deal file should return JSON is_success true
	 ** and remove the DealFile record.
	 **/
	public function test_file_delete_deletes_file_and_returns_success_json()
	{
		Storage::fake('deal_files');
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);

		$file = DealFile::create([
			'deal_id'   => $deal->id,
			'file_name' => 'report.docx',
			'file_path' => 'report_dummy.docx',
		]);
		Storage::disk('deal_files')->put('report_dummy.docx', 'data');

		$req = Request::create("/deals/{$deal->id}/file-delete/{$file->id}", 'DELETE');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->fileDelete($req, $deal->id, $file->id);

		$response->assertJson(['is_success' => true]);
		$this->assertDatabaseMissing('deal_files', ['id' => $file->id]);
	}

	/** @test
	 **
	 ** Storing a note on a deal should update the notes field
	 ** and return JSON is_success true.
	 **/
	public function test_note_store_updates_notes_field()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'notes'       => '',
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}/note", 'POST', ['notes' => 'Important note']);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$response  = $controller->noteStore($req, $deal->id);

		$this->assertEquals('Important note', Deal::find($deal->id)->notes);
		$response->assertJson(['is_success' => true]);
	}

	/** @test
	 **
	 ** The taskCreate endpoint should return a View
	 ** containing priorities and status arrays.
	 **/
	public function test_task_create_returns_view_with_priorities_and_status()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}/tasks/create", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->taskCreate($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $resp);
		$data = $resp->getData();
		$this->assertArrayHasKey('priorities', $data);
		$this->assertArrayHasKey('status', $data);
	}

	/** @test
	 **
	 ** Storing a task on a deal should persist it and
	 ** flash a success message to session.
	 **/
	public function test_task_store_creates_task_and_logs()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);

		$payload = [
			'name'     => 'Follow up',
			'date'     => now()->toDateString(),
			'time'     => now()->format('H:i'),
			'priority' => DealTask::$priorities[0],
			'status'   => DealTask::$status[0],
		];
		$req = Request::create("/deals/{$deal->id}/tasks", 'POST', $payload);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->taskStore($req, $deal->id);

		$this->assertDatabaseHas('deal_tasks', [
			'deal_id' => $deal->id,
			'name'    => 'Follow up',
		]);
		$this->assertTrue(session()->has('success'));
	}

	/** @test
	 **
	 ** Showing a task should return a View with the task data.
	 **/
	public function test_task_show_returns_task_view()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$task    = DealTask::factory()->create(['deal_id' => $deal->id]);

		$req = Request::create("/deals/{$deal->id}/tasks/{$task->id}", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->taskShow($req, $deal->id, $task->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $resp);
	}

	/** @test
	 **
	 ** Editing a task should return the edit View for that task.
	 **/
	public function test_task_edit_returns_edit_view()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$task    = DealTask::factory()->create(['deal_id' => $deal->id]);

		$req = Request::create("/deals/{$deal->id}/tasks/{$task->id}/edit", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->taskEdit($req, $deal->id, $task->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $resp);
	}

	/** @test
	 **
	 ** Updating a task via taskUpdate should change its fields in DB.
	 **/
	public function test_task_update_changes_task()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$task    = DealTask::factory()->create(['deal_id' => $deal->id]);

		$payload = [
			'name'     => 'Updated task',
			'date'     => now()->toDateString(),
			'time'     => now()->format('H:i'),
			'priority' => DealTask::$priorities[1],
			'status'   => DealTask::$status[1],
		];
		$req = Request::create("/deals/{$deal->id}/tasks/{$task->id}", 'PUT', $payload);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->taskUpdate($req, $deal->id, $task->id);

		$this->assertDatabaseHas('deal_tasks', [
			'id'   => $task->id,
			'name' => 'Updated task',
		]);
	}

	/** @test
	 **
	 ** Toggling a task's status via taskUpdateStatus should return JSON
	 ** with is_success true and flip the status in DB.
	 **/
	public function test_task_update_status_toggles_and_returns_json()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$task    = DealTask::factory()->create(['deal_id' => $deal->id, 'status' => 0]);

		$req = Request::create("/deals/{$deal->id}/tasks/{$task->id}/status", 'PATCH', ['status' => 0]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->taskUpdateStatus($req, $deal->id, $task->id);

		$data = $resp->getData(true);
		$this->assertTrue($data['is_success']);
		$this->assertDatabaseHas('deal_tasks', ['id' => $task->id, 'status' => 1]);
	}

	/**
	 ** @test
	 **
	 ** Deleting a task should remove it from the database
	 ** and return a RedirectResponse.
	 **/
	public function test_task_destroy_deletes_task_and_redirects()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$task = DealTask::factory()->create(['deal_id' => $deal->id]);

		$req = Request::create("/deals/{$deal->id}/tasks/{$task->id}", 'DELETE');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->taskDestroy($req, $deal->id, $task->id);

		$this->assertInstanceOf(RedirectResponse::class, $resp);
		$this->assertDatabaseMissing('deal_tasks', ['id' => $task->id]);
	}

	/**
	 ** @test
	 **
	 ** The source edit form should be returned as a View
	 ** when a deal has existing sources.
	 **/
	public function test_source_edit_returns_view()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'sources'     => '1,2',
			'created_by'  => $ownerId,
		]);
		Source::factory()->count(3)->create(['created_by' => $ownerId]);

		$req = Request::create("/deals/{$deal->id}/sources", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->sourceEdit($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** Updating sources should append new source IDs to the deal
	 ** and preserve existing ones.
	 **/
	public function test_source_update_updates_sources_and_logs()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'sources'     => '1',
			'created_by'  => $ownerId,
		]);
		$new = Source::factory()->create(['created_by' => $ownerId]);

		$req = Request::create(
			"/deals/{$deal->id}/sources",
			'POST',
			['sources' => [$new->id]]
		);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->sourceUpdate($req, $deal->id);

		$deal->refresh();
		$this->assertStringContainsString((string)$new->id, $deal->sources);
	}

	/**
	 ** @test
	 **
	 ** Removing a source from a deal should strip that ID
	 ** from the comma-separated list.
	 **/
	public function test_source_destroy_removes_source()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$sid     = 5;
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'sources'     => "{$sid},6",
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}/sources/{$sid}", 'DELETE');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->sourceDestroy($req, $deal->id, $sid);

		$deal->refresh();
		$this->assertStringNotContainsString("{$sid}", $deal->sources);
	}

	/**
	 ** @test
	 **
	 ** The permission edit form should return a View
	 ** for assigning permissions to a client on a deal.
	 **/
	public function test_permission_edit_returns_view()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$client = User::factory()->create(['created_by' => $ownerId, 'type' => 'client']);

		$req = Request::create("/deals/{$deal->id}/permission/{$client->id}", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->permission($req, $deal->id, $client->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** Storing permissions for a client should insert or update
	 ** the client_permissions record accordingly.
	 **/
	public function test_permission_store_creates_or_updates_permissions()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		$client = User::factory()->create(['created_by' => $ownerId, 'type' => 'client']);
		$perms = array_keys(Deal::$permissions);

		$req = Request::create(
			"/deals/{$deal->id}/permission/{$client->id}",
			'POST',
			['permissions' => [$perms[0], $perms[1]]]
		);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->permissionStore($req, $deal->id, $client->id);

		$this->assertDatabaseHas('client_permissions', [
			'deal_id'     => $deal->id,
			'client_id'   => $client->id,
			'permissions' => implode(',', [$perms[0], $perms[1]]),
		]);
	}

	/**
	 ** @test
	 **
	 ** The jsonUser endpoint should return a JSON map
	 ** of user IDs to names for a given deal.
	 **/
	public function test_json_user_returns_users_list()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		UserDeal::create(['deal_id' => $deal->id, 'user_id' => $user?->id]);

		$req = Request::create('/deals/json-user', 'GET', ['deal_id' => $deal->id]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->jsonUser($req);

		$resp->assertJsonFragment([$user->id > $user?->name]);
	}

	/**
	 ** @test
	 **
	 ** Changing the default pipeline should update
	 ** the user's default_pipeline field.
	 **/
	public function test_change_pipeline_updates_user_default_pipeline()
	{
		$user       = User::factory()->create();
		$newPipeline = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);

		$req = Request::create('/deals/change-pipeline', 'POST', ['defaultPipelineId' => $newPipeline->id]);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->changePipeline($req);

		$this->assertEquals($newPipeline->id, $user?->fresh()->default_pipeline);
	}

	/**
	 ** @test
	 **
	 ** The discussionCreate endpoint should return a View
	 ** for adding a new discussion to a deal.
	 **/
	public function test_discussion_create_returns_view()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}/discussion/create", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->discussionCreate($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** Storing a discussion should insert a record
	 ** and return a RedirectResponse.
	 **/
	public function test_discussion_store_creates_discussion_and_redirects()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}/discussion", 'POST', ['comment' => 'New message']);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->discussionStore($req, $deal->id);

		$this->assertDatabaseHas('deal_discussions', [
			'deal_id' => $deal->id,
			'comment' => 'New message',
		]);
		$this->assertInstanceOf(RedirectResponse::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** Posting to changeStatus should update the deal's status
	 ** and return a RedirectResponse.
	 **/
	public function test_change_status_updates_deal_status()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'status'      => 'Active',
			'created_by'  => $ownerId,
		]);

		$req = Request::create("/deals/{$deal->id}/status", 'POST', ['dealStatus' => 'Won']);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->changeStatus($req, $deal->id);

		$this->assertEquals('Won', $deal->fresh()->status);
		$this->assertInstanceOf(RedirectResponse::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** The callCreate endpoint should return a View
	 ** for scheduling a new call on a deal.
	 **/
	public function test_call_create_returns_view()
	{
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		UserDeal::create(['deal_id' => $deal->id, 'user_id' => $user?->id]);

		$req = Request::create("/deals/{$deal->id}/call/create", 'GET');
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->callCreate($req, $deal->id);

		$this->assertInstanceOf(\Illuminate\View\View::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** Storing a call should persist it in deal_calls
	 ** and return a RedirectResponse.
	 **/
	public function test_call_store_creates_call_and_logs()
	{
		Mail::fake();
		$user    = User::factory()->create();
		$ownerId = $user?->ownerId();
		$pipeline = Pipeline::factory()->create(['created_by' => $ownerId]);
		$stage   = Stage::factory()->create(['pipeline_id' => $pipeline->id]);
		$deal    = Deal::factory()->create([
			'pipeline_id' => $pipeline->id,
			'stage_id'    => $stage->id,
			'created_by'  => $ownerId,
		]);
		UserDeal::create(['deal_id' => $deal->id, 'user_id' => $user?->id]);

		$payload = [
			'subject'    => 'Call subject',
			'callType'   => 'Outbound',
			'userId'     => $user?->id,
			'duration'   => '00:10:00',
			'description' => 'Discuss details',
			'callResult' => 'Connected',
		];
		$req = Request::create("/deals/{$deal->id}/call", 'POST', $payload);
		$req->setUserResolver(fn () => $user);

		$controller = new DealController();
		$resp      = $controller->callStore($req, $deal->id);

		$this->assertDatabaseHas('deal_calls', [
			'deal_id' => $deal->id,
			'subject' => 'Call subject',
		]);
		$this->assertInstanceOf(RedirectResponse::class, $resp);
	}

	/**
	 ** @test
	 **
	 ** It should render the deal show view
	 ** with tasks when deal is active and user has permissions.
	 **/
	public function show_displays_view_for_active_deal_with_tasks()
	{
		// grant 'view deal' and 'view task' permissions
		Gate::forUser($this->user)->define('view deal', fn () => true);
		Gate::forUser($this->user)->define('view task', fn () => true);

		// attach two tasks to the deal
		Task::factory()->count(2)->create(['deal_id' => $this->deal->id]);

		$response = $this->get(route('deals.show', $this->deal));

		$response->assertStatus(200)
			->assertViewIs('deals.show')
			->assertViewHas('deal', fn ($d) => $d->id === $this->deal->id)
			->assertViewHas('calendarTasks', fn ($tasks) => count($tasks) === 2);
	}

	/**
	 ** @test
	 **
	 ** It should redirect back with error
	 ** when the deal is inactive.
	 **/
	public function show_redirects_back_for_inactive_deal()
	{
		Gate::forUser($this->user)->define('view deal', fn () => true);

		// mark deal inactive
		$this->deal->update(['is_active' => false]);

		$response = $this->get(route('deals.show', $this->deal));

		$response->assertRedirect()
			->assertSessionHas('error', __('Permission Denied.'));
	}

	/**
	 ** @test
	 **
	 ** It should deny access with 403
	 ** when the user lacks 'view deal' permission.
	 **/
	public function show_denies_access_without_permission()
	{
		Gate::before(fn () => false);

		$response = $this->get(route('deals.show', $this->deal));

		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** The deal() helper should cache and return the same instance.
	 **/
	public function deal_helper_caches_and_returns_deal()
	{
		$controller = app(\App\Http\Controllers\DealController::class);

		$first = $controller->deal($this->deal->id);
		$second = $controller->deal($this->deal->id);

		$this->assertSame($first, $second);
		$this->assertEquals($this->deal->id, $first->id);
	}
}
