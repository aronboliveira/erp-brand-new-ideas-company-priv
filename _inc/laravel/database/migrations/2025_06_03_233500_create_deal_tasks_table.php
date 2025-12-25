<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{HasNullableAuditColumns, TaskConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealTasksTable extends Migration
{
    use HasNullableAuditColumns, TaskConnected;
    private const TABLE       = DC::TABLE_DL_TSK;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_DL)->index();
            $table->string(PJC::COL_NM)->index();
            $table->text('description')->nullable();
            $table->date(AC::COL_TSK_DATE); // ? at boot/saving, ensure that this will mirror the date column of AC::COL_TSK_ID if the FK is not null AND the date field is not null in the DC::TABLE_TASKS row
            $table->time(AC::COL_TSK_TIME); // ? at boot/saving, ensure that this will mirror the time column of AC::COL_TSK_ID if the FK is not null AND the time field is not null in the DC::TABLE_TASKS row
            $table->unsignedTinyInteger(PJC::COL_PRT)->default(1); // * ideally this should have been an string, but was written like that in legacy code. It should represent a index within the Priority Level enum, limiting between 0 and the maximum number of statuses defined there
            $table->unsignedTinyInteger(AC::COL_TSK_STT)->default(0); // * ideally this should have been an string, but was written like that in legacy code. It should represent a index within the EvaluationStatus enum, limiting between 0 and the maximum number of statuses defined there
            $this->addTaskColumns($table, unique: false, nullable: true, cascade: false);
            $table->foreign(AC::COL_DL)
                ->references('id')
                ->on(DC::TABLE_DEALS)
                ->cascadeOnDelete();
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropTaskColumnForeign($table, self::TABLE);
            if (Schema::hasColumn(self::TABLE, AC::COL_DL)) {
                try {
                    $table->dropForeign([AC::COL_DL]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . AC::COL_DL
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
