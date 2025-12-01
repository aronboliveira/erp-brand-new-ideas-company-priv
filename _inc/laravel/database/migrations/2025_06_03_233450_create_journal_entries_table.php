<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJournalEntriesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_JOURNAL_ENTRIES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->uuid('journal_id')->nullable();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);
            $table->timestamps();
            $table->foreign(DatabaseConstants::COL_TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->onDelete('cascade'); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::COL_TABLE_CREATOR) &&
                    $table->dropForeign([DatabaseConstants::COL_TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::COL_TABLE_CREATOR
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
