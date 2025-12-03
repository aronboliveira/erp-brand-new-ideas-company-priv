<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{DealConnected, HasNullableAuditColumns, IsBusinessContact};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateDealCallsTable extends Migration
{
    use DealConnected, HasNullableAuditColumns, IsBusinessContact;
    private const TABLE = DC::TABLE_DL_CALLS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addDealColumns($table, unique: false, nullable: false, cascade: true);
            $this->addBasicBusinessContactColumns($table, nullableUser: true, cascade: true);
            $this->addBusinessCallColumns($table);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropDealColumnForeign($table, self::TABLE);
            $this->dropBasicBusinessContactColumnForeigns($table);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
