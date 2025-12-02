<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{HasDocumentColumns, HasNullableAuditColumns, LeadConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadFilesTable extends Migration
{
    use HasDocumentColumns, HasNullableAuditColumns, LeadConnected;
    private const TABLE = DC::TABLE_LD_FILES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addLeadColumns($table);
            $table->string(DC::COL_FL_NM)->nullable();
            $this->addDocumentColumns($table);
            $this->addAuditColumns($table);
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropLeadColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
