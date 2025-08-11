<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTransactionsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_TRS;
    private const COL_ACC = 'account';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                             // ! CHANGED
                $table->uuid('user_id');                                    // ! CHANGED
                $table->string('user_type');
                $table->uuid(self::COL_ACC);                                    // ! CHANGED
                $table->string('type')->nullable();
                $table->decimal('amount', 16, 2)->default('0.0');
                $table->text('description')->nullable();
                $table->date('date');
                $table->uuid(DatabaseConstants::TABLE_CREATOR);                                 // ! CHANGED
                $table->uuid('payment_id')->nullable();                     // ! CHANGED
                $table->string('category');
                $table->timestamps();
                foreach ([
                    self::COL_ACC                       => DatabaseConstants::TABLE_BANK_ACC,
                    DatabaseConstants::TABLE_CREATOR    => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable)
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->onDelete('cascade');
                // * consider polymorphic FK for payment_id
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
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
