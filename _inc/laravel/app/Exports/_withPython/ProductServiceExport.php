<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Http, Log, URL};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class ProductServiceExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const ENDPOINT   = '/api/product_service_export';
    private const HEADINGS   = [
        'ID', 'Name', 'SKU', 'Sale Price', 'Purchase Price',
        'Tax', 'Category', 'Unit', 'Type', 'Description'
    ];
    private array $rows = [];

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        try {
            $resp = Http::timeout(30)
                ->post(URL::to(self::ENDPOINT), [
                    'user_id' => $user?->id
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
}
