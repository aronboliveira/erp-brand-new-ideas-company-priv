<?php

namespace App\Traits;

use Illuminate\Database\{Eloquent\Model, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait DescribesClientForm
{
	/**
	 * Add <form> and HTMLFormElement-like columns.
	 *
	 * Notes:
	 * - Some HTMLFormElement properties are derived/runtime-only (elements, length, etc.) and are intentionally excluded.
	 */
	protected const DEFAULT_FORM_METHOD = 'post';
	protected const DEFAULT_FORM_ENCTYPE = 'application/x-www-form-urlencoded';
	protected const DEFAULT_FORM_AUTOCOMPLETE = 'on';

	protected const CLIENT_FORM_COLUMNS = [
		'form_name',
		'title',
		'description',
		'action',
		'method',
		'enctype',
		'target',
		'accept_charset',
		'autocomplete',
		'novalidate',
		'referrerpolicy',
		'allowed_methods',
		'allowed_enctypes',
		'submit_label',
		'reset_label',
		'show_reset',
		'prevent_double_submit',
		'submit_debounce_ms',
	];

	protected const ALLOWED_FORM_METHODS = ['get', 'post', 'dialog'];
	protected const ALLOWED_FORM_ENCTYPES = [
		'application/x-www-form-urlencoded',
		'multipart/form-data',
		'text/plain',
	];
	protected const ALLOWED_FORM_AUTOCOMPLETE = ['on', 'off'];

	protected const MAX_ACTION_LEN = 1024;
	protected const MAX_TITLE_LEN = 254;
	protected const MAX_NAME_LEN = 254;

	protected const DEBOUNCE_MIN_MS = 0;
	protected const DEBOUNCE_MAX_MS = 120000;

	/** @var array<string, array<string, bool>> */
	protected static array $schemaHasFormColumnCache = [];

	protected static function bootDescribesClientForm(): void
	{
		static::saving(function (Model $model): void {
			try {
				if (!method_exists($model, 'getTable')) return;

				$table = (string) $model->getTable();
				if ($table === '') return;

				static::normalizeClientFormAttributes($model, $table);
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed to normalize client form attributes', [
					'table' => method_exists($model, 'getTable') ? $model->getTable() : null,
					'id'    => method_exists($model, 'getAttribute') ? $model->getAttribute('id') : null,
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		});
	}

	protected function addClientFormColumns(Blueprint $table): void
	{
		// Primary identification / semantics
		$table->string('form_name', 254)->nullable()->index();     // mirrors <form name="">
		$table->string('title', 254)->nullable();                  // optional UI title (not native attr, but commonly mapped)
		$table->text('description')->nullable();                   // optional help text/summary for client rendering

		// Submission endpoint & behavior
		$table->string('action', 1024)->nullable();                // <form action="">
		$table->string('method', 16)->nullable()->index();         // <form method="get|post|dialog"> (store raw, normalize in model later)
		$table->string('enctype', 64)->nullable();                 // <form enctype="">
		$table->string('target', 64)->nullable();                  // <form target="">
		$table->string('accept_charset', 128)->nullable();          // <form accept-charset="">
		$table->string('autocomplete', 32)->nullable();             // <form autocomplete="on|off">

		// Validation controls
		$table->boolean('novalidate')->nullable();                 // <form novalidate>

		// Linking / security-adjacent toggles (client-side semantics; validate/normalize in model later)
		$table->string('referrerpolicy', 64)->nullable();           // not a standard <form> attr in all contexts, but used in fetch/navigation contexts
		$table->json('allowed_methods')->nullable();                // optional allowlist, e.g. ["GET","POST"]
		$table->json('allowed_enctypes')->nullable();               // optional allowlist, e.g. ["application/x-www-form-urlencoded", "multipart/form-data"]

		// Client runtime hints (non-native; commonly needed by builders)
		$table->string('submit_label', 128)->nullable();            // text for submit button (UI builder)
		$table->string('reset_label', 128)->nullable();             // text for reset button (UI builder)
		$table->boolean('show_reset')->nullable();                  // UI hint
		$table->boolean('prevent_double_submit')->nullable();       // UI hint
		$table->unsignedInteger('submit_debounce_ms')->nullable();  // UI hint
	}

	/**
	 * Drop columns added by addClientFormColumns().
	 */
	protected function dropClientFormColumns(Blueprint $table, string $tableName): void
	{
		$cols = [
			'form_name',
			'title',
			'description',
			'action',
			'method',
			'enctype',
			'target',
			'accept_charset',
			'autocomplete',
			'novalidate',
			'referrerpolicy',
			'allowed_methods',
			'allowed_enctypes',
			'submit_label',
			'reset_label',
			'show_reset',
			'prevent_double_submit',
			'submit_debounce_ms',
		];

		foreach ($cols as $col) {
			try {
				Schema::hasColumn($tableName, $col) && $table->dropColumn($col);
			} catch (\Exception $e) {
				Log::warning(
					static::class . ' failed to drop column on ' . $tableName,
					[
						'column' => $col,
						'error'  => $e->getMessage(),
						'file'   => $e->getFile(),
						'line'   => $e->getLine(),
					]
				);
			}
		}
	}

	public static function clientFormColumns(): array
	{
		return [
			'form_name',
			'title',
			'description',
			'action',
			'method',
			'enctype',
			'target',
			'accept_charset',
			'autocomplete',
			'novalidate',
			'referrerpolicy',
			'allowed_methods',
			'allowed_enctypes',
			'submit_label',
			'reset_label',
			'show_reset',
			'prevent_double_submit',
			'submit_debounce_ms',
		];
	}

	public static function clientFormSubmissionColumns(): array
	{
		return ['action', 'method', 'enctype', 'target', 'accept_charset', 'autocomplete', 'referrerpolicy'];
	}

	public static function clientFormConstraintColumns(): array
	{
		return ['novalidate', 'allowed_methods', 'allowed_enctypes'];
	}

	public static function clientFormUiColumns(): array
	{
		return ['submit_label', 'reset_label', 'show_reset', 'prevent_double_submit', 'submit_debounce_ms', 'title', 'description'];
	}

	protected static function normalizeClientFormAttributes(Model $model, string $table): void
	{
		// semantic identification
		static::normalizeStringColumn($model, $table, 'form_name', self::MAX_NAME_LEN, true);
		static::normalizeStringColumn($model, $table, 'title', self::MAX_TITLE_LEN, false);
		static::normalizeNullableTextColumn($model, $table, 'description');

		// submit behavior
		static::normalizeStringColumn($model, $table, 'action', self::MAX_ACTION_LEN, false);
		static::normalizeFormMethod($model, $table);
		static::normalizeFormEnctype($model, $table);
		static::normalizeStringColumn($model, $table, 'target', 64, false);
		static::normalizeStringColumn($model, $table, 'accept_charset', 128, false);
		static::normalizeFormAutocomplete($model, $table);

		// validation controls
		static::normalizeNullableBoolColumn($model, $table, 'novalidate');

		// security-ish / allowlists
		static::normalizeStringColumn($model, $table, 'referrerpolicy', 64, false);
		static::normalizeAllowedMethods($model, $table);
		static::normalizeAllowedEnctypes($model, $table);

		// UI hints
		static::normalizeStringColumn($model, $table, 'submit_label', 128, false);
		static::normalizeStringColumn($model, $table, 'reset_label', 128, false);
		static::normalizeNullableBoolColumn($model, $table, 'show_reset');
		static::normalizeNullableBoolColumn($model, $table, 'prevent_double_submit');
		static::normalizeDebounceMs($model, $table);
	}

	protected static function normalizeFormMethod(Model $model, string $table): void
	{
		if (!static::hasFormColumnCached($table, 'method')) return;

		$raw = $model->getAttribute('method');
		$val = strtolower(trim((string) $raw));

		if ($val === '') $val = null;
		if ($val !== null && !in_array($val, self::ALLOWED_FORM_METHODS, true)) {
			Log::debug(static::class . ' invalid form method; keeping raw value', [
				'table' => $table,
				'id'    => $model->getAttribute('id'),
				'raw'   => $raw,
			]);
			return;
		}

		// keep nullable for now; consumers can fallback to DEFAULT_FORM_METHOD
		$model->setAttribute('method', $val);
	}

	protected static function normalizeFormEnctype(Model $model, string $table): void
	{
		if (!static::hasFormColumnCached($table, 'enctype')) return;

		$raw = $model->getAttribute('enctype');
		$val = strtolower(trim((string) $raw));
		if ($val === '') $val = null;

		if ($val !== null && !in_array($val, self::ALLOWED_FORM_ENCTYPES, true)) {
			Log::debug(static::class . ' invalid enctype; keeping raw value', [
				'table' => $table,
				'id'    => $model->getAttribute('id'),
				'raw'   => $raw,
			]);
			return;
		}

		$model->setAttribute('enctype', $val);
	}

	protected static function normalizeFormAutocomplete(Model $model, string $table): void
	{
		if (!static::hasFormColumnCached($table, 'autocomplete')) return;

		$raw = $model->getAttribute('autocomplete');
		$val = strtolower(trim((string) $raw));
		if ($val === '') $val = null;

		if ($val !== null && !in_array($val, self::ALLOWED_FORM_AUTOCOMPLETE, true)) {
			Log::debug(static::class . ' invalid autocomplete; keeping raw value', [
				'table' => $table,
				'id'    => $model->getAttribute('id'),
				'raw'   => $raw,
			]);
			return;
		}

		$model->setAttribute('autocomplete', $val);
	}

	protected static function normalizeAllowedMethods(Model $model, string $table): void
	{
		if (!static::hasFormColumnCached($table, 'allowed_methods')) return;

		$raw = $model->getAttribute('allowed_methods');
		$list = static::staticNormalizeStringList($raw);

		// store as normalized lowercase tokens; keep nullable
		if (!$list) {
			$model->setAttribute('allowed_methods', null);
			return;
		}

		$out = [];
		foreach ($list as $m) {
			$mm = strtolower(trim($m));
			if ($mm === '') continue;
			if (!in_array($mm, self::ALLOWED_FORM_METHODS, true)) continue;
			$out[] = $mm;
		}

		$out = array_values(array_unique($out));
		$model->setAttribute('allowed_methods', $out ?: null);
	}

	protected static function normalizeAllowedEnctypes(Model $model, string $table): void
	{
		if (!static::hasFormColumnCached($table, 'allowed_enctypes')) return;

		$raw = $model->getAttribute('allowed_enctypes');
		$list = static::staticNormalizeStringList($raw);

		if (!$list) {
			$model->setAttribute('allowed_enctypes', null);
			return;
		}

		$out = [];
		foreach ($list as $e) {
			$ee = strtolower(trim($e));
			if ($ee === '') continue;
			if (!in_array($ee, self::ALLOWED_FORM_ENCTYPES, true)) continue;
			$out[] = $ee;
		}

		$out = array_values(array_unique($out));
		$model->setAttribute('allowed_enctypes', $out ?: null);
	}

	protected static function normalizeDebounceMs(Model $model, string $table): void
	{
		if (!static::hasFormColumnCached($table, 'submit_debounce_ms')) return;

		$raw = $model->getAttribute('submit_debounce_ms');
		if ($raw === null || $raw === '') {
			$model->setAttribute('submit_debounce_ms', null);
			return;
		}

		if (!is_numeric($raw)) {
			Log::debug(static::class . ' invalid submit_debounce_ms; nullifying', [
				'table' => $table,
				'id'    => $model->getAttribute('id'),
				'raw'   => $raw,
			]);
			$model->setAttribute('submit_debounce_ms', null);
			return;
		}

		$val = (int) $raw;
		if ($val < self::DEBOUNCE_MIN_MS) $val = self::DEBOUNCE_MIN_MS;
		if ($val > self::DEBOUNCE_MAX_MS) $val = self::DEBOUNCE_MAX_MS;

		$model->setAttribute('submit_debounce_ms', $val);
	}

	public function effectiveFormMethod(): string
	{
		$m = strtolower(trim((string) $this->getAttribute('method')));
		return in_array($m, self::ALLOWED_FORM_METHODS, true) ? $m : self::DEFAULT_FORM_METHOD;
	}

	public function effectiveFormEnctype(): string
	{
		$e = strtolower(trim((string) $this->getAttribute('enctype')));
		return in_array($e, self::ALLOWED_FORM_ENCTYPES, true) ? $e : self::DEFAULT_FORM_ENCTYPE;
	}

	public function formUsesMultipart(): bool
	{
		return $this->effectiveFormEnctype() === 'multipart/form-data';
	}

	public function formAutocompleteEnabled(): bool
	{
		$v = strtolower(trim((string) $this->getAttribute('autocomplete')));
		if ($v === '') $v = self::DEFAULT_FORM_AUTOCOMPLETE;
		return $v !== 'off';
	}

	public function formHasClientValidationDisabled(): bool
	{
		// nullable flag: treat null as false for evaluation
		return static::parseBoolean($this->getAttribute('novalidate'), false);
	}

	public function formAllowlistMethods(): ?array
	{
		$list = $this->getAttribute('allowed_methods');
		$list = static::staticNormalizeStringList($list);
		if (!$list) return null;

		$out = [];
		foreach ($list as $m) {
			$mm = strtolower(trim((string) $m));
			if (in_array($mm, self::ALLOWED_FORM_METHODS, true)) $out[] = $mm;
		}
		$out = array_values(array_unique($out));
		return $out ?: null;
	}

	public function formAllowlistEnctypes(): ?array
	{
		$list = $this->getAttribute('allowed_enctypes');
		$list = static::staticNormalizeStringList($list);
		if (!$list) return null;

		$out = [];
		foreach ($list as $e) {
			$ee = strtolower(trim((string) $e));
			if (in_array($ee, self::ALLOWED_FORM_ENCTYPES, true)) $out[] = $ee;
		}
		$out = array_values(array_unique($out));
		return $out ?: null;
	}

	/**
	 * Evaluates whether current settings are consistent with allowlists (if present).
	 * Intended for "preflight" checks before exposing to clients.
	 */
	public function formConstraintsOk(): bool
	{
		$mAllow = $this->formAllowlistMethods();
		$eAllow = $this->formAllowlistEnctypes();

		$m = $this->effectiveFormMethod();
		$e = $this->effectiveFormEnctype();

		if ($mAllow && !in_array($m, $mAllow, true)) return false;
		if ($eAllow && !in_array($e, $eAllow, true)) return false;

		return true;
	}

	protected static function hasFormColumnCached(string $table, string $column): bool
	{
		if (isset(self::$schemaHasFormColumnCache[$table][$column]))
			return self::$schemaHasFormColumnCache[$table][$column];

		try {
			return self::$schemaHasFormColumnCache[$table][$column] = Schema::hasColumn($table, $column);
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed schema::hasColumn', [
				'table'  => $table,
				'column' => $column,
				'error'  => $e->getMessage(),
				'file'   => $e->getFile(),
				'line'   => $e->getLine(),
			]);
			return self::$schemaHasFormColumnCache[$table][$column] = false;
		}
	}

	protected static function normalizeStringColumn(Model $model, string $table, string $col, int $maxLen, bool $slugLike = false): void
	{
		if (!static::hasFormColumnCached($table, $col)) return;

		$raw = $model->getAttribute($col);
		if ($raw === null) return;

		$val = trim((string) $raw);
		if ($val === '') {
			$model->setAttribute($col, null);
			return;
		}

		if ($maxLen > 0 && mb_strlen($val) > $maxLen)
			$val = mb_substr($val, 0, $maxLen);

		$model->setAttribute($col, $val);
	}

	protected static function normalizeNullableTextColumn(Model $model, string $table, string $col): void
	{
		if (!static::hasFormColumnCached($table, $col)) return;

		$raw = $model->getAttribute($col);
		if ($raw === null) return;

		$val = trim((string) $raw);
		$model->setAttribute($col, $val === '' ? null : $val);
	}

	protected static function normalizeNullableBoolColumn(Model $model, string $table, string $col): void
	{
		if (!static::hasFormColumnCached($table, $col)) return;

		$raw = $model->getAttribute($col);

		if ($raw === null || $raw === '') {
			$model->setAttribute($col, null);
			return;
		}

		$model->setAttribute($col, static::parseBoolean($raw, false));
	}

	protected static function parseBoolean(mixed $value, bool $default = false): bool
	{
		if (is_bool($value)) return $value;
		if ($value === null) return $default;

		$v = strtolower(trim((string) $value));
		return match ($v) {
			'1', 'true', 'yes', 'on'  => true,
			'0', 'false', 'no', 'off' => false,
			default                   => $default,
		};
	}

	protected static function staticNormalizeStringList(mixed $value): ?array
	{
		if ($value === null || $value === '') return null;

		$arr = is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?: [$value]) : (array) $value);

		$out = [];
		foreach ($arr as $v) {
			if (!is_scalar($v)) continue;
			$s = trim((string) $v);
			if ($s === '') continue;
			$out[] = $s;
		}

		$out = array_values(array_unique($out));
		return $out ?: null;
	}
}
