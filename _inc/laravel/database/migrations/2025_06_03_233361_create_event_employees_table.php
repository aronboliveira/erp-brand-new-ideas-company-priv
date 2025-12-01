<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{EventRole};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEventEmployeesTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_EV_EMP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_EV_ID)->index();
            $this->addEmployeeColumns($table, unique: false, nullable: false);
            $table->unique([AC::COL_EV_ID, UC::COL_EMP_ID])->nullable(); // ? nullable for testing purposes
            $table->enum('role', EventRole::values())->default(EventRole::Attendee->value)->nullable(); // ? nullable for testing purposes
            $table->json('metadata')->nullable();
            $table->foreign(AC::COL_EV_ID)
                ->references('id')
                ->on(DC::TABLE_EVENTS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            foreach (
                [
                    AC::COL_EV_ID,
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
