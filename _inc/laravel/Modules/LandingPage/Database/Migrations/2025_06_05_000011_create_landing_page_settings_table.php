<?php

use App\Config\Constants\{DatabaseConstants as DC, LandingPageConstants as LPC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLandingPageSettingsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_LPS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid("query_key")->unique();
                $table->string(LPC::COL_LPS_NM);
                $table->longtext(LPC::COL_LPS_V)->nullable();
                $this->addNullableAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table);
        });
        Schema::dropIfExists(self::TABLE);
    }
};
