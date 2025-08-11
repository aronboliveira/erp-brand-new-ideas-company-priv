<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Http, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class VendorExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const API_ENDPOINT = '/api/vendor_export';
    private const TIMEOUT     = 30;

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;

        Log::info(__METHOD__ . ' invoking python export', ['user_id' => $user?->id]);

        try {
            $resp = Http::timeout(self::TIMEOUT)
                ->get(url(self::API_ENDPOINT), [
                    'user_id' => $user?->creatorId()
                ]);

            if (!$resp->ok()) {
                Log::warning(__METHOD__ . ' bad response', ['status' => $resp->status()]);
                return collect();
            }

            $data = $resp->json('data') ?? [];
            Log::info(__METHOD__ . ' success', ['count' => count($data)]);
            return collect($data);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Contact',
            'Billing Name',
            'Billing Country',
            'Billing State',
            'Billing City',
            'Billing Phone',
            'Billing Zip',
            'Billing Address',
            'Shipping Name',
            'Shipping Country',
            'Shipping State',
            'Shipping City',
            'Shipping Phone',
            'Shipping Zip',
            'Shipping Address',
            'Balance'
        ];
    }
}
