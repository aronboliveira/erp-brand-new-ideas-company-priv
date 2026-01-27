<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateComplaintsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CPT;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, nullable: true);
            $table->string('title')->index();
            $table->string('reason')->nullable();
            $table->uuid(CC::COL_CPT_FRM)->nullable();
            $table->uuid(CC::COL_CPT_AGST)->nullable();
            $table->date(CC::COL_CPT_DT)->useCurrent()->index();
            $table->string('description')->nullable();
            $table->string('notes')->nullable();
            foreach ([CC::COL_CPT_FRM => DC::TABLE_EMPLOYEES, CC::COL_CPT_AGST => DC::TABLE_EMPLOYEES] as $column => $referencedTable) $table->foreign($column)->references('id')->on($referencedTable)->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            foreach ([CC::COL_CPT_FRM, CC::COL_CPT_AGST,] as $col) {
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
