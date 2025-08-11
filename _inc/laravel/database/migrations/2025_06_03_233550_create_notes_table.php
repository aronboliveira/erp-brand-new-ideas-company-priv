<?php

use App\Config\Constants\{ActivitiesConstants, DatabaseConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateNotesTable extends Migration
{
    private const TABLE              = DatabaseConstants::TABLE_NOTES;
    private const COL_MODULE_ID      = ActivitiesConstants::COL_MI;
    private const COL_MODULE_TYPE    = ActivitiesConstants::COL_MT;
    private const COL_NOTE           = ActivitiesConstants::COL_NT;
    private const COL_NOTE_CREATED_BY = self::COL_NOTE . '_' . DatabaseConstants::TABLE_CREATOR;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                          // ! CHANGED
            $table->text(self::COL_NOTE)->nullable();               // ! CHANGED
            $table->uuid(self::COL_MODULE_ID);                      // ! CHANGED
            $table->string(self::COL_MODULE_TYPE);                  // ! CHANGED
            $table->uuid(self::COL_NOTE_CREATED_BY)->index();                // ! CHANGED
            $table->timestamps();
            $table->foreign(self::COL_NOTE_CREATED_BY)
                ->references('id')->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, self::COL_NOTE_CREATED_BY)
                    && $table->dropForeign([self::COL_NOTE_CREATED_BY]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to execute down for '
                        . self::COL_NOTE_CREATED_BY
                        . ' foreign key column: '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
