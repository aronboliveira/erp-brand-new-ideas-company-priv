<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\UserType;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Relations\BelongsTo};

class ContractComment extends Comment
{
    use HasFactory;

    protected $table = DC::TABLE_CTC_CMT;

    protected $casts = [
        UC::COL_U_TP => UserType::class,
    ];

    protected static function defaultUserType(): UserType
    {
        return UserType::Client;
    }

    protected static function fillableFields(): array
    {
        return array_merge(parent::fillableFields(), [PJC::COL_CTC_ID]);
    }

    protected static function withRelations(): array
    {
        return ['author', 'contract'];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID, 'id');
    }

    public function scopeForContract($query, string $contractId)
    {
        return $query->where(PJC::COL_CTC_ID, $contractId);
    }
}
