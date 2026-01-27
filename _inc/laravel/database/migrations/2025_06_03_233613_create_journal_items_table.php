<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC};
use App\Enums\{TransactionType, TransferType};
use App\Traits\{HasNfeColumns, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJournalItemsTable extends Migration
{
    use HasNfeColumns, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JRN_IT;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 254)->nullable()->unique(); // ? unique code for the journal item, generated at model level or inserted, with the code pattern JIT-{UUID}-{timestamp}
            $table->uuid('journal')->index();
            $table->uuid('account')->index();
            $table->unsignedSmallInteger('line')->nullable()->index(); // ? nullable for tests
            $table->unique(['journal', 'line']);
            $table->enum('posting_type', ['debit', 'credit'])->nullable()->default('debit')->index(); // ? nullable for tests, enforced at model
            $table->float('debit', 20, 6)->default(0.00); // * ideally these should be decimals, but keeping for legacy
            $table->float('credit', 20, 6)->default(0.00);
            $table->string('currency', 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable()->index(); // ? nullable for tests, REJECTED if not follows the currency of the linked journal entry
            $table->decimal(BC::COL_EXC_RT, 16, 6)->default(1.000000)->nullable()->index(); // ? nullable for tests, enforced at model level
            $table->uuid(BC::COL_BNK_ACC)->nullable()->index(); // ? linked bank account, if any
            $table->date(BC::COL_BNK_EXT_DT)->nullable()->index(); // ? bank extraction date, if any
            $table->uuid('transaction')->nullable()->index(); // ? linked transaction, if any
            $table->enum(BC::COL_TRS_TP, array_column(TransactionType::cases(), 'value'))->nullable()->index(); // ? nullable for tests, if 'transaction' is set, must match its type forcefully (as the source of truth)
            $table->uuid('transfer')->nullable()->index(); // ? linked transfer, if any
            $table->uuid('payment')->nullable()->index(); // ? linked payment, if any
            $table->enum(BC::COL_TRF_TP, array_column(TransferType::cases(), 'value'))->nullable()->index(); // ? nullable for tests, if 'payment' is set, must match its method forcefully (as the source of truth)
            $table->string(BC::COL_PIX_KEY, 100)->nullable()->index(); // ? linked pix key, if any
            $table->string(BC::COL_CHK_NM, 20)->nullable()->index(); // ? linked check number, if any
            $table->string(BC::COL_TED_DOC_N, 50)->nullable()->index(); // ? linked TED document number, if any
            $table->uuid('company')->nullable()->index(); // ? the company this journal item belongs to, not necessarily of the author, but defaulted as such if the author has a valid FK with UC::COL_EMP_ID on his row as a user, then in the fetched employee row querying for the CC::COL_BRC_ID or 'branch' (Schema::hasColumn for it) to finally get the 'company' column (that is, this must be cached); MUST refer to a user whereIn('type', ['company', 'vendor'])
            $table->uuid('branch')->nullable()->index(); // ? the branch this journal item belongs to, not necessarily of the author, but defaulted as such if the author has a valid FK with UC::COL_EMP_ID on his row as a user
            $table->uuid('department')->nullable()->index(); // ? the department being referred to, not necessarily of the author, but defaulted as such if the author has a valid FK with UC::COL_EMP_ID on his row as a user
            $table->uuid('project')->nullable()->index(); // ? the project being referred to, if any
            $table->uuid('entity')->nullable()->index(); // ? linked entity, if any, cacheing the cpf/cnpj (UC::COL_ENT_CD column) number and type (UC::COL_ENT_TP column), type and name at model level
            $table->text('description')->nullable();
            $table->text('memo')->nullable();
            $table->text('notes')->nullable();
            $table->boolean(BC::COL_IS_RCC)->default(false)->nullable()->index(); // ?  is this a reversal journal item // ? nullable for tests
            $table->dateTime(BC::COL_RCC_DT)->nullable()->index(); // ? date of reconciliation, if any
            $table->string(BC::COL_RCC_DOC)->nullable()->index(); // ? reconciliation document, if any
            $this->addNfeColumns($table);
            $table->json('attachments')->nullable();
            $table->json('taxes')->nullable(); // ? stores metadata about taxes and uuids for their rows in the taxes table, cacheing at model level the total tax_rate and tax_amount
            $table->json('categories')->nullable(); // ? flexible list of categories for tracking, cacheing at model level the category names
            $table->json('metadata')->nullable();
            foreach (
                [
                    'journal' => DC::TABLE_JOURNAL_ENTRIES,
                    'account' => DC::TABLE_COAS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->restrictOnDelete();
            foreach (
                [
                    'company' => DC::TABLE_USERS,
                    'branch' => DC::TABLE_BRANCHES,
                    'department' => DC::TABLE_DEPARTMENTS,
                    'project' => DC::TABLE_PROJECTS,
                    'entity' => DC::TABLE_USERS,
                    BC::COL_RCC_DOC => DC::TABLE_DOCS,
                    'transaction' => DC::TABLE_TRS,
                    'payment' => DC::TABLE_PAY,
                    'transfer' => DC::TABLE_TRFS,
                    BC::COL_BNK_ACC => DC::TABLE_BANK_ACC,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->nullOnDelete();
            $table->softDeletes();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $cols = [
                'journal',
                'account',
                'company',
                'branch',
                'department',
                'project',
                'entity',
                BC::COL_RCC_DOC,
                'transaction',
                'payment',
                'transfer',
                BC::COL_BNK_ACC,
            ];

            foreach ($cols as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) && $table->dropForeign([$col]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to drop foreign key for ' . $col . ': ' . $e->getMessage());
                }
            }

            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
