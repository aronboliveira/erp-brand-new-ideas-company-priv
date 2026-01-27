<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\ActivityType;
use App\Traits\{HasAuditFields, LogsIcons, NormalizesArrays, TracksFailures, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    use LogsIcons, UsesUuids, HasAuditFields, NormalizesArrays, TracksFailures;

    protected $table = DC::TABLE_ACT_LOG;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        UC::COL_USER_ID,
        PJC::COL_PJ_ID,
        PJC::COL_CTC_ID,
        PJC::COL_LD_ID,
        AC::COL_TSK_ID,
        AC::COL_DL,

        'document',
        AC::COL_TSK_FL,
        AC::COL_LD_FL,
        AC::COL_DL_FL,

        AC::COL_LOG_TP,
        'remark',
        'timestamp',
        'metadata',

        ...self::FAILURE_TRACKING_COLS,
    ];

    protected $casts = [
        AC::COL_LOG_TP     => 'string',
        'timestamp'        => 'datetime',
        'metadata'         => 'array',
        DC::COL_FL_AT      => 'datetime',
        DC::COL_LST_RTR_AT => 'datetime',
        DC::COL_ER_LG      => 'array',
        DC::COL_RTR_CT     => 'integer',
    ];

    protected $with = ['user'];

    protected $appends = [
        'rendered_remark',
        'activity_type',
    ];

    private static array $userData = [];

    // * legacy, should be replaced by the ActivityType enum
    private const ICONS = [
        'Invite User'                  => 'ti-user',
        'User Assigned to the Task'    => 'ti-user-check',
        'User Removed from the Task'   => 'ti-user-x',
        'Upload File'                  => 'ti-cloud-upload',
        'Create Milestone'             => 'ti-crop',
        'Create Bug'                   => 'ti-bug',
        'Create Task'                  => 'ti-square-plus',
        'Move Task'                    => 'ti-command',
        'Create Expense'               => 'ti-clipboard-list',
        'Move'                         => 'ti-arrows-maximize',
        'Add Product'                  => 'ti-shopping-cart-plus',
        'Update Sources'               => 'ti-brand-open-source',
        'Create Deal Call'             => 'ti-phone-plus',
        'Create Deal Email'            => 'ti-record-mail',
        'Create Invoice'               => 'ti-file-plus',
        'Add Contact'                  => 'ti-notebook',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $model->enforceDefaults();
                $model->ensureJsonAttributesAreEncoded([
                    'metadata',
                    DC::COL_ER_LG,
                ]);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' saving normalization failed: ' . $e->getMessage(), [
                    'id' => $model->getKey(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
        });
    }

    protected function enforceDefaults(): void
    {
        $rawType = $this->getAttribute(AC::COL_LOG_TP);

        $enum = ActivityType::normalize($rawType)
            ?? ActivityType::normalize(Str::snake((string) ($rawType ?? '')))
            ?? ActivityType::Other;

        $this->setAttribute(AC::COL_LOG_TP, $enum->value);

        if ($this->getAttribute('timestamp') === null)
            $this->setAttribute('timestamp', now());

        $retryCount = $this->getAttribute(DC::COL_RTR_CT);
        if (!is_int($retryCount))
            $this->setAttribute(DC::COL_RTR_CT, (int) ($retryCount ?? 0));
    }

    public function setMetadataAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute('metadata', $value);
    }

    public function setErrorLogAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute(DC::COL_ER_LG, $value);
    }

    public function getRenderedRemarkAttribute(): string
    {
        return $this->fetchGetRemark();
    }

    public function getActivityTypeAttribute(): string
    {
        try {
            return $this->activityTypeEnum()->value;
        } catch (\Throwable) {
            return ActivityType::Other->value;
        }
    }

    public function activityTypeEnum(): ActivityType
    {
        $raw = $this->getAttribute(AC::COL_LOG_TP);
        return ActivityType::normalize($raw)
            ?? ActivityType::normalize(Str::snake((string) ($raw ?? '')))
            ?? ActivityType::Other;
    }

    protected function metadataArray(): array
    {
        return self::normalizeArrayField($this->getAttribute('metadata'));
    }

    protected function legacyRemarkPayload(): array
    {
        $remark = $this->getAttribute('remark');

        if (!is_string($remark) || trim($remark) === '')
            return [];

        $trimmed = trim($remark);
        if (!self::looksLikeJson($trimmed))
            return [];

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            Log::debug(self::class . ' failed decoding legacy remark JSON: ' . $e->getMessage(), [
                'id' => $this->getKey(),
            ]);
            return [];
        }
    }

    public function getRemark(): string
    {
        $key = (string) ($this->getKey() ?? spl_object_id($this));

        if (!array_key_exists($key, self::$userData))
            self::$userData[$key] = $this->fetchGetRemark();

        return (string) self::$userData[$key];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', UC::COL_USER_ID);
    }

    public function userDetail(): HasOne
    {
        $id = UC::COL_USER_ID;
        $cls = get_class($this);

        return $this->hasOne(
            substr($cls, 0, strrpos($cls, '\\')) . '\\' . ucfirst(__FUNCTION__),
            $id,
            $id
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID, 'id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, PJC::COL_LD_ID, 'id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, AC::COL_TSK_ID, 'id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, AC::COL_DL, 'id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document', 'id');
    }

    public function fetchGetRemark(): string
    {
        $data = $this->metadataArray();
        if (!$data)
            $data = $this->legacyRemarkPayload();

        $name = (string) ($this->user?->name ?? '');
        $rawType = (string) ($this->getAttribute(AC::COL_LOG_TP) ?? '');

        $enum = ActivityType::normalize($rawType)
            ?? ActivityType::normalize(Str::snake($rawType))
            ?? null;

        if (!$enum) {
            $legacyMap = [
                'Invite User' => ActivityType::InviteUser,
                'User Assigned to the Task' => ActivityType::UserAssignedToTask,
                'User Assigned To Task' => ActivityType::UserAssignedToTask,
                'User Removed from the Task' => ActivityType::UserRemovedFromTask,
                'User Removed From Task' => ActivityType::UserRemovedFromTask,
                'Upload File' => ActivityType::UploadFile,
                'Create Bug' => ActivityType::CreateBug,
                'Create Milestone' => ActivityType::CreateMilestone,
                'Create Task' => ActivityType::CreateTask,
                'Move Task' => ActivityType::MoveTask,
                'Create Expense' => ActivityType::CreateExpense,
                'Add Product' => ActivityType::AddProduct,
                'Update Sources' => ActivityType::UpdateSources,
                'Create Deal Call' => ActivityType::CreateDealCall,
                'Create Deal Email' => ActivityType::CreateDealEmail,
                'Move' => ActivityType::Move,
            ];

            $enum = $legacyMap[$rawType] ?? null;
        }

        try {
            $actor = trim($name) !== '' ? e($name) : e((string) __('Someone'));

            $action = $enum?->getAction() ?? '';
            $entity = $enum?->getEntity() ?? '';

            $actionVariant = match ($action) {
                'move' => 'primary',
                'add', 'create' => 'success',
                'upload' => 'info',
                'delete' => 'danger',
                'update' => 'warning',
                default => 'secondary',
            };

            $actionLabel = match ($action) {
                'move' => (string) __('moved'),
                'add' => (string) __('added'),
                'upload' => (string) __('uploaded'),
                'create' => (string) __('created'),
                'delete' => (string) __('deleted'),
                'update' => (string) __('updated'),
                default => (string) __('activity'),
            };

            $entityLabel = $entity !== ''
                ? Str::headline(str_replace('_', ' ', $entity))
                : ($enum ? Str::headline($enum->value) : (trim($rawType) !== '' ? Str::headline($rawType) : (string) __('Activity')));

            $iconCls = trim((string) $this->logIcon());
            $iconHtml = $iconCls !== ''
                ? '<i class="' . e($iconCls) . '"></i>'
                : '<span class="fw-semibold small">•</span>';

            $title = (string) ($data['title'] ?? $data['name'] ?? $data['task_name'] ?? $data['file_name'] ?? $data['subject'] ?? '');
            $memberName = (string) ($data['member_name'] ?? $data['assignee_name'] ?? $data['user_name'] ?? '');
            $fileName = (string) ($data['file_name'] ?? $data['filename'] ?? '');
            $oldStage = (string) ($data['old_stage'] ?? $data['from_stage'] ?? '');
            $newStage = (string) ($data['new_stage'] ?? $data['to_stage'] ?? '');
            $oldStatus = (string) ($data['old_status'] ?? $data['from_status'] ?? '');
            $newStatus = (string) ($data['new_status'] ?? $data['to_status'] ?? '');

            $fromValue = $oldStage !== '' ? $oldStage : $oldStatus;
            $toValue = $newStage !== '' ? $newStage : $newStatus;

            $badge = static fn(string $text, string $variant, string $extra = ''): string
            => '<span class="badge text-bg-' . e($variant) . ' ' . $extra . '">' . e($text) . '</span>';

            $pill = static fn(string $label, string $value, string $variant = 'secondary'): string
            => '<span class="badge rounded-pill text-bg-' . e($variant) . '">'
                . e($label) . ': <span class="fw-semibold">' . e($value) . '</span></span>';

            $uuidChip = static function (string $label, mixed $value, string $variant = 'secondary'): ?string {
                if (!is_scalar($value)) return null;
                $v = trim((string) $value);
                if ($v === '') return null;
                $short = Str::length($v) > 10 ? (Str::substr($v, 0, 8) . '…') : $v;
                return '<span class="badge rounded-pill text-bg-' . e($variant) . '" title="' . e($v) . '">'
                    . e($label) . ': <span class="font-monospace">' . e($short) . '</span></span>';
            };

            $ts = $this->getAttribute('timestamp');
            $timeHtml = '';
            if ($ts instanceof \Carbon\CarbonInterface) {
                $timeHtml = '<span class="text-muted small" title="' . e($ts->toDateTimeString()) . '">'
                    . e($ts->diffForHumans())
                    . '</span>';
            }

            $chips = [];

            $chip = $uuidChip('PJ', $this->getAttribute(PJC::COL_PJ_ID), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('CTC', $this->getAttribute(PJC::COL_CTC_ID), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('LD', $this->getAttribute(PJC::COL_LD_ID), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('TSK', $this->getAttribute(AC::COL_TSK_ID), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('DL', $this->getAttribute(AC::COL_DL), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('DOC', $this->getAttribute('document'), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('TSK-FL', $this->getAttribute(AC::COL_TSK_FL), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('LD-FL', $this->getAttribute(AC::COL_LD_FL), 'secondary');
            if ($chip) $chips[] = $chip;

            $chip = $uuidChip('DL-FL', $this->getAttribute(AC::COL_DL_FL), 'secondary');
            if ($chip) $chips[] = $chip;

            $chipsHtml = $chips
                ? '<div class="d-flex flex-wrap gap-1 mt-2">' . implode('', $chips) . '</div>'
                : '';

            $detailBadges = [];

            if (trim($title) !== '')
                $detailBadges[] = $pill((string) __('Title'), $title, 'light');

            if (trim($fileName) !== '')
                $detailBadges[] = $pill((string) __('File'), $fileName, 'light');

            if (trim($memberName) !== '')
                $detailBadges[] = $pill((string) __('Member'), $memberName, 'light');

            if (trim($fromValue) !== '')
                $detailBadges[] = $pill((string) __('From'), (string) __(ucwords($fromValue)), 'light');

            if (trim($toValue) !== '')
                $detailBadges[] = $pill((string) __('To'), (string) __(ucwords($toValue)), 'light');

            $detailsHtml = $detailBadges
                ? '<div class="d-flex flex-wrap gap-1 mt-2">' . implode('', $detailBadges) . '</div>'
                : '';

            $sentence = match ($enum) {
                ActivityType::InviteUser =>
                '<span class="text-muted">' . e((string) __('has invited')) . '</span> '
                    . '<span class="fw-semibold">' . e((string) ($data['title'] ?? $data['name'] ?? '')) . '</span>',

                ActivityType::UserAssignedToTask =>
                '<span class="text-muted">' . e((string) __('has assigned task')) . '</span> '
                    . '<span class="fw-semibold">' . e((string) ($data['task_name'] ?? $data['title'] ?? '')) . '</span> '
                    . '<span class="text-muted">' . e((string) __('to')) . '</span> '
                    . '<span class="fw-semibold">' . e((string) ($data['member_name'] ?? $data['assignee_name'] ?? '')) . '</span>',

                ActivityType::UserRemovedFromTask =>
                '<span class="text-muted">' . e((string) __('has removed')) . '</span> '
                    . '<span class="fw-semibold">' . e((string) ($data['member_name'] ?? $data['assignee_name'] ?? '')) . '</span> '
                    . '<span class="text-muted">' . e((string) __('from task')) . '</span> '
                    . '<span class="fw-semibold">' . e((string) ($data['task_name'] ?? $data['title'] ?? '')) . '</span>',

                ActivityType::MoveTask, ActivityType::MoveLeadStage =>
                '<span class="text-muted">' . e((string) __('moved')) . '</span> '
                    . '<span class="fw-semibold">' . e($title !== '' ? $title : $entityLabel) . '</span>'
                    . (trim($fromValue) !== '' ? ' <span class="text-muted">' . e((string) __('from')) . '</span> <span class="fw-semibold">' . e((string) __(ucwords($fromValue))) . '</span>' : '')
                    . (trim($toValue) !== '' ? ' <span class="text-muted">' . e((string) __('to')) . '</span> <span class="fw-semibold">' . e((string) __(ucwords($toValue))) . '</span>' : ''),

                ActivityType::Move =>
                '<span class="text-muted">' . e((string) __('moved')) . '</span> '
                    . '<span class="fw-semibold">' . e($title !== '' ? $title : $entityLabel) . '</span>'
                    . (trim($fromValue) !== '' ? ' <span class="text-muted">' . e((string) __('from')) . '</span> <span class="fw-semibold">' . e((string) __(ucwords($fromValue))) . '</span>' : '')
                    . (trim($toValue) !== '' ? ' <span class="text-muted">' . e((string) __('to')) . '</span> <span class="fw-semibold">' . e((string) __(ucwords($toValue))) . '</span>' : ''),

                default =>
                '<span class="text-muted">' . e($actionLabel) . '</span> '
                    . '<span class="fw-semibold">' . e($entityLabel) . '</span>'
                    . ($title !== '' ? ' <span class="text-muted">—</span> <span class="fw-semibold">' . e($title) . '</span>' : ''),
            };

            $border = ($enum && method_exists($enum, 'isDestructive') && $enum->isDestructive())
                ? 'border-danger-subtle'
                : 'border-secondary-subtle';

            $headerBadges =
                '<div class="d-flex flex-wrap align-items-center gap-2">'
                . $badge(Str::upper($action !== '' ? $action : 'log'), $actionVariant, 'text-uppercase fw-semibold')
                . '<span class="text-muted small">' . e($entityLabel) . '</span>'
                . '</div>';

            return
                '<div class="d-flex align-items-start gap-2 p-2 p-sm-3 border ' . e($border) . ' rounded-3 bg-body">'
                . '<div class="flex-shrink-0">'
                . '<span class="d-inline-flex align-items-center justify-content-center rounded-circle border bg-body-tertiary text-body-secondary" style="width:28px;height:28px;">'
                . $iconHtml
                . '</span>'
                . '</div>'
                . '<div class="flex-grow-1">'
                . '<div class="d-flex justify-content-between align-items-start gap-2">'
                . '<div>'
                . $headerBadges
                . '<div class="mt-1">'
                . '<span class="fw-semibold">' . $actor . '</span> '
                . $sentence
                . '</div>'
                . '</div>'
                . ($timeHtml !== '' ? '<div class="text-end">' . $timeHtml . '</div>' : '')
                . '</div>'
                . $detailsHtml
                . $chipsHtml
                . '</div>'
                . '</div>';
        } catch (\Throwable $e) {
            Log::debug(self::class . ' fetchGetRemark failed: ' . $e->getMessage(), [
                'id' => $this->getKey(),
                'log_type' => $rawType,
            ]);

            return (string) ($this->getAttribute('remark') ?? '');
        }
    }

    protected function legacyIconKeyFromType(string $rawType): string
    {
        $key = Str::headline($rawType);

        $special = [
            'User Assigned To Task' => 'User Assigned to the Task',
            'User Removed From Task' => 'User Removed from the Task',
        ];

        return $special[$key] ?? $key;
    }

    public function logIcon(): string
    {
        $rawType = (string) ($this->getAttribute(AC::COL_LOG_TP) ?? '');
        if ($rawType === '')
            return '';

        $legacyKey = $this->legacyIconKeyFromType($rawType);
        if (array_key_exists($legacyKey, self::ICONS))
            return (string) self::ICONS[$legacyKey];

        $enum = ActivityType::normalize($rawType)
            ?? ActivityType::normalize(Str::snake($rawType))
            ?? null;

        if (!$enum)
            return '';

        $legacyKey = $this->legacyIconKeyFromType($enum->value);
        if (array_key_exists($legacyKey, self::ICONS))
            return (string) self::ICONS[$legacyKey];

        return match ($enum->getAction()) {
            'move' => 'ti-arrows-maximize',
            'add' => 'ti-plus',
            'upload' => 'ti-cloud-upload',
            'create' => 'ti-square-plus',
            'delete' => 'ti-trash',
            'update' => 'ti-edit',
            default => '',
        };
    }

    public function markFailed(?string $reason = null, mixed $errorLog = null): void
    {
        $this->setAttribute(DC::COL_FL_AT, now());
        $this->setAttribute(DC::COL_FLD_RS, $reason !== null ? trim((string) $reason) : null);

        if ($errorLog !== null)
            $this->setAttribute(DC::COL_ER_LG, self::normalizeArrayField($errorLog));
    }

    public function markRetried(mixed $errorLog = null): void
    {
        $curr = (int) ($this->getAttribute(DC::COL_RTR_CT) ?? 0);
        $this->setAttribute(DC::COL_RTR_CT, $curr + 1);
        $this->setAttribute(DC::COL_LST_RTR_AT, now());

        if ($errorLog !== null)
            $this->setAttribute(DC::COL_ER_LG, self::normalizeArrayField($errorLog));
    }

    public function clearFailure(): void
    {
        $this->setAttribute(DC::COL_FL_AT, null);
        $this->setAttribute(DC::COL_FLD_RS, null);
        $this->setAttribute(DC::COL_ER_LG, null);
    }

    public function scopeForProject(Builder $q, string $projectId): Builder
    {
        return $q->where(PJC::COL_PJ_ID, $projectId);
    }

    public function scopeForUser(Builder $q, string $userId): Builder
    {
        return $q->where(UC::COL_USER_ID, $userId);
    }

    public function scopeOfType(Builder $q, ActivityType|string|null $type): Builder
    {
        $raw = $type instanceof ActivityType ? $type->value : (string) ($type ?? '');
        $enum = ActivityType::normalize($raw) ?? ActivityType::normalize(Str::snake($raw)) ?? ActivityType::Other;

        return $q->where(AC::COL_LOG_TP, $enum->value);
    }
}
