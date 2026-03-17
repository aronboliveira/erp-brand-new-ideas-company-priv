<?php

namespace App\Imports;

use App\Traits\{ChecksLogin, DelegatesPythonImport};
use App\Models\{Customer};
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};

class CustomerImport implements ToModel
{
    use ChecksLogin, DelegatesPythonImport, Importable;

    private const PYTHON_IMPORTER = 'CustomerImport';

    private bool  $headerFound = false;
    private array $headerMap  = [];

    /** @phpstan-return \App\Models\Customer|null */
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

            return Customer::create($data);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed importing row: ' . $e->getMessage());
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
