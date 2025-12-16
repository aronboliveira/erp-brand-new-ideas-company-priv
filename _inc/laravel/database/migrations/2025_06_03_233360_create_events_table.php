<?php

use App\Config\Constants\{ActivitiesConstants as AC, CompaniesConstants as CC, DatabaseConstants as DC};
use App\Traits\{BranchConnected, EmployeeConnected, HasNullableAuditColumns, StoresPlanning};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEventsTable extends Migration
{
    use BranchConnected, EmployeeConnected, HasNullableAuditColumns, StoresPlanning;
    private const TABLE = DC::TABLE_EVENTS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(CC::COL_CP_ID)->nullable()->index(); // ? nullable for tests
            $this->addPlanningColumns($table, nullableTitle: true);
            $table->string('responsible', 255)->nullable()->index(); // * name of the person responsible for the event
            $table->uuid(AC::COL_RES_ID)->nullable()->index(); // ? a responsible is not necessary a registered user
            $table->json('participants')->nullable(); // * list of participants' names and contacts, and the id they are linked to if registered in the system
            $table->json('organizers')->nullable(); // * list of organizers' names and contacts, and the id they are linked to if registered in the system, the responsible must be included through the model
            $table->json('confirmed')->nullable(); // * list of confirmed attendees' names and contacts, and the id they are linked to if registered in the system (booted and saving will check in the participants + organizers json to update this column)
            $table->json('gifts')->nullable(); // * list of gifts to bring, buy, or prepare for the event
            $table->json('sponsors')->nullable(); // * list of sponsors' names and contacts, and the id they are linked to if registered in the system
            $table->string('color')->default('#3788d8');
            $table->text('description')->nullable();
            $this->addBranchColumns($table, nullable: true, unique: false);
            $this->addEmployeeColumns($table, unique: false, nullable: true);
            foreach (
                [
                    CC::COL_CP_ID => DC::TABLE_USERS,
                    AC::COL_RES_ID => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropBranchColumnForeign($table, self::TABLE);
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            $this->dropPlanningColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    CC::COL_CP_ID,
                    AC::COL_RES_ID,
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
