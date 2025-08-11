<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\{Branch, Department, Designation, Employee};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const REMOVED_ATTRIBUTES = [
        'id',
        'password',
        'userId',
        'employeeId',
        'documents',
        'salary_type',
        'taxPayerId',
        'isActive',
        'createdBy',
        'createdAt',
        'updatedAt'
    ];

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;

        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        $data = Employee::where(
            'created_by',
            $user?->creatorId()
        )->get();

        $data->each(function ($emp) {
            foreach (self::REMOVED_ATTRIBUTES as $attr)
                unset($emp->{$attr});
        });

        $data->each(
            fn ($emp, $i) => $this->enrich($emp, $i, $data)
        );

        Log::info(__METHOD__ . ' completed', ['count' => $data->count()]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Date of Birth',
            'Gender',
            'Phone Number',
            'Address',
            'Email ID',
            'Branch',
            'Department',
            'Designation',
            'Date of Join',
            'Account Holder Name',
            'Account Number',
            'Bank Name',
            'Bank Identifier Code',
            'Branch Location',
            'Salary'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:P1')->applyFromArray([
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
            }
        ];
    }

    private function enrich($emp, int $i, Collection &$data): void
    {
        $data[$i]['branch']    = $emp->branch->name     ?? '-';
        $data[$i]['department'] = $emp->department->name ?? '-';
        $data[$i]['designation'] = $emp->designation->name ?? '-';
        $data[$i]['salary']    = Employee::employeeSalary($emp->salary);
    }
}
