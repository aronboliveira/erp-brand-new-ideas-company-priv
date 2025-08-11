<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants,
    SupportsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSupportsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_SUPPORTS;
    private const COL_TK_CRT = SupportsConstants::COL_TKT_CR;
    private const COL_USER = SupportsConstants::COL_USR;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();        // ! CHANGED
            $table->string(SupportsConstants::COL_SBJ);
            $table->uuid(self::COL_TK_CRT);       // ! CHANGED
            $table->uuid(self::COL_USER);                 // ! CHANGED
            $table->string(ProjectsConstants::COL_PRT);
            $table->date(ProjectsConstants::COL_E_DT);
            $table->string(SupportsConstants::COL_TKT_CD)->nullable();
            $table->string(ActivitiesConstants::COL_TSK_STT)->default('0');
            $table->string(SupportsConstants::COL_ATC)->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);           // ! CHANGED
            $table->text(ActivitiesConstants::COL_DESC)->nullable();
            $table->timestamps();
            foreach ([
                self::COL_TK_CRT  => DatabaseConstants::TABLE_USERS,
                self::COL_USER    => DatabaseConstants::TABLE_USERS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_TK_CRT,
                self::COL_USER,
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
