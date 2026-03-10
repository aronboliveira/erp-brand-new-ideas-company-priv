<?php

namespace App\Exports;
use App\Models\ProductService;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};
final class ProductServiceExport implements FromCollection, WithHeadings
{
    use ChecksLogin;
    use DelegatesPythonExport;
    private const HEADINGS = [
        'ID',
        'Name',
        'SKU',
        'Sale Price',
        'Purchase Price',
        'Tax',
        'Category',
        'Unit',
        'Type',
        'Description'
    ];
    private const PYTHON_EXPORTER = 'ProductServiceExport';
    private const SELECT_FIELDS = [
        'product_services.description',
        'product_service_categories.name as category',
        'product_service_units.name as unit',
        'product_services.id',
        'product_services.name as item',
        'product_services.type',
        'purchase_price',
        'sale_price',
        'sku',
        'tax_id as tax'
    public function collection(): Collection
    {
        $rows ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return collect();
            }
            $user = $userOrRedirect;
            if (empty($user)) {
                Log::error(__METHOD__ . ' null user', ['class' => static::class]);
            Log::info(__METHOD__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $query = ProductService::select(self::SELECT_FIELDS)
                ->leftJoin(
                    'product_service_categories',
                    'product_services.category_id',
                    '=',
                    'product_service_categories.id'
                )
                    'product_service_units',
                    'product_services.unit_id',
                    'product_service_units.id'
                ->where('product_services.created_by', $user->creatorId());
            $items = $query->get();
            Log::info(__METHOD__ . ' fetched', [
                'count' => $items->count(),
            $rowsArray = [];
            foreach ($items as $item) {
                $taxData = ProductService::taxData((string)($item->tax ?? ''));
                $rowsArray[] = [
                    $item->id ?? 0,
                    $item->item ?? '',
                    $item->sku ?? '',
                    $user->priceFormat($item->sale_price ?? 0),
                    $user->priceFormat($item->purchase_price ?? 0),
                    $taxData ?? '',
                    $item->category ?? '',
                    $item->unit ?? '',
                    $item->type ?? '',
                    $item->description ?? ''
                ];
            $rows = Collection::make($rowsArray);
            Log::info(__METHOD__ . ' completed', [
                'count' => $rows->count(),
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            $rows = collect();
        }
        return $rows;
    }
    public function headings(): array
        return self::HEADINGS;
    public function exportViaPython(?string $outputPath = null): string
        $result ??= '';
        $data ??= [];
                return '';
            $data = [
                'products' => $items->map(fn($item) => [
                    'id' => $item->id ?? 0,
                    'name' => $item->item ?? '',
                    'sku' => $item->sku ?? '',
                    'sale_price' => $item->sale_price ?? 0,
                    'purchase_price' => $item->purchase_price ?? 0,
                    'tax' => ProductService::taxData((string)($item->tax ?? '')) ?? '',
                    'category' => $item->category ?? '',
                    'unit' => $item->unit ?? '',
                    'type' => $item->type ?? '',
                    'description' => $item->description ?? '',
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('products_services');
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
            $result = '';
        return $result;
}
