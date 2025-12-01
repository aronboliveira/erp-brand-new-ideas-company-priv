<?php

namespace App\Models;

use App\Config\Constants\{
	ActivitiesConstants,
	DatabaseConstants,
	ProjectsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{
	Builder,
	Model,
	Relations\BelongsTo
};

class Schedule extends Model
{
	use UsesUuids;
	protected $primaryKey = 'id';
	protected $fillable = [
		ActivitiesConstants::COL_NT,
		ActivitiesConstants::COL_SCHD_TP,
		ProjectsConstants::COL_S_DT,
		ActivitiesConstants::COL_ST_TIME,
		ActivitiesConstants::COL_MI,
		ActivitiesConstants::COL_MT,
		DatabaseConstants::COL_TABLE_CREATOR,
	];
	protected $casts = [
		ProjectsConstants::COL_S_DT => 'date',
		ActivitiesConstants::COL_ST_TIME => 'time',
	];

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, DatabaseConstants::COL_TABLE_CREATOR);
	}

	protected static function booted(): void
	{
		static::addGlobalScope(DatabaseConstants::ORDER_NEW, function (Builder $builder) {
			$builder->orderBy('id', 'desc');
		});
	}
}
