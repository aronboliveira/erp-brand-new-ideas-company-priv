<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class BugComment extends Model
{
    use UsesUuids, HasAuditFields;

    protected $fillable = [
        'bug_id',
        'comment',
        UC::COL_U_TP,
    ];
    protected $guarded  = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];
    protected $with = ['bug'];
    protected $casts = [
        UC::COL_U_TP => UserType::class,
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            try {
                $stringValue = $model->{UC::COL_U_TP} instanceof UserType
                    ? $model->{UC::COL_U_TP}->value
                    : (string) $model->{UC::COL_U_TP};
                $model->{UC::COL_U_TP} = UserType::normalize($stringValue) ?? throw new \InvalidArgumentException(
                    'Invalid user type: ' . $stringValue
                );
            } catch (\InvalidArgumentException $e) {
                $model->{UC::COL_U_TP} = UserType::Client;
            }
        });
        static::updating(function ($model) {
            if ($model->isDirty(UC::COL_U_TP)) {
                $model->{UC::COL_U_TP} = UserType::normalize((string) $model->{UC::COL_U_TP})
                    ?? throw new \InvalidArgumentException('Invalid user_type');
            }
        });
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class, 'bug_id', 'id');
    }

    public function commentUser(): ?User
    {
        return User::where('id', $this->created_by)->first();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }
}
