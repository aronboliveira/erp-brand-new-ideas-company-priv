<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{HasNullableAuditColumns, PipelineConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadStagesTable extends Migration
{
    use HasNullableAuditColumns, PipelineConnected;
    private const TABLE = DC::TABLE_LEAD_STAGES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(PJC::COL_STG_NM)->index(); // * in a more mature system this may be a enum
            $this->addPipelineColumns($table, nullable: false, cascade: false);
            $table->integer(AC::COL_OD)->default(0);
            $table->text('notes')->nullable();
            $table->integer(PJC::COL_EST_CC)->default(0)->nullable();
            $table->boolean(PJC::COL_CRT)->default(false)->nullable(); // ? nullable for testing purposes, defaulted in boot/saving for now
            $this->addAuditColumns($table);
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropPipelineColumnForeign($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
