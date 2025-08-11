<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugCommentsTable extends Migration
{
    private const TABLE = 'bug_comments';
    private const COL_BUG = 'bug_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                             // ! CHANGED
                $table->text('comment');
                $table->uuid(self::COL_BUG);                                     // ! CHANGED
                $table->string('user_type', 100);
                $table->uuid(DatabaseConstants::TABLE_CREATOR);                                 // ! CHANGED
                $table->timestamps();
                $table->foreign(self::COL_BUG)->references('id')->on(DatabaseConstants::TABLE_BUGS)
                    ->onDelete('cascade');          // * ADDED consider FK
                $table->foreign(DatabaseConstants::TABLE_CREATOR)->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)->onDelete('cascade');      // * ADDED consider FK
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([self::COL_BUG, DatabaseConstants::TABLE_CREATOR] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
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
