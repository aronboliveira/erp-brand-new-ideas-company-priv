<?php

use App\Config\Constants\{BanksConstants as BKC, BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{PaymentMethod, PaymentStatus};
use App\Traits\{HasNullableAuditColumns, HasPaymentColumns, RegistersShipping, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePaymentsTable extends Migration
{
    use HasNullableAuditColumns, HasPaymentColumns, RegistersShipping, TracksFailures;
    private const TABLE = DC::TABLE_PAY;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->date('date')->default(now()->format('Y-m-d'))->nullable();
            $table->uuid(BC::COL_BACC_ID)->nullable();
            $table->uuid(BC::COL_ACC_TO)->nullable(); // * the account to which the payment is made, can be null for cash payments, and managed at controller level
            $table->unsignedDecimal('discount', 16, 2)->nullable()->default('0.00');
            $table->uuid(BKC::COL_COA)->default(DC::DEFAULT_UUID)->nullable(); // * this should be nullable for cases where the payment method is cash or pix, and managed at controller level // * defaulted to system id to avoid issues with legacy data
            $table->uuid(UC::COL_VD_ID)->index()->nullable();
            $table->uuid(BC::COL_CAT_ID)->index()->nullable();
            $table->string('recurring')->nullable();
            $this->addPaymentColumns($table);
            $table->enum('status', PaymentStatus::values())->default(PaymentStatus::Pending->value)->nullable(); // ? nullable for testing purposes
            $table->string(BC::COL_ADD_RCP)->nullable(); // * this is not clear in the old implementation, so keeping it as is for now for compatibility
            $table->json(BC::COL_RCP_MD)->nullable();
            foreach (
                [
                    BC::COL_BACC_ID => DC::TABLE_BANK_ACC,
                    BC::COL_ACC_TO => DC::TABLE_BANK_ACC,
                    BKC::COL_COA => DC::TABLE_COAS,
                    UC::COL_VD_ID => DC::TABLE_VENDORS,
                    BC::COL_CAT_ID => DC::TABLE_PROD_SERV_CATS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addBillingColumns($table);
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropPaymentColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_BACC_ID,
                    BC::COL_ACC_TO,
                    BKC::COL_COA,
                    UC::COL_VD_ID,
                    BC::COL_CAT_ID,
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
