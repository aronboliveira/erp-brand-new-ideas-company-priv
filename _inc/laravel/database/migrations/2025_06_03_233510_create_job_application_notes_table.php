<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobApplicationNotesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JB_AP_NTS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('author', 254)->nullable()->index(); // ? name of the author
                $table->uuid(AC::COL_AUTHOR_ID)->nullable()->index(); // ? id of the author, referencing DC::TABLE_USERS
                $table->timestamp(AC::COL_WRT_AT)->nullable()->index(); // ? written_at; automatically set to null if both 'author' and AC::COL_AUTHOR_ID are null
                $table->uuid(AC::COL_APLN_ID)->index();
                $table->uuid(AC::COL_NOTE_CREATED)->nullable()->index();
                $table->text('note')->nullable(); // * this is a redundant column that, if null, should be filled from the row referenced in DC::TABLE_NOTES
                $table->uuid('reviewer', 254)->nullable()->index(); // ? name of the reviewer
                $table->timestamp(AC::COL_RVW_AT)->nullable()->index(); // ? reviewed_at; automatically set to null if 'reviewer' is null
                $table->foreign(AC::COL_APLN_ID)
                    ->references('id')
                    ->on(DC::TABLE_JOB_APPS)
                    ->cascadeOnDelete();
                $table->foreign(AC::COL_NOTE_CREATED)
                    ->references('id')
                    ->on(DC::TABLE_NOTES)
                    ->restrictOnDelete();
                foreach (
                    [
                        AC::COL_AUTHOR_ID => DC::TABLE_USERS,
                        'reviewer' => DC::TABLE_USERS,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->nullOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    AC::COL_AUTHOR_ID,
                    AC::COL_APLN_ID,
                    AC::COL_NOTE_CREATED,
                    'reviewer',
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
