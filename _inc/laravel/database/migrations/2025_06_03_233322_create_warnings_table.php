<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateWarningsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_WRN;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, nullable: true);
            $table->uuid(CC::COL_WRN_BY)->nullable();
            $table->uuid(CC::COL_WRN_TO)->nullable();
            $table->date(CC::COL_WRN_DATE)->default(now()->format('Y-m-d'))->index();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            foreach ([CC::COL_WRN_TO => DC::TABLE_EMPLOYEES, CC::COL_WRN_BY => DC::TABLE_EMPLOYEES] as $column => $referencedTable) $table->foreign($column)->references('id')->on($referencedTable)->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeForeign($table, self::TABLE);
            foreach ([CC::COL_WRN_TO, CC::COL_WRN_BY,] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning('Failed to drop foreign key for ' . $col . ': ' . $e->getMessage());
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
