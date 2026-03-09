<?php

namespace App\Exports;

use App\Traits\DelegatesPythonExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{
    FromArray,
    WithColumnWidths,
    WithCustomStartCell,
    WithEvents,
    WithHeadings,
    WithStyles
};
use Maatwebsite\Excel\Events\{AfterSheet, BeforeWriting};
use PhpOffice\PhpSpreadsheet\Style\{Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class BalanceSheetExport implements FromArray, WithColumnWidths, WithCustomStartCell, WithEvents, WithHeadings, WithStyles
{
    use DelegatesPythonExport;

    private const BOLD_LABELS = [
        'Liabilities & Equity',
        'Total Assets',
        'Total Equity',
        'Total Liabilities & Equity'
    ];
    private const COLUMN_WIDTHS = [
        'A' => 30,
        'B' => 15,
        'C' => 15,
        'D' => 15,
        'E' => 15,
        'F' => 15
    ];
    private const DATA_COLUMNS    = ['A', 'B', 'C'];
    private const HEADER_FILL_RGB = '003366';
    private const HEADINGS        = ['Account', 'Account No', 'Total'];
    private const MERGE_RANGES    = ['A1:F1', 'A2:F2', 'A3:F3'];
    private const PYTHON_EXPORTER = 'BalanceSheetExport';
    private const START_CELL      = 'A5';

    private array  $data;
    private string $companyName;
    private string $endDate;
    private string $startDate;

    public function __construct(array $rows, string $startDate, string $endDate, string $companyName)
    {
        $this->data = [];
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyName = $companyName;
        Log::info(__CLASS__ . '::__construct start', [
            'company' => $companyName,
            'start' => $startDate,
            'end' => $endDate,
            'class' => static::class
        ]);
        try {
            $formatted = [];
            $grandTotal = 0;
            $liEqStarted = false;
            foreach ($rows as $category => $subs) {
                $catTotal = 0;
                $liSet = false;
                $asSet = false;
                foreach ($subs as $sub) {
                    foreach ($sub['account'] ?? [] as $acct) {
                        if (($acct['netAmount'] ?? null) !== null) {
                            if (in_array($category, ['Liabilities', 'Equity'], true)) {
                                if (!$liEqStarted) {
                                    $formatted[] = [
                                        'Account Name' => 'Liabilities & Equity',
                                        'Account No' => '',
                                        'Total' => ''
                                    ];
                                    $liEqStarted = true;
                                }
                                if (!$liSet) {
                                    $formatted[] = [
                                        'Account Name' => '  ' . $category,
                                        'Account No' => '',
                                        'Total' => ''
                                    ];
                                    $liSet = true;
                                }
                            } elseif (!$asSet) {
                                $formatted[] = [
                                    'Account Name' => $category,
                                    'Account No' => '',
                                    'Total' => ''
                                ];
                                $asSet = true;
                            }
                            break 2;
                        }
                    }
                }
                foreach ($subs as $sub) {
                    $subTotal = 0;
                    foreach ($sub['account'] ?? [] as $acct) {
                        if (($acct['netAmount'] ?? null) !== null) {
                            $formatted[] = [
                                'Account Name' => '    ' . ($sub['subType'] ?? ''),
                                'Account No' => '',
                                'Total' => ''
                            ];
                            break;
                        }
                    }
                    foreach ($sub['account'] ?? [] as $acct) {
                        if (($acct['netAmount'] ?? null) !== null) {
                            $formatted[] = [
                                'Account Name' => '       ' . ($acct['account_name'] ?? ''),
                                'Account No' => $acct['account_no'] ?? '',
                                'Total' => $acct['netAmount'] ?? 0
                            ];
                            $subTotal += $acct['netAmount'] ?? 0;
                        }
                    }
                    if ($subTotal !== 0) {
                        $formatted[] = [
                            'Account Name' => '    Total ' . ($sub['subType'] ?? ''),
                            'Account No' => '',
                            'Total' => $subTotal
                        ];
                    }
                    $catTotal += $subTotal;
                }
                if ($catTotal !== 0) {
                    if (in_array($category, ['Liabilities', 'Equity'], true)) {
                        $formatted[] = [
                            'Account Name' => '  Total ' . $category,
                            'Account No' => '',
                            'Total' => $catTotal
                        ];
                        $grandTotal += $catTotal;
                    } else {
                        $formatted[] = [
                            'Account Name' => 'Total ' . $category,
                            'Account No' => '',
                            'Total' => $catTotal
                        ];
                    }
                }
            }
            $formatted[] = [
                'Account Name' => 'Total Liabilities & Equity',
                'Account No' => '',
                'Total' => $grandTotal
            ];
            $this->data = $formatted;
            Log::info(__CLASS__ . '::__construct success', [
                'rows' => count($formatted),
                'class' => static::class
            ]);
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
        return $this->data;
    }

    public function columnWidths(): array
    {
        return self::COLUMN_WIDTHS;
    }

    public function startCell(): string
    {
        return self::START_CELL;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function styles(Worksheet $sheet): void
    {
        try {
            foreach (self::DATA_COLUMNS as $col) {
                $sheet->getStyle("{$col}5")->getFont()->setBold(true);
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
        Log::info(__CLASS__ . '::registerEvents start', ['class' => static::class]);
        return [
            BeforeWriting::class => fn() => Log::info(__CLASS__ . '::beforeWriting', [
                'class' => static::class
            ]),
            AfterSheet::class => function (AfterSheet $e): void {
                try {
                    Log::info(__METHOD__ . ' formatting start', [
                        'class' => static::class
                    ]);
                    $sheet = $e->sheet->getDelegate();
                    if (empty($sheet)) {
                        Log::warning(__METHOD__ . ' null sheet', [
                            'class' => static::class
                        ]);
                        return;
                    }
                    foreach (self::MERGE_RANGES as $range) {
                        $sheet->mergeCells($range);
                    }
                    $sheet->setCellValue('A1', "Balance Sheet - {$this->companyName}")
                        ->getStyle('A1')->getFont()->setBold(true);
                    $sheet->setCellValue('A2', "Print Out Date : " . date('Y-m-d H:i'));
                    $sheet->setCellValue('A3', "Date : {$this->startDate} - {$this->endDate}");
                    $sheet->freezePane('A6');
                    $sheet->getStyle(self::START_CELL)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB(self::HEADER_FILL_RGB);
                    foreach (self::DATA_COLUMNS as $col) {
                        $sheet->getStyle("{$col}5")
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN);
                    }
                    foreach ($this->data as $i => $row) {
                        if (in_array($row['Account Name'] ?? '', self::BOLD_LABELS, true)) {
                            array_map(
                                fn($c) => $sheet
                                    ->getStyle("{$c}" . ($i + 6))
                                    ->getFont()
                                    ->setBold(true),
                                self::DATA_COLUMNS
                            );
                        }
                    }
                    Log::info(__METHOD__ . ' formatting complete', [
                        'class' => static::class
                    ]);
                } catch (\Throwable $e) {
                    Log::error(__METHOD__ . ' formatting exception', [
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
            $data = [
                'rows' => $this->data,
                'company_name' => $this->companyName,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'headings' => self::HEADINGS,
                'bold_labels' => self::BOLD_LABELS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('balance_sheet');
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
