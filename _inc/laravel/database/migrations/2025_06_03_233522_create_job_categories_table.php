<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateJobCategoriesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JOB_CATS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title', 1024)->index();
                $table->string('slug', 254)->nullable()->unique(); // ? automatically generated based on snake-cased sanitized title or job_cat_{timestamp}
                $table->string('code', 128)->nullable()->unique(); // ? optional unique code for the job category, automatically generated if not provided as JB-CAT-{Str::uuid}-{timestamp}
                $table->string('field', 254)->nullable()->index(); // ? e.g. IT, HR, Finance, etc.
                $table->text('description')->nullable();
                $table->text('requirements')->nullable();
                $table->text('skills')->nullable();
                $table->text('responsibilities')->nullable();
                $table->text('benefits')->nullable();
                $table->text('incentives')->nullable();
                $table->text('notes')->nullable();
                $table->boolean(AC::COL_IA)->default(true)->nullable()->index();
                $table->json(PJC::COL_ACP_LVLS)->nullable(); // ? array of accepted job levels, e.g. ['internship', 'junior', 'mid', 'senior', 'lead', 'manager', 'director', 'vp', 'c-level'], based on => $table->enum(PJC::COL_ACP_LVLS, array_column(JobLevel::cases(), 'value'))->nullable()->index(); // ? e.g. ['internship', 'junior', 'mid', 'senior', 'lead', 'manager', 'director', 'vp', 'c-level']
                $table->json(PJC::COL_ACP_PRS)->nullable(); // ? array of accepted presence types, e.g. ['onsite', 'remote', 'hybrid', 'etc'.], based on => $table->enum(PJC::COL_PRS_TP, array_column(WorkPresence::cases(), 'value'))->nullable()->index(); // ? array of accepted presence types, e.g. ['onsite', 'remote', 'hybrid', 'etc'.]
                $table->json(PJC::COL_ACP_CTC_TP)->nullable(); // ? array of accepted contract types, e.g. ['clt', 'pj', 'freelancer', 'etc'.] => based on => $table->enum(PJC::COL_ACP_CTC_TP, array_column(WorkContractType::cases(), 'value'))->nullable()->index(); // ? array of accepted contract types, e.g. ['clt', 'pj', 'freelancer', 'etc'.]
                $table->json(PJC::COL_ACP_SHFT)->nullable(); // ? array of accepted shift types, e.g. ['day', 'night', 'flexible', 'part-time', 'etc'.] => based on => $table->enum(PJC::COL_ACP_SHFT, array_column(WorkShift::cases(), 'value'))->nullable()->index(); // ? array of accepted shift types, e.g. ['day', 'night', 'flexible', 'part-time', 'etc'.]
                $table->json('certifications')->nullable(); // ? array of required certifications, as names/ids possibly referencing rows in DC::TABLE_DOCS
                $table->json('attachments')->nullable(); // ? array of 'name', 'id' or 'file_path' for DC::TABLE_DOCS rows
                $table->json('metadata')->nullable();
                $table->json('companies')->nullable(); // ? array of the 'name' or 'id' of users whereIn('type', ['company', 'vendor']) that can accept this job category
                $table->json('branches')->nullable(); // ? array of the 'name' or 'id' of branches where this job category is available
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
