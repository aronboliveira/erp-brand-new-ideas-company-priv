<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, UsersConstants as UC};
use App\Enums\FieldType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class FormFieldResponseSeeder extends Seeder
{
	// private const HARD_CAP = 1600;
	private const HARD_CAP = 2;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	public function run(): void
	{
		$created = 0;
		$clock = microtime(true);
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		try {
			$formIds = DB::table(DC::TABLE_FORM_BUILD)->pluck('id')->all();
			if (!$formIds) {
				$out->writeln('<comment>FormFieldResponseSeeder: no forms found, skipping seeding.</comment>');
				Log::warning(static::class . ' aborting: no forms found', ['table' => DC::TABLE_FORM_BUILD]);
				return;
			}

			$fieldIds = DB::table(DC::TABLE_FORM_FIELDS)->pluck('id')->all();
			if (!$fieldIds) {
				$out->writeln('<comment>FormFieldResponseSeeder: no form fields found, skipping seeding.</comment>');
				Log::warning(static::class . ' aborting: no form fields found', ['table' => DC::TABLE_FORM_FIELDS]);
				return;
			}

			$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();
			$formDataResponseIds = DB::table(DC::TABLE_FORM_RSP)->pluck('id')->all();
			$pipelineIds = DB::table(DC::TABLE_PIPELINES)->pluck('id')->all();

			shuffle($fieldIds);

			$pickCount = (int) floor(count($fieldIds) * 0.25);
			if ($pickCount < 1) $pickCount = 1;

			$pickedFieldIds = array_slice($fieldIds, 0, $pickCount);

			$now = now();
			$rows = [];
			$cap = self::HARD_CAP;
			foreach ($pickedFieldIds as $fieldId) {
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					$out->writeln('<comment>[FormFieldResponseSeeder]</comment> Seeding time limit reached, stopping early.');
					break;
				}
				$iterations = random_int(1, 8);

				for ($i = 1; $i <= $iterations; $i++) {
					if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
						$out->writeln('<comment>[FormFieldResponseSeeder]</comment> Seeding time limit reached, stopping early.');
						break 2;
					}
					if (--$cap <= 0) {
						$out->writeln('<comment>[FormFieldResponseSeeder]</comment> Reached hard cap of ' . self::HARD_CAP . ' records, stopping seeding.');
						break 2;
					}
					if ($created >= self::HARD_CAP) break 2;

					$type = FieldType::cases()[array_rand(FieldType::cases())];
					$formId = $formIds[array_rand($formIds)];

					$userId = null;
					if ($userIds && random_int(0, 100) < 55) $userId = $userIds[array_rand($userIds)];

					$formDataResponseId = null;
					if ($formDataResponseIds && random_int(0, 100) < 65) $formDataResponseId = $formDataResponseIds[array_rand($formDataResponseIds)];

					$name = $this->fakeNameToken($type);
					$htmlId = $this->fakeHtmlId($name);
					$htmlLabel = $this->fakeHtmlLabel($type, $name);

					[$value, $checked] = $this->fakeValueAndChecked($type);

					$creator = $userIds ? $userIds[array_rand($userIds)] : null;
					$updater = $creator;

					$rows[] = [
						'id' => (string) Str::uuid(),
						'type' => $type->value,

						FC::COL_FM_ID => $formId,
						UC::COL_USER_ID => $userId,
						FC::COL_FM_DT_RSP_ID => $formDataResponseId,

						'name' => $name,
						FC::COL_HTML_LB => $htmlLabel,
						FC::COL_HTML_ID => $htmlId,

						'value' => $value,
						'checked' => $checked,

						FC::COL_NM_ID => null,
						FC::COL_SUBJ_ID => null,
						FC::COL_EML_ID => null,
						FC::COL_PPL_ID => ($pipelineIds && random_int(0, 100) < 25) ? $pipelineIds[array_rand($pipelineIds)] : null,

						DC::COL_TABLE_CREATOR => $creator,
						DC::COL_TABLE_UPDATER => $updater,
						DC::COL_C_AT => $now,
						DC::COL_U_AT => $now,

						'metadata' => json_encode([
							'form_field_id' => $fieldId,
							'iteration' => $i,
							'seed' => static::class,
						], JSON_UNESCAPED_UNICODE),
					];

					$created++;

					if (count($rows) >= 1000) {
						DB::table(DC::TABLE_FM_FLD_RSP)->insert($rows);
						$rows = [];
					}
				}
			}

			if ($rows) DB::table(DC::TABLE_FM_FLD_RSP)->insert($rows);

			Log::info(static::class . ' completed', [
				'created' => $created,
				'hard_cap' => self::HARD_CAP,
				'picked_field_ids' => count($pickedFieldIds),
				'total_field_ids' => count($fieldIds),
			]);
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed', [
				'created' => $created,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
	}

	private function fakeNameToken(FieldType $type): string
	{
		$base = match ($type) {
			FieldType::Email => 'email',
			FieldType::Tel => 'phone',
			FieldType::Number, FieldType::Range => 'number',
			FieldType::Date, FieldType::Time, FieldType::DateTimeLocal, FieldType::Month, FieldType::Week => 'date',
			FieldType::Url => 'url',
			FieldType::Password => 'password',
			FieldType::Search => 'search',
			FieldType::Textarea => 'message',
			FieldType::Checkbox => 'agree',
			FieldType::RadioGroup, FieldType::Select => 'choice',
			FieldType::File => 'file',
			default => 'field',
		};

		return $base . '_' . Str::lower(Str::random(8));
	}

	private function fakeHtmlId(string $name): string
	{
		$s = Str::slug($name, '_');
		if ($s === '') $s = 'field_' . Str::lower(Str::random(8));
		return $s;
	}

	private function fakeHtmlLabel(FieldType $type, string $name): string
	{
		$base = match ($type) {
			FieldType::Email => 'Email',
			FieldType::Tel => 'Phone',
			FieldType::Number => 'Number',
			FieldType::Date => 'Date',
			FieldType::Time => 'Time',
			FieldType::DateTimeLocal => 'Date & Time',
			FieldType::Month => 'Month',
			FieldType::Week => 'Week',
			FieldType::Url => 'Website',
			FieldType::Password => 'Password',
			FieldType::Search => 'Search',
			FieldType::Textarea => 'Message',
			FieldType::Checkbox => 'I agree',
			FieldType::RadioGroup => 'Select one',
			FieldType::Select => 'Select',
			FieldType::File => 'Attachment',
			FieldType::Color => 'Color',
			default => 'Field',
		};

		return $base . ' (' . $name . ')';
	}

	/**
	 * Returns [value, checked] aligned to your rule:
	 * - checked is only for Checkbox/RadioGroup; else forced null
	 * - value is nullable, stored as text
	 */
	private function fakeValueAndChecked(FieldType $type): array
	{
		$checked = null;

		if ($type->isCheckable()) {
			$checked = (bool) random_int(0, 1);
			$value = $type === FieldType::RadioGroup
				? ('opt_' . Str::lower(Str::random(6)))
				: ($checked ? '1' : null);

			return [$value, $checked];
		}

		$value = match ($type) {
			FieldType::Email => Str::lower(Str::random(8)) . '@example.test',
			FieldType::Tel => '+55 ' . random_int(11, 99) . ' 9' . random_int(1000, 9999) . '-' . random_int(1000, 9999),
			FieldType::Number => (string) random_int(0, 50000),
			FieldType::Range => (string) random_int(0, 100),
			FieldType::Date => now()->subDays(random_int(0, 1200))->toDateString(),
			FieldType::Time => now()->subMinutes(random_int(0, 1440))->format('H:i'),
			FieldType::DateTimeLocal => now()->subDays(random_int(0, 365))->format('Y-m-d\TH:i'),
			FieldType::Month => now()->subMonths(random_int(0, 36))->format('Y-m'),
			FieldType::Week => now()->subWeeks(random_int(0, 52))->format('o-\WW'),
			FieldType::Url => 'https://example.test/' . Str::lower(Str::random(10)),
			FieldType::Password => Str::random(14),
			FieldType::Search => Str::lower(Str::random(10)),
			FieldType::Textarea => 'msg_' . Str::random(40),
			FieldType::Select => 'opt_' . Str::lower(Str::random(6)),
			FieldType::Color => '#' . strtoupper(Str::random(6)),
			FieldType::File => 'file_' . Str::lower(Str::random(10)) . '.bin',
			default => 'txt_' . Str::random(18),
		};

		if (random_int(0, 100) < 10) $value = null;

		return [$value, $checked];
	}
}
