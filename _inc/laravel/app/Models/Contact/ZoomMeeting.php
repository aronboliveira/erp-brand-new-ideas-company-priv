<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Traits\UsesUuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class ZoomMeeting extends Model
{
    use HasFactory, UsesUuids;

    private const APPENDS = ['client_name', ActivitiesConstants::COL_PJ_NM];             // ! CHANGED
    private const FILLABLE = [                                         // ! CHANGED
        ActivitiesConstants::COL_TT,
        'meeting_id',
        'client_id',
        ProjectsConstants::COL_PJ_ID,
        ProjectsConstants::COL_S_DT,
        'duration',
        'start_url',
        ActivitiesConstants::COL_PW,
        'join_url',
        ActivitiesConstants::COL_TSK_STT,
        DatabaseConstants::COL_TABLE_CREATOR
    ];
    protected $appends = self::APPENDS;
    protected $fillable = self::FILLABLE;

    public function getClientNameAttribute(): string
    {
        $client = User::select(UsersConstants::COL_NM)
            ->whereKey($this->client_id)
            ->orderBy('id', 'desc')
            ->first();
        return $client[UsersConstants::COL_NM] ?? '';
    }

    // public function getProjectNameAttribute(): string
    // {
    //   $project = Project::select('project_name')
    //     ->whereKey($this->project_id)
    //     ->orderBy('id','desc')
    //     ->first();
    //
    //   return $project->project_name ?? '';
    // }

    public function checkDateTime(): int
    {
        return Carbon::parse($this->start_date)
            ->addMinutes($this->duration)
            ->gt(Carbon::now()) ? 1 : 0;
    }

    public function projectName()
    {
        return $this->hasOne(Project::class, 'id', ProjectsConstants::COL_PJ_ID);
        // * consider belongsTo(Project::class,ProjectsConstants::COL_PJ_ID)
    }

    public function userName()
    {
        return $this->hasOne(User::class, 'id', UsersConstants::COL_USER_ID);
        // * consider belongsTo(User::class,UsersConstants::COL_USER_ID)
    }

    public function users(string $users): array
    {
        return collect(explode(',', $users))
            ->map(fn($id) => User::find($id))
            ->toArray();
    }
}
