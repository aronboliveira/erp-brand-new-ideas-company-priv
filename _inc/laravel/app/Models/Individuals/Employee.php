<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\Gender;
use App\Models\{
    Allowance,
    Commission,
    EmployeeDocument,
    Loan,
    OtherPayment,
    Overtime,
    PayslipType,
    SaturationDeduction
};
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Collection,
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne
};
use Illuminate\Support\{Str, Facades\Hash};
use Illuminate\Support\Facades\Log;

/**
 * @property string|null $account_number
 * @property int|string|null $branch_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property float|int|string|null $salary
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property int|null $employee_id
 * @property string|null $name
 * @property string|null $email
 */
class Employee extends Model
{
    use HasAuditFields, HasFactory, NormalizesAddresses, UsesUuids;

    protected $table = DC::TABLE_EMPLOYEES;
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $with = ['branch', 'department', 'designation'];
    protected $hidden = ['password'];
    protected $casts = [
        'dob' => 'date',
        'salary' => 'decimal:2',
        'gender' => Gender::class,
        'documents' => 'array',
        'password' => 'hashed',
        'manager' => 'boolean',
    ];
    protected $fillable = [
        UC::COL_USER_ID,
        'name',
        'manager',
        'phone',
        'email',
        'gender',
        'notes',
        'password',
        'address',
        'dob',
        CPC::COL_BRC_ID,
        CPC::COL_BRC_LC,
        CPC::COL_DEP_ID,
        UC::COL_DSG_ID,
        CPC::COL_DOJ,
        'documents',
        UC::COL_ACC_HD,
        UC::COL_ACC_NM,
        UC::COL_BANK_NM,
        UC::COL_BANK_IC,
        UC::COL_TAX_ID,
        'salary',
        UC::COL_SLR_TP,
        UC::COL_IA,
    ];

