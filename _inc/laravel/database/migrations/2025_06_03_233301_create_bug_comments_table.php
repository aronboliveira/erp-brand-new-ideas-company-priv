<?php

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PC, UsersConstants as UC};
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
                $table->uuid('id')->primary();
                $table->uuid(self::COL_BUG)->index();
                $table->text('comment');
                $table->string(UC::COL_U_TP, 64)->default(PC::CL);
                $table->uuid(DC::TABLE_CREATOR)->nullable();
                $table->uuid(DC::TABLE_UPDATER)->nullable();
                $table->timestamps();
                $table->foreign(self::COL_BUG)
                    ->references('id')
                    ->on(DC::TABLE_BUGS)
                    ->cascadeOnDelete();
                foreach ([DC::TABLE_UPDATER, DC::TABLE_CREATOR] as $col)
                    $table->foreign($col)
                        ->references('id')
                        ->on(DC::TABLE_USERS)
                        ->nullOnDelete();
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([self::COL_BUG, DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $col) {
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
