<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoicePaymentsTable extends Migration
{
    private const TABLE         = 'invoice_payments';
    private const COL_ACC = 'account_id';
    private const COL_INV = 'invoice_id';
    private const COL_ORDER = 'order_id';
    private const R = 'receipt';
    private const P = 'payment';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                          // ! CHANGED
            $table->uuid(self::COL_INV);                     // ! CHANGED
            $table->date('date');
            $table->decimal('amount', 16, 2)->default(0.00);
            $table->uuid(self::COL_ACC);                     // ! CHANGED
            $table->integer(self::P . '_method')->default(0);
            $table->string(self::P . '_type')->default('Manually');
            $table->uuid(self::COL_ORDER)->nullable(); // ! CHANGED
            $table->uuid('txn_id')->nullable(); // ! CHANGED
            $table->string('currency')->nullable();
            $table->string(self::R)->nullable();
            $table->string('add_' . self::R)->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            $table->timestamps();
            foreach ([
                self::COL_INV => DatabaseConstants::TABLE_INVS,
                self::COL_ACC => DatabaseConstants::TABLE_BANK_ACC,
                self::COL_ORDER => DatabaseConstants::TABLE_ORDERS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS
            ] as $col => $tbl) {
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
            }
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_INV,
                self::COL_ACC,
                self::COL_ORDER,
                DatabaseConstants::TABLE_CREATOR
            ] as $col) {
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
