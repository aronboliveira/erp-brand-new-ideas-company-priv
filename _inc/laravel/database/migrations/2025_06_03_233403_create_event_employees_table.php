<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEventEmployeesTable extends Migration
{
    private const ENTITY = 'event';
    private const TABLE = self::ENTITY . '_employees';
    private const COL_EV = self::ENTITY . '_id';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();            // ! CHANGED
            $table->uuid(self::COL_EV)->index();        // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE)->index();     // ! CHANGED
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->index();      // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_EV                       => DatabaseConstants::TABLE_EVENTS,
                self::COL_EMPLOYEE                    => DatabaseConstants::TABLE_EMPLOYEES,
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
                self::COL_EV,
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
