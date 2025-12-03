<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{DealConnected, HasNullableAuditColumns, IsBusinessContact};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateDealEmailsTable extends Migration
{
    use DealConnected, HasNullableAuditColumns, IsBusinessContact;
    private const TABLE = DC::TABLE_DL_EMAILS;
    // todo criar trait para colunas de business email e business call + classe abstrata para dividir lógica comum
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addDealColumns($table);
            $this->addBasicBusinessContactColumns($table, nullableUser: true, cascade: true);
            $this->addBussinessEmailColumns($table);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropDealColumnForeign($table, self::TABLE);
            $this->dropBasicBusinessContactColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
