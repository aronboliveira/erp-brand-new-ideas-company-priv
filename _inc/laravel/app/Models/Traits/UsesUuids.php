<?php

namespace App\Traits;

use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{Log};

trait UsesUuids
{
	protected static $uuids = [];
	protected static function bootUsesUuids(): void
	{
	    try {
    		static::creating(function ($model) {
    			if (empty($model->{$model->getKeyName()})) {
    				$cls = get_class($model);
    				$newId = '';
    				if (!isset(self::$uuids[$cls])) self::$uuids[$cls] = [];
    				do $newId = (string) Str::uuid();
    				while (in_array($newId, self::$uuids[$cls] ?? [], true));
    				self::$uuids[$cls][] = $newId;
    				$model->{$model->getKeyName()} = $newId;
    			}
    		});
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::bootUsesUuids — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	public function initializeUsesUuids(): void
	{
		$this->incrementing = false;
		$this->keyType = 'string';
	}

	public function getIncrementing(): bool
	{
		return false;
	}

	public function getKeyType(): string
	{
		return 'string';
	}

	public static function queryByKey(string $uuid): ?self
	{
		return static::where('query_key', $uuid)->firstOrFail();
	}
}
