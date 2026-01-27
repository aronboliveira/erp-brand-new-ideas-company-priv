<?php

use App\Config\Constants\{
    DatabaseConstants as DC,
    BillsConstants as BC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Enums\{ConsumableType, TransactionType, UserType};
use App\Traits\{
    AcceptsSchedule,
    HasNullableAuditColumns,
    HasPaymentRequestColumns,
    RegistersShipping,
    TracksFailures
};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillsTable extends Migration
{
    use AcceptsSchedule;
    use HasNullableAuditColumns;
    use HasPaymentRequestColumns;
    use RegistersShipping;
    use TracksFailures;

    private const TABLE = DC::TABLE_BILLS;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addPaymentRequestColumns($table);
            $table->uuid(BC::COL_BL_ID)->default(DC::DEFAULT_UUID)->unique(); // ? secondary identifier for querying
            $table->date(BC::COL_BL_DT)->index();
            // * booted and saving should ensure that 'send_date', 'due_date' and 'bill_date' and never null (defaulting, respectively, to today / today + 1 day / today + 2 days) and that the ['bill_date', 'send_date'] are always <= 'due_date'
            $table->uuid(UC::COL_VD_ID)->nullable()->index();
            $table->uuid(BC::COL_OD_ID)->default(DC::DEFAULT_UUID)->nullable(); // * default para compatibilidade com legado
            $table->enum('type', array_unique(array_merge(ConsumableType::values(), array_filter(TransactionType::values(), fn($value) => $value === TransactionType::Bill))))->default(ConsumableType::Other)->nullable(); // ? nullable para testes
            $table->enum(UC::COL_U_TP, UserType::values())->default(UserType::Customer)->nullable()->index();
            $table->string(BC::COL_PRC_CUR, 10)->default(SC::DEF_SITE_CURRENCY_ID)->nullable();
            $table->json('items')->nullable();
            $this->addShippingColumns($table);
            $this->addScheduleColumns($table);
            foreach (
                [
                    BC::COL_OD_ID     => DC::TABLE_ORDERS,
                    UC::COL_VD_ID     => DC::TABLE_VENDORS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
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
            foreach ([BC::COL_OD_ID, UC::COL_VD_ID] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for ' . $col . ' on table ' . self::TABLE . ': ' . $e->getMessage()
                    );
                }
            }
        });

        Schema::dropIfExists(self::TABLE);
    }
}
