<?php

use App\Config\Constants\{
    BillsConstants as BC,
    ChartsConstants as CHTC,
    DatabaseConstants as DC,
    UsersConstants as UC,
    SettingsConstants as SC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateChartOfAccountsTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_COAS;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string(CHTC::COL_NM)->index();
            $table->integer(CHTC::COL_CD)->default(0)->index();
            $table->integer('depth')->default(0)->nullable()->index(); // ? nullable para testes
            $table->decimal(CHTC::CUR_BL, 25, 6)->default(0.000000)->nullable(); // ? nullable para testes
            $table->decimal(CHTC::INIT_BL, 25, 6)->default(0.000000)->nullable(); // ? nullable para testes
            $table->decimal(CHTC::EXP_NXT_MN_BL, 25, 6)->default(0.000000)->nullable(); // ? nullable para testes
            $table->string(BC::COL_CUR_ID, 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable para testes
            $table->json('rules')->nullable();
            $table->json('restrictions')->nullable();
            $table->uuid(UC::COL_RSP_ID)->nullable()->index(); // * ponteiro para responsável legal, se cabível; se nulo, delegar ao user_id
            $table->boolean(UC::COL_PD_UPD)->default(false)->nullable(); // ? nullable para testes
            $table->boolean(UC::COL_IS_SYS)->default(true)->nullable(); // ? nullable para testes; em ::saving verificar creator vs DEFAULT_UUID
            $table->uuid(CHTC::COL_TP)->nullable();
            $table->uuid(CHTC::COL_SUBTP)->nullable();
            $table->integer(CHTC::COL_ENB)->default(1);
            $table->text(CHTC::COL_DESC)->nullable();
            $table->uuid(UC::COL_USER_ID);
            foreach (
                [
                    CHTC::COL_SUBTP => DC::TABLE_COA_SUBTYPES,
                    CHTC::COL_TP    => DC::TABLE_COA_TYPES,
                    UC::COL_RSP_ID  => DC::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->nullOnDelete();
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([CHTC::COL_TP, CHTC::COL_SUBTP, UC::COL_USER_ID] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for ' .
                            $col .
                            ' on table ' .
                            self::TABLE .
                            ': ' .
                            $e->getMessage()
                    );
                }
            }

            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
