<?php

namespace App\Traits;

use Illuminate\Database\{Eloquent\Model, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait DescribesHtmlLinkedEntity
{

	protected const ALLOWED_ARIA = [
		'aria-label',
		'aria-labelledby',
		'aria-describedby',
		'aria-hidden',
		'aria-required',
		'aria-invalid',
		'aria-live',
		'aria-checked',
		'aria-selected',
		'aria-expanded',
		'aria-controls',
		'aria-current',
		'aria-valuemin',
		'aria-valuemax',
		'aria-valuenow',
		'aria-valuetext',
		'aria-level',
		'aria-orientation',
		'aria-pressed',
		'aria-readonly',
	];

	protected const HTML_LINKED_JSON_COLS = ['aria', 'dataset', 'selectors', 'size', 'tags'];

	protected const HTML_LINKED_COLS = [...self::HTML_LINKED_JSON_COLS];

	public static function htmlLinkedColumns(bool $includeTags = true): array
	{
		return $includeTags ? self::HTML_LINKED_COLS : ['aria', 'dataset', 'selectors', 'size'];
	}

	public static function htmlLinkedJsonColumns(bool $includeTags = true): array
	{
		return self::htmlLinkedColumns($includeTags);
	}

	/**
	 * Return html-linked attributes present in the model instance AND schema.
	 */
	protected function getHtmlLinkedAttributes(bool $includeTags = true): array
	{
		$out = [];
		$table = $this->getTable();

		foreach (self::htmlLinkedColumns($includeTags) as $col) {
			if (!Schema::hasColumn($table, $col)) continue;
			$out[$col] = $this->getAttribute($col);
		}

		return $out;
	}

	/**
	 * Useful when building a “client render payload”:
	 * returns a single merged array with only html-linked keys.
	 */
	protected static function normalizeHtmlLinkedPayload(array $attr, bool $includeTags = true): array
	{
		$keys = array_flip(self::htmlLinkedColumns($includeTags));
		$out = array_intersect_key($attr, $keys);
		foreach ($out as $k => $v) {
			if ($v === null) continue;
			if (is_array($v) && $v === []) $out[$k] = null;
		}

		return $out;
	}


	protected function addHtmlLinkedColumns(Blueprint $table, bool $includeTags = true): void
	{
		$table->json('aria')->nullable();
		$table->json('dataset')->nullable();
		$table->json('selectors')->nullable();
		$table->json('size')->nullable();
		if ($includeTags) $table->json('tags')->nullable();
	}

	protected function dropHtmlLinkedColumns(Blueprint $table, string $tableName, bool $includeTags = true): void
	{
		$cols = ['aria', 'dataset', 'selectors', 'size'];
		if ($includeTags) $cols[] = 'tags';
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

	protected static function bootDescribesHtmlLinkedEntity(): void
	{
		static::saving(function (Model $model): void {
			$table = $model->getTable();

			try {
				if (!Schema::hasTable($table)) return;

				static::normalizeHtmlLinkedMetadata($model);
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed to normalize html-linked attributes before saving', [
					'table' => $table,
					'id'    => $model->getAttribute('id'),
					'error' => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		});
	}

	protected static function hasCol(Model $m, string $col): bool
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

	protected static function normalizeHtmlLinkedMetadata(Model $model): void
	{
		if (self::hasCol($model, 'aria')) {
			$aria = static::normalizeAssocArray($model->getAttribute('aria'));
			$aria = static::sanitizeAriaArray($aria);
			$model->setAttribute('aria', $aria ?: null);
		}

		if (self::hasCol($model, 'dataset')) {
			$dataset = static::normalizeAssocArray($model->getAttribute('dataset'));
			$dataset = static::sanitizeDatasetArray($dataset);
			$model->setAttribute('dataset', $dataset ?: null);
		}

		if (self::hasCol($model, 'selectors')) {
			$selectors = NormalizesArrays::normalizeArrayField($model->getAttribute('selectors'));
			$selectors = static::sanitizeSelectorsArray($selectors);
			$model->setAttribute('selectors', $selectors ?: null);
		}

		if (self::hasCol($model, 'size')) {
			$size = static::normalizeAssocArray($model->getAttribute('size'));
			$size = static::sanitizeSizeArray($size);
			$model->setAttribute('size', $size ?: null);
		}

		if (self::hasCol($model, 'tags')) {
			$tags = NormalizesArrays::normalizeArrayField($model->getAttribute('tags'));
			$tags = static::sanitizeTagsArray($tags);
			$model->setAttribute('tags', $tags ?: null);
		}
	}

	protected static function normalizeAssocArray(mixed $value): array
	{
		$array = NormalizesArrays::normalizeArrayField($value);
		if ($array === []) return [];

		if (array_keys($array) === range(0, count($array) - 1)) {
			$assoc = [];
			foreach ($array as $item) {
				if (!is_array($item) || count($item) !== 2) continue;
				[$k, $v] = array_values($item);
				if (!is_string($k)) continue;
				$assoc[$k] = $v;
			}
			return $assoc ?: [];
		}

		return $array;
	}

	protected static function sanitizeTagsArray(array $tags): array
	{
		$clean = [];

		foreach ($tags as $tag) {
			if (!is_string($tag)) continue;
			$tag = trim($tag);
			if ($tag === '') continue;
			$clean[] = \Illuminate\Support\Str::slug($tag, '_');
		}

		return array_values(array_unique($clean));
	}

	protected static function sanitizeAriaArray(array $aria): array
	{
		$result = [];

		foreach ($aria as $key => $value) {
			if (!is_string($key)) continue;

			$attr = strtolower(trim($key));
			if (!str_starts_with($attr, 'aria-')) $attr = 'aria-' . ltrim($attr, '-');
			if (!in_array($attr, static::ALLOWED_ARIA, true)) continue;

			if ($value === null) continue;
			if (is_bool($value)) $value = $value ? 'true' : 'false';
			else {
				$value = trim((string) $value);
				if ($value === '') continue;
			}

			$result[$attr] = $value;
		}

		return $result;
	}

	protected static function sanitizeDatasetArray(array $dataset): array
	{
		$result = [];

		foreach ($dataset as $key => $value) {
			if (!is_string($key)) continue;

			$attr = strtolower(trim($key));
			if (!str_starts_with($attr, 'data-')) $attr = 'data-' . ltrim($attr, '-');

			if ($value === null) continue;
			if (is_bool($value)) $value = $value ? 'true' : 'false';
			else {
				$value = trim((string) $value);
				if ($value === '') continue;
			}

			$result[$attr] = $value;
		}

		return $result;
	}

	protected static function sanitizeSelectorsArray(array $selectors): array
	{
		$clean = [];

		foreach ($selectors as $selector) {
			if (!is_string($selector)) continue;

			$selector = trim($selector);
			if ($selector === '') continue;
			if (!preg_match('/^[#.][A-Za-z0-9_-]+$/', $selector)) continue;

			$clean[] = $selector;
		}

		return array_values(array_unique($clean));
	}

	protected static function sanitizeSizeArray(?array $size): ?array
	{
		if (!$size) return null;

		$result = [];

		foreach (['width', 'height'] as $key) {
			if (!array_key_exists($key, $size)) continue;

			$value = trim((string) $size[$key]);
			if ($value === '') continue;
			if (!preg_match('/^\d+(\.\d+)?(px|em|rem|%|vw|vh)$/', $value)) continue;

			$result[$key] = $value;
		}

		return $result === [] ? null : $result;
	}
}
