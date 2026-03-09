<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{UserType};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BugComment extends Comment
{
    use HasFactory;

    protected $table = DC::TABLE_BG_CMT;

    protected $casts = [
        UC::COL_U_TP => UserType::class,
    ];

    protected static function defaultUserType(): UserType
    {
        return UserType::Client;
    }

    protected static function fillableFields(): array
    {
        return array_merge(parent::fillableFields(), [AC::COL_BUG]);
    }

    protected static function withRelations(): array
    {
        return ['author', 'bug'];
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class, AC::COL_BUG, 'id');
    }

    public function scopeForBug($query, string $bugId)
    {
        return $query->where(AC::COL_BUG, $bugId);
    }

    public function commentUser(): ?User
    {
        $createdBy = $this->getAttribute(DC::COL_TABLE_CREATOR);
        if (!$createdBy) return null;
        return User::find($createdBy);
    }
}
