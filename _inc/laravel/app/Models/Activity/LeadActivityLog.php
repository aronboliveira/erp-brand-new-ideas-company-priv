<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{
    AppModuleType,
    LogType,
    UserType
};
use App\Traits\{NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};

/**
 * @property string|null $label
 * @property string|null $module
 * @property string|null $remark
 * @property string|null $log_type

 * @property string|null $description
 */
final class LeadActivityLog extends Model
{
    use HasFactory;
    use NormalizesArrays;
    use UsesUuids;

    protected $table = DC::TABLE_LD_ACT_LOGS;

    protected $fillable = [
        UC::COL_USER_ID,      // user_id
        UC::COL_U_TP,         // user_type
        PJC::COL_LD_ID,       // lead_id
        AC::COL_LOG_TP,       // log_type
        'remark',
        AC::COL_MD,           // module
        'label',
        'description',
        DC::COL_RL_CAT,       // related_categories
        PJC::COL_TAGS,        // tags
        DC::COL_ER_LG,        // error_log
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR
    ];

    protected $casts = [
        UC::COL_U_TP   => UserType::class,
        AC::COL_LOG_TP => LogType::class,
        AC::COL_MD     => AppModuleType::class,
        DC::COL_RL_CAT => 'array',
        PJC::COL_TAGS  => 'array',
        DC::COL_ER_LG  => 'array',
    ];

    protected $with = [
        'user',
        'lead'
    ];

    private const ICONS = [
        LogType::Error->value          => 'ti-alert-circle',
        LogType::Warning->value        => 'ti-alert-triangle',
        LogType::Info->value           => 'ti-info-circle',
        LogType::Debug->value          => 'ti-bug',
        LogType::Critical->value       => 'ti-alert-octagon',
        LogType::Alert->value          => 'ti-alarm',
        LogType::Emergency->value      => 'ti-flame',
        LogType::Notice->value         => 'ti-flag',
        LogType::Security->value       => 'ti-shield-lock',
        LogType::Access->value         => 'ti-door-enter',
        LogType::Query->value          => 'ti-database-search',
        LogType::Job->value            => 'ti-player-play',
        LogType::Event->value          => 'ti-calendar-event',
        LogType::Audit->value          => 'ti-clipboard-list',
        LogType::Performance->value    => 'ti-activity',
        LogType::Api->value            => 'ti-api',
        LogType::Database->value       => 'ti-database',
        LogType::Authentication->value => 'ti-lock',
        LogType::Authorization->value  => 'ti-lock-access',
        LogType::Validation->value     => 'ti-checkup-list',
        LogType::Mail->value           => 'ti-mail',
        LogType::Notification->value   => 'ti-bell',
        LogType::Cache->value          => 'ti-cloud',
        LogType::Session->value        => 'ti-id',
        LogType::Queue->value          => 'ti-playlist',
        LogType::Schedule->value       => 'ti-clock',
        LogType::Console->value        => 'ti-terminal',
        LogType::System->value         => 'ti-cpu',
        LogType::Other->value          => 'ti-dots',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (LeadActivityLog $model): void {
            foreach (
                [
                    DC::COL_RL_CAT,
                    PJC::COL_TAGS,
                    DC::COL_ER_LG
                ] as $column
            )
                $model->setAttribute($column, self::normalizeArrayField($model->getAttribute($column) ?? null));
        });
    }

    /**
     * Custom setter to prevent ValueError when setting an invalid log_type.
     * Uses tryFrom() instead of from() for graceful handling of unknown values.
     */
    public function setLogTypeAttribute(mixed $value): void
    {
        if ($value instanceof LogType) {
            $this->attributes[AC::COL_LOG_TP] = $value->value;
        } elseif (is_string($value)) {
            $case = LogType::tryFrom($value);
            $this->attributes[AC::COL_LOG_TP] = $case?->value ?? $value;
        } else {
            $this->attributes[AC::COL_LOG_TP] = $value;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, PJC::COL_LD_ID, 'id');
    }

    public function getLeadRemarkAttribute(): string
    {
        return $this->buildLeadRemark();
    }

    public function getLogIconAttribute(): string
    {
        return $this->logIcon();
    }

    public function logIcon(): string
    {
        $key = $this->resolveLogTypeValue();
        return self::ICONS[$key] ?? self::ICONS[LogType::Other->value];
    }

    protected function buildLeadRemark(): string
    {
        try {
            $userName = $this->user?->{UC::COL_NM} ?? '';
            $logTypeLabel = $this->log_type instanceof LogType
                ? $this->log_type->label()
                : (string) $this->log_type;
            $moduleLabel = $this->module instanceof AppModuleType
                ? $this->module->label()
                : (string) $this->module;
            $text = $this->label
                ?: $this->description
                ?: $this->remark
                ?: '';
            $parts = [];
            if ($userName !== '')
                $parts[] = $userName;
            if ($moduleLabel !== '')
                $parts[] = '[' . __($moduleLabel) . ']';
            if ($logTypeLabel !== '')
                $parts[] = __($logTypeLabel) . ':';
            if ($text !== '')
                $parts[] = $text;
            return trim(implode(' ', $parts));
        } catch (\Throwable) {
            return '';
        }
    }

    protected function resolveLogTypeValue(): string
    {
        try {
            $raw = $this->{AC::COL_LOG_TP} ?? null;
            if ($raw instanceof LogType)
                return $raw->value;
            return is_string($raw) ? strtolower(trim($raw)) : LogType::Other->value;
        } catch (\Throwable) {
            $rawAttr = $this->getAttributes()[AC::COL_LOG_TP] ?? null;
            return is_string($rawAttr) ? strtolower(trim($rawAttr)) : LogType::Other->value;
        }
    }

    public function isErrorLevel(): bool
    {
        return $this->log_type instanceof LogType
            ? $this->log_type->isErrorLevel()
            : false;
    }

    public function isSecurityRelated(): bool
    {
        return $this->log_type instanceof LogType
            ? $this->log_type->isSecurityRelated()
            : false;
    }

    public function isSystemRelated(): bool
    {
        return $this->log_type instanceof LogType
            ? $this->log_type->isSystemRelated()
            : false;
    }
}
