<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Casts\Attribute, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\Facades\Log;
/**
 * @property int|null $branch_id
 * @property mixed $created_by
 * @property int|null $department_id
 * @property int|null $employee_id
 * @property \Illuminate\Support\Carbon|string|null $transfer_date
 * @property string|null $description
 */

class Transfer extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, DefinesDates;

    protected $table = DC::TABLE_TRFS;

    protected $guarded = ['id'];

    protected $fillable = [
        UC::COL_EMP_ID,
        UC::COL_BRC_ID, // destination branch_id
        UC::COL_DEP_ID, // destination department_id
        UC::COL_TRF_DT,
        'description',
        'notes',
        DC::COL_TABLE_UPDATER, // opcional
    ];

    protected $casts = [
        UC::COL_TRF_DT => 'date',
    ];

    protected $with = [
        'employee',
        'branch',
        'department',
    ];

    protected $appends = [
        'changes_branch',
        'changes_department',
        'changes_anything',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->normalizeDestinationFields();
                $m->ensureDestinationsDifferFromEmployeeOrFail();
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving failed', [
                    'transfer_id' => $m->getKey(),
                    'employee_id' => $m->getAttribute(UC::COL_EMP_ID),
                    'error'       => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Defensive normalization: trims UUID-ish fields; empty => null.
     * (Keeps setAttribute/getAttribute to preserve casts/boot logic.)
     */
    protected function normalizeDestinationFields(): void
    {
        foreach ([UC::COL_BRC_ID, UC::COL_DEP_ID, UC::COL_EMP_ID] as $key) {
            $raw = $this->getAttribute($key);

            if ($raw === null)
                continue;

            if (!is_string($raw)) {
                Log::warning(self::class . " non-string {$key}", [
                    'transfer_id' => $this->getKey(),
                    'value_type'  => gettype($raw),
                ]);
                $this->setAttribute($key, null);
                continue;
            }

            $trimmed = trim($raw);
            $this->setAttribute($key, $trimmed === '' ? null : $trimmed);
        }
    }

    /**
     * HARD RULE:
     * - destination branch_id MUST NOT equal employee.branch_id
     * - destination department_id MUST NOT equal employee.department_id
     * - additionally, at least one destination (branch or department) must be provided
     */
    protected function ensureDestinationsDifferFromEmployeeOrFail(): void
    {
        $empId = $this->getAttribute(UC::COL_EMP_ID);
        if (!is_string($empId) || trim($empId) === '')
            throw new \InvalidArgumentException('Transfer requires a valid employee_id.');

        $employee = Employee::query()
            ->select(['id', UC::COL_BRC_ID, UC::COL_DEP_ID])
            ->where('id', $empId)
            ->first();

        if (!$employee)
            throw new \RuntimeException('Transfer employee not found.');

        $destBranch = $this->getAttribute(UC::COL_BRC_ID);
        $destDept   = $this->getAttribute(UC::COL_DEP_ID);

        // ao menos um destino precisa ser informado
        if ($destBranch === null && $destDept === null)
            throw new \InvalidArgumentException('Transfer must change branch and/or department (destination cannot be fully null).');

        $srcBranch = $employee->getAttribute(UC::COL_BRC_ID);
        $srcDept   = $employee->getAttribute(UC::COL_DEP_ID);

        $branchProvided = is_string($destBranch) && trim($destBranch) !== '';
        $deptProvided   = is_string($destDept) && trim($destDept) !== '';

        $branchSame = $branchProvided && is_string($srcBranch) && $srcBranch !== '' && $destBranch === $srcBranch;
        $deptSame   = $deptProvided && is_string($srcDept) && $srcDept !== '' && $destDept === $srcDept;

        // se só um foi informado, ele PRECISA diferir do atual
        if ($branchProvided && !$deptProvided && $branchSame)
            throw new \InvalidArgumentException('Destination branch_id must be different from employee branch_id when department_id is not provided.');

        if ($deptProvided && !$branchProvided && $deptSame)
            throw new \InvalidArgumentException('Destination department_id must be different from employee department_id when branch_id is not provided.');

        // se ambos foram informados, não pode ser "sem mudança" (ambos iguais)
        if ($branchProvided && $deptProvided && $branchSame && $deptSame)
            throw new \InvalidArgumentException('Transfer must change at least branch_id or department_id (both destinations match current employee values).');
    }

    public function changesBranch(): Attribute
    {
        return Attribute::make(get: function (): bool {
            $dest = $this->getAttribute(UC::COL_BRC_ID);
            if (!is_string($dest) || trim($dest) === '')
                return false;

            $emp = $this->relationLoaded('employee') ? $this->getRelation('employee') : null;
            if (!$emp instanceof Employee)
                return true; // sem employee carregado, assume "intenção de mudança"

            $src = $emp->getAttribute(UC::COL_BRC_ID);
            return is_string($src) && $src !== '' ? $dest !== $src : true;
        });
    }

    public function changesDepartment(): Attribute
    {
        return Attribute::make(get: function (): bool {
            $dest = $this->getAttribute(UC::COL_DEP_ID);
            if (!is_string($dest) || trim($dest) === '')
                return false;

            $emp = $this->relationLoaded('employee') ? $this->getRelation('employee') : null;
            if (!$emp instanceof Employee)
                return true;

            $src = $emp->getAttribute(UC::COL_DEP_ID);
            return is_string($src) && $src !== '' ? $dest !== $src : true;
        });
    }

    public function changesAnything(): Attribute
    {
        return Attribute::make(get: fn(): bool => (bool) $this->getAttribute('changes_branch') || (bool) $this->getAttribute('changes_department'));
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, UC::COL_BRC_ID, 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, UC::COL_DEP_ID, 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
    }
}
