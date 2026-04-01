<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    SettingsConstants as SC,
    UsersConstants as UC,
};
use App\Models\{
    BankAccount,
    Bill,
    Customer,
    Product,
    ProductService,
    Project,
    StockReport,
    Tax,
    Utility,
    Vendor,
    WarehouseProduct,
};
use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, DB, Log};

/**
 * FinanceBillingService — extracted from Utility.php
 *
 * Price formatting, tax computations, bill item stats,
 * customer/vendor balance, warehouse stock operations.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class FinanceBillingService
{
    use ChecksLogin;

    // ── Static caches (moved from Utility — proxy via Utility::$prop) ──
    // These reference Utility's static caches to avoid duplication.

    // ─────────────────────────────────────────────────────────
    //  Price / number formatting
    // ─────────────────────────────────────────────────────────

    public static function priceFormat(array $settings, float|int $price): string
    {
        $symbol  = $settings[SC::CR_SB] ?? '';
        $position = $settings[SC::CR_SB_P] ?? 'pre';
        $decimals = (int) ($settings['decimal_number'] ?? 2);
        $formatted = number_format($price, $decimals);
        return ($position === 'pre' ? $symbol : '')
            . $formatted
            . ($position === 'post' ? $symbol : '');
    }

    public static function projectCurrencyFormat(string|int $projectId, float|int $amount, bool $decimal = false): ?string
    {
        $project = Project::find($projectId);
        if (!$project) {
            $settings = Utility::settings();
            $symbol = $settings[SC::CR_SB] ?? '';
            $position = $settings[SC::CR_SB_P] ?? 'pre';
            $dec = $decimal ? (int) Utility::getValByName('decimal_number') : (int) Utility::getValByName('decimal_number');
            $formatted = number_format($amount, $dec);
            return ($position === 'pre' ? $symbol : '') . $formatted . ($position === 'post' ? $symbol : '');
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────
    //  Bill item stats
    // ─────────────────────────────────────────────────────────

    /**
     * Compute aggregated item stats for a bill's PDF / template view.
     *
     * @param  Bill               $bill     Bill with items eagerly loaded.
     * @param  array<string,mixed> $settings Creator-level settings.
     * @return array{0: list<object>, 1: array<string,float>, 2: float, 3: float, 4: float, 5: float}
     */
    public static function billItemStats(Bill $bill, array $settings): array
    {
        $totalTaxPrice = 0.0;
        $totalQuantity = 0.0;
        $totalRate     = 0.0;
        $totalDiscount = 0.0;
        $taxesData     = [];
        $items         = [];

        foreach ($bill->items as $it) {
            $name     = $it->productService?->name ?? '';
            $qty      = (float) ($it->quantity ?? 0);
            $price    = (float) ($it->price ?? 0);
            $discount = (float) ($it->discount ?? 0);
            $taxRate  = (string) ($it->tax ?? '');

            $totalQuantity += $qty;
            $totalRate     += $price;
            $totalDiscount += $discount;

            $itemTaxes = [];
            if ($taxRate !== '') {
                foreach (self::tax($taxRate) as $tax) {
                    $taxPrice       = self::taxRate((float) ($tax->rate ?? 0), $price, $qty, $discount);
                    $totalTaxPrice += $taxPrice;
                    $itemTaxes[]    = [
                        'name'      => $tax->name ?? '',
                        'rate'      => ($tax->rate ?? 0) . '%',
                        'price'     => self::priceFormat($settings, $taxPrice),
                        'tax_price' => $taxPrice,
                    ];
                    $taxesData[$tax->name ?? ''] = ($taxesData[$tax->name ?? ''] ?? 0) + $taxPrice;
                }
            }

            $items[] = (object) [
                'name'        => $name,
                'quantity'    => $qty,
                'tax'         => $taxRate,
                'discount'    => $discount,
                'price'       => $price,
                'unit'        => $it->productService?->unit_id ?? 0,
                'description' => $it->description ?? '',
                'itemTax'     => $itemTaxes,
            ];
        }

        return [$items, $taxesData, $totalTaxPrice, $totalQuantity, $totalRate, $totalDiscount];
    }

    // ─────────────────────────────────────────────────────────
    //  Tax helpers
    // ─────────────────────────────────────────────────────────

    public static function getTax(string|int $taxId): ?Tax
    {
        if (!isset(Utility::$taxes[$taxId])) {
            try {
                Utility::$taxes[$taxId] = Tax::find($taxId);
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed fetching Tax[{$taxId}]: {$e->getMessage()}");
                return null;
            }
        }
        return Utility::$taxes[$taxId] ?? null;
    }

    public static function tax(string $taxesCsv): array
    {
        $cacheKey = $taxesCsv;
        if (!isset(Utility::$taxsData[$cacheKey])) {
            $taxIds = array_filter(explode(',', $taxesCsv));
            $results = [];
            foreach ($taxIds as $id) {
                $taxModel = self::getTax(trim($id));
                if ($taxModel !== null)
                    $results[] = $taxModel;
            }
            Utility::$taxsData[$cacheKey] = $results;
        }
        return Utility::$taxsData[$cacheKey];
    }

    public static function taxRate(float $taxRate, float $price, float $quantity, float $discount = 0): float
    {
        $base = ($price * $quantity) - $discount;
        return $base * ($taxRate * 0.01);
    }

    public static function totalTaxRate(string $taxesCsv): float
    {
        $cacheKey = $taxesCsv;
        if (!isset(Utility::$taxRateData[$cacheKey])) {
            $taxIds = array_filter(explode(',', $taxesCsv));
            $rateSum = 0.0;
            foreach ($taxIds as $id) {
                $taxModel = self::getTax(trim($id));
                $rateSum += $taxModel->rate ?? 0;
            }
            Utility::$taxRateData[$cacheKey] = $rateSum;
        }
        return Utility::$taxRateData[$cacheKey];
    }

    // ─────────────────────────────────────────────────────────
    //  Balance helpers
    // ─────────────────────────────────────────────────────────

    public static function userBalance(string $userType, string|int $id, float $amount, string $type): void
    {
        $modelClass = $userType === 'customer' ? Customer::class : Vendor::class;
        $user = $modelClass::find($id);
        if (!$user) return;
        try {
            DB::transaction(function () use ($user, $amount, $type) {
                $multiplier = $type === 'credit' ? 1 : -1;
                $newBalance = ($user?->balance ?? 0) + ($amount * $multiplier);
                $user->balance = $newBalance;
                $user?->save();
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating {$userType}Balance[{$id}]: {$e->getMessage()}");
        }
    }

    public static function updateUserBalance(string $userType, string|int $id, float $amount, string $type): void
    {
        $modelClass = $userType === PMC::CT ? Customer::class : Vendor::class;
        $user = $modelClass::find($id);
        if (!$user) return;
        try {
            DB::transaction(function () use ($user, $amount, $type) {
                $multiplier = $type === 'credit' ? -1 : 1;
                $newBalance = ($user?->balance ?? 0) + ($amount * $multiplier);
                $user->balance = $newBalance;
                $user?->save();
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updatingUser {$userType}Balance[{$id}]: {$e->getMessage()}");
        }
    }

    public static function bankAccountBalance(string|int $id, float $amount, string $type): void
    {
        $account = BankAccount::find($id);
        if (!$account) return;
        try {
            DB::transaction(function () use ($account, $amount, $type) {
                $multiplier = $type === 'credit' ? 1 : -1;
                $newBalance = ($account->opening_balance ?? 0) + ($amount * $multiplier);
                $account->opening_balance = $newBalance;
                $account->save();
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating BankAccount[{$id}]: {$e->getMessage()}");
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Stock / warehouse
    // ─────────────────────────────────────────────────────────

    public static function totalQuantity(string $type, int $quantity, string|int $productId): void
    {
        $product = ProductService::find($productId);
        if (!$product) return;
        DB::transaction(function () use ($product, $type, $quantity) {
            $currentQty = $product->quantity ?? 0;
            $newQty = $type === 'minus'
                ? max(0, $currentQty - $quantity)
                : $currentQty + $quantity;
            $product->quantity = $newQty;
            $product->save();
        });
    }

    public static function warehouseQuantity(string $type, int $quantity, string|int $productId, string|int $warehouseId): void
    {
        $record = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
        if (!$record) return;
        DB::transaction(function () use ($record, $type, $quantity) {
            $currentQty = $record->quantity ?? 0;
            $newQty = $type === 'minus'
                ? max(0, $currentQty - $quantity)
                : $currentQty + $quantity;
            $record->quantity = $newQty;
            $record->save();
        });
    }

    public static function warehouseTransferQty(string|int $fromWarehouse, string|int $toWarehouse, string|int $productId, int $quantity, ?string $delete = null): void
    {
        DB::transaction(function () use ($fromWarehouse, $toWarehouse, $productId, $quantity, $delete) {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            $toRecord = WarehouseProduct::firstOrNew([
                'warehouse_id' => $toWarehouse,
                'product_id'   => $productId,
            ]);
            if (!$toRecord->exists && $delete !== 'delete') {
                $toRecord->quantity  = $quantity;
                $toRecord[DC::COL_TABLE_CREATOR] = $user?->creatorId();
                $toRecord->save();
            } elseif ($toRecord->exists) {
                $toRecord->quantity += $quantity;
                $toRecord->save();
            }
            $fromRecord = WarehouseProduct::where('warehouse_id', $fromWarehouse)
                ->where('product_id', $productId)
                ->first();
            if ($fromRecord) {
                $newQty = $fromRecord->quantity - $quantity;
                if ($newQty <= 0)
                    $fromRecord->delete();
                else {
                    $fromRecord->quantity = $newQty;
                    $fromRecord->save();
                }
            }
        });
    }

    public static function addProductStock(string|int $productId, int $quantity, string $type, string $description, string|int $typeId): void
    {
        DB::transaction(function () use ($productId, $quantity, $type, $description, $typeId) {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            StockReport::create([
                'product_id' => $productId,
                'quantity'   => $quantity,
                'type'       => $type,
                'type_id'    => $typeId,
                'description' => $description,
                'title'      => ucfirst($type) . ' Stock',
                DC::COL_TABLE_CREATOR => $user?->creatorId(),
            ]);
        });
    }

    public static function addWarehouseStock(string|int $productId, int $quantity, string|int $warehouseId): void
    {
        try {
            DB::transaction(function () use ($productId, $quantity, $warehouseId) {
                $record = WarehouseProduct::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                $newQty = $quantity;
                if ($record)
                    $newQty = $record->quantity + $quantity;
                WarehouseProduct::updateOrCreate(
                    [
                        'warehouse_id' => $warehouseId,
                        'product_id' => $productId,
                    ],
                    ['quantity' => $newQty]
                );
            });
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed updating warehouse stock: {$e->getMessage()}");
        }
    }
}
