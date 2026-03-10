<?php

namespace App\Exports;
use App\Models\{Branch, Department, Designation, Employee};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class EmployeeExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;
    private const HEADINGS = [
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
    private const PYTHON_EXPORTER    = 'EmployeeExport';
    private const REMOVED_ATTRIBUTES = [
        'created_at',
        'created_by',
        'documents',
        'employee_id',
        'id',
        'is_active',
        'password',
        'salary_type',
        'tax_payer_id',
        'updated_at',
        'user_id'
    public function collection(): Collection
    {
        $data ??= collect();
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
            Log::info(__METHOD__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $data = Employee::where('created_by', $user->creatorId())->get();
            $data->each(function ($emp) {
                foreach (self::REMOVED_ATTRIBUTES as $attr) {
                    unset($emp->{$attr});
                }
            });
            $data->each(fn($emp, $i) => $this->enrich($emp, $i, $data));
            Log::info(__METHOD__ . ' completed', [
                'count' => $data->count(),
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            $data = collect();
        }
        return $data;
    }
    public function headings(): array
        return self::HEADINGS;
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
                    $sheet->freezePane('A2');
                    Log::info(__METHOD__ . ' styling completed', [
                } catch (\Throwable $e) {
                    Log::error(__METHOD__ . ' styling exception', [
                        'error' => $e->getMessage(),
        ];
    private function enrich($emp, int $i, Collection &$data): void
            $data[$i]['branch'] = $emp->branch->name ?? '-';
            $data[$i]['department'] = $emp->department->name ?? '-';
            $data[$i]['designation'] = $emp->designation->name ?? '-';
            $data[$i]['salary'] = Employee::employeeSalary($emp->salary ?? 0);
            Log::warning(__METHOD__ . ' enrich failed', [
                'index' => $i,
    public function exportViaPython(?string $outputPath = null): string
        $result ??= '';
        $data ??= [];
                return '';
            $employees = Employee::where('created_by', $user->creatorId())->get();
            $data = [
                'employees' => $employees->map(fn($e) => [
                    'name' => $e->name ?? '',
                    'dob' => $e->dob ?? '',
                    'gender' => $e->gender ?? '',
                    'phone' => $e->phone ?? '',
                    'address' => $e->address ?? '',
                    'email' => $e->email ?? '',
                    'branch' => $e->branch->name ?? '-',
                    'department' => $e->department->name ?? '-',
                    'designation' => $e->designation->name ?? '-',
                    'date_of_join' => $e->date_of_join ?? '',
                    'account_holder_name' => $e->account_holder_name ?? '',
                    'account_number' => $e->account_number ?? '',
                    'bank_name' => $e->bank_name ?? '',
                    'bank_identifier_code' => $e->bank_identifier_code ?? '',
                    'branch_location' => $e->branch_location ?? '',
                    'salary' => Employee::employeeSalary($e->salary ?? 0),
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('employees');
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
            $result = '';
        return $result;
}
