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
use App\Traits\EvaluatesMemory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class UserEmailTemplatesSeeder extends Seeder
{
	use EvaluatesMemory;

	private const SEED = 20251215;
	private const ALLOWED_USER_TYPES = ['admin', 'super admin', 'customer', 'hr', 'accountant'];
	private const BATCH_SIZE = 512;
	private const MAX_TEMPLATES_PER_USER = 16;
	private const MEMORY_CHECK_INTERVAL = 50;

	private ?Carbon $oldestUserDate = null;
	private ?Carbon $currentDate = null;
	private int $operationCounter = 0;

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

		$this->currentDate = Carbon::now();

		try {
			$this->oldestUserDate = $this->getOldestUserCreatedAt();
		} catch (\Exception $e) {
			$this->command?->error(static::class . ': failed to retrieve oldest user date: ' . $e->getMessage());
			return;
		}

		try {
			$totalCreated = $this->processBatches($templateIds);
			$this->command?->info(static::class . ": created {$totalCreated} user email template links.");
		} catch (\Exception $e) {
			$this->command?->error(static::class . ': seeding failed: ' . $e->getMessage());
		}
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
		try {
			return DB::table(DC::TABLE_EMAIL_TEMPLATES)
				->select('id')
				->orderBy(DC::COL_C_AT)
				->pluck('id')
				->map(fn($id) => (string) $id)
				->all();
		} catch (\Exception $e) {
			$this->command?->error(static::class . ': failed to retrieve template IDs: ' . $e->getMessage());
			return [];
		}
	}

	private function getOldestUserCreatedAt(): ?Carbon
	{
		try {
			$oldest = DB::table(DC::TABLE_USERS)
				->whereIn('type', self::ALLOWED_USER_TYPES)
				->min(DC::COL_C_AT);

			return $oldest ? Carbon::parse($oldest) : null;
		} catch (\Exception $e) {
			$this->command?->warn(static::class . ': could not determine oldest user date, using fallback.');
			return null;
		}
	}

	private function processBatches(array $templateIds): int
	{
		$totalCreated = 0;
		$offset = 0;
		$HARD_CAP = 2; // was unbounded (while true)
		$startTime = microtime(true);
		$SECONDS_LIMIT = 32;

		while (true) {
			if ($totalCreated >= $HARD_CAP || (microtime(true) - $startTime) > $SECONDS_LIMIT) {
				break;
			}
			try {
				$users = $this->getUserBatch($offset);

				if ($users->isEmpty()) {
					break;
				}

				$batchCreated = $this->seedUserTemplatesBatch($users, $templateIds);
				$totalCreated += $batchCreated;

				$this->command?->info(static::class . ": processed batch at offset {$offset}, created {$batchCreated} links.");

				$offset += self::BATCH_SIZE;
				$this->checkMemoryUsage();
			} catch (\Exception $e) {
				$this->command?->error(static::class . ": batch processing failed at offset {$offset}: " . $e->getMessage());
				$offset += self::BATCH_SIZE;
			}
		}

		return $totalCreated;
	}

	private function getUserBatch(int $offset): Collection
	{
		return DB::table(DC::TABLE_USERS)
			->select('id', DC::COL_C_AT)
			->whereIn('type', self::ALLOWED_USER_TYPES)
			->orderBy(DC::COL_C_AT)
			->offset($offset)
			->limit(self::BATCH_SIZE)
			->get();
	}

	private function seedUserTemplatesBatch(Collection $users, array $templateIds): int
	{
		$totalCreated = 0;

		// Pre-fetch existing links for all users in batch
		$userIds = $users->pluck('id')->map(fn($id) => (string) $id)->all();
		$existingLinksByUser = $this->getExistingLinksForUsers($userIds);
		$hasDefaultByUser = $this->getUsersWithDefaults($userIds);

		foreach ($users as $user) {
			try {
				$userId = (string) $user->id;
				if ($userId === '') {
					continue;
				}

				$existingLinks = $existingLinksByUser[$userId] ?? [];
				$hasDefault = in_array($userId, $hasDefaultByUser, true);

				$created = $this->seedUserTemplates($user, $templateIds, $existingLinks, $hasDefault);
				$totalCreated += $created;

				$this->operationCounter++;

				if ($this->operationCounter % self::MEMORY_CHECK_INTERVAL === 0) {
					$this->checkMemoryUsage();
				}
			} catch (\Exception $e) {
				$this->command?->warn(static::class . ": failed to seed templates for user {$user->id}: " . $e->getMessage());
				continue;
			}
		}

		return $totalCreated;
	}

	private function seedUserTemplates($user, array $templateIds, array $existingLinks, bool $hasDefault): int
	{
		$userId = (string) $user->id;
		if ($userId === '') {
			return 0;
		}

		$targetCount = $this->calculateTemplateCount($user);
		if ($targetCount === 0) {
			return 0;
		}

		$targetCount = min($targetCount, count($templateIds), self::MAX_TEMPLATES_PER_USER);

		try {
			$selectedIds = $this->selectRandomTemplates($templateIds, $targetCount);
		} catch (\Exception $e) {
			throw new \Exception("Failed to select random templates: " . $e->getMessage());
		}

		$created = 0;
		$recordsToInsert = [];

		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		foreach ($selectedIds as $templateId) {
			$templateId = trim((string) $templateId);
			if ($templateId === '' || in_array($templateId, $existingLinks, true)) {
				continue;
			}

			try {
				$isFavorite = fake()->boolean(30);
				$isDefault = false;

				if (!$hasDefault && fake()->boolean(20)) {
					$isDefault = true;
					$hasDefault = true;
				}

				$clients = $this->generateClientsList();
				// $output->writeln(static::class . ": preparing to link template {$templateId} to user {$userId} (favorite: " . ($isFavorite ? 'yes' : 'no') . ", default: " . ($isDefault ? 'yes' : 'no') . ")");
				// Use DB insert instead of Eloquent to avoid model overhead
				$recordsToInsert[] = [
					'id' => (string) \Illuminate\Support\Str::uuid(),
					EC::COL_TMP => $templateId,
					UC::COL_USER_ID => $userId,
					UC::COL_IA => true,
					'counter' => fake()->numberBetween(0, 250),
					PC::COL_IS_FV => $isFavorite,
					DC::COL_IS_DEF => $isDefault,
					'clients' => json_encode($clients),
					DC::COL_C_AT => now(),
					DC::COL_U_AT => now(),
				];

				$created++;
			} catch (\Exception $e) {
				$this->command?->warn(static::class . ": failed to prepare template link for user {$userId}, template {$templateId}: " . $e->getMessage());
				continue;
			}
		}

		// Bulk insert all records for this user
		if (!empty($recordsToInsert)) {
			try {
				DB::table(DC::TABLE_USER_EML_TMPS)->insert($recordsToInsert);
			} catch (\Exception $e) {
				$this->command?->error(static::class . ": failed to bulk insert for user {$userId}: " . $e->getMessage());
				return 0;
			}
		}

		return $created;
	}

	private function calculateTemplateCount($user): int
	{
		if (!isset($user->{DC::COL_C_AT}) || $this->oldestUserDate === null) {
			return fake()->numberBetween(0, self::MAX_TEMPLATES_PER_USER);
		}

		try {
			$userCreatedAt = Carbon::parse($user->{DC::COL_C_AT});

			$daysFromNow = $this->currentDate->diffInDays($userCreatedAt);
			$daysFromOldest = $this->oldestUserDate->diffInDays($userCreatedAt);
			$totalSpan = $this->currentDate->diffInDays($this->oldestUserDate);

			if ($totalSpan === 0) {
				return fake()->numberBetween(0, self::MAX_TEMPLATES_PER_USER);
			}

			$ageWeight = min(1.0, $daysFromNow / max(1, $totalSpan));
			$positionWeight = min(1.0, $daysFromOldest / max(1, $totalSpan));
			$combinedWeight = ($ageWeight * 0.7) + ($positionWeight * 0.3);
			$weightedMax = (int) round($combinedWeight * self::MAX_TEMPLATES_PER_USER);
			$minCount = (int) floor($weightedMax * 0.5);
			$maxCount = min(self::MAX_TEMPLATES_PER_USER, $weightedMax);

			return fake()->numberBetween($minCount, $maxCount);
		} catch (\Exception $e) {
			return fake()->numberBetween(0, self::MAX_TEMPLATES_PER_USER);
		}
	}

	private function selectRandomTemplates(array $templateIds, int $count): array
	{
		$shuffled = $templateIds;
		shuffle($shuffled);
		return array_slice($shuffled, 0, $count);
	}

	private function getExistingLinksForUsers(array $userIds): array
	{
		if (empty($userIds)) {
			return [];
		}

		try {
			$links = DB::table(DC::TABLE_USER_EML_TMPS)
				->whereIn(UC::COL_USER_ID, $userIds)
				->select(UC::COL_USER_ID, EC::COL_TMP)
				->get();

			$grouped = [];
			foreach ($links as $link) {
				$userId = (string) $link->{UC::COL_USER_ID};
				$templateId = (string) $link->{EC::COL_TMP};

				if (!isset($grouped[$userId])) {
					$grouped[$userId] = [];
				}
				$grouped[$userId][] = $templateId;
			}

			return $grouped;
		} catch (\Exception $e) {
			$this->command?->error(static::class . ': failed to fetch existing links: ' . $e->getMessage());
			return [];
		}
	}

	private function getUsersWithDefaults(array $userIds): array
	{
		if (empty($userIds)) {
			return [];
		}

		try {
			return DB::table(DC::TABLE_USER_EML_TMPS)
				->whereIn(UC::COL_USER_ID, $userIds)
				->where(DC::COL_IS_DEF, true)
				->pluck(UC::COL_USER_ID)
				->map(fn($id) => (string) $id)
				->all();
		} catch (\Exception $e) {
			$this->command?->error(static::class . ': failed to fetch users with defaults: ' . $e->getMessage());
			return [];
		}
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
