<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Throwable;
/**
 * @property mixed $created_by
 * @property string|null $deleteUrl
 * @property string|null $file

 * @property mixed $delete
 */

class TaskFile extends AbstractFile
{
    use HasFactory;

    protected $table = DC::TABLE_TSK_FL;

    protected $with = ['creator', 'task'];

    protected $casts = [
        UC::COL_U_TP => UserType::class,
    ];

    protected static function fillableFields(): array
    {
        return array_merge(parent::fillableFields(), [
            'file',         // legado
            AC::COL_TSK_ID,
            UC::COL_U_TP,
        ]);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                $raw = $m->getAttribute(UC::COL_U_TP);
                $norm = $raw instanceof UserType
                    ? $raw
                    : UserType::normalize(is_string($raw) ? $raw : null);
                $m->setAttribute(UC::COL_U_TP, ($norm ?: UserType::Customer)->value);
                $legacy = $m->getAttribute('file');
                $path   = $m->getAttribute(DC::COL_FL_PT);
                if ((is_string($legacy) && trim($legacy) !== '') && (!is_string($path) || trim($path) === ''))
                    $m->setAttribute(DC::COL_FL_PT, trim($legacy));
                elseif ((is_string($path) && trim($path) !== '') && (!is_string($legacy) || trim($legacy) === ''))
                    $m->setAttribute('file', trim($path));
            } catch (Throwable $e) {
                Log::warning(static::class . ' saving normalization failed', [
                    'id'    => $m->getAttribute('id'),
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, AC::COL_TSK_ID, 'id');
    }

    public function scopeForTask($query, string $taskId)
    {
        return $query->where(AC::COL_TSK_ID, $taskId);
    }
}
