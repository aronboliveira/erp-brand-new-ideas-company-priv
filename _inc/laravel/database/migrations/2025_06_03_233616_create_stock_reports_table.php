<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateStockReportsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE           = 'stock_reports';
    private const COL_PRODUCT_ID  = 'product_id';
    private const COL_QUANTITY    = 'quantity';
    private const COL_TYPE_ID     = 'type_id';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->nullable(); // * generated as STK-RPT-{UUID}, checking uniqueness with do/while;
            $table->string('title')->index();
            $table->enum('type', [
                'pos',
                'purchase',
                'warehouse',
                'company',
                'vendor',
                'branch',
                'inventory',
                'financial',
                'accounting',
                'payroll',
                'hr',
                'employee',
                'customer',
                'supplier',
                'product',
                'service',
                'invoice',
                'payment',
                'receipt',
                'expense',
                'revenue',
                'profit_loss',
                'balance_sheet',
                'cash_flow',
                'tax',
                'vat',
                'budget',
                'forecast',
                'project',
                'task',
                'timesheet',
                'attendance',
                'leave',
                'performance',
                'asset',
                'depreciation',
                'bank_reconciliation',
                'general_ledger',
                'trial_balance',
                'accounts_receivable',
                'accounts_payable',
                'aged_receivables',
                'aged_payables',
                'sales_order',
                'purchase_order',
                'delivery_note',
                'credit_note',
                'debit_note',
                'stock_movement',
                'stock_valuation',
                'stock_transfer',
                'manufacturing',
                'production',
                'quality_control',
                'audit',
                'compliance',
                'custom',
            ])->index();
            $table->uuid(self::COL_PRODUCT_ID);
            $table->string(self::COL_TYPE_ID)->index(); // * this is meant to be a polymorphic key (mostly uuids) according to the 'type', respectively from DC::TABLE_POS, DC::TABLE_PUR_ORDERS, DC::TABLE_WAREHOUSES, DC::TABLE_USERS, DC::TABLE_VENDORS || DC::TABLE_USERS, DC::TABLE_BRANCHES, DC::TABLE_VENDORS || DC::TABLE_USERS || DC
            $table->integer(self::COL_QUANTITY)->default(0);
            $table->uuid(PJC::COL_COA_ID)->nullable();
            $table->text('description')->nullable();
            foreach (
                [
                    self::COL_PRODUCT_ID => DC::TABLE_PROD_SERVS,
                    PJC::COL_COA_ID   => DC::TABLE_COAS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_PRODUCT_ID,
                    PJC::COL_COA_ID
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
