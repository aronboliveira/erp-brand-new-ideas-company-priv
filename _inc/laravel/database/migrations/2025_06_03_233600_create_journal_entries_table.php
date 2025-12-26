<?php

use App\Config\Constants\{ActivitiesConstants as AC, BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{EvaluationStatus, LedgerBookType, PaymentType};
use App\Traits\{HasNfeColumns, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJournalEntriesTable extends Migration
{
    use HasNfeColumns, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JOURNAL_ENTRIES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 254)->nullable()->unique(); // * nullable only for testing, automatically generate as JE-{timestamp}-{Str::uuid()} at boot/saving
            $table->string('name', 254)->nullable()->index();
            $table->string('reference')->nullable();
            $table->date('date')->index();
            $table->date(AC::COL_PST_DT)->nullable()->index(); // ? if null, mirror 'date', which is the entry date. NEVER before the 'date' column
            $table->date(PJC::COL_RVS_DT)->nullable()->index(); // ? reversal date, if any
            $table->string('period', 20)->nullable()->index(); // ? fiscal period, e.g., '2025-Q2' // ? nullable for tests
            $table->uuid('author')->nullable()->index();
            $table->uuid('reviewer')->nullable()->index(); // ? MUST be a user whereIn('type', ['accountant', 'admin', 'super admin']) 
            $table->timestamp(BC::COL_ACC_AT)->nullable()->index(); // ? approval date, if any
            $table->timestamp(BC::COL_REJ_AT)->nullable()->index(); // ? rejection date, if any
            $table->text(BC::COL_REJ_RS)->nullable(); // ? rejection reasons, if any
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Draft->value)->nullable()->index(); // ? nullable for tests
            $table->enum(BC::COL_PAY_TP, array_column(PaymentType::cases(), 'value'))->nullable()->index(); // ? nullable for tests
            $table->decimal(BC::COL_TTL_DBT, 20, 6)->default(0.00)->nullable()->index(); // ? nullable for tests, enforced at model level
            $table->decimal(BC::COL_TTL_CRT, 20, 6)->default(0.00)->nullable()->index(); // ? nullable for tests, enforced at model level
            $table->string('currency', 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable()->index(); // ? nullable for tests
            $table->decimal(BC::COL_EXC_RT, 16, 6)->default(1.000000)->nullable()->index(); // ? nullable for tests, enforced at model level
            $table->text('description')->nullable();
            $table->text('memo')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('company')->nullable()->index(); // ? the company this journal entry belongs to, not necessarily of the author, but defaulted as such if the author has a valid FK with UC::COL_EMP_ID on his row as a user, then in the fetched employee row querying for the CC::COL_BRC_ID or 'branch' (Schema::hasColumn for it) to finally get the 'company' column (that is, this must be cached); MUST refer to a user whereIn('type', ['company', 'vendor'])
            $table->uuid('branch')->nullable()->index(); // ? the branch this journal entry belongs to, not necessarily of the author, but defaulted as such if the author has a valid FK with UC::COL_EMP_ID on his row as a user
            $table->uuid('department')->nullable()->index(); // ? the department being referred to, not necessarily of the author, but defaulted as such if the author has a valid FK with UC::COL_EMP_ID on his row as a user
            $table->uuid('project')->nullable()->index(); // ? the project being referred to, if any
            $table->uuid('document')->nullable()->index(); // ? linked document, if any, cacheing at model level the type, number and date/time stamps
            $table->uuid(BC::COL_INV_ID)->nullable()->index(); // ? linked invoice, if any
            $table->uuid(BC::COL_BL_ID)->nullable()->index(); // ? linked bill, if any
            $table->uuid(BC::COL_OD_ID)->nullable()->index(); // ? linked order, if any
            $table->uuid(BC::COL_TRS_ID)->nullable()->index(); // ? linked transaction, if any
            $table->uuid(BC::COL_PAY_ID)->nullable()->index(); // ? linked payment, if any
            $table->uuid(BC::COL_PSLP_ID)->nullable()->index(); // ? linked payslip, if any
            $table->uuid(BC::COL_EXP_ID)->nullable()->index(); // ? linked expense, if any
            $table->uuid(BC::COL_POS_ID)->nullable()->index(); // ? linked POS, if any
            $table->uuid(BC::COL_POS_PAY_ID)->nullable()->index(); // ? linked POS payment, if any; IF not null and the BC::COL_POS_ID can be used to select a valid POS, then BC::COL_POS_ID in the DC::TABLE_POS_PAY row must be equal to this journal entry's BC::COL_POS_ID, else nullified
            $table->uuid(BC::COL_CRD_NT_ID)->nullable()->index(); // ? linked credit note, if any
            $table->uuid(BC::COL_DBT_NT_ID)->nullable()->index(); // ? linked debit note, if any
            $table->uuid(BC::COL_LN_ID)->nullable()->index(); // ? linked loan, if any
            $table->uuid(BC::COL_ALW_ID)->nullable()->index(); // ? linked allowance, if any
            $table->uuid('revenue')->nullable()->index(); // ? linked revenue, if any
            $table->uuid('contract')->nullable()->index(); // ? linked contract, if any
            $table->uuid('deal')->nullable()->index(); // ? linked deal, if any
            $table->uuid(PJC::COL_JRN_ID)->nullable();
            $table->boolean(BC::COL_IS_RVS)->default(false)->nullable()->index(); // ?  is this a reversal journal entry // ? nullable for tests
            $table->uuid(BC::COL_RVSING_ID)->nullable()->index(); // ? if this is a reversal entry, the journal entry that is performing the reversal, null if this is not a reversal entry, MUST NEVER be the same as 'id' of this row neither of COL_RVSED_ID
            $table->uuid(BC::COL_RVSED_ID)->nullable()->index(); // ? the reversed entry id, MUST NEVER be the same as 'id' of this row neither of COL_RVSING_ID, and of course null if this is not a reversal entry,
            // * == CONFORMIDADE COM LEI BRASILEIRA == *
            $table->enum(BC::COL_BK_TP, array_column(LedgerBookType::cases(), 'value'))->default(LedgerBookType::GeneralLedger->value)->index();
            $table->string('nire', 20)->nullable(); // ? Número de Identificação do Registro de Empresas
            $table->string(BC::COL_HSH_ECD, 100)->nullable();
            $table->boolean(BC::COL_ECD_TRS)->default(false)->nullable()->index(); // ? nullable for tests, enforced as boolean at model
            $table->timestamp(BC::COL_ECD_AT)->nullable();
            $this->addNfeColumns($table);
            $table->uuid(BC::COL_ORG_USER_ID)->nullable()->index(); // ? original author of the journal entry, if imported from another system
            $table->string(BC::COL_IP_ADR, 45)->nullable()->index(); // ? IP address of the author at creation time
            $table->string(BC::COL_USR_AGT, 512)->nullable(); // ? user agent of the author at creation time
            // * ================================ *
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->json('taxes')->nullable(); // ? stores metadata about taxes and uuids for their rows in the taxes table
            $table->json('items')->nullable(); // ? list of items ids. IF not null, then the sum of debit and credit in the journal items linked to this journal entry MUST be equal to the total debit and credit of this journal entry; REJECTS items that are not of the same currency as this journal entry
            $table->json('transactions')->nullable(); // ? list of transaction ids. IF not null, then each transaction MUST have a journal item in this journal entry
            $table->softDeletes();
            $this->addAuditColumns($table);
            foreach (
                [
                    'author' => DC::TABLE_USERS,
                    'reviewer' => DC::TABLE_USERS,
                    'company' => DC::TABLE_USERS,
                    'branch' => DC::TABLE_BRANCHES,
                    'department' => DC::TABLE_DEPARTMENTS,
                    'project' => DC::TABLE_PROJECTS,
                    'document' => DC::TABLE_DOCS,
                    BC::COL_RVSING_ID => self::TABLE,
                    BC::COL_RVSED_ID => self::TABLE,
                    PJC::COL_JRN_ID => DC::TABLE_DOCS,
                    BC::COL_INV_ID => DC::TABLE_INVS,
                    BC::COL_BL_ID => DC::TABLE_BILLS,
                    BC::COL_OD_ID => DC::TABLE_ORDERS,
                    BC::COL_TRS_ID => DC::TABLE_TRS,
                    BC::COL_PAY_ID => DC::TABLE_PAY,
                    BC::COL_PSLP_ID => DC::TABLE_PAY_SLP,
                    BC::COL_EXP_ID => DC::TABLE_EXP,
                    BC::COL_POS_ID => DC::TABLE_POS,
                    BC::COL_POS_PAY_ID => DC::TABLE_POS_PAY,
                    BC::COL_CRD_NT_ID => DC::TABLE_CR_NOTES,
                    BC::COL_DBT_NT_ID => DC::TABLE_DB_NOTES,
                    BC::COL_LN_ID => DC::TABLE_LN,
                    BC::COL_ALW_ID => DC::TABLE_ALW,
                    'revenue' => DC::TABLE_RVN,
                    'contract' => DC::TABLE_CONTRACTS,
                    'deal' => DC::TABLE_DEALS,
                    BC::COL_ORG_USER_ID => DC::TABLE_USERS,
                ] as $col => $foreignTable
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($foreignTable)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'author',
                    'reviewer',
                    'company',
                    'branch',
                    'department',
                    'project',
                    'document',
                    BC::COL_RVSING_ID,
                    BC::COL_RVSED_ID,
                    PJC::COL_JRN_ID,
                    BC::COL_INV_ID,
                    BC::COL_BL_ID,
                    BC::COL_OD_ID,
                    BC::COL_TRS_ID,
                    BC::COL_PAY_ID,
                    BC::COL_PSLP_ID,
                    BC::COL_EXP_ID,
                    BC::COL_POS_ID,
                    BC::COL_POS_PAY_ID,
                    BC::COL_CRD_NT_ID,
                    BC::COL_DBT_NT_ID,
                    BC::COL_LN_ID,
                    BC::COL_ALW_ID,
                    'revenue',
                    'contract',
                    'deal',
                    BC::COL_ORG_USER_ID
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
