<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDocumentsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_DOCS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();          // ! CHANGED
                $table->string('name');
                $table->string('is_required');
                $table->uuid(DatabaseConstants::TABLE_CREATOR);              // ! CHANGED
                $table->timestamps();
                $table->foreign(DatabaseConstants::TABLE_CREATOR)
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)
                    ->onDelete('cascade'); // * ADDED
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
