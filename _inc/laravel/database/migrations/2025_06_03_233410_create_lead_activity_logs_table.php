<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{AppModuleType, LogType, UserType};
use App\Traits\{
    HasNullableAuditColumns,
    LeadConnected
};
use Illuminate\Database\{
    Migrations\Migration,
    Schema\Blueprint
};
use Illuminate\Support\Facades\{
    Log,
    Schema
};

class CreateLeadActivityLogsTable extends Migration
{
    use HasNullableAuditColumns;
    use LeadConnected;

    private const TABLE    = DC::TABLE_LD_ACT_LOGS;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_USER_ID)->index();
            $table->string(UC::COL_U_TP, UserType::values())->default(UserType::Client)->nullable();
            $this->addLeadColumns($table, unique: false, nullable: false, cascade: true);
            $table->enum(AC::COL_LOG_TP, LogType::values())
                ->default(LogType::Other)
                ->index();
            $table->text('remark')->nullable();
            $table->enum(AC::COL_MD, AppModuleType::values())->default(AppModuleType::Other)->nullable()->index();
            $table->string('label', 255)->nullable();
            $table->text('description')->nullable();
            $table->json(DC::COL_RL_CAT)->nullable();
            $table->json(PJC::COL_TAGS)->nullable();
            $table->json(DC::COL_ER_LG)->nullable();
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropLeadColumnForeign($table, self::TABLE);
            foreach (
                [
                    UC::COL_USER_ID,
                ] as $column
            ) {
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
