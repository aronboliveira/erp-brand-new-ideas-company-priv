<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{DEICategory, EvaluationStatus, JobLevel, Visibility, WorkContractType, WorkPresence, WorkShift};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JOBS;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 1024)->index();
            $table->string('slug', 254)->nullable()->unique();
            $table->string('code', 128)->nullable()->unique();
            $table->uuid('category')->nullable()->index();
            $table->uuid('announcement')->nullable()->index();
            $table->uuid('company')->nullable()->index();
            $table->string('country', 64)->nullable()->index();
            $table->string('state', 1024)->nullable()->index();
            $table->string('city', 1024)->nullable()->index();
            $table->text('address')->nullable();
            $table->uuid('branch')->index();
            $table->uuid('department')->nullable()->index();
            $table->uuid('designation')->nullable()->index();
            $table->enum('level', array_column(JobLevel::cases(), 'value'))->default(JobLevel::Staff->value)->nullable()->index();
            $table->enum(PJC::COL_PRS_TP, array_column(WorkPresence::cases(), 'value'))->nullable()->index();
            $table->enum(PJC::COL_CTC_TP, array_column(WorkContractType::cases(), 'value'))->nullable()->index();
            $table->enum(PJC::COL_SHFT_TP, array_column(WorkShift::cases(), 'value'))->nullable()->index();
            $table->unsignedDecimal(AC::COL_EXP_SLR, 15, 2)->nullable();
            $table->string(AC::COL_EXP_SLR_CURR, 5)->nullable()->default(SC::DEF_SITE_CURRENCY_ID)->index();
            $table->uuid(AC::COL_RCT_ID)->nullable()->index();
            $table->string(AC::COL_RCT_NM, 254)->nullable()->index();
            $table->string(AC::COL_RCT_EML, 254)->nullable()->index();
            $table->string(AC::COL_RCT_PST, 254)->nullable()->index();
            $table->uuid(AC::COL_MNG_ID)->nullable()->index();
            $table->string(AC::COL_MNG_NM, 254)->nullable()->index();
            $table->string(AC::COL_MNG_EML, 254)->nullable()->index();
            $table->string(AC::COL_MNG_PST, 254)->nullable()->index();
            $table->string(AC::COL_RCV_EML, 254)->nullable()->index();
            $table->uuid(AC::COL_RCV_TMP)->nullable()->index();
            $table->string(AC::COL_RES_EML, 254)->nullable()->index();
            $table->uuid(AC::COL_RSP_TMP)->nullable()->index();
            $table->unsignedTinyInteger(AC::COL_MIN_EXP_Y)->default(2)->nullable();
            $table->text('description')->nullable();
            $table->text('requirement')->nullable();
            $table->uuid(AC::COL_RQ_DOC)->nullable();
            $table->text('skill')->nullable();
            $table->uuid(AC::COL_SKL_DOC)->nullable();
            $table->float(AC::COL_MIN_SKL_MTCH)->default(70.0)->nullable();
            $table->unsignedTinyInteger(AC::COL_MAX_NTC_PD)->default(90)->nullable();
            $table->boolean(AC::COL_RQ_WK_AUTH)->default(false)->nullable();
            $table->boolean(AC::COL_RQ_VS)->default(false)->nullable();
            $table->boolean(AC::COL_RQ_RLC)->default(false)->nullable();
            $table->boolean(AC::COL_RLC_PV)->default(false)->nullable();
            $table->unsignedTinyInteger(AC::COL_MAX_RLC_DAYS)->default(60)->nullable();
            $table->boolean(AC::COL_RQ_BKG_CK)->default(false)->nullable();
            $table->boolean(AC::COL_RQ_CV_LT)->default(false)->nullable();
            $table->boolean(AC::COL_RQ_PRTF)->default(false)->nullable();
            $table->unsignedInteger(AC::COL_MAX_AP)->default(1024)->nullable();
            $table->unsignedInteger(AC::COL_CUR_AP)->default(0)->nullable();
            $table->boolean(AC::COL_IS_DEI)->default(false)->nullable();
            $table->unsignedBigInteger(AC::COL_VW_CT)->default(0)->nullable();
            $table->timestamp(AC::COL_LST_AP_AT)->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->date(PJC::COL_S_DT)->nullable();
            $table->date(PJC::COL_E_DT)->nullable();
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Draft->value)->nullable();
            $table->enum('visibility', array_column(Visibility::cases(), 'value'))->default(Visibility::Draft->value)->nullable();
            $table->string(AC::COL_CT_QT)->nullable();
            $table->uuid(AC::COL_FM_ID)->nullable()->index();
            $table->uuid('project')->nullable()->index();
            $table->uuid('milestone')->nullable()->index();
            $table->uuid('goal')->nullable()->index();
            $table->json('benefits')->nullable();
            $table->json('incentives')->nullable();
            $table->json('stages')->nullable();
            $table->json(AC::COL_RQ_CRT)->nullable();
            $table->json(AC::COL_PRF_CRT)->nullable();
            $table->json(AC::COL_RQ_SKL)->nullable();
            $table->json(AC::COL_PRF_SKL)->nullable();
            $table->json(AC::COL_RQ_LG)->nullable();
            $table->json(AC::COL_PRF_LG)->nullable();
            $table->json(AC::COL_ACP_WK_AUTH)->nullable();
            $table->json(AC::COL_ALW_CTR)->nullable();
            $table->json(AC::COL_ALW_ST)->nullable();
            $table->enum(AC::COL_DEI_CTG, array_column(DEICategory::cases(), 'value'))->nullable();
            $table->json(AC::COL_DEI_CRT)->nullable();
            $table->json(AC::COL_DEI_DOCS)->nullable();
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $table->json('platforms')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid(AC::COL_HRD_ID)->nullable()->index();
            $table->string('hired', 254)->nullable();
            $table->string('applicant')->nullable();
            $table->uuid(AC::COL_APL_ID)->nullable();
            $table->foreign('branch')
                ->references('id')
                ->on(DC::TABLE_BRANCHES)
                ->restrictOnDelete();
            foreach (
                [
                    'company' => DC::TABLE_USERS,
                    'category' => DC::TABLE_JOB_CATS,
                    'announcement' => DC::TABLE_ANC,
                    'department' => DC::TABLE_DEPARTMENTS,
                    'designation' => DC::TABLE_DESIGNS,
                    AC::COL_RCT_ID => DC::TABLE_USERS,
                    AC::COL_MNG_ID => DC::TABLE_USERS,
                    AC::COL_RCV_TMP => DC::TABLE_EMAIL_TEMPLATES,
                    AC::COL_RSP_TMP => DC::TABLE_EMAIL_TEMPLATES,
                    AC::COL_RQ_DOC => DC::TABLE_DOCS,
                    AC::COL_SKL_DOC => DC::TABLE_DOCS,
                    AC::COL_FM_ID => DC::TABLE_FORM_BUILD,
                    'project' => DC::TABLE_PROJECTS,
                    'milestone' => DC::TABLE_MSS,
                    'goal' => DC::TABLE_GL,
                    AC::COL_HRD_ID => DC::TABLE_USERS,
                    AC::COL_APL_ID => DC::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')->on($tbl)
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
                    'branch',
                    'company',
                    'category',
                    'announcement',
                    'department',
                    'designation',
                    AC::COL_RCT_ID,
                    AC::COL_MNG_ID,
                    AC::COL_RCV_TMP,
                    AC::COL_RSP_TMP,
                    AC::COL_RQ_DOC,
                    AC::COL_SKL_DOC,
                    AC::COL_FM_ID,
                    'project',
                    'milestone',
                    'goal',
                    AC::COL_HRD_ID,
                    AC::COL_APL_ID,
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
