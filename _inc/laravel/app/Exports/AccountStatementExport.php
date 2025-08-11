<?php

namespace App\Exports;

use App\Models\Revenue;
use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;

final class AccountStatementExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const REMOVED_ATTRIBUTES = [
        'account_id', 'add_receipt', 'category_id', 'created_at',
        'created_by', 'customer_id', 'payment_method', 'reference',
        'updated_at'
    ];
    private const HEADER_RANGE      = 'A1:D1';
    private const FREEZE_CELL       = 'A2';

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        $data = Revenue::where(
            'created_by',
            $user?->id
        )->get();
        Log::info(__METHOD__ . ' fetched', ['count' => $data->count()]);

        $data->each(function ($statement) {
            foreach (self::REMOVED_ATTRIBUTES as $attr) unset($statement->{$attr});
        });
        Log::info(__METHOD__ . ' sanitized', ['count' => $data->count()]);

        return $data;
    }

    public function headings(): array
    {
        return ['Statement Id', 'Date', 'Amount', 'Description'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                Log::info(__METHOD__ . ' styling started');
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle(self::HEADER_RANGE)->applyFromArray([
                    'borders' => [
                        'horizontal' => [
                            'borderStyle' => 'thin',
                            'color' => ['argb' => '11333333']
                        ],
                        'vertical' => [
                            'borderStyle' => 'thin',
                            'color' => ['argb' => 'FF333333']
                        ]
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['argb' => '001122']
                    ],
                    'font' => ['bold' => true]
                ]);
                $sheet->freezePane(self::FREEZE_CELL);
                Log::info(__METHOD__ . ' styling completed');
            }
        ];
    }
}
