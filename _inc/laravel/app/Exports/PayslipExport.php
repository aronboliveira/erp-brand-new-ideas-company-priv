<?php

namespace App\Exports;

use App\Models\{Payslip, User};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayslipExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;

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
    private const PYTHON_EXPORTER = 'PayslipExport';

    private object $request;

    public function __construct(object $request)
    {
        $this->request = $request;
    }

    public function collection(): Collection
    {
        $result ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
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
            Log::info(__METHOD__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $query = Payslip::where('created_by', $user->creatorId());
            $month = $this->request->filterMonth
                ?? date('m', strtotime('last month'));
            $year = $this->request->filterYear
                ?? date('Y');
            $query->where('salary_month', "{$year}-{$month}");
            $records = $query->get();
            $result = collect($records)->map(function ($payslip) use ($user) {
                $emp = $payslip->employees ?? null;
                return [
                    'empId' => ($emp !== null)
                        ? (User::employeeIdFormat((int)($emp->employee_id ?? 0)) ?? '')
                        : '',
                    'name' => $emp->name ?? '',
                    'salary' => $user->priceFormat($payslip->gross_salary ?? 0),
                    'netSalary' => $user->priceFormat($payslip->net_payable ?? 0),
                    'status' => ($payslip->status ?? 0) === 0 ? 'UnPaid' : 'Paid',
                    'accountHolderName' => $emp->account_holder_name ?? '',
                    'accountNumber' => $emp->account_number ?? '',
                    'bankName' => $emp->bank_name ?? '',
                    'bankIdentifierCode' => $emp->bank_identifier_code ?? '',
                    'branchLocation' => $emp->branch_location ?? '',
                    'taxPayerId' => $emp->tax_payer_id ?? '',
                ];
            });
            Log::info(__METHOD__ . ' completed', [
                'count' => $result->count(),
                'class' => static::class
            ]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $result = collect();
        }
        return $result;
    }

    public function headings(): array
    {
        return self::HEADERS;
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
                    $sheet->getStyle('A1:K1')->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '001122']
                        ],
                        'borders' => [
                            'horizontal' => [
                                'borderStyle' => Border::BORDER_HAIR,
                                'color' => ['argb' => '11333333']
                            ],
                            'vertical' => [
                                'borderStyle' => Border::BORDER_THIN,
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
        $user ??= null;
        $userOrRedirect ??= null;
        $data ??= [];
        try {
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
            $query = Payslip::where('created_by', $user->creatorId());
            $month = $this->request->filterMonth
                ?? date('m', strtotime('last month'));
            $year = $this->request->filterYear
                ?? date('Y');
            $query->where('salary_month', "{$year}-{$month}");
            $records = $query->get();
            $data = [
                'payslips' => $records->map(function ($payslip) use ($user) {
                    $emp = $payslip->employees ?? null;
                    return [
                        'employee_id' => ($emp !== null)
                            ? (User::employeeIdFormat((int)($emp->employee_id ?? 0)) ?? '')
                            : '',
                        'name' => $emp->name ?? '',
                        'gross_salary' => $payslip->gross_salary ?? 0,
                        'net_salary' => $payslip->net_payable ?? 0,
                        'status' => ($payslip->status ?? 0) === 0 ? 'UnPaid' : 'Paid',
                        'account_holder_name' => $emp->account_holder_name ?? '',
                        'account_number' => $emp->account_number ?? '',
                        'bank_name' => $emp->bank_name ?? '',
                        'bank_identifier_code' => $emp->bank_identifier_code ?? '',
                        'branch_location' => $emp->branch_location ?? '',
                        'tax_payer_id' => $emp->tax_payer_id ?? '',
                    ];
                })->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADERS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('payslips');
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
