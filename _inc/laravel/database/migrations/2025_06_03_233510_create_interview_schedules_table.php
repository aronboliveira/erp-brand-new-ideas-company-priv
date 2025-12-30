<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{HasNullableAuditColumns, TaskConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// todo update journal,custom_question migrations
class CreateInterviewSchedulesTable extends Migration
{
    use HasNullableAuditColumns, TaskConnected;
    private const TABLE = DC::TABLE_ITV_SCD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('candidate')->index();
            $table->uuid('employee')->index(); // ? the interviewer, MUST have a valid UC::COL_EMP_ID, else the instance is rejected
            $table->date('date');
            $table->time('time');
            $table->string('url', 254)->nullable()->index(); // ? can be online interview link
            $table->string('location', 254)->nullable()->index(); // ? physical location
            $table->string(AC::COL_RL_TTL)->nullable()->index(); // ? role_title
            $table->text(AC::COL_RL_DSC)->nullable();
            $table->text('comment')->nullable();
            $table->string(AC::COL_EMP_RES)->nullable();
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable();
            $this->addTaskColumns($table, unique: false, nullable: true, cascade: false); // ? the interview can be a task for a user, mainly if the interviewer is an employee
            $table->uuid(PJC::COL_PJ_ID)->nullable()->index(); // ? can be related to a larger project, like a team overhaul
            $table->uuid('document')->nullable()->index(); // ? related document, like a requisition form
            $table->uuid('todo')->nullable()->index(); // ? related to-do item for the employee  // ? the interview can be linked to a to-do of the employee as an user. If not null, it should be checked if the 'employee' uuid column and the UC::COL_USER_ID in the DC::TABLE_USR_TD are the same, else it's nullified at boot/save
            $table->json('steps')->nullable(); // ? metadata about the steps
            $table->json('results')->nullable(); // ? metadata about the results
            $table->json('attachments')->nullable(); // ? files, links, etc.
            $table->json('involved')->nullable(); // ? people involved in the hiring process, must forcefully include the interviewer
            $table->foreign('candidate')->references('id')->on(DC::TABLE_JOB_APPS)->cascadeOnDelete();
            $table->foreign('employee')->references('id')->on(DC::TABLE_USERS)->restrictOnDelete();
            $table->foreign(PJC::COL_PJ_ID)->references('id')->on(DC::TABLE_PROJECTS)->nullOnDelete();
            $table->foreign('document')->references('id')->on(DC::TABLE_DOCS)->nullOnDelete();
            $table->foreign('todo')->references('id')->on(DC::TABLE_USR_TD)->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropTaskColumnForeign($table, self::TABLE);
            foreach (
                [
                    'candidate',
                    'employee',
                    PJC::COL_PJ_ID,
                    'document',
                    'todo'
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
