<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectStagesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE         = DC::TABLE_PROJ_STAGES;
    private const COL_NAME      = PJC::COL_NM;
    private const COL_COLOR     = PJC::COL_CL;
    private const COL_ORDER     = AC::COL_OD;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(self::COL_NAME);
            $table->string(self::COL_COLOR, 15)->nullable();
            $table->integer(self::COL_ORDER)->default(0);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
