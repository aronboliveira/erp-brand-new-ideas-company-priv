<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{
    FromArray,
    WithColumnWidths,
    WithCustomStartCell,
    WithEvents,
    WithHeadings,
    WithStyles
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class TrialBalanceExport implements FromArray, WithHeadings, WithStyles, WithCustomStartCell, WithColumnWidths, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;

    private const BODY_FILL_OPACITY       = '33FFFFFF';
    private const COLUMN_WIDTHS           = ['A' => 30, 'B' => 15, 'C' => 15, 'D' => 15];
    private const DATE_CELL               = 'A4';
    private const HEADER_BORDER_COLOR     = '000000';
    private const HEADER_FILL_COLOR       = 'FF1F1F2F';
    private const HEADINGS = [
        'Account Name',
        'Account No',
        'Debit',
        'Credit'
    ];
    private const HORIZONTAL_BORDER_COLOR = '11000000';
    private const PRINT_CELL              = 'A3';
    private const PYTHON_EXPORTER         = 'TrialBalanceExport';
    private const START_CELL              = 'A6';
    private const TITLE_CELL              = 'A2';
    private const VERTICAL_BORDER_COLOR   = '808080';

    private array  $data;
    private string $companyName;
    private string $endDate;
    private string $startDate;

    public function __construct(array $data, string $startDate, string $endDate, string $companyName)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyName = $companyName;
        $this->data = [];
        try {
            $totalDebit = 0;
            $totalCredit = 0;
            $formatted = [];
            foreach ($data as $typeName => $typeItems) {
                $formatted[] = [
                    'Account Name' => '',
                    'Account No' => '',
                    'Debit' => '',
                    'Credit' => ''
                ];
                $formatted[] = [
                    'Account Name' => $typeName,
                    'Account No' => '',
                    'Debit' => '',
                    'Credit' => ''
                ];
                foreach ($typeItems as $acct) {
                    $debit = $acct['totalDebit'] ?? 0;
                    $credit = $acct['totalCredit'] ?? 0;
                    $totalDebit += $debit;
                    $totalCredit += $credit;
                    $formatted[] = [
                        'Account Name' => $acct['name'] ?? '',
                        'Account No' => $acct['code'] ?? '',
                        'Debit' => $debit,
                        'Credit' => $credit
                    ];
                }
            }
            if (!empty($formatted)) {
                $formatted[] = [
                    'Account Name' => 'Total',
                    'Account No' => '',
                    'Debit' => $totalDebit,
                    'Credit' => $totalCredit
                ];
            }
            $this->data = $formatted;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $this->data = [];
        }
    }

    public function array(): array
    {
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' login redirect', [
                    'class' => static::class
                ]);
                return [];
            }
            Log::info(__METHOD__ . ' started', [
                'user_id' => $userOrRedirect->id ?? null,
                'class' => static::class
            ]);
            return $this->data;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'class' => static::class
            ]);
            return [];
        }
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function startCell(): string
    {
        return self::START_CELL;
    }

    public function columnWidths(): array
    {
        return self::COLUMN_WIDTHS;
    }

    public function styles(Worksheet $sheet)
    {
        try {
            foreach (array_keys(self::COLUMN_WIDTHS) as $col) {
                $sheet->getStyle("{$col}" . self::START_CELL[1])->getFont()->setBold(true);
            }
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' styles exception', [
                'error' => $e->getMessage(),
                'class' => static::class
            ]);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                try {
                    Log::info(__METHOD__ . ' styling started', [
                        'sheet' => 'trial balance',
                        'class' => static::class
                    ]);
                    $sheet = $event->sheet->getDelegate();
                    if (empty($sheet)) {
                        Log::warning(__METHOD__ . ' null sheet', [
                            'class' => static::class
                        ]);
                        return;
                    }
                    $sheet->mergeCells('A2:D2');
                    $sheet->mergeCells('A3:D3');
                    $sheet->mergeCells('A4:D4');
                    $sheet->setCellValue(self::TITLE_CELL, 'Trial Balance - ' . $this->companyName)
                        ->getStyle(self::TITLE_CELL)->getFont()->setBold(true);
                    $sheet->setCellValue(self::PRINT_CELL, 'Print Out Date : ' . date('Y-m-d H:i'));
                    $sheet->setCellValue(self::DATE_CELL, 'Date : ' . $this->startDate . ' - ' . $this->endDate);
                    $sheet->getStyle('A2:D' . $sheet->getHighestRow())
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle(self::TITLE_CELL . ':D2')->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB(self::HEADER_FILL_COLOR);
                    $sheet->freezePane(self::columnLetter(self::START_CELL) . (intval(self::START_CELL[1]) + 1));
                    $last = $sheet->getHighestRow();
                    for ($r = intval(self::START_CELL[1]) + 1; $r <= $last; ++$r) {
                        $sheet->getStyle("A{$r}:D{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB(self::BODY_FILL_OPACITY);
                    }
                    $sheet->getStyle("A2:D{$last}")
                        ->applyFromArray([
                            'borders' => [
                                'inside' => [
                                    'horizontal' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => self::HORIZONTAL_BORDER_COLOR]
                                    ],
                                    'vertical' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => self::VERTICAL_BORDER_COLOR]
                                    ]
                                ]
                            ]
                        ]);
                    $sheet->getStyle("A2:D2")
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

    private static function columnLetter(string $cell): string
    {
        return preg_replace('/\d+$/', '', $cell) ?? '';
    }

    public function exportViaPython(?string $outputPath = null): string
    {
        $result ??= '';
        $data ??= [];
        try {
            $data = [
                'accounts' => $this->data,
                'company_name' => $this->companyName,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('trial_balance');
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
