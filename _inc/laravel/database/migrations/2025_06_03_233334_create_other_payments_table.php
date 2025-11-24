<?php

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateOtherPaymentsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;

    private const TABLE = DC::TABLE_OT_PYMTS;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: false, nullable: false);
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('type')->nullable()->index();
            $table->string('description')->nullable();
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
