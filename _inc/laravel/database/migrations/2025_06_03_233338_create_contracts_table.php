<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{EvaluationStatus, Frequency};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateContractsTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_CONTRACTS;
    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->nullable(); // ? nullable for initial tests, will be generated as CTR-{UUID}, checking with do/while for uniqueness
            $table->uuid('type')->nullable();
            $table->string(PJC::COL_CN)->unique()->nullable(); // ? nullable for initial tests
            $table->string('title')->nullable()->index();               // ? nullable for initial tests
            $table->string('subject')->nullable();
            $table->string('value')->nullable(); // TODO mudar posteriormente para decimal
            $table->string('currency', 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // * CR_SB => 'site_currency_symbol', nullable for initial tests
            $table->longText('description')->nullable();
            $table->text('notes')->nullable();
            $table->date(PJC::COL_S_DT)->default(now()->addDays(7)->format('Y-m-d')); // ? default to tomorrow for initial tests
            $table->date(PJC::COL_E_DT)->default(now()->addDays(30)->format('Y-m-d'));  // ? default to 30 days from now for initial tests
            $table->longText(PJC::COL_CDESC)->nullable();
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Draft->value)->nullable(); // ? nullable for initial tests
            $table->boolean('renewable')->default(false)->nullable(); // ? Nullable para testes iniciais
            $table->boolean(PJC::COL_ARNW)->default(false)->nullable(); // * during ::saving, if renewable is false then goes to false as well
            $table->enum('frequency', array_column(Frequency::cases(), 'value'))->default(Frequency::Monthly->value)
                ->nullable()->index(); // ? Nullable para testes iniciais
            $table->uuid('company')->nullable(); // ? nullable for initial tests
            $table->uuid(PJC::COL_CLIENT_ID)->nullable()->index();
            $table->string(PJC::COL_CLIENT_NAME)->nullable()->index(); // ? nullable for initial tests
            $table->string(PJC::COL_OBG_NAME)->nullable()->index(); // ? nullable for initial tests
            $table->string(PJC::COL_OBL_NAME)->nullable()->index(); // ? nullable for initial tests
            $table->string(PJC::COL_OBG_IDF)->nullable()->index(); // ? CNPJ or CPF, nullable for initial tests
            $table->string(PJC::COL_OBL_IDF)->nullable()->index(); // ? CNPJ or CPF, nullable for initial tests
            $table->string(PJC::COL_OBG_ADDR)->nullable(); // ? nullable for initial tests
            $table->string(PJC::COL_OBL_ADDR)->nullable(); // ? nullable for initial tests
            $table->string(PJC::COL_OBG_CTC)->nullable(); // ? nullable for initial tests
            $table->string(PJC::COL_OBL_CTC)->nullable(); // ? nullable for initial tests
            $table->longText(PJC::COL_CL_SIG)->nullable();
            $table->longText(PJC::COL_CO_SIG)->nullable();
            $table->date(PJC::COL_CL_SIGN_AT)->nullable(); // ? nullable for initial tests
            $table->date(PJC::COL_CO_SIGN_AT)->nullable(); // ? nullable for initial tests
            $table->uuid(PJC::COL_APV_BY)->nullable(); // ? nullable for initial tests
            $table->date(PJC::COL_APV_AT)->nullable(); // ? nullable for initial tests
            $table->uuid(PJC::COL_REJ_BY)->nullable(); // ? nullable for initial tests
            $table->date(PJC::COL_REJ_AT)->nullable(); // ? nullable for initial tests
            $table->string(PJC::COL_WT_NM)->nullable(); // ? nullable for initial tests
            $table->string(PJC::COL_WT2_NM)->nullable(); // ? nullable for initial tests
            $table->string(PJC::COL_WT_IDF)->nullable(); // ? CNPJ or CPF
            $table->string(PJC::COL_WT2_IDF)->nullable(); // ? CNPJ or CPF
            $table->longText(PJC::COL_WT_SIG)->nullable();
            $table->longText(PJC::COL_WT2_SIG)->nullable();
            $table->date(PJC::COL_WT_SIGN_AT)->nullable(); // ? nullable for initial tests
            $table->date(PJC::COL_WT2_SIGN_AT)->nullable(); // ? nullable for initial tests
            $table->uuid(PJC::COL_PJ_ID)->nullable();
            $table->uuid(PJC::COL_PLN_SCHD_ID)->nullable()->index(); // ? the PJC::COL_S_DT and PJC::COL_E_DT should be inside the contract limit period, else this is nullified at boot/save
            $table->string(PJC::COL_F_PATH)->nullable(); // ? nullable for initial tests
            $table->json(PJC::COL_ATC_PATHS)->nullable();
            foreach (
                [
                    'company' => DC::TABLE_USERS,
                    PJC::COL_CLIENT_ID => DC::TABLE_USERS,
                    'type' => DC::TABLE_CONTRACT_TYPES,
                    PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                    PJC::COL_F_PATH => DC::TABLE_DOCS,
                    PJC::COL_APV_BY => DC::TABLE_USERS,
                    PJC::COL_REJ_BY => DC::TABLE_USERS,
                    PJC::COL_PLN_SCHD_ID => DC::TABLE_PLN_SCHD,
                ] as $column => $referencedTable
            )
                $fk = $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    'company',
                    PJC::COL_CLIENT_ID,
                    'type',
                    PJC::COL_PJ_ID,
                    PJC::COL_F_PATH,
                    PJC::COL_APV_BY,
                    PJC::COL_REJ_BY,
                    PJC::COL_PLN_SCHD_ID,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }

            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
