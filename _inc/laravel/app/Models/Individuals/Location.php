<?php

namespace App\Models;

use App\Traits\{UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,Model};

class Location extends Model
{
    use HasFactory;
	use UsesUuids;

	protected $fillable = [
		'company_id',
		'is_active',
	];
}
