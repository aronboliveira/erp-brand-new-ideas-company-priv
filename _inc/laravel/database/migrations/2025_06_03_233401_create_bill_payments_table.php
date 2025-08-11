<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillPaymentsTable extends Migration
{
    private const TABLE = 'bill_payments';
    private const COL_BILL = 'bill_id';
    private const COL_ACC = 'account_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();               // ! CHANGED
            $table->uuid(self::COL_BILL);                     // ! CHANGED
            $table->date('date');
            $table->decimal('amount', 16, 2)->default(0);
            $table->uuid(self::COL_ACC);                  // ! CHANGED
            $table->integer('payment_method');
            $table->string('reference')->nullable();
            $table->string('add_receipt')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            foreach ([
                self::COL_BILL => DatabaseConstants::TABLE_BILLS,
                self::COL_ACC  => DatabaseConstants::TABLE_BANK_ACC,
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_BILL,
                self::COL_ACC,
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
