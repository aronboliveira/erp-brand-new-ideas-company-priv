<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasFileColumns, HasNullableAuditColumns};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugFilesTable extends Migration
{
    use HasNullableAuditColumns, HasFileColumns;

    private const TABLE = DC::TABLE_BG_FL;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE))
            return;
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_BUG)->index(); // bug_id
            $table->string('file')->nullable()->index();
            $this->addFileColumns($table);
            $table->enum(UC::COL_U_TP, UserType::values())
                ->default(UserType::Client->value)
                ->index();
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

            foreach ([AC::COL_BUG] as $column) {
                try {
                    Schema::hasColumn(self::TABLE, $column) && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for ' . $column . ' on table ' . self::TABLE . ': ' . $e->getMessage()
                    );
                }
            }
        });

        Schema::dropIfExists(self::TABLE);
    }
}
