<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAwardsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_AWD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table);
            $table->uuid(UC::COL_AWD_TP)->nullable()->index();
            $table->date('date');
            $table->string('gift')->nullable();
            $table->string('description')->nullable();
            $table->foreign(UC::COL_AWD_TP)
                ->references('id')
                ->on(DC::TABLE_AWD_TPS)
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
                    UC::COL_AWD_TP,
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
