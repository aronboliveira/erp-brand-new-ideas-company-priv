<?php

namespace App\Exports;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Traits\ChecksLogin;
use App\Models\{ProjectTask, ProjectReport};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;

final class TaskReportExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const REMOVED_ATTRIBUTES = [
        ProjectsConstants::COL_E_HRS,
        ProjectsConstants::COL_PR_CL,
        ProjectsConstants::COL_PJ_ID,
        ActivitiesConstants::COL_OD,
        DatabaseConstants::TABLE_CREATOR,
        ProjectsConstants::COL_IS_FV,
        ProjectsConstants::COL_IS_CP,
        ProjectsConstants::COL_M_AT,
        ProjectsConstants::COL_PGR,
        DatabaseConstants::COL_C_AT,
        DatabaseConstants::COL_U_AT
    ];

    private int $projectId;

    public function __construct(int $id)
    {
        $this->projectId = $id;
    }

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [
            ProjectsConstants::COL_PJ_ID => $this->projectId,
            UsersConstants::COL_USER_ID => $user?->id
        ]);
        $tasks = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $this->projectId)
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->get();
        $tasks->each(function ($task) {
            foreach (self::REMOVED_ATTRIBUTES as $attr) unset($task->{$attr});
        });
        $result = collect($tasks)->map(function ($task) {
            return [
                'id'          => $task->id,
                ActivitiesConstants::COL_TT       => $task[ActivitiesConstants::COL_TT],
                ActivitiesConstants::COL_DESC => $task[ActivitiesConstants::COL_DESC],
                ProjectsConstants::COL_S_DT   => $task[ProjectsConstants::COL_S_DT],
                ProjectsConstants::COL_E_DT     => $task[ProjectsConstants::COL_E_DT],
                ProjectsConstants::COL_PRT    => $task[ProjectsConstants::COL_PRT],
                ProjectsConstants::COL_ASGN    => ProjectReport::assignUser($task[ProjectsConstants::COL_ASGN]),
                'milestone'   => ProjectReport::milestone($task[ProjectsConstants::COL_ML_ID]),
                ActivitiesConstants::COL_TSK_STT      => ProjectReport::status($task[ProjectsConstants::COL_STAGE_ID]),
            ];
        });
        Log::info(__METHOD__ . ' completed', ['count' => $result->count()]);
        return $result;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Description',
            'Start Date',
            'End Date',
            'Priority',
            'Assign To',
            'Milestone',
            'Status'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font'   => ['bold' => true],
                    'fill'   => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '001122']
                    ],
                    'borders' => [
                        'horizontal' => [
                            'borderStyle' => 'hair',
                            'color' => ['argb' => '11333333']
                        ],
                        'vertical' => [
                            'borderStyle' => 'thin',
                            'color' => ['argb' => 'FF333333']
                        ]
                    ]
                ]);
                $sheet->freezePane('A2');
            }
        ];
    }
}
