<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\ActivityType;
use App\Traits\{DealConnected, HasNullableAuditColumns, LeadConnected, TaskConnected, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateActivityLogsTable extends Migration
{
    use DealConnected, HasNullableAuditColumns, LeadConnected, TaskConnected, TracksFailures;
    private const TABLE = DC::TABLE_ACT_LOG;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid(UC::COL_USER_ID)->index();
                $table->uuid(PJC::COL_PJ_ID)->nullable();
                $table->uuid(PJC::COL_CTC_ID)->nullable();
                $this->addLeadColumns($table, unique: false, nullable: true, cascade: false);
                $this->addTaskColumns($table, unique: false, nullable: true, cascade: false);
                $this->addDealColumns($table, unique: false, nullable: true, cascade: false);
                $table->uuid('document')->nullable();
                $table->uuid(AC::COL_TSK_FL)->nullable();
                $table->uuid(AC::COL_LD_FL)->nullable();
                $table->uuid(AC::COL_DL_FL)->nullable();
                $table->enum(AC::COL_LOG_TP, array_column(ActivityType::cases(), 'value'))->default(ActivityType::Other->value)->index(); // ? if somehow failed the cases, then set to Other->value at model level
                $table->text('remark')->nullable();
                $table->timestamp('timestamp')->useCurrent()->nullable(); // ? enforced at model level
                $table->json('metadata')->nullable();
                $table->foreign(UC::COL_USER_ID)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
                    ->cascadeOnDelete();
                foreach (
                    [
                        PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                        PJC::COL_CTC_ID => DC::TABLE_CONTRACTS,
                        'document' => DC::TABLE_DOCS,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->nullOnDelete();
                $this->addAuditColumns($table);
                $this->addFailureTrackingColumns($table);
            });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropTaskColumnForeign($table, self::TABLE);
            $this->dropLeadColumnForeign($table, self::TABLE);
            $this->dropDealColumnForeign($table, self::TABLE);
            foreach (
                [
                    UC::COL_USER_ID,
                    PJC::COL_PJ_ID,
                    PJC::COL_CTC_ID,
                    'document',
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
