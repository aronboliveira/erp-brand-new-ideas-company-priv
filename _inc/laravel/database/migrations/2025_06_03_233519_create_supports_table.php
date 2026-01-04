<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SupportsConstants as SC
};
use App\Enums\{AppModuleType, CaseStatus, PriorityLevel, Visibility};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSupportsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_SUPPORTS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject', 1024)->index();
            $table->enum('module', array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Support->value)->nullable()->index(); // ? enforced at model level
            $table->uuid(SC::COL_TKT_CR)->index();
            $table->string(SC::COL_TKT_CD)->nullable(); // * ticket code, alphanumeric unique code for the support case; if null, automatically written as SUP-TKT-{UUID}-{YYYYMMDD}
            $table->uuid('user')->index(); // ? the requester who called
            $table->uuid('client')->nullable()->index(); // ? the client related to the support case, if any
            $table->enum('priority', array_values(array_filter(array_column(PriorityLevel::cases(), 'value'), fn($v) => $v !== PriorityLevel::None->value)))->default(PriorityLevel::Medium->value)->index();
            $table->text('description')->nullable();
            $table->enum('visibility', array_values(array_column(Visibility::cases(), 'value')))->default(Visibility::Private->value)->nullable()->index();
            $table->date(PJC::COL_S_DT); // ? start_date, in which the work becomes active, NEVER lower than COL_E_DT
            $table->date(PJC::COL_E_DT); // ? end_date, in which it should be expired, NEVER lower than today IF the status is not Closed/Archived/Deleted
            $table->string('status')->default('0'); // * this should map to the CaseStatus enum indexes of the cases. It is kept as such due to legacy compatibility. Should represent a number never less than 0, and never more than the highest index of case.
            $table->enum(SC::COL_STT_LB, array_values(array_column(CaseStatus::cases(), 'value')))->default(CaseStatus::New->value)->index(); // ? this and 'status' are redundant for now while we update. Should be aligned according to the CaseStatus enum values. This being the string caseitself, while 'status' is the index
            $table->unsignedSmallInteger(SC::COL_REOPEN_CT)->default(0); // ? counts how many times the case was reopened
            $table->date(SC::COL_SVD_AT)->nullable(); // ? date when the case was marked as solved, automatically triggered by changing the status to [CaseStatus::Resolved->value]; if null, also by [CaseStatus::Closed->value]; any other status change or save nullifies this column; should NEVER be lower than COL_SBM_AT or COL_S_DT, and NEVER greater than COL_E_DT
            $table->uuid(PJC::COL_SBM_BY)->nullable(); // ? the submitter who created the support case (can be different from the requester)
            $table->date(PJC::COL_SBM_AT)->nullable(); // ? should never be lower than COL_S_DT and never higher than COL_E_DT
            $table->uuid(SC::COL_ASG_BY)->nullable(); // ? the assigner who assigned the case to someone
            $table->uuid(SC::COL_ASG_TO)->nullable()->index(); // ? the assignee who is assigned to handle the case
            $table->date(SC::COL_ASG_AT)->nullable(); // ? should never be lower than COL_S_DT neither COL_SBM_AT and never higher than COL_SBM_AT neither COL_E_DT
            $table->uuid(SC::COL_CLSD_BY)->nullable(); // ? the closer who closed the case
            $table->date(SC::COL_CLSD_AT)->nullable(); // ? should never be lower than COL_SBM_AT neither COL_S_DT and never higher than COL_E_DT
            $table->unsignedSmallInteger(AC::COL_TTL_TIME)->default(0)->nullable(); // ? nullable for tests, enforced at model level; total time spent on the case, in minutes
            $table->uuid('email')->nullable()->index(); // ? email that was sent for the support case (if any), storing data about the email itself
            $table->uuid('notification')->nullable(); // ? notification that was sent for the support case (if any), storing data about the notification itself
            $table->uuid('task')->nullable()->index(); // ? task that may be linked to the support case (if any)
            $table->uuid('bug')->nullable()->index(); // ? bug that may be linked to the support case (if any)
            $table->string('attachment', 254)->nullable(); // * this is never made clear, but we will filter by being a file_path (where file_exists) within the local storage of the server OR a URL starting with https:// + trusted domain (the env('APP_URL') one OR known trusted cloud storage/CDN ones) OR ids to the Document table
            $table->foreign('user')
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete(); // ? dinamically changed to restrictOnDelete if in_array('status', [CaseStatus::Open->value, CaseStatus::InProgress->value, CaseStatus::WaitingCustomer->value, CaseStatus::OnHold->value, CaseStatus::WaitingVendor->value, CaseStatus::WaitingThirdParty->value, CaseStatus::Escalated->value, CaseStatus::Reopened->value])
            $table->foreign(SC::COL_TKT_CR)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->restrictOnDelete();
            foreach (
                [
                    'client' => DC::TABLE_CLIENTS,
                    PJC::COL_SBM_BY => DC::TABLE_USERS,
                    SC::COL_ASG_BY => DC::TABLE_USERS,
                    SC::COL_ASG_TO  => DC::TABLE_USERS,
                    SC::COL_CLSD_BY => DC::TABLE_USERS,
                    'email' => DC::TABLE_EMAILS,
                    'notification' => DC::TABLE_NTF,
                    'task' => DC::TABLE_TASKS,
                    'bug' => DC::TABLE_BUGS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->nullOnDelete();
            $table->json(SC::COL_OTHER_ATTACHMENTS)->nullable(); // * json array of strings to be filtered with the same constraints as the main attachment
            $table->json(SC::COL_OTHER_VENDORS)->nullable(); // * json array of Vendors (DC::TABLE_VENDORS OR DC::TABLE_USERS where('type', 'vendor')) ids that are involved in the support case
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'user',
                    'client',
                    SC::COL_TKT_CR,
                    PJC::COL_SBM_BY,
                    SC::COL_ASG_BY,
                    SC::COL_ASG_TO,
                    SC::COL_CLSD_BY,
                    'email',
                    'notification',
                    'task',
                    'bug',
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
