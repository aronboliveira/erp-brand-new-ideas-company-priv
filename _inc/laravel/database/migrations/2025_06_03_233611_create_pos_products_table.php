<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePosProductsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_POS_PRD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_POS_ID)->index();
            $table->uuid(BC::COL_PRD_ID)->unique();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedDecimal('tax', 15, 2)->default(0.00);
            $table->float('discount', 15, 2)->default(0.00)->nullable();
            $table->decimal('price', 15, 2)->default(0.00);
            $table->text('description')->nullable();
            foreach (
                [
                    BC::COL_POS_ID => DC::TABLE_POS,
                    BC::COL_PRD_ID => DC::TABLE_PROD_SERVS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
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
                    BC::COL_POS_ID,
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
