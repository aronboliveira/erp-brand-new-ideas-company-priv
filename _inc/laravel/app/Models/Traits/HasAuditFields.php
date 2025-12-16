<?php

namespace App\Traits;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\User;

trait HasAuditFields
{
	protected static function bootHasAuditFields()
	{
		static::creating(function ($model) {
			if (auth()->check()) {
				$model->setAttribute(DC::COL_TABLE_CREATOR, $model->getAttribute(DC::COL_TABLE_CREATOR) ?? auth()->id());
				$model->setAttribute(DC::COL_TABLE_UPDATER, $model->getAttribute(DC::COL_TABLE_UPDATER) ?? auth()->id());
			}
		});

		static::updating(function ($model) {
			if (auth()->check()) {
				$model->setAttribute(DC::COL_TABLE_UPDATER, auth()->id());
			}
		});
	}

	public function creator()
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR);
	}

	public function updater()
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER);
	}
}
