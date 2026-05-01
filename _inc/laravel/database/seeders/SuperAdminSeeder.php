<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\PermissionsConstants as PMC;
use App\Config\Constants\UsersConstants as UC;
use App\Models\{User, Utility};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash, Log, Schema};
use Illuminate\Support\Str;

/**
 * SuperAdminSeeder — Seeds a super admin user for testing.
 * 
 * ⚠️ IMPORTANT: The SA user UUID is referenced throughout the application.
 * Always use the same UUID to maintain FK integrity.
 */
class SuperAdminSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('Users table does not exist. Skipping SuperAdminSeeder.');
			return;
		}

		// Standard SA UUID from documentation
		$saUuid = DC::DEFAULT_UUID ?? 'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7';
		$saEmail = 'suporte@brandnewideascompany.com';

		// Check if SA user already exists
		$existingUser = DB::table(DC::TABLE_USERS)
			->where('id', $saUuid)
			->orWhere(UC::COL_EM, $saEmail)
			->first();

		if ($existingUser) {
			$this->command?->comment("Super admin user already exists (ID: {$existingUser->id}). Skipping.");
			Log::info('SuperAdminSeeder: SA user already exists', ['id' => $existingUser->id]);
			return;
		}

		try {
			$now = now();

			$userData = [
				'id'          => $saUuid,
				UC::COL_NM    => 'Super Admin',
				UC::COL_EM    => $saEmail,
				UC::COL_PW    => Hash::make('Admin@BrandNewIdeasCompany2026!'),
				UC::COL_TP    => PMC::SA, // 'super admin'
				UC::COL_LG    => 'pt-br',
				UC::COL_SL    => 0,
				UC::COL_ENT_CD => Utility::generateRandomCnpj(),
				UC::COL_ENT_TP => 'cnpj',
				'is_active'   => 1,
				'created_by'  => $saUuid,
				'updated_by'  => $saUuid,
				'created_at'  => $now,
				'updated_at'  => $now,
				'email_verified_at' => $now,
			];

			DB::table(DC::TABLE_USERS)->insert($userData);

			$this->command?->info("Super admin user created successfully!");
			$this->command?->line("  Email: {$saEmail}");
			$this->command?->line("  Password: Admin@BrandNewIdeasCompany2026!");
			$this->command?->line("  UUID: {$saUuid}");

			Log::info('SuperAdminSeeder: Created SA user', [
				'id'    => $saUuid,
				'email' => $saEmail,
			]);
		} catch (\Throwable $e) {
			$this->command?->error("Failed to create super admin: {$e->getMessage()}");
			Log::error('SuperAdminSeeder: Failed to create SA user', [
				'error' => $e->getMessage(),
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
			]);
		}
	}
}
