<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Http, Log, URL};
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;

final class AccountStatementExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

    private const ENDPOINT    = '/api/account_statement_export';
    private const HEADER_RANGE = 'A1:D1';
    private const FREEZE_CELL = 'A2';
    private const HEADINGS    = [
        'Statement Id', 'Date', 'Amount', 'Description'
    ];

    private array $data = [];

    public function collection(): Collection|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        try {
            $response = Http::timeout(30)
                ->post(URL::to(self::ENDPOINT), [
                    'user_id' => $user?->id
                ]);
            $this->data = $response->json('data', []);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            $this->data = [];
        }

        Log::info(__METHOD__ . ' completed', ['count' => count($this->data)]);
        return collect($this->data);
    }

    public function headings(): array
    {
        return self::HEADINGS;
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
