<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Http, Log, URL};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class InvoiceExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const ENDPOINT = '/api/invoice_export';
    private const HEADERS = [
        'Invoice Id', 'Issue Date', 'Due Date', 'Send Date',
        'Category', 'Ref Number', 'Status'
    ];
    private array $data = [];

    public function collection()
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
                    'user_id' => $user?->id
                ]);
            $this->data = $resp->json('data', []);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            $this->data = [];
        }

        Log::info(__METHOD__ . ' completed', ['count' => count($this->data)]);
        return collect($this->data);
    }

    public function headings(): array
    {
        return self::HEADERS;
    }
}
