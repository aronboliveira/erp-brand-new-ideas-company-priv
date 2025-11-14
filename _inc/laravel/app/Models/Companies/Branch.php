<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use UsesUuids, HasAuditFields;

    protected $table     = DC::TABLE_BRANCHES;
    protected $fillable  = [CPC::COL_BRC_NM, 'description', 'address', 'phone', 'departments', CPC::COL_ADM, CPC::COL_MNG];
    protected $casts     = [
        'budget'   => 'decimal:2',
        'expenses' => 'decimal:2',
        'profit'   => 'decimal:2',
    ];
    protected $guarded = ['id', DC::TABLE_CREATOR, CPC::COL_FND];

    public function getDepartmentsAttribute($value): array
    {
        return $value ? explode(',', $value) : [];
    }

    public function getDepartmentById(string $deptId): ?string
    {
        $departments = $this->getDepartments($this->attributes['departments']);
        return in_array($deptId, $departments, true) ? $deptId : null;
    }

    public function hasDepartments(): bool
    {
        return !empty($this->attributes['departments']);
    }

    public function hasDepartmentById(string $deptId): bool
    {
        $departments = $this->getDepartments($this->attributes['departments']);
        return in_array($deptId, $departments, true);
    }

    public function getFirstDepartment(): ?string
    {
        $departments = $this->getDepartments($this->attributes['departments']);
        return $departments[0] ?? null;
    }

    public function getLastDepartment(): ?string
    {
        $departments = $this->getDepartments($this->attributes['departments']);
        return end($departments) ?: null;
    }

    public function setDepartmentsAttribute(array $values): void
    {
        $this->attributes['departments'] = implode(',', $values);
    }
}
