<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class BugFile extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'file', 'name', 'extension', 'file_size',
        'created_by', 'bug_id', 'user_type'
    ]; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS;  // ! CHANGED
}
