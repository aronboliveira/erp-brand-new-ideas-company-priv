<?php

namespace App\Imports;

use App\Models\ProductService;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};
use function App\Http\Controllers\Helpers\defaultUndefinedException;

final class ProductServiceImport implements ToModel
{
    use Importable;

    private ?int $headerStartIndex = null;
    private ?array $headers       = null;

    /** @phpstan-return \App\Models\ProductService|null */
    public function model(array $row): ?ProductService
    {
        try {
            if ($this->headers === null) {
                [$this->headerStartIndex, $this->headers] = $this->locateHeaderRow($row);
                // keep only fillable fields
                $this->headers = array_values(
                    array_intersect(
                        $this->headers,
                        (new ProductService())->getFillable()
                    )
                );
                return null;
            }

            $data = [];
            foreach ($this->headers as $i => $field)
                $data[$field] = $row[$this->headerStartIndex + $i] ?? null;
            $data['created_by'] = Auth::id();

            return ProductService::create($data);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return null;
        }
    }

    private function locateHeaderRow(array $row): array
    {
        $start = null;
        foreach ($row as $i => $cell) {
            if (
                $cell !== null &&
                $cell !== '' &&
                !(is_float($cell) && is_nan($cell)) &&
                isset($row[$i + 1]) &&
                $row[$i + 1] !== null &&
                $row[$i + 1] !== '' &&
                !(is_float($row[$i + 1]) && is_nan($row[$i + 1]))
            ) {
                $start = $i;
                break;
            }
        }
        if ($start === null) throw new \RuntimeException('Header row not detected');

        $cols = [];
        $empty = 0;
        for ($c = $start; isset($row[$c]); $c++) {
            $val = $row[$c];
            if ($val === null || $val === '' || (is_float($val) && is_nan($val))) {
                if (++$empty === 2) break;
                continue;
            }
            $cols[] = $this->toCamelCase((string) $val);
            $empty = 0;
        }

        return [$start, $cols];
    }

    private function toCamelCase(string $text): string
    {
        $clean = preg_replace('/[^A-Za-z0-9 ]+/', '', $text);
        $parts = preg_split('/\s+/', $clean);
        $first = strtolower(array_shift($parts));
        return $first . implode('', array_map('ucfirst', $parts));
    }
}
