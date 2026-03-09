<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Traits\{FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Casts\Attribute, Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\{Carbon, Facades\Log, Str};

/**
 * @property int|string $id
 * @property string|null $name
 * @property string|null $date
 * @property string|null $time
 * @property string|null $description
 * @property int|string|null $deal_id
 * @property int|string|null $status
 * @property int|string|null $priority
 * @property int|string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $priority_level
 * @property string|null $status_enum
 */
class DealTask extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, NormalizesArrays, FiltersSecureAttachments;

    protected $table = DC::TABLE_DL_TSK;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        AC::COL_DL,
        PJC::COL_NM,
        'description',
        AC::COL_TSK_DATE,
        AC::COL_TSK_TIME,
        PJC::COL_PRT,
        AC::COL_TSK_STT,
        AC::COL_TSK_ID,
        'attachments',
        'tags',
    ];

    protected $casts = [
        AC::COL_TSK_DATE => 'date',
        PJC::COL_PRT     => 'integer',
        AC::COL_TSK_STT  => 'integer',
        'attachments'    => 'array',
        'tags'           => 'array',
    ];

    protected $with = [
        'deal',
        'task',
        'createdBy',
    ];

    protected $appends = [
        'priority_level',
        'priority_label',
        'status_enum',
        'status_label',
        'task_title',
    ];

    private const JSON_FIELDS = ['attachments', 'tags'];

    // * legacy, should be represented by PriorityLevel enum
    public static $priorities = [
        0 => 'None',
        1 => 'Low',
        2 => 'Medium',
        3 => 'High',
        4 => 'Critical',
        5 => 'Urgent',
        6 => 'Blocker',
        7 => 'Immediate',
    ];

    // * legacy, should be represented by EvaluationStatus enum
    public static $status = [
        0 => 'In Progress',
        1 => 'Completed',
        2 => 'Active',
        3 => 'Suspended',
        4 => 'Pending',
        5 => 'Draft',
        6 => 'Cancelled',
        7 => 'Expired',
        8 => 'Archived',
        9 => 'Undefined',
        10 => 'Accept',
        11 => 'Decline',
        12 => 'Not Started',
    ];

    /**
     * Mapeamento legível e estável de códigos (tinyint) -> Enum.
     * Prioridade segue o weight() do PriorityLevel.
     */
    private const PRIORITY_CODE_MAP = [
        0 => PriorityLevel::None,
        1 => PriorityLevel::Low,
        2 => PriorityLevel::Medium,
        3 => PriorityLevel::High,
        4 => PriorityLevel::Urgent,
        5 => PriorityLevel::Critical,
        6 => PriorityLevel::Blocker,
        7 => PriorityLevel::Immediate,
    ];

    /**
     * Mantém compatibilidade com legado (0=InProgress, 1=Completed),
     * mas abre espaço para mais estados sem depender da ordem original do enum.
     */
    private const STATUS_CODE_MAP = [
        0  => EvaluationStatus::InProgress,
        1  => EvaluationStatus::Completed,
        2  => EvaluationStatus::Pending,
        3  => EvaluationStatus::Active,
        4  => EvaluationStatus::Suspended,
        5  => EvaluationStatus::Draft,
        6  => EvaluationStatus::Cancelled,
        7  => EvaluationStatus::Expired,
        8  => EvaluationStatus::Archived,
        9  => EvaluationStatus::NotStarted,
        10 => EvaluationStatus::Accept,
        11 => EvaluationStatus::Decline,
        12 => EvaluationStatus::Undefined,
    ];

    private static array $taskMirrorCache = []; // task_id => ['date'=>Carbon|null, 'time'=>string|null, 'title'=>string|null]

    protected static function booted(): void
    {
        is_callable('parent::booted') && parent::booted();
        static::creating(function (self $m): void {
            $name = trim((string)($m->getAttribute(PJC::COL_NM) ?? ''));
            if ($name === '') $m->setAttribute(PJC::COL_NM, self::generateName());

            // Campos NOT NULL na migration: garante mínimos, caso inserção venha incompleta
            $date = $m->getAttribute(AC::COL_TSK_DATE);
            if ($date === null) $m->setAttribute(AC::COL_TSK_DATE, now()->toDateString());

            $time = trim((string)($m->getAttribute(AC::COL_TSK_TIME) ?? ''));
            if ($time === '') $m->setAttribute(AC::COL_TSK_TIME, now()->format('H:i:s'));
        });
        static::saving(function (self $m): void {
            $m->ensureJsonAttributesAreEncoded(self::JSON_FIELDS);

            // Normaliza listas JSON (tags/attachments) como lista de strings únicas (quando fizer sentido)
            try {
                $tags = $m->normalizeStringList($m->getAttribute('tags'));
                if ($tags !== null) $m->setAttribute('tags', $tags);

                $attachments = $m->normalizeStringList($m->getAttribute('attachments'));
                if ($attachments !== null) $m->setAttribute('attachments', $attachments);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize json string lists', [
                    'id'  => $m->getAttribute('id'),
                    'err' => $e->getMessage(),
                ]);
            }

            // Clamp prioridade/status para os limites dos mapas (e tinyint)
            $m->setAttribute(PJC::COL_PRT, self::clampCode($m->getAttribute(PJC::COL_PRT), array_key_last(self::PRIORITY_CODE_MAP)));
            $m->setAttribute(AC::COL_TSK_STT, self::clampCode($m->getAttribute(AC::COL_TSK_STT), array_key_last(self::STATUS_CODE_MAP)));

            // Espelha date/time do Task quando task_id está definido e o Task possui valores válidos
            $taskId = (string)($m->getAttribute(AC::COL_TSK_ID) ?? '');
            if ($taskId !== '') self::mirrorDateTimeFromTask($m, $taskId);

            // Segurança: garante NOT NULL em date/time após espelhamento
            $date = $m->getAttribute(AC::COL_TSK_DATE);
            if ($date === null) $m->setAttribute(AC::COL_TSK_DATE, now()->toDateString());

            $time = trim((string)($m->getAttribute(AC::COL_TSK_TIME) ?? ''));
            if ($time === '') $m->setAttribute(AC::COL_TSK_TIME, now()->format('H:i:s'));
        });
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, AC::COL_DL, 'id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, AC::COL_TSK_ID, 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    protected function priorityLevel(): Attribute
    {
        return Attribute::make(
            get: function (): PriorityLevel {
                $code = (int)($this->getAttribute(PJC::COL_PRT) ?? 1);
                return self::PRIORITY_CODE_MAP[$code] ?? PriorityLevel::Medium;
            }
        );
    }

    protected function priorityLabel(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $lvl = $this->priority_level; // Attribute acima
                $val = $lvl instanceof PriorityLevel ? $lvl->value : (string)$lvl;
                return PriorityLevel::labels(DC::DEFAULT_LANG)[$val] ?? $val;
            }
        );
    }

    protected function statusEnum(): Attribute
    {
        return Attribute::make(
            get: function (): EvaluationStatus {
                $code = (int)($this->getAttribute(AC::COL_TSK_STT) ?? 0);
                return self::STATUS_CODE_MAP[$code] ?? EvaluationStatus::Undefined;
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $st = $this->status_enum;
                $val = $st instanceof EvaluationStatus ? $st->value : (string)$st;
                return EvaluationStatus::labels(DC::DEFAULT_LANG)[$val] ?? $val;
            }
        );
    }

    protected function taskTitle(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->relationLoaded('task') && $this->task) {
                    $t = $this->task->getAttribute('title');
                    return is_string($t) ? $t : (string)($t ?? '');
                }

                $taskId = (string)($this->getAttribute(AC::COL_TSK_ID) ?? '');
                if ($taskId === '') return '';

                $cached = self::$taskMirrorCache[$taskId]['title'] ?? null;
                return is_string($cached) ? $cached : '';
            }
        );
    }

    public function setPriority(PriorityLevel|string|int|null $value): self
    {
        if ($value instanceof PriorityLevel) {
            $this->setAttribute(PJC::COL_PRT, $value->weight());
            return $this;
        }

        if (is_int($value) || (is_string($value) && is_numeric($value))) {
            $this->setAttribute(PJC::COL_PRT, self::clampCode($value, array_key_last(self::PRIORITY_CODE_MAP)));
            return $this;
        }

        $enum = PriorityLevel::normalize(is_string($value) ? $value : null);
        $this->setAttribute(PJC::COL_PRT, $enum->weight());
        return $this;
    }

    public function setStatus(EvaluationStatus|string|int|null $value): self
    {
        if ($value instanceof EvaluationStatus) {
            $this->setAttribute(AC::COL_TSK_STT, self::statusCodeFromEnum($value));
            return $this;
        }

        if (is_int($value) || (is_string($value) && is_numeric($value))) {
            $this->setAttribute(AC::COL_TSK_STT, self::clampCode($value, array_key_last(self::STATUS_CODE_MAP)));
            return $this;
        }

        $enum = EvaluationStatus::normalize(is_string($value) ? $value : null);
        $this->setAttribute(AC::COL_TSK_STT, self::statusCodeFromEnum($enum));
        return $this;
    }

    public function scopeForDeal($query, string $dealId)
    {
        return $query->where(AC::COL_DL, $dealId);
    }

    public function scopeLinkedToTask($query, string $taskId)
    {
        return $query->where(AC::COL_TSK_ID, $taskId);
    }

    private static function generateName(): string
    {
        return 'DL-TSK-' . (string)Str::uuid();
    }

    private static function clampCode(mixed $value, int $max): int
    {
        $n = is_numeric($value) ? (int)$value : 0;
        if ($n < 0) $n = 0;
        if ($n > $max) $n = $max;
        if ($n > 255) $n = 255;
        return $n;
    }

    private static function statusCodeFromEnum(EvaluationStatus $status): int
    {
        foreach (self::STATUS_CODE_MAP as $code => $enum)
            if ($enum === $status) return (int)$code;

        return 12; // Undefined
    }

    private static function mirrorDateTimeFromTask(self $m, string $taskId): void
    {
        try {
            if (!array_key_exists($taskId, self::$taskMirrorCache)) {
                $row = Task::query()
                    ->select(['title', AC::COL_TSK_DATE, AC::COL_TSK_TIME])
                    ->whereKey($taskId)
                    ->first();

                $date = null;
                $time = null;
                $title = null;

                if ($row) {
                    $title = (string)($row->getAttribute('title') ?? null);

                    $rawDate = $row->getAttribute(AC::COL_TSK_DATE);
                    if ($rawDate !== null) {
                        $date = $rawDate instanceof Carbon
                            ? $rawDate->copy()->startOfDay()
                            : Carbon::parse((string)$rawDate)->startOfDay();
                    }

                    $rawTime = $row->getAttribute(AC::COL_TSK_TIME);
                    $time = is_string($rawTime) ? trim($rawTime) : (string)($rawTime ?? '');
                    if ($time === '') $time = null;
                }

                self::$taskMirrorCache[$taskId] = [
                    'date'  => $date,
                    'time'  => $time,
                    'title' => $title,
                ];
            }

            $cached = self::$taskMirrorCache[$taskId];

            if (($cached['date'] ?? null) instanceof Carbon)
                $m->setAttribute(AC::COL_TSK_DATE, $cached['date']->toDateString());

            if (is_string($cached['time'] ?? null) && $cached['time'] !== '')
                $m->setAttribute(AC::COL_TSK_TIME, $cached['time']);
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to mirror date/time from task', [
                'id'      => $m->getAttribute('id'),
                'task_id' => $taskId,
                'err'     => $e->getMessage(),
            ]);
        }
    }
}
