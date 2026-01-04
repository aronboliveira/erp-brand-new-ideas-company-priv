<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, Visibility};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateCompetenciesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CMPT;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 128)->unique(); // * unique alphanumeric code for the competency, automatically generated as CMPT-{UUID}, checking with do/while for uniqueness
            $table->string('name')->index();
            $table->enum('module', array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->nullable()->index(); // ? a possible module to which this competency belongs, like 'financial', 'hrm' or 'infrastructure' // ? enforced at model level
            $table->string('category')->nullable()->index(); // ? freely note about category
            $table->string('family')->nullable()->index(); // ? freely note about family, to emulate a subcategory
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('type')->nullable(); // * this was never made clear... keeping for legacy compatibility
            $table->enum('visibility', array_column(Visibility::cases(), 'value'))->default(Visibility::Public->value)->nullable()->index(); // ? enforced at model level
            $table->uuid(PJC::COL_SBM_BY)->nullable()->index(); // ? the submitter who created the competency
            $table->date(PJC::COL_SBM_AT)->nullable();
            $table->uuid(PJC::COL_APV_BY)->nullable()->index(); // ? should be a user whereIn('type', [UserType::Admin->value, UserType::SuperAdmin->value, UserType::Company->value, UserType::Hr->value]) (else throw to reject at model level)
            $table->uuid(PJC::COL_APV_AT)->nullable()->index();
            $table->uuid(PJC::COL_REJ_BY)->nullable()->index(); // ? should be a user whereIn('type', [UserType::Admin->value, UserType::SuperAdmin->value, UserType::Company->value, UserType::Hr->value]) (else throw to reject at model level)
            $table->uuid(PJC::COL_REJ_AT)->nullable()->index();
            $table->json('jobs')->nullable(); // ? json array of job ids (from DC::TABLE_JOBS) related to this competency
            $table->json('projects')->nullable(); // ? json array of project ids (from DC::TABLE_PROJECTS) related to this competency
            $table->json('companies')->nullable(); // ? json array of company ids (from DC::TABLE_USERS whereIn('type', [UserType::Company->value, UserType::Vendor->value])) registered to look for this competency
            $table->json('branches')->nullable(); // ? json array of branch ids (from DC::TABLE_BRANCHES) looking for this competency
            $table->json('departments')->nullable(); // ? json array of department ids (from DC::TABLE_DEPARTMENTS) related to this competency
            $table->json('levels')->nullable(); // ? json array of strings representing the accept levels reflected by the IndicatorTechnicalLevel enum ('None', 'Beginner', 'Intermediate', 'Advanced', 'Expert'); if null, means all level cases are acceptable
            $table->json('skills')->nullable(); // ? json array of desired skills, as raw scalar strings, validated file_paths in the local storage, URLs to safe https:// + secure domains or Document id of rows in the DC::TABLE_DOCUMENTS table
            $table->json('certifications')->nullable(); // ? json array of desired certifications, as raw scalar strings, validated file_paths in the local storage, URLs to safe https:// + secure domains or Document id of rows in the DC::TABLE_DOCUMENTS table
            $table->json('requirements')->nullable(); // ? json array of desired additional requirements, as raw scalar strings, validated file_paths in the local storage, URLs to safe https:// + secure domains or Document id of rows in the DC::TABLE_DOCUMENTS table
            $table->json('behaviors')->nullable(); // ? json array of desired behaviors, as raw scalar strings, validated file_paths in the local storage, URLs to safe https:// + secure domains or Document id of rows in the DC::TABLE_DOCUMENTS table
            $table->json('tags')->nullable(); // ? json array of scalar strings representing tags related to the competency
            foreach (
                [PJC::COL_SBM_BY => DC::TABLE_USERS, PJC::COL_APV_BY => DC::TABLE_USERS, PJC::COL_REJ_BY => DC::TABLE_USERS]
                as $col => $fkTable
            )
                $table->foreign($col)->references('id')->on($fkTable)->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach ([PJC::COL_SBM_BY, PJC::COL_APV_BY, PJC::COL_REJ_BY] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Throwable) {
                    Log::warning('Failed to drop foreign key on ' . self::TABLE . '.' . $col);
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
