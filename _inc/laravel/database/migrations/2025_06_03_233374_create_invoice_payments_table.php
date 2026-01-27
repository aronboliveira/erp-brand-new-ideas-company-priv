<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\PaymentType;
use App\Traits\{HasNfeColumns, HasNullableAuditColumns, HasPaymentColumns, HasPaymentConclusionColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoicePaymentsTable extends Migration
{
    use HasNfeColumns, HasNullableAuditColumns, HasPaymentColumns, HasPaymentConclusionColumns;
    private const TABLE = DC::TABLE_INV_PAY;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('code')->unique()->nullable(); // ? nullable for tests, should be booted/created at model level if null
            $table->uuid(BC::COL_PAY_ID)->nullable()->unique();
            $table->unique([BC::COL_PAY_ID, 'code'], 'uniq_inv_pay_pay_code');
            $this->addPaymentColumns($table, nullableReconcile: true, nullableInvoice: false);
            $this->addPaymentConclusionColumns($table, nullableAcc: false, nullableCat: true, onDeleteAcc: 'restrict', onDeleteCat: 'set null');
            $this->addNfeColumns($table);
            $table->enum(BC::COL_PAY_TP, PaymentType::values())->default(PaymentType::Manual);
            $table->uuid(BC::COL_OD_ID)->nullable();
            $table->uuid(BC::COL_TAX_ID)->nullable();
            $table->string('currency')->nullable(); // * this will be kept for legacy reasons, but should be deprecated in future versions and booted and saving should ensure that the BC::COL_CUR_ID matches the currency of the linked invoice and this one, with the hierarchy: COL_CUR_ID in invoice > COL_CUR_ID in payment, found in the payment columns > 'currency' in payment, here listed
            $table->string('receipt')->nullable(); // * constrained at model level to be match a secure url (with https + domain at env(APP_URL) or known domains of storage providers) OR a file path at the local filesystem OR a id for an existing Document (model) row
            foreach (
                [
                    BC::COL_OD_ID => DC::TABLE_ORDERS,
                    BC::COL_PAY_ID => DC::TABLE_PAY,
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
            $this->dropPaymentColumnForeigns($table, self::TABLE);
            $this->dropPaymentConclusionColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_OD_ID,
                    BC::COL_PAY_ID
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
