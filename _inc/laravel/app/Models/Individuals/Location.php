<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuids;

class Location extends Model
{
	use UsesUuids;

	protected $fillable = [
		'company_id',
		'is_active',
	];
}
