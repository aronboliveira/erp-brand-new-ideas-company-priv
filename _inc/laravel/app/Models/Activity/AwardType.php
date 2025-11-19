<?php

namespace App\Models;

use App\Traits\{HasAuditFields, UsesUuids};
use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class AwardType extends Model
{
    use HasAuditFields, HasFactory, UsesUuids;
    protected $table = DC::TABLE_AWD_TPS;
    protected $fillable = ['name'];
    protected $guarded = ['id', DC::TABLE_CREATOR];
}
