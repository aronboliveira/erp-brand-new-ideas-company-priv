<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    DatabaseConstants as DC
};
use App\Traits\{ChecksLogin, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProjectStages extends Model
{
    use ChecksLogin, HasFactory, UsesUuids, HasAuditFields;

    private const COL_NAME      = PJC::COL_NM;
    private const COL_COLOR     = PJC::COL_CL;
    private const COL_ORDER     = AC::COL_OD;
    private const COL_CREATED_BY = DC::COL_TABLE_CREATOR;

    protected $fillable = [
        self::COL_NAME,
        self::COL_COLOR,
        self::COL_ORDER,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR
    ];

    public function tasks(string $projectId): Collection
    {
        $user = Auth::user();
        $query = Task::where(PJC::COL_STG, $this->id)
            ->where(PJC::COL_PJ_ID, $projectId);
        if ($user->type !== PMC::CL && $user->type !== PMC::CPN)
            $query->where(PJC::COL_ASGN, $user?->id);
        return $query->orderBy(self::COL_ORDER)->get();
    }

    public static function getChartData(): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
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
        if ($user->type == PMC::CPN) {
            foreach ($stages as $stage) {
                $data = [];
                foreach ($arrDate as $d)
                    $data[] = Task::where(PJC::COL_STG, $stage->id)
                        ->whereDate(DC::COL_U_AT, $d)->count();
                $arrTask[] = [
                    'label'           => $stage->name,
                    'fill'            => '!0',
                    'backgroundColor' => 'transparent',
                    'borderColor'     => $stage->color,
                    'data'            => $data,
                ];
                $i++;
            }
        } elseif ($user->type == PMC::CL) {
            foreach ($stages as $stage) {
                $data = [];
                foreach ($arrDate as $d)
                    $data[] = Task::join(
                        DC::TABLE_PROJECTS,
                        DC::TABLE_TASKS . '.' . PJC::COL_PJ_ID,
                        '=',
                        DC::TABLE_PROJECTS . 'id'
                    )
                        ->where(DC::TABLE_PROJECTS . PMC::CL, $user?->id)
                        ->where(PJC::COL_STG, $stage->id)
                        ->whereDate(DC::TABLE_TASKS . '.'
                            . DC::COL_U_AT, $d)
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
                    $data[] = Task::where(PJC::COL_ASGN, $user?->id)
                        ->where(PJC::COL_STG, $stage->id)
                        ->whereDate(DC::TABLE_TASKS . '.'
                            . DC::COL_U_AT, $d)
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
