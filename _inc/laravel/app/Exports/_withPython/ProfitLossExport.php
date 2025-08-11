<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Http, Log};
use Maatwebsite\Excel\Concerns\{FromArray, WithHeadings};

final class ProfitLossExport implements FromArray, WithHeadings
{
    use ChecksLogin;

    private const API_ENDPOINT = '/api/profit_loss_export';
    private const TIMEOUT     = 60;

    private array $params;
    private array $data    = [];
    private array $headings = ['Account', 'Account No', 'Total'];

    public function __construct(
        array  $rows,
        string $startDate,
        string $endDate,
        string $companyName
    ) {
        $this->params = [
            'data'         => $rows,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'company_name' => $companyName
        ];
    }

    public function array(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) {
            Log::warning(__METHOD__ . ' permission denied');
            return [];
        }
        $user = $userOrRedirect;

        Log::info(__METHOD__ . ' invoking python export', ['user_id' => $user?->id]);

        try {
            $resp = Http::timeout(self::TIMEOUT)
                ->post(
                    url(self::API_ENDPOINT),
                    array_merge($this->params, ['user_id' => $user?->id])
                );

            if (!$resp->ok()) {
                Log::warning(__METHOD__ . ' bad response', ['status' => $resp->status()]);
                return [];
            }

            $payload       = $resp->json();
            $this->data    = $payload['data']     ?? [];
            $this->headings = $payload['headings'] ?? $this->headings;

            Log::info(__METHOD__ . ' success', ['rows' => count($this->data)]);
            return $this->data;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
