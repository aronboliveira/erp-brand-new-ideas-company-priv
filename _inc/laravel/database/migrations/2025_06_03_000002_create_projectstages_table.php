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
    private const COL_COLOR     = PJC::COL_CL;
    private const COL_ORDER     = AC::COL_OD;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('color', 15)->default('#11ff3388')->nullable();
            $table->integer('order')->default(0);
            $this->addAuditColumns($table);
            $table->json('notes')->nullable();
            $table->json('involved')->nullable(); // ? ids/names of users involved in this stage
            $table->json('metadata')->nullable();
            $table->json('positioning')->nullable(); // ? metadata for ui positioning
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
