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
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SalesReportExport implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithCustomStartCell,
    WithColumnWidths,
    WithEvents
{
    use ChecksLogin;

    private const COLUMN_WIDTHS    = ['A' => 30, 'B' => 15, 'C' => 15, 'D' => 20];
    private const HEADER_ROW       = 6;
    private const MERGE_RANGES     = ['A2:D2', 'A3:D3', 'A4:D4'];
    private const START_CELL       = 'A6';
    private const HEADINGS_ITEM    = [
        'Item Name', 'Quantity Sold', 'Amount', 'Average Amount'
    ];
    private const HEADINGS_CUSTOMER = [
        'Customer Name', 'Invoice Count', 'Sales', 'Sales With Tax'
    ];

    private array  $data;
    private string $startDate;
    private string $endDate;
    private string $companyName;
    private string $reportName;

    public function __construct(
        array $data,
        string $startDate,
        string $endDate,
        string $companyName,
        string $reportName
    ) {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) {
            $this->data = [];
            return;
        }
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [
            'user_id' => $user?->id, 'report' => $reportName
        ]);

        $formatted = [];
        if ($reportName === 'Item') {
            foreach ($data as $v) {
                $formatted[] = [
                    'Item Name'      => $v['name'],
                    'Quantity Sold'  => $v['invoice_count'],
                    'Amount'         => $v['price'],
                    'Average Amount' => $v['avg_price']
                ];
            }
        } else {
            foreach ($data as $v) {
                $formatted[] = [
                    'Customer Name'  => $v['name'],
                    'Invoice Count'  => $v['invoice_count'],
                    'Sales'          => $v['price'],
                    'Sales With Tax' => $v['price'] + $v['total_tax']
                ];
            }
        }

        $this->data       = $formatted;
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->companyName = $companyName;
        $this->reportName = $reportName;
        Log::info(__METHOD__ . ' completed', ['rows' => count($formatted)]);
    }

    public function array(): array
    {
        return $this->data;
    }

    public function startCell(): string
    {
        return self::START_CELL;
    }

    public function columnWidths(): array
    {
        return self::COLUMN_WIDTHS;
    }

    public function styles(Worksheet $sheet): void
    {
        foreach (['A', 'B', 'C', 'D'] as $col)
            $sheet->getStyle("{$col}" . self::HEADER_ROW)
                ->getFont()->setBold(true);
    }

    public function headings(): array
    {
        return $this->reportName === 'Item'
            ? self::HEADINGS_ITEM
            : self::HEADINGS_CUSTOMER;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e): void {
                Log::info(__METHOD__ . ' styling started', [
                    'report' => $this->reportName
                ]);
                $sheet = $e->sheet->getDelegate();
                foreach (self::MERGE_RANGES as $rng)
                    $sheet->mergeCells($rng);
                $sheet->setCellValue(
                    'A2',
                    "Sales By {$this->reportName} - {$this->companyName}"
                )->getStyle('A2')->getFont()->setBold(true);
                $sheet->setCellValue(
                    'A3',
                    "Print Out Date : " . date('Y-m-d H:i')
                );
                $sheet->setCellValue(
                    'A4',
                    "Date : {$this->startDate} - {$this->endDate}"
                );
                $sheet->freezePane(self::START_CELL);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A2:D{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                Log::info(__METHOD__ . ' styling completed');
            }
        ];
    }
}
