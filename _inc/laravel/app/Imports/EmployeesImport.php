<?php

namespace App\Imports;

use App\Models\Employee;
use App\Traits\DelegatesPythonImport;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel, WithHeadingRow};

final class EmployeesImport implements ToModel, WithHeadingRow
{
    use DelegatesPythonImport;
    use Importable;

    private const PYTHON_IMPORTER = 'EmployeesImport';

    private const REQUIRED_FIELDS = ['name', 'email', 'employee_id'];

    public function model(array $row): ?Employee
    {
        try {
            foreach (self::REQUIRED_FIELDS as $field) {
                if (!array_key_exists($field, $row) || $row[$field] === null) {
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

    /**
     * Delegate the import processing to the Python importer.
     *
     * @param array $rows  Pre-parsed rows from the spreadsheet
     * @return array       Validated result from the Python process
     */
    public function importViaPython(array $rows): array
    {
        $result ??= [];
        try {
            $data = [
                'rows' => $rows,
                'required_fields' => self::REQUIRED_FIELDS,
                'created_by' => Auth::id(),
            ];
            $result = self::_executePythonImporter(
                self::PYTHON_IMPORTER,
                $data
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $result = ['status' => 'error', 'errors' => [$e->getMessage()], 'rows' => []];
        }
        return $result;
    }
}
