<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Http, Log, URL};
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class BillExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const BODY_FILL_OPACITY  = '33FFFFFF';
    private const BORDER_COLOR       = '808080';
    private const ENDPOINT          = '/api/bill_export';
    private const EXPENSE_TYPE       = 'expense';
    private const HEADER_BORDER_COLOR = '000000';
    private const HEADER_FILL_COLOR  = 'FF1F1F2F';
    private const FREEZE_CELL        = 'A2';
    private const HEADER_ROW         = '1:1';
    private const HEADINGS = [
        'Bill No', 'Bill Date', 'Due Date',
        'Order No', 'Status', 'Send Date', 'Category'
    ];

    private array $rows = [];

    public function collection(): Collection|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        try {
            $resp = Http::timeout(30)
                ->post(URL::to(self::ENDPOINT), [
                    'user_id' => $user?->id,
                    'type'   => self::EXPENSE_TYPE
                ]);
            $this->rows = $resp->json('data', []);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            $this->rows = [];
        }

        Log::info(__METHOD__ . ' completed', ['count' => count($this->rows)]);
        return collect($this->rows);
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e): void {
                Log::info(__METHOD__ . ' styling started');
                $sheet  = $e->sheet->getDelegate();
                $sheet->getStyle(self::HEADER_ROW)->getFont()->setBold(true);
                $sheet->freezePane(self::FREEZE_CELL);
                $sheet->getStyle(self::HEADER_ROW)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB(self::HEADER_FILL_COLOR);

                $highest = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();
                for ($r = 2; $r <= $highest; ++$r) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB(self::BODY_FILL_OPACITY);
                }

                $allRange   = "A1:{$lastCol}{$highest}";
                $sheet->getStyle($allRange)
                    ->getBorders()
                    ->getAllBorders()
                    ->getColor()
                    ->setARGB(self::BORDER_COLOR);

                $headerRange = "A1:{$lastCol}1";
                $sheet->getStyle($headerRange)
                    ->getBorders()
                    ->getAllBorders()
                    ->getColor()
                    ->setARGB(self::HEADER_BORDER_COLOR);
                Log::info(__METHOD__ . ' styling completed');
            }
        ];
    }
}
