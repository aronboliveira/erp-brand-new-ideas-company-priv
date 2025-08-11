<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class ContractAttachment extends Model
{
    use UsesUuids;

    protected $table = 'contract_attachment';

    private const FILLABLE_FIELDS = [
        'contract_id', 'files', 'user_id', 'created_by' // ! CHANGED
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    // * consider adding: belongsTo(Contract::class,'contract_id','id')
    // * consider adding: belongsTo(User::class,'user_id','id')
}
