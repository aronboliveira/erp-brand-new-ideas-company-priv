<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{EvaluationStatus, Frequency};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBudgetsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_BDG;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->nullable(); // ? nullable for tests; automatically generated as BDG-{UUID}, checking uniqueness with do/while;
            $table->string('name')->index();
            $table->enum('type', ['revenue', 'expense', 'mixed'])->index()->nullable(); // ? nullable for tests
            $table->string('period')->index()->nullable(); // ? e.g., Q1 2024, FY 2024, etc. Should demand the inclusion of a year via regex in the model, else nullified.
            $table->enum('frequency', array_column(Frequency::cases(), 'value'))->default(Frequency::Once->value)->nullable()->index();
            $table->date('from')->nullable(); // * should ALWAYS mirror start_date. Kept for legacy.
            $table->date(PJC::COL_S_DT)->nullable();
            $table->date('to')->nullable();   // * should ALWAYS mirror end_date. Kept for legacy.
            $table->date(PJC::COL_E_DT)->nullable(); // ? should never be lower than COL_S_DT
            $table->decimal('amount', 16, 2)->default(0.00)->nullable(); // ? nullable for tests
            $table->string('currency', 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable for tests
            $table->decimal(BC::COL_EXC_RT)->default(1.0000)->nullable(); // ? nullable for tests
            $table->decimal(BC::COL_WRN_TRSH, 4, 2)->nullable(); // ? campled to be between 0 and 100 at model
            $table->decimal(BC::COL_CRT_WRN_TH, 4, 2)->nullable(); // ? campled to be between 0 and 100 at model, NEVER lower than COL_WRN_TRSH if this is set
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Pending->value)->nullable()->index();
            $table->uuid(PJC::COL_SBM_BY)->nullable();
            $table->timestamp(PJC::COL_SBM_AT)->nullable();
            $table->uuid(PJC::COL_APV_BY)->nullable();
            $table->timestamp(PJC::COL_APV_AT)->nullable();
            $table->uuid(PJC::COL_REJ_BY)->nullable();
            $table->timestamp(PJC::COL_REJ_AT)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text(BC::COL_INC_DATA)->nullable();
            $table->text(BC::COL_EXP_DATA)->nullable();
            $table->uuid(PJC::COL_PJ_ID)->index()->nullable();
            $table->uuid(PJC::COL_CTC_ID)->index()->nullable();
            $table->uuid('company')->index()->nullable();
            $table->uuid('branch')->index()->nullable();
            $table->uuid('department')->index()->nullable();
            $table->json(BC::COL_BNK_TRFS)->nullable(); // ? string[] of id of rows in DC::TABLE_BNK_TRF
            $table->json('transactions')->nullable(); // ? string[] of id of rows in DC::TABLE_TRS
            $table->json(BC::COL_CARD_NTS)->nullable(); // ? string[] of id of rows in DC::TABLE_CD_NOTES (credit_notes) or DC::TABLE_DB_NOTES (debit_notes)
            $table->json('receipts')->nullable(); // ? string[] of id of rows in DC::TABLE_DOCUMENTS, urls or local file paths. Kept separate from 'attachments' for easier querying. Should be merged into 'attachments' through the model booted save. Check the Schema of DC::TABLE_TRS, DC::TABLE_BNK_TRF, DC::TABLE_CD_NOTES and DC::TABLE_DB_NOTES for 'receipt' or BC::COL_ADD_RCP ('receipt_attached')
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
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
