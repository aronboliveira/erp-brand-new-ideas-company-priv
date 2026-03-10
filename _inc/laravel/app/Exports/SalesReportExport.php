<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
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
    use DelegatesPythonExport;

    private const COLUMN_WIDTHS = ['A' => 30, 'B' => 15, 'C' => 15, 'D' => 20];
    private const HEADER_ROW    = 6;
    private const HEADINGS_CUSTOMER = [
        'Customer Name',
        'Invoice Count',
        'Sales',
        'Sales With Tax'
    ];
    private const HEADINGS_ITEM = [
        'Item Name',
        'Quantity Sold',
        'Amount',
        'Average Amount'
    ];
    private const MERGE_RANGES    = ['A2:D2', 'A3:D3', 'A4:D4'];
    private const PYTHON_EXPORTER = 'SalesReportExport';
    private const START_CELL      = 'A6';

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
        $this->data = [];
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyName = $companyName;
        $this->reportName = $reportName;
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return;
            }
            $user = $userOrRedirect;
            Log::info(__METHOD__ . ' started', [
                'user_id' => $user->id ?? null,
                'report' => $reportName,
                'class' => static::class
            ]);
            $formatted = [];
            if ($reportName === 'Item') {
                foreach ($data as $v) {
                    $formatted[] = [
                        'Item Name' => $v['name'] ?? '',
                        'Quantity Sold' => $v['invoice_count'] ?? 0,
                        'Amount' => $v['price'] ?? 0,
                        'Average Amount' => $v['avg_price'] ?? 0
                    ];
                }
            } else {
                foreach ($data as $v) {
                    $formatted[] = [
                        'Customer Name' => $v['name'] ?? '',
                        'Invoice Count' => $v['invoice_count'] ?? 0,
                        'Sales' => $v['price'] ?? 0,
                        'Sales With Tax' => ($v['price'] ?? 0) + ($v['total_tax'] ?? 0)
                    ];
                }
            }
            $this->data = $formatted;
            Log::info(__METHOD__ . ' completed', [
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
        try {
            foreach (['A', 'B', 'C', 'D'] as $col) {
                $sheet->getStyle("{$col}" . self::HEADER_ROW)
                    ->getFont()->setBold(true);
            }
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' styles exception', [
                'error' => $e->getMessage(),
                'class' => static::class
            ]);
        }
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
                try {
                    Log::info(__METHOD__ . ' styling started', [
                        'report' => $this->reportName,
                        'class' => static::class
                    ]);
                    $sheet = $e->sheet->getDelegate();
                    if (empty($sheet)) {
                        Log::warning(__METHOD__ . ' null sheet', [
                            'class' => static::class
                        ]);
                        return;
                    }
                    foreach (self::MERGE_RANGES as $rng) {
                        $sheet->mergeCells($rng);
                    }
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
        $data ??= [];
        try {
            $data = [
                'sales' => $this->data,
                'company_name' => $this->companyName,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'report_name' => $this->reportName,
                'headings' => $this->reportName === 'Item'
                    ? self::HEADINGS_ITEM
                    : self::HEADINGS_CUSTOMER,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('sales_report');
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
