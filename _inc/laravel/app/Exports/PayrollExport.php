<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\Payslip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    use ChecksLogin;

    private const HEADINGS          = ['Employee Id', 'Status', 'Employee Name', 'Salary', 'Net Salary', 'Month'];
    private const JOIN_FOREIGN_FIELD = 'employees.id';
    private const JOIN_LOCAL_FIELD  = 'pay_slips.employee_id';
    private const JOIN_TABLE        = 'employees';
    private const SELECT_COLUMNS    = ['pay_slips.*', 'employees.name'];

    public function collection(): Collection
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return collect();
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', ['user_id' => $user?->id]);

        try {
            $month = date('Y-m');
            Log::info(__CLASS__ . '::building query', ['month' => $month, 'user_id' => $user?->id]);
            $rows = Payslip::select(...self::SELECT_COLUMNS)
                ->leftJoin(self::JOIN_TABLE, self::JOIN_LOCAL_FIELD, '=', self::JOIN_FOREIGN_FIELD)
                ->where('pay_slips.created_by', $user?->creatorId())
                ->where('salary_month', $month)
                ->get();
            Log::info(__CLASS__ . '::fetched records', ['count' => count($rows)]);

            return $rows->map(fn ($p) => [
                ...(array)$p,
                'employeeId'   => $p->employees
                    ? $user?->employeeIdFormat($p->employees->employee_id)
                    : '',
                'employeeName' => $p->name ?? '',
                'salary'       => $user?->priceFormat($p->basic_salary),
                'netSalary'    => $user?->priceFormat($p->net_payble),
                'month'        => $p->salary_month,
                'status'       => $p->status === 0 ? 'UnPaid' : 'Paid'
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function styles(Worksheet $sheet)
    {
        // header row: bold font + cold low-opacity fill
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCE5FF'] // cold light blue
                ]
            ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet       = $event->sheet->getDelegate();
                $highestRow  = $sheet->getHighestRow();
                $highestCol  = $sheet->getHighestColumn();
                $fullRange   = 'A1:' . $highestCol . $highestRow;

                // freeze header
                $sheet->freezePane('A2');

                // horizontal borders nearly transparent
                $sheet->getStyle($fullRange)
                    ->applyFromArray([
                        'borders' => [
                            'insideHorizontal' => [
                                'borderStyle' => Border::BORDER_HAIR,
                                'color' => ['rgb' => 'E0E0E0']
                            ]
                        ]
                    ]);

                // vertical borders grey; header outline deep grey
                $sheet->getStyle($fullRange)
                    ->applyFromArray([
                        'borders' => [
                            'insideVertical' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ]
                    ]);
                $sheet->getStyle('A1:' . $highestCol . '1')
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '333333']
                            ]
                        ]
                    ]);
            }
        ];
    }
}
