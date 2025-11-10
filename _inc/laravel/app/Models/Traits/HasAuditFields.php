<?php

namespace App\Traits;

use App\Models\User;

trait HasAuditFields
{
	protected static function bootHasAuditFields()
	{
		static::creating(function ($model) {
			if (auth()->check()) {
				$model->created_by = $model->created_by ?? auth()->id();
				$model->updated_by = $model->updated_by ?? auth()->id();
			}
		});

		static::updating(function ($model) {
			if (auth()->check()) {
				$model->updated_by = auth()->id();
			}
		});
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function updater()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}
}
