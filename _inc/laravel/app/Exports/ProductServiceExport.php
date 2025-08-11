<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class ProductServiceExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const HEADINGS = [
        'ID', 'Name', 'SKU', 'Sale Price', 'Purchase Price',
        'Tax', 'Category', 'Unit', 'Type', 'Description'
    ];
    private const SELECT_FIELDS = [
        'product_services.id', 'product_services.name as item',
        'sku', 'sale_price', 'purchase_price', 'tax_id as tax',
        'product_service_categories.name as category',
        'product_service_units.name as unit', 'product_services.type',
        'description'
    ];

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        $query = ProductService::select(self::SELECT_FIELDS)
            ->leftJoin(
                'product_service_categories',
                'product_services.category_id',
                '=',
                'product_service_categories.id'
            )
            ->leftJoin(
                'product_service_units',
                'product_services.unit_id',
                '=',
                'product_service_units.id'
            )
            ->where(
                'product_services.created_by',
                $user?->creatorId()
            );
        $items = $query->get();
        Log::info(__METHOD__ . ' fetched', ['count' => $items->count()]);

        $rows = [];
        foreach ($items as $item) {
            $taxData = ProductService::taxData($item->tax);
            $rows[] = [
                $item->id,
                $item->item,
                $item->sku,
                $user?->priceFormat($item->sale_price),
                $user?->priceFormat($item->purchase_price),
                $taxData,
                $item->category,
                $item->unit,
                $item->type,
                $item->description
            ];
        }
        Log::info(__METHOD__ . ' completed', ['count' => count($rows)]);

        return Collection::make($rows);
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }
}
