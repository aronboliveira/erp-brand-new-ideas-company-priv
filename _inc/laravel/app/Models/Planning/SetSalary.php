<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class SetSalary extends Model
{
	use UsesUuids;

	private const FILLABLE_FIELDS = [
		'employee_id', 'salary_type', 'salary', 'created_by'
	];
	protected $fillable = self::FILLABLE_FIELDS;

	public function employee(): BelongsTo
	{
		return $this->belongsTo(Employee::class, 'employee_id', 'id');
		// * consider eager loading via $with
	}

	public function salaryType(): BelongsTo
	{
		return $this->belongsTo(PayslipType::class, 'salary_type', 'id');
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by', 'id');
	}
}
