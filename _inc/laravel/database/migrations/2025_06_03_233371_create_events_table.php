<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEventsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_EVENTS;
    private const DATE = 'date';
    private const COL_BRANCH = 'branch_id';
    private const COL_DEPARTMENT = 'department_id';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid(self::COL_BRANCH); // ! CHANGED
            $table->uuid(self::COL_DEPARTMENT);
            $table->uuid(self::COL_EMPLOYEE);
            $table->string('title');
            $table->date('start_' . self::DATE);
            $table->date('end_' . self::DATE);
            $table->string('color');
            $table->text('description')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_BRANCH                        => DatabaseConstants::TABLE_BRANCHES,
                self::COL_DEPARTMENT                    => DatabaseConstants::TABLE_DEPARTMENTS,
                self::COL_EMPLOYEE                      => DatabaseConstants::TABLE_EMPLOYEES,
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
                self::COL_BRANCH,
                DatabaseConstants::TABLE_DEPARTMENTS,
                DatabaseConstants::TABLE_EMAIL_TEMPLATES,
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
