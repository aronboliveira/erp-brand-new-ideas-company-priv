<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\PaymentType;
use App\Traits\{HasNullableAuditColumns, HasPaymentColumns, HasPaymentConclusionColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillPaymentsTable extends Migration
{
    use HasNullableAuditColumns, HasPaymentColumns, HasPaymentConclusionColumns;
    private const TABLE = DC::TABLE_BL_PAY;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('code')->unique()->nullable(); // ? nullable for tests, should be booted/created at model level if null
            $table->uuid(BC::COL_BL_ID)->index();
            $table->uuid(BC::COL_OD_ID)->nullable();
            $this->addPaymentColumns($table, nullableReconcile: true, nullableInvoice: true);
            $this->addPaymentConclusionColumns($table, nullableAcc: false, nullableCat: true, onDeleteAcc: 'restrict', onDeleteCat: 'set null');
            $table->enum(BC::COL_PAY_TP, PaymentType::values())->default(PaymentType::Manual)->nullable(); // ? nullable for tests, force default in booting/saving
            $table->foreign(BC::COL_BL_ID)
                ->references('id')
                ->on(DC::TABLE_BILLS)
                ->restrictOnDelete();
            $table->foreign(BC::COL_OD_ID)
                ->references('id')
                ->on(DC::TABLE_ORDERS)
                ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropPaymentColumnForeigns($table, self::TABLE);
            $this->dropPaymentConclusionColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_BL_ID,
                    BC::COL_OD_ID,
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
