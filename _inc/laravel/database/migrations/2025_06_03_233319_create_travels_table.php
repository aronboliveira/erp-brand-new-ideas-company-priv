<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTravelsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TRAVELS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table);
            $table->date(PJC::COL_S_DT)->index()->default(now()->format('Y-m-d'));
            $table->date(PJC::COL_E_DT)->default(now()->addDays(7)->format('Y-m-d'));
            $table->string(PJC::VST_PLC)->nullable()->index();
            $table->string(PJC::VST_PPS)->nullable();
            $table->string('description')->nullable();
            $table->string('notes')->nullable();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeForeign($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
