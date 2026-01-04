<?php

use App\Config\Constants\{ActivitiesConstants as AC, BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{AppModuleType, EvaluationStatus};
use App\Traits\{HasCreditCardInfo, HasNfeColumns, HasNullableAuditColumns, HasPaymentColumns, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateExpensesTable extends Migration
{
    use HasCreditCardInfo, HasNfeColumns, HasNullableAuditColumns, HasPaymentColumns, TracksFailures;
    private const TABLE = DC::TABLE_EXP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('module', array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Financial->value)->nullable()->index();
            $table->uuid('company')->nullable()->index();
            $table->uuid('branch')->nullable()->index();
            $table->uuid('department')->nullable()->index();
            $table->uuid('accountant')->nullable(); // ? must reference a user with UserType::Accountant->value, else nullified
            $table->uuid('vendor')->nullable()->index();
            $table->string('name', 1024)->index();
            $table->date('date')->nullable(); // * never explained, but will be assumed as the due_date
            $table->decimal(BC::COL_TTL_AMT, 16, 2)->default(0.00); // * total amount after taxes
            $this->addBasicPaymentColumns($table, nullableInvoice: true);
            $this->addCreditCardInfoColumns($table, nullableCard: true);
            $table->uuid('pos')->nullable()->index();
            $table->uuid('bill')->nullable()->index();
            $table->uuid('order')->nullable()->index();
            $table->string('currency', 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable();
            $table->decimal(BC::COL_EXC_RT, 16, 6)->default(1.000000); // * exchange rate to the base currency (SC::DEF_SITE_CURRENCY_ID) at the moment of creation of the expense
            $table->enum('evaluation', array_filter(array_column(EvaluationStatus::cases(), 'value')), fn($s) => $s !== EvaluationStatus::NotStarted)->default(EvaluationStatus::Pending->value)->nullable(); // ? nullable for tests, enforced at model level
            $table->string('attachment', 254)->nullable(); // * this is not clear, but we will assume it's a URL, path or a valid id (a uuid) for the Document model (see filters in BC::COL_ADD_ATTACH)
            $table->uuid(PJC::COL_PJ_ID)->nullable()->index();
            $table->uuid(PJC::COL_PJ_TSK_ID)->nullable();
            $table->uuid(AC::COL_TSK_ID)->nullable();
            $table->string('receipt', 254)->nullable()->index(); // * can be a uuid referencing a Document model, a file path or a valid URL string. Tested just like 'attachment' column
            $table->json('taxes')->nullable(); // * storing tax ids or names, filtered against the Tax model. The 'amount' column of the Tax model will be used to calculate the total tax for the expense in the model annd update dynamically the row's COL_TTL_AMT ('total_amount') column
            $this->addNfeColumns($table);
            foreach (
                [
                    'company' => DC::TABLE_USERS,
                    'branch' => DC::TABLE_BRANCHES,
                    'department' => DC::TABLE_DEPARTMENTS,
                    'accountant' => DC::TABLE_USERS,
                    'vendor' => DC::TABLE_VENDORS,
                    'pos' => DC::TABLE_POS,
                    'bill' => DC::TABLE_BILLS,
                    'order' => DC::TABLE_ORDERS,
                    PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                    PJC::COL_PJ_TSK_ID => DC::TABLE_PROJ_TSKS,
                    AC::COL_TSK_ID => DC::TABLE_TASKS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
            $table->softDeletes();
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropBasicPaymentColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'company',
                    'branch',
                    'department',
                    'accountant',
                    'vendor',
                    'pos',
                    'bill',
                    'order',
                    PJC::COL_PJ_ID,
                    PJC::COL_PJ_TSK_ID,
                    AC::COL_TSK_ID,
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
