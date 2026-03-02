<?php

namespace Tests\Unit\Traits;

use App\Models\User;
use Illuminate\Support\Facades\{Gate, Hash};
use Spatie\Permission\Models\{Permission, Role};

trait CreatesMockUser
{
	protected function createUserWithPermissions(array $permissions): User
	{
		$user = User::factory()->create(['password' => Hash::make('password')]);

		$role = Role::create(['name' => 'test-role-' . uniqid()]);
		foreach ($permissions as $permName) {
			$permission = Permission::firstOrCreate(['name' => $permName]);
			$role->givePermissionTo($permission);
		}

		$user?->assignRole($role);

		return $user;
	}

	protected function createUserWithoutPermissions(): User
	{
		return User::factory()->create(['password' => Hash::make('password')]);
	}

	private function actingAsUserWithAllPermissions(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		Gate::before(fn () => true);
	}
}
