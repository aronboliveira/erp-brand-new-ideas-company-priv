<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Services\ProjectRequestService;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class ProjectStage extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_PROJ_STAGES;

    protected $fillable = [
        'name',
        'description',
        'color',
        'order',
        'notes',
        'involved',
        'metadata',
        'positioning',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'order'       => 'integer',
        'notes'       => 'array',
        'involved'    => 'array',
        'metadata'    => 'array',
        'positioning' => 'array',
    ];

    protected $appends = [
        'involved_count',
        'notes_count',
        'ui_color',
    ];

    protected $with = [
        'createdBy',
    ];

    public const DEFAULT_COLOR = '#11ff3388';

    /** @var array<string,int> */
    private static array $tasksCountCache = [];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureJsonAttributesAreEncoded(['notes', 'involved', 'metadata', 'positioning']);

                $name = trim((string) $m->getAttribute('name'));
                if ($name === '')
                    $m->setAttribute('name', 'Stage ' . Str::upper(Str::substr((string) $m->getAttribute('id'), 0, 6)));

                $color = trim((string) $m->getAttribute('color'));
                if ($color === '')
                    $color = self::DEFAULT_COLOR;

                if ($color !== '' && $color[0] !== '#')
                    $color = '#' . $color;

                if (strlen($color) > 15)
                    $color = substr($color, 0, 15);

                $m->setAttribute('color', $color);

                $order = $m->getAttribute('order');
                $order = is_numeric($order) ? (int) $order : 0;
                if ($order < 0) $order = 0;
                $m->setAttribute('order', $order);

                $inv = $m->normalizeStringList($m->getAttribute('involved'));
                $m->setAttribute('involved', $inv);

                $nts = self::normalizeArrayField($m->getAttribute('notes'));
                $m->setAttribute('notes', $nts ?: null);
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving normalization failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'id' => $m->getAttribute('id'),
                ]);
            }
        });
    }

    public function createdBy(): ?BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function updatedBy(): ?BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
    }

    public function getInvolvedCountAttribute(): int
    {
        $inv = self::normalizeArrayField($this->getAttribute('involved'));
        return count($inv);
    }

    public function getNotesCountAttribute(): int
    {
        $notes = self::normalizeArrayField($this->getAttribute('notes'));
        return count($notes);
    }

    public function getUiColorAttribute(): string
    {
        $c = trim((string) $this->getAttribute('color'));
        return $c !== '' ? $c : self::DEFAULT_COLOR;
    }

    public function involvedList(): array
    {
        return self::normalizeArrayField($this->getAttribute('involved'));
    }

    public function notesList(): array
    {
        return self::normalizeArrayField($this->getAttribute('notes'));
    }

    public function metadataArray(): array
    {
        return self::normalizeArrayField($this->getAttribute('metadata'));
    }

    public function positioningArray(): array
    {
        return self::normalizeArrayField($this->getAttribute('positioning'));
    }

    public function addInvolved(string $idOrName): void
    {
        $s = trim($idOrName);
        if ($s === '') return;

        $list = $this->normalizeStringList($this->getAttribute('involved')) ?? [];
        if (!in_array($s, $list, true)) $list[] = $s;

        $this->setAttribute('involved', array_values($list));
    }

    public function tasksForProject(string|int $projectId): Collection
    {
        return app(ProjectRequestService::class)
            ->stageTasksForProject($this, $projectId);
    }

    public function tasksCountForProject(string|int $projectId): int
    {
        $stageId = (string) ($this->getAttribute('id') ?? '');
        if ($stageId === '') return 0;
        $svc = app(ProjectRequestService::class);
        $key = $svc->getStageTasksKey((string) $stageId, (string) $projectId);
        $count = max(0, (int) $svc->stageTasksCountForProject($stageId, (string) $projectId));
        if (!(isset(self::$tasksCountCache) && is_array(self::$tasksCountCache))) return $count;
        if (isset(self::$tasksCountCache[$key])) return self::$tasksCountCache[$key];
        return self::$tasksCountCache[$key] = max(0, (int) $svc->stageTasksCountForProject($stageId, (string) $projectId));
    }

    public static function getChartData(): array|RedirectResponse
    {
        return app(ProjectRequestService::class)->projectStageChartData();
    }
}
