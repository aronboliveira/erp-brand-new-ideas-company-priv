<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSaturationDeductionsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;

    private const TABLE = DC::TABLE_ST_DD;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: false, nullable: true);
            $table->uuid(BC::COL_DD_OPT)->nullable()->index();
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('type')->nullable()->index();
            $this->addAuditColumns($table);
            $table->foreign(BC::COL_DD_OPT)
                ->references('id')
                ->on(DC::TABLE_DEDUCTION_OPTS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropEmployeeForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            try {
                if (Schema::hasColumn(self::TABLE, BC::COL_DD_OPT))
                    $table->dropForeign([BC::COL_DD_OPT]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for deduction_option on table ' .
                        self::TABLE .
                        ': ' .
                        $e->getMessage()
                );
            }
        });

        Schema::dropIfExists(self::TABLE);
    }
}
