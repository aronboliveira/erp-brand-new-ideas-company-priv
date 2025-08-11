<?php

namespace App\Exports;

use Illuminate\Support\Facades\{Http, Log};
use Maatwebsite\Excel\Concerns\{FromArray, WithHeadings};

final class BalanceSheetExport implements FromArray, WithHeadings
{
    private const API_ENDPOINT = '/api/balance_sheet_export';
    private const TIMEOUT     = 60;

    private array  $data;
    private array  $headings;

    public function __construct(array $rows, string $startDate, string $endDate, string $companyName)
    {
        Log::info(__CLASS__ . '::__construct invoking python export', [
            'start' => $startDate, 'end' => $endDate, 'company' => $companyName
        ]);

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->post(url(self::API_ENDPOINT), [
                    'data'        => $rows,
                    'start_date'  => $startDate,
                    'end_date'    => $endDate,
                    'company_name' => $companyName
                ]);

            if (!$response->ok()) {
                Log::warning(__METHOD__ . ' bad response', ['status' => $response->status()]);
                $this->data    = [];
                $this->headings = [];
                return;
            }

            $payload       = $response->json();
            $this->data    = $payload['data']     ?? [];
            $this->headings = $payload['headings'] ?? ['Account', 'Account No', 'Total'];

            Log::info(__CLASS__ . '::__construct success', ['rows' => count($this->data)]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            $this->data    = [];
            $this->headings = [];
        }
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
