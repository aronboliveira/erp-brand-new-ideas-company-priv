<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	EmailsConstants as EC,
	UsersConstants as UC,
	ProjectsConstants as PC
};
use App\Enums\EmailTemplateType;
use App\Models\UserEmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserEmailTemplatesSeeder extends Seeder
{
	private const SEED = 20251215;
	private const ALLOWED_USER_TYPES = ['admin', 'super admin', 'customer', 'hr', 'accountant'];

	public function run(): void
	{
		fake()->seed(self::SEED);

		if (!$this->validateTables()) {
			return;
		}

		$templateIds = $this->getTemplateIds();
		if (empty($templateIds)) {
			$this->command?->warn(static::class . ': no email templates found, nothing to assign to users.');
			return;
		}

		$users = $this->getFilteredUsers();
		if ($users->isEmpty()) {
			$this->command?->warn(static::class . ': no eligible users found, nothing to seed.');
			return;
		}

		$totalCreated = $this->seedUserTemplates($users, $templateIds);

		$this->command?->info(static::class . ": created {$totalCreated} user email template links.");
	}

	private function validateTables(): bool
	{
		$requiredTables = [
			DC::TABLE_USERS => 'users',
			DC::TABLE_EMAIL_TEMPLATES => 'email templates',
			DC::TABLE_USER_EML_TMPS => 'user email templates',
		];

		foreach ($requiredTables as $constant => $name) {
			if (!DB::getSchemaBuilder()->hasTable($constant)) {
				$this->command?->warn(static::class . ": {$name} table missing, seeder aborted.");
				return false;
			}
		}

		return true;
	}

	private function getTemplateIds(): array
	{
		return DB::table(DC::TABLE_EMAIL_TEMPLATES)
			->select('id')
			->orderBy(DC::COL_C_AT)
			->pluck('id')
			->map(fn($id) => (string) $id)
			->all();
	}

	private function getFilteredUsers(): \Illuminate\Support\Collection
	{
		return DB::table(DC::TABLE_USERS)
			->select('id')
			->whereIn('type', self::ALLOWED_USER_TYPES)
			->orderBy(DC::COL_C_AT)
			->get();
	}

	private function seedUserTemplates($users, array $templateIds): int
	{
		$typeCount = count(EmailTemplateType::cases());
		$totalCreated = 0;

		foreach ($users as $user) {
			$userId = (string) $user->id;
			if ($userId === '') {
				continue;
			}

			$maxPerUser = min($typeCount, count($templateIds));
			if ($maxPerUser === 0) {
				continue;
			}

			$targetCount = fake()->numberBetween(0, $maxPerUser);
			if ($targetCount === 0) {
				continue;
			}

			$selectedIds = $this->selectRandomTemplates($templateIds, $targetCount);
			$existingLinks = $this->getExistingLinks($userId);
			$hasDefault = $this->userHasDefault($userId);

			foreach ($selectedIds as $templateId) {
				$templateId = trim((string) $templateId);
				if ($templateId === '' || in_array($templateId, $existingLinks, true)) {
					continue;
				}

				$isFavorite = fake()->boolean(30);
				$isDefault = false;

				if (!$hasDefault && fake()->boolean(20)) {
					$isDefault = true;
					$hasDefault = true;
				}

				$clients = $this->generateClientsList();

				UserEmailTemplate::create([
					EC::COL_TMP => $templateId,
					UC::COL_USER_ID => $userId,
					UC::COL_IA => true,
					'counter' => fake()->numberBetween(0, 250),
					PC::COL_IS_FV => $isFavorite,
					DC::COL_IS_DEF => $isDefault,
					'clients' => $clients,
				]);

				$totalCreated++;
			}
		}

		return $totalCreated;
	}

	private function selectRandomTemplates(array $templateIds, int $count): array
	{
		$shuffled = $templateIds;
		shuffle($shuffled);
		return array_slice($shuffled, 0, $count);
	}

	private function getExistingLinks(string $userId): array
	{
		return DB::table(DC::TABLE_USER_EML_TMPS)
			->where(UC::COL_USER_ID, $userId)
			->pluck(EC::COL_TMP)
			->map(fn($id) => (string) $id)
			->all();
	}

	private function userHasDefault(string $userId): bool
	{
		return DB::table(DC::TABLE_USER_EML_TMPS)
			->where(UC::COL_USER_ID, $userId)
			->where(DC::COL_IS_DEF, true)
			->exists();
	}

	private function generateClientsList(): array
	{
		$all = [
			'web',
			'mobile',
			'outlook',
			'gmail',
			'protonmail',
			'thunderbird',
		];

		if (fake()->boolean(30)) {
			return [];
		}

		shuffle($all);
		$count = fake()->numberBetween(1, min(3, count($all)));

		return array_slice($all, 0, $count);
	}
}
