<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasNullableAuditColumns, HasPaymentRequestColumns, RegistersShipping, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoicesTable extends Migration
{
    use HasNullableAuditColumns, HasPaymentRequestColumns, RegistersShipping, TracksFailures;
    private const TABLE              =  DC::TABLE_INVS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_INV_ID)->index()->unique(); // ? queryable secondary identifier
            $this->addPaymentRequestColumns($table, isProjection: false, issues: true, addIssueDays: 0);
            $this->addShippingColumns($table);
            $table->uuid(BC::COL_BL_ID)->nullable()->index(); // ? nullable for tests
            $table->uuid(BC::COL_CST_ID)->index();
            $table->uuid(BC::COL_TAX_ID)->nullable();
            $table->text(BC::COL_REF_N)->nullable();
            $table->foreign(BC::COL_CST_ID)
                ->references('id')
                ->on(DC::TABLE_CUSTOMERS)
                ->restrictOnDelete();
            $table->foreign(BC::COL_BL_ID)
                ->references('id')
                ->on(DC::TABLE_BILLS)
                ->nullOnDelete();
            $table->foreign(BC::COL_TAX_ID)
                ->references('id')
                ->on(DC::TABLE_TAXES)
                ->nullOnDelete();
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropPaymentRequestColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_CST_ID,
                    BC::COL_BL_ID,
                    BC::COL_TAX_ID,
                ] as $col
            ) {
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
