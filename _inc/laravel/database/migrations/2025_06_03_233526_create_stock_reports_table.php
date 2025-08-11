<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateStockReportsTable extends Migration
{
    private const TABLE           = 'stock_reports';
    private const COL_PRODUCT_ID  = 'product_id';
    private const COL_QUANTITY    = 'quantity';
    private const COL_TYPE        = 'type';
    private const COL_TYPE_ID     = 'type_id';
    private const COL_DESCRIPTION = 'description';
    private const COL_CREATED_BY  = 'created_by';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                            // ! CHANGED
            $table->uuid(self::COL_PRODUCT_ID);                       // ! CHANGED
            $table->integer(self::COL_QUANTITY)->default(0);
            $table->string(self::COL_TYPE);
            $table->uuid(self::COL_TYPE_ID);                          // ! CHANGED
            $table->text(self::COL_DESCRIPTION)->nullable();
            $table->uuid(self::COL_CREATED_BY);                       // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_PRODUCT_ID => DatabaseConstants::TABLE_PROD_SERVS,
                self::COL_CREATED_BY => DatabaseConstants::TABLE_USERS,
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_PRODUCT_ID,
                self::COL_CREATED_BY,
            ] as $column) {
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
