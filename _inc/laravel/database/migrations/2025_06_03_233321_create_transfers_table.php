<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTransfersTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TRFS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_BRC_ID)->nullable();
            $table->uuid(UC::COL_DEP_ID)->nullable();
            $this->addEmployeeColumns($table, false, false);
            $table->date(UC::COL_TRF_DT)->index();
            $table->string('description')->nullable();
            $table->string('notes')->nullable();
            foreach (
                [
                    UC::COL_BRC_ID => DC::TABLE_BRANCHES,
                    UC::COL_DEP_ID => DC::TABLE_DEPARTMENTS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeForeign($table, self::TABLE);
            foreach (
                [
                    UC::COL_BRC_ID,
                    UC::COL_DEP_ID,
                ] as $col
            ) {
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
