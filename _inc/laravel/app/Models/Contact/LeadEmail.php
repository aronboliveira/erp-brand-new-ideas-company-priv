<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadEmail extends Model
{
    use HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_LD_EMAILS;

    private const FILLABLE_FIELDS = [
        PJC::COL_LD_ID,           // lead_id
        'from',
        'to',
        'subject',
        'counter',
        PJC::COL_IS_FUP,          // is_follow_up
        'description',
        'attachments',
        PJC::COL_ATC_FRULES,      // attachment_filter_rules
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    protected $casts = [
        'counter'             => 'integer',
        PJC::COL_IS_FUP       => 'boolean',
        'attachments'         => 'array',
        PJC::COL_ATC_FRULES   => 'array',
    ];

    protected $with = [
        'lead',
        'createdBy',
        'updatedBy',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, PJC::COL_LD_ID, 'id');
    }

    public function isFollowUp(): bool
    {
        return (bool) $this->{PJC::COL_IS_FUP};
    }

    public function getSender(): ?User
    {
        return User::where('email', $this->from)->first();
    }

    public function getRecipient(): ?User
    {
        return User::where('email', $this->to)->first();
    }
}
