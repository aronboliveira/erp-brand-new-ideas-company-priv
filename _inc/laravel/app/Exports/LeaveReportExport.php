<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\{Employee, Leave, User};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\{Collection, Facades\Log};
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithEvents,
    WithHeadings
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class LeaveReportExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const BODY_FILL_OPACITY      = '33FFFFFF';
    private const HEADER_BORDER_COLOR    = '000000';
    private const HEADER_FILL_COLOR      = 'FF1F1F2F';
    private const HORIZONTAL_BORDER_COLOR = '11000000';
    private const UNSET_FIELDS           = [
        'id',
        'leave_type_id',
        'start_date',
        'end_date',
        'applied_on',
        'total_leave_days',
        'leave_reason',
        'created_at',
        'created_by',
        'remark',
        'status',
        'updated_at',
        'account_id'
    ];
    private const VERTICAL_BORDER_COLOR  = '808080';

    public function collection(): Collection|RedirectResponse
    {
        $request = request();
        if (
            ($userOrRedirect = self::_checkLogin($request))
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;

        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', ['user_id' => $user?->id]);
        try {
            $allLeaves = Leave::all();
            $employees = Employee::where(
                'created_by',
                $user?->creatorId()
            )
                ->get();

            $counts = [];
            foreach ($employees as $emp) {
                $counts[$emp->id] = [
                    'approved' => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Approved')
                        ->count(),
                    'reject'   => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Reject')
                        ->count(),
                    'pending'  => Leave::where('employee_id', $emp->id)
                        ->where('status', 'Pending')
                        ->count()
                ];
            }

            $rows = [];
            foreach ($allLeaves as $leave) {
                /** @var \App\Models\Employee|null $emp */
                $emp     = $leave->employees;
                $empIdFmt = $emp
                    ? User::employeeIdFormat($emp->employee_id)
                    : '';
                $cnt     = $counts[$emp->id] ?? ['approved' => 0, 'reject' => 0, 'pending' => 0];
                $rows[]  = [
                    $empIdFmt,
                    $emp->name       ?? '',
                    $cnt['approved'] ?: '0',
                    $cnt['reject']   ?: '0',
                    $cnt['pending']
                ];
            }

            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' prepared rows', ['count' => count($rows)]);
            return Collection::make($rows);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return Collection::make([]);
        }
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee',
            'Approved Leaves',
            'Rejected Leaves',
            'Pending Leaves'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet  = $event->sheet->getDelegate();
                $sheet->getStyle('1:1')->getFont()->setBold(true);
                $sheet->freezePane('A2');
                $sheet->getStyle('1:1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB(self::HEADER_FILL_COLOR);

                $highest = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();
                for ($r = 2; $r <= $highest; ++$r)
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB(self::BODY_FILL_OPACITY);

                $sheet->getStyle("A1:{$lastCol}{$highest}")
                    ->getBorders()
                    ->getHorizontal()
                    ->getColor()
                    ->setARGB(self::HORIZONTAL_BORDER_COLOR);

                $sheet->getStyle("A1:{$lastCol}{$highest}")
                    ->getBorders()
                    ->getVertical()
                    ->getColor()
                    ->setARGB(self::VERTICAL_BORDER_COLOR);

                $sheet->getStyle("A1:{$lastCol}1")
                    ->getBorders()
                    ->getAllBorders()
                    ->getColor()
                    ->setARGB(self::HEADER_BORDER_COLOR);
            }
        ];
    }
}
