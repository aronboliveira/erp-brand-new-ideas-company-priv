<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsTo, BelongsToMany};
use Illuminate\Support\Collection;
use App\Models\{
	Lead,
	Label,
	LeadStage,
	LeadFile,
	Pipeline,
	ProductService,
	Source,
	User,
	LeadActivityLog,
	LeadDiscussion,
	LeadCall,
	LeadEmail
};

class LeadTest extends TestCase
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
	 ** Lead is mass assignable for all fillable fields
	 **/
	public function lead_is_fillable()
	{
		$data = [
			'name'         => 'Acme Lead',
			'email'        => 'lead@example.com',
			'phone'        => '1234567890',
			'subject'      => 'New Opportunity',
			'user_id'      => User::factory()->create()->id,
			'pipeline_id'  => Pipeline::factory()->create()->id,
			'stage_id'     => LeadStage::factory()->create()->id,
			'sources'      => '1,2',
			'products'     => '3,4',
			'notes'        => 'Some notes',
			'labels'       => '5,6',
			'order'        => 2,
			'created_by'   => 'admin',
			'is_active'    => true,
			'is_converted' => false,
			'date'         => '2025-05-24',
		];

		$lead = Lead::create($data);

		$this->assertFillableMatches($data, $lead);
	}

	/**
	 ** @test
	 **
	 ** Lead uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function lead_uses_uuid_for_primary_key()
	{
		$lead = Lead::create([
			'name'         => 'Test',
			'email'        => 'a@b.com',
			'phone'        => '000',
			'subject'      => 'S',
			'user_id'      => User::factory()->create()->id,
			'pipeline_id'  => Pipeline::factory()->create()->id,
			'stage_id'     => LeadStage::factory()->create()->id,
			'sources'      => '',
			'products'     => '',
			'notes'        => '',
			'labels'       => '',
			'order'        => 1,
			'created_by'   => 'u',
			'is_active'    => false,
			'is_converted' => false,
			'date'         => '2025-05-24',
		]);

		$key = $lead->getKey();

		$this->assertIsString($key);
		$this->assertFalse($lead->getIncrementing());
		$this->assertSame('string', $lead->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** labels() returns empty Collection when no labels set
	 **/
	public function labels_returns_empty_collection_if_no_labels()
	{
		$lead = Lead::factory()->create(['labels' => null]);
		$this->assertInstanceOf(Collection::class, $lead->labelRecords());
		$this->assertTrue($lead->labelRecords()->isEmpty());
	}

	/**
	 ** @test
	 **
	 ** labels() returns Label models when labels CSV provided
	 **/
	public function labels_returns_models_for_label_ids()
	{
		$labels = Label::factory()->count(2)->create();
		$lead  = Lead::factory()->create([
			'labels' => implode(',', $labels->pluck('id')->toArray())
		]);

		$col = $lead->labelRecords();
		$this->assertInstanceOf(Collection::class, $col);
		$this->assertCount(2, $col);
		$this->assertTrue($col->pluck('id')->sort()->values()->all() === $labels->pluck('id')->sort()->values()->all());
	}

	/**
	 ** @test
	 **
	 ** sources() returns empty Collection when no sources set
	 **/
	public function sources_returns_empty_collection_if_no_sources()
	{
		$lead = Lead::factory()->create(['sources' => null]);
		$this->assertInstanceOf(Collection::class, $lead->sources());
		$this->assertTrue($lead->sources()->isEmpty());
	}

	/**
	 ** @test
	 **
	 ** sources() returns Source models when sources CSV provided
	 **/
	public function sources_returns_models_for_source_ids()
	{
		$items = Source::factory()->count(2)->create();
		$lead = Lead::factory()->create([
			'sources' => implode(',', $items->pluck('id')->toArray())
		]);

		$col = $lead->sources();
		$this->assertInstanceOf(Collection::class, $col);
		$this->assertCount(2, $col);
		$this->assertTrue($col->pluck('id')->sort()->values()->all() === $items->pluck('id')->sort()->values()->all());
	}

	/**
	 ** @test
	 **
	 ** products() returns empty Collection when no products set
	 **/
	public function products_returns_empty_collection_if_no_products()
	{
		$lead = Lead::factory()->create(['products' => null]);
		$this->assertInstanceOf(Collection::class, $lead->products());
		$this->assertTrue($lead->products()->isEmpty());
	}

	/**
	 ** @test
	 **
	 ** products() returns ProductService models when products CSV provided
	 **/
	public function products_returns_models_for_product_ids()
	{
		$items = ProductService::factory()->count(2)->create();
		$lead = Lead::factory()->create([
			'products' => implode(',', $items->pluck('id')->toArray())
		]);

		$col = $lead->products();
		$this->assertInstanceOf(Collection::class, $col);
		$this->assertCount(2, $col);
		$this->assertTrue($col->pluck('id')->sort()->values()->all() === $items->pluck('id')->sort()->values()->all());
	}

	/**
	 ** @test
	 **
	 ** stage() relation should point to LeadStage model
	 **/
	public function stage_relation_resolves_to_lead_stage_model()
	{
		$relation = (new Lead)->stage();

		$this->assertInstanceOf(BelongsTo::class,    $relation);
		$this->assertSame(LeadStage::class,       get_class($relation->getRelated()));
		$this->assertSame('stage_id',                   $relation->getForeignKeyName());
		$this->assertSame('id',             $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** pipeline() relation should point to Pipeline model
	 **/
	public function pipeline_relation_resolves_to_pipeline_model()
	{
		$relation = (new Lead)->pipeline();

		$this->assertInstanceOf(BelongsTo::class,    $relation);
		$this->assertSame(Pipeline::class,        get_class($relation->getRelated()));
		$this->assertSame('pipeline_id',                   $relation->getForeignKeyName());
		$this->assertSame('id',          $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** files() hasMany relation should point to LeadFile model
	 **/
	public function files_relation_resolves_to_lead_file_model()
	{
		$relation = (new Lead)->files();

		$this->assertInstanceOf(HasMany::class,   $relation);
		$this->assertSame(LeadFile::class,        get_class($relation->getRelated()));
		$this->assertSame('lead_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** users() belongsToMany relation uses user_leads pivot table
	 **/
	public function users_relation_is_belongs_to_many()
	{
		$relation = (new Lead)->users();

		$this->assertInstanceOf(BelongsToMany::class, $relation);
		$this->assertSame(User::class,               get_class($relation->getRelated()));
		$this->assertSame('user_leads',              $relation->getTable());
		$this->assertSame('lead_id',                 $relation->getForeignPivotKeyName());
		$this->assertSame('user_id',                 $relation->getRelatedPivotKeyName());
	}

	/**
	 ** @test
	 **
	 ** activities() hasMany relation should point to LeadActivityLog model ordered desc
	 **/
	public function activities_relation_resolves_to_activity_log_model_with_order()
	{
		$relation = (new Lead)->activities();
		$this->assertInstanceOf(HasMany::class,        $relation);
		$this->assertSame(LeadActivityLog::class,      get_class($relation->getRelated()));
		$orders = $relation->getQuery()->getQuery()->orders;
		$this->assertEquals([['column' => 'id', 'direction' => 'desc']], $orders);
	}

	/**
	 ** @test
	 **
	 ** discussions() hasMany relation should point to LeadDiscussion model ordered desc
	 **/
	public function discussions_relation_resolves_to_discussion_model_with_order()
	{
		$relation = (new Lead)->discussions();
		$this->assertInstanceOf(HasMany::class,        $relation);
		$this->assertSame(LeadDiscussion::class,       get_class($relation->getRelated()));
		$orders = $relation->getQuery()->getQuery()->orders;
		$this->assertEquals([['column' => 'id', 'direction' => 'desc']], $orders);
	}

	/**
	 ** @test
	 **
	 ** calls() hasMany relation should point to LeadCall model
	 **/
	public function calls_relation_resolves_to_lead_call_model()
	{
		$relation = (new Lead)->calls();
		$this->assertInstanceOf(HasMany::class,   $relation);
		$this->assertSame(LeadCall::class,        get_class($relation->getRelated()));
		$this->assertSame('lead_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** emails() hasMany relation should point to LeadEmail model ordered desc
	 **/
	public function emails_relation_resolves_to_lead_email_model_with_order()
	{
		$relation = (new Lead)->emails();
		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(LeadEmail::class,          get_class($relation->getRelated()));
		$orders = $relation->getQuery()->getQuery()->orders;
		$this->assertEquals([['column' => 'id', 'direction' => 'desc']], $orders);
	}
}
