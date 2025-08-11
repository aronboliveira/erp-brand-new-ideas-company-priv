<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePosProductsTable extends Migration
{
    private const TABLE            = 'pos_products';
    private const COL_DESCRIPTION  = 'description';
    private const COL_DISCOUNT     = 'discount';
    private const COL_PRICE        = 'price';
    private const COL_POS_ID       = 'pos_id';
    private const COL_PRODUCT_ID   = 'product_id';
    private const COL_QUANTITY     = 'quantity';
    private const COL_TAX          = 'tax';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                             // ! CHANGED
            $table->uuid(self::COL_POS_ID);                            // ! CHANGED
            $table->uuid(self::COL_PRODUCT_ID);                        // ! CHANGED
            $table->integer(self::COL_QUANTITY)->default(0);
            $table->string(self::COL_TAX)->default('0.00');
            $table->float(self::COL_DISCOUNT, 15, 2)->default(0.00)->nullable();
            $table->decimal(self::COL_PRICE, 15, 2)->default(0.00);    // ! CHANGED
            $table->text(self::COL_DESCRIPTION)->nullable();           // * matches model
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            $table->timestamps();
            foreach ([
                self::COL_POS_ID                  => DatabaseConstants::TABLE_POS,
                self::COL_PRODUCT_ID              => DatabaseConstants::TABLE_PROD_SERVS,
                DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_POS_ID,
                self::COL_PRODUCT_ID,
                DatabaseConstants::TABLE_CREATOR,
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
