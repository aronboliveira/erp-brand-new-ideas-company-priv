<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Illuminate\Support\Collection;

class Lead extends Model
{
    use HasFactory;
    use UsesUuids;
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'user_id', 'pipeline_id',
        'stage_id', 'sources', 'products', 'notes', 'labels', 'order',
        'created_by', 'is_active', 'is_converted', 'date'
    ];
    public function labels(): Collection
    {
        return $this->labels
            ? Label::whereIn('id', explode(',', $this->labels))->get()
            : collect();
    }
    public function stage(): HasOne
    {
        return $this->hasOne(LeadStage::class, 'id', 'stage_id');
    }
    public function files(): HasMany
    {
        return $this->hasMany(LeadFile::class, 'lead_id', 'id');
    }
    public function pipeline(): HasOne
    {
        return $this->hasOne(Pipeline::class, 'id', 'pipeline_id');
    }
    public function products(): Collection
    {
        return $this->products
            ? ProductService::whereIn('id', explode(',', $this->products))
            ->get()
            : collect();
    }
    public function sources(): Collection
    {
        return $this->sources
            ? Source::whereIn('id', explode(',', $this->sources))
            ->get()
            : collect();
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_leads',
            'lead_id',
            'user_id'
        );
    }
    public function activities(): HasMany
    {
        return $this->hasMany(
            LeadActivityLog::class,
            'lead_id',
            'id'
        )->orderByDesc('id');
    }
    public function discussions(): HasMany
    {
        return $this->hasMany(
            LeadDiscussion::class,
            'lead_id',
            'id'
        )->orderByDesc('id');
    }
    public function calls(): HasMany
    {
        return $this->hasMany(LeadCall::class, 'lead_id', 'id');
    }
    public function emails(): HasMany
    {
        return $this->hasMany(
            LeadEmail::class,
            'lead_id',
            'id'
        )->orderByDesc('id');
    }
}
