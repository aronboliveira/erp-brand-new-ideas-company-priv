<?php

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class JobApplicationNote extends Model
{
    use UsesUuids;

    private const COL_APPLICATION_ID = 'application_id';
    private const COL_CREATED_BY    = 'created_by';
    private const COL_NOTE          = 'note';
    private const COL_NOTE_CREATED  = 'note_created';
    private const FILLABLE          = [
        self::COL_APPLICATION_ID,
        self::COL_NOTE_CREATED,
        self::COL_NOTE,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function noteCreated(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_NOTE_CREATED);
        // * consider belongsTo(User::class,self::COL_NOTE_CREATED,'id')
    }
}
