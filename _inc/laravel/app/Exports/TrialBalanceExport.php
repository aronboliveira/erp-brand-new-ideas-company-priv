<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
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

    private const BODY_FILL_OPACITY       = '33FFFFFF';
    private const COLUMN_WIDTHS          = ['A' => 30, 'B' => 15, 'C' => 15, 'D' => 15];
    private const DATE_CELL              = 'A4';
    private const HEADER_BORDER_COLOR    = '000000';
    private const HEADER_FILL_COLOR      = 'FF1F1F2F';
    private const HORIZONTAL_BORDER_COLOR = '11000000';
    private const PRINT_CELL             = 'A3';
    private const START_CELL             = 'A6';
    private const TITLE_CELL             = 'A2';
    private const VERTICAL_BORDER_COLOR  = '808080';

    private array  $data;
    private string $companyName;
    private string $endDate;
    private string $startDate;

    public function __construct(array $data, string $startDate, string $endDate, string $companyName)
    {
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->companyName = $companyName;

        $totalDebit = 0;
        $totalCredit = 0;
        $formatted = [];

        foreach ($data as $typeName => $typeItems) {
            $formatted[] = ['Account Name' => '', 'Account No' => '', 'Debit' => '', 'Credit' => ''];
            $formatted[] = ['Account Name' => $typeName, 'Account No' => '', 'Debit' => '', 'Credit' => ''];
            foreach ($typeItems as $acct) {
                $debit = $acct['totalDebit'];
                $credit = $acct['totalCredit'];
                $totalDebit  += $debit;
                $totalCredit += $credit;
                $formatted[] = [
                    'Account Name' => $acct['name'],
                    'Account No'  => $acct['code'],
                    'Debit'       => $debit,
                    'Credit'      => $credit
                ];
            }
        }

        if ($formatted)
            $formatted[] = [
                'Account Name' => 'Total',
                'Account No' => '',
                'Debit' => $totalDebit,
                'Credit' => $totalCredit
            ];

        $this->data = $formatted;
    }

    public function array(): array
    {
        $request = Request::instance();
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) {
            Log::warning(
                __CLASS__ . '::array login redirect',
                ['request' => $request->all()]
            );
            return [];
        }
        $user = $userOrRedirect;

        Log::info(__CLASS__ . '::array started', ['user_id' => $user?->id]);
        try {
            return $this->data;
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::array failed', ['error' => $e->getMessage()]);
            return [];
        }
    }


    public function headings(): array
    {
        return ['Account Name', 'Account No', 'Debit', 'Credit'];
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
        foreach (array_keys(self::COLUMN_WIDTHS) as $col)
            $sheet->getStyle("{$col}" . self::START_CELL[1])->getFont()->setBold(true);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                Log::info(__CLASS__ . '::registerEvents', ['sheet' => 'trial balance']);
                $sheet = $event->sheet->getDelegate();
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
                for ($r = intval(self::START_CELL[1]) + 1; $r <= $last; ++$r)
                    $sheet->getStyle("A{$r}:D{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB(self::BODY_FILL_OPACITY);

                $sheet->getStyle("A2:D{$last}")
                    ->applyFromArray([
                        'borders' => [
                            'inside' => [
                                'horizontal' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color'       => ['argb' => self::HORIZONTAL_BORDER_COLOR]
                                ],
                                'vertical'   => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color'       => ['argb' => self::VERTICAL_BORDER_COLOR]
                                ]
                            ]
                        ]
                    ]);
                $sheet->getStyle("A2:D2")
                    ->getBorders()
                    ->getAllBorders()
                    ->getColor()
                    ->setARGB(self::HEADER_BORDER_COLOR);
            }
        ];
    }

    private static function columnLetter(string $cell): string
    {
        return preg_replace('/\d+$/', '', $cell);
    }
}
