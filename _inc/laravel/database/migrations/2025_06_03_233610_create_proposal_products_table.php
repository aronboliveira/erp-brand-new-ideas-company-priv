<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProposalProductsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PPS_PRD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_PPS_ID)->index();
            $table->uuid(BC::COL_PRD_ID)->unique();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('tax', 15, 2)->nullable();
            $table->float('discount')->default(0.00);
            $table->decimal('price', 16, 2)->default(0.00);
            $table->text('description')->nullable();
            foreach (
                [
                    BC::COL_PPS_ID => DC::TABLE_PROPOSALS,
                    BC::COL_PRD_ID => DC::TABLE_PRODUCTS,
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
                    BC::COL_PPS_ID,
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
