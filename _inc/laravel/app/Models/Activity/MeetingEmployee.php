<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Traits\{
    HasAuditFields,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Arr;

class MeetingEmployee extends Model
{
    use HasAuditFields;
    use HasFactory;
    use UsesUuids;

    protected $table = DC::TABLE_MET_EMP;

    protected $fillable = [
        CC::COL_INV_CD,
        CC::COL_MT_ID,
        UC::COL_EMP_ID,
        CC::COL_IS_HST,
        CC::COL_HAS_MRC_MT,
        CC::COL_HAS_CMR_OFF,
        CC::COL_HAS_SCR_ENB,
        'metadata',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $with = [
        'meeting',
        'employee',
        'createdBy',
    ];

    protected $casts = [
        CC::COL_IS_HST      => 'boolean',
        CC::COL_HAS_MRC_MT  => 'boolean',
        CC::COL_HAS_CMR_OFF => 'boolean',
        CC::COL_HAS_SCR_ENB => 'boolean',
        'metadata'          => 'array',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(
            Meeting::class,
            CC::COL_MT_ID,
            'id'
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            UC::COL_EMP_ID,
            'id'
        );
    }

    public function isHost(): bool
    {
        return (bool) $this->{CC::COL_IS_HST};
    }

    public function hasMicrophoneMutedByDefault(): bool
    {
        return (bool) $this->{CC::COL_HAS_MRC_MT};
    }

    public function hasCameraOffByDefault(): bool
    {
        return (bool) $this->{CC::COL_HAS_CMR_OFF};
    }

    public function hasScreenSharingEnabledByDefault(): bool
    {
        return (bool) $this->{CC::COL_HAS_SCR_ENB};
    }

    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        $meta = $this->metadata ?? [];
        return Arr::get(is_array($meta) ? $meta : [], $key, $default);
    }

    public function setMetadataValue(string $key, mixed $value): void
    {
        $meta = $this->metadata ?? [];
        if (!is_array($meta))
            $meta = [];
        Arr::set($meta, $key, $value);
        $this->metadata = $meta;
    }

    public static function countForMeeting(string $meetingId): int
    {
        return static::where(CC::COL_MT_ID, $meetingId)->count();
    }

    public static function countHostsForMeeting(string $meetingId): int
    {
        return static::where(CC::COL_MT_ID, $meetingId)
            ->where(CC::COL_IS_HST, true)
            ->count();
    }

    public static function hostsForMeeting(string $meetingId)
    {
        return static::where(CC::COL_MT_ID, $meetingId)
            ->where(CC::COL_IS_HST, true)
            ->get();
    }
}
