<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillProductsTable extends Migration
{
    private const TABLE = 'bill_products';
    private const COL_BILL = 'bill_id';
    private const COL_PROD = 'product_id';
    private const COL_COA = 'chart_account_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                            // ! CHANGED
            $table->uuid(self::COL_BILL);                                   // ! CHANGED
            $table->uuid(self::COL_PROD);                                // ! CHANGED
            $table->uuid(self::COL_COA);                          // ! CHANGED
            $table->integer('quantity');
            $table->string('tax', 50)->nullable();
            $table->float('discount')->default(0.00);
            $table->decimal('total', 16, 2)->default(0.0);             // ! CHANGED
            $table->text('description')->nullable();                   // * added description to match model
            $table->timestamps();
            $table->foreign(self::COL_BILL)
                ->references('id')
                ->on(DatabaseConstants::TABLE_BILLS)
                ->cascadeOnDelete(); // * ADDED
            $table->foreign(self::COL_PROD)
                ->references('id')
                ->on(DatabaseConstants::TABLE_PROD_SERVS)
                ->cascadeOnDelete(); // * ADDED
            $table->foreign(self::COL_COA)
                ->references('id')->on(DatabaseConstants::TABLE_COAS)
                ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_BILL,
                self::COL_PROD,
                self::COL_COA,
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
