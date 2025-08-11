<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{CompanyPolicy, Branch};

class CompanyPolicyTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** CompanyPolicy is mass assignable for branch, title, description, file, and created_by
	 **/
	public function company_policy_is_fillable()
	{
		$branch = Branch::factory()->create();

		$data = [
			'branch'      => $branch->id,
			'title'       => 'Privacy Policy',
			'description' => 'Company privacy guidelines',
			'file'        => '/policies/privacy.pdf',
			'created_by'  => 'admin_user',
		];

		$policy = CompanyPolicy::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $policy->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** CompanyPolicy uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function company_policy_uses_uuid_for_primary_key()
	{
		$policy = CompanyPolicy::factory()->create();
		$key   = $policy->getKey();

		$this->assertIsString($key);
		$this->assertFalse($policy->getIncrementing());
		$this->assertSame('string', $policy->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** branches() relation should point to Branch via branch
	 **/
	public function branches_relation_resolves_to_branch_model()
	{
		$relation = (new CompanyPolicy)->branches();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Branch::class,       get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('branch',            $relation->getLocalKeyName());
	}
}
