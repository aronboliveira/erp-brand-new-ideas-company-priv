<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{AppModuleType, FieldType, MimeType};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Log, Schema};

trait DescribesClientField
{
	protected const CLIENT_FIELD_COLS = [
		'name',
		'type',
		'module',
		'description',
		'default',
		'placeholder',
		'pattern',
		'readonly',
		'required',
		'multiline',
		'multiple',
		'autocapitalize',
		'autocomplete',
		'autocorrect',
		'disabled',
		'min',
		'max',
		'step',
		'minlength',
		'maxlength',
		'rows',
		'cols',
		'wrap',
		'spellcheck',
		'options',
		'optgroups',
		'accepts',
	];

	protected const CLIENT_FIELD_BOOL_COLS = [
		'readonly',
		'required',
		'multiline',
		'multiple',
		'autocapitalize',
		'autocomplete',
		'autocorrect',
		'disabled',
	];

	protected const CLIENT_FIELD_JSON_COLS = [
		'options',
		'optgroups',
		'accepts',
	];

	protected const CLIENT_FIELD_RANGE_COLS = [
		'min',
		'max',
		'step',
	];

	protected const CLIENT_FIELD_TEXT_LIMIT_COLS = [
		'minlength',
		'maxlength',
	];

	protected const CLIENT_FIELD_TEXTAREA_COLS = [
		'rows',
		'cols',
		'wrap',
	];

	protected const CLIENT_FIELD_VALID_WRAP = ['soft', 'hard'];
	protected const CLIENT_FIELD_VALID_SPELLCHECK = ['true', 'default', 'false'];

	/**
	 * Columns for mass “resolved field payload” operations.
	 * Useful for merge/overlay logic and for Schema::hasColumn loops.
	 */
	public static function clientFieldColumns(): array
	{
		return self::CLIENT_FIELD_COLS;
	}

	public static function clientFieldBooleanColumns(): array
	{
		return self::CLIENT_FIELD_BOOL_COLS;
	}

	public static function clientFieldJsonColumns(): array
	{
		return self::CLIENT_FIELD_JSON_COLS;
	}

	public static function clientFieldRangeColumns(): array
	{
		return self::CLIENT_FIELD_RANGE_COLS;
	}

	public static function clientFieldTextareaColumns(): array
	{
		return self::CLIENT_FIELD_TEXTAREA_COLS;
	}

	public static function clientFieldTextLimitColumns(): array
	{
		return self::CLIENT_FIELD_TEXT_LIMIT_COLS;
	}

	/**
	 * “Constraint-ish” subset, helpful when auditing field security/validation.
	 * (pattern/min/max/minlength/maxlength/required/disabled/readonly)
	 */
	public static function clientFieldConstraintColumns(): array
	{
		return array_values(array_unique([
			'pattern',
			'min',
			'max',
			'step',
			'minlength',
			'maxlength',
			'required',
			'disabled',
			'readonly',
		]));
	}

	/**
	 * Return only the client-field attributes present in this model instance AND schema.
	 * Designed to be called from a Model that uses this trait.
	 */
	protected function getClientFieldAttributes(): array
	{
		$out = [];
		$table = $this->getTable();

		foreach (self::clientFieldColumns() as $col) {
			if (!Schema::hasColumn($table, $col)) continue;
			$out[$col] = $this->getAttribute($col);
		}

		return $out;
	}

