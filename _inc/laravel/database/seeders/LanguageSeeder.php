<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

/**
 * LanguageSeeder — Seeds the languages table with common language options
 * for the locale picker on the login page and throughout the app.
 */
class LanguageSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_LANGS)) {
			$this->command?->warn('Languages table does not exist. Skipping LanguageSeeder.');
			return;
		}

		// Check if any users exist before setting created_by/updated_by
		$hasUsers = DB::table('users')->exists();
		$saUuid = $hasUsers ? (DC::DEFAULT_UUID ?? 'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7') : null;

		$languages = [
			// Latin/Western
			['code' => 'en', 'full_name' => 'English'],
			['code' => 'pt-br', 'full_name' => 'Português (Brasil)'],
			['code' => 'pt', 'full_name' => 'Português'],
			['code' => 'es', 'full_name' => 'Español'],
			['code' => 'fr', 'full_name' => 'Français'],
			['code' => 'de', 'full_name' => 'Deutsch'],
			['code' => 'it', 'full_name' => 'Italiano'],
			['code' => 'nl', 'full_name' => 'Nederlands'],
			['code' => 'pl', 'full_name' => 'Polski'],
			['code' => 'tr', 'full_name' => 'Türkçe'],
			['code' => 'ru', 'full_name' => 'Русский'],
			['code' => 'uk', 'full_name' => 'Українська'],

			// RTL Languages
			['code' => 'ar', 'full_name' => 'العربية', 'rtl' => true],
			['code' => 'he', 'full_name' => 'עברית', 'rtl' => true],
			['code' => 'fa', 'full_name' => 'فارسی', 'rtl' => true],
			['code' => 'ur', 'full_name' => 'اردو', 'rtl' => true],

			// CJK / Compound Letter Languages
			['code' => 'zh-cn', 'full_name' => '简体中文'],
			['code' => 'zh-tw', 'full_name' => '繁體中文'],
			['code' => 'ja', 'full_name' => '日本語'],
			['code' => 'ko', 'full_name' => '한국어'],
			['code' => 'th', 'full_name' => 'ภาษาไทย'],
			['code' => 'vi', 'full_name' => 'Tiếng Việt'],
			['code' => 'hi', 'full_name' => 'हिन्दी'],
			['code' => 'bn', 'full_name' => 'বাংলা'],
			['code' => 'ta', 'full_name' => 'தமிழ்'],
		];

		$now = now();

		// Check if rtl column exists
		$hasRtlColumn = Schema::hasColumn(DC::TABLE_LANGS, 'rtl');

		foreach ($languages as $lang) {
			$exists = DB::table(DC::TABLE_LANGS)->where('code', $lang['code'])->exists();
			if (!$exists) {
				try {
					$insertData = [
						'id'         => Str::uuid()->toString(),
						'code'       => $lang['code'],
						'full_name'  => $lang['full_name'],
						'created_at' => $now,
						'updated_at' => $now,
					];

					// Only set created_by/updated_by if users exist
					if ($saUuid !== null) {
						$insertData['created_by'] = $saUuid;
						$insertData['updated_by'] = $saUuid;
					}

					// Add rtl flag if column exists
					if ($hasRtlColumn) {
						$insertData['rtl'] = $lang['rtl'] ?? false;
					}

					DB::table(DC::TABLE_LANGS)->insert($insertData);
					$this->command?->info("Inserted language: {$lang['full_name']} ({$lang['code']})");
					Log::info("LanguageSeeder: Inserted language {$lang['code']}");
				} catch (\Throwable $e) {
					$this->command?->error("Failed to insert language {$lang['code']}: {$e->getMessage()}");
					Log::error("LanguageSeeder: Failed to insert {$lang['code']}", ['error' => $e->getMessage()]);
				}
			} else {
				// If language exists and rtl column exists, update rtl flag for RTL languages
				if ($hasRtlColumn && isset($lang['rtl']) && $lang['rtl'] === true) {
					try {
						DB::table(DC::TABLE_LANGS)
							->where('code', $lang['code'])
							->update(['rtl' => true, 'updated_at' => $now]);
						$this->command?->comment("Updated RTL flag for {$lang['code']}");
					} catch (\Throwable $e) {
						Log::warning("LanguageSeeder: Failed to update RTL for {$lang['code']}", ['error' => $e->getMessage()]);
					}
				}
				$this->command?->comment("Language {$lang['code']} already exists, skipping.");
			}
		}

		$this->command?->info('LanguageSeeder completed.');
	}
}
