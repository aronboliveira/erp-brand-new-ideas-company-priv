<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC};
use App\Models\{User};
use Illuminate\Support\Facades\{Log};

trait HasAuditFields
{
	protected static function bootHasAuditFields()
	{
		try {
			static::creating(function ($model) {
				if (auth()->check()) {
					$model->setAttribute(DC::COL_TABLE_CREATOR, $model->getAttribute(DC::COL_TABLE_CREATOR) ?? auth()->id() ?? DC::DEFAULT_UUID);
					$model->setAttribute(DC::COL_TABLE_UPDATER, $model->getAttribute(DC::COL_TABLE_UPDATER) ?? auth()->id() ?? DC::DEFAULT_UUID);
				}
			});
			static::updating(function ($model) {
				if (auth()->check())
					$model->setAttribute(DC::COL_TABLE_UPDATER, auth()->id());
			});
		} catch (\Throwable $e) {
			Log::error(static::class . '::bootHasAuditFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return null;
		}
	}

	public function creator()
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR);
	}

	public function createdBy()
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR);
	}

	public function updater()
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER);
	}

	public function updatedBy()
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER);
	}
}
