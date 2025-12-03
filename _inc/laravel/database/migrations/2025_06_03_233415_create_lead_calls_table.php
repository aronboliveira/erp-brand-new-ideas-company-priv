<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{HasNullableAuditColumns, IsBusinessContact, LeadConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateLeadCallsTable extends Migration
{
    use HasNullableAuditColumns, IsBusinessContact, LeadConnected;
    private const TABLE = DC::TABLE_LD_CALLS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addLeadColumns($table, unique: false, nullable: false, cascade: true);
            $this->addBasicBusinessContactColumns($table, nullableUser: true, cascade: true);
            $this->addBusinessCallColumns($table);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropLeadColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropBasicBusinessContactColumnForeigns($table);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
