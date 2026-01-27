<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasNfeColumns, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePosPaymentsTable extends Migration
{
    // * it's not celar why POS payments are separated from regular payments, but it's in the legacy specs
    // * since this entity is redundant, all additional fields are found in the payments table and should be assured to align (with the data on the pos_payments table being the source of truth for POS-related payments) via ::booted and ::saving
    use HasNfeColumns, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_POS_PAY;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(BC::COL_POS_ID);
                $table->uuid('payment')->nullable()->unique(); // ? nullable for tests
                $table->uuid(BC::COL_BACC_ID)->nullable(); // * this should be nullable for cases where the payment method is cash or pix, and managed at controller level
                $table->unique([BC::COL_POS_ID, 'payment', BC::COL_BACC_ID], 'uniq_pos_pay_bacc');
                $table->date('date')->useCurrent()->nullable(); // ? if the link to DC::TABLE_PAY is successful AND DC::TABLE_PAY has 'date' on the schema, overrides the date in the linked payment
                $table->unsignedDecimal('amount', 16, 2)->default('0.00'); // ? if the link to DC::TABLE_PAY is successful, overrides the amount in the linked payment
                $table->unsignedDecimal('discount', 16, 2)->nullable()->default('0.00');
                $table->unsignedDecimal(BC::COL_DSC_AMT, 16, 2)->nullable()->default('0.00'); // * it's not clear why both discount and discount amount are needed, but it's in the legacy specs, maybe it's the gross vs net amount?
                $this->addNfeColumns($table);
                $table->foreign(BC::COL_POS_ID)
                    ->references('id')
                    ->on(DC::TABLE_POS)
                    ->restrictOnDelete();
                $table->foreign(BC::COL_BACC_ID)
                    ->references('id')
                    ->on(DC::TABLE_BANK_ACC)
                    ->nullOnDelete();
                $table->foreign('payment')
                    ->references('id')
                    ->on(DC::TABLE_PAY)
                    ->nullOnDelete(); // * todo should NEVER be null on production, but kept as it is for testing purposes
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
                    BC::COL_BACC_ID,
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
