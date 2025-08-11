<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBudgetsTable extends Migration
{
    private const TABLE = 'budgets';
    private const D = 'date';
    private const DATA = 'data';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();            // ! CHANGED
            $table->string('name');
            $table->string('period');
            $table->date('start_' . self::D)->nullable();   // ! CHANGED (was 'from')
            $table->date('end_' . self::D)->nullable();     // ! CHANGED (was 'to')
            $table->text('income_' . self::DATA)->nullable();  // ! CHANGED
            $table->text('expense_' . self::DATA)->nullable(); // ! CHANGED
            $table->uuid(DatabaseConstants::TABLE_CREATOR);               // ! CHANGED
            $table->timestamps();
            $table->foreign(DatabaseConstants::TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::TABLE_CREATOR)
                    && $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to execute down for ' . DatabaseConstants::TABLE_CREATOR
                        . ' foreign key column: ' . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
