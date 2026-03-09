<?php

namespace App\Imports;

use App\Models\Vendor;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonImport;
use Illuminate\Support\Facades\{Auth, Hash, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};

class VendorImport implements ToModel
{
    use ChecksLogin;
    use DelegatesPythonImport;
    use Importable;

    private const PYTHON_IMPORTER = 'VendorImport';

    private const FIELDS = [
        'vendor_id',
        'name',
        'email',
        'password',
        'contact',
        'avatar',
        'is_active',
        'created_by',
        'email_verified_at',
        'billing_name',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_phone',
        'billing_zip',
        'billing_address',
        'shipping_name',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_phone',
        'shipping_zip',
        'shipping_address',
    ];

    private bool $headerFound = false;
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
            foreach (self::FIELDS as $offset => $field) {
                $value = $row[$this->headerStartIndex + $offset] ?? null;
                $data[$field] = $field === 'password' && $value
                    ? Hash::make($value)
                    : $value;
            }

            return Vendor::create($data);
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
