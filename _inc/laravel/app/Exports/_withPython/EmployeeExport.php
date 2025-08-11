<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Http, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

class EmployeeExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const API_ENDPOINT = '/api/employee_export';
    private const TIMEOUT = 30;

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
                    'userId' => $user?->creatorId()
                ]);

            if (!$resp->ok()) {
                Log::warning(__METHOD__ . ' bad response', ['status' => $resp->status()]);
                return collect();
            }

            $data = $resp->json('data') ?? [];
            Log::info(__METHOD__ . ' succeeded', ['count' => count($data)]);
            return collect($data);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'Name',
            'Date of Birth',
            'Gender',
            'Phone Number',
            'Address',
            'Email ID',
            'Branch',
            'Department',
            'Designation',
            'Date of Join',
            'Account Holder Name',
            'Account Number',
            'Bank Name',
            'Bank Identifier Code',
            'Branch Location',
            'Salary'
        ];
    }
}
