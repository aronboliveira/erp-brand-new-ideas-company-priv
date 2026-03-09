<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC};
use App\Enums\{AppModuleType, FieldType};
use App\Models\FormField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class FormFieldSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const HARD_CAP = 16000;
	private const ATTEMPT_LIMIT = 80;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		$types   = FieldType::cases();
		$modules = $this->moduleValues();

		$variantsPerType = 6;
		$rawTarget = max(64, count($types) * $variantsPerType * min(4, count($modules)));
		$target = $this->targetCount($rawTarget);
		$target = min($target, self::HARD_CAP);

		$this->out->writeln("<info>[FormFieldSeeder]</info> target={$target} types=" . count($types));

		$creatorId = $this->firstUserId();

		$formIds = DB::table(DC::TABLE_FORM_BUILD)->select('id')->pluck('id')->all();
		if ($formIds === []) {
			$this->out->writeln("<comment>[FormFieldSeeder]</comment> no forms found; creating minimal forms via raw insert");
			$formIds = $this->ensureFormsExist($creatorId, 64);
		}

		$cqIds = DB::table(DC::TABLE_CUSTOM_QUESTIONS)->select('id')->pluck('id')->all();

		$created = 0;
		$i = 0;

		while ($created < $target) {
			$t = $types[$i % count($types)];
			$variant = intdiv($i, count($types)) % $variantsPerType;
			$module = $modules[($i + $variant) % count($modules)];
			$formId = $formIds[$i % count($formIds)];
			$i++;

			$linkToCustomQuestion = $cqIds !== [] && (($variant % 2) === 1);
			$customQuestionId = $linkToCustomQuestion ? $cqIds[array_rand($cqIds)] : null;

			$name = $this->uniqueName('ff_', self::ATTEMPT_LIMIT, DC::TABLE_FM_FD, 'name');

			$client = $this->buildClientFieldPayloadForType($t, $variant, $name, $module);
			$html   = $this->buildHtmlLinkedPayload($name);

			$email = (random_int(0, 3) === 0) ? ("seed_" . Str::lower(Str::random(6)) . "@example.com") : null;

			$payload = array_merge([
				FC::COL_FM_ID    => $formId,
				'email'          => $email,
				FC::COL_CT_QT_ID => $customQuestionId,
				'tags'           => ['seed', 'form_field', $t->value],
			], $client, $html);

			$this->out->writeln(
				" - creating FormField form={$formId} type={$payload['type']} module={$payload['module']} variant={$variant} linked_cq=" . ($customQuestionId ? 'yes' : 'no')
			);

			try {
				$m = new FormField();
				$m->fill($payload);
				if ($creatorId) $m->setAttribute(DC::COL_TABLE_CREATOR, $creatorId);
				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed to create FormField', [
					'table' => DC::TABLE_FM_FD,
					'type'  => $payload['type'] ?? null,
					'name'  => $payload['name'] ?? null,
					'form'  => $formId,
					'err'   => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		}
	}

	private function buildClientFieldPayloadForType(FieldType $t, int $variant, string $name, string $module): array
	{
		// Same discipline used in CustomQuestionSeeder
		$type = $t->value;

		$isTextual  = method_exists($t, 'isTextual') ? (bool) $t->isTextual() : in_array($type, ['text', 'textarea', 'email', 'url', 'password', 'tel', 'search'], true);
		$isNumeric  = method_exists($t, 'isNumeric') ? (bool) $t->isNumeric() : in_array($type, ['number', 'range'], true);
		$isDateTime = method_exists($t, 'isDateTime') ? (bool) $t->isDateTime() : in_array($type, ['date', 'time', 'datetime-local', 'month', 'week'], true);

		$isTextarea = $type === 'textarea';
		$isSelect   = $type === 'select';
		$isFile     = $type === 'file';
		$isEmail    = $type === 'email';
		$isUrl      = $type === 'url';
		$isPassword = $type === 'password';

		$required = ($variant % 3) === 0;
		$disabled = ($variant % 13) === 0;
		$readonly = $isTextual ? (($variant % 7) === 0) : null;

		$multiline = $isTextual ? ($isTextarea ? true : (($variant % 5) === 0)) : null;

		$multipleAllowed = $isEmail || $isFile || $isSelect;
		$multiple = $multipleAllowed ? (($variant % 2) === 1) : null;

		$autocapAllowed = $isTextual && !$isEmail && !$isUrl && !$isPassword;
		$autocap = $autocapAllowed ? (($variant % 2) === 0) : null;

		$autocomplete = $isTextual ? (($variant % 3) !== 2) : null;
		$autocorrect  = $autocapAllowed ? (($variant % 3) === 1) : null;

		$placeholder = $isTextual ? ("Enter " . Str::headline($name)) : null;

		$pattern = null;
		if ($isTextual) {
			if ($isEmail) $pattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$';
			elseif ($isUrl) $pattern = '^(https?:\/\/)?.*(\.[a-zA-Z]{2,})(\/\S*)?$';
			elseif ($type === 'tel') $pattern = '^\+?[0-9\s\-\(\)]{7,15}$';
			elseif (($variant % 4) === 0) $pattern = '^[A-Za-z0-9 _-]{3,64}$';
		}

		$minlength = $isTextual ? (($variant % 4) === 0 ? 2 : 0) : null;
		$maxlength = $isTextual ? (($variant % 4) === 1 ? 80 : 255) : null;

		$rows = null;
		$cols = null;
		$wrap = null;
		$spellcheck = null;

		if ($isTextarea) {
			$rows = [2, 3, 5, 8][($variant % 4)];
			$cols = [20, 30, 40, 60][($variant % 4)];
			$wrap = (($variant % 2) === 0) ? 'soft' : 'hard';
			$spellcheck = ['false', 'default', 'true'][($variant % 3)];
		} elseif ($isTextual) {
			$spellcheck = ['false', 'default', 'true'][($variant % 3)];
		}

		$min = null;
		$max = null;
		$step = null;

		if ($isNumeric) {
			if (($variant % 2) === 0) {
				$min = '0';
				$max = '1000';
				$step = '10';
			} else {
				$min = '-1';
				$max = '1';
				$step = '0.1';
			}
		} elseif ($isDateTime) {
			if ($type === 'time') {
				$min = '09:00';
				$max = '17:00';
				$step = '60';
			} elseif ($type === 'month') {
				$min = '2021-01';
				$max = '2029-12';
				$step = '1';
			} elseif ($type === 'week') {
				$min = '2021-W01';
				$max = '2029-W52';
				$step = '1';
			} elseif ($type === 'datetime-local') {
				$min = '2021-01-01T00:00';
				$max = '2029-12-31T23:59';
				$step = '60';
			} else {
				$min = '2021-01-01';
				$max = '2029-12-31';
				$step = '1';
			}
		}

		$options = null;
		$optgroups = null;

		$supportsOptions = $isSelect || $isTextual || $isNumeric || $isDateTime;
		if ($supportsOptions) {
			$optgroups = [
				['label' => 'Group A'],
				['label' => 'Group B'],
			];

			$options = [
				['text' => 'Option 1', 'value' => 'opt_1', 'selected' => (($variant % 3) === 0), 'disabled' => false, 'group' => 'Group A'],
				['text' => 'Option 2', 'value' => 'opt_2', 'selected' => false, 'disabled' => (($variant % 5) === 0), 'group' => 'Group A'],
				['text' => 'Option 3', 'value' => 'opt_3', 'selected' => (($variant % 3) === 1), 'disabled' => false, 'group' => 'Group B'],
			];

			if ($isSelect && ($variant % 2) === 1) {
				$options[] = ['text' => 'Option 4', 'value' => 'opt_4', 'selected' => false, 'disabled' => false, 'group' => 'Group B'];
			}
		}

		$accepts = null;
		if ($isFile) {
			$accepts = [
				'image/png',
				'.pdf',
			];
		}

		return [
			'name'           => $name,
			'type'           => $type,
			'module'         => $module,
			'description'    => "Seeded FormField ({$type}) variant {$variant}",
			'default'        => null,
			'placeholder'    => $placeholder,
			'pattern'        => $pattern,

			'readonly'       => $readonly,
			'required'       => $required,
			'multiline'      => $multiline,
			'multiple'       => $multiple,
			'autocapitalize' => $autocap,
			'autocomplete'   => $autocomplete,
			'autocorrect'    => $autocorrect,
			'disabled'       => $disabled,

			'min'            => $min,
			'max'            => $max,
			'step'           => $step,

			'minlength'      => $minlength,
			'maxlength'      => $maxlength,

			'rows'           => $rows,
			'cols'           => $cols,
			'wrap'           => $wrap,
			'spellcheck'     => $spellcheck,

			'options'        => $options,
			'optgroups'      => $optgroups,
			'accepts'        => $accepts,
		];
	}

	private function buildHtmlLinkedPayload(string $name): array
	{
		return [
			'aria'      => ['aria-label' => Str::headline($name)],
			'dataset'   => ['data-seeded' => 'true', 'data-kind' => 'form_field'],
			'selectors' => ['.seeded', '#' . $name],
			'size'      => ['width' => (random_int(0, 1) ? '480px' : '100%')],
		];
	}

	private function ensureFormsExist(?string $creatorId, int $count): array
	{
		$count = $this->targetCount($count);
		$ids = [];

		$attempts = 0;
		while (count($ids) < $count && $attempts < ($count * 2)) {
			$attempts++;

			$id = (string) Str::uuid();
			$code = 'form_' . Str::lower(Str::random(10));

			$exists = DB::table(DC::TABLE_FORM_BUILD)->where('code', $code)->exists();
			if ($exists) continue;

			try {
				DB::table(DC::TABLE_FORM_BUILD)->insert([
					'id'         => $id,
					'name'       => 'Seeded Form ' . Str::upper(Str::random(4)),
					'code'       => $code,
					'is_active'  => true,
					'is_lead_active' => false,
					'created_at' => now(),
					'updated_at' => now(),
					DC::COL_TABLE_CREATOR => $creatorId,
				]);
				$ids[] = $id;
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed to insert FormBuilder row', [
					'table' => DC::TABLE_FORM_BUILD,
					'err'   => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		}

		return $ids ?: DB::table(DC::TABLE_FORM_BUILD)->select('id')->pluck('id')->all();
	}

	private function uniqueName(string $prefix, int $attemptLimit, string $table, string $column): string
	{
		$attempts = 0;
		do {
			$attempts++;
			$candidate = $prefix . Str::slug(Str::lower(Str::random(10)), '_');
			$exists = DB::table($table)->where($column, $candidate)->exists();
		} while ($exists && $attempts < $attemptLimit);

		if ($exists) $candidate = $prefix . Str::uuid();

		return $candidate;
	}

	private function moduleValues(): array
	{
		try {
			return array_map(fn($c) => $c->value, AppModuleType::cases());
		} catch (\Throwable) {
			return ['crm', 'hrm', 'finance', 'forms'];
		}
	}

	private function firstUserId(): ?string
	{
		try {
			return DB::table(DC::TABLE_USERS)->select('id')->orderBy('id')->limit(1)->value('id');
		} catch (\Throwable $e) {
			Log::debug(static::class . ' failed to read first user id', [
				'err'  => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}
	}

	private function targetCount(int $rawTotal): int
	{
		$mod = $rawTotal % 64;
		return $mod === 0 ? $rawTotal : ($rawTotal + (64 - $mod));
	}
}
