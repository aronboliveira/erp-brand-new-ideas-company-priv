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
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Collection,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne
};
use Illuminate\Support\{Str, Facades\Hash};

class Employee extends Model
{
    use UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_EMPLOYEES;
    protected $guarded = ['id', DC::TABLE_CREATOR];
    protected $with = ['branch', 'department', 'designation'];
    protected $hidden = ['password'];
    protected $casts = [
        'dob' => 'date',
        'salary' => 'decimal:2',
        'gender' => Gender::class,
        'documents' => 'array',
        'password' => 'hashed',
    ];
    protected $fillable = [
        UC::COL_USER_ID,
        'name',
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
        $normalizePhone = static function (?string $v): ?string {
            if ($v === null) return null;
            $v = preg_replace('/\D+/', '', $v);
            return $v !== '' ? $v : null;
        };
        $normalizeEmail = static function (?string $v): ?string {
            if ($v === null) return null;
            $v = strtolower(trim($v));
            return $v !== '' ? $v : null;
        };
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

        static::creating(function (self $m) use ($normalizePhone, $normalizeEmail, $normalizeAccNum, $ensureUnique) {
            if (empty($m->{UC::COL_EMP_ID})) {
                do $publicId = (string) \Illuminate\Support\Str::uuid();
                while (self::where(UC::COL_EMP_ID, $publicId)->exists());
                $m->{UC::COL_EMP_ID} = $publicId;
            }

            $m->phone = $normalizePhone($m->phone ?? null);
            $m->email = $normalizeEmail($m->email ?? null);
            $m->{UC::COL_ACC_NM}
                = $normalizeAccNum($m->{UC::COL_ACC_NM} ?? null);

            $ensureUnique($m, 'phone');
            $ensureUnique($m, 'email');
            $ensureUnique($m, UC::COL_ACC_NM);

            if (empty($m->{CPC::COL_BRC_LC}) && $m->{CPC::COL_BRC_ID}) {
                $addr = $m->branch()->value('address');
                if ($addr) $m->{CPC::COL_BRC_LC} = $addr;
            }
            if (!empty($m->password) && !str_starts_with((string) $m->password, '$2y$'))
                $m->password = Hash::make($m->password);
            if (empty($m->{CPC::COL_DOJ}))
                $m->{CPC::COL_DOJ} = now('America/Sao_Paulo')->format('Y-m-d');
        });

        static::updating(function (self $m) use ($normalizePhone, $normalizeEmail, $normalizeAccNum, $ensureUnique) {
            if ($m->isDirty('phone')) {
                $m->phone = $normalizePhone($m->phone ?? null);
                $ensureUnique($m, 'phone');
            }
            if ($m->isDirty('email')) {
                $m->email = $normalizeEmail($m->email ?? null);
                $ensureUnique($m, 'email');
            }
            if ($m->isDirty(UC::COL_ACC_NM)) {
                $m->{UC::COL_ACC_NM}
                    = $normalizeAccNum($m->{UC::COL_ACC_NM} ?? null);
                $ensureUnique($m, UC::COL_ACC_NM);
            }

            if (
                $m->isDirty(CPC::COL_BRC_ID)
                && empty($m->{CPC::COL_BRC_LC})
            ) {
                $addr = $m->branch()->value('address');
                if ($addr) $m->{CPC::COL_BRC_LC} = $addr;
            }
            if ($m->isDirty('password') && !empty($m->password) && !str_starts_with((string) $m->password, '$2y$'))
                $m->password = Hash::make($m->password);
            if ($m->isDirty('gender'))
                $m->gender = $m->gender;
        });
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
        return $this->hasMany(EmployeeDocument::class, 'employee_id', 'employee_id')->get();
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

    public function salary_type(): HasOne // * KEPT FOR COMPATIBILITY, SHOULD NOT BE CALLED IN ENDPOINT
    {
        return $this->salaryTypeName();
    }

    public function salaryTypeName(): mixed
    {
        return $this->hasOne(PayslipType::class, 'id', 'salary_type')->pluck('name')->first();
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function saturationDeductions(): HasMany
    {
        return $this->hasMany(SaturationDeduction::class);
    }

    public function otherPayments(): HasMany
    {
        return $this->hasMany(OtherPayment::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
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
        $allowances = Allowance::where('employee_id', $id)->get();
        return json_encode($allowances);
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
        return $this->hasOne(Payslip::class, 'id', 'employee_id');
    }

    public function presentStatus($employee_id, $date): mixed
    {
        return \App\Models\EmployeeAttendance::where('employee_id', $employee_id)
            ->where('date', $date)
            ->first();
    }

    public static function employeeSalary($salary): float
    {
        $e = self::where('salary', $salary)->first();
        return $e && $e->salary > 0 ? $e->salary : '-';
    }
}
