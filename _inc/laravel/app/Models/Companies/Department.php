<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC};
use App\Traits\{HasAuditFields, NormalizesAddresses, StoresManyRefJson, UsesUuids};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Factories\{HasFactory};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use Illuminate\Support\Facades\{DB};

class Department extends Model
{
    use UsesUuids, HasFactory, HasAuditFields, NormalizesAddresses, StoresManyRefJson;

    protected $fillable = [
        'company',
        CPC::COL_BRC_ID,
        'name',
        'address',
        'description',
        'phone',
        'email',
        CPC::COL_MNG,
        'budget',
        'budgets',
        'expenses',
        'profits'
    ];
    protected $table    = DC::TABLE_DEPARTMENTS;
    protected $guarded  = ['id', DC::COL_TABLE_CREATOR];
    protected $with = ['branch'];
    protected $casts     = [
        'budget'   => 'decimal:2',
        'expenses' => 'decimal:2',
        'profit'   => 'decimal:2',
        'budgets'  => 'array',
    ];

    public static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            if (!empty($m->getAttribute('phone')) && $isNormalizePhoneCallable)
                $m->setAttribute('phone', self::normalizePhone($m->getAttribute('phone'), 'Department Phone', $m->getAttribute('id')));
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            if (!empty($m->getAttribute('email')) && $isNormalizeEmailCallable)
                $m->setAttribute('email', self::normalizeEmail(trim($m->getAttribute('email'))));
            if (!DB::table(DC::TABLE_USERS)->where('id', $m->getAttribute('company'))->where('type', 'company')->exists())
                $m->setAttribute('company', null);
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, CPC::COL_BRC_ID, 'id');
    }
}
