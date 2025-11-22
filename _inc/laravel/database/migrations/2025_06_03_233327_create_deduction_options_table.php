<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateDeductionOptionsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_DEDUCTION_OPTS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('code', 50)->nullable()->index();
            $table->enum(BC::COL_DD_TYPE, [
                'legal',
                'voluntary',
                'judicial',
                'syndical',
                'benefit',
                'loan',
                'advance',
                'other'
            ])->default('other')
                ->nullable(); // ? Nullable para testes iniciais
            $table->enum(BC::COL_CCL_BS, [
                BC::VL_GRS_SL,
                BC::VL_NET_SL,
                BC::VL_SPC_AMT,
                'percentage',
                BC::VL_PRG_TBL,
                BC::VL_CTRB_SL,
                'mixed'
            ])->default(BC::VL_GRS_SL)
                ->nullable(); // ? Nullable para testes iniciais
            $table->unsignedSmallInteger(BC::COL_MIN_PCT)
                ->default(0)
                ->nullable(); // ? Nullable para testes iniciais
            $table->unsignedSmallInteger(BC::COL_MAX_PCT)
                ->default(100)
                ->nullable(); // ? Nullable para testes iniciais
            $table->enum('frequency', [
                'monthly',
                'weekly',
                'biweekly',
                'semimonthly',
                'semestral',
                'annual',
                'once',
                'variable'
            ])->default('monthly')
                ->nullable()->index(); // ? Nullable para testes iniciais
            $table->unsignedTinyInteger(BC::COL_MDAY_LMT)->min(1)->max(12)->nullable(); // ? Nullable para testes iniciais
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
