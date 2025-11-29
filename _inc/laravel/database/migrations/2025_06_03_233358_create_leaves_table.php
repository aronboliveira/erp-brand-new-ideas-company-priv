<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeavesTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_LV;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $this->addEmployeeColumns($table);
                $table->uuid(CC::COL_LV_TP_ID)->index();
                $table->date(PJC::COL_APL_ON); // * booted and saving should ensure that it's never before today
                $table->date(PJC::COL_S_DT)->index(); // * booted and saving should ensure S_DT <= E_DT and never before today
                $table->date(PJC::COL_E_DT); // * booted and saving should ensure E_DT > S_DT && the difference between S_DT and E_DT is within allowed limits, with should be defined querying the leave type with 'days' + 'extensible_days'
                $table->string(PJC::COL_TT_LV_DY, 12); // todo this should be changed to a date afterwards, but will be kept as a string for compatibility with legacy implementation for now;
                // * booted and saving should ensure its never more than the allowed days for the leave type, calculated as 'days' + 'extensible_days' according to the selected leave type
                $table->unsignedInteger('discount')->default(0); // * booted and saving should ensure its never more than 100 and never less than 0, according to the selected leave type's min and max salary deduction percent
                $table->text(PJC::COL_LV_RS)->nullable();
                $table->string('remark')->nullable();
                $table->string('status')->index();
                $table->json('attachments')->nullable();
                $table->json('conditions')->nullable();
                $table->foreign(CC::COL_LV_TP_ID)
                    ->references('id')
                    ->on(DC::TABLE_LEAVE_TYPES)
                    ->onDelete('cascade');
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropEmployeeForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    CC::COL_LV_TP_ID,
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
