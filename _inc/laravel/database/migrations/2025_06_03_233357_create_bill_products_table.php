<?php

use App\Config\Constants\{BanksConstants as BKC, BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasNullableAuditColumns, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillProductsTable extends Migration
{
    use HasNullableAuditColumns;
    use TracksFailures;
    private const TABLE = DC::TABLE_BL_PRD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_BL_ID)->index();
            $table->uuid(BC::COL_PRD_ID)->nullable()->index();
            $table->uuid(BKC::COL_COA)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->float('discount')->default(0.00); // todo change this to decimal later
            $table->unsignedDecimal('total', 16, 4)->default(0.0000);
            $table->string('tax', 64)->nullable(); // * it is not clear if this was meant to be a tax label, id or percentage, so keeping for compatibility, and for mock seeders it will be considered a label randomized within the Tax model
            $table->uuid(BC::COL_TAX_ID)->nullable(); // * tax id
            $table->json(BC::COL_OT_TX)->nullable(); // * other taxes applied, stored separately for compatibility with legacy code // * for now, these are only accepted in saving if also present in the 'taxes' of the Bill instance queried with COL_BL_ID
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $this->addFailureTrackingColumns($table);
            $this->addAuditColumns($table);
            $table->foreign(BC::COL_BL_ID)
                ->references('id')
                ->on(DC::TABLE_BILLS)
                ->cascadeOnDelete();
            foreach (
                [
                    BC::COL_PRD_ID => DC::TABLE_PROD_SERVS,
                    BKC::COL_COA => DC::TABLE_COAS,
                    BC::COL_TAX_ID => DC::TABLE_TAXES,

                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_BL_ID,
                    BC::COL_PRD_ID,
                    BKC::COL_COA,
                    BC::COL_TAX_ID
                ] as $col
            ) {
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
