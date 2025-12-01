<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateWarehouseProductsTable extends Migration
{
    private const WH = 'warehouse';
    private const TABLE            = self::WH . '_products';
    private const COL_CREATED_BY   = DatabaseConstants::COL_TABLE_CREATOR;
    private const COL_PRODUCT_ID   = 'product_id';
    private const COL_QUANTITY     = 'quantity';
    private const COL_WAREHOUSE_ID = self::WH . '_id';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                             // ! CHANGED
            $table->uuid(self::COL_WAREHOUSE_ID);                       // ! CHANGED
            $table->uuid(self::COL_PRODUCT_ID);                         // ! CHANGED
            $table->integer(self::COL_QUANTITY)->default(0);
            $table->uuid(self::COL_CREATED_BY);                         // ! CHANGED
            $table->timestamps();
            foreach (
                [
                    self::COL_WAREHOUSE_ID => DatabaseConstants::TABLE_WHS,
                    self::COL_PRODUCT_ID => DatabaseConstants::TABLE_PROD_SERVS,
                    self::COL_CREATED_BY => DatabaseConstants::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_WAREHOUSE_ID,
                    self::COL_PRODUCT_ID,
                    self::COL_CREATED_BY,
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