    protected static function booted(): void
    {
        parent::booted();
        $normalizeAccNum = static function (?string $v): ?string {
            if ($v === null) return null;
            $v = preg_replace('/\s+/u', '', $v);
            return $v !== '' ? $v : null;
        };
        $ensureUnique = static function (self $m, string $col): void {
            $val = $m->{$col} ?? null;
            if ($val === null) return;
            $q = self::query()->where($col, $val);
            if ($m->exists) $q->where('id', '!=', $m->getKey());
            if ($q->exists())
                throw new \DomainException("Valor já utilizado para {$col}");
        };

        static::creating(function (self $m) use ($normalizeAccNum, $ensureUnique) {
            self::ensureValidAge($m);
            if (empty($m->getAttribute('manager')))
                $m->setAttribute('manager', false);
            if (empty($m->getAttribute(UC::COL_EMP_ID))) {
                do $publicId = (string) Str::uuid();
                while (self::where(UC::COL_EMP_ID, $publicId)->exists());
                $m->setAttribute(UC::COL_EMP_ID, $publicId);
            }
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            $isNormalizePhoneCallable && $m->setAttribute('phone', self::normalizePhone($m->getAttribute('phone'), 'Employee phone', $m->getAttribute('id')));
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            $isNormalizeEmailCallable && $m->setAttribute('email', self::normalizeEmail($m->getAttribute('email') ?? null));
            $m->setAttribute(UC::COL_ACC_NM, $normalizeAccNum($m->getAttribute(UC::COL_ACC_NM) ?? null));
            $ensureUnique($m, 'phone');
            $ensureUnique($m, 'email');
            $ensureUnique($m, UC::COL_ACC_NM);
            if (empty($m->{CPC::COL_BRC_LC}) && $m->{CPC::COL_BRC_ID}) {
                $addr = $m->branch()->value('address');
                if ($addr) $m->setAttribute(CPC::COL_BRC_LC, $addr);
            }
            if ($m->isDirty('gender')) {
                $rawGender = $m->getAttribute('gender');
                $normalized = Gender::normalize($rawGender);
                $m->setAttribute('gender', $normalized?->value ?? Gender::Other->value);
            }
            if (!empty($m->getAttribute('password')) && !str_starts_with((string) $m->getAttribute('password'), '$2y$'))
                $m->setAttribute('password', Hash::make($m->getAttribute('password')));
            if (empty($m->getAttribute(CPC::COL_DOJ)))
                $m->setAttribute(CPC::COL_DOJ, now('America/Sao_Paulo')->format('Y-m-d'));
            try {
                $userId = $m->getAttribute(UC::COL_USER_ID);
                $empName = $m->getAttribute('name');
                $empEmail = $m->getAttribute('email');
                $empPhone = $m->getAttribute('phone');
                $empAsUser = null;
                if (!empty($userId))
                    $empAsUser = User::query()->find($userId);
                if (!$empAsUser) {
                    $empAsUser = User::query()
                        ->where(function ($query) use ($empName, $empEmail, $empPhone) {
                            if (!empty($empEmail))
                                $query->orWhere('email', $empEmail);
                            if (!empty($empPhone))
                                $query->orWhere('phone', $empPhone);
                            if (!empty($empName))
                                $query->orWhere('name', $empName);
                        })
                        ->first();
                }
                if ($empAsUser) {
                    if (empty($m->getAttribute(UC::COL_USER_ID)))
                        $m->user()->associate($empAsUser);
                    foreach (['name', 'gender'] as $col) {
                        $userValue = $empAsUser->getAttribute($col) ?? null;
                        if (!empty($userValue))
                            $m->setAttribute($col, $userValue);
                    }
                    foreach (['phone', 'email'] as $col) {
                        $userValue = $empAsUser->getAttribute($col) ?? null;
                        $empValue = $m->getAttribute($col) ?? null;
                        if ($userValue !== null && $empValue === null)
                            $m->setAttribute($col, $userValue);
                    }
                }
            } catch (\Exception $e) {
                Log::warning(
                    static::class . ' failed to sync employee with user',
                    [
                        'employee_id' => $m->getAttribute('id'),
                        'user_id' => $m->getAttribute(UC::COL_USER_ID),
                        'error' => $e->getMessage(),
                    ]
                );
            }
        });

        static::updating(function (self $m) use ($normalizeAccNum, $ensureUnique) {
            self::ensureValidAge($m);
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            $isNormalizePhoneCallable && $m->setAttribute('phone', self::normalizePhone($m->getAttribute('phone'), 'Employee phone', $m->getAttribute('id')));
            $ensureUnique($m, 'phone');
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            $isNormalizeEmailCallable && $m->setAttribute('email', self::normalizeEmail($m->getAttribute('email') ?? null));
            $ensureUnique($m, 'email');
            if ($m->isDirty(UC::COL_ACC_NM)) {
                $m->{UC::COL_ACC_NM}
                    = $normalizeAccNum($m->{UC::COL_ACC_NM} ?? null);
                $ensureUnique($m, UC::COL_ACC_NM);
            }
            if (
                $m->isDirty(CPC::COL_BRC_ID)
                && empty($m->getAttribute(CPC::COL_BRC_LC))
            ) {
                $addr = $m->branch()->value('address');
                if ($addr) $m->setAttribute(CPC::COL_BRC_LC, $addr);
            }
            if ($m->isDirty('password') && !empty($m->getAttribute('password')) && !str_starts_with((string) $m->getAttribute('password'), '$2y$'))
                $m->setAttribute('password', Hash::make($m->getAttribute('password')));
            if ($m->isDirty('gender')) {
                $rawGender = $m->getAttribute('gender');
                $normalized = Gender::normalize($rawGender);
                $m->setAttribute('gender', $normalized?->value ?? Gender::Other->value);
            }
            try {
                if ($m->isDirty('gender')) {
                    $rawGender = $m->getAttribute('gender');
                    $normalized = Gender::normalize($rawGender);
                    $m->setAttribute('gender', $normalized?->value ?? Gender::Other->value);
                }

                $userId = $m->getAttribute(UC::COL_USER_ID);
                $empName = $m->getAttribute('name');
                $empEmail = $m->getAttribute('email');
                $empPhone = $m->getAttribute('phone');

                $empAsUser = null;

                if (!empty($userId))
                    $empAsUser = User::query()->find($userId);

                if (!$empAsUser)
                    $empAsUser = User::query()
                        ->where(function ($query) use ($empName, $empEmail, $empPhone) {
                            if (!empty($empEmail))
                                $query->orWhere('email', $empEmail);
                            if (!empty($empPhone))
                                $query->orWhere('phone', $empPhone);
                            if (!empty($empName))
                                $query->orWhere('name', $empName);
                        })
                        ->first();

                if ($empAsUser) {
                    if (empty($m->getAttribute(UC::COL_USER_ID)))
                        $m->user()->associate($empAsUser);

                    foreach (['name', 'gender'] as $col) {
                        $userValue = $empAsUser->{$col} ?? null;

                        if (!empty($userValue)) {
                            if ($col === 'gender') {
                                $normalizedGender = Gender::normalize($userValue);
                                $m->setAttribute($col, $normalizedGender?->value ?? Gender::Other->value);
                            } else {
                                $m->setAttribute($col, $userValue);
                            }
                        }
                    }

                    foreach (['phone', 'email'] as $col) {
                        $userValue = $empAsUser->{$col} ?? null;
                        $empValue = $m->getAttribute($col) ?? null;

                        if ($userValue !== null && $empValue === null)
                            $m->setAttribute($col, $userValue);
                    }
                }
            } catch (\Exception $e) {
                Log::warning(
                    static::class . ' failed to sync employee with user',
                    [
                        'employee_id' => $m->getAttribute('id'),
                        'user_id' => $m->getAttribute(UC::COL_USER_ID),
                        'error' => $e->getMessage(),
                    ]
                );
            }
        });
    }

