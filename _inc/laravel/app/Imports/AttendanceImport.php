<?php

namespace App\Imports;

use App\Models\EmployeeAttendance;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonImport;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};

class AttendanceImport implements ToModel
{
    use ChecksLogin;
    use DelegatesPythonImport;
    use Importable;

    private const PYTHON_IMPORTER = 'AttendanceImport';

    private const FIELDS = [
        'employee_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
    ];

    private bool $headerFound     = false;
    private int  $headerStartIndex = 0;

    public function model(array $row): ?\Illuminate\Database\Eloquent\Model
    {
        try {
            if (!$this->headerFound) {
                $emptyStreak = 0;
                foreach ($row as $i => $cell) {
                    $isEmpty = $cell === null
                        || $cell === ''
                        || strtolower((string) $cell) === 'nan';
                    if ($isEmpty) {
                        $emptyStreak++;
                        if ($emptyStreak >= 2) {
                            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' header row not found');
                            return null;
                        }
                        continue;
                    }
                    $this->headerStartIndex = $i;
                    $this->headerFound     = true;
                    return null;
                }
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' header row incomplete');
                return null;
            }

            $data = [];
            foreach (self::FIELDS as $offset => $field)
                $data[$field] = $row[$this->headerStartIndex + $offset] ?? null;
            $data['created_by'] = Auth::id();

            return EmployeeAttendance::create($data);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed importing row: ' . $e->getMessage());
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
                'fields' => self::FIELDS,
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
