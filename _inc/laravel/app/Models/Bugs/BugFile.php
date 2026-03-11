<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
final class BugFile extends AbstractFile
{
    use HasFactory;

    protected $table = DC::TABLE_BG_FL;

    protected $with = ['bug'];

    protected $fillable = [
        AC::COL_BUG,
        UC::COL_U_TP,
        // legado:
        'file',
        // HasFileColumns:
        DC::COL_FL_PT,
        'name',
        'extension',
        DC::COL_MM_TP,
        DC::COL_LA,
        'size',
        'description',
        'notes',
        DC::COL_DL_CT,
        DC::COL_FL_SZ,
        DC::COL_PERM_RLS,
        'executors',
        'editors',
        'viewers',
        DC::COL_EXP_DT,
        'type',
    ];

    protected $casts = [
        UC::COL_U_TP => UserType::class,
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            $raw = $m->getAttribute(UC::COL_U_TP);
            $normalized = $raw instanceof UserType
                ? $raw
                : UserType::normalize(is_string($raw) ? $raw : (string) $raw);
            $m->setAttribute(UC::COL_U_TP, $normalized);
        });
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class, AC::COL_BUG, 'id');
    }
}
