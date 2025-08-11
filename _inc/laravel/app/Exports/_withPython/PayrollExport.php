<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const HEADINGS = ['Employee Id', 'Status', 'Employee Name', 'Salary', 'Net Salary', 'Month'];

    public function collection(): Collection
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return collect();
        $user = $userOrRedirect;
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', ['user_id' => $user?->id]);

        try {
            Log::info(__CLASS__ . '::requesting python endpoint', ['user_id' => $user?->id]);
            $response = Http::timeout(30)->get(URL::to('/api/payroll_export'));
            if ($response->failed()) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['status' => $response->status()]);
                return collect();
            }
            $data = $response->json() ?? [];
            Log::info(__CLASS__ . '::received data', ['count' => count($data)]);
            return collect($data);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' exception', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }
}
