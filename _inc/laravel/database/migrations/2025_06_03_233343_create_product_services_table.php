<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProductServicesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_PROD_SERVS;
    private const COL_TAX = 'tax_id';
    private const COL_CAT = 'category_id';
    private const COL_UNIT = 'unit_id';
    private const COL_COA = 'chartaccount_id';
    private const COL_SALE_COA = 'sale_' . self::COL_COA;
    private const COL_EXP_COA = 'expense_' . self::COL_COA;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                       // ! CHANGED
            $table->string('name');
            $table->string('sku');
            $table->decimal('sale_price', 16, 2)->default(0.0);
            $table->decimal('purchase_price', 16, 2)->default(0.0);
            $table->float('quantity')->default(0.0);             // * consider moving to separate stock table
            $table->uuid(self::COL_TAX)->nullable();                  // ! CHANGED
            $table->uuid(self::COL_CAT)
                ->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            $table->uuid(self::COL_UNIT)
                ->default(DatabaseConstants::DEFAULT_UUID);     // ! CHANGED
            $table->string('type');
            $table->uuid(self::COL_SALE_COA)
                ->default(DatabaseConstants::DEFAULT_UUID);    // !CHANGED
            $table->uuid(self::COL_EXP_COA)
                ->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            $table->text('description')->nullable();
            $table->string('pro_image')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);                          // ! CHANGED
            foreach ([
                self::COL_TAX               => DatabaseConstants::TABLE_TAXES,
                self::COL_CAT               => DatabaseConstants::TABLE_PROD_SERV_CATS,
                self::COL_UNIT              => DatabaseConstants::TABLE_PROD_SERV_UNITS,
                self::COL_SALE_COA          => DatabaseConstants::TABLE_COAS,
                self::COL_EXP_COA           => DatabaseConstants::TABLE_COAS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
            ] as $c => $t)
                $table->foreign($c)
                    ->references('id')
                    ->on($t)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_TAX,
                self::COL_CAT,
                self::COL_UNIT,
                self::COL_SALE_COA,
                self::COL_EXP_COA,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
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
