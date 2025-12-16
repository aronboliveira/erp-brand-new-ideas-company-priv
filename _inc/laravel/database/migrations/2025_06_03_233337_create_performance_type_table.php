<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePerformanceTypeTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_PRF_TP;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();       // ! CHANGED
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('category')->nullable()->index();
            $table->string(PJC::COL_M_METRIC)->nullable()->index();
            $table->json('metrics')->nullable();
            $table->boolean(PJC::COL_CRT)->default(false)->nullable();
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
