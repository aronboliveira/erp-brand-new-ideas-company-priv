<?php

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLoansTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;

    private const TABLE = DC::TABLE_LN;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: false, nullable: false);
            $table->uuid(BC::COL_LN_OPT)->nullable()->index();
            $table->string('title')->index();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('currency', 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable for tests
            $table->string('type')->nullable()->index();
            $table->date(PJC::COL_S_DT);
            $table->date(PJC::COL_E_DT)->nullable();
            $table->string('reason');
            $table->string(BC::COL_DD_TYPE)->nullable()->index();
            $table->unsignedSmallInteger('installments')->nullable(); // ? Nullable para fase inicial; em produção deveria ser obrigatório p/ consignados
            $table->boolean(BC::COL_IS_PAY_RL_DDT)->default(false)->nullable(); // ? Nullable para fase inicial
            $this->addAuditColumns($table);
            $table->foreign(BC::COL_LN_OPT)
                ->references('id')
                ->on(DC::TABLE_LOAN_OPTS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);

            try {
                if (Schema::hasColumn(self::TABLE, BC::COL_LN_OPT))
                    $table->dropForeign([BC::COL_LN_OPT]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for ' .
                        BC::COL_LN_OPT .
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
