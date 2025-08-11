<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class LeadFile extends Model
{
    use HasFactory, UsesUuids;
    protected $fillable = ['lead_id', 'file_name', 'file_path'];
    private const FK_LEAD_ID = 'lead_id';
    private const LOCAL_KEY  = 'id';
    private const MODEL_LEAD = Lead::class;
    public function lead(): BelongsTo // * ADDED
    {
        return $this->belongsTo(self::MODEL_LEAD, self::FK_LEAD_ID, self::LOCAL_KEY);
    }
}
