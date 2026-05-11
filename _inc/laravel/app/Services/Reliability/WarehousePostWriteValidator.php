<?php

namespace App\Services\Reliability;

use App\Config\Constants\{BillsConstants as BLC, DatabaseConstants as DC};
use App\Models\{OperationLedger, Pos, ProductService, Purchase, PurchaseProduct, StockReport, Warehouse, WarehouseProduct, WarehouseTransfer};

class WarehousePostWriteValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public function validate(string $eventType, array $payload, ?OperationLedger $ledger = null): PostWriteValidationResult
    {
        return match ($eventType) {
            'warehouse.product.created',
            'warehouse.product.updated' => $this->validateProductPresent($eventType, $payload, $ledger),
            'warehouse.product.imported' => $this->validateBulkImport($payload, $ledger),
            'warehouse.product.deleted' => $this->validateProductDeleted($payload, $ledger),
            'warehouse.stock.adjusted' => $this->validateStockAdjusted($payload, $ledger),
            'warehouse.transfer.created',
            'warehouse.transfer.updated' => $this->validateTransferPresent($eventType, $payload, $ledger),
            'warehouse.transfer.deleted' => $this->validateTransferDeleted($payload, $ledger),
            'warehouse.warehouse.deleted' => $this->validateWarehouseDeleted($payload, $ledger),
            'warehouse.purchase.created',
            'warehouse.purchase.updated' => $this->validatePurchasePresent($eventType, $payload, $ledger),
            'warehouse.purchase.product.deleted' => $this->validatePurchaseProductDeleted($payload, $ledger),
            'warehouse.purchase.deleted' => $this->validatePurchaseDeleted($payload, $ledger),
            'warehouse.pos.created' => $this->validatePosPresent($payload, $ledger),
            default => PostWriteValidationResult::pass(
                'warehouse',
                (string) ($payload['source_table'] ?? 'warehouse'),
                null,
                isset($payload['id']) ? (string) $payload['id'] : null,
                $payload,
                $this->originEvent($eventType, $payload, $ledger),
            ),
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateBulkImport(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $productIds = array_values(array_filter(array_map(
            fn(mixed $id): ?string => $this->stringOrNull($id),
            is_array($payload['product_ids'] ?? null) ? $payload['product_ids'] : []
        )));
        $importedRows = (int) ($payload['imported_rows'] ?? $payload['row_count'] ?? 0);
        $errors = [];

        if ($importedRows <= 0) {
            $errors['product'] = 'Product/service import did not persist any rows.';
        }

        if ($productIds !== []) {
            $persisted = ProductService::query()->whereIn('id', $productIds)->count();
            if ($persisted !== count($productIds)) {
                $errors['product'] = 'One or more imported product/service rows are missing after import.';
            }
        }

        return $this->result(
            $errors,
            DC::TABLE_PROD_SERVS,
            ProductService::class,
            $productIds[0] ?? null,
            [
                'payload' => $payload,
                'checked_product_ids' => $productIds,
            ],
            $this->originEvent('warehouse.product.imported', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateProductPresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $productId = $this->stringOrNull($payload['product_id'] ?? $payload['product_service_id'] ?? $payload['id'] ?? null);
        $product = $productId ? ProductService::query()->find($productId) : null;
        $errors = [];

        if (!$product) {
            $errors['product'] = 'Product/service was not persisted.';
        } else {
            $this->validateProductCore($product, $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_PROD_SERVS,
            ProductService::class,
            $productId,
            [
                'payload' => $payload,
                'product' => $product?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateProductDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $productId = $this->stringOrNull($payload['product_id'] ?? $payload['product_service_id'] ?? $payload['id'] ?? null);
        $product = $productId ? ProductService::query()->find($productId) : null;
        $warehouseRows = $productId ? WarehouseProduct::query()->where(BLC::COL_PRD_ID, $productId)->count() : 0;
        $errors = [];

        if ($product) {
            $errors['product_delete'] = 'Product/service still exists after delete operation.';
        }
        if ($warehouseRows > 0 && (bool) ($payload['expect_no_warehouse_stock'] ?? true)) {
            $errors['warehouse_product'] = 'Warehouse stock rows still reference the deleted product/service.';
        }

        return $this->result(
            $errors,
            DC::TABLE_PROD_SERVS,
            ProductService::class,
            $productId,
            [
                'payload' => $payload,
                'product_exists_after_delete' => (bool) $product,
                'warehouse_rows_after_delete' => $warehouseRows,
            ],
            $this->originEvent('warehouse.product.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateStockAdjusted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $productId = $this->stringOrNull($payload['product_id'] ?? null);
        $warehouseId = $this->stringOrNull($payload['warehouse_id'] ?? null);
        $product = $productId ? ProductService::query()->find($productId) : null;
        $errors = [];

        if (!$product) {
            $errors['product'] = 'Stock adjustment points to a missing product/service.';
        } else {
            $this->validateProductCore($product, $payload, $errors);
        }

        if ($warehouseId) {
            $this->validateWarehouseQuantity($warehouseId, $productId, $payload['expected_warehouse_quantity'] ?? null, $errors);
        }

        $this->validateStockReport($payload, $errors);

        return $this->result(
            $errors,
            DC::TABLE_PROD_SERVS,
            ProductService::class,
            $productId,
            [
                'payload' => $payload,
                'product' => $product?->getAttributes(),
            ],
            $this->originEvent('warehouse.stock.adjusted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateTransferPresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $transferId = $this->stringOrNull($payload['transfer_id'] ?? $payload['id'] ?? null);
        $transfer = $transferId ? WarehouseTransfer::query()->find($transferId) : null;
        $errors = [];

        if (!$transfer) {
            $errors['transfer'] = 'Warehouse transfer was not persisted.';
        } else {
            $this->validateTransferCore($transfer, $errors);
        }

        $this->validateWarehouseQuantity(
            $this->stringOrNull($payload['from_warehouse_id'] ?? $payload['from_warehouse'] ?? null),
            $this->stringOrNull($payload['product_id'] ?? null),
            $payload['expected_source_quantity'] ?? null,
            $errors,
            'source'
        );
        $this->validateWarehouseQuantity(
            $this->stringOrNull($payload['to_warehouse_id'] ?? $payload['to_warehouse'] ?? null),
            $this->stringOrNull($payload['product_id'] ?? null),
            $payload['expected_destination_quantity'] ?? null,
            $errors,
            'destination'
        );

        return $this->result(
            $errors,
            DC::TABLE_WRH_TRF,
            WarehouseTransfer::class,
            $transferId,
            [
                'payload' => $payload,
                'transfer' => $transfer?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateTransferDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $transferId = $this->stringOrNull($payload['transfer_id'] ?? $payload['id'] ?? null);
        $transfer = $transferId ? WarehouseTransfer::query()->find($transferId) : null;
        $errors = [];

        if ($transfer) {
            $errors['transfer_delete'] = 'Warehouse transfer still exists after delete/reversal.';
        }

        $this->validateWarehouseQuantity(
            $this->stringOrNull($payload['from_warehouse_id'] ?? $payload['from_warehouse'] ?? null),
            $this->stringOrNull($payload['product_id'] ?? null),
            $payload['expected_source_quantity'] ?? null,
            $errors,
            'source'
        );
        $this->validateWarehouseQuantity(
            $this->stringOrNull($payload['to_warehouse_id'] ?? $payload['to_warehouse'] ?? null),
            $this->stringOrNull($payload['product_id'] ?? null),
            $payload['expected_destination_quantity'] ?? null,
            $errors,
            'destination'
        );

        return $this->result(
            $errors,
            DC::TABLE_WRH_TRF,
            WarehouseTransfer::class,
            $transferId,
            [
                'payload' => $payload,
                'transfer_exists_after_delete' => (bool) $transfer,
            ],
            $this->originEvent('warehouse.transfer.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateWarehouseDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $warehouseId = $this->stringOrNull($payload['warehouse_id'] ?? $payload['id'] ?? null);
        $warehouse = $warehouseId ? Warehouse::query()->find($warehouseId) : null;
        $stockRows = $warehouseId ? WarehouseProduct::query()->where(BLC::COL_WRH_ID, $warehouseId)->count() : 0;
        $openTransfers = $warehouseId
            ? WarehouseTransfer::query()
                ->where(function ($query) use ($warehouseId): void {
                    $query->where(BLC::COL_FROM_WRH, $warehouseId)
                        ->orWhere(BLC::COL_TO_WRH, $warehouseId);
                })
                ->count()
            : 0;
        $errors = [];

        if ($warehouse) {
            $errors['warehouse_delete'] = 'Warehouse still exists after delete operation.';
        }
        if ($stockRows > 0) {
            $errors['warehouse_quantity'] = 'Warehouse stock rows still exist after warehouse delete.';
        }
        if ($openTransfers > 0) {
            $errors['transfer_warehouse'] = 'Warehouse transfer rows still reference the deleted warehouse.';
        }

        return $this->result(
            $errors,
            DC::TABLE_WRH,
            Warehouse::class,
            $warehouseId,
            [
                'payload' => $payload,
                'warehouse_exists_after_delete' => (bool) $warehouse,
                'stock_rows_after_delete' => $stockRows,
                'transfer_rows_after_delete' => $openTransfers,
            ],
            $this->originEvent('warehouse.warehouse.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePurchasePresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $purchaseId = $this->stringOrNull($payload['purchase_id'] ?? $payload['id'] ?? null);
        $purchase = $purchaseId ? Purchase::query()->find($purchaseId) : null;
        $errors = [];

        if (!$purchase) {
            $errors['purchase'] = 'Purchase was not persisted.';
        }

        $this->validateExpectedQuantities($payload, $errors);

        return $this->result(
            $errors,
            DC::TABLE_PURCHASES,
            Purchase::class,
            $purchaseId,
            [
                'payload' => $payload,
                'purchase' => $purchase?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePurchaseDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $purchaseId = $this->stringOrNull($payload['purchase_id'] ?? $payload['id'] ?? null);
        $purchase = $purchaseId ? Purchase::query()->find($purchaseId) : null;
        $errors = [];

        if ($purchase) {
            $errors['purchase'] = 'Purchase still exists after delete operation.';
        }

        $this->validateExpectedQuantities($payload, $errors);

        return $this->result(
            $errors,
            DC::TABLE_PURCHASES,
            Purchase::class,
            $purchaseId,
            [
                'payload' => $payload,
                'purchase_exists_after_delete' => (bool) $purchase,
            ],
            $this->originEvent('warehouse.purchase.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePurchaseProductDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $purchaseProductId = $this->stringOrNull($payload['purchase_product_id'] ?? $payload['id'] ?? null);
        $purchaseId = $this->stringOrNull($payload['purchase_id'] ?? null);
        $purchase = $purchaseId ? Purchase::query()->find($purchaseId) : null;
        $purchaseProduct = $purchaseProductId ? PurchaseProduct::query()->find($purchaseProductId) : null;
        $errors = [];

        if (!$purchase) {
            $errors['purchase'] = 'Purchase row is missing after purchase-product delete operation.';
        }
        if ($purchaseProduct) {
            $errors['purchase_stock'] = 'Purchase product still exists after delete operation.';
        }

        $this->validateExpectedQuantities($payload, $errors);

        return $this->result(
            $errors,
            DC::TABLE_PRC_PRD,
            PurchaseProduct::class,
            $purchaseProductId,
            [
                'payload' => $payload,
                'purchase_exists_after_item_delete' => (bool) $purchase,
                'purchase_product_exists_after_delete' => (bool) $purchaseProduct,
            ],
            $this->originEvent('warehouse.purchase.product.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePosPresent(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $posId = $this->stringOrNull($payload['pos_id'] ?? $payload['id'] ?? null);
        $pos = $posId ? Pos::query()->find($posId) : null;
        $errors = [];

        if (!$pos) {
            $errors['pos'] = 'POS commit row was not persisted.';
        }

        $this->validateExpectedQuantities($payload, $errors);

        return $this->result(
            $errors,
            DC::TABLE_POS,
            Pos::class,
            $posId,
            [
                'payload' => $payload,
                'pos' => $pos?->getAttributes(),
            ],
            $this->originEvent('warehouse.pos.created', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $errors
     */
    private function validateExpectedQuantities(array $payload, array &$errors): void
    {
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = $this->stringOrNull($item['product_id'] ?? null);
            if (!$productId) {
                continue;
            }

            $product = ProductService::query()->find($productId);
            if (!$product) {
                $errors['product'] = 'Operation item points to a missing product/service.';
                continue;
            }

            $expectedProductQuantity = $item['expected_product_quantity'] ?? null;
            if (is_numeric($expectedProductQuantity) && (float) $product->quantity !== (float) $expectedProductQuantity) {
                $errors['product_quantity'] = 'Product/service quantity does not match the expected operation result.';
            }

            $warehouseId = $this->stringOrNull($item['warehouse_id'] ?? $payload['warehouse_id'] ?? null);
            $this->validateWarehouseQuantity(
                $warehouseId,
                $productId,
                $item['expected_warehouse_quantity'] ?? null,
                $errors,
                'item_' . $index
            );
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $errors
     */
    private function validateProductCore(ProductService $product, array $payload, array &$errors): void
    {
        if ((float) ($product->quantity ?? 0) < 0.0) {
            $errors['product_quantity'] = 'Product/service quantity cannot be negative.';
        }
        if ((float) ($product->{BLC::COL_SL_PRC} ?? 0) < 0.0 || (float) ($product->{BLC::COL_PC_PRC} ?? 0) < 0.0) {
            $errors['product_price'] = 'Product/service prices cannot be negative.';
        }
        if (trim((string) ($product->type ?? '')) === '') {
            $errors['product_type'] = 'Product/service type must stay populated.';
        }

        if (is_numeric($payload['expected_product_quantity'] ?? null) && (float) $product->quantity !== (float) $payload['expected_product_quantity']) {
            $errors['product_quantity'] = 'Product/service quantity does not match the expected post-write value.';
        }
        if (is_numeric($payload['expected_sale_price'] ?? null) && (float) $product->{BLC::COL_SL_PRC} !== (float) $payload['expected_sale_price']) {
            $errors['product_price'] = 'Product/service sale price does not match the expected post-write value.';
        }
        if (is_numeric($payload['expected_purchase_price'] ?? null) && (float) $product->{BLC::COL_PC_PRC} !== (float) $payload['expected_purchase_price']) {
            $errors['product_price'] = 'Product/service purchase price does not match the expected post-write value.';
        }
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function validateTransferCore(WarehouseTransfer $transfer, array &$errors): void
    {
        if ((string) $transfer->{BLC::COL_FROM_WRH} === (string) $transfer->{BLC::COL_TO_WRH}) {
            $errors['transfer_warehouse'] = 'Warehouse transfer source and destination cannot match.';
        }
        if ((int) ($transfer->quantity ?? 0) <= 0) {
            $errors['transfer_quantity'] = 'Warehouse transfer quantity must be greater than zero.';
        }

        $received = (int) ($transfer->{BLC::COL_QTY_RCV} ?? 0);
        $returned = (int) ($transfer->{BLC::COL_QTY_RTN} ?? 0);
        $quantity = (int) ($transfer->quantity ?? 0);
        if ($received + $returned > $quantity) {
            $errors['transfer_quantity'] = 'Warehouse transfer received plus returned quantity exceeds transfer quantity.';
        }
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function validateWarehouseQuantity(
        ?string $warehouseId,
        ?string $productId,
        mixed $expectedQuantity,
        array &$errors,
        string $label = 'warehouse',
    ): void {
        if (!$warehouseId || !$productId || !is_numeric($expectedQuantity)) {
            return;
        }

        $record = WarehouseProduct::query()
            ->where(BLC::COL_WRH_ID, $warehouseId)
            ->where(BLC::COL_PRD_ID, $productId)
            ->first();
        $actual = $record ? (int) $record->quantity : 0;
        $expected = max(0, (int) $expectedQuantity);

        if ($actual !== $expected) {
            $errors['warehouse_quantity_mismatch'] = "Warehouse {$label} quantity does not match the expected post-write value.";
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $errors
     */
    private function validateStockReport(array $payload, array &$errors): void
    {
        $stockReportId = $this->stringOrNull($payload['stock_report_id'] ?? null);
        $stockReportType = $this->stringOrNull($payload['stock_report_type'] ?? null);
        $stockReportTypeId = $this->stringOrNull($payload['stock_report_type_id'] ?? null);

        if (!$stockReportId && (!$stockReportType || !$stockReportTypeId)) {
            return;
        }

        $query = StockReport::query();
        if ($stockReportId) {
            $query->whereKey($stockReportId);
        } else {
            $query->where('type', $stockReportType)->where(BLC::COL_TP_ID, $stockReportTypeId);
        }

        $report = $query->latest('created_at')->first();
        if (!$report) {
            $errors['stock_report'] = 'Stock report row was not persisted.';
            return;
        }

        if (is_numeric($payload['quantity'] ?? null) && (int) $report->quantity !== (int) $payload['quantity']) {
            $errors['stock_report_quantity'] = 'Stock report quantity does not match the operation payload.';
        }
    }

    /**
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $snapshot
     * @param array<string, mixed> $originEvent
     */
    private function result(
        array $errors,
        string $sourceTable,
        string $sourceType,
        ?string $sourceRecordId,
        array $snapshot,
        array $originEvent,
    ): PostWriteValidationResult {
        if ($errors === []) {
            return PostWriteValidationResult::pass('warehouse', $sourceTable, $sourceType, $sourceRecordId, $snapshot, $originEvent);
        }

        return PostWriteValidationResult::fail(
            'warehouse',
            $sourceTable,
            $sourceType,
            $sourceRecordId,
            array_keys($errors),
            $errors,
            $snapshot,
            $originEvent,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function originEvent(string $eventType, array $payload, ?OperationLedger $ledger): array
    {
        return [
            'event_type' => $eventType,
            'operation_ledger_id' => $ledger?->id,
            'operation_key' => $ledger?->operation_key,
            'payload_reference' => $payload['message_key'] ?? $payload['reference'] ?? null,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
