<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasCommentColumns, HasNullableAuditColumns};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugCommentsTable extends Migration
{
    use HasNullableAuditColumns, HasCommentColumns;

    private const TABLE = DC::TABLE_BG_CMT;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(AC::COL_BUG)->index();
                $this->addCommentColumns(
                    $table,
                    unnullify: [UC::COL_USER_ID, UC::COL_U_TP],
                    userTypeValues: array_column(UserType::cases(), 'value'),
                    defaultUserType: UserType::Client
                );
                $table->foreign(AC::COL_BUG)
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
            $this->dropCommentColumnForeigns($table, self::TABLE);

            foreach ([AC::COL_BUG] as $col) {
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
