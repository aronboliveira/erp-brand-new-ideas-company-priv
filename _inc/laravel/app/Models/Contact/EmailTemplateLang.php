<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateLang extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'parent_id', 'lang', 'subject', 'content'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    // * consider adding: belongsTo(EmailTemplate::class,'parent_id','id')
}
