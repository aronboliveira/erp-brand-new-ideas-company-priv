<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\User;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class TerminationType extends Model
{
    use HasAuditFields, UsesUuids;
    private const COL_CREATED_BY = DC::TABLE_CREATOR;
    private const COL_NAME      = 'name';

    protected $table = DC::TABLE_TERMINATION_TYPES;
    protected $fillable = [self::COL_NAME];
    protected $guarded = ['id', self::COL_CREATED_BY];

    public function createdBy(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider using belongsTo(User::class, self::COL_CREATED_BY)
    }
}
