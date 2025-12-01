<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectUsersTable extends Migration
{
    private const TABLE = 'project_users';
    private const COL_PROJ = 'project_id';
    private const COL_USER = 'user_id';
    private const COL_INVITE = 'invited_by';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();             // ! CHANGED from bigIncrements
            $table->uuid(self::COL_PROJ);                // ! CHANGED from integer
            $table->uuid(self::COL_USER);                   // ! CHANGED from integer
            $table->uuid(self::COL_INVITE)->nullable();    // ! CHANGED from integer default 0
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_PROJ => DatabaseConstants::TABLE_PROJECTS,
                    self::COL_USER => DatabaseConstants::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
            $table->foreign(self::COL_INVITE)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->onDelete('set null'); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_PROJ,
                    self::COL_USER,
                    self::COL_INVITE,
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
