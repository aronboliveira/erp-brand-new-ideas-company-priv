<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\PriorityLevel;
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log};

class UserToDo extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    protected $table = DC::TABLE_USR_TD;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        AC::COL_TT,              // title
        'description',
        UC::COL_USER_ID,         // user_id

        PJC::COL_ASG_BY,         // assigned_by
        PJC::COL_ASG_AT,         // assigned_at

        'notification',
        'milestone',
        'project',
        'task',

        'priority',
        'progress',
        'order',
        PJC::COL_E_HRS,          // estimated_hrs
        PJC::COL_D_DATE,         // due_date

        PJC::COL_IS_CP,          // is_complete
        PJC::COL_CMP_AT,         // completed_at
        PJC::COL_IS_FV,          // is_favorite

        'tags',
        'attachments',

        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        AC::COL_TT        => 'string',
        'description'     => 'string',

        UC::COL_USER_ID   => 'string',
        PJC::COL_ASG_BY   => 'string',
        PJC::COL_ASG_AT   => 'datetime',

        'notification'    => 'string',
        'milestone'       => 'string',
        'project'         => 'string',
        'task'            => 'string',

        'priority'        => 'string',
        'progress'        => 'decimal:2',
        'order'           => 'integer',
        PJC::COL_E_HRS    => 'string',
        PJC::COL_D_DATE   => 'date',

        PJC::COL_IS_CP    => 'boolean',
        PJC::COL_CMP_AT   => 'datetime',
        PJC::COL_IS_FV    => 'boolean',

        'tags'            => 'array',
        'attachments'     => 'array',
    ];

    protected $with = [
        'notification',
        'milestone',
        'project',
        'task',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureDefaults();
                $m->normalizeCore();
                $m->normalizeJson();
                $m->enforceCoherence();
                $m->validateForeignKeysSoft();
                $m->validateAssignedBySoft();
            } catch (\Throwable $ex) {
                Log::error(static::class . ' saving() failed', [
                    'id'    => (string) ($m->getAttribute('id') ?? ''),
                    'error' => $ex->getMessage(),
                ]);
                throw $ex;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function assignedBy(): ?BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_ASG_BY, 'id');
    }

    public function milestone(): ?BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'milestone', 'id');
    }

    public function project(): ?BelongsTo
    {
        return $this->belongsTo(Project::class, 'project', 'id');
    }

    public function task(): ?BelongsTo
    {
        return $this->belongsTo(Task::class, 'task', 'id');
    }

    public function notification(): ?BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification', 'id');
    }

    private function ensureDefaults(): void
    {
        $title = trim((string) ($this->getAttribute(AC::COL_TT) ?? ''));
        if ($title === '') $this->setAttribute(AC::COL_TT, 'To-do');

        if ($this->getAttribute('priority') === null || trim((string) $this->getAttribute('priority')) === '') {
            $this->setAttribute('priority', PriorityLevel::Medium->value);
        }

        if ($this->getAttribute('progress') === null) $this->setAttribute('progress', 0.00);
        if ($this->getAttribute('order') === null) $this->setAttribute('order', 0);

        if ($this->getAttribute(PJC::COL_IS_CP) === null) $this->setAttribute(PJC::COL_IS_CP, false);
        if ($this->getAttribute(PJC::COL_IS_FV) === null) $this->setAttribute(PJC::COL_IS_FV, false);
    }

    private function normalizeCore(): void
    {
        foreach ([UC::COL_USER_ID, PJC::COL_ASG_BY, 'milestone', 'project', 'task'] as $k)
            $this->setAttribute($k, $this->normalizeUuidNullable($this->getAttribute($k)));
        foreach ([AC::COL_TT, 'description'] as $k) {
            $v = $this->getAttribute($k);
            if (is_string($v)) $this->setAttribute($k, trim($v));
        }
        $p = $this->getAttribute('progress');
        $pv = is_numeric($p) ? (float) $p : 0.0;
        $pv = max(0.0, min(100.0, $pv));
        $this->setAttribute('progress', round($pv, 2));
        $o = $this->getAttribute('order');
        $ov = is_numeric($o) ? (int) $o : 0;
        $ov = max(0, min(65535, $ov));
        $this->setAttribute('order', $ov);
        $rawPr = trim((string) ($this->getAttribute('priority') ?? ''));
        $pr = PriorityLevel::tryFrom($rawPr) ?? PriorityLevel::Medium;
        $this->setAttribute('priority', $pr->value);
        $eh = $this->getAttribute(PJC::COL_E_HRS);
        if (is_string($eh)) {
            $eh = trim($eh);
            $this->setAttribute(PJC::COL_E_HRS, $eh === '' ? null : $this->normalizeTimeString($eh));
        }
    }

    private function normalizeJson(): void
    {
        $this->setAttribute('tags', self::normalizeArrayField($this->getAttribute('tags')));
        $this->setAttribute('attachments', self::normalizeArrayField($this->getAttribute('attachments')));
        $this->setAttribute('tags', array_slice($this->getAttribute('tags') ?? [], 0, 256));
        $this->setAttribute('attachments', array_slice($this->getAttribute('attachments') ?? [], 0, 256));
    }

    private function enforceCoherence(): void
    {
        $isComplete = (bool) ($this->getAttribute(PJC::COL_IS_CP) ?? false);
        $progress   = (float) ($this->getAttribute('progress') ?? 0);
        if ($isComplete && $progress < 100)
            $this->setAttribute('progress', 100.00);
        if (!$isComplete && $progress >= 100) {
            $this->setAttribute(PJC::COL_IS_CP, true);
            $isComplete = true;
        }
        if (!$isComplete) {
            $this->setAttribute(PJC::COL_CMP_AT, null);
            return;
        }
        $cmp = $this->getAttribute(PJC::COL_CMP_AT);
        if (!$cmp)
            $this->setAttribute(PJC::COL_CMP_AT, now('America/Sao_Paulo'));
    }

    /**
     * Evita FK inválida (quando alguém envia uuid inexistente): nullifica em vez de falhar.
     * (Regra pragmática para compatibilidade e seeders.)
     */
    private function validateForeignKeysSoft(): void
    {
        $map = [
            'notification' => DC::TABLE_NTF,
            'milestone' => DC::TABLE_MSS,
            'project'   => DC::TABLE_PROJECTS,
            'task'      => DC::TABLE_TASKS,
            UC::COL_USER_ID => DC::TABLE_USERS,
            PJC::COL_ASG_BY => DC::TABLE_USERS,
        ];

        foreach ($map as $col => $table) {
            $id = $this->getAttribute($col);
            if (!is_string($id) || $id === '') continue;

            try {
                $exists = DB::table($table)->where('id', $id)->exists();
                if (!$exists) $this->setAttribute($col, null);
            } catch (\Throwable $ex) {
                Log::debug(static::class . ' fk soft-check failed', [
                    'col' => $col,
                    'table' => $table,
                    'error' => $ex->getMessage(),
                ]);
            }
        }
    }

    /**
     * Regra descrita na migration:
     * assigned_by pode ser null; se não for, deve ser um user permitido (admin/super admin/company)
     * ou um user com employee vinculado que seja manager=true.
     *
     * Aqui é "soft": se não atender, nullifica.
     */
    private function validateAssignedBySoft(): void
    {
        $assignedBy = $this->getAttribute(PJC::COL_ASG_BY);
        if (!is_string($assignedBy) || $assignedBy === '') return;

        try {
            $u = DB::table(DC::TABLE_USERS)
                ->select(['id', 'type', UC::COL_EMP_ID])
                ->where('id', $assignedBy)
                ->first();

            if (!$u) {
                $this->setAttribute(PJC::COL_ASG_BY, null);
                return;
            }

            $type = strtolower(trim((string) ($u->type ?? '')));
            if (in_array($type, ['admin', 'super admin', 'company'], true)) return;

            $empId = is_string($u->{UC::COL_EMP_ID} ?? null) ? trim((string) $u->{UC::COL_EMP_ID}) : '';
            if ($empId === '') {
                $this->setAttribute(PJC::COL_ASG_BY, null);
                return;
            }

            $isManager = (bool) DB::table(DC::TABLE_EMPLOYEES)
                ->where('id', $empId)
                ->where('manager', true)
                ->exists();

            if (!$isManager) $this->setAttribute(PJC::COL_ASG_BY, null);
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' assigned_by soft-validate failed', [
                'assigned_by' => (string) $assignedBy,
                'error' => $ex->getMessage(),
            ]);
            $this->setAttribute(PJC::COL_ASG_BY, null);
        }
    }

    private function normalizeUuidNullable(mixed $value): ?string
    {
        if (!is_string($value)) return null;
        $v = trim($value);
        if ($v === '') return null;
        if (!preg_match('/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/', $v))
            return null;
        return $v;
    }

    private function normalizeTimeString(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') return null;

        try {
            if (preg_match('/^\d{1,2}:\d{2}$/', $v))
                return Carbon::createFromFormat('H:i', $v)->format('H:i:s');
            if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $v))
                return Carbon::createFromFormat('H:i:s', $v)->format('H:i:s');
        } catch (\Throwable $ex) {
            Log::debug(static::class . ' normalizeTimeString failed', [
                'value' => $value,
                'error' => $ex->getMessage(),
            ]);
        }

        return null;
    }
}
