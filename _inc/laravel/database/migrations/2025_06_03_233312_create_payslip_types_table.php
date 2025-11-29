<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePayslipTypesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PAY_SLP_TP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string('name')->unique()->index();
            $table->text('description')->nullable();
            $table->decimal(BC::COL_MIN_AMT, 15, 2)->default(0.00)->nullable();
            $table->decimal(BC::COL_MAX_AMT, 15, 2)->default(9999999999.99)->nullable();
            $table->text(BC::COL_RL_APL)->nullable(); // ? if null, applies to all roles; else explode and check for the UserType enum matches pairing with the UC::COL_TP
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
