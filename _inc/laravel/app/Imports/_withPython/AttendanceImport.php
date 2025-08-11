<?php

namespace App\Imports;

use App\Traits\ChecksLogin;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\{Auth, Http, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};

class AttendanceImport implements ToModel
{
    use Importable, ChecksLogin;

    private const ENDPOINT = '/api/attendance_import';
    private const FIELDS  = [
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
                            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' header not found');
                            return null;
                        }
                        continue;
                    }
                    $this->headerStartIndex = $i;
                    $this->headerFound     = true;
                    return null;
                }
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' header incomplete');
                return null;
            }

            $data = [];
            foreach (self::FIELDS as $offset => $field)
                $data[$field] = $row[$this->headerStartIndex + $offset] ?? null;
            $data['created_by'] = Auth::id();

            $response = Http::post(self::ENDPOINT, ['data' => $data]);
            if (!$response->ok()) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' API error: ' . $response->status());
                return null;
            }

            return new EmployeeAttendance($response->json('data', []));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return null;
        }
    }
}
