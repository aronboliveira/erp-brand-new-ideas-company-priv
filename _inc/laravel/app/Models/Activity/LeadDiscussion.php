<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\UserType;
use App\Traits\{
    FiltersSecureAttachments,
    HasAuditFields,
    NormalizesArrays,
    UsesUuids,
};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo
};

class LeadDiscussion extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use NormalizesArrays;
    use FiltersSecureAttachments;

    protected $table = DC::TABLE_LD_DSC;

    private const FK_LEAD = PJC::COL_LD_ID;
    private const FK_USER = UC::COL_USER_ID;

    protected $fillable = [
        self::FK_LEAD,
        UC::COL_USER_ID,
        UC::COL_U_TP,
        'comment',
        AC::COL_CAN_NADM_DL,
        AC::COL_IS_FLAG,
        AC::COL_IS_RPL,
        AC::COL_IS_RPLD,
        'label',
        'attachments',
        'reactions',
        'metadata',
        DC::COL_TABLE_CREATOR,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        AC::COL_CAN_NADM_DL => 'bool',
        AC::COL_IS_FLAG     => 'bool',
        AC::COL_IS_RPL      => 'bool',
        AC::COL_IS_RPLD     => 'bool',
        'attachments'       => 'array',
        'reactions'         => 'array',
        'metadata'          => 'array',
    ];

    protected $with = [
        'lead',
        'user',
        'createdBy',
        'updatedBy',
    ];

    protected static function booted(): void
    {
        static::saving(function (LeadDiscussion $discussion) {
            if ($discussion->getAttribute(AC::COL_CAN_NADM_DL) === null)
                $discussion->setAttribute(AC::COL_CAN_NADM_DL, false);
            if ($discussion->getAttribute(AC::COL_IS_FLAG) === null)
                $discussion->setAttribute(AC::COL_IS_FLAG, false);
            if ($discussion->getAttribute(AC::COL_IS_RPL) === null)
                $discussion->setAttribute(AC::COL_IS_RPL, false);
            if ($discussion->getAttribute(AC::COL_IS_RPLD) === null)
                $discussion->setAttribute(AC::COL_IS_RPLD, false);
            $type = UserType::normalize($discussion->getAttribute(UC::COL_U_TP) ?? null);
            $discussion->setAttribute(UC::COL_U_TP, ($type?->value) ?? UserType::Client->value);
            foreach (['attachments', 'reactions', 'metadata'] as $field) {
                if (is_array($discussion->getAttribute($field)))
                    continue;
                $discussion->setAttribute($field, self::normalizeArrayField($discussion->getAttribute($field) ?? null));
            }
            if (!is_array($discussion->getAttribute('attachments')) && $discussion->getAttribute('attachments') !== null)
                $discussion->setAttribute('attachments', (array) $discussion->getAttribute('attachments'));
            if (!is_array($discussion->getAttribute('reactions')) && $discussion->getAttribute('reactions') !== null)
                $discussion->setAttribute('reactions', (array) $discussion->getAttribute('reactions'));
            if (!is_array($discussion->getAttribute('metadata')) && $discussion->getAttribute('metadata') !== null)
                $discussion->setAttribute('metadata', (array) $discussion->getAttribute('metadata'));
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, self::FK_LEAD);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::FK_USER);
    }

    public function scopeFlagged($query)
    {
        return $query->where(AC::COL_IS_FLAG, true);
    }

    public function scopeForLead($query, string $leadId)
    {
        return $query->where(self::FK_LEAD, $leadId);
    }

    public function scopeReplies($query)
    {
        return $query->where(AC::COL_IS_RPL, true);
    }

    public function canBeDeletedByNonAdmin(): bool
    {
        return (bool) $this->{AC::COL_CAN_NADM_DL};
    }

    public function isFlagged(): bool
    {
        return (bool) $this->{AC::COL_IS_FLAG};
    }

    public function isReply(): bool
    {
        return (bool) $this->{AC::COL_IS_RPL};
    }

    public function isReplied(): bool
    {
        return (bool) $this->{AC::COL_IS_RPLD};
    }

    public function userType(): ?UserType
    {
        return UserType::normalize($this->{UC::COL_U_TP} ?? null);
    }
}
