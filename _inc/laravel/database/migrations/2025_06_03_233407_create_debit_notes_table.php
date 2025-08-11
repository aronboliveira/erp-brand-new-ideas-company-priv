<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDebitNotesTable extends Migration
{
    private const TABLE = 'debit_notes';
    private const COL_BILL = 'bill';
    private const COL_VENDOR = 'vendor';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                 // ! CHANGED
            $table->uuid(self::COL_BILL);                          // ! CHANGED
            $table->uuid(self::COL_VENDOR);                        // ! CHANGED
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            foreach ([
                self::COL_BILL   => DatabaseConstants::TABLE_BILLS,
                self::COL_VENDOR => DatabaseConstants::TABLE_VENDORS,
                DatabaseConstants::TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
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
                self::COL_VENDOR,
                DatabaseConstants::TABLE_CREATOR,
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
