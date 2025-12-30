<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{ActivityType, AppModuleType};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// ? this model is kind of redundant considering there is already an ActivityLog one with far more use, but keeping for legacy purposes
class CreateLogActivitiesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_LOG_ACTS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->enum('module', array_column(AppModuleType::cases(), 'value'))->nullable()->index();
                $table->uuid(AC::COL_MI)->nullable();
                $table->enum('type', array_column(ActivityType::cases(), 'value'))->default(ActivityType::Other->value)->nullable()->index();
                $table->date(PJC::COL_S_DT);
                $table->time(AC::COL_TSK_TIME);
                $table->text('note')->nullable();
                $table->json('metadata')->nullable();
                $this->addAuditColumns($table);
            });
        }
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
