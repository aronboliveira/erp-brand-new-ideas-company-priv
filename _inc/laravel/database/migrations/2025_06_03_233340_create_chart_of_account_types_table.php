<?php

use App\Config\Constants\{
    ChartsConstants as CHTC,
    DatabaseConstants as DC,
    SettingsConstants as SC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateChartOfAccountTypesTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_COA_TYPES;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string(CHTC::COL_CD)->unique()->index()->nullable(); // ? nullable para testes
            $table->string('category')->index()->nullable();
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->json('units')
                ->default(json_encode([
                    'X' => [
                        'type'   => 'timestamp',
                        'unit'   => 'MM',
                        'values' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
                    ],
                    'Y' => [
                        'type'   => 'value',
                        'unit'   => SC::DEF_SITE_CURRENCY_SB,
                        'values' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    ],
                ]))
                ->nullable(); // ? mock para testes
            $table->string(CHTC::COL_NM)->nullable();
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
