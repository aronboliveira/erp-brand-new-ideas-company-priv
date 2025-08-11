<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLoansTable extends Migration
{
    private const ENTITY = 'loan';
    private const TABLE = self::ENTITY . 's';
    private const DATE = 'date';
    private const COL_EMPLOYEE = 'employee_id';
    private const COL_OPT = self::ENTITY . '_option';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();      // ! CHANGED
                $table->uuid(self::COL_EMPLOYEE);        // ! CHANGED
                $table->uuid(self::COL_OPT);        // ! CHANGED
                $table->string('title');
                $table->decimal('amount', 15, 2)->default('0.0');
                $table->string('type')->nullable();
                $table->date('start_' . self::DATE);
                $table->date('end_' . self::DATE);
                $table->string('reason');
                $table->uuid(DatabaseConstants::TABLE_CREATOR);         // ! CHANGED
                $table->timestamps();
                foreach ([
                    self::COL_EMPLOYEE                   => DatabaseConstants::TABLE_EMPLOYEES,
                    self::COL_OPT                   => DatabaseConstants::TABLE_LOAN_OPTS,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
                self::COL_OPT,
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
