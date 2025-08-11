<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UsesUuids
{
	protected static $uuids = [];
	protected static function bootUsesUuids(): void
	{
		static::creating(function ($model) {
			if (empty($model->{$model->getKeyName()})) {
				$cls = get_class($model);
				$newId = '';
				if (!isset(self::$uuids[$cls])) self::$uuids[$cls] = [];
				do $newId = (string) Str::uuid();
				while (in_array($newId, self::$uuids, true));
				self::$uuids[$cls][] = $newId;
				$model->{$model->getKeyName()} = $newId;
			}
		});
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
}
