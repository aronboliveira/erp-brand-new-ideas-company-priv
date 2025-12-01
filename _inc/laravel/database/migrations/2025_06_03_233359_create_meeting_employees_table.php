<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateMeetingEmployeesTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_MET_EMP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(CC::COL_INV_CD)->unique()->index()->nullable(); // ? nullable for testing purposes
            $table->uuid(CC::COL_MT_ID)->index();
            $this->addEmployeeColumns($table, unique: false, nullable: false);
            $table->unique([CC::COL_MT_ID, UC::COL_EMP_ID])->nullable(); // ? nullable for testing purposes
            $table->boolean(CC::COL_IS_HST)->default(false)->nullable();
            $table->boolean(CC::COL_HAS_MRC_MT)->default(false)->nullable();
            $table->boolean(CC::COL_HAS_CMR_OFF)->default(false)->nullable();
            $table->boolean(CC::COL_HAS_SCR_ENB)->default(false)->nullable();
            $table->json('metadata')->nullable();
            $table->foreign(CC::COL_MT_ID)
                ->references('id')
                ->on(DC::TABLE_MEETINGS)
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
                    CC::COL_MT_ID,
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
