<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserToDosTable extends Migration
{
    private const TABLE = 'user_to_dos';
    private const COL_USER = 'user_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();               // ! CHANGED
                $table->string(ActivitiesConstants::COL_TT);
                $table->boolean(ProjectsConstants::COL_IS_CP)->default(false);
                $table->uuid(self::COL_USER);                     // ! CHANGED
                $table->timestamps();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
                foreach (
                    [
                        self::COL_USER                  => DatabaseConstants::TABLE_USERS,
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
        try {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                foreach (
                    [
                        self::COL_USER,
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
        } catch (\Exception $e) {
            Log::warning(
                'One or more foreign keys on `' . self::TABLE . '` did not exist: '
                    . $e->getMessage()
            );
        }
        Schema::dropIfExists(self::TABLE);
    }
}
