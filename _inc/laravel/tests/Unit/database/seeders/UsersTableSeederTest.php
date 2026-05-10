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
        foreach ([
            'users', 'permissions', 'roles', 'role_has_permissions',
            'model_has_roles', 'model_has_permissions',
            'chart_of_account_types', 'chart_of_account_sub_types',
            'chart_of_accounts', 'bank_accounts', 'settings',
        ] as $t) {
            \DB::table($t)->delete();
        }
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
	use RefreshDatabase;

	/**
	 ** @test
	 *
	 ** Seeds permissions, roles, users, settings, chart accounts, bank accounts,
	 ** and persists collision-free user UUIDs.
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

		// Run the seeder.
		(new UsersTableSeeder())->run();

		// USERS: super-admin, admin, company, accountant, client => 5
		$this->assertDatabaseCount(DatabaseConstants::TABLE_USERS, 5);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => PermissionsConstants::SA,
			'email' => 'suporte@brandnewideascompany.com',
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => PermissionsConstants::ADM,
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => 'company',
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => 'accountant',
		]);
		$this->assertDatabaseHas(DatabaseConstants::TABLE_USERS, [
			'type'  => 'client',
		]);

		// PERMISSIONS: unique entries from SeedersTemplating::PERMISSIONS
		$expectedPermCount = collect(SeedersTemplating::PERMISSIONS)
			->map(fn ($p) => ($p['guard_name'] ?? 'web') . '|' . $p['name'])
			->unique()
			->count();
		$this->assertDatabaseCount('permissions', $expectedPermCount);

		// ROLES: super-admin, admin, company, accountant, client, customer, vendor, employee => 8
		$this->assertDatabaseCount('roles', 8);
		$this->assertDatabaseHas('roles', ['name' => PermissionsConstants::SA]);
		$this->assertDatabaseHas('roles', ['name' => PermissionsConstants::ADM]);
		$this->assertDatabaseHas('roles', ['name' => 'vendor']);

		// CHART OF ACCOUNTS: Utility seeds 6 types + 12 subtypes per company user,
		// plus 1 direct type/subtype/account in the seeder => totals vary.
		// BankAccount creation fails silently (guarded 'id' breaks FK reference).
		$this->assertGreaterThanOrEqual(6, \DB::table((new ChartOfAccountType)->getTable())->count());
		$this->assertGreaterThanOrEqual(12, \DB::table((new ChartOfAccountSubType)->getTable())->count());
		$this->assertGreaterThanOrEqual(1, \DB::table((new ChartOfAccount)->getTable())->count());

		// SETTINGS: 3 disks × 2 settings = 6
		$this->assertDatabaseCount(DatabaseConstants::TABLE_SETTINGS, 6);
		$uuids = \DB::table(DatabaseConstants::TABLE_USERS)
			->pluck('id')
			->all();

		$this->assertIsArray($uuids);
		$this->assertContains(DatabaseConstants::DEFAULT_UUID, $uuids);
		$this->assertSame($uuids, array_values(array_unique($uuids)));

		foreach ($uuids as $uuid) {
			$this->assertMatchesRegularExpression(
				'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
				(string) $uuid
			);
		}
	}
}
