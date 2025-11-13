<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\HasAuditFields;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugFile extends Model
{
    use UsesUuids, HasAuditFields;
    protected $fillable = [
        'file',
        'name',
        'extension',
        'file_size',
        'bug_id',
        'user_type'
    ];
    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
    ];
    protected $casts = [
        UC::COL_U_TP => UserType::class,
    ];
    protected $with = ['bug'];

    protected static function booted(): void
    {
        is_callable('parent::booted') && parent::booted();
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
}
