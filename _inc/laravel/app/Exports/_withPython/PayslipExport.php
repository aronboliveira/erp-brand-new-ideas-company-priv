<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Http, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

class PayslipExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const API_ENDPOINT = '/api/payslip_export';
    private const TIMEOUT     = 30;

    private object $request;

    public function __construct(object $request)
    {
        $this->request = $request;
    }

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;

        Log::info(__METHOD__ . ' invoking python export', ['user_id' => $user?->id]);

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->post(url(self::API_ENDPOINT), [
                    'userId'      => $user?->creatorId(),
                    'filterMonth' => $this->request->filterMonth  ?? null,
                    'filterYear'  => $this->request->filterYear   ?? null,
                ]);

            if (!$response->ok()) {
                Log::warning(__METHOD__ . ' bad response', ['status' => $response->status()]);
                return collect();
            }

            $data = $response->json('data') ?? [];
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
            'EMP ID',
            'Name',
            'Salary',
            'Net Salary',
            'Status',
            'Account Holder Name',
            'Account Number',
            'Bank Name',
            'Bank Identifier Code',
            'Branch Location',
            'Tax Payer Id'
        ];
    }
}
