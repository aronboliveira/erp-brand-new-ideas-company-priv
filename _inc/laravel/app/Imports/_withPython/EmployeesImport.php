<?php

namespace App\Imports;

use App\Models\{Employee};
use Illuminate\Support\Facades\{Auth, Http, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel, WithHeadingRow};

final class EmployeesImport implements ToModel, WithHeadingRow
{
    use Importable;

    private const ENDPOINT = '/api/employees_import';

    public function model(array $row): ?Employee
    {
        try {
            $data = [];
            foreach ((new Employee())->getFillable() as $field) {
                if (array_key_exists($field, $row)) {
                    $val = $row[$field];
                    $data[$field] = ($val !== '' && strtolower((string)$val) !== 'nan')
                        ? $val
                        : null;
                }
            }
            $data['created_by'] = Auth::id();

            $response = Http::timeout(10)
                ->post(self::ENDPOINT, ['data' => $data]);
            if (!$response->ok()) {
                Log::error(__METHOD__ . ' API error: ' . $response->status());
                return null;
            }

            return new Employee($response->json('data', []));
        } catch (\Throwable $e) {
            Log::error(sprintf(
                '%s failed: %s',
                __METHOD__,
                $e->getMessage()
            ));
            return null;
        }
    }
}
