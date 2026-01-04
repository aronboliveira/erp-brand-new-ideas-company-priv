<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// * it's not clear what this model is for semantically, but it's being kept for legacy reasons. We will just make it a bridge table for payments for now
// * this is likely for interacting with FormData settings fields, but it's not clear
class CreateCompanyPaymentSettingsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CPN_PAY_SETG;
    public function up(): void
    {
        Schema::create(
            self::TABLE,
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name')->index();
                $table->string('value');
                $this->addAuditColumns($table);
                $table->unique(['name', DC::COL_TABLE_CREATOR]);
            }
        );
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
