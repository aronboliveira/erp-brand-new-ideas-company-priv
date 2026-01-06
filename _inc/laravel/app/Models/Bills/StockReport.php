<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class StockReport extends Model
{
    use UsesUuids;
    public const TYPE_POS = 'pos';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_WAREHOUSE = 'warehouse';
    public const TYPE_COMPANY = 'company';
    public const TYPE_VENDOR = 'vendor';
    public const TYPE_BRANCH = 'branch';
    public const TYPE_SALES = 'sales';
    public const TYPE_INVENTORY = 'inventory';
    public const TYPE_FINANCIAL = 'financial';
    public const TYPE_ACCOUNTING = 'accounting';
    public const TYPE_PAYROLL = 'payroll';
    public const TYPE_HR = 'hr';
    public const TYPE_EMPLOYEE = 'employee';
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_SUPPLIER = 'supplier';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_PROFIT_LOSS = 'profit_loss';
    public const TYPE_BALANCE_SHEET = 'balance_sheet';
    public const TYPE_CASH_FLOW = 'cash_flow';
    public const TYPE_TAX = 'tax';
    public const TYPE_VAT = 'vat';
    public const TYPE_BUDGET = 'budget';
    public const TYPE_FORECAST = 'forecast';
    public const TYPE_PROJECT = 'project';
    public const TYPE_TASK = 'task';
    public const TYPE_TIMESHEET = 'timesheet';
    public const TYPE_ATTENDANCE = 'attendance';
    public const TYPE_LEAVE = 'leave';
    public const TYPE_PERFORMANCE = 'performance';
    public const TYPE_ASSET = 'asset';
    public const TYPE_DEPRECIATION = 'depreciation';
    public const TYPE_BANK_RECONCILIATION = 'bank_reconciliation';
    public const TYPE_GENERAL_LEDGER = 'general_ledger';
    public const TYPE_TRIAL_BALANCE = 'trial_balance';
    public const TYPE_ACCOUNTS_RECEIVABLE = 'accounts_receivable';
    public const TYPE_ACCOUNTS_PAYABLE = 'accounts_payable';
    public const TYPE_AGED_RECEIVABLES = 'aged_receivables';
    public const TYPE_AGED_PAYABLES = 'aged_payables';
    public const TYPE_SALES_ORDER = 'sales_order';
    public const TYPE_PURCHASE_ORDER = 'purchase_order';
    public const TYPE_DELIVERY_NOTE = 'delivery_note';
    public const TYPE_CREDIT_NOTE = 'credit_note';
    public const TYPE_DEBIT_NOTE = 'debit_note';
    public const TYPE_STOCK_MOVEMENT = 'stock_movement';
    public const TYPE_STOCK_VALUATION = 'stock_valuation';
    public const TYPE_STOCK_TRANSFER = 'stock_transfer';
    public const TYPE_MANUFACTURING = 'manufacturing';
    public const TYPE_PRODUCTION = 'production';
    public const TYPE_QUALITY_CONTROL = 'quality_control';
    public const TYPE_AUDIT = 'audit';
    public const TYPE_COMPLIANCE = 'compliance';
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_MAPPINGS = [
        self::TYPE_POS => [
            DC::TABLE_POS,
            DC::TABLE_POS_PRD,
            DC::TABLE_POS_PAY,
        ],
        self::TYPE_PURCHASE => [
            DC::TABLE_PURCHASES,
            DC::TABLE_PRC_PRD,
            DC::TABLE_PRC_PAY,
        ],
        self::TYPE_WAREHOUSE => [
            DC::TABLE_WHS,
            DC::TABLE_WRH_PRD,
            DC::TABLE_WRH_TRF,
        ],
        self::TYPE_COMPANY => [
            DC::TABLE_SETTINGS,
        ],
        self::TYPE_VENDOR => [
            DC::TABLE_VENDORS,
        ],
        self::TYPE_BRANCH => [
            DC::TABLE_BRANCHES,
        ],
        self::TYPE_SALES => [
            DC::TABLE_INVS,
            DC::TABLE_INV_PRD,
            DC::TABLE_INV_PAY,
            DC::TABLE_ORDERS,
        ],
        self::TYPE_INVENTORY => [
            DC::TABLE_PROD_SERVS,
            DC::TABLE_PRODUCTS,
            DC::TABLE_WRH_PRD,
            DC::TABLE_POS_PRD,
        ],
        self::TYPE_FINANCIAL => [
            DC::TABLE_TRS,
            DC::TABLE_PAY,
            DC::TABLE_RVN,
            DC::TABLE_EXP,
        ],
        self::TYPE_ACCOUNTING => [
            DC::TABLE_COAS,
            DC::TABLE_COA_TYPES,
            DC::TABLE_COA_SUBTYPES,
            DC::TABLE_JOURNAL_ENTRIES,
            DC::TABLE_JRN_IT,
        ],
        self::TYPE_PAYROLL => [
            DC::TABLE_PAY_SLP,
            DC::TABLE_SSLR,
            DC::TABLE_ALW,
            DC::TABLE_ST_DD,
            DC::TABLE_OT_PYMTS,
            DC::TABLE_LN,
        ],
        self::TYPE_HR => [
            DC::TABLE_EMPLOYEES,
            DC::TABLE_EDOCS,
            DC::TABLE_EATD,
            DC::TABLE_TERMINATIONS,
            DC::TABLE_RSG,
        ],
        self::TYPE_EMPLOYEE => [
            DC::TABLE_EMPLOYEES,
            DC::TABLE_EDOCS,
            DC::TABLE_EATD,
        ],
        self::TYPE_CUSTOMER => [
            DC::TABLE_CUSTOMERS,
            DC::TABLE_CLIENTS,
        ],
        self::TYPE_SUPPLIER => [
            DC::TABLE_VENDORS,
        ],
        self::TYPE_PRODUCT => [
            DC::TABLE_PROD_SERVS,
            DC::TABLE_PRODUCTS,
            DC::TABLE_PRD_CAT,
        ],
        self::TYPE_SERVICE => [
            DC::TABLE_PROD_SERVS,
        ],
        self::TYPE_INVOICE => [
            DC::TABLE_INVS,
            DC::TABLE_INV_PRD,
            DC::TABLE_INV_PAY,
            DC::TABLE_INV_BANK_TRANSFERS,
        ],
        self::TYPE_PAYMENT => [
            DC::TABLE_PAY,
            DC::TABLE_INV_PAY,
            DC::TABLE_BL_PAY,
            DC::TABLE_POS_PAY,
            DC::TABLE_PRC_PAY,
        ],
        self::TYPE_RECEIPT => [
            DC::TABLE_PAY,
            DC::TABLE_INV_PAY,
        ],
        self::TYPE_EXPENSE => [
            DC::TABLE_EXP,
            DC::TABLE_BILLS,
            DC::TABLE_BL_PRD,
        ],
        self::TYPE_REVENUE => [
            DC::TABLE_RVN,
            DC::TABLE_INVS,
        ],
        self::TYPE_PROFIT_LOSS => [
            DC::TABLE_TRS,
            DC::TABLE_RVN,
            DC::TABLE_EXP,
        ],
        self::TYPE_BALANCE_SHEET => [
            DC::TABLE_COAS,
            DC::TABLE_TRS,
        ],
        self::TYPE_CASH_FLOW => [
            DC::TABLE_TRS,
            DC::TABLE_BANK_ACC,
            DC::TABLE_BNK_TRF,
        ],
        self::TYPE_TAX => [
            DC::TABLE_TAXES,
            DC::TABLE_INVS,
            DC::TABLE_BILLS,
        ],
        self::TYPE_VAT => [
            DC::TABLE_TAXES,
        ],
        self::TYPE_BUDGET => [
            DC::TABLE_BDG,
        ],
        self::TYPE_FORECAST => [
            DC::TABLE_BDG,
        ],
        self::TYPE_PROJECT => [
            DC::TABLE_PROJECTS,
            DC::TABLE_PROJ_TSKS,
            DC::TABLE_PRJ_USR,
            DC::TABLE_PRJ_INV,
        ],
        self::TYPE_TASK => [
            DC::TABLE_TASKS,
            DC::TABLE_PROJ_TSKS,
            DC::TABLE_TSK_CMT,
            DC::TABLE_TSK_FL,
        ],
        self::TYPE_TIMESHEET => [
            DC::TABLE_TMS,
            DC::TABLE_TM_TRK,
        ],
        self::TYPE_ATTENDANCE => [
            DC::TABLE_EATD,
        ],
        self::TYPE_LEAVE => [
            DC::TABLE_LV,
            DC::TABLE_LEAVE_TYPES,
        ],
        self::TYPE_PERFORMANCE => [
            DC::TABLE_APR,
            DC::TABLE_IND,
            DC::TABLE_GL,
            DC::TABLE_GL_TRK,
        ],
        self::TYPE_ASSET => [
            DC::TABLE_AST,
        ],
        self::TYPE_DEPRECIATION => [
            DC::TABLE_AST,
        ],
        self::TYPE_BANK_RECONCILIATION => [
            DC::TABLE_BANK_ACC,
            DC::TABLE_BNK_TRF,
            DC::TABLE_TRS,
        ],
        self::TYPE_GENERAL_LEDGER => [
            DC::TABLE_COAS,
            DC::TABLE_JOURNAL_ENTRIES,
            DC::TABLE_JRN_IT,
        ],
        self::TYPE_TRIAL_BALANCE => [
            DC::TABLE_COAS,
            DC::TABLE_TRS,
        ],
        self::TYPE_ACCOUNTS_RECEIVABLE => [
            DC::TABLE_INVS,
            DC::TABLE_CUSTOMERS,
            DC::TABLE_INV_PAY,
        ],
        self::TYPE_ACCOUNTS_PAYABLE => [
            DC::TABLE_BILLS,
            DC::TABLE_VENDORS,
            DC::TABLE_BL_PAY,
        ],
        self::TYPE_AGED_RECEIVABLES => [
            DC::TABLE_INVS,
            DC::TABLE_CUSTOMERS,
        ],
        self::TYPE_AGED_PAYABLES => [
            DC::TABLE_BILLS,
            DC::TABLE_VENDORS,
        ],
        self::TYPE_SALES_ORDER => [
            DC::TABLE_ORDERS,
            DC::TABLE_INVS,
        ],
        self::TYPE_PURCHASE_ORDER => [
            DC::TABLE_PURCHASES,
        ],
        self::TYPE_DELIVERY_NOTE => [
            DC::TABLE_WRH_TRF,
        ],
        self::TYPE_CREDIT_NOTE => [
            DC::TABLE_CR_NOTES,
        ],
        self::TYPE_DEBIT_NOTE => [
            DC::TABLE_DB_NOTES,
        ],
        self::TYPE_STOCK_MOVEMENT => [
            DC::TABLE_WRH_PRD,
            DC::TABLE_WRH_TRF,
        ],
        self::TYPE_STOCK_VALUATION => [
            DC::TABLE_PRODUCTS,
            DC::TABLE_WRH_PRD,
        ],
        self::TYPE_STOCK_TRANSFER => [
            DC::TABLE_WRH_TRF,
        ],
        self::TYPE_MANUFACTURING => [
            DC::TABLE_PRODUCTS,
        ],
        self::TYPE_PRODUCTION => [
            DC::TABLE_PRODUCTS,
        ],
        self::TYPE_QUALITY_CONTROL => [
            DC::TABLE_PRODUCTS,
        ],
        self::TYPE_AUDIT => [
            DC::TABLE_LOG_ACTS,
            DC::TABLE_ACT_LOG,
        ],
        self::TYPE_COMPLIANCE => [
            DC::TABLE_DOCS,
            DC::TABLE_CPN_POL,
        ],
        self::TYPE_CUSTOM => [],
    ];
    private const COL_CREATED_BY = 'created_by';
    private const COL_DESCRIPTION = 'description';
    private const COL_PRODUCT_ID = 'product_id';
    private const COL_QUANTITY   = 'quantity';
    private const COL_TYPE       = 'type';
    private const COL_TYPE_ID    = 'type_id';

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_QUANTITY,
        self::COL_TYPE,
        self::COL_TYPE_ID,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
    ];

    public function product(): HasOne
    {
        return $this
            ->hasOne(ProductService::class, 'id', self::COL_PRODUCT_ID);
        // * consider using belongsTo(ProductService::class, self::COL_PRODUCT_ID)
    }

    public static function products(string $productCsv): mixed
    {
        $categoryArr = explode(',', $productCsv);
        foreach ($categoryArr as $product) {
            $productObj = ProductService::find($product);
            $categoryArr = isset($productObj)
                ? $productObj->name
                : '';
        }
        return $categoryArr;
        // * TODO: logic returns string; consider refactoring to return array of names
    }


    public static function getTablesByType(string $type): array
    {
        return self::TYPE_MAPPINGS[$type] ?? [];
    }

    public static function getTypeByTable(string $table): ?string
    {
        foreach (self::TYPE_MAPPINGS as $type => $tables)
            if (in_array($table, $tables, true))
                return $type;
        return null;
    }

    public static function isValidTypeTable(string $type, string $table): bool
    {
        return in_array($table, self::TYPE_MAPPINGS[$type] ?? [], true);
    }

    public static function getAllTypes(): array
    {
        return array_keys(self::TYPE_MAPPINGS);
    }
}
