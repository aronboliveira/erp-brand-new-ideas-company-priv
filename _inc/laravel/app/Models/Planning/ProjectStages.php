<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    PermissionsConstants,
    ProjectsConstants,
    DatabaseConstants
};
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model};
use Illuminate\Support\Facades\Auth;

class ProjectStages extends Model
{
    use ChecksLogin, HasFactory, UsesUuids;

    private const COL_NAME      = ProjectsConstants::COL_NM;
    private const COL_COLOR     = ProjectsConstants::COL_CL;
    private const COL_ORDER     = ActivitiesConstants::COL_OD;
    private const COL_CREATED_BY = DatabaseConstants::TABLE_CREATOR;

    protected $fillable = [
        self::COL_NAME,
        self::COL_COLOR,
        self::COL_CREATED_BY,
        self::COL_ORDER,
    ];

    public function tasks(string $projectId): Collection
    {
        $user = Auth::user();
        $query = Task::where(ProjectsConstants::COL_STG, $this->id)
            ->where(ProjectsConstants::COL_PJ_ID, $projectId);
        if ($user->type !== PermissionsConstants::CL && $user->type !== PermissionsConstants::CPN)
            $query->where(ProjectsConstants::COL_ASGN, $user?->id);
        return $query->orderBy(self::COL_ORDER)->get();
    }

    public static function getChartData(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $m      = date('m');
        $de     = date('d');
        $y      = date('Y');
        $format = 'Y-m-d';
        $arrDate = [];
        $arrDay = [];
        for ($i = 0; $i <= 6; $i++) {
            $date             = date($format, mktime(0, 0, 0, $m, $de - $i, $y));
            $arrDay['label'][] = __(date(
                'D',
                mktime(0, 0, 0, $m, $de - $i, $y)
            ));
            $arrDate[]        = $date;
        }
        $stages = self::where(
            self::COL_CREATED_BY,
            $user?->creatorId()
        )->get();
        $arrTask = [];
        $i      = 0;
        if ($user->type == PermissionsConstants::CPN) {
            foreach ($stages as $stage) {
                $data = [];
                foreach ($arrDate as $d)
                    $data[] = Task::where(ProjectsConstants::COL_STG, $stage->id)
                        ->whereDate(DatabaseConstants::COL_U_AT, $d)->count();
                $arrTask[] = [
                    'label'           => $stage->name,
                    'fill'            => '!0',
                    'backgroundColor' => 'transparent',
                    'borderColor'     => $stage->color,
                    'data'            => $data,
                ];
                $i++;
            }
        } elseif ($user->type == PermissionsConstants::CL) {
            foreach ($stages as $stage) {
                $data = [];
                foreach ($arrDate as $d)
                    $data[] = Task::join(
                        DatabaseConstants::TABLE_PROJECTS,
                        DatabaseConstants::TABLE_TASKS . '.' . ProjectsConstants::COL_PJ_ID,
                        '=',
                        DatabaseConstants::TABLE_PROJECTS . 'id'
                    )
                        ->where(DatabaseConstants::TABLE_PROJECTS . PermissionsConstants::CL, $user?->id)
                        ->where(ProjectsConstants::COL_STG, $stage->id)
                        ->whereDate(DatabaseConstants::TABLE_TASKS . '.'
                            . DatabaseConstants::COL_U_AT, $d)
                        ->count();
                $arrTask[] = [
                    'label'           => $stage->name,
                    'fill'            => '!0',
                    'backgroundColor' => 'transparent',
                    'borderColor'     => $stage->color,
                    'data'            => $data,
                ];
                $i++;
            }
        } else {
            foreach ($stages as $stage) {
                $data = [];
                foreach ($arrDate as $d)
                    $data[] = Task::where(ProjectsConstants::COL_ASGN, $user?->id)
                        ->where(ProjectsConstants::COL_STG, $stage->id)
                        ->whereDate(DatabaseConstants::TABLE_TASKS . '.'
                            . DatabaseConstants::COL_U_AT, $d)
                        ->count();
                $arrTask[] = [
                    'label'           => $stage->name,
                    'fill'            => '!0',
                    'backgroundColor' => 'transparent',
                    'borderColor'     => $stage->color,
                    'data'            => $data,
                ];
                $i++;
            }
        }
        $arrTaskData = array_merge($arrDay, ['dataset' => $arrTask]);
        unset($arrTaskData['dataset'][$i - 1]['fill']);
        $arrTaskData['dataset'][$i - 1]['backgroundColor'] = '#ccc';
        return $arrTaskData;
    }
}
