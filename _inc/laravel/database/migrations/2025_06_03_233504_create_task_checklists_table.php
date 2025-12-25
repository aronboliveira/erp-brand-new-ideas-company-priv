<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasNullableAuditColumns, TaskConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTaskChecklistsTable extends Migration
{
    use HasNullableAuditColumns, TaskConnected;
    private const TABLE = DC::TABLE_TSK_CHKL;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name', 254)->nullable()->index(); // ? if not provided, then randomly generate as "TSK-CHKL-{Str::uuid}", checking for existence in the table before finishing
                $table->text('description')->nullable();
                $table->string('url', 248)->nullable()->index(); // ? optional url link to the checklist, like a Notion or Trello page
                $table->boolean('completed')->nullable()->default(false)->index(); // ? enforced at boot/saving as boolean
                $table->dateTime(PJC::COL_CMP_AT)->nullable()->index(); // ? completed_at, MUST be null if not completed, else it's the date/time when it was marked as completed
                $table->date(PJC::COL_D_DATE)->nullable()->index(); // ? MUST be at maximum the date of the linked task's date, else it's enforced as such; if null, then place as the date of the task upon saving
                $table->boolean(AC::COL_IS_FV)->nullable()->default(false)->index(); // ? enforced at boot/saving as boolean
                $table->enum(UC::COL_U_TP, array_column(UserType::cases(), 'value'))->default(UserType::Customer->value); // * this field is kind of redundant considering the DC::COL_TABLE_CREATOR here, PJC::COL_ASGN + PJC::COL_AOM_ID in the linked task, but it can help with quick finding/cache if it's frequently selected ++ the possibility of the creator being the system itself; was already in legacy code
                $table->unsignedTinyInteger('status')->default(0)->index();
                $table->unsignedTinyInteger('order')->default(0); // ? order to be sorted among other checklists linked to the same task, enforced at boot/saving as non-negative integer with a maximum as the number of checklists linked to the same task during the time of its creation
                $this->addTaskColumns($table, unique: false, nullable: false, cascade: true);
                $table->uuid('stage')->nullable()->index(); // ? reference to a TaskStage id, if applicable, MUST be a stage linked to the same task as this checklist is linked to, else it's nullified
                $table->uuid('notification')->nullable()->index(); // ? reference to a Notification id, if applicable, for tracking purposes
                $table->json('involved')->nullable(); // ? list of user id/names or employee id/names involved in this checklist, MUST be a subgroup of those involved in the linked task, filtering as such, or the whole group itself
                $table->json('attachments')->nullable();
                $table->json('tags')->nullable();
                $table->json('positioning')->nullable(); // ? json defining the positioning of this checklist in the task's checklist UI, like x/y coordinates, collapsed/expanded state, etc.
                foreach (
                    [
                        'stage' => DC::TABLE_TSK_STGS,
                        'notification' => DC::TABLE_NTF,
                    ] as $col => $tb
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tb)
                        ->nullOnDelete();
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
                    'stage',
                    'notification',
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