	/**
	 * Normalize a client-field associative array (not persisting).
	 * This is useful for “resolved/effective” payload computation.
	 */
	protected static function normalizeClientFieldPayload(array $attr): array
	{
		// Keep it intentionally light here; the heavy logic remains in boot hooks.
		// This method is used as a “pre-flight” for merges, API output, etc.

		foreach (self::CLIENT_FIELD_BOOL_COLS as $b) {
			if (array_key_exists($b, $attr)) $attr[$b] = filter_var($attr[$b], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
		}

		if (isset($attr['wrap']) && !in_array($attr['wrap'], self::CLIENT_FIELD_VALID_WRAP, true)) $attr['wrap'] = null;
		if (isset($attr['spellcheck']) && !in_array($attr['spellcheck'], self::CLIENT_FIELD_VALID_SPELLCHECK, true)) $attr['spellcheck'] = null;

		return $attr;
	}

	/**
	 * Merge precedence helper:
	 * $base overridden by $overrides only when override value is not null/empty.
	 * (Useful for your “source of truth is local table when valid” rule.)
	 */
	protected static function overlayIfMeaningful(array $base, array $overrides): array
	{
		foreach ($overrides as $k => $v) {
			if ($v === null) continue;
			if (is_string($v) && trim($v) === '') continue;
			$base[$k] = $v;
		}
		return $base;
	}

	protected static function bootDescribesClientField(): void
	{
		static::saving(function (Model $model): void {
			$table = $model->getTable();

			try {
				if (!Schema::hasTable($table)) return;

				static::normalizeClientFieldCore($model);
				static::normalizeClientFieldFlagsAndRanges($model);
				static::normalizeClientFieldOptions($model);
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed to normalize client-field attributes before saving', [
					'table' => $table,
					'id'    => $model->getAttribute('id'),
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		});
	}

	protected function addClientFieldColumns(
		Blueprint $table,
		bool $nullableModule = false,
		bool $enumType = false,
	): void {
		$table->string('name')->index();
		$enumType ? $table->enum('type', array_column(FieldType::cases(), 'value'))->default(FieldType::Text->value)->nullable() : $table->string('type')->default('text')->nullable();
		$nullableModule ? $table->string('module')->nullable() : $table->string('module');
		$table->text('description')->nullable();
		$table->string('default', 254)->nullable();
		$table->string('placeholder', 254)->nullable();
		$table->string('pattern', 1024)->nullable();
		$bool = function (string $name) use ($table): void {
			$col = $table->boolean($name);
			$col->nullable()->default(false);
		};
		$bool('readonly');
		$bool('required');
		$bool('multiline');
		$bool('multiple');
		$bool('autocapitalize');
		$bool('autocomplete');
		$bool('autocorrect');
		$bool('disabled');
		$str = function (string $name, ?string $default = null) use ($table): void {
			$col = $table->string($name);
			$col->nullable()->default($default);
		};
		$str('min', '0');
		$str('max', '9007199254740991');
		$str('step', 'any');
		$table->unsignedSmallInteger('minlength')->default(0)->nullable();
		$table->unsignedInteger('maxlength')->default(1024)->nullable();
		$table->unsignedInteger('rows')->default(2)->nullable();
		$table->unsignedInteger('cols')->default(20)->nullable();
		$table->enum('wrap', ['soft', 'hard'])->default('soft')->nullable();
		$table->enum('spellcheck', ['true', 'default', 'false'])->nullable();
		$table->json('options')->nullable();
		$table->json('optgroups')->nullable();
		$table->json('accepts')->nullable();
	}

	protected function dropClientFieldColumns(
		Blueprint $table,
		string $tableName,
	): void {
		$cols = [
			'name',
			'type',
			'module',
			'description',
			'default',
			'placeholder',
			'pattern',
			'readonly',
			'required',
			'multiline',
			'multiple',
			'autocapitalize',
			'autocomplete',
			'autocorrect',
			'disabled',
			'min',
			'max',
			'step',
			'minlength',
			'maxlength',
			'rows',
			'cols',
			'wrap',
			'spellcheck',
			'options',
			'optgroups',
			'accepts',
		];
		foreach ($cols as $col) {
			try {
				Schema::hasColumn($tableName, $col) &&
					$table->dropColumn($col);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
						. $col
						. ': '
						. $e->getMessage()
				);
			}
		}
	}

	protected static function hasColumn(Model $m, string $col): bool
	{
		try {
			return Schema::hasColumn($m->getTable(), $col);
		} catch (\Throwable $e) {
			Log::debug(static::class . ' Schema::hasColumn failed', [
				'table' => $m->getTable(),
				'col'   => $col,
				'error' => $e->getMessage(),
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
			]);
			return false;
		}
	}

	protected static function normalizeClientFieldCore(Model $model): void
	{
		if (self::hasColumn($model, 'name')) {
			$name = trim((string) $model->getAttribute('name'));
			if ($name === '') $name = defined(DC::class . '::DEFAULT_TT') ? DC::DEFAULT_TT : 'Untitled';
			$model->setAttribute('name', $name);
		}

		if (self::hasColumn($model, 'type')) {
			$rawType = $model->getAttribute('type');

			$type = null;
			if (enum_exists(FieldType::class) && method_exists(FieldType::class, 'normalize')) {
				try {
					$type = FieldType::normalize($rawType);
					$model->setAttribute('type', $type?->value ?? (string) $rawType);
				} catch (\Throwable $e) {
					Log::debug(static::class . ' failed to normalize FieldType', [
						'table' => $model->getTable(),
						'id'    => $model->getAttribute('id'),
						'raw'   => $rawType,
						'error' => $e->getMessage(),
						'file'  => $e->getFile(),
						'line'  => $e->getLine(),
					]);
					$model->setAttribute('type', is_scalar($rawType) ? (string) $rawType : 'text');
				}
			} else {
				$model->setAttribute('type', is_scalar($rawType) ? (string) $rawType : 'text');
			}
		}

		if (self::hasColumn($model, 'module')) {
			$rawModule = $model->getAttribute('module');

			if ($rawModule === null && static::colIsNullable($model, 'module')) {
				$model->setAttribute('module', null);
			} else {
				if (enum_exists(AppModuleType::class) && method_exists(AppModuleType::class, 'normalize')) {
					try {
						$module = AppModuleType::normalize($rawModule);
						$model->setAttribute('module', $module?->value ?? (string) $rawModule);
					} catch (\Throwable $e) {
						Log::debug(static::class . ' failed to normalize AppModuleType', [
							'table' => $model->getTable(),
							'id'    => $model->getAttribute('id'),
							'raw'   => $rawModule,
							'error' => $e->getMessage(),
							'file'  => $e->getFile(),
							'line'  => $e->getLine(),
						]);
						$model->setAttribute('module', is_scalar($rawModule) ? (string) $rawModule : null);
					}
				} else {
					$model->setAttribute('module', is_scalar($rawModule) ? (string) $rawModule : null);
				}
			}
		}

		if (self::hasColumn($model, 'description')) {
			$desc = $model->getAttribute('description');
			if ($desc !== null) {
				$desc = trim((string) $desc);
				if ($desc === '') $desc = null;
			}
			$model->setAttribute('description', $desc);
		}

		if (self::hasColumn($model, 'default')) {
			$def = $model->getAttribute('default');
			if ($def !== null) {
				$def = trim((string) $def);
				if ($def === '') $def = null;
			}
			$model->setAttribute('default', $def);
		}
	}

	protected static function normalizeClientFieldFlagsAndRanges(Model $model): void
	{
		$typeEnum = null;

		if (self::hasColumn($model, 'type') && enum_exists(FieldType::class) && method_exists(FieldType::class, 'normalize')) {
			try {
				$typeEnum = FieldType::normalize($model->getAttribute('type'));
			} catch (\Throwable $e) {
				Log::debug(static::class . ' failed to normalize FieldType for flags/ranges', [
					'table' => $model->getTable(),
					'id'    => $model->getAttribute('id'),
					'raw'   => $model->getAttribute('type'),
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		}

		$isTextual  = $typeEnum && method_exists($typeEnum, 'isTextual')  ? (bool) $typeEnum->isTextual()  : true;
		$isNumeric  = $typeEnum && method_exists($typeEnum, 'isNumeric')  ? (bool) $typeEnum->isNumeric()  : false;
		$isDateTime = $typeEnum && method_exists($typeEnum, 'isDateTime') ? (bool) $typeEnum->isDateTime() : false;

		$isTextarea = $typeEnum ? ($typeEnum === FieldType::Textarea) : false;
		$isFile     = $typeEnum ? ($typeEnum === FieldType::File)     : false;
		$isEmail    = $typeEnum ? ($typeEnum === FieldType::Email)    : false;
		$isUrl      = $typeEnum ? ($typeEnum === FieldType::Url)      : false;
		$isPassword = $typeEnum ? ($typeEnum === FieldType::Password) : false;
		$isSelect   = $typeEnum ? ($typeEnum === FieldType::Select)   : false;

		if (self::hasColumn($model, 'placeholder')) {
			$ph = $model->getAttribute('placeholder');
			if ($isTextual) {
				$ph = $ph !== null ? trim((string) $ph) : null;
				if ($ph === '') $ph = null;
			} else $ph = null;
			$model->setAttribute('placeholder', $ph);
		}

		if (self::hasColumn($model, 'pattern')) {
			$pattern = $model->getAttribute('pattern');
			if ($isTextual) {
				$pattern = $pattern !== null ? trim((string) $pattern) : null;
				if ($pattern === '') $pattern = null;

				if (!$pattern && self::hasColumn($model, 'type')) {
					$typeVal = (string) $model->getAttribute('type');
					if ($typeVal === FieldType::Email->value) $pattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$';
					elseif ($typeVal === FieldType::Url->value) $pattern = '^(https?:\/\/)?.*(\.[a-zA-Z]{2,})(\/\S*)?$';
					elseif ($typeVal === FieldType::Tel->value) $pattern = '^\+?[0-9\s\-\(\)]{7,15}$';
				}
			} else $pattern = null;

			$model->setAttribute('pattern', $pattern);
		}

		foreach (['required', 'disabled'] as $b) {
			if (!self::hasColumn($model, $b)) continue;
			$model->setAttribute($b, static::parseBoolean($model->getAttribute($b), false));
		}

		if (self::hasColumn($model, 'readonly')) {
			$model->setAttribute('readonly', $isTextual ? static::parseBoolean($model->getAttribute('readonly'), false) : null);
		}

		if (self::hasColumn($model, 'multiline')) {
			$model->setAttribute('multiline', $isTextual ? static::parseBoolean($model->getAttribute('multiline'), false) : null);
		}

		if (self::hasColumn($model, 'multiple')) {
			$allowed = $isEmail || $isFile || $isSelect;
			$model->setAttribute('multiple', $allowed ? static::parseBoolean($model->getAttribute('multiple'), false) : null);
		}

		$autocapAllowed = $isTextual && !$isEmail && !$isUrl && !$isPassword;

		if (self::hasColumn($model, 'autocapitalize')) {
			$model->setAttribute('autocapitalize', $autocapAllowed ? static::parseBoolean($model->getAttribute('autocapitalize'), false) : null);
		}

		if (self::hasColumn($model, 'autocomplete')) {
			$model->setAttribute('autocomplete', $isTextual ? static::parseBoolean($model->getAttribute('autocomplete'), false) : null);
		}

		if (self::hasColumn($model, 'autocorrect')) {
			$model->setAttribute('autocorrect', $autocapAllowed ? static::parseBoolean($model->getAttribute('autocorrect'), false) : null);
		}

		if (self::hasColumn($model, 'minlength') || self::hasColumn($model, 'maxlength')) {
			if ($isTextual) {
				$minlength = $model->getAttribute('minlength');
				$maxlength = $model->getAttribute('maxlength');

				$min = is_numeric($minlength) && (int) $minlength >= 0 ? (int) $minlength : 0;
				$max = is_numeric($maxlength) && (int) $maxlength > 0 ? (int) $maxlength : 1024;

				if ($min > $max) [$min, $max] = [$max, $min];

				if (self::hasColumn($model, 'minlength')) $model->setAttribute('minlength', $min);
				if (self::hasColumn($model, 'maxlength')) $model->setAttribute('maxlength', $max);
			} else {
				if (self::hasColumn($model, 'minlength')) $model->setAttribute('minlength', null);
				if (self::hasColumn($model, 'maxlength')) $model->setAttribute('maxlength', null);
			}
		}

		if (self::hasColumn($model, 'rows') || self::hasColumn($model, 'cols') || self::hasColumn($model, 'wrap')) {
			if ($isTextarea) {
				$rows = $model->getAttribute('rows');
				$cols = $model->getAttribute('cols');

				$rows = is_numeric($rows) && (int) $rows > 0 ? (int) $rows : 2;
				$cols = is_numeric($cols) && (int) $cols > 0 ? (int) $cols : 20;

				$wrap = $model->getAttribute('wrap');
				$wrap = in_array($wrap, ['soft', 'hard'], true) ? $wrap : 'soft';

				if (self::hasColumn($model, 'rows')) $model->setAttribute('rows', $rows);
				if (self::hasColumn($model, 'cols')) $model->setAttribute('cols', $cols);
				if (self::hasColumn($model, 'wrap')) $model->setAttribute('wrap', $wrap);
			} else {
				if (self::hasColumn($model, 'rows')) $model->setAttribute('rows', null);
				if (self::hasColumn($model, 'cols')) $model->setAttribute('cols', null);
				if (self::hasColumn($model, 'wrap')) $model->setAttribute('wrap', null);
			}
		}

		if (self::hasColumn($model, 'spellcheck')) {
			if ($isTextual) {
				$spell = $model->getAttribute('spellcheck');
				$spell = in_array($spell, ['true', 'false', 'default'], true) ? $spell : 'false';
				$model->setAttribute('spellcheck', $spell);
			} else $model->setAttribute('spellcheck', null);
		}

		if ($isNumeric || $isDateTime) static::normalizeRangeAttributes($model, $typeEnum);
		else {
			foreach (['min', 'max', 'step'] as $c) {
				if (!self::hasColumn($model, $c)) continue;
				$model->setAttribute($c, null);
			}
		}
	}

	protected static function normalizeClientFieldOptions(Model $model): void
	{
		if (!self::hasColumn($model, 'options') && !self::hasColumn($model, 'optgroups') && !self::hasColumn($model, 'accepts')) return;

		$typeEnum = null;
		if (self::hasColumn($model, 'type') && enum_exists(FieldType::class) && method_exists(FieldType::class, 'normalize')) {
			try {
				$typeEnum = FieldType::normalize($model->getAttribute('type'));
			} catch (\Throwable) {
				$typeEnum = null;
			}
		}

		$isTextual  = $typeEnum && method_exists($typeEnum, 'isTextual')  ? (bool) $typeEnum->isTextual()  : true;
		$isNumeric  = $typeEnum && method_exists($typeEnum, 'isNumeric')  ? (bool) $typeEnum->isNumeric()  : false;
		$isDateTime = $typeEnum && method_exists($typeEnum, 'isDateTime') ? (bool) $typeEnum->isDateTime() : false;
		$isSelect   = $typeEnum ? ($typeEnum === FieldType::Select) : false;
		$isFile     = $typeEnum ? ($typeEnum === FieldType::File)   : false;

		$supportsOptions = $isSelect || $isTextual || $isNumeric || $isDateTime;

		if ($supportsOptions) {
			if (self::hasColumn($model, 'options')) {
				$options = NormalizesArrays::normalizeArrayField($model->getAttribute('options'));
				$options = static::sanitizeOptionsArray($options);
				$model->setAttribute('options', $options ?: null);
			}
			if (self::hasColumn($model, 'optgroups')) {
				$optgroups = NormalizesArrays::normalizeArrayField($model->getAttribute('optgroups'));
				$optgroups = static::sanitizeOptgroupsArray($optgroups);
				$model->setAttribute('optgroups', $optgroups ?: null);
			}
		} else {
			if (self::hasColumn($model, 'options')) $model->setAttribute('options', null);
			if (self::hasColumn($model, 'optgroups')) $model->setAttribute('optgroups', null);
		}

		if (self::hasColumn($model, 'accepts')) {
			if ($isFile) {
				$accepts = NormalizesArrays::normalizeArrayField($model->getAttribute('accepts'));
				$accepts = static::sanitizeAcceptsArray($accepts);
				$model->setAttribute('accepts', $accepts ?: null);
			} else $model->setAttribute('accepts', null);
		}
	}

	protected static function normalizeRangeAttributes(Model $model, ?FieldType $typeEnum): void
	{
		$hasMin = self::hasColumn($model, 'min');
		$hasMax = self::hasColumn($model, 'max');
		$hasStep = self::hasColumn($model, 'step');
		if (!$hasMin && !$hasMax && !$hasStep) return;

		$typeEnum ??= FieldType::normalize($model->getAttribute('type'));

		$min  = trim((string) ($model->getAttribute('min') ?? ''));
		$max  = trim((string) ($model->getAttribute('max') ?? ''));
		$step = trim((string) ($model->getAttribute('step') ?? ''));

		$defaultNumericMin = defined(static::class . '::DEFAULT_NUMERIC_MIN') ? constant(static::class . '::DEFAULT_NUMERIC_MIN') : '0';
		$defaultNumericMax = defined(static::class . '::DEFAULT_NUMERIC_MAX') ? constant(static::class . '::DEFAULT_NUMERIC_MAX') : '9007199254740991';

		if ($typeEnum && method_exists($typeEnum, 'isNumeric') && $typeEnum->isNumeric()) {
			if ($min === '' || !is_numeric($min)) $min = $defaultNumericMin;
			if ($max === '' || !is_numeric($max)) $max = $defaultNumericMax;

			$minFloat = (float) $min;
			$maxFloat = (float) $max;
			if ($minFloat > $maxFloat) [$minFloat, $maxFloat] = [$maxFloat, $minFloat];

			$min = rtrim(rtrim(sprintf('%.14F', $minFloat), '0'), '.');
			$max = rtrim(rtrim(sprintf('%.14F', $maxFloat), '0'), '.');

			if ($step === '') $step = 'any';
			elseif ($step !== 'any') {
				if (!is_numeric($step) || (float) $step <= 0) $step = 'any';
				else $step = rtrim(rtrim(sprintf('%.14F', (float) $step), '0'), '.');
			}
		} else {
			[$defaultMin, $defaultMax] = static::defaultDateRange($typeEnum);

			if ($min === '' || !static::isValidDateBoundary($typeEnum, $min)) $min = $defaultMin;
			if ($max === '' || !static::isValidDateBoundary($typeEnum, $max)) $max = $defaultMax;
			if (static::compareDateBoundaries($typeEnum, $min, $max) > 0) [$min, $max] = [$max, $min];

			if ($step === '' || !ctype_digit($step) || (int) $step <= 0) $step = '1';
		}

		if ($hasMin) $model->setAttribute('min', $min);
		if ($hasMax) $model->setAttribute('max', $max);
		if ($hasStep) $model->setAttribute('step', $step);
	}

	protected static function defaultDateRange(?FieldType $type): array
	{
		return match ($type) {
			FieldType::Time          => ['00:00', '23:59'],
			FieldType::Month         => ['1970-01', '2099-12'],
			FieldType::Week          => ['1970-W01', '2099-W52'],
			FieldType::DateTimeLocal => ['1970-01-01T00:00', '2099-12-31T23:59'],
			default                  => ['1970-01-01', '2099-12-31'],
		};
	}

	protected static function isValidDateBoundary(?FieldType $type, string $value): bool
	{
		return static::toCarbonForBoundary($type, $value) instanceof Carbon;
	}

	protected static function compareDateBoundaries(?FieldType $type, string $a, string $b): int
	{
		$ca = static::toCarbonForBoundary($type, $a);
		$cb = static::toCarbonForBoundary($type, $b);

		if (!$ca || !$cb) return 0;
		if ($ca->equalTo($cb)) return 0;

		return $ca->lessThan($cb) ? -1 : 1;
	}

	protected static function toCarbonForBoundary(?FieldType $type, string $value): ?Carbon
	{
		try {
			return match ($type) {
				FieldType::Time          => Carbon::createFromFormat('H:i', $value),
				FieldType::Month         => Carbon::parse($value . '-01'),
				FieldType::Week          => Carbon::parse($value),
				FieldType::DateTimeLocal => Carbon::parse($value),
				default                  => Carbon::parse($value),
			};
		} catch (\Throwable) {
			return null;
		}
	}

	protected static function parseBoolean(mixed $value, bool $default = false): bool
	{
		if (is_bool($value)) return $value;
		if ($value === null) return $default;

		$value = strtolower(trim((string) $value));

		return match ($value) {
			'1', 'true', 'yes', 'on'  => true,
			'0', 'false', 'no', 'off' => false,
			default                   => $default,
		};
	}

	protected static function sanitizeOptionsArray(array $options): array
	{
		$result = [];

		foreach ($options as $option) {
			if (!is_array($option)) {
				if (is_scalar($option)) {
					$text = trim((string) $option);
					if ($text === '') continue;
					$result[] = [
						'text'     => $text,
						'value'    => $text,
						'selected' => false,
						'disabled' => false,
						'group'    => null,
					];
				}
				continue;
			}

			$text  = isset($option['text']) ? trim((string) $option['text']) : '';
			$value = isset($option['value']) ? trim((string) $option['value']) : '';

			if ($text === '' && $value === '') continue;
			if ($text === '') $text = $value;
			if ($value === '') $value = $text;

			$selected = array_key_exists('selected', $option)
				? static::parseBoolean($option['selected'], false)
				: false;

			$disabled = array_key_exists('disabled', $option)
				? static::parseBoolean($option['disabled'], false)
				: false;

			$group = isset($option['group']) ? trim((string) $option['group']) : null;
			if ($group === '') $group = null;

			$result[] = [
				'text'     => $text,
				'value'    => $value,
				'selected' => $selected,
				'disabled' => $disabled,
				'group'    => $group,
			];
		}

		$unique = [];
		$seen   = [];

		foreach ($result as $opt) {
			$key = $opt['value'] . '|' . ($opt['group'] ?? '');
			if (isset($seen[$key])) continue;
			$seen[$key] = true;
			$unique[]   = $opt;
		}

		return $unique;
	}

	protected static function sanitizeOptgroupsArray(array $optgroups): array
	{
		$labels = [];

		foreach ($optgroups as $group) {
			if (is_array($group) && isset($group['label'])) $label = trim((string) $group['label']);
			else $label = trim((string) $group);

			if ($label === '') continue;
			$labels[] = $label;
		}

		$labels = array_values(array_unique($labels));

		return array_map(fn(string $label) => ['label' => $label], $labels);
	}

	protected static function sanitizeAcceptsArray(array $accepts): array
	{
		$result = [];

		foreach ($accepts as $entry) {
			if (!is_string($entry)) continue;

			$entry = strtolower(trim($entry));
			if ($entry === '') continue;

			if (str_contains($entry, '/')) {
				$mime = MimeType::normalize($entry);
				$result[] = $mime?->value ?? $entry;
				continue;
			}

			$ext = ltrim($entry, '.');
			if ($ext === '') continue;

			$mime = MimeType::fromExtension($ext);
			if ($mime) $result[] = $mime->value;
			else $result[] = '.' . $ext;
		}

		return array_values(array_unique($result));
	}

	protected static function colIsNullable(Model $m, string $col): bool
	{
		try {
			$columns = Schema::getColumnListing($m->getTable());
			if (!in_array($col, $columns, true)) return true;
			return true;
		} catch (\Throwable) {
			return true;
		}
	}
}
