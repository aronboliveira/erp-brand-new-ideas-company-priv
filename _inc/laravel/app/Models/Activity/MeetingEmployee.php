<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class MeetingEmployee extends Model
{
    use HasFactory, UsesUuids;
    protected $fillable = [
        'meeting_id', 'employee_id', 'created_by'
    ];
    private const FK_MEETING   = 'meeting_id';
    private const FK_EMPLOYEE  = 'employee_id';
    private const LOCAL_KEY    = 'id';
    private const MODEL_MEETING = Meeting::class;
    private const MODEL_EMPLOYEE = Employee::class;
    public function meeting(): BelongsTo // * ADDED
    {
        return $this->belongsTo(
            self::MODEL_MEETING,
            self::FK_MEETING,
            self::LOCAL_KEY
        );
    }
    public function employee(): BelongsTo // * ADDED
    {
        return $this->belongsTo(
            self::MODEL_EMPLOYEE,
            self::FK_EMPLOYEE,
            self::LOCAL_KEY
        );
    }
}
