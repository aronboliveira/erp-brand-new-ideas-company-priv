<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateGoalsTable extends Migration
{
    private const TABLE = 'goals';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();           // ! CHANGED
            $table->string('name');
            $table->string('type');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->boolean('is_display')->default(true); // ! CHANGED
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();       // ! CHANGED consider FK to users
            $table->timestamps();
            $table->foreign(DatabaseConstants::COL_TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::COL_TABLE_CREATOR)
                    && $table->dropForeign([DatabaseConstants::COL_TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::COL_TABLE_CREATOR
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
