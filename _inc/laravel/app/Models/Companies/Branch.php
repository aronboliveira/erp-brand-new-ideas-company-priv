<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC};
use App\Traits\{HasAuditFields, NormalizesAddresses, StoresManyRefJson, UsesCountryRegions, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log, Schema};

/**
 * @property string|null $address
 */
class Branch extends Model
{
    use UsesUuids, HasAuditFields, NormalizesAddresses, UsesCountryRegions, StoresManyRefJson;

    protected $table     = DC::TABLE_BRANCHES;
    protected $fillable  = ['company', 'name', 'description', 'country', 'state', 'city', 'address', 'zip', 'phone', 'departments', CPC::COL_ADM, CPC::COL_MNG, 'budget', 'expenses', 'profit', 'budgets'];
    protected $casts     = [
        'budget'   => 'decimal:2',
        'expenses' => 'decimal:2',
        'profit'   => 'decimal:2',
        'budgets'  => 'array'
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR, CPC::COL_FND];

    public static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            if (Schema::hasColumn($m->getTable(), 'phone') && !empty($m->getAttribute('phone')))
                is_callable([self::class, 'normalizePhone']) && $m->setAttribute('phone', self::normalizePhone($m->getAttribute('phone'), 'Branch Phone', $m->getAttribute('id')));
            if (Schema::hasColumn($m->getTable(), 'email') && !empty($m->getAttribute('email')))
                is_callable([self::class, 'normalizeEmail']) && $m->setAttribute('email', self::normalizeEmail($m->getAttribute('email'), 'Branch Email', $m->getAttribute('id')));
            if (!empty($m->getAttribute('departments')) && is_array($m->getAttribute('departments')) || is_string($m->getAttribute('departments'))) {
                $departments = is_array($m->getAttribute('departments'))
                    ? $m->getAttribute('departments')
                    : explode(',', $m->getAttribute('departments'));
                $departments = array_map('trim', $departments);
                $departments = array_filter($departments, fn($dept) => !empty($dept));
                $m->setAttribute('departments', implode(',', $departments));
            }
            $m->rescueCountryStateFromKnownCityList($m);
            $m->rescueGeoFromAddressTokensIfMissing();
            $m->rescueGeoFromZipIfMissing();
            $m->normalizeGeo();
        });
        static::updating(function (self $m): void {
            $departments = $m->getAttribute('departments');
            if (empty($departments))
                return;
            $departmentsArray = is_array($departments)
                ? $departments
                : array_map('trim', explode(',', $departments));
            $departmentsArray = array_filter($departmentsArray, fn($dept) => !empty($dept));
            if (empty($departmentsArray)) {
                $m->setAttribute('departments', '');
                return;
            }
            if (!DB::table(DC::TABLE_USERS)->where('id', $m->getAttribute('company'))->where('type', 'company')->exists())
                $m->setAttribute('company', null);
            if (!empty($m->getAttribute('company'))) {
                try {
                    $companyUnspecificDepartments = DB::table(DC::TABLE_DEPARTMENTS)
                        ->whereNull(CPC::COL_BRC_ID)
                        ->whereIn('id', $departmentsArray)
                        ->pluck('id')
                        ->map(fn($id) => (string) $id)
                        ->all();
                    $unspecificDepartments = array_intersect(
                        $companyUnspecificDepartments,
                        $departmentsArray
                    );
                    $branchCompany = $m->getAttribute('company');
                    if ($branchCompany === null || (string) $branchCompany === '')
                        $validDepartments = $unspecificDepartments;
                    else {
                        $validSpecificDepartments = DB::table(DC::TABLE_DEPARTMENTS)
                            ->where('company', $branchCompany)
                            ->whereIn('id', $departmentsArray)
                            ->pluck('id')
                            ->map(fn($id) => (string) $id)
                            ->all();
                        $validDepartments = array_merge($unspecificDepartments, $validSpecificDepartments);
                    }

                    $m->setAttribute('departments', implode(',', array_unique($validDepartments)));
                } catch (\Exception $e) {
                    Log::error(
                        static::class . ' failed to process departments on update',
                        [
                            'id' => $m->getAttribute('id'),
                            'departments' => $departments,
                            'error' => $e->getMessage(),
                            				'method' => __METHOD__,
				'line' => $e->getLine(),
                        ]
                    );

                    $m->setAttribute('departments', implode(',', $departmentsArray));
                }
            }
        });
    }

    public function getDepartmentsAttribute($value): array
    {
        return $value ? explode(',', $value) : [];
    }

    public function getDepartmentById(string $deptId): ?string
    {
        $departments = $this->getDepartmentsAttribute($this->attributes['departments'] ?? null);
        return in_array($deptId, $departments, true) ? $deptId : null;
    }

    public function hasDepartments(): bool
    {
        return !empty($this->attributes['departments']);
    }

    public function hasDepartmentById(string $deptId): bool
    {
        $departments = $this->getDepartmentsAttribute($this->attributes['departments'] ?? null);
        return in_array($deptId, $departments, true);
    }

    public function getFirstDepartment(): ?string
    {
        $departments = $this->getDepartmentsAttribute($this->attributes['departments'] ?? null);
        return $departments[0] ?? null;
    }

    public function getLastDepartment(): ?string
    {
        $departments = $this->getDepartmentsAttribute($this->attributes['departments'] ?? null);
        return end($departments) ?: null;
    }

    public function setDepartmentsAttribute(array|string $values): void
    {
        if (is_array($values))
            $this->attributes['departments'] = implode(',', $values);
        else
            $this->attributes['departments'] = $values;
    }
}
