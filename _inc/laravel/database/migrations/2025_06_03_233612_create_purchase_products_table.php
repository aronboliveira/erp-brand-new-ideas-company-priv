<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * this model just be a "bridge" that links purchases to the products made with them
class CreatePurchaseProductsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PRC_PRD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_PRC_ID)->index();
            $table->uuid(BC::COL_PRD_ID)->index();
            $table->integer('quantity');
            $table->string('tax', 50)->nullable();
            $table->decimal('discount', 16, 2)->default(0.00);
            $table->decimal('total', 16, 2)->default(0.00);
            foreach (
                [
                    BC::COL_PRC_ID => DC::TABLE_PURCHASES,
                    BC::COL_PRD_ID => DC::TABLE_PROD_SERVS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete('cascade');
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_PRC_ID,
                    BC::COL_PRD_ID,
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
