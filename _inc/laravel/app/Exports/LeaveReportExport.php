<?php

namespace App\Exports;

use App\Models\{Employee, Leave, User};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class LeaveReportExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;

    private const BODY_FILL_OPACITY       = '33FFFFFF';
    private const HEADER_BORDER_COLOR     = '000000';
    private const HEADER_FILL_COLOR       = 'FF1F1F2F';
    private const HEADINGS = [
        'Employee ID',
        'Employee',
        'Approved Leaves',
        'Rejected Leaves',
        'Pending Leaves'
    ];
    private const HORIZONTAL_BORDER_COLOR = '11000000';
    private const PYTHON_EXPORTER         = 'LeaveReportExport';
    private const UNSET_FIELDS = [
        'account_id',
        'applied_on',
        'created_at',
        'created_by',
        'end_date',
        'id',
        'leave_reason',
        'leave_type_id',
        'remark',
        'start_date',
        'status',
        'total_leave_days',
        'updated_at'
    ];
    private const VERTICAL_BORDER_COLOR   = '808080';

    public function collection(): Collection|RedirectResponse
    {
        $rows ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
        $request ??= request();
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return collect();
            }
            $user = $userOrRedirect;
            if (empty($user)) {
                Log::error(__METHOD__ . ' null user', ['class' => static::class]);
                return collect();
            }
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $allLeaves = Leave::all();
            $employees = Employee::where('created_by', $user->creatorId())->get();
            $counts = [];
            foreach ($employees as $emp) {
                $counts[$emp->id ?? 0] = [
                    'approved' => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Approved')
                        ->count(),
                    'reject' => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Reject')
                        ->count(),
                    'pending' => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Pending')
                        ->count()
                ];
            }
            $rowsArray = [];
            foreach ($allLeaves as $leave) {
                $emp = $leave->employee ?? null;
                $empIdFmt = $emp
                    ? User::employeeIdFormat((int)($emp->employee_id ?? 0))
                    : '';
                $cnt = $counts[$emp->id ?? 0] ?? [
                    'approved' => 0,
                    'reject' => 0,
                    'pending' => 0
                ];
                $rowsArray[] = [
                    $empIdFmt,
                    $emp->name ?? '',
                    $cnt['approved'] ?: '0',
                    $cnt['reject'] ?: '0',
                    $cnt['pending'] ?? 0
                ];
            }
            $rows = Collection::make($rowsArray);
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' prepared rows', [
                'count' => $rows->count(),
                'class' => static::class
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $rows = Collection::make([]);
        }
        return $rows;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
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
                    $sheet->getStyle('1:1')->getFont()->setBold(true);
                    $sheet->freezePane('A2');
                    $sheet->getStyle('1:1')->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB(self::HEADER_FILL_COLOR);
                    $highest = $sheet->getHighestRow();
                    $lastCol = $sheet->getHighestColumn();
                    for ($r = 2; $r <= $highest; ++$r) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB(self::BODY_FILL_OPACITY);
                    }
                    $sheet->getStyle("A1:{$lastCol}{$highest}")
                        ->getBorders()
                        ->getInsideHorizontal()
                        ->getColor()
                        ->setARGB(self::HORIZONTAL_BORDER_COLOR);
                    $sheet->getStyle("A1:{$lastCol}{$highest}")
                        ->getBorders()
                        ->getInsideVertical()
                        ->getColor()
                        ->setARGB(self::VERTICAL_BORDER_COLOR);
                    $sheet->getStyle("A1:{$lastCol}1")
                        ->getBorders()
                        ->getAllBorders()
                        ->getColor()
                        ->setARGB(self::HEADER_BORDER_COLOR);
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
        $user ??= null;
        $userOrRedirect ??= null;
        $data ??= [];
        try {
            $request = request();
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return '';
            }
            $user = $userOrRedirect;
            if (empty($user)) {
                Log::error(__METHOD__ . ' null user', ['class' => static::class]);
                return '';
            }
            $allLeaves = Leave::all();
            $employees = Employee::where('created_by', $user->creatorId())->get();
            $counts = [];
            foreach ($employees as $emp) {
                $counts[$emp->id ?? 0] = [
                    'approved' => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Approved')
                        ->count(),
                    'reject' => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Reject')
                        ->count(),
                    'pending' => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Pending')
                        ->count()
                ];
            }
            $leaveData = [];
            foreach ($allLeaves as $leave) {
                $emp = $leave->employee ?? null;
                $cnt = $counts[$emp->id ?? 0] ?? [
                    'approved' => 0,
                    'reject' => 0,
                    'pending' => 0
                ];
                $leaveData[] = [
                    'employee_id' => $emp
                        ? User::employeeIdFormat((int)($emp->employee_id ?? 0))
                        : '',
                    'employee_name' => $emp->name ?? '',
                    'approved_leaves' => $cnt['approved'] ?? 0,
                    'rejected_leaves' => $cnt['reject'] ?? 0,
                    'pending_leaves' => $cnt['pending'] ?? 0,
                ];
            }
            $data = [
                'leaves' => $leaveData,
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('leave_report');
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
