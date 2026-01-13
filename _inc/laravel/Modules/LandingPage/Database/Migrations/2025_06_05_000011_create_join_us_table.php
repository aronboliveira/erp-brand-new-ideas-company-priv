<?php

use App\Config\Constants\{DatabaseConstants as DC, LandingPageConstants as LPC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJoinUsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JU;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid("query_key")->unique();
                $table->string('email', 254)->unique();
                $this->addAuditColumns($table);
            });
        }
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table);
        });
        Schema::dropIfExists(self::TABLE);
    }
};