    protected static function ensureValidAge(self $m): void
    {
        $dob = $m->getAttribute('dob');
        if ($dob instanceof Carbon) {
            $age = $dob->age;

            if ($age < 18) {
                Log::warning('Attempted to save employee under 18', [
                    'employee_id' => $m->id,
                    'date_of_birth' => $dob->format('Y-m-d'),
                    'age' => $age,
                ]);
                throw new \InvalidArgumentException(
                    "Employee must be 18 or older. Current age: {$age}"
                );
            }
        }
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, CPC::COL_BRC_ID, 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, CPC::COL_DEP_ID, 'id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, UC::COL_DSG_ID, 'id');
    }

    public function salaryType(): BelongsTo
    {
        return $this->belongsTo(PayslipType::class, UC::COL_SLR_TP, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, UC::COL_TAX_ID, 'id');
    }

    public function documents(): Collection
    {
        return $this->employeeDocuments()->get();
    }

    public function termination(): HasOne
    {
        return $this->hasOne(Termination::class, UC::COL_EMP_ID, 'id');
    }

    public function resignation(): HasOne
    {
        return $this->hasOne(Resignation::class, UC::COL_EMP_ID, 'id');
    }


    public function getSalaryTypeNameAttribute(): ?string
    {
        return $this->salaryType?->name;
    }

