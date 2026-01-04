<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{EvaluationStatus, Visibility};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTimesheetsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TMS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 254)->unique(); // * generated as "TMS-{UUID}", checking uniqueness at model level with do/while
                $table->uuid(PJC::COL_PJ_ID)->nullable()->index();
                $table->uuid(AC::COL_TSK_ID)->nullable(); // ? if there is a linked project and the 'date' of the linked task is before its PJC::COL_S_DT or after its PJC::COL_E_DT, this should be nullified
                $table->uuid(PJC::COL_PJ_TSK_ID)->nullable(); // ? if is linked to a project different than the one linked to this timesheet, this should be nullified; if there is a linked project AND its PJC::COL_S_DT is not null AND the PJC::COL_S_DT of the linked project task is before the PJC::COL_S_DT of the project OR after the project PJC::COL_E_DT, this should be nullified; the same logic applies to the (possibly null) PJC::COL_E_DT of the linked project if the linked project task PJC::COL_S_DT OR the PJC::COL_E_DT disrespect the project date bounds;
                $table->date(PJC::COL_S_DT)->nullable(); // ? NEVER before than the PJC::COL_S_DT NEITHER after PJC::COL_E_DT of the linked project (if any)
                $table->time(PJC::COL_E_DT)->nullable(); // ? NEVER before than the PJC::COL_S_DT NEITHER after PJC::COL_E_DT of the linked project (if any)
                $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::NotStarted->value)->nullable()->index(); // ? adjusted dynamically based on the _by/_at columns && the 'date' and 'time' columns
                $table->date('date')->index(); // * this was never explained in legacy, but it is supossed to be the due_date and due_time
                $table->time('time');
                $table->decimal(AC::COL_TTL_TIME)->default(0.00)->nullable(); // ? total_time in hours, decimal with 2 places
                $table->decimal(PJC::COL_EXP_DR)->default(0.00)->nullable(); // ? expense_duraction in hours, decimal with 2 places, hard capped by the linked project (if not null) COL_E_HRS column (if not null and a valid decimal) OR the ((PJC::COL_E_DT - PJC::COL_S_DT) * 24) if both date columns are not null (whatever is the mininum between the two [estimated_hrs vs difference of date boundaries], assuming none is nullish)
                $table->uuid(PJC::COL_SBM_BY)->nullable()->index();
                $table->timestamp(PJC::COL_SBM_AT)->nullable()->index();
                $table->uuid(PJC::COL_APV_BY)->nullable()->index();
                $table->timestamp(PJC::COL_APV_AT)->nullable()->index();
                $table->uuid(PJC::COL_REJ_BY)->nullable()->index();
                $table->timestamp(PJC::COL_REJ_AT)->nullable()->index();
                $table->enum('visibility', array_column(Visibility::cases(), 'value'))->default(Visibility::Private->value)->nullable()->index();
                $table->text('description')->nullable();
                $table->uuid(UC::COL_EMP_ID)->nullable()->index(); // ? the employee responsible for this timesheet
                $table->json('attachments')->nullable();
                foreach (
                    [
                        PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                        AC::COL_TSK_ID => DC::TABLE_TASKS,
                        PJC::COL_PJ_TSK_ID => DC::TABLE_PROJ_TSKS,
                        UC::COL_EMP_ID => DC::TABLE_EMPLOYEES,
                        PJC::COL_SBM_BY => DC::TABLE_USERS,
                        PJC::COL_APV_BY => DC::TABLE_USERS,
                        PJC::COL_REJ_BY => DC::TABLE_USERS,
                        UC::COL_EMP_ID => DC::TABLE_EMPLOYEES,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->nullOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    PJC::COL_PJ_ID,
                    AC::COL_TSK_ID,
                    PJC::COL_PJ_TSK_ID,
                    PJC::COL_SBM_BY,
                    PJC::COL_APV_BY,
                    PJC::COL_REJ_BY,
                    UC::COL_EMP_ID,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
