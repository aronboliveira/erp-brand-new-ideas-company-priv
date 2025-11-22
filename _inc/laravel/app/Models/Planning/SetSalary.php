<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{Frequency, SalaryType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class SetSalary extends Model
{
	use HasAuditFields, UsesUuids;
	protected $table = DC::TABLE_SSLR;
	protected $fillable = [
		UC::COL_EMP_ID,
		UC::COL_SLR_TP,
		'salary',
		'frequency',
		BC::COL_MDAY_LMT,
	];
	protected $guarded = ['id', DC::TABLE_CREATOR];
	protected $with = [
		'employee',
	];
	protected $casts = [
		'salary'    => 'decimal:2',
		'frequency' => 'string',
	];

	protected static function booted(): void
	{
		parent::booted();
		static::saving(function (SetSalary $setSalary): void {
			$setSalary->{BC::COL_MDAY_LMT} ??= 5;
			if ($setSalary->{BC::COL_MDAY_LMT} < 1 || $setSalary->{BC::COL_MDAY_LMT} > 31)
				$setSalary->{BC::COL_MDAY_LMT} = 5;
			SalaryType::normalize($setSalary->{UC::COL_SLR_TP});
			$setSalary->{'frequency'} ??= 'monthly';
			Frequency::normalize($setSalary->{'frequency'});
		});
	}
	public function employee(): BelongsTo
	{
		return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
	}

	public function salaryType(): BelongsTo
	{
		return $this->belongsTo(PayslipType::class, UC::COL_SLR_TP, 'id');
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, DC::TABLE_CREATOR, 'id');
	}
}
