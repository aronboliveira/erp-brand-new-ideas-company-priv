<?php

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugCommentsTable extends Migration
{
    use HasNullableAuditColumns;
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
                $table->foreign(self::COL_BUG)
                    ->references('id')
                    ->on(DC::TABLE_BUGS)
                    ->cascadeOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach ([self::COL_BUG] as $col) {
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
