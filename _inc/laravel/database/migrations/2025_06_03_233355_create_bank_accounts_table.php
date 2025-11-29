<?php

use App\Config\Constants\{BillsConstants as BLC, BanksConstants as BKC, ChartsConstants as CHTC, DatabaseConstants as DC, SettingsConstants as SC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
use Illuminate\Support\Str;

class CreateBankAccountsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_BANK_ACC;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(BKC::COL_ACC_N)->index();
            $table->boolean(BKC::COL_IS_VRT)->default(true)->nullable(); // ? nullable para testes
            $table->boolean(BLC::COL_AUTORCC)->default(false)->nullable(); // ? nullable para testes
            $table->json(BLC::COL_RCC_RL)->nullable();

            $table->uuid(BKC::COL_HD_ID)->index()->nullable(); // ? um holder não necessariamente possui uma conta no sistema
            $table->string(BKC::COL_HNM)->index();
            $table->string(BKC::COL_CT); // TODO revisar tipo (telefone, E.164, etc.)
            $table->text(BKC::COL_HD_ADDR)->nullable(); // ? nullable para testes

            $table->uuid(UC::COL_RSP_ID)->index()->nullable(); // ? um responsável não necessariamente possui uma conta no sistema
            $table->string(UC::COL_RSP_NM)->nullable(); // ? nullable para testes
            $table->string(UC::COL_RSP_TEL)->nullable(); // ? nullable para testes
            $table->string(UC::COL_RSP_EM)->nullable(); // ? nullable para testes
            $table->text(UC::COL_RSP_ADDR)->nullable(); // ? nullable para testes

            $table->string(BKC::COL_NM);
            $table->text(BKC::COL_ADR);
            $table->string(BKC::COL_BANK_IDF)->index()->nullable(); // ? nullable para testes, deve corresponder ao CNPJ no brasil
            $table->string(BKC::COL_AG_N)->nullable();
            $table->string(BKC::COL_AG_DG)->nullable();

            $table->uuid(BKC::COL_COA)->nullable();

            $table->decimal(BKC::COL_OB, 25, 2)->default(0.00);
            $table->decimal(CHTC::CUR_BL, 25, 2)->default(0.00)->nullable(); // ? nullable para testes
            $table->decimal(BKC::COL_AMT_STR, 25, 2)->default(0.00)->nullable(); // ? nullable para testes
            $table->decimal(BKC::COL_AM_LK, 25, 2)->default(0.00)->nullable(); // ? nullable para testes
            $table->json('vaults')->nullable();
            $table->json(BKC::COL_PIX_KEYS)->nullable();
            $table->boolean(BKC::COL_ACPT_PIX)->default(false)->nullable(); // ? nullable para testes
            $table->string(BLC::COL_CUR_ID, 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable para testes
            $table->json('restrictions')->nullable();
            $table->json('profile')->nullable();
            $table->boolean(BKC::COL_HAS_CRD)->default(false)->nullable(); // ? nullable para testes
            // * in ::booted and ::saving, if has_credit_card is true, ensure credit_cards is not null/empty, else switch it to false
            $table->json(BKC::COL_CRD_CD)->nullable();
            $table->boolean(BKC::COL_ACPTS_CRD_CD)->default(false)->nullable(); // ? nullable para testes
            $table->boolean(BKC::COL_HAS_PND_STT)->default(false)->nullable(); // ? nullable para testes
            $table->boolean(BKC::COL_HAS_DBT)->default(false)->nullable(); // ? nullable para testes
            // * in ::booted and ::saving, if has_debit_card is true, ensure debit_cards is not null/empty, else switch it to false
            $table->json(BKC::COL_DBT_CD)->nullable();
            $table->boolean(BKC::COL_ACPTS_DBT_CD)->default(false)->nullable(); // ? nullable para testes
            $table->boolean(BLC::COL_IS_PRM)->default(false)->index()->nullable(); // ? nullable para testes
            $table->float(BKC::COL_RSK)->default(0.00)->nullable(); // ? nullable para testes
            $table->boolean(UC::COL_IA)->default(true)->index()->nullable(); // ? nullable para testes
            $table->string(BKC::COL_INT_PRV)->default('manual')->index()->nullable();
            $table->json(BLC::COL_SYNC_ER)->nullable();
            foreach (
                [
                    BKC::COL_COA      => DC::TABLE_COAS,
                    BKC::COL_HD_ID    => DC::TABLE_USERS,
                    UC::COL_RSP_ID    => DC::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach ([BKC::COL_COA, BKC::COL_HD_ID, UC::COL_RSP_ID] as $col) {
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
        });
        Schema::dropIfExists(self::TABLE);
    }
}
