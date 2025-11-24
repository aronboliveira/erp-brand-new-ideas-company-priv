<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    SettingsConstants as SC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateProductServiceUnitsTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_PROD_SERV_UNITS;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('code')->unique()->index()->nullable(); // ? nullable para testes
            $table->enum('status', ['active', 'paused', 'inactive', 'undefined'])->default('inactive')
                ->index()->nullable(); // ? nullable para testes
            $table->string(AC::COL_MUNIT)->nullable(); // * e.g.: hour, session, item, license, etc
            $table->bigInteger('quantity')->default(1)->nullable(); // ? nullable para testes
            $table->decimal(BC::COL_BS_PRC, 15, 4)->default(0.0000)->nullable(); // ? nullable para testes
            $table->string(BC::COL_CUR_ID, 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable para testes
            $table->json('attributes')->nullable();
            $table->text('description')->nullable(); // ? nullable para testes
            $table->text('notes')->nullable(); // ? nullable para testes
            $table->timestamp(AC::COL_AV_FROM)->index()->default(now())->nullable();
            $table->timestamp(AC::COL_AV_UNTIL)->index()->default(now()->addYears(1))->nullable();
            $this->addAuditColumns($table);
            $table->softDeletes();
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
