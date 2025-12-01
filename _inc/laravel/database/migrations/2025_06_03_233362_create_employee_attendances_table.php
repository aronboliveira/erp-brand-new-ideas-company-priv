<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\AttendanceStatus;
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmployeeAttendancesTable extends Migration
{
    use HasNullableAuditColumns, EmployeeConnected;
    private const TABLE = DC::TABLE_EATD;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $this->addEmployeeColumns($table, nullable: false);
                $table->date('date');
                $table->enum('status', AttendanceStatus::values())->default(AttendanceStatus::Present->value)->index();
                $table->time(AC::COL_CLK_IN)->index();
                $table->time(AC::COL_CLK_OUT)->index();
                $table->time(AC::COL_ERL_ARV)->nullable(); // ? nullable for testing purposes, // ? at boot/saving, ensure that this is always before the CLK_IN, else set to 00:00 and decrease the counter if it was set
                $table->unsignedSmallInteger(AC::COL_ERL_AV_CT)->default(0)->nullable(); // ? nullable for testing purposes, forced to 0 if null on booting/saving
                $table->time('late'); // ? at boot/saving, ensure that this is always after the CLK_IN, else set to 00:00 and decrease the counter if it was set
                $table->unsignedSmallInteger(AC::COL_LT_CT)->default(0)->nullable(); // ? nullable for testing purposes, forced to 0 if null on booting/saving
                $table->time(AC::COL_ERL_LV); // ? at boot/saving, ensure that this is always before the CLK_OUT, else set to 00:00 and decrease the counter if it was set
                $table->unsignedSmallInteger(AC::COL_ERL_LV_CT)->default(0)->nullable(); // ? nullable for testing purposes, forced to 0 if null on booting/saving
                $table->time('overtime'); // ? at boot/saving, ensure that this is always after the CLK_OUT, else set to 00:00 and decrease the counter if it was set
                $table->unsignedSmallInteger(AC::COL_OVT_CT)->default(0)->nullable(); // ? nullable for testing purposes, forced to 0 if null on booting/saving
                $table->uuid(AC::COL_OVT_ID)->nullable(); // ? nullable for testing purposes, references an overtime entry if applicable
                $table->time(AC::COL_TT_RST)->default('00:00:00');
                $table->time(AC::COL_TT_WRK)->default('08:00:00')->nullable(); // ? nullable for testing purposes, default at model level to the differece between CLK_IN and CLK_OUT minus TT_RST
                $table->foreign(AC::COL_OVT_ID)
                    ->references('id')
                    ->on(DC::TABLE_OVT)
                    ->nullOnDelete();
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
                    AC::COL_OVT_ID,
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
