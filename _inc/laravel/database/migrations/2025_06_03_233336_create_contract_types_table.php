<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateContractTypesTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_CONTRACT_TYPES;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('description')->nullable();
            $table->string('category')->nullable()->index(); // nullable para testes; em produção provavelmente obrigatório
            $table->decimal(BC::COL_MIN_V, 10, 2)->default(0.00)->nullable(); // ? nullable para testes iniciais
            $table->decimal(BC::COL_MAX_V, 10, 2)->default(0.00)->nullable(); // ? nullable para testes iniciais
            $table->integer(BC::COL_MIN_M)->default(1)->nullable(); // ? nullable para testes iniciais
            $table->integer(BC::COL_MAX_M)->default(12)->nullable(); // ? nullable para testes iniciais
            $table->text(BC::COL_TC)->nullable();
            $table->boolean(BC::COL_DEF_TRMC)->default(false)->nullable(); // ? nullable para testes iniciais
            $table->boolean(BC::COL_SVR_GRT)->default(false)->nullable(); // ? nullable para testes iniciais
            $table->boolean(BC::COL_RNGT)->default(false)->nullable(); // ? nullable para testes iniciais
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
