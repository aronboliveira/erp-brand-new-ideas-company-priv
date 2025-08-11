<?php

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBranchesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_BRANCHES;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();             // ! CHANGED
                $table->string(CompaniesConstants::COL_BRC_NM);
                $table->uuid(DatabaseConstants::TABLE_CREATOR);                // ! CHANGED
                $table->timestamps();
                $table->foreign(DatabaseConstants::TABLE_CREATOR)              // * consider FK
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)
                    ->onDelete('cascade');
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
                    'Failed to drop foreign key for '
                        . DatabaseConstants::TABLE_CREATOR
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
