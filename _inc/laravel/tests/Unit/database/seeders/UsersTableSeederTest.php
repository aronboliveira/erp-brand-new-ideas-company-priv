<?php

namespace Tests\Unit\Seeders;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Config\Constants\{
	DatabaseConstants,
	PermissionsConstants,
	SeedersTemplating,
};
use App\Models\{
	ChartOfAccountType,
	ChartOfAccountSubType,
	ChartOfAccount,
	BankAccount
};
use Database\Seeders\UsersTableSeeder;

class UsersTableSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 *
	 ** Seeds permissions, roles, users, settings, chart accounts, and bank accounts
	 ** when run() is invoked (no return value).
	 **/
	public function it_seeds_permissions_roles_users_and_related_entities()
	{
		// Freeze time for consistent timestamps
		$now = Carbon::create(2025, 6, 10, 12, 0, 0);
		Carbon::setTestNow($now);

		// Ensure clean slate
		$this->assertDatabaseCount(DatabaseConstants::TABLE_USERS, 0);
		$this->assertDatabaseCount('permissions', 0);
		$this->assertDatabaseCount('roles', 0);
		$this->assertDatabaseCount((new ChartOfAccountType)->getTable(), 0);
		$this->assertDatabaseCount((new BankAccount)->getTable(), 0);
		$this->assertDatabaseCount(DatabaseConstants::TABLE_SETTINGS, 0);

		// Run the seeder (returns void)
		(new UsersTableSeeder())->run();

		// USERS: super-admin, admin, company, accountant, client => 5
		$this->assertDatabaseCount(DatabaseConstants::TABLE_USERS, 5);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'name'  => 'Super Admin',
			'type'  => PermissionsConstants::SA,
			'email' => 'superadmin@example.com',
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => PermissionsConstants::ADM,
			'email' => 'admin@example.com',
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => 'company',
			'email' => 'company@example.com',
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => 'accountant',
			'email' => 'accountant@example.com',
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => 'client',
			'email' => 'client@example.com',
		]);

		// PERMISSIONS: unique entries from SeedersTemplating::PERMISSIONS
		$expectedPermCount = collect(SeedersTemplating::PERMISSIONS)
			->map(fn ($p) => ($p['guard_name'] ?? 'web') . '|' . $p['name'])
			->unique()
			->count();
		$this->assertDatabaseCount('permissions', $expectedPermCount);
		$this->assertDatabaseHas('permissions', [
			'name'       => PermissionsConstants::SA,
			'guard_name' => 'web',
		]);

		// ROLES: super-admin, admin, company, accountant, client, vendor => 6
		$this->assertDatabaseCount('roles', 6);
		$this->assertDatabaseHas('roles', ['name' => PermissionsConstants::SA]);
		$this->assertDatabaseHas('roles', ['name' => PermissionsConstants::ADM]);
		$this->assertDatabaseHas('roles', ['name' => 'vendor']);

		// CHART OF ACCOUNTS and BANK ACCOUNTS: one each
		$this->assertDatabaseCount((new ChartOfAccountType)->getTable(), 1);
		$this->assertDatabaseCount((new ChartOfAccountSubType)->getTable(), 1);
		$this->assertDatabaseCount((new ChartOfAccount)->getTable(), 1);
		$this->assertDatabaseCount((new BankAccount)->getTable(), 1);

		// SETTINGS: 3 disks × 2 settings = 6
		$this->assertDatabaseCount(DatabaseConstants::TABLE_SETTINGS, 6);
	}

	/**
	 ** @test
	 *
	 ** Marks incomplete: cannot simulate exceeding the UUID retry limit.
	 **/
	public function it_marks_uuid_retry_limit_as_incomplete()
	{
		$this->markTestIncomplete('Cannot simulate >100,000 duplicate UUID attempts in a unit test.');
	}
}
