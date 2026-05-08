<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateWarehouseProductsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_WRH_PRD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_WRH_ID)->index();
            $table->uuid(BC::COL_PRD_ID)->index();
            $table->unsignedInteger('quantity')->default(0);
            // A product may be stocked in many warehouses; what must be
            // unique is the (warehouse, product) pair — not the product
            // itself. The original `->unique()` on product_id contradicted
            // FinanceBillingService::warehouseTransferQty(), which inserts
            // a second row with the same product_id during a transfer.
            $table->unique([BC::COL_WRH_ID, BC::COL_PRD_ID], 'warehouse_products_wh_prd_unique');
            foreach (
                [
                    BC::COL_WRH_ID => DC::TABLE_WHS,
                    BC::COL_PRD_ID => DC::TABLE_PRODUCTS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_WRH_ID,
                    BC::COL_PRC_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
