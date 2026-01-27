<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	EmailsConstants as EC,
	MessagesConstants as MC
};
use App\Enums\AvailableLang;
use App\Models\EmailTemplateLang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailTemplateLangsSeeder extends Seeder
{
	private const SEED = 20251215;
	private const HARD_CAP = 2048 * 8;
	private const SECONDS_LIMIT = 3 * 10 ** 2;

	public function run(): void
	{
		fake()->seed(self::SEED);
		$clock = microtime(true);
		if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_EMAIL_TEMPLATES)) {
			$this->command?->warn(static::class . ': email templates table missing, seeder aborted.');
			return;
		}

		if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_EML_TMP_LG)) {
			$this->command?->warn(static::class . ': email template langs table missing, seeder aborted.');
			return;
		}

		$availableLangCases  = AvailableLang::cases();
		$availableLangValues = array_column($availableLangCases, 'value');

		$parents = DB::table(DC::TABLE_EMAIL_TEMPLATES)
			->select('id', MC::COL_AV_LG)
			->orderBy(DC::COL_C_AT)
			->get();

		if ($parents->isEmpty()) {
			$this->command?->warn(static::class . ': no email templates found, nothing to seed.');
			return;
		}

		$totalCreated = 0;
		$cap = self::HARD_CAP;
		foreach ($parents as $parent) {
			$parentId = (string) $parent->id;

			$langsForParent = $this->extractAvailableLangsForParent(
				$parent->{MC::COL_AV_LG} ?? null,
				$availableLangValues
			);

			foreach ($langsForParent as $langValue) {
				$cap--;
				if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
					$this->command?->warn(static::class . ': time limit reached, aborting.');
					break 2;
				}
				if ($cap <= 0 || !$cap) {
					$this->command?->warn(static::class . ': creation limit reached, aborting.');
					break 2;
				}
				$langValue = trim((string) $langValue);
				if ($langValue === '') {
					continue;
				}

				$exists = DB::table(DC::TABLE_EML_TMP_LG)
					->where(EC::COL_PRT_ID, $parentId)
					->where('lang', $langValue)
					->exists();

				if ($exists) {
					continue;
				}

				$translatorId = $this->pickTranslatorId();

				$variables = $this->generateVariablesPayload();
				$metadata  = $this->generateMetadataPayload();
				(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln('Creating email template lang for parent ' . $parentId . ' lang ' . $langValue);
				EmailTemplateLang::create([
					EC::COL_PRT_ID => $parentId,
					'lang'         => $langValue,
					'subject'      => fake()->sentence(6),
					'content'      => fake()->paragraphs(3, true),
					'translator'   => fake()->boolean(70) ? fake()->name() : null,
					MC::COL_TRL_ID => $translatorId,
					'variables'    => $variables,
					'metadata'     => $metadata,
				]);

				$totalCreated++;
			}
		}

		$this->command?->info(static::class . ": created {$totalCreated} email template translations.");
	}

	/**
	 * Decodifica available_languages do template pai e filtra contra AvailableLang::cases().
	 * Se falhar ou vier vazio, escolhe de 1 a N línguas a partir de todos os AvailableLang.
	 */
	private function extractAvailableLangsForParent(mixed $raw, array $availableLangValues): array
	{
		$decoded = [];

		try {
			if (is_string($raw) && $raw !== '') {
				$tmp = json_decode($raw, true);
				if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
					$decoded = $tmp;
				}
			} elseif (is_array($raw)) {
				$decoded = $raw;
			}
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed to decode available_languages', [
				'value' => $raw,
				'error' => $e->getMessage(),
			]);
			$decoded = [];
		}

		$decoded = array_map(
			fn($v) => strtolower(trim((string) $v)),
			$decoded
		);

		$decoded = array_values(array_unique($decoded));

		$filtered = array_values(
			array_intersect($decoded, $availableLangValues)
		);

		if (count($filtered) > 0) {
			return $filtered;
		}

		$countAll = count($availableLangValues);
		if ($countAll === 0) {
			return [];
		}

		$targetCount = fake()->numberBetween(1, $countAll);
		$shuffled    = $availableLangValues;
		shuffle($shuffled);

		return array_slice($shuffled, 0, $targetCount);
	}

	/**
	 * Escolhe um tradutor (usuário) ou retorna DEFAULT_UUID para representar “sem conta”.
	 */
	private function pickTranslatorId(): ?string
	{
		if (!DB::getSchemaBuilder()->hasTable(DC::TABLE_USERS)) {
			return DC::DEFAULT_UUID;
		}

		try {
			$candidate = DB::table(DC::TABLE_USERS)
				->inRandomOrder()
				->value('id');

			if (!$candidate) {
				return DC::DEFAULT_UUID;
			}

			return fake()->boolean(65) ? (string) $candidate : DC::DEFAULT_UUID;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed to pick translator user id', [
				'error' => $e->getMessage(),
			]);

			return DC::DEFAULT_UUID;
		}
	}

	private function generateVariablesPayload(): array
	{
		if (fake()->boolean(25)) {
			return [];
		}

		return [
			'user_name'    => 'string',
			'user_email'   => 'email',
			'company_name' => 'string',
			'link'         => 'url',
		];
	}

	private function generateMetadataPayload(): array
	{
		if (fake()->boolean(30)) {
			return [];
		}

		return [
			'version'      => fake()->randomElement(['v1', 'v2', 'v3']),
			'last_review'  => now()->subDays(fake()->numberBetween(0, 365))->toIso8601String(),
			'is_approved'  => fake()->boolean(80),
		];
	}
}
