<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\StockReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class ProductStockExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const BODY_FILL_OPACITY      = '33FFFFFF';
    private const HEADER_BORDER_COLOR    = '000000';
    private const HEADER_FILL_COLOR      = 'FF1F1F2F';
    private const HORIZONTAL_BORDER_COLOR = '11000000';
    private const UNSET_FIELDS           = [
        'created_by',
        'type_id',
        'updated_at'
    ];
    private const VERTICAL_BORDER_COLOR  = '808080';

    public function collection(): Collection|RedirectResponse
    {
        $request = request();
        if (
            ($userOrRedirect = self::_checkLogin($request))
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;

        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', ['user_id' => $user?->id]);
        try {
            $stocks = StockReport::where(
                'created_by',
                $user?->id
            )->get();
            $rows = [];
            foreach ($stocks as $stock) {
                $productName = StockReport::products($stock->product_id);
                foreach (self::UNSET_FIELDS as $field) unset($stock->{$field});
                $row = $stock->toArray();
                $row['product_id'] = $productName;
                $rows[] = $row;
            }
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' prepared rows', ['count' => count($rows)]);
            return Collection::make($rows);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return Collection::make([]);
        }
    }

    public function headings(): array
    {
        return [
            'Stock Id',
            'Product Name',
            'Quantity',
            'Type',
            'Description',
            'Date'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet  = $event->sheet->getDelegate();
                $sheet->getStyle('1:1')->getFont()->setBold(true);
                $sheet->freezePane('A2');
                $sheet->getStyle('1:1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB(self::HEADER_FILL_COLOR);
                $highest = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();
                for ($r = 2; $r <= $highest; ++$r)
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB(self::BODY_FILL_OPACITY);
                $sheet->getStyle("A1:{$lastCol}{$highest}")
                    ->getBorders()->getInsideHorizontal()
                    ->getColor()->setARGB(self::HORIZONTAL_BORDER_COLOR);
                $sheet->getStyle("A1:{$lastCol}{$highest}")
                    ->getBorders()->getInsideVertical()
                    ->getColor()->setARGB(self::VERTICAL_BORDER_COLOR);
                $sheet->getStyle("A1:{$lastCol}1")
                    ->getBorders()->getAllBorders()
                    ->getColor()->setARGB(self::HEADER_BORDER_COLOR);
            }
        ];
    }
}
