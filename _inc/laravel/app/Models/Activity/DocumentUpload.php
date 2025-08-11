<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class DocumentUpload extends Model
{
    use UsesUuids;
    protected $fillable = ['name', 'role', 'document', 'description', 'created_by'];
}
