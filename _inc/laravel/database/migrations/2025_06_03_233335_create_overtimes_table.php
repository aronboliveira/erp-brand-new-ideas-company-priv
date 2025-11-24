<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateOvertimesTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;

    private const TABLE = DC::TABLE_OVT;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: false, nullable: false);
            $table->string('title');
            $table->integer(UC::COL_NDAYS);
            $table->integer('hours');
            $table->integer('rate');
            $table->string('type')->nullable()->index(); // ? fixed | percentage
            $table->string('notes')->nullable();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropEmployeeForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
