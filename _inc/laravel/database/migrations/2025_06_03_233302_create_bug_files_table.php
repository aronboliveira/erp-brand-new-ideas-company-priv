<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugFilesTable extends Migration
{
    private const TABLE = 'bug_files';
    private const COL_BUG = 'bug_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();             // ! CHANGED
            $table->string('file');
            $table->string('name');
            $table->string('extension');
            $table->string('file_size');
            $table->uuid(self::COL_BUG); // ! CHANGED
            $table->string('user_type');               // ! CHANGED (was String)
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            foreach ([
                self::COL_BUG                          => DatabaseConstants::TABLE_BUGS,
                DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_BUG,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
