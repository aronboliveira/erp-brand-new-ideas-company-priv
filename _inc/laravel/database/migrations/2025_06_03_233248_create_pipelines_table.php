<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePipelinesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE_NAME = DC::TABLE_PIPELINES;

    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string(PJC::COL_PPL_NM);
            $table->integer(AC::COL_OD)->default(0);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
