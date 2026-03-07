<?php

namespace App\Imports;

use App\Traits\ChecksLogin;
use App\Models\Customer;
use Illuminate\Support\Facades\{Http, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};

class CustomerImport implements ToModel
{
    use Importable, ChecksLogin;

    private const ENDPOINT  = '/api/customer_import';
    private bool  $headerFound = false;
    private array $headerMap  = [];

    public function model(array $row): ?Customer
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return null;
        $user = $userOrRedirect;

        try {
            if (!$this->headerFound) {
                $this->headerMap  = $this->detectHeader($row);
                if (!$this->headerMap) {
                    Log::error(__CLASS__ . '::' . __FUNCTION__ . ' header row not found');
                    return null;
                }
                $this->headerFound = true;
                return null;
            }

            $data = [];
            foreach ((new Customer())->getFillable() as $field)
                if (isset($this->headerMap[$field])) {
                    $val = $row[$this->headerMap[$field]] ?? null;
                    $data[$field] = ($val !== '' && strtolower((string)$val) !== 'nan')
                        ? $val
                        : null;
                }
            $data['created_by'] = $user?->id;

            $response = Http::post(self::ENDPOINT, ['data' => $data]);
            if (!$response->ok()) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' API error: ' . $response->status());
                return null;
            }

            return new Customer($response->json('data', []));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return null;
        }
    }

    private function detectHeader(array $row): ?array
    {
        $map        = [];
        $emptyStreak = 0;
        $fillable   = (new Customer())->getFillable();

        foreach ($row as $i => $cell) {
            if ($cell === null || trim((string)$cell) === '') {
                $emptyStreak++;
                if ($emptyStreak >= 2) break;
                continue;
            }
            $emptyStreak = 0;
            $key        = strtolower(trim((string)$cell));
            if (in_array($key, $fillable, true)) $map[$key] = $i;
        }

        return count($map) >= 2 ? $map : null;
    }
}
