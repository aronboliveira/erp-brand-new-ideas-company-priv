<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoiceProductsTable extends Migration
{
    private const TABLE           = 'invoice_products';
    private const COL_INVOICE_ID  = 'invoice_id';
    private const COL_PRODUCT_ID  = 'product_id';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                         // ! CHANGED
            $table->uuid(self::COL_INVOICE_ID);                    // ! CHANGED
            $table->uuid(self::COL_PRODUCT_ID);                    // ! CHANGED
            $table->integer('quantity');
            $table->string('tax', 50)->nullable();
            $table->float('discount')->default(0.00);
            $table->decimal('price', 16, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->timestamps();
            foreach ([
                self::COL_INVOICE_ID => DatabaseConstants::TABLE_INVS,
                self::COL_PRODUCT_ID => DatabaseConstants::TABLE_PROD_SERVS
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
                self::COL_INVOICE_ID,
                self::COL_PRODUCT_ID,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
