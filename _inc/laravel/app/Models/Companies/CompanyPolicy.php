<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class CompanyPolicy extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'branch', 'title', 'description', 'file', 'created_by'
    ];                                                               // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS;                       // ! CHANGED

    public function branches()
    {
        return $this->hasOne(Branch::class, 'id', 'branch');       // * consider using belongsTo(Branch::class,'branch')
    }
}
