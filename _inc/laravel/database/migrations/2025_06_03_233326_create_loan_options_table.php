<?php

use App\Traits\{HasNullableAuditColumns, IsNumericBenefit};
use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateLoanOptionsTable extends Migration
{
    use HasNullableAuditColumns, IsNumericBenefit;
    private const TABLE = DC::TABLE_LOAN_OPTS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addNumericBenefitColumns($table);
            $table->unsignedSmallInteger(BC::COL_MIN_ITM)
                ->default(1)
                ->nullable(); // ? Nullable para testes iniciais
            $table->unsignedSmallInteger(BC::COL_MAX_ITM)
                ->default(96)
                ->nullable(); // ? Nullable para testes iniciais
            $table->unsignedSmallInteger(BC::COL_FGTS_PCT)
                ->nullable()
                ->max(50); // ? Nullable para testes iniciais
            $table->boolean(BC::COL_SVR_GRT)
                ->default(false)
                ->nullable(); // ? Nullable para testes iniciais
            $table->boolean(BC::COL_RNGT)
                ->default(true)
                ->nullable(); // ? Nullable para testes iniciais
            $table->unsignedTinyInteger(BC::COL_GRC_PRD_DYS)
                ->default(0)
                ->nullable(); // ? Nullable para testes iniciais
            $table->text(BC::COL_TC)
                ->nullable(); // ? Nullable para testes iniciais
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
