<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectStagesTable extends Migration
{
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
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->integer(self::COL_ORDER)->default(0);
            $table->timestamps();
            $table->foreign(DC::TABLE_CREATOR)
                ->references('id')->on(DC::TABLE_USERS)
                ->nullOnDelete();
            $table->foreign(DC::TABLE_UPDATER)
                ->references('id')->on(DC::TABLE_USERS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $column)
                try {
                    Schema::hasColumn(self::TABLE, $column) &&
                        $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning('Failed to drop foreign key for ' . $column . ': ' . $e->getMessage());
                }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
