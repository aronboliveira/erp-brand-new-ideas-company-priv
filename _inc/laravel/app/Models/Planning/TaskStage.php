<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants
};
use App\Traits\{ChecksLogin, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TaskStage extends Model
{
    use ChecksLogin, UsesUuids;

    protected $fillable = [
        ProjectsConstants::COL_STG_NM,
        ActivitiesConstants::COL_PJ,
        ActivitiesConstants::COL_CPT,
        ProjectsConstants::COL_CL,
        ActivitiesConstants::COL_OD,
        DatabaseConstants::COL_TABLE_CREATOR,
    ];

    private const STAGES_LIST = [
        'Todo',
        'In Progress',
        'Review',
        'Done'
    ];
    public static array $stages = self::STAGES_LIST;

    public static function getChartData(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $today =  Carbon::now();
        $period = collect(range(0, 6))
            ->map(fn($i) => $today->copy()->subDays($i));
        $labels = $period->map(fn($d) => __($d->format('D')))->all();
        $dates = $period->map(fn($d) => $d->format('Y-m-d'))->all();
        $stages = self::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
        $datasets = [];
        foreach ($stages as $stage) {
            $data = array_map(
                fn($d) => match ($user?->type) {
                    PermissionsConstants::CPN => ProjectTask::where(ProjectsConstants::COL_STAGE_ID, $stage->id)
                        ->whereDate(DatabaseConstants::COL_U_AT, $d)->count(),
                    PermissionsConstants::CL => ProjectTask::join(
                        DatabaseConstants::TABLE_PROJECTS,
                        DatabaseConstants::TABLE_TSK_STGS . '.' . ProjectsConstants::COL_PJ_ID,
                        '=',
                        DatabaseConstants::TABLE_PROJECTS . '.id'
                    )->where(DatabaseConstants::TABLE_PROJECTS . '.client_id', $user?->id)
                        ->where(ProjectsConstants::COL_STAGE_ID, $stage->id)
                        ->whereDate(DatabaseConstants::TABLE_TSK_STGS . '.'
                            . DatabaseConstants::COL_U_AT, $d)
                        ->count(),
                    default => ProjectTask::where(ProjectsConstants::COL_ASGN, $user?->id)
                        ->where(ProjectsConstants::COL_STAGE_ID, $stage->id)
                        ->whereDate(DatabaseConstants::COL_U_AT, $d)
                        ->count()
                },
                $dates
            );
            $datasets[] = [
                ProjectsConstants::COL_NM
                => $stage->name,
                'backgroundColor' => 'transparent',
                'borderColor'     => $stage->color,
                'data'            => $data
            ];
        }
        $last = count($datasets) - 1;
        unset($datasets[$last]['fill']);
        $datasets[$last]['backgroundColor'] = '#ccc';
        return ['label' => $labels, 'dataset' => $datasets];
    }
}
