<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePosPaymentsTable extends Migration
{
    private const TABLE = 'pos_payments';
    private const D = 'discount';
    private const COL_ACC = 'account_id';
    private const COL_POS = 'pos_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                             // ! CHANGED
                $table->uuid('pos_id');                                     // ! CHANGED
                $table->uuid(self::COL_ACC); // * ADDED
                $table->date('date')->nullable();
                $table->decimal('amount', 15, 2)->default('0.00');
                $table->decimal(self::D, 15, 2)->nullable()->default('0.00');
                $table->decimal(self::D . '_amount', 15, 2)->nullable()->default('0.00'); // ! CHANGED
                $table->uuid(DatabaseConstants::TABLE_CREATOR)
                    ->default(DatabaseConstants::DEFAULT_UUID);                    // ! CHANGED
                $table->timestamps();
                foreach ([
                    self::COL_POS                      => DatabaseConstants::TABLE_POS,
                    self::COL_ACC                      => DatabaseConstants::TABLE_BANK_ACC,
                    DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
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
                self::COL_POS,
                self::COL_ACC,
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
