<?php

namespace App\Exports;
use App\Models\Payslip;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings, WithStyles};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class PayrollExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;
    private const HEADINGS = [
        'Employee Id',
        'Status',
        'Employee Name',
        'Salary',
        'Net Salary',
        'Month'
    ];
    private const JOIN_FOREIGN_FIELD = 'employees.id';
    private const JOIN_LOCAL_FIELD   = 'payslips.employee_id';
    private const JOIN_TABLE         = 'employees';
    private const PYTHON_EXPORTER    = 'PayrollExport';
    private const SELECT_COLUMNS     = ['payslips.*', 'employees.name'];
    public function collection(): Collection
    {
        $rows ??= collect();
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
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $month = date('Y-m');
            Log::info(__CLASS__ . '::building query', [
                'month' => $month,
            $records = Payslip::select(...self::SELECT_COLUMNS)
                ->leftJoin(self::JOIN_TABLE, self::JOIN_LOCAL_FIELD, '=', self::JOIN_FOREIGN_FIELD)
                ->where('payslips.created_by', $user->creatorId())
                ->where('salary_month', $month)
                ->get();
            Log::info(__CLASS__ . '::fetched records', [
                'count' => $records->count(),
            $rows = $records->map(fn($p) => [
                ...(array)$p,
                'employeeId' => ($p->employees ?? null)
                    ? $user->employeeIdFormat((int)($p->employees->employee_id ?? 0))
                    : '',
                'employeeName' => $p->name ?? '',
                'salary' => $user->priceFormat($p->gross_salary ?? 0),
                'netSalary' => $user->priceFormat($p->net_payable ?? 0),
                'month' => $p->salary_month ?? '',
                'status' => ($p->status ?? 0) === 0 ? 'UnPaid' : 'Paid'
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            $rows = collect();
        }
        return $rows;
    }
    public function headings(): array
        return self::HEADINGS;
    public function styles(Worksheet $sheet)
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')
                ->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CCE5FF']
                    ]
            Log::error(__METHOD__ . ' styles exception', [
        return [];
    public function registerEvents(): array
        return [
            AfterSheet::class => function (AfterSheet $event) {
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
                    $highestRow = $sheet->getHighestRow();
                    $highestCol = $sheet->getHighestColumn();
                    $fullRange = 'A1:' . $highestCol . $highestRow;
                    $sheet->freezePane('A2');
                    $sheet->getStyle($fullRange)
                        ->applyFromArray([
                            'borders' => [
                                'insideHorizontal' => [
                                    'borderStyle' => Border::BORDER_HAIR,
                                    'color' => ['rgb' => 'E0E0E0']
                                ]
                            ]
                                'insideVertical' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'CCCCCC']
                    $sheet->getStyle('A1:' . $highestCol . '1')
                                'outline' => [
                                    'borderStyle' => Border::BORDER_MEDIUM,
                                    'color' => ['rgb' => '333333']
                    Log::info(__METHOD__ . ' styling completed', [
                } catch (\Throwable $e) {
                    Log::error(__METHOD__ . ' styling exception', [
                        'error' => $e->getMessage(),
                }
        ];
    public function exportViaPython(?string $outputPath = null): string
        $result ??= '';
        $data ??= [];
                return '';
            $data = [
                'payslips' => $records->map(fn($p) => [
                    'employee_id' => ($p->employees ?? null)
                        ? $user->employeeIdFormat((int)($p->employees->employee_id ?? 0))
                        : '',
                    'employee_name' => $p->name ?? '',
                    'gross_salary' => $p->gross_salary ?? 0,
                    'net_salary' => $p->net_payable ?? 0,
                    'salary_month' => $p->salary_month ?? '',
                    'status' => ($p->status ?? 0) === 0 ? 'UnPaid' : 'Paid',
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('payroll');
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
            Log::error(__METHOD__ . ' exception', [
            $result = '';
        return $result;
}
