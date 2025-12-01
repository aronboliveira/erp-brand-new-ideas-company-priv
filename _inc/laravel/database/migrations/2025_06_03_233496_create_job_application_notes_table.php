<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobApplicationNotesTable extends Migration
{
    private const TABLE = 'job_application_notes';
    private const N = 'note';
    private const COL_APP = 'application_id';
    private const COL_NOTE = self::N . '_created';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                   // ! CHANGED
                $table->uuid(self::COL_APP);                  // ! CHANGED
                $table->uuid(self::COL_NOTE);                    // ! CHANGED
                $table->text(self::N)->nullable();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);                      // ! CHANGED
                $table->timestamps();
                foreach (
                    [
                        self::COL_APP                   => DatabaseConstants::TABLE_JOB_APPS,
                        self::COL_NOTE                  => DatabaseConstants::TABLE_USERS,
                        DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete(); // * ADDED
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_APP,
                    self::COL_NOTE,
                    DatabaseConstants::COL_TABLE_CREATOR,
                ] as $col
            ) {
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
