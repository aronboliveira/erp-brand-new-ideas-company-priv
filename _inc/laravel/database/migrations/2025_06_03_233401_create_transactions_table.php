<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\TransactionType;
use App\Traits\{AcceptsSchedule, HasNullableAuditColumns, HasPaymentColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTransactionsTable extends Migration
{
    use AcceptsSchedule, HasNullableAuditColumns, HasPaymentColumns;
    private const TABLE = DC::TABLE_TRS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('account')->index();
                $table->uuid(UC::COL_USER_ID)->index();
                $table->string(UC::COL_U_TP);
                $table->enum(BC::COL_PAY_TP, [TransactionType::Bill->value, TransactionType::Invoice->value, TransactionType::Pos->value, TransactionType::Other->value])->default(TransactionType::Other->value)->index()->nullable();
                $table->uuid(BC::COL_PAY_ID)->index()->nullable(); // ? Referes to a bill payment OR an invoice payment OR a POS payment
                $table->index([BC::COL_PAY_TP, BC::COL_PAY_ID]);
                $table->string('category')->index(); // ? this is just a label for exposing the transaction type, kept for compatibility, but should be deprecated
                $this->addPaymentColumns($table);
                $this->addScheduleColumns($table);
                $table->string('type')->nullable()->index(); // ? this referes to the source, like a payment service
                $table->date('date')->default(now()->format('Y-m-d'));
                foreach (
                    [
                        'account'         => DC::TABLE_BANK_ACC,
                        UC::COL_USER_ID   => DC::TABLE_USERS,
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
            $this->dropPaymentColumnForeigns($table);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'account',
                    UC::COL_USER_ID,
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
