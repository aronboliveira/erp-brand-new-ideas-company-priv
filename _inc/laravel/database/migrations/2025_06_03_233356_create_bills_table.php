<?php

use App\Config\Constants\{
    DatabaseConstants as DC,
    BillsConstants as BC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Enums\{BillStatus, ConsumableType, PaymentStatus, TransactionType, UserType};
use App\Traits\{
    HasNullableAuditColumns,
    RegistersShipping,
    AcceptsSchedule,
    TracksFailures
};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillsTable extends Migration
{
    use HasNullableAuditColumns;
    use RegistersShipping;
    use AcceptsSchedule;
    use TracksFailures;

    private const TABLE = DC::TABLE_BILLS;
    private const DATE  = 'date';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_BL_ID)->default(DC::DEFAULT_UUID)->unique()->index(); // ? secondary identifier for querying
            $table->date(BC::COL_BL_DT)->index();
            $table->date(BC::COL_SD_DT)->nullable()->index(); // ? nullable para testes 
            // * booted and saving should ensure that 'send_date', 'due_date' and 'bill_date' and never null (defaulting, respectively, to today / today + 1 day / today + 2 days) and that the ['bill_date', 'send_date'] are always <= 'due_date'
            $table->date(PJC::COL_D_DATE)->index();
            $table->uuid(UC::COL_VD_ID)->nullable()->index();
            $table->uuid(BC::COL_CAT_ID)->nullable()->index();
            $table->uuid(BC::COL_OD_ID)->default(DC::DEFAULT_UUID)->nullable(); // * default para compatibilidade com legado
            $table->unsignedTinyInteger(PJC::COL_STATUS)->default(0)->max(4)->index();
            $table->enum(BC::COL_STT_LB, BillStatus::values())->default(BillStatus::Draft)->index()->nullable(); // ? nullable para testes
            // * booted and saving should enforce the default in nullish cases
            $table->enum(BC::COL_PAY_STT, PaymentStatus::values())->default(PaymentStatus::Processing)->index()->nullable(); // ? nullable para testes
            $table->enum('type', array_unique(array_merge(ConsumableType::values(), array_filter(TransactionType::values(), fn($value) => $value === TransactionType::Bill))))->default(ConsumableType::Other)->nullable(); // ? nullable para testes
            $table->enum(UC::COL_U_TP, UserType::values())->default(UserType::Customer)->nullable()->index();
            $table->string(BC::COL_PRC_CUR, 10)->default(SC::DEF_SITE_CURRENCY_ID)->nullable();
            $table->unsignedTinyInteger(BC::COL_SHIP_DSP)->default(1);
            $table->unsignedDecimal('amount', 15, 2)->default(0.00)->nullable(); // * it is not clear why the amount wasn't listed in the old implementation, so its added here as nullable for now
            $table->unsignedTinyInteger(BC::COL_DSC_APL)->default(0); // ? whether or not it is eligible for discount
            $table->unsignedDecimal('discount', 15, 2)->default(0.00)->nullable();
            // * it is not clear why 'discount' was not listed in the old implementation, so its added here as nullable for now
            // * booted ans saving should ensure that 'discount' is always <= 'amount'
            $table->json('taxes')->nullable();
            $table->json('items')->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $this->addBillingColumns($table);
            $this->addShippingColumns($table);
            $this->addScheduleColumns($table);
            $this->addFailureTrackingColumns($table);
            $this->addAuditColumns($table);
            foreach (
                [
                    BC::COL_OD_ID     => DC::TABLE_ORDERS,
                    UC::COL_VD_ID     => DC::TABLE_VENDORS,
                    BC::COL_CAT_ID    => DC::TABLE_PROD_SERV_CATS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([BC::COL_OD_ID, UC::COL_VD_ID, BC::COL_CAT_ID] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for ' . $col . ' on table ' . self::TABLE . ': ' . $e->getMessage()
                    );
                }
            }
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
