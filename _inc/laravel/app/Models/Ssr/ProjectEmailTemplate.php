<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class ProjectEmailTemplate extends Model
{
    use UsesUuids;

    protected $fillable = [
        // 'id', // * no longer needed: UUID is auto-generated
        'template_id',
        'project_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id', 'id');
        // * kept original relationship logic; no change in querying type
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
        // * kept original relationship logic; no change in querying type
    }
}
