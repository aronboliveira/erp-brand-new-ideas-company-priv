<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class LeadDiscussion extends Model
{
    use HasFactory;
    use UsesUuids;
    protected $fillable = ['lead_id', 'comment', 'created_by'];
    private const FK_LEAD   = 'lead_id';
    private const FK_USER   = 'created_by';
    private const LOCAL_KEY = 'id';
    private const MODEL_USER = User::class;
    public function user(): HasOne
    {
        return $this->hasOne(self::MODEL_USER, self::LOCAL_KEY, self::FK_USER);
    }
}
