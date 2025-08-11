<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{HasOne, BelongsToMany, HasMany};
use App\Models\{
	Deal,
	Pipeline,
	Stage,
	User,
	DealFile,
	DealTask,
	Invoice,
	DealCall,
	DealEmail,
	ActivityLog,
	DealDiscussion
};

class DealTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Deal is mass assignable for all fillable fields
	 **/
	public function deal_is_fillable()
	{
		$data = [
			'name'        => 'Acme Deal',
			'phone'       => '555-1234',
			'price'       => 1234.56,
			'pipeline_id' => 'pipe-1',
			'stage_id'    => 'stage-1',
			'group_id'    => 'group-1',
			'sources'     => '1,2,3',
			'products'    => '4,5',
			'notes'       => 'Important notes',
			'labels'      => '6,7',
			'permissions' => 'foo,bar',
			'status'      => 'Active',
			'order'       => 10,
			'created_by'  => 'user1',
			'is_active'   => true,
		];

		$deal = Deal::create($data);

		foreach ($data as $key => $value) {
			$this->assertEquals($value, $deal->$key);
		}
	}

	/**
	 ** @test
	 **
	 ** static permissions array is populated in booted()
	 **/
	public function permissions_static_property_is_built_correctly()
	{
		// Force booted() to run
		Deal::make([]);

		$perms = Deal::$permissions;

		$this->assertContains('Client View Tasks',     $perms);
		$this->assertContains('Client View Products',  $perms);
		$this->assertContains('Client Add File',       $perms);
		$this->assertContains('Client Deal Activity',  $perms);
	}

	/**
	 ** @test
	 **
	 ** pipeline() relation should point to Pipeline model
	 **/
	public function pipeline_relation_resolves_to_pipeline_model()
	{
		$relation = (new Deal)->pipeline();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Pipeline::class,    get_class($relation->getRelated()));
		$this->assertSame('id',               $relation->getForeignKeyName());
		$this->assertSame('pipeline_id',      $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** stage() relation should point to Stage model
	 **/
	public function stage_relation_resolves_to_stage_model()
	{
		$relation = (new Deal)->stage();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Stage::class,       get_class($relation->getRelated()));
		$this->assertSame('id',               $relation->getForeignKeyName());
		$this->assertSame('stage_id',         $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** clients() belongsToMany relation uses client_deals pivot
	 **/
	public function clients_relation_is_belongs_to_many()
	{
		$relation = (new Deal)->clients();

		$this->assertInstanceOf(BelongsToMany::class, $relation);
		$this->assertSame(User::class,               get_class($relation->getRelated()));
		$this->assertSame('client_deals',            $relation->getTable());
		$this->assertSame('deal_id',                 $relation->getForeignPivotKeyName());
		$this->assertSame('client_id',               $relation->getRelatedPivotKeyName());
	}

	/**
	 ** @test
	 **
	 ** users() belongsToMany relation uses user_deals pivot
	 **/
	public function users_relation_is_belongs_to_many()
	{
		$relation = (new Deal)->users();

		$this->assertInstanceOf(BelongsToMany::class, $relation);
		$this->assertSame(User::class,               get_class($relation->getRelated()));
		$this->assertSame('user_deals',              $relation->getTable());
		$this->assertSame('deal_id',                 $relation->getForeignPivotKeyName());
		$this->assertSame('user_id',                 $relation->getRelatedPivotKeyName());
	}

	/**
	 ** @test
	 **
	 ** files() hasMany relation should point to DealFile model
	 **/
	public function files_relation_resolves_to_deal_file_model()
	{
		$relation = (new Deal)->files();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(DealFile::class,           get_class($relation->getRelated()));
		$this->assertSame('deal_id',                 $relation->getForeignKeyName());
		$this->assertSame('id',                      $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** tasks() hasMany relation should point to DealTask model
	 **/
	public function tasks_relation_resolves_to_deal_task_model()
	{
		$relation = (new Deal)->tasks();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(DealTask::class,           get_class($relation->getRelated()));
		$this->assertSame('deal_id',                 $relation->getForeignKeyName());
		$this->assertSame('id',                      $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** completeTasks() filters tasks where status = 1
	 **/
	public function completeTasks_scope_filters_status()
	{
		$query = (new Deal)->completeTasks()->getQuery();
		$wheres = collect($query->wheres);
		$this->assertTrue(
			$wheres->contains(
				fn ($w) =>
				$w['type'] === 'Basic' && $w['column'] === 'status' && $w['value'] === 1
			)
		);
	}

	/**
	 ** @test
	 **
	 ** invoices() hasMany relation should point to Invoice model
	 **/
	public function invoices_relation_resolves_to_invoice_model()
	{
		$relation = (new Deal)->invoices();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(Invoice::class,            get_class($relation->getRelated()));
		$this->assertSame('deal_id',                 $relation->getForeignKeyName());
		$this->assertSame('id',                      $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** calls() hasMany relation should point to DealCall model
	 **/
	public function calls_relation_resolves_to_deal_call_model()
	{
		$relation = (new Deal)->calls();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(DealCall::class,           get_class($relation->getRelated()));
		$this->assertSame('deal_id',                 $relation->getForeignKeyName());
		$this->assertSame('id',                      $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** emails() hasMany relation should point to DealEmail model with descending order
	 **/
	public function emails_relation_resolves_to_deal_email_model()
	{
		$relation = (new Deal)->emails();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(DealEmail::class,          get_class($relation->getRelated()));
		$this->assertSame('deal_id',                 $relation->getForeignKeyName());
		// check that ordering is applied
		$orders = $relation->getQuery()->orders;
		$this->assertEquals([['column' => 'id', 'direction' => 'desc']], $orders);
	}

	/**
	 ** @test
	 **
	 ** activities() hasMany relation should point to ActivityLog model with descending order
	 **/
	public function activities_relation_resolves_to_activity_log_model()
	{
		$relation = (new Deal)->activities();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(ActivityLog::class,        get_class($relation->getRelated()));
		$this->assertSame('deal_id',                 $relation->getForeignKeyName());
		$orders = $relation->getQuery()->orders;
		$this->assertEquals([['column' => 'id', 'direction' => 'desc']], $orders);
	}

	/**
	 ** @test
	 **
	 ** discussions() hasMany relation should point to DealDiscussion model with descending order
	 **/
	public function discussions_relation_resolves_to_discussion_model()
	{
		$relation = (new Deal)->discussions();

		$this->assertInstanceOf(HasMany::class,      $relation);
		$this->assertSame(DealDiscussion::class,     get_class($relation->getRelated()));
		$this->assertSame('deal_id',                 $relation->getForeignKeyName());
		$orders = $relation->getQuery()->orders;
		$this->assertEquals([['column' => 'id', 'direction' => 'desc']], $orders);
	}
}
