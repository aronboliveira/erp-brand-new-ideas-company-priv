<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel, WithHeadingRow};

final class EmployeesImport implements ToModel, WithHeadingRow
{
    use Importable;

    private const REQUIRED_FIELDS = ['name', 'email', 'employee_id'];

    public function model(array $row): ?Employee
    {
        try {
            foreach (self::REQUIRED_FIELDS as $field) {
                if (empty($row[$field] ?? null)) {
                    Log::warning(__METHOD__ . ' missing required field: ' . $field);
                    return null;
                }
            }

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

            return new Employee($data);
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
