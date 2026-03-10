<?php

namespace App\Imports;

use App\Models\ProductService;
use Illuminate\Support\Facades\{Auth, Http, Log};
use Maatwebsite\Excel\Concerns\{Importable, ToModel};
use function App\Http\Controllers\Helpers\defaultUndefinedException;

final class ProductServiceImport implements ToModel
{
    use Importable;

    private const ENDPOINT = '/api/python/product_service_import';

    /** @phpstan-return \App\Models\ProductService|null */
    public function model(array $row): ?ProductService
    {
        try {
            $payload = [
                'row'        => $row,
                'created_by' => Auth::id()
            ];
            $resp = Http::timeout(30)
                ->post(self::ENDPOINT, $payload)
                ->throw();

            $content = $resp->json();
            if (!isset($content['data']) || !is_array($content['data'])) {
                Log::error(
                    __CLASS__ . '::' . __FUNCTION__ . ' invalid response',
                    ['resp' => $content]
                );
                throw new \UnexpectedValueException('Invalid payload');
            }

            return new ProductService($content['data']);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed: ' . $e->getMessage());
            return null;
        }
    }
}
