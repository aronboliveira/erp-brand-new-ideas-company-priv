<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasNfeColumns, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * this model just be a "bridge" that links purchases to payments made against them
class CreatePurchasePayments extends Migration
{
    use HasNfeColumns, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PRC_PAY;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_PRC_ID)->index();
            $table->uuid(BC::COL_PAY_ID)->nullable()->index();
            $table->uuid(BC::COL_BACC_ID)->nullable()->index();
            $table->unique([BC::COL_PRC_ID, BC::COL_PAY_ID, BC::COL_BACC_ID], 'uniq_prc_pay_bacc');
            $table->date('date');
            $table->decimal('amount', 16, 2)->default(0.00);
            $table->unsignedTinyInteger(BC::COL_PAY_MTD)->default(0);
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->string(BC::COL_ADD_RCP)->nullable();
            $this->addNfeColumns($table);
            $table->foreign(BC::COL_PRC_ID)
                ->references('id')
                ->on(DC::TABLE_PURCHASES)
                ->restrictOnDelete();
            foreach (
                [
                    BC::COL_PAY_ID => DC::TABLE_PAY,
                    BC::COL_BACC_ID => DC::TABLE_BANK_ACC,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
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
                    BC::COL_PAY_ID,
                    BC::COL_BACC_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to execute down for '
                            . $column
                            . ' foreign key column: '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
