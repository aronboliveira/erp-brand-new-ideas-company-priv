<?php

use App\Config\Constants\{DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePurchaseProductsTable extends Migration
{
    private const PC = 'purchase';
    private const PD = 'product';
    private const TABLE = DC::TABLE_PRC_PRD;
    private const COL_PURCHASE = self::PC . '_id';
    private const COL_PROD = self::PD . '_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_PURCHASE);
            $table->uuid(self::COL_PROD);
            $table->integer('quantity');
            $table->string('tax', 50)->nullable();
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00); // ! CHANGED from price to total
            $table->timestamps();
            $table->uuid(DC::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_PURCHASE => DC::TABLE_PURCHASES,
                    self::COL_PROD     => DC::TABLE_PROD_SERVS,
                    DC::COL_TABLE_CREATOR => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_PURCHASE,
                    self::COL_PROD,
                    DC::COL_TABLE_CREATOR
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
