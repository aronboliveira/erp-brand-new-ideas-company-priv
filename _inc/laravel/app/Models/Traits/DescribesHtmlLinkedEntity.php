<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Schema\{Blueprint};
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

		protected function getHtmlLinkedAttributes(bool $includeTags = true): array
	{
		    try {
    		$out = [];
    		$table = $this->getTable();

    		foreach (self::htmlLinkedColumns($includeTags) as $col) {
    			if (!Schema::hasColumn($table, $col)) continue;
    			$out[$col] = $this->getAttribute($col);
    		}

    		return $out;
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::getHtmlLinkedAttributes — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return [];
		    }
	}

		protected static function normalizeHtmlLinkedPayload(array $attr, bool $includeTags = true): array
	{
		    try {
    		$keys = array_flip(self::htmlLinkedColumns($includeTags));
    		$out = array_intersect_key($attr, $keys);
    		foreach ($out as $k => $v) {
    			if ($v === null) continue;
    			if (is_array($v) && $v === []) $out[$k] = null;
    		}

    		return $out;
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::normalizeHtmlLinkedPayload — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return [];
		    }
	}

	protected function addHtmlLinkedColumns(Blueprint $table, bool $includeTags = true): void
	{
	    try {
    		$table->json('aria')->nullable();
    		$table->json('dataset')->nullable();
    		$table->json('selectors')->nullable();
    		$table->json('size')->nullable();
    		if ($includeTags) $table->json('tags')->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addHtmlLinkedColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function dropHtmlLinkedColumns(Blueprint $table, string $tableName, bool $includeTags = true): void
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropHtmlLinkedColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected static function bootDescribesHtmlLinkedEntity(): void
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::bootDescribesHtmlLinkedEntity — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
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
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeHtmlLinkedMetadata — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected static function normalizeAssocArray(mixed $value): array
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeAssocArray — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	protected static function sanitizeTagsArray(array $tags): array
	{
	    try {
    		$clean = [];

    		foreach ($tags as $tag) {
    			if (!is_string($tag)) continue;
    			$tag = trim($tag);
    			if ($tag === '') continue;
    			$clean[] = \Illuminate\Support\Str::slug($tag, '_');
    		}

    		return array_values(array_unique($clean));
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::sanitizeTagsArray — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	protected static function sanitizeAriaArray(array $aria): array
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::sanitizeAriaArray — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	protected static function sanitizeDatasetArray(array $dataset): array
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::sanitizeDatasetArray — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	protected static function sanitizeSelectorsArray(array $selectors): array
	{
	    try {
    		$clean = [];

    		foreach ($selectors as $selector) {
    			if (!is_string($selector)) continue;

    			$selector = trim($selector);
    			if ($selector === '') continue;
    			if (!preg_match('/^[#.][A-Za-z0-9_-]+$/', $selector)) continue;

    			$clean[] = $selector;
    		}

    		return array_values(array_unique($clean));
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::sanitizeSelectorsArray — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}

	protected static function sanitizeSizeArray(?array $size): ?array
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::sanitizeSizeArray — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}
}
