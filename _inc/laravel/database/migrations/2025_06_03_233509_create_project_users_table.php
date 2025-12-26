<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{ParticipationStatus, ProjectRole};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectUsersTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PRJ_USR;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(PJC::COL_PJ_ID)->index();
            $table->uuid(UC::COL_USER_ID)->index();
            $table->boolean(UC::COL_IA)->default(false)->nullable()->index(); // ? nullable for tests, is_active, automatically converted to false when COL_LFT_AT is set to true
            $table->uuid(PJC::COL_INV_BY)->nullable()->index(); // ? it the inviter is of 'type' as 'super admin', 'admin' or 'company' OR the user has role as 'owner', 'admin', 'manager' in the row, then there is the user is automatically accepted (even if through code input or url), converting COL_ACC_AT to now(), COL_JND_AT to now(), COL_ACC_BY to the inviter id and COL_INV_STT to Accepted->value
            $table->timestamp(PJC::COL_INV_AT)->nullable()->index();
            $table->enum(PJC::COL_INV_STT, array_column(ParticipationStatus::cases(), 'value'))->default(ParticipationStatus::Pending->value)->nullable()->index(); // ? nullable for tests, enforced at model
            $table->string(PJC::COL_INV_URL, 254)->nullable()->index();
            $table->string(PJC::COL_INV_CD, 512)->nullable()->unique(); // ? unique invitation code, nullable for tests, should be automatically generated at model level with pattern INV-{UUID}-{timestamp} (available as a public const in the ProjectUser model)
            $table->timestamp(PJC::COL_JND_AT)->nullable();
            $table->timestamp(PJC::COL_ACC_AT)->nullable();
            $table->uuid(PJC::COL_ACC_BY)->nullable()->index(); // ? MUST be a user whereIn('type', ['company', 'vendor', 'super admin', 'admin', 'hr']) OR a user with role as 'owner', 'admin', 'manager' in the row
            $table->timestamp(PJC::COL_LFT_AT)->nullable();
            $table->uuid(PJC::COL_RMV_BY)->nullable()->index(); // ? MUST be a user whereIn('type', ['company', 'vendor', 'super admin', 'admin', 'hr']) OR a user with role as 'owner', 'admin', 'manager' in the row
            $table->boolean(PJC::COL_IS_TMP)->default(false)->nullable()->index(); // ? nullable for tests
            $table->timestamp(PJC::COL_EXP_AT)->nullable()->index(); // ? this not null only when COL_IS_TMP is true
            $table->timestamp(PJC::COL_LST_EDT_AT)->nullable()->index();
            $table->enum('role', array_column(ProjectRole::cases(), 'value'))->default(ProjectRole::Member->value)->nullable()->index(); // ? nullable for tests, enforced at model
            $table->boolean(PJC::COL_CAN_WRT_OWN)->default(true)->nullable(); // ? nullable for tests, automatically convert to true for project leaders, users whereIn('type', ['company', 'vendor', 'super admin', 'hr', 'accountant', 'admin']) OR users with role as 'owner', 'admin', 'manager', 'developer', 'contributor' in the row
            $table->boolean(PJC::COL_CAN_WRT_OTH)->default(false)->nullable(); // ? nullable for tests, automatically convert to true for project leaders, users whereIn('type', ['company', 'super admin', 'admin']) OR users with role as 'owner', 'admin' in the row
            $table->boolean(PJC::COL_CAN_RD_OTH)->default(false)->nullable(); // ? nullable for tests, automatically convert to true for project leaders, users whereIn('type', ['company', 'super admin', 'admin', 'hr', 'accountant']) OR users with role as 'owner', 'admin', 'manager', 'developer', 'contributor' in the row
            $table->boolean(PJC::COL_IS_PRJ_LD)->default(false)->nullable()->index(); // ? nullable for tests, only one per project should be true, enforced at model, automatically converts COL_CAN_WRT_OWN, COL_CAN_WRT_OTH and COL_CAN_RD_OTH to true when set to true
            $table->decimal(PJC::COL_HR_PRC, 16, 4)->default(0.0000)->nullable(); // ? nullable for tests, hard hypothetical limit on the PJC::COL_BUDGET column of the linked project
            $table->decimal(PJC::COL_BLB_HRS, 16, 4)->default(0.0000)->nullable(); // ? nullable for tests
            $table->boolean(PJC::COL_ALW_EML_NTF)->default(true)->nullable(); // ? nullable for tests
            $table->boolean(PJC::COL_ALW_PSH_NTF)->default(true)->nullable(); // ? nullable for tests
            $table->boolean(PJC::COL_ALW_MNT_NTF)->default(true)->nullable(); // ?w nullable for tests
            $table->boolean(PJC::COL_ALW_STT_UPD_NTF)->default(true)->nullable(); // ? nullable for tests
            $table->decimal(PJC::COL_TTL_HRS, 16, 4)->default(0.0000)->nullable(); // ? nullable for tests
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('preferences')->nullable(); // ? aggregates all user-project specific preferences, including these already listed here as columns and other flexible ones
            foreach (
                [
                    PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                    UC::COL_USER_ID => DC::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete();
            foreach (
                [
                    PJC::COL_INV_BY,
                    PJC::COL_ACC_BY,
                    PJC::COL_RMV_BY,
                ] as $col
            )
                $table->foreign($col)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
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
                    UC::COL_USER_ID,
                    PJC::COL_INV_BY,
                    PJC::COL_ACC_BY,
                    PJC::COL_RMV_BY,
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
