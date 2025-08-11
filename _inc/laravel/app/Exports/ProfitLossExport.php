<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
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
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ProfitLossExport implements FromArray, WithColumnWidths, WithCustomStartCell, WithEvents, WithHeadings, WithStyles
{
    use ChecksLogin;

    private const BLANK_ROW    = ['Account Name' => '', 'Account No' => '', 'Total' => ''];
    private const COLUMN_WIDTHS = ['A' => 30, 'B' => 15, 'C' => 15];
    private const DATA_START   = 'A5';
    private const HEADER_FILL  = '003366';
    private const BOLD_TYPES   = [
        'Income', 'Costs of Goods Sold', 'Expenses',
        'Total Income', 'Total Costs of Goods Sold', 'Total Expenses',
        'Gross Profit', 'Net Profit/Loss'
    ];

    private array  $data;
    private string $companyName;
    private string $endDate;
    private string $startDate;

    public function __construct(array $rows, string $startDate, string $endDate, string $companyName)
    {
        Log::info(__CLASS__ . '::__construct start', ['company' => $companyName, 'start' => $startDate, 'end' => $endDate]);

        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->companyName = $companyName;
        $this->data       = [];

        try {
            $formatted = [];
            $totalInc = 0;
            $totalCogs = 0;
            $totalExp = 0;

            foreach ($rows as $cat) {
                if (in_array($cat['Type'], ['Income', 'Costs of Goods Sold'], true)) {
                    $formatted[] = self::BLANK_ROW;
                    $formatted[] = ['Account Name' => $cat['Type'], 'Account No' => '', 'Total' => ''];

                    foreach ($cat['account'] as $acct) {
                        $amt = abs($acct['netAmount'] ?? 0);
                        if (!preg_match('/\btotal\b/i', $acct['account_name'])) {
                            $formatted[] = [
                                'Account Name' => '   ' . $acct['account_name'],
                                'Account No' => $acct['account_code'],
                                'Total' => $amt
                            ];
                        } else {
                            $formatted[] = [
                                'Account Name' => $acct['account_name'],
                                'Account No' => $acct['account_code'],
                                'Total' => $amt
                            ];
                        }
                        if ($acct['account_name'] === 'Total Income')             $totalInc = $amt;
                        if ($acct['account_name'] === 'Total Costs of Goods Sold') $totalCogs = $amt;
                    }
                }
            }

            $formatted[] = ['Account Name' => 'Gross Profit', 'Account No' => '', 'Total' => $totalInc - $totalCogs];

            foreach ($rows as $cat) {
                if ($cat['Type'] === 'Expenses') {
                    $formatted[] = self::BLANK_ROW;
                    $formatted[] = ['Account Name' => 'Expenses', 'Account No' => '', 'Total' => ''];

                    foreach ($cat['account'] as $acct) {
                        $amt = abs($acct['netAmount'] ?? 0);
                        if ($acct['account_name'] === 'Total Expenses') {
                            $totalExp = $amt;
                            $formatted[] = [
                                'Account Name' => $acct['account_name'],
                                'Account No' => $acct['account_code'],
                                'Total' => $amt
                            ];
                        } else {
                            $formatted[] = [
                                'Account Name' => '   ' . $acct['account_name'],
                                'Account No' => $acct['account_code'],
                                'Total' => $amt
                            ];
                        }
                    }

                    $formatted[] = [
                        'Account Name' => 'Net Profit/Loss',
                        'Account No' => '',
                        'Total' => ($totalInc - $totalCogs) - $totalExp
                    ];
                }
            }

            $this->data = $formatted;
            Log::info(__CLASS__ . '::__construct success', ['rows' => count($formatted)]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            $this->data = [];
        }
    }

    public function startCell(): string
    {
        return self::DATA_START;
    }

    public function columnWidths(): array
    {
        return self::COLUMN_WIDTHS;
    }

    public function array(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) {
            Log::warning(__METHOD__ . ' permission denied');
            return [];
        }
        Log::info(__METHOD__ . ' start', ['rows' => count($this->data)]);
        return $this->data;
    }

    public function headings(): array
    {
        return ['Account', 'Account No', 'Total'];
    }

    public function styles(Worksheet $sheet): void
    {
        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getStyle("{$col}5")->getFont()->setBold(true);
        }
    }

    public function registerEvents(): array
    {
        Log::info(__CLASS__ . '::registerEvents');
        return [
            BeforeWriting::class => fn () => Log::info(__METHOD__ . '::beforeWriting'),
            AfterSheet::class    => function (AfterSheet $e): void {
                Log::info(__METHOD__ . ' formatting start');
                $sheet = $e->sheet->getDelegate();

                foreach (['A1:F1', 'A2:F2', 'A3:F3'] as $rng) {
                    $sheet->mergeCells($rng);
                }

                $sheet->setCellValue('A1', "Profit & Loss - {$this->companyName}")
                    ->getStyle('A1')->getFont()->setBold(true);
                $sheet->setCellValue('A2', 'Print Out Date : ' . date('Y-m-d H:i'));
                $sheet->setCellValue('A3', "Date : {$this->startDate} - {$this->endDate}");

                foreach (['A2', 'A3'] as $cell) {
                    $sheet->getStyle($cell)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $sheet->getStyle(self::DATA_START)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB(self::HEADER_FILL);

                foreach (['A', 'B', 'C'] as $col) {
                    $sheet->getStyle("{$col}5")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                foreach ($this->data as $i => $row) {
                    $name = $row['Account Name'] ?? '';
                    if (in_array($name, self::BOLD_TYPES, true)) {
                        $idx = $i + 6;
                        $sheet->mergeCells(
                            in_array($name, ['Gross Profit', 'Net Profit/Loss'])
                                ? "A{$idx}:B{$idx}"
                                : "A{$idx}:A{$idx}"
                        );
                        $sheet->getStyle("A{$idx}:C{$idx}")
                            ->getFont()
                            ->setBold(true);
                    }
                }

                Log::info(__METHOD__ . ' formatting complete');
            }
        ];
    }
}
