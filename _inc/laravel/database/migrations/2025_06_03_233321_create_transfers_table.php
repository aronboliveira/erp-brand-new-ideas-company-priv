<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTransfersTable extends Migration
{
    private const TABLE = 'transfers';
    private const COL_EMPLOYEE = 'employee_id';
    private const COL_BRANCH = 'branch_id';
    private const COL_DEP = 'department_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();            // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE);              // ! CHANGED
            $table->uuid(self::COL_BRANCH);                // ! CHANGED
            $table->uuid(self::COL_DEP);            // ! CHANGED
            $table->date('transfer_date');
            $table->string('description')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);               // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_EMPLOYEE                      => DatabaseConstants::TABLE_EMPLOYEES,
                self::COL_BRANCH                        => DatabaseConstants::TABLE_BRANCHES,
                self::COL_DEP                           => DatabaseConstants::TABLE_DEPARTMENTS,
                DatabaseConstants::TABLE_CREATOR        => DatabaseConstants::TABLE_USERS,
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
                self::COL_EMPLOYEE,
                self::COL_BRANCH,
                self::COL_DEP,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
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
