<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends Comment
{
    protected $table = DC::TABLE_TSK_CMT;

    protected static function fillableFields(): array
    {
        return array_merge(parent::fillableFields(), [AC::COL_TSK_ID]);
    }

    protected static function withRelations(): array
    {
        return ['author', 'task'];
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
