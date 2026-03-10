<?php

namespace App\Exports;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Models\{ProjectReport, ProjectTask};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;

final class TaskReportExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;

    private const HEADINGS = [
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
    private const PYTHON_EXPORTER    = 'TaskReportExport';
    private const REMOVED_ATTRIBUTES = [
        ProjectsConstants::COL_E_HRS,
        ProjectsConstants::COL_PR_CL,
        ProjectsConstants::COL_PJ_ID,
        ActivitiesConstants::COL_OD,
        DatabaseConstants::COL_TABLE_CREATOR,
        ProjectsConstants::COL_IS_FV,
        ProjectsConstants::COL_IS_CP,
        ProjectsConstants::COL_M_AT,
        ProjectsConstants::COL_PGR,
        DatabaseConstants::COL_C_AT,
        DatabaseConstants::COL_U_AT
    ];

    private string $projectId;

    public function __construct(string|int $id)
    {
        $this->projectId = (string) $id;
    }

    public function collection(): Collection
    {
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return collect();
            }
            $user = $userOrRedirect;
            Log::info(__METHOD__ . ' started', [
                ProjectsConstants::COL_PJ_ID => $this->projectId,
                UsersConstants::COL_USER_ID => $user->id ?? null,
                'class' => static::class
            ]);
            $tasks = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $this->projectId)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                ->get();
            $tasks->each(function ($task) {
                foreach (self::REMOVED_ATTRIBUTES as $attr) {
                    unset($task->{$attr});
                }
            });
            $result = collect($tasks)->map(function ($task) {
                return [
                    'id' => $task->id ?? null,
                    ActivitiesConstants::COL_TT => $task[ActivitiesConstants::COL_TT] ?? '',
                    ActivitiesConstants::COL_DESC => $task[ActivitiesConstants::COL_DESC] ?? '',
                    ProjectsConstants::COL_S_DT => $task[ProjectsConstants::COL_S_DT] ?? '',
                    ProjectsConstants::COL_E_DT => $task[ProjectsConstants::COL_E_DT] ?? '',
                    ProjectsConstants::COL_PRT => $task[ProjectsConstants::COL_PRT] ?? '',
                    ProjectsConstants::COL_ASGN => ProjectReport::assignUser($task[ProjectsConstants::COL_ASGN] ?? ''),
                    'milestone' => ProjectReport::milestone($task[ProjectsConstants::COL_ML_ID] ?? 0),
                    ActivitiesConstants::COL_TSK_STT => ProjectReport::status($task[ProjectsConstants::COL_STAGE_ID] ?? 0),
                ];
            });
            Log::info(__METHOD__ . ' completed', [
                'count' => $result->count(),
                'class' => static::class
            ]);
            return $result;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            return collect();
        }
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                try {
                    Log::info(__METHOD__ . ' styling started', [
                        'class' => static::class
                    ]);
                    $sheet = $event->sheet->getDelegate();
                    if (empty($sheet)) {
                        Log::warning(__METHOD__ . ' null sheet', [
                            'class' => static::class
                        ]);
                        return;
                    }
                    $sheet->getStyle('A1:I1')->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
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
                    Log::info(__METHOD__ . ' styling completed', [
                        'class' => static::class
                    ]);
                } catch (\Throwable $e) {
                    Log::error(__METHOD__ . ' styling exception', [
                        'error' => $e->getMessage(),
                        'class' => static::class
                    ]);
                }
            }
        ];
    }

    public function exportViaPython(?string $outputPath = null): string
    {
        $result ??= '';
        $data ??= [];
        try {
            $collection = $this->collection();
            $data = [
                'tasks' => $collection->toArray(),
                'project_id' => $this->projectId,
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('task_report');
            }
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $result = '';
        }
        return $result;
    }
}
