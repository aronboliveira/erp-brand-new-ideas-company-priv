<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{HasNullableAuditColumns, LeadConnected, IsBusinessContact};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateLeadEmailsTable extends Migration
{
    use HasNullableAuditColumns, LeadConnected, IsBusinessContact;
    private const TABLE = DC::TABLE_LD_EMAILS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addLeadColumns($table);
            $this->addBasicBusinessContactColumns($table, nullableUser: true, cascade: true);
            $this->addBussinessEmailColumns($table);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropLeadColumnForeign($table, self::TABLE);
            $this->dropBasicBusinessContactColumnForeigns($table);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
