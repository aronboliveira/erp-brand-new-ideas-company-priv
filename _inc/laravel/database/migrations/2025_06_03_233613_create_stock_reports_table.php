<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Models\StockReport;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateStockReportsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE           = DC::TABLE_STK_RPT;
    private const COL_TYPE_ID     = 'type_id';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->nullable(); // * generated as STK-RPT-{UUID}, checking uniqueness with do/while;
            $table->string('title')->index();
            $table->enum('type', [
                StockReport::TYPE_POS,
                StockReport::TYPE_PURCHASE,
                StockReport::TYPE_WAREHOUSE,
                StockReport::TYPE_COMPANY,
                StockReport::TYPE_VENDOR,
                StockReport::TYPE_BRANCH,
                StockReport::TYPE_INVENTORY,
                StockReport::TYPE_FINANCIAL,
                StockReport::TYPE_ACCOUNTING,
                StockReport::TYPE_PAYROLL,
                StockReport::TYPE_HR,
                StockReport::TYPE_EMPLOYEE,
                StockReport::TYPE_CUSTOMER,
                StockReport::TYPE_SUPPLIER,
                StockReport::TYPE_PRODUCT,
                StockReport::TYPE_SERVICE,
                StockReport::TYPE_INVOICE,
                StockReport::TYPE_PAYMENT,
                StockReport::TYPE_RECEIPT,
                StockReport::TYPE_EXPENSE,
                StockReport::TYPE_REVENUE,
                StockReport::TYPE_PROFIT_LOSS,
                StockReport::TYPE_BALANCE_SHEET,
                StockReport::TYPE_CASH_FLOW,
                StockReport::TYPE_TAX,
                StockReport::TYPE_VAT,
                StockReport::TYPE_BUDGET,
                StockReport::TYPE_FORECAST,
                StockReport::TYPE_PROJECT,
                StockReport::TYPE_TASK,
                StockReport::TYPE_TIMESHEET,
                StockReport::TYPE_ATTENDANCE,
                StockReport::TYPE_LEAVE,
                StockReport::TYPE_PERFORMANCE,
                StockReport::TYPE_ASSET,
                StockReport::TYPE_DEPRECIATION,
                StockReport::TYPE_BANK_RECONCILIATION,
                StockReport::TYPE_GENERAL_LEDGER,
                StockReport::TYPE_TRIAL_BALANCE,
                StockReport::TYPE_ACCOUNTS_RECEIVABLE,
                StockReport::TYPE_ACCOUNTS_PAYABLE,
                StockReport::TYPE_AGED_RECEIVABLES,
                StockReport::TYPE_AGED_PAYABLES,
                StockReport::TYPE_SALES_ORDER,
                StockReport::TYPE_PURCHASE_ORDER,
                StockReport::TYPE_DELIVERY_NOTE,
                StockReport::TYPE_CREDIT_NOTE,
                StockReport::TYPE_DEBIT_NOTE,
                StockReport::TYPE_STOCK_MOVEMENT,
                StockReport::TYPE_STOCK_VALUATION,
                StockReport::TYPE_STOCK_TRANSFER,
                StockReport::TYPE_MANUFACTURING,
                StockReport::TYPE_PRODUCTION,
                StockReport::TYPE_QUALITY_CONTROL,
                StockReport::TYPE_AUDIT,
                StockReport::TYPE_COMPLIANCE,
                StockReport::TYPE_CUSTOM,
            ])->index();
            $table->unsignedBigInteger('quantity')->default(0);
            $table->string(self::COL_TYPE_ID)->index(); // * this is meant to be a polymorphic key (mostly uuids) according to the 'type', respectively from DC::TABLE_POS, DC::TABLE_PUR_ORDERS, DC::TABLE_WAREHOUSES, DC::TABLE_USERS, DC::TABLE_VENDORS || DC::TABLE_USERS, DC::TABLE_BRANCHES, DC::TABLE_VENDORS || DC::TABLE_USERS || DC
            $table->uuid(BC::COL_PRD_ID)->nullable()->index(); // ? if PRD_SV_ID is null and PRD_ID is not null AND has a valid PRD_SV_ID, then mirror into PRD_SV_ID here
            $table->uuid(BC::COL_PRD_SV_ID)->nullable()->index(); // ? there must be a valid id here or in COL_PRD_ID, else the model throws
            $table->uuid(PJC::COL_COA_ID)->nullable();
            $table->uuid(PJC::COL_PLN_SCHD_ID)->nullable()->index();
            $table->text('description')->nullable();
            $table->json('receipts')->nullable();
            $table->json('attachments')->nullable();
            $table->json('filters')->nullable();
            foreach (
                [
                    BC::COL_PRD_ID => DC::TABLE_PROD_SERVS,
                    PJC::COL_COA_ID   => DC::TABLE_COAS,
                    PJC::COL_PLN_SCHD_ID => DC::TABLE_PLN_SCHD,
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
            foreach (
                [
                    BC::COL_PRD_ID,
                    PJC::COL_COA_ID,
                    PJC::COL_PLN_SCHD_ID
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
