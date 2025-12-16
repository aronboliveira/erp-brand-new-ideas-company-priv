<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealEmail extends Model
{
    use HasAuditFields, UsesUuids, NormalizesAddresses;

    protected $table = DC::TABLE_DL_EMAILS;

    protected $fillable = [
        AC::COL_DL,              // deal_id
        UC::COL_USER_ID,         // user_id (dono do registro/contato)
        'from',                  // endereço/telefone origem
        'to',                    // endereço/telefone destino
        'subject',
        'description',
        'notes',
        'counter',
        PJC::COL_IS_FUP,         // is_follow_up
        'attachments',
        PJC::COL_ATC_FRULES,     // attachment_filter_rules
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'counter'           => 'integer',
        PJC::COL_IS_FUP     => 'boolean',
        'attachments'       => 'array',
        PJC::COL_ATC_FRULES => 'array',
    ];

    protected $with = [
        'deal',
        'user',
        'fromUser',
        'toUser',
        'createdBy',
        'updatedBy',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (DealEmail $dealEmail): void {
            $dealEmail->setAttribute('from', self::normalizeEmail($dealEmail->getAttribute('from'), 'DealEmail from', $dealEmail->getAttribute('id') ?? null));
            $dealEmail->setAttribute('to', self::normalizeEmail($dealEmail->getAttribute('to'), 'DealEmail to', $dealEmail->getAttribute('id') ?? null));
        });
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, AC::COL_DL, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_FRM_ID, 'id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_TO_ID, 'id');
    }

    public function isFollowUp(): bool
    {
        return (bool) $this->{PJC::COL_IS_FUP};
    }

    public function hasAttachments(): bool
    {
        $attachments = $this->getAttribute('attachments') ?? [];
        return is_array($attachments) && count($attachments) > 0;
    }
}
