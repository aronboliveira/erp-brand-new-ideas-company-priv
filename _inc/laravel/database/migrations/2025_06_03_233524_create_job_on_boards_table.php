<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\{Confirmation, Frequency, WorkShift};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// * this model has a poor semantic name still (inherited from legacy), but it aims to represent the proposed hiring (the 'aftermath' of an evaluation) of an applicant
class CreateJobOnBoardsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JB_BRD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_APLN_ID)->index();
            $table->boolean(AC::COL_WRK_AUTH_APV)->default(false)->nullable(); // ? work authorization approved, can be inherited from the AC::COL_APLN_ID relation, but this is the source of truth (aggregating both work permit and visa if applicable)
            $table->boolean(AC::COL_RLC_CMP)->default(false)->nullable(); // ? relocation compliant, can be inherited from the AC::COL_APLN_ID relation, but this is the source of truth
            $table->boolean(AC::COL_TRMS_ACPT)->default(false)->nullable(); // ? terms accepted, can be inherited from the AC::COL_APLN_ID relation, but this is the source of truth
            $table->boolean(AC::COL_NTC_PRD_CMP)->default(false)->nullable(); // ? notice period compliant
            $table->boolean(AC::COL_RFR_APV)->default(false)->nullable(); // ? referrer approved
            $table->boolean(AC::COL_FM_APV)->default(false)->nullable(); // ? form approved
            $table->boolean(AC::COL_RCT_APV)->default(false)->nullable(); // ? recruiter approved
            $table->boolean(AC::COL_MNG_APV)->default(false)->nullable(); // ? manager approved
            $table->boolean(AC::COL_EXP_APV)->default(false)->nullable(); // ? experience approved
            $table->boolean(AC::COL_SKL_APV)->default(false)->nullable(); // ? skills approved (aggregating skills and languages)
            $table->boolean(AC::COL_LCT_APV)->default(false)->nullable(); // ? localization approved (aggregating allowed countries and states)
            $table->boolean(AC::COL_ITV_APRV)->default(false)->nullable(); // ? interview approved
            $table->boolean(AC::COL_TEST_APV)->default(false)->nullable(); // ? tests approved
            $table->boolean(AC::COL_BG_CK_APV)->default(false)->nullable(); // ? background check approved, can be inherited from the AC::COL_APLN_ID relation, but this is the source of truth
            $table->boolean(AC::COL_DEI_APV)->default(false)->nullable(); // ? DEI approved, automatically set to null if AC::COL_IS_DEI is null/false on the linked row of DC::TABLE_JOBS OR if the AC::COL_DEI_CTG is null at DC::TABLE_JOB_APPS. IF AC::COL_IS_DEI for DC::TABLE_JOBS is true and AC::COL_DEI_CTG for DC::TABLE_JOB_APPS is not set, there should be a helper for emitting a corrupt application
            $table->date(AC::COL_JNG_DT)->nullable();
            $table->enum('status', array_column(Confirmation::cases(), 'value'))->default(Confirmation::Pending->value)->nullable();
            $table->integer(AC::COL_CNV_TO_EMP)->default(0); // ? pseudoboolean, constrained as 0 for evens or 0 itself, rest to 1
            $table->enum(AC::COL_JB_TP, array_column(WorkShift::cases(), 'value'))->nullable()->default(WorkShift::FullTime->value)->index();
            $table->integer(AC::COL_DYS_WK)->default(5)->nullable()->index(); // ? the NUMBER of days in a week, clamped between 1 and 7
            $table->json(AC::COL_WRK_DYS)->nullable(); // * constrained at model to only accept ->value from cases in the Weekday enum (after proper normalization)
            $table->unsignedDecimal('salary', 16, 2)->nullable(); // * in production this should never be null
            $table->enum(AC::COL_SLR_TP, array_column(Frequency::cases(), 'value'))->default(Frequency::Monthly->value)->nullable()->index();
            $table->string(AC::COL_SLR_DUR)->nullable(); // * apparently an arbitrary column for something like "10 months", "6 weeks", etc.
            $table->uuid('contract')->nullable()->index(); // ? nullable for initial tests
            $table->uuid('trainer')->nullable()->index();
            $table->json('trainings')->nullable(); // ? array of training ids or names that the onboarded job applicant must complete, validated at model level by existing in DC::TABLE_TRAINING
            $table->foreign(AC::COL_APLN_ID)
                ->references('id')
                ->on(DC::TABLE_JOB_APPS)
                ->restrictOnDelete();
            foreach (
                [
                    'contract' => DC::TABLE_CONTRACTS,
                    'trainer' => DC::TABLE_TRAINERS,
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
                    AC::COL_APLN_ID,
                    'contract',
                    'trainer',
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
