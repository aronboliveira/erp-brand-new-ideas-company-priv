<?php

use App\Config\Constants\{BanksConstants as BKC, BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{AcceptsSchedule, HasNullableAuditColumns, HasPaymentColumns, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBankTransfersTable extends Migration
{
    use AcceptsSchedule, HasPaymentColumns, HasNullableAuditColumns, TracksFailures;
    private const TABLE = DC::TABLE_BNK_TRF;
    // * models linked: users,contracts,loans,invoices,payslips,product_service_units,bank_account
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(BKC::COL_TRF_CD)->unique()->index()->nullable(); // ? nullable for testing purposes
            $table->uuid(BC::COL_ACC_FROM)->index();
            $table->uuid(BC::COL_ACC_TO)->index();
            $this->addPaymentColumns($table);
            $this->addScheduleColumns($table);
            $this->addFailureTrackingColumns($table);
            $this->addAuditColumns($table);
            foreach (
                [
                    BC::COL_ACC_FROM       => DC::TABLE_BANK_ACC,
                    BC::COL_ACC_TO         => DC::TABLE_BANK_ACC,
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
            $this->dropPaymentColumnForeigns($table);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_ACC_FROM,
                    BC::COL_ACC_TO,
                    BC::COL_RCC_BY,
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
