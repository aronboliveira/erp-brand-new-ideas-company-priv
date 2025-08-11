<?php

namespace App\Exports;

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
    private const BOLD_LABELS    = ['Liabilities & Equity', 'Total Assets', 'Total Equity', 'Total Liabilities & Equity'];
    private const COLUMN_WIDTHS  = ['A' => 30, 'B' => 15, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 15];
    private const DATA_COLUMNS   = ['A', 'B', 'C'];
    private const HEADER_FILL_RGB = '003366';
    private const MERGE_RANGES   = ['A1:F1', 'A2:F2', 'A3:F3'];
    private const START_CELL     = 'A5';

    private array  $data;
    private string $companyName;
    private string $endDate;
    private string $startDate;

    public function __construct(array $rows, string $startDate, string $endDate, string $companyName)
    {
        Log::info(__CLASS__ . '::__construct start', ['company' => $companyName, 'start' => $startDate, 'end' => $endDate]);
        try {
            $formatted  = [];
            $grandTotal = 0;
            $liEqStarted = false;

            foreach ($rows as $category => $subs) {
                $catTotal = 0;
                $liSet   = false;
                $asSet   = false;

                // locate category header
                foreach ($subs as $sub) {
                    foreach ($sub['account'] as $acct) {
                        if ($acct['netAmount'] !== null) {
                            if (in_array($category, ['Liabilities', 'Equity'], true)) {
                                if (!$liEqStarted) {
                                    $formatted[] = ['Account Name' => 'Liabilities & Equity', 'Account No' => '', 'Total' => ''];
                                    $liEqStarted = true;
                                }
                                if (!$liSet) {
                                    $formatted[] = ['Account Name' => '  ' . $category, 'Account No' => '', 'Total' => ''];
                                    $liSet = true;
                                }
                            } elseif (!$asSet) {
                                $formatted[] = ['Account Name' => $category, 'Account No' => '', 'Total' => ''];
                                $asSet = true;
                            }
                            break 2;
                        }
                    }
                }

                // process subcategories
                foreach ($subs as $sub) {
                    $subTotal = 0;
                    foreach ($sub['account'] as $acct) {
                        if ($acct['netAmount'] !== null) {
                            $formatted[] = ['Account Name' => '    ' . $sub['subType'], 'Account No' => '', 'Total' => ''];
                            break;
                        }
                    }
                    foreach ($sub['account'] as $acct) {
                        if ($acct['netAmount'] !== null) {
                            $formatted[] = [
                                'Account Name' => '       ' . $acct['account_name'],
                                'Account No' => $acct['account_no'],
                                'Total' => $acct['netAmount']
                            ];
                            $subTotal += $acct['netAmount'];
                        }
                    }
                    if ($subTotal !== 0) {
                        $formatted[] = [
                            'Account Name' => '    Total ' . $sub['subType'],
                            'Account No' => '', 'Total' => $subTotal
                        ];
                    }
                    $catTotal += $subTotal;
                }

                // category total
                if ($catTotal !== 0) {
                    if (in_array($category, ['Liabilities', 'Equity'], true)) {
                        $formatted[] = ['Account Name' => '  Total ' . $category, 'Account No' => '', 'Total' => $catTotal];
                        $grandTotal += $catTotal;
                    } else {
                        $formatted[] = ['Account Name' => 'Total ' . $category, 'Account No' => '', 'Total' => $catTotal];
                    }
                }
            }

            // grand total row
            $formatted[] = ['Account Name' => 'Total Liabilities & Equity', 'Account No' => '', 'Total' => $grandTotal];

            $this->data       = $formatted;
            $this->startDate  = $startDate;
            $this->endDate    = $endDate;
            $this->companyName = $companyName;

            Log::info(__CLASS__ . '::__construct success', ['rows' => count($formatted)]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
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
        return ['Account', 'Account No', 'Total'];
    }

    public function styles(Worksheet $sheet): void
    {
        foreach (self::DATA_COLUMNS as $col) {
            $sheet->getStyle("{$col}5")->getFont()->setBold(true);
        }
    }

    public function registerEvents(): array
    {
        Log::info(__CLASS__ . '::registerEvents start');
        return [
            BeforeWriting::class => fn () => Log::info(__CLASS__ . '::beforeWriting'),
            AfterSheet::class    => function (AfterSheet $e): void {
                Log::info(__METHOD__ . ' formatting start');
                $sheet = $e->sheet->getDelegate();

                // merges & titles
                foreach (self::MERGE_RANGES as $range) $sheet->mergeCells($range);
                $sheet->setCellValue('A1', "Balance Sheet - {$this->companyName}")
                    ->getStyle('A1')->getFont()->setBold(true);
                $sheet->setCellValue('A2', "Print Out Date : " . date('Y-m-d H:i'));
                $sheet->setCellValue('A3', "Date : {$this->startDate} - {$this->endDate}");
                $sheet->freezePane('A6');

                // header styling
                $sheet->getStyle(self::START_CELL)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB(self::HEADER_FILL_RGB);

                // borders on data columns
                foreach (self::DATA_COLUMNS as $col) {
                    $sheet->getStyle("{$col}5")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // bold certain labels
                foreach ($this->data as $i => $row) {
                    if (in_array($row['Account Name'] ?? '', self::BOLD_LABELS, true)) {
                        array_map(
                            fn ($c) => $sheet
                                ->getStyle("{$c}" . ($i + 6))
                                ->getFont()
                                ->setBold(true),
                            self::DATA_COLUMNS
                        );
                    }
                }

                Log::info(__METHOD__ . ' formatting complete');
            }
        ];
    }
}
