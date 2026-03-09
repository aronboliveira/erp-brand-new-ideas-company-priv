<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\Payslip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayslipExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const HEADERS = [
        'EMP ID',
        'Name',
        'Salary',
        'Net Salary',
        'Status',
        'Account Holder Name',
        'Account Number',
        'Bank Name',
        'Bank Identifier Code',
        'Branch Location',
        'Tax Payer Id'
    ];

    private object $request;

    public function __construct(object $request)
    {
        $this->request = $request;
    }

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;

        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        try {
            $query = Payslip::where(
                'created_by',
                $user?->creatorId()
            );

            $month = $this->request->filterMonth
                ?? date('m', strtotime('last month'));
            $year = $this->request->filterYear
                ?? date('Y');

            $query->where(
                'salary_month',
                "{$year}-{$month}"
            );

            $records = $query->get();

            $result = collect($records)->map(function ($payslip) use ($user) {
                $emp = $payslip->employees;
                return [
                    'empId'             => ($emp?->employee_id !== null)
                        ? \App\Models\User::employeeIdFormat($emp->employee_id)
                        : '',
                    'name'              => $emp?->name                        ?? '',
                    'salary'            => $user?->priceFormat($payslip->gross_salary),
                    'netSalary'         => $user?->priceFormat($payslip->net_payable),
                    'status'            => $payslip->status === 0 ? 'UnPaid' : 'Paid',
                    'accountHolderName' => $emp?->account_holder_name        ?? '',
                    'accountNumber'     => $emp?->account_number             ?? '',
                    'bankName'          => $emp?->bank_name                  ?? '',
                    'bankIdentifierCode' => $emp?->bank_identifier_code       ?? '',
                    'branchLocation'    => $emp?->branch_location            ?? '',
                    'taxPayerId'        => $emp?->tax_payer_id               ?? '',
                ];
            });

            Log::info(__METHOD__ . ' completed', ['count' => $result->count()]);

            return $result;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function headings(): array
    {
        return self::HEADERS;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:K1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '001122']
                    ],
                    'borders' => [
                        'horizontal' => [
                            'borderStyle' => Border::BORDER_HAIR,
                            'color'      => ['argb' => '11333333']
                        ],
                        'vertical' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'      => ['argb' => 'FF333333']
                        ]
                    ]
                ]);
                $sheet->freezePane('A2');
            }
        ];
    }
}