    public function setDocumentsAttribute($value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['documents'] = json_last_error() === JSON_ERROR_NONE ? json_encode($decoded, JSON_UNESCAPED_UNICODE) : json_encode([$value], JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['documents'] = json_encode($value ?? [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function setGenderAttribute($value): void
    {
        $enum = $value instanceof Gender ? $value : Gender::normalize((string)$value);
        $this->attributes['gender'] = $enum?->value;
    }

    public function setPhoneAttribute($value): void
    {
        $v = is_string($value) ? preg_replace('/\D+/', '', $value) : $value;
        $this->attributes['phone'] = $v ?: null;
    }

    public function setEmailAttribute($value): void
    {
        $v = is_string($value) ? strtolower(trim($value)) : $value;
        $this->attributes['email'] = $v ?: null;
    }

    // NOTE: salary_type() alias removed — collides with 'salary_type' column.
    // Use $model->salaryTypeName() (relation) or $model->getAttributes()['salary_type'] (column).

    public function salaryTypeName(): mixed
    {
        return $this->hasOne(PayslipType::class, 'id', UC::COL_SLR_TP)->value('name');
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class, UC::COL_EMP_ID, 'id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, UC::COL_EMP_ID, 'id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, UC::COL_EMP_ID, 'id');
    }

    public function saturationDeductions(): HasMany
    {
        return $this->hasMany(SaturationDeduction::class, UC::COL_EMP_ID, 'id');
    }

    public function otherPayments(): HasMany
    {
        return $this->hasMany(OtherPayment::class, UC::COL_EMP_ID, 'id');
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class, UC::COL_EMP_ID, 'id');
    }

    public function getNetSalary(): float
    {
        $total_allowance = $this->allowances->sum(
            fn($a) =>
            $a->type === 'fixed' ? $a->amount : $a->amount * $this->salary / 100
        );
        $total_commission = $this->commissions->sum(
            fn($c) =>
            $c->type === 'fixed' ? $c->amount : $c->amount * $this->salary / 100
        );
        $total_loan = $this->loans->sum(
            fn($l) =>
            $l->type === 'fixed' ? $l->amount : $l->amount * $this->salary / 100
        );
        $total_saturation_deduction = $this->saturationDeductions->sum(
            fn($d) =>
            $d->type === 'fixed' ? $d->amount : $d->amount * $this->salary / 100
        );
        $total_other_payment = $this->otherPayments->sum(
            fn($o) =>
            $o->type === 'fixed' ? $o->amount : $o->amount * $this->salary / 100
        );
        $total_over_time = $this->overtimes->sum(
            fn($ot) =>
            $ot->number_of_days * $ot->hours * $ot->rate
        );

        return $this->salary
            + $total_allowance
            + $total_commission
            - $total_loan
            - $total_saturation_deduction
            + $total_other_payment
            + $total_over_time;
    }

    public static function allowance(string|int $id): string|false
    {
        return Allowance::where(UC::COL_EMP_ID, $id)->get()->toJson();
    }


    public static function commission(string|int $id): string|false
    {
        $commissions = Commission::where('employee_id', $id)->get();
        return json_encode($commissions);
    }

    public static function loan(string|int $id): string|false
    {
        $loans = Loan::where('employee_id', $id)->get();
        return json_encode($loans);
    }

    public static function saturationDeduction(string|int $id): string|false
    {
        $deductions = SaturationDeduction::where('employee_id', $id)->get();
        return json_encode($deductions);
    }

    public static function otherPayment(string|int $id): string|false
    {
        $payments = OtherPayment::where('employee_id', $id)->get();
        return json_encode($payments);
    }

    public static function overtime(string|int $id): string|false
    {
        $ots = Overtime::where('employee_id', $id)->get();
        return json_encode($ots);
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, UC::COL_EMP_ID, 'id');
    }

    public static function employeeId(): string|int
    {
        $e = self::latest()->first();
        if (!$e) return 1;
        $id = $e->getKey();
        return is_numeric($id)
            ? ((int)$id + 1)
            : (string) Str::uuid();
    }

    public function paySlip(): HasOne
    {
        return $this->hasOne(Payslip::class, UC::COL_EMP_ID, 'id');
    }

    public function presentStatus($employee_id, $date): mixed
    {
        return \App\Models\EmployeeAttendance::where('employee_id', $employee_id)
            ->where('date', $date)
            ->first();
    }

    public static function employeeSalary($salary): float|string
    {
        $e = self::where('salary', $salary)->first();
        return $e && $e->salary > 0 ? (float) $e->salary : '-';
    }
}
