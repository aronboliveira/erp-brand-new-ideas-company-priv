<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC};
use App\Enums\{AppModuleType, FieldType};
use App\Models\{CustomQuestion};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class CustomQuestionSeeder extends Seeder
{
	private ConsoleOutput $out;

	// private const HARD_CAP = 16000;
	private const HARD_CAP = 2;
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

		$this->out->writeln("<info>[CustomQuestionSeeder]</info> target={$target} types=" . count($types));

		$creatorId = $this->firstUserId();
		$cfIds = DB::table(DC::TABLE_CUSTOM_FIELDS)->select('id')->pluck('id')->all();

		$created = 0;
		$i = 0;

		while ($created < $target) {
			$t = $types[$i % count($types)];
			$variant = intdiv($i, count($types)) % $variantsPerType;
			$module = $modules[($i + $variant) % count($modules)];
			$i++;

			$linkToCustomField = $cfIds !== [] && (($variant % 2) === 1);
			$customFieldId = $linkToCustomField ? $cfIds[array_rand($cfIds)] : null;

			$question = $this->uniqueQuestion(self::ATTEMPT_LIMIT);
			$name = $this->uniqueNameFromQuestion($question, self::ATTEMPT_LIMIT);

			$client = $this->buildClientFieldPayloadForType($t, $variant, $name, $module);
			$html   = $this->buildHtmlLinkedPayloadForQuestion($question);

			$payload = array_merge([
				'question'       => $question,
				DC::COL_IR       => (($variant % 3) === 0 ? null : (($variant % 2) === 0 ? '1' : '0')), // null sometimes to trigger fallback
				'tags'           => ['seed', 'custom_question', $t->value],
				FC::COL_CT_FD_ID => $customFieldId,
			], $client, $html);

			$this->out->writeln(
				" - creating CustomQuestion type={$payload['type']} module={$payload['module']} variant={$variant} linked_cf=" . ($customFieldId ? 'yes' : 'no')
			);

			try {
				$m = new CustomQuestion();
				$m->fill($payload);
				if ($creatorId) $m->setAttribute(DC::COL_TABLE_CREATOR, $creatorId);
				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed to create CustomQuestion', [
					'table' => DC::TABLE_CUSTOM_QUESTIONS,
					'type'  => $payload['type'] ?? null,
					'q'     => $question,
					'err'   => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		}
	}

	private function buildClientFieldPayloadForType(FieldType $t, int $variant, string $name, string $module): array
	{
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

		$minlength = $isTextual ? (($variant % 4) === 0 ? 5 : 0) : null;
		$maxlength = $isTextual ? (($variant % 4) === 1 ? 64 : 255) : null;

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
				$max = '100';
				$step = '1';
			} else {
				$min = '-5';
				$max = '5';
				$step = '0.25';
			}
		} elseif ($isDateTime) {
			if ($type === 'time') {
				$min = '08:00';
				$max = '18:00';
				$step = '60';
			} elseif ($type === 'month') {
				$min = '2020-01';
				$max = '2030-12';
				$step = '1';
			} elseif ($type === 'week') {
				$min = '2020-W01';
				$max = '2030-W52';
				$step = '1';
			} elseif ($type === 'datetime-local') {
				$min = '2020-01-01T00:00';
				$max = '2030-12-31T23:59';
				$step = '60';
			} else {
				$min = '2020-01-01';
				$max = '2030-12-31';
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
				'image/jpeg',
				'.pdf',
			];
		}

		return [
			'name'           => $name,
			'type'           => $type,
			'module'         => $module,
			'description'    => "Seeded CustomQuestion ({$type}) variant {$variant}",
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

	private function buildHtmlLinkedPayloadForQuestion(string $question): array
	{
		return [
			'aria'      => ['aria-label' => $question],
			'dataset'   => ['data-question' => 'true', 'data-seeded' => 'true'],
			'selectors' => ['.seeded', '#cq_' . Str::random(6)],
			'size'      => ['width' => (random_int(0, 1) ? '480px' : '100%')],
		];
	}

	private function uniqueQuestion(int $attemptLimit): string
	{
		$attempts = 0;
		do {
			$attempts++;
			$candidate = 'Question ' . Str::upper(Str::random(8));
			$exists = DB::table(DC::TABLE_CUSTOM_QUESTIONS)->where('question', $candidate)->exists();
		} while ($exists && $attempts < $attemptLimit);

		if ($exists) $candidate = 'Question ' . Str::uuid();

		return $candidate;
	}

	private function uniqueNameFromQuestion(string $question, int $attemptLimit): string
	{
		$attempts = 0;
		do {
			$attempts++;
			$candidate = 'cq_' . Str::slug($question, '_') . '_' . Str::lower(Str::random(4));
			$exists = DB::table(DC::TABLE_CUSTOM_QUESTIONS)->where('name', $candidate)->exists();
		} while ($exists && $attempts < $attemptLimit);

		if ($exists) $candidate = 'cq_' . Str::uuid();

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
