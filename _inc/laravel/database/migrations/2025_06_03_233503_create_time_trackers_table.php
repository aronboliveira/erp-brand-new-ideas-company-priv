
<?php

use App\Config\Constants\{ActivitiesConstants as AC, CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTimeTrackersTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TM_TRK;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 254)->nullable()->index();
            $table->text('description')->nullable();
            $table->uuid(PJC::COL_PJ_ID)->nullable();
            $table->uuid(AC::COL_TSK_ID)->nullable();
            $table->uuid(UC::COL_USER_ID)->nullable()->index(); // ? it can be linked to a user as the task doer or a team leader
            $table->uuid(CC::COL_DEP_ID)->nullable()->index(); // ? it can be linked to a department
            $table->text(PJC::COL_TAG_ID)->nullable(); // * not clear yet
            $table->unsignedTinyInteger(PJC::COL_IS_BLB)->default(0)->index();
            $table->unsignedDecimal(PJC::COL_BLB_HRS, 10, 2)->default(0);
            $table->unsignedDecimal(PJC::COL_HRS_WTT_TIMER, 10, 2)->nullable()->comment('Manually entered hours'); // ? enforce at boot/saving to never be more than AC::COL_TTL_TIME
            $table->unsignedDecimal(PJC::COL_HR_PRC, 10, 2)->default(0)->nullable()->index(); // ? if is_billable is false, then this is nullified
            $table->string('currency', 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? this CANNOT be null when col_hr_prc is defined 
            $table->dateTime(AC::COL_ST_TIME)->nullable();
            $table->dateTime(AC::COL_E_TIME)->nullable(); // * it's kind of redundant to have AC::COL_TTL_TIME too, but keeping for legacy reasons. If the difference from COL_ST_TIME to COL_E_TIME is more than COL_TTL_TIME, then the extra time is added to COL_TTL_TIME;
            $table->string(AC::COL_TTL_TIME)->default('0'); // * it's not clear what format to use here, so string for now, aligned with legacy code
            $table->string(AC::COL_IA)->default('1')->index(); // * it's not clear why this was not a boolean or a unsignedTinyInt on legacy code, but keeping for legacy
            $this->addAuditColumns($table);
            foreach (
                [
                    PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                    UC::COL_USER_ID => DC::TABLE_USERS,
                ] as $col => $tableName
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tableName)
                    ->cascadeOnDelete();
            foreach (
                [
                    AC::COL_TSK_ID => DC::TABLE_PROJ_TSKS,
                    CC::COL_DEP_ID => DC::TABLE_DEPARTMENTS,
                ] as $col => $tableName
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tableName)
                    ->nullOnDelete();
            $table->json('tags')->nullable();
            $table->json('attachments')->nullable();
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
                    CC::COL_DEP_ID,
                    UC::COL_USER_ID,
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
