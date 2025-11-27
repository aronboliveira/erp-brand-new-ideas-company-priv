<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    SettingsConstants as SC
};
use App\Enums\ProductStatus;
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
            $table->uuid(BC::COL_PRD_SV_ID)->nullable(); // ? nullable para testes
            $table->string('name')->index();
            $table->string('code')->unique()->index()->nullable(); // ? nullable para testes
            $table->enum('status', [ProductStatus::Active->value, ProductStatus::Paused->value, ProductStatus::Inactive->value, ProductStatus::Undefined->value])->default(ProductStatus::Inactive->value)
                ->index()->nullable(); // ? nullable para testes
            $table->string(AC::COL_MUNIT)->nullable(); // * e.g.: hour, session, item, license, etc
            $table->unsignedBigInteger(BC::COL_PRC_IDX)->default(0);
            $table->decimal(BC::COL_BS_PRC, 15, 4)->default(0.0000)->nullable(); // ? nullable para testes
            $table->decimal('discount', 15, 4)->default(0.0000)->nullable();
            $table->string(BC::COL_CUR_ID, 3)
                ->default(SC::DEF_SITE_CURRENCY_ID)
                ->nullable(); // ? nullable para testes
            $table->foreign(BC::COL_PRD_SV_ID)
                ->references('id')
                ->on(DC::TABLE_PROD_SERVS)
                ->nullOnDelete(); // ? nullable para testes
            $table->json('attributes')->nullable();
            $table->text('notes')->nullable(); // ? nullable para testes
            $this->addAuditColumns($table);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            Schema::hasColumn(self::TABLE, BC::COL_PRD_SV_ID) &&
                $table->dropForeign([BC::COL_PRD_SV_ID]);
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
