<?php

namespace App\Imports;

use App\Traits\ChecksLogin;
use App\Models\Vendor;
use Illuminate\Support\Facades\{Auth, Hash, Http, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};

class VendorImport implements ToModel
{
    use Importable, ChecksLogin;

    private const ENDPOINT    = '/api/vendor_import';
    private const FIELDS      = [
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
                        || strtolower((string)$cell) === 'nan';
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
            foreach (self::FIELDS as $offset => $field) {
                $value = $row[$this->headerStartIndex + $offset] ?? null;
                $data[$field] = $field === 'password' && $value
                    ? Hash::make($value)
                    : ($field === 'created_by'
                        ? Auth::id()
                        : $value
                    );
            }

            $response = Http::post(self::ENDPOINT, ['data' => $data]);
            if (!$response->ok()) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' API error: ' . $response->status());
                return null;
            }

            return new Vendor($response->json('data', []));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return null;
        }
    }
}
