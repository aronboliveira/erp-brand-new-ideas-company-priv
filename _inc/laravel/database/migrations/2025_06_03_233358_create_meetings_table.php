<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{BranchConnected, EmployeeConnected, HasNullableAuditColumns, StoresPlanning};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateMeetingsTable extends Migration
{
    use BranchConnected, EmployeeConnected, HasNullableAuditColumns, StoresPlanning;
    private const TABLE = DC::TABLE_MEETINGS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('code')->unique()->index()->nullable(); // ? nullable for testing purposes
            $this->addPlanningColumns($table, nullableTitle: false, nullableFixedDate: false, nullableFixedTime: false, nullableDept: true, onDeleteDept: 'set null');
            $this->addEmployeeColumns($table, false, true);
            $this->addBranchColumns($table, false, true);
            $this->addAuditColumns($table);
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropBranchForeign($table, self::TABLE);
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    CC::COL_DEP_ID,
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
