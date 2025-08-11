<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateMeetingsTable extends Migration
{
    private const TABLE = 'meetings';
    private const COL_BRANCH = 'branch_id';
    private const COL_DEP = 'department_id';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();         // ! CHANGED
            $table->uuid(self::COL_BRANCH);             // ! CHANGED
            $table->uuid(self::COL_DEP);         // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE);           // ! CHANGED
            $table->string('title');
            $table->date('date');
            $table->time('time');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);            // ! CHANGED
            foreach ([
                self::COL_BRANCH                        => DatabaseConstants::TABLE_BRANCHES,
                self::COL_DEP                    => DatabaseConstants::TABLE_DEPARTMENTS,
                self::COL_EMPLOYEE                      => DatabaseConstants::TABLE_EMPLOYEES,
                DatabaseConstants::TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_BRANCH,
                self::COL_DEP,
                self::COL_EMPLOYEE,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
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
