<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateSetSalariesTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_SSLR;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: true);
            $table->string(UC::COL_SLR_TP)->index(); // TODO MODIFICAR PARA ENUM POSTERIORMENTE
            $table->decimal('salary', 15, 2)->default(DC::MININUM_WAGE_BR);
            $table->enum('frequency', [
                'once',
                'variable',
                'hourly',
                'biweekly',
                'weekly',
                'semimonthly',
                'semestral',
                'monthly',
                'annual',
            ])->default('monthly')
                ->index()
                ->nullable(); // ? Nullable para testes iniciais
            $table->unsignedTinyInteger(BC::COL_MDAY_LMT)->min(1)->max(31)->default(5)
                ->nullable(); // ? Nullable para testes iniciais
            $this->addAuditColumns($table, false);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
