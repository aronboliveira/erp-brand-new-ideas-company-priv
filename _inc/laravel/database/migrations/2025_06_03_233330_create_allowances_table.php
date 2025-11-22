<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateAllowancesTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;

    private const TABLE = DC::TABLE_ALW;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: false, nullable: false);
            $table->uuid(BC::COL_ALW_OPT)->index()->nullable();
            $table->string('title')->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('type')
                ->index()
                ->nullable(); // ? Nullable apenas para testes iniciais ou dados legados.
            $this->addAuditColumns($table);
            $table->foreign(BC::COL_ALW_OPT)
                ->references('id')
                ->on(DC::TABLE_ALLOWANCE_OPTS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropEmployeeForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            try {
                if (Schema::hasColumn(self::TABLE, BC::COL_ALW_OPT))
                    $table->dropForeign([BC::COL_ALW_OPT]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for ' .
                        BC::COL_ALW_OPT .
                        ' on table ' .
                        self::TABLE .
                        ': ' .
                        $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
