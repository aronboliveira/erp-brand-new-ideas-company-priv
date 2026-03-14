<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC, TemplatesConstants as TC, UsersConstants as UC};
use App\Traits\{DefinesDates, HasAuditFields, NormalizesArrays, PlansByHierarchy, StoresManyRefJson, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\{Collection, Str};
/**
 * @property int|null $product_id
 */

class StockReport extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays, PlansByHierarchy, StoresManyRefJson, DefinesDates;

    protected $table = DC::TABLE_STK_RPT;

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'code',
        'title',
        'type',
        BC::COL_TP_ID,
        'quantity',
        BC::COL_PRD_ID,
        BC::COL_PRD_SV_ID,
        PJC::COL_COA_ID,
        PJC::COL_PLN_SCHD_ID,
        BC::COL_JRN_ENT_ID,
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        'description',
        BC::COL_IS_PDF_AVL,
        BC::COL_IS_SST_AVL,
        BC::COL_IS_DOC_AVL,
        BC::COL_IS_WEB_AVL,
        BC::COL_IS_PBI_AVL,
        BC::COL_SBM_BY,
        BC::COL_SBM_AT,
        BC::COL_APV_BY,
        BC::COL_APV_AT,
        PJC::COL_REJ_BY,
        PJC::COL_REJ_AT,
        'receipts',
        'attachments',
        'filters',
        'metadata',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'quantity'            => 'integer',
        BC::COL_IS_PDF_AVL     => 'boolean',
        BC::COL_IS_SST_AVL     => 'boolean',
        BC::COL_IS_DOC_AVL     => 'boolean',
        BC::COL_IS_WEB_AVL     => 'boolean',
        BC::COL_IS_PBI_AVL     => 'boolean',
        BC::COL_SBM_AT         => 'datetime',
        BC::COL_APV_AT         => 'datetime',
        PJC::COL_REJ_AT        => 'datetime',
        PJC::COL_S_DT          => 'date',
        PJC::COL_E_DT          => 'date',
        'receipts'             => 'array',
        'attachments'          => 'array',
        'filters'              => 'array',
        'metadata'             => 'array',
    ];

    protected $with = [
        'creator',
    ];

    protected $appends = [
        'available_formats',
        'has_valid_product_reference',
        'type_tables',

        'is_pos',
        'is_purchase',
        'is_warehouse',
        'is_sales',

        'is_financial',
        'financial_table',

        'is_transaction',
        'is_payment',
        'is_invoice',
        'is_bill',
        'is_expense',
        'is_revenue',
        'is_budget',
        'is_credit_note',
        'is_debit_note',
        'is_loan',

        'is_accounting',
        'accounting_table',
        'is_chart_account',
        'is_journal_entry',
        'is_journal_item',

        'is_contract',

        'is_hr',
        'hr_table',
        'is_employee',
        'is_employee_document',
        'is_employee_attendance',
        'is_termination',
        'is_resignation',
        'is_award',
        'is_award_type',
        'is_allowance',
        'is_allowance_option',
        'is_overtime',
        'is_leave',
        'is_leave_type',
        'is_promotion',
        'is_appraisal',
        'entity_table',
        'entity_type',
        'is_hr',
        'hr_table',
        'is_payroll',
        'payroll_table',
        'is_inventory',
        'inventory_table',
        'inventory_summary',
        'payments_summary',
        'bank_transfers_summary',
    ];

    protected static array $stockReportSchemaHasTableCache = [];
    protected static array $stockReportSchemaHasColumnCache = [];
    protected static array $firstExistingColumnCache = [];
    protected static array $typeTablesCache = [];
    protected static array $financialTableHintCache = [];
    protected static array $accountingTableHintCache = [];
    protected static array $contractTableHintCache = [];
    protected static array $hrTableHintCache = [];
    protected static array $typeByTableCache = [];
    protected static array $genericTableHintCache = [];
    protected static array $entityTableCache = [];
    protected static array $columnTypeCache = [];
    protected static array $numericColumnCache = [];
    protected static array $firstExistingNumericColumnCache = [];

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
            DC::TABLE_POS, // Pos
            DC::TABLE_POS_PRD, // PosProduct
            DC::TABLE_POS_PAY, // PosPayment
        ],
        self::TYPE_PURCHASE => [
            DC::TABLE_PURCHASES, // Purchase
            DC::TABLE_PRC_PRD, // PurchaseProduct
            DC::TABLE_PRC_PAY, // PurchasePayment
        ],
        self::TYPE_WAREHOUSE => [
            DC::TABLE_WHS, // Warehouse
            DC::TABLE_WRH_PRD, // WarehouseProduct
            DC::TABLE_WRH_TRF, // WarehouseTransfer
        ],
        self::TYPE_SALES => [
            DC::TABLE_PURCHASES, // Purchase
            DC::TABLE_PRC_PAY, // PurchasePayment
            DC::TABLE_ORDERS, // Order
            DC::TABLE_DEALS, // Deal
            DC::TABLE_DL_CALLS, // DealCall
            DC::TABLE_DL_EMAILS, // DealEmail
            DC::TABLE_DL_FL, // DealFile
            DC::TABLE_DL_TSK, // DealTask
            'deal_discussions', // DealDiscussion
            DC::TABLE_LEADS, // Lead
            DC::TABLE_LD_CALLS, // LeadCall
            DC::TABLE_LD_DSC, // LeadDiscussion
            DC::TABLE_LD_EMAILS, // LeadEmail
            DC::TABLE_LD_FILES, // LeadFile
            DC::TABLE_LEAD_STAGES, // LeadStage
            DC::TABLE_CONTRACTS // Contract
        ],
        self::TYPE_INVENTORY => [
            DC::TABLE_PROD_SERVS, // ProductService
            DC::TABLE_PRODUCTS, // Product
            DC::TABLE_WRH_PRD, // WarehouseProduct
            DC::TABLE_POS_PRD, // PosProduct
            DC::TABLE_PPS_PRD, // ProposalProduct
            DC::TABLE_BL_PRD, // BillProduct
            DC::TABLE_INV_PRD, // InvoiceProduct
            DC::TABLE_PRC_PRD, // PurchaseProduct
        ],
        self::TYPE_FINANCIAL => [
            DC::TABLE_TRS, // Transaction
            DC::TABLE_PAY, // Payment
            DC::TABLE_RVN, // Revenue
            DC::TABLE_EXP, // Expense
            DC::TABLE_PROPOSALS, // Proposal
            DC::TABLE_DEALS, // Deal
            DC::TABLE_BILLS, // Bill
            DC::TABLE_INVS, // Invoice
            DC::TABLE_BDG, // Budget
            DC::TABLE_CR_NOTES, // CreditNote
            DC::TABLE_DB_NOTES, // DebitNote
            DC::TABLE_CONTRACTS, // Contract
            DC::TABLE_LN // Loan
        ],
        self::TYPE_ACCOUNTING => [
            DC::TABLE_COAS, // ChartOfAccount
            DC::TABLE_COA_TYPES, // ChartOfAccountType
            DC::TABLE_COA_SUBTYPES, // ChartOfAccountSubtype
            DC::TABLE_JOURNAL_ENTRIES, // JournalEntry
            DC::TABLE_JRN_IT, // JournalItem
            DC::TABLE_CONTRACTS, // Contract
        ],
        self::TYPE_COMPANY => [
            DC::TABLE_USERS, // UserType::Company
            DC::TABLE_BRANCHES, // Branch
            DC::TABLE_DEPARTMENTS, // Department
        ],
        self::TYPE_VENDOR => [
            DC::TABLE_VENDORS, // Vendor
            DC::TABLE_USERS, // UserType::Vendor
        ],
        self::TYPE_BRANCH => [
            DC::TABLE_BRANCHES, // Branch
            DC::TABLE_DEPARTMENTS, // Department
        ],
        self::TYPE_HR => [
            DC::TABLE_EMPLOYEES, // Employee
            DC::TABLE_EDOCS, // EmployeeDocument
            DC::TABLE_EATD, // EmployeeAttendance
            DC::TABLE_TERMINATIONS, // Termination
            DC::TABLE_RSG, // Resignation
            DC::TABLE_CONTRACTS, // Contract
            DC::TABLE_AWD, // Award
            DC::TABLE_AWD_TPS,
            DC::TABLE_ALW, // Allowance
            DC::TABLE_ALLOWANCE_OPTS,
            DC::TABLE_OVT, // Overtime
            DC::TABLE_LV, // Leave
            DC::TABLE_LEAVE_TYPES, // LeaveType
            DC::TABLE_PRMT, // Promotion
            DC::TABLE_APR, // Appraisal
        ],
        self::TYPE_PAYROLL => [
            DC::TABLE_PAY_SLP, // Payslip
            DC::TABLE_SSLR, // SetSalary
            DC::TABLE_ALW, // Allowance
            DC::TABLE_ST_DD, // SaturationDeduction
            DC::TABLE_OT_PYMTS, // OtherPayment
            DC::TABLE_OVT, // Overtime
            DC::TABLE_LN, // Loan
        ],
        self::TYPE_EMPLOYEE => [
            DC::TABLE_EMPLOYEES, // Employee
            DC::TABLE_EDOCS, // EmployeeDocument
            DC::TABLE_EATD, // EmployeeAttendance
            DC::TABLE_AWD, // Award
            DC::TABLE_OVT, // Overtime
            DC::TABLE_PRMT // Promotion
        ],
        self::TYPE_CUSTOMER => [
            DC::TABLE_CUSTOMERS, // Customer
            DC::TABLE_CLIENTS, // Client
            DC::TABLE_USERS, // UserType::Customer, UserType::Client
        ],
        self::TYPE_SUPPLIER => [
            DC::TABLE_VENDORS,
        ],
        self::TYPE_PRODUCT => [
            DC::TABLE_PROD_SERVS, // ProductService
            DC::TABLE_PRODUCTS, // Product
            DC::TABLE_PRD_CAT, // ProductCategory
            DC::TABLE_PROD_SERV_CATS, // ProductServiceCategory
        ],
        self::TYPE_SERVICE => [
            DC::TABLE_PROD_SERVS, // ProductService
        ],
        self::TYPE_INVOICE => [
            DC::TABLE_INVS, // Invoice
            DC::TABLE_INV_PRD, // InvoiceProduct
            DC::TABLE_INV_PAY, // InvoicePayment
            DC::TABLE_INV_BANK_TRANSFERS, // InvoiceBankTransfer
        ],
        self::TYPE_PAYMENT => [
            DC::TABLE_PAY, // Payment
            DC::TABLE_INV_PAY, // InvoicePayment
            DC::TABLE_BL_PAY, // BillPayment
            DC::TABLE_POS_PAY, // PosPayment
            DC::TABLE_PRC_PAY, // PurchasePayment
        ],
        self::TYPE_RECEIPT => [
            DC::TABLE_PAY, // Payment
            DC::TABLE_INV_PAY, // InvoicePayment
        ],
        self::TYPE_EXPENSE => [
            DC::TABLE_EXP, // Expense
            DC::TABLE_BILLS, // Bill
            DC::TABLE_INVS, // Invoice
        ],
        self::TYPE_REVENUE => [
            DC::TABLE_RVN, // Revenue
        ],
        self::TYPE_PROFIT_LOSS => [
            DC::TABLE_TRS, // Transaction
            DC::TABLE_RVN, // Revenue
            DC::TABLE_EXP, // Expense
        ],
        self::TYPE_BALANCE_SHEET => [
            DC::TABLE_COAS, // ChartOfAccount
            DC::TABLE_TRS, // Transaction
        ],
        self::TYPE_CASH_FLOW => [
            DC::TABLE_TRS, // Transaction
            DC::TABLE_BANK_ACC, // BankAccount
            DC::TABLE_BNK_TRF, // BankTransfer
        ],
        self::TYPE_TAX => [
            DC::TABLE_TAXES, // Tax
            DC::TABLE_INVS, // Invoice
            DC::TABLE_BILLS, // Bill
        ],
        self::TYPE_VAT => [
            DC::TABLE_TAXES, // Tax
        ],
        self::TYPE_BUDGET => [
            DC::TABLE_BDG, // Budget
        ],
        self::TYPE_FORECAST => [
            DC::TABLE_BDG, // Budget
        ],
        self::TYPE_PROJECT => [
            DC::TABLE_PROJECTS, // Project
            DC::TABLE_PROJ_TSKS, // ProjectTask
            DC::TABLE_PRJ_USR, // ProjectUser
            DC::TABLE_PRJ_INV, // ProjectInvoice
        ],
        self::TYPE_TASK => [
            DC::TABLE_TASKS, // Task
            DC::TABLE_PROJ_TSKS, // ProjectTask
            DC::TABLE_TSK_CMT, // TaskComment
            DC::TABLE_TSK_FL, // TaskFile
        ],
        self::TYPE_TIMESHEET => [
            DC::TABLE_TMS, // Timesheet
            DC::TABLE_TM_TRK, // TimeTrack
        ],
        self::TYPE_ATTENDANCE => [
            DC::TABLE_EATD, // EmployeeAttendance
        ],
        self::TYPE_LEAVE => [
            DC::TABLE_LV, // Leave
            DC::TABLE_LEAVE_TYPES, // LeaveType
        ],
        self::TYPE_PERFORMANCE => [
            DC::TABLE_APR, // Appraisal
            DC::TABLE_IND, // Indicator
            DC::TABLE_GL, // Goal
            DC::TABLE_GL_TRK, // GoalTracking
        ],
        self::TYPE_ASSET => [
            DC::TABLE_AST, // Asset
        ],
        self::TYPE_DEPRECIATION => [
            DC::TABLE_AST, // Asset
        ],
        self::TYPE_BANK_RECONCILIATION => [
            DC::TABLE_BANK_ACC, // BankAccount
            DC::TABLE_BNK_TRF, // BankTransfer
            DC::TABLE_TRS, // Transaction
        ],
        self::TYPE_GENERAL_LEDGER => [
            DC::TABLE_COAS, // ChartOfAccount
            DC::TABLE_JOURNAL_ENTRIES, // JournalEntry
            DC::TABLE_JRN_IT, // JournalEntryItem
        ],
        self::TYPE_TRIAL_BALANCE => [
            DC::TABLE_COAS, // ChartOfAccount
            DC::TABLE_TRS, // Transaction
        ],
        self::TYPE_ACCOUNTS_RECEIVABLE => [
            DC::TABLE_INVS, // Invoice
            DC::TABLE_CUSTOMERS, // Customer
            DC::TABLE_INV_PAY, // InvoicePayment
        ],
        self::TYPE_ACCOUNTS_PAYABLE => [
            DC::TABLE_BILLS, // Bill
            DC::TABLE_VENDORS, // Vendor
            DC::TABLE_BL_PAY, // BillPayment
        ],
        self::TYPE_AGED_RECEIVABLES => [
            DC::TABLE_INVS, // Invoice
            DC::TABLE_CUSTOMERS, // Customer
        ],
        self::TYPE_AGED_PAYABLES => [
            DC::TABLE_BILLS, // Bill
            DC::TABLE_VENDORS, // Vendor
        ],
        self::TYPE_SALES_ORDER => [
            DC::TABLE_ORDERS, // Order
            DC::TABLE_INVS, // Invoice
        ],
        self::TYPE_PURCHASE_ORDER => [
            DC::TABLE_PURCHASES, // Purchase
        ],
        self::TYPE_DELIVERY_NOTE => [
            DC::TABLE_WRH_TRF, // WarehouseTransfer
        ],
        self::TYPE_CREDIT_NOTE => [
            DC::TABLE_CR_NOTES, // CreditNote
        ],
        self::TYPE_DEBIT_NOTE => [
            DC::TABLE_DB_NOTES, // DebitNote
        ],
        self::TYPE_STOCK_MOVEMENT => [
            DC::TABLE_WRH_PRD, // WarehouseProduct
            DC::TABLE_WRH_TRF, // WarehouseTransfer
        ],
        self::TYPE_STOCK_VALUATION => [
            DC::TABLE_PRODUCTS, // Product
            DC::TABLE_WRH_PRD, // WarehouseProduct
        ],
        self::TYPE_STOCK_TRANSFER => [
            DC::TABLE_WRH_TRF, // WarehouseTransfer
        ],
        self::TYPE_MANUFACTURING => [
            DC::TABLE_PRODUCTS, // Product
        ],
        self::TYPE_PRODUCTION => [
            DC::TABLE_PRODUCTS, // Product
        ],
        self::TYPE_QUALITY_CONTROL => [
            DC::TABLE_PRODUCTS, // Product
        ],
        self::TYPE_AUDIT => [
            DC::TABLE_LOG_ACTS, // LogActivity
            DC::TABLE_ACT_LOG, // ActivityLog
        ],
        self::TYPE_COMPLIANCE => [
            DC::TABLE_DOCS, // Document
            DC::TABLE_CPN_POL, // CompanyPolicy
        ],
        self::TYPE_CUSTOM => [],
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            try {
                $m->assignCodeIfMissing();
            } catch (\Throwable $e) {
                Log::warning('StockReport failed on creating: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                throw $e;
            }
        });

        static::saving(function (self $m): void {
            try {
                $m->normalizeCoreFields();
                $m->normalizeJsonFields();

                if ($m->requiresProductReference()) {
                    $m->mirrorProductServiceIdFromProductIfPossible();
                    $m->assertHasValidProductReference();
                }
            } catch (\Throwable $e) {
                Log::warning('StockReport failed on saving: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                throw $e;
            }
        });
    }

    public static function getTablesByType(string $type): array
    {
        $type = strtolower(trim((string) $type));
        if ($type === '') return [];

        if (isset(static::$typeTablesCache[$type]))
            return static::$typeTablesCache[$type];

        return static::$typeTablesCache[$type] = (self::TYPE_MAPPINGS[$type] ?? []);
    }

    public static function getTypeByTable(string $table): ?string
    {
        $table = trim((string) $table);
        if ($table === '') return null;

        if (array_key_exists($table, static::$typeByTableCache))
            return static::$typeByTableCache[$table];

        foreach (self::TYPE_MAPPINGS as $type => $tables)
            if (in_array($table, $tables, true))
                return static::$typeByTableCache[$table] = $type;

        return static::$typeByTableCache[$table] = null;
    }

    public static function isValidTypeTable(string $type, string $table): bool
    {
        $type = strtolower(trim((string) $type));
        $table = trim((string) $table);
        if ($type === '' || $table === '') return false;

        return in_array($table, self::TYPE_MAPPINGS[$type] ?? [], true);
    }

    public static function getAllTypes(): array
    {
        return array_keys(self::TYPE_MAPPINGS);
    }

    public static function analyzeNumericSeries(array $values): array
    {
        $vals = [];
        foreach ($values as $v) {
            $f = static::toFloatOrNull($v);
            $f !== null && $vals[] = $f;
        }

        $n = count($vals);
        if ($n === 0) {
            return [
                'count' => 0,
                'sum' => 0.0,
                'min' => null,
                'max' => null,
                'mean' => null,
                'median' => null,
                'variance_population' => null,
                'stddev_population' => null,
                'variance_sample' => null,
                'stddev_sample' => null,
            ];
        }

        sort($vals);
        $sum = array_sum($vals);
        $min = $vals[0];
        $max = $vals[$n - 1];
        $mean = $sum / $n;

        $median = ($n % 2 === 1)
            ? $vals[(int) floor($n / 2)]
            : (($vals[($n / 2) - 1] + $vals[$n / 2]) / 2);

        $sq = 0.0;
        foreach ($vals as $x) $sq += ($x - $mean) * ($x - $mean);

        $varPop = $sq / $n;
        $stdPop = sqrt($varPop);

        $varSamp = $n > 1 ? ($sq / ($n - 1)) : null;
        $stdSamp = $varSamp !== null ? sqrt($varSamp) : null;

        return [
            'count' => $n,
            'sum' => (float) $sum,
            'min' => (float) $min,
            'max' => (float) $max,
            'mean' => (float) $mean,
            'median' => (float) $median,
            'variance_population' => (float) $varPop,
            'stddev_population' => (float) $stdPop,
            'variance_sample' => $varSamp !== null ? (float) $varSamp : null,
            'stddev_sample' => $stdSamp !== null ? (float) $stdSamp : null,
        ];
    }

    public function productProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, BC::COL_PRD_ID);
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, BC::COL_PRD_SV_ID);
    }

    public function product(): ?BelongsTo
    {
        return Utility::getProduct($this);
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, PJC::COL_COA_ID);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, BC::COL_JRN_ENT_ID);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_SBM_BY);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_APV_BY);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_REJ_BY);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, BC::COL_TP_ID, 'id');
    }

    public function contractJournalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'contract', BC::COL_TP_ID);
    }

    public function journalItems(): HasMany
    {
        $fk = static::firstExistingColumnCached(DC::TABLE_JRN_IT, [BC::COL_JRN_ENT_ID, 'journal', 'entry']);
        return $fk ? $this->hasMany(JournalItem::class, $fk, BC::COL_JRN_ENT_ID) : $this->hasMany(JournalItem::class, BC::COL_JRN_ENT_ID, BC::COL_JRN_ENT_ID);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, BC::COL_TP_ID, 'id');
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, BC::COL_TP_ID, 'id');
    }

    public function employeeAttendance(): BelongsTo
    {
        return $this->belongsTo(EmployeeAttendance::class, BC::COL_TP_ID, 'id');
    }

    public function termination(): BelongsTo
    {
        return $this->belongsTo(Termination::class, BC::COL_TP_ID, 'id');
    }

    public function resignation(): BelongsTo
    {
        return $this->belongsTo(Resignation::class, BC::COL_TP_ID, 'id');
    }

    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class, BC::COL_TP_ID, 'id');
    }

    public function awardType(): BelongsTo
    {
        return $this->belongsTo(AwardType::class, BC::COL_TP_ID, 'id');
    }

    public function allowance(): BelongsTo
    {
        return $this->belongsTo(Allowance::class, BC::COL_TP_ID, 'id');
    }

    public function allowanceOption(): BelongsTo
    {
        return $this->belongsTo(AllowanceOption::class, BC::COL_TP_ID, 'id');
    }

    public function overtime(): BelongsTo
    {
        return $this->belongsTo(Overtime::class, BC::COL_TP_ID, 'id');
    }

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class, BC::COL_TP_ID, 'id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, BC::COL_TP_ID, 'id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, BC::COL_TP_ID, 'id');
    }

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class, BC::COL_TP_ID, 'id');
    }

    public function getIsHrAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_HR;
    }

    public function getIsEmployeeAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_EMPLOYEE) return true;
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_EMPLOYEES;
    }

    public function getIsEmployeeDocumentAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_EDOCS;
    }

    public function getIsEmployeeAttendanceAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_ATTENDANCE) return true;
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_EATD;
    }

    public function getIsTerminationAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_TERMINATIONS;
    }

    public function getIsResignationAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_RSG;
    }

    public function getIsAwardAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_AWD;
    }

    public function getIsAwardTypeAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_AWD_TPS;
    }

    public function getIsAllowanceAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_ALW;
    }

    public function getIsAllowanceOptionAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_ALLOWANCE_OPTS;
    }

    public function getIsOvertimeAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_OVT;
    }

    public function getIsLeaveAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_LEAVE) return true;
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_LV;
    }

    public function getIsLeaveTypeAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_LEAVE_TYPES;
    }

    public function getIsPromotionAttribute(): bool
    {
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_PRMT;
    }

    public function getIsAppraisalAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_PERFORMANCE) return true;
        return $this->getIsHrAttribute() && $this->hrTableHint(false) === DC::TABLE_APR;
    }

    public function getIsPosAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_POS;
    }

    public function getIsPurchaseAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_PURCHASE;
    }

    public function getIsWarehouseAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_WAREHOUSE;
    }

    public function getIsSalesAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_SALES;
    }

    public function getIsFinancialAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_FINANCIAL;
    }

    public function getFinancialTableAttribute(): ?string
    {
        return $this->getIsFinancialAttribute() ? $this->financialTableHint(false) : null;
    }

    public function getIsAccountingAttribute(): bool
    {
        $t = $this->normalizedType();
        return in_array($t, [self::TYPE_ACCOUNTING, self::TYPE_GENERAL_LEDGER, self::TYPE_TRIAL_BALANCE], true);
    }

    public function getAccountingTableAttribute(): ?string
    {
        return $this->getIsAccountingAttribute() ? $this->accountingTableHint(false) : null;
    }

    public function getIsChartAccountAttribute(): bool
    {
        if (!$this->getIsAccountingAttribute()) return false;
        return $this->accountingTableHint(false) === DC::TABLE_COAS;
    }

    public function getIsJournalEntryAttribute(): bool
    {
        if (!$this->getIsAccountingAttribute()) return false;
        return $this->accountingTableHint(false) === DC::TABLE_JOURNAL_ENTRIES;
    }

    public function getIsJournalItemAttribute(): bool
    {
        if (!$this->getIsAccountingAttribute()) return false;
        return $this->accountingTableHint(false) === DC::TABLE_JRN_IT;
    }

    public function getIsContractAttribute(): bool
    {
        return $this->contractTableHint(false) === DC::TABLE_CONTRACTS;
    }

    public function getIsTransactionAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_PROFIT_LOSS) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_TRS;
    }

    public function getIsPaymentAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_PAYMENT) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        $hint = $this->financialTableHint(false);
        return $hint === DC::TABLE_PAY || $hint === DC::TABLE_INV_PAY;
    }

    public function getIsInvoiceAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_INVOICE) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_INVS;
    }

    public function getIsBillAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_ACCOUNTS_PAYABLE) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_BILLS;
    }

    public function getIsExpenseAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_EXPENSE) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_EXP;
    }

    public function getIsRevenueAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_REVENUE) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_RVN;
    }

    public function getIsBudgetAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_BUDGET) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_BDG;
    }

    public function getIsCreditNoteAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_CREDIT_NOTE) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_CR_NOTES;
    }

    public function getIsDebitNoteAttribute(): bool
    {
        if ($this->normalizedType() === self::TYPE_DEBIT_NOTE) return true;
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_DB_NOTES;
    }

    public function getIsLoanAttribute(): bool
    {
        if ($this->normalizedType() !== self::TYPE_FINANCIAL) return false;
        return $this->financialTableHint(false) === DC::TABLE_LN;
    }

    public function scopeHr($q): mixed
    {
        return $q->where('type', self::TYPE_HR);
    }

    public function scopeAccounting($q): mixed
    {
        return $q->where('type', self::TYPE_ACCOUNTING);
    }

    public function scopeOfType($q, string|array|null $type): mixed
    {
        if ($type === null) return $q;

        if (is_array($type)) {
            $types = array_values(array_filter(array_map(fn($v) => strtolower(trim((string) $v)), $type), fn($v) => $v !== ''));
            return $types ? $q->whereIn('type', $types) : $q;
        }

        $t = strtolower(trim((string) $type));
        return $t === '' ? $q : $q->where('type', $t);
    }

    public function scopeOfTypeId($q, string|null $typeId): mixed
    {
        $typeId = trim((string) $typeId);
        return $typeId === '' ? $q : $q->where(BC::COL_TP_ID, $typeId);
    }

    public function reportHrEmployeeSummary(): array
    {
        if (!$this->getIsEmployeeAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id)) return [];

        $row = class_exists(Employee::class) ? Employee::query()->find($id) : null;

        if (!$row && static::stockReportSchemaHasTableCached(DC::TABLE_EMPLOYEES) && static::schemaHasColumnCached(DC::TABLE_EMPLOYEES, UC::COL_EMP_ID)) {
            try {
                $row = class_exists(Employee::class)
                    ? Employee::query()->where(UC::COL_EMP_ID, $id)->first()
                    : DB::table(DC::TABLE_EMPLOYEES)->where(UC::COL_EMP_ID, $id)->first();
            } catch (\Throwable $e) {
                $row = null;
            }
        }

        if (!$row) $row = $this->dbRowById(DC::TABLE_EMPLOYEES, $id);
        if (!$row) return [];

        $branchCol = static::firstExistingColumnCached(DC::TABLE_EMPLOYEES, [CC::COL_BRC_ID, 'branch', 'branch_id']);

        return [
            'employee_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_ref' => (string) (static::rowGet($row, UC::COL_EMP_ID) ?? ''),
            'name' => (string) (static::rowGet($row, 'name') ?? ''),
            'email' => (string) (static::rowGet($row, 'email') ?? ''),
            'phone' => (string) (static::rowGet($row, 'phone') ?? ''),
            'gender' => (string) (static::rowGet($row, 'gender') ?? ''),
            'manager' => (bool) (static::rowGet($row, 'manager') ?? false),
            'branch_id' => $branchCol ? (string) (static::rowGet($row, $branchCol) ?? '') : null,
            'department_id' => (string) (static::rowGet($row, CC::COL_DEP_ID) ?? ''),
            'designation_id' => (string) (static::rowGet($row, UC::COL_DSG_ID) ?? ''),
            'salary' => (float) (static::rowGet($row, 'salary') ?? 0),
        ];
    }

    public function reportHrAttendanceSummary(): array
    {
        if (!$this->getIsEmployeeAttendanceAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_EATD)) return [];

        $row = class_exists(EmployeeAttendance::class) ? EmployeeAttendance::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_EATD, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_EATD, [UC::COL_EMP_ID, 'employee', 'employee_id']);
        $clkIn = static::firstExistingColumnCached(DC::TABLE_EATD, ['clock_in', 'clk_in', 'in']);
        $clkOut = static::firstExistingColumnCached(DC::TABLE_EATD, ['clock_out', 'clk_out', 'out']);

        return [
            'attendance_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'date' => (string) (static::rowGet($row, 'date') ?? ''),
            'status' => (string) (static::rowGet($row, 'status') ?? ''),
            'clock_in' => $clkIn ? (string) (static::rowGet($row, $clkIn) ?? '') : null,
            'clock_out' => $clkOut ? (string) (static::rowGet($row, $clkOut) ?? '') : null,
        ];
    }

    public function reportHrLeaveSummary(): array
    {
        if (!$this->getIsLeaveAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_LV)) return [];

        $row = class_exists(Leave::class) ? Leave::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_LV, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_LV, [UC::COL_EMP_ID, 'employee', 'employee_id']);
        $lvTypeCol = static::firstExistingColumnCached(DC::TABLE_LV, [CC::COL_LV_TP_ID, 'leave_type', 'leave_type_id']);

        return [
            'leave_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'leave_type_id' => $lvTypeCol ? (string) (static::rowGet($row, $lvTypeCol) ?? '') : null,
            'applied_on' => (string) (static::rowGet($row, PJC::COL_APL_ON) ?? ''),
            'start_date' => (string) (static::rowGet($row, PJC::COL_S_DT) ?? ''),
            'end_date' => (string) (static::rowGet($row, PJC::COL_E_DT) ?? ''),
            'total_days' => (string) (static::rowGet($row, PJC::COL_TT_LV_DY) ?? ''),
            'discount' => (int) (static::rowGet($row, 'discount') ?? 0),
            'status' => (string) (static::rowGet($row, 'status') ?? ''),
            'reason' => (string) (static::rowGet($row, PJC::COL_LV_RS) ?? ''),
        ];
    }

    public function reportHrAllowanceSummary(): array
    {
        if (!$this->getIsAllowanceAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_ALW)) return [];

        $row = class_exists(Allowance::class) ? Allowance::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_ALW, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_ALW, [UC::COL_EMP_ID, 'employee', 'employee_id']);

        return [
            'allowance_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'option_id' => (string) (static::rowGet($row, BC::COL_ALW_OPT) ?? ''),
            'title' => (string) (static::rowGet($row, 'title') ?? ''),
            'amount' => (float) (static::rowGet($row, 'amount') ?? 0),
            'type' => (string) (static::rowGet($row, 'type') ?? ''),
        ];
    }

    public function reportHrAllowanceOptionSummary(): array
    {
        if (!$this->getIsAllowanceOptionAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_ALLOWANCE_OPTS)) return [];

        $row = class_exists(AllowanceOption::class) ? AllowanceOption::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_ALLOWANCE_OPTS, $id);
        if (!$row) return [];

        return [
            'allowance_option_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'name' => (string) (static::rowGet($row, 'name') ?? ''),
            'description' => (string) (static::rowGet($row, 'description') ?? ''),
            'expected_budget' => (float) (static::rowGet($row, BC::COL_EXP_BDG) ?? 0),
            'max_budget' => (float) (static::rowGet($row, BC::COL_MAX_BDG) ?? 0),
            'valid_from' => (string) (static::rowGet($row, BC::COL_VLD_FRM) ?? ''),
            'valid_to' => (string) (static::rowGet($row, BC::COL_VLD_TO) ?? ''),
            'renews' => (bool) (static::rowGet($row, 'renews') ?? false),
        ];
    }

    public function reportHrOvertimeSummary(): array
    {
        if (!$this->getIsOvertimeAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_OVT)) return [];

        $row = class_exists(Overtime::class) ? Overtime::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_OVT, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_OVT, [UC::COL_EMP_ID, 'employee', 'employee_id']);

        return [
            'overtime_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'title' => (string) (static::rowGet($row, 'title') ?? ''),
            'days' => (int) (static::rowGet($row, UC::COL_NDAYS) ?? 0),
            'hours' => (int) (static::rowGet($row, 'hours') ?? 0),
            'rate' => (int) (static::rowGet($row, 'rate') ?? 0),
            'type' => (string) (static::rowGet($row, 'type') ?? ''),
        ];
    }

    public function reportHrAwardSummary(): array
    {
        if (!$this->getIsAwardAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_AWD)) return [];

        $row = class_exists(Award::class) ? Award::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_AWD, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_AWD, [UC::COL_EMP_ID, 'employee', 'employee_id']);

        return [
            'award_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'award_type_id' => (string) (static::rowGet($row, UC::COL_AWD_TP) ?? ''),
            'date' => (string) (static::rowGet($row, 'date') ?? ''),
            'gift' => (string) (static::rowGet($row, 'gift') ?? ''),
            'description' => (string) (static::rowGet($row, 'description') ?? ''),
        ];
    }

    public function reportHrPromotionSummary(): array
    {
        if (!$this->getIsPromotionAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_PRMT)) return [];

        $row = class_exists(Promotion::class) ? Promotion::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_PRMT, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_PRMT, [UC::COL_EMP_ID, 'employee', 'employee_id']);

        return [
            'promotion_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'designation_id' => (string) (static::rowGet($row, UC::COL_DSG_ID) ?? ''),
            'date' => (string) (static::rowGet($row, UC::COL_PRMT_DT) ?? ''),
            'title' => (string) (static::rowGet($row, UC::COL_PRMT_TL) ?? ''),
            'description' => (string) (static::rowGet($row, 'description') ?? ''),
        ];
    }

    public function reportHrAppraisalSummary(): array
    {
        if (!$this->getIsAppraisalAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_APR)) return [];

        $row = class_exists(Appraisal::class) ? Appraisal::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_APR, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_APR, [UC::COL_EMP_ID, 'employee', 'employee_id', 'employee']);

        return [
            'appraisal_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'status' => (string) (static::rowGet($row, 'status') ?? ''),
            'date' => (string) (static::rowGet($row, PJC::COL_APR_DT) ?? ''),
            'rating' => (string) (static::rowGet($row, 'rating') ?? ''),
            'remark' => (string) (static::rowGet($row, 'remark') ?? ''),
        ];
    }

    public function reportHrTerminationSummary(): array
    {
        if (!$this->getIsTerminationAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_TERMINATIONS)) return [];

        $row = class_exists(Termination::class) ? Termination::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_TERMINATIONS, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_TERMINATIONS, [UC::COL_EMP_ID, 'employee', 'employee_id']);

        return [
            'termination_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'notice_date' => (string) (static::rowGet($row, UC::COL_TERMINATION_NDT) ?? ''),
            'termination_date' => (string) (static::rowGet($row, UC::COL_TERMINATION_DT) ?? ''),
            'termination_type_id' => (string) (static::rowGet($row, UC::COL_TERMINATION_TP) ?? ''),
            'description' => (string) (static::rowGet($row, 'description') ?? ''),
        ];
    }

    public function reportHrResignationSummary(): array
    {
        if (!$this->getIsResignationAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_RSG)) return [];

        $row = class_exists(Resignation::class) ? Resignation::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_RSG, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_RSG, [UC::COL_EMP_ID, 'employee', 'employee_id']);

        return [
            'resignation_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'notice_date' => (string) (static::rowGet($row, UC::COL_RESIGNATION_NDT) ?? ''),
            'resignation_date' => (string) (static::rowGet($row, UC::COL_RESIGNATION_DT) ?? ''),
            'description' => (string) (static::rowGet($row, 'description') ?? ''),
            'notes' => (string) (static::rowGet($row, 'notes') ?? ''),
        ];
    }

    public function reportHrEmployeeDocumentSummary(): array
    {
        if (!$this->getIsEmployeeDocumentAttribute()) return [];

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached(DC::TABLE_EDOCS)) return [];

        $row = class_exists(EmployeeDocument::class) ? EmployeeDocument::query()->find($id) : null;
        if (!$row) $row = $this->dbRowById(DC::TABLE_EDOCS, $id);
        if (!$row) return [];

        $empCol = static::firstExistingColumnCached(DC::TABLE_EDOCS, [UC::COL_EMP_ID, 'employee', 'employee_id']);

        return [
            'employee_document_id' => (string) (static::rowGet($row, 'id') ?? ''),
            'employee_id' => $empCol ? (string) (static::rowGet($row, $empCol) ?? '') : null,
            'document_template_id' => (string) (static::rowGet($row, TC::COL_DC_ID) ?? ''),
            'document_value' => (string) (static::rowGet($row, TC::COL_DC_V) ?? ''),
        ];
    }

    public function reportHrSummary(): array
    {
        if (!$this->getIsHrAttribute()) return [];

        return match ($this->hrTableHint(true)) {
            DC::TABLE_EMPLOYEES => $this->reportHrEmployeeSummary(),
            DC::TABLE_EDOCS => $this->reportHrEmployeeDocumentSummary(),
            DC::TABLE_EATD => $this->reportHrAttendanceSummary(),
            DC::TABLE_TERMINATIONS => $this->reportHrTerminationSummary(),
            DC::TABLE_RSG => $this->reportHrResignationSummary(),
            DC::TABLE_AWD => $this->reportHrAwardSummary(),
            DC::TABLE_ALW => $this->reportHrAllowanceSummary(),
            DC::TABLE_ALLOWANCE_OPTS => $this->reportHrAllowanceOptionSummary(),
            DC::TABLE_OVT => $this->reportHrOvertimeSummary(),
            DC::TABLE_LV => $this->reportHrLeaveSummary(),
            DC::TABLE_PRMT => $this->reportHrPromotionSummary(),
            DC::TABLE_APR => $this->reportHrAppraisalSummary(),
            default => [],
        };
    }

    public function getTypeTablesAttribute(): array
    {
        $type = $this->normalizedType();

        if ($type === self::TYPE_HR) {
            $hint = $this->hrTableHint(false);
            return $hint ? [$hint] : static::getTablesByType($type);
        }

        if ($type !== self::TYPE_FINANCIAL && $type !== self::TYPE_ACCOUNTING)
            return static::getTablesByType($type);

        if ($type === self::TYPE_ACCOUNTING) {
            $hint = $this->accountingTableHint(false);
            if (!$hint) return static::getTablesByType($type);

            return match ($hint) {
                DC::TABLE_COAS => [DC::TABLE_COAS],
                DC::TABLE_COA_TYPES => [DC::TABLE_COA_TYPES],
                DC::TABLE_COA_SUBTYPES => [DC::TABLE_COA_SUBTYPES],
                DC::TABLE_JOURNAL_ENTRIES => [DC::TABLE_JOURNAL_ENTRIES],
                DC::TABLE_JRN_IT => [DC::TABLE_JRN_IT],
                DC::TABLE_CONTRACTS => [DC::TABLE_CONTRACTS],
                default => [$hint],
            };
        }

        $hint = $this->financialTableHint(false);
        if (!$hint)
            return static::getTablesByType($type);

        return match ($hint) {
            DC::TABLE_INVS => static::getTablesByType(self::TYPE_INVOICE),
            DC::TABLE_BILLS => static::getTablesByType(self::TYPE_ACCOUNTS_PAYABLE),
            DC::TABLE_RVN => static::getTablesByType(self::TYPE_REVENUE),
            DC::TABLE_EXP => static::getTablesByType(self::TYPE_EXPENSE),
            DC::TABLE_BDG => static::getTablesByType(self::TYPE_BUDGET),
            DC::TABLE_CR_NOTES => static::getTablesByType(self::TYPE_CREDIT_NOTE),
            DC::TABLE_DB_NOTES => static::getTablesByType(self::TYPE_DEBIT_NOTE),
            DC::TABLE_PAY => static::getTablesByType(self::TYPE_PAYMENT),
            DC::TABLE_INV_PAY => [DC::TABLE_INV_PAY],
            DC::TABLE_INV_BANK_TRANSFERS => [DC::TABLE_INV_BANK_TRANSFERS],
            DC::TABLE_TRS => [DC::TABLE_TRS],
            DC::TABLE_LN => [DC::TABLE_LN],
            DC::TABLE_CONTRACTS => [DC::TABLE_CONTRACTS],
            default => [$hint],
        };
    }

    public function getEntityTableAttribute(): ?string
    {
        $type = $this->normalizedType();
        $id = $this->normalizedTypeId();

        $cacheKey = $type . '|' . (string) $id;
        if (array_key_exists($cacheKey, static::$entityTableCache))
            return static::$entityTableCache[$cacheKey];

        $table = null;

        if ($type === self::TYPE_FINANCIAL) $table = $this->financialTableHint(true);
        elseif ($type === self::TYPE_ACCOUNTING) $table = $this->accountingTableHint(true);
        else $table = $this->tableHintForType($type, true);

        if ($table === null) {
            $filters = $this->getAttribute('filters');
            $filters = is_array($filters) ? $filters : [];
            $t = trim((string) ($filters['table'] ?? ''));
            $table = ($t !== '' && self::isValidTypeTable($type, $t)) ? $t : null;
        }

        return static::$entityTableCache[$cacheKey] = $table;
    }

    public function getEntityTypeAttribute(): ?string
    {
        $table = $this->getEntityTableAttribute();
        return $table ? static::getTypeByTable($table) : null;
    }

    public function getHrTableAttribute(): ?string
    {
        return $this->getIsHrAttribute() ? $this->tableHintForType(self::TYPE_HR, true) : null;
    }

    public function getIsPayrollAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_PAYROLL;
    }

    public function getPayrollTableAttribute(): ?string
    {
        return $this->getIsPayrollAttribute() ? $this->tableHintForType(self::TYPE_PAYROLL, true) : null;
    }

    public function getIsInventoryAttribute(): bool
    {
        return $this->normalizedType() === self::TYPE_INVENTORY;
    }

    public function getInventoryTableAttribute(): ?string
    {
        return $this->getIsInventoryAttribute() ? $this->tableHintForType(self::TYPE_INVENTORY, true) : null;
    }

    public function getHasValidProductReferenceAttribute(): bool
    {
        try {
            $prdId = trim((string) ($this->getAttribute(BC::COL_PRD_ID) ?? ''));
            $svId = trim((string) ($this->getAttribute(BC::COL_PRD_SV_ID) ?? ''));

            return ($prdId !== '' && Utility::looksLikeUuid($prdId)) || ($svId !== '' && Utility::looksLikeUuid($svId));
        } catch (\Throwable $e) {
            Log::warning('StockReport failed to compute has_valid_product_reference: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    public function getAvailableFormatsAttribute(): array
    {
        $out = [];
        $table = $this->getTable();

        foreach (
            [
                BC::COL_IS_PDF_AVL => 'pdf',
                BC::COL_IS_SST_AVL => 'spreadsheet',
                BC::COL_IS_DOC_AVL => 'document',
                BC::COL_IS_WEB_AVL => 'web',
                BC::COL_IS_PBI_AVL => 'powerbi',
            ] as $col => $label
        ) {
            if (!static::schemaHasColumnCached($table, $col)) continue;
            if ((bool) ($this->getAttribute($col) ?? false)) $out[] = $label;
        }

        return $out;
    }

    /**
     * Collect product-related rows for this report's type ID from inventory tables.
     */
    public function productRows(?string $typeId = null): Collection
    {
        $id = trim((string) ($typeId ?? $this->normalizedTypeId() ?? ''));
        if ($id === '' || !Utility::looksLikeUuid($id)) return collect();

        $out = collect();
        $productFkCandidates = [BC::COL_PRD_ID, BC::COL_PRD_SV_ID, 'product', 'product_service_id'];

        $out = $out->merge($this->collectRowsByFk(DC::TABLE_PROD_SERVS, ['id'], $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_WRH_PRD, $productFkCandidates, $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_POS_PRD, $productFkCandidates, $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_BL_PRD, $productFkCandidates, $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_INV_PRD, $productFkCandidates, $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_PRC_PRD, $productFkCandidates, $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_PPS_PRD, $productFkCandidates, $id));

        return $out->values();
    }

    /**
     * Given a CSV of product-service IDs, return the name of the last valid one.
     */
    public static function products(?string $ids = null): string
    {
        if ($ids === null || trim($ids) === '') return '';

        $idList = array_filter(array_map('trim', explode(',', $ids)));
        if (empty($idList)) return '';

        $last = '';
        foreach ($idList as $id) {
            try {
                $ps = ProductService::find($id);
                if ($ps && !empty($ps->name)) $last = $ps->name;
            } catch (\Throwable) {
                continue;
            }
        }

        return $last;
    }

    public function inventoryQuantitySubtotals(?string $typeId = null): array
    {
        $rows = $this->productRows($typeId);

        $quantityCandidates = ['quantity', 'stock', 'qty', 'units', 'count'];
        $sub = [];
        $values = [];

        foreach ($rows as $item) {
            $table = (string) ($item['table'] ?? '');
            $row = is_array($item['row'] ?? null) ? $item['row'] : [];
            if ($table === '' || !$row) continue;

            $col = static::firstExistingNumericColumnCached($table, $quantityCandidates);
            if (!$col || !array_key_exists($col, $row)) continue;

            $v = static::toFloatOrNull($row[$col]);
            if ($v === null) continue;

            $values[] = $v;

            $sub[$table] ??= ['table' => $table, 'column' => $col, 'rows' => 0, 'sum' => 0.0];
            $sub[$table]['rows']++;
            $sub[$table]['sum'] += $v;
        }

        return [
            'per_table' => array_values($sub),
            'stats' => static::analyzeNumericSeries($values),
        ];
    }

    public function inventoryQuantityStats(?string $typeId = null): array
    {
        return ($this->inventoryQuantitySubtotals($typeId)['stats'] ?? []);
    }

    public function payments(?string $typeId = null): Collection
    {
        $id = trim((string) ($typeId ?? $this->normalizedTypeId() ?? ''));
        if ($id === '' || !Utility::looksLikeUuid($id)) return collect();

        $out = collect();

        // Parent-based pivot payments
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_POS_PAY, static::posFkCandidates(), $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_PRC_PAY, [BC::COL_PRC_ID, 'purchase'], $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_INV_PAY, [BC::COL_INV_ID, 'invoice'], $id));
        $out = $out->merge($this->collectRowsByFk(DC::TABLE_BL_PAY, [BC::COL_BL_ID, 'bill'], $id));

        // Direct Payment row by id (if type_id is a Payment id)
        if (static::stockReportSchemaHasTableCached(DC::TABLE_PAY)) {
            try {
                $out = $out->merge(
                    DB::table(DC::TABLE_PAY)->where('id', $id)->get()->map(fn($r) => ['table' => DC::TABLE_PAY, 'row' => (array) $r])
                );
            } catch (\Throwable $e) {
            }
        }

        // Payment rows referencing the id via common fks (best-effort)
        $paymentFkCandidates = [BC::COL_INV_ID, BC::COL_BL_ID, BC::COL_POS_ID, BC::COL_PRC_ID, 'invoice', 'bill', 'pos', 'purchase', 'ref', 'ref_id'];
        $fk = static::firstExistingColumnCached(DC::TABLE_PAY, $paymentFkCandidates);

        if ($fk && static::stockReportSchemaHasTableCached(DC::TABLE_PAY)) {
            try {
                $out = $out->merge(
                    DB::table(DC::TABLE_PAY)->where($fk, $id)->get()->map(fn($r) => ['table' => DC::TABLE_PAY, 'row' => (array) $r, 'fk' => $fk])
                );
            } catch (\Throwable $e) {
            }
        }

        return $out->values();
    }

    public function paymentsValueSubtotals(?string $typeId = null): array
    {
        $rows = $this->payments($typeId);

        $valueCandidates = ['amount', 'price', 'value', 'total', 'paid', 'net'];
        $sub = [];
        $values = [];

        foreach ($rows as $item) {
            $table = (string) ($item['table'] ?? '');
            $row = is_array($item['row'] ?? null) ? $item['row'] : [];
            if ($table === '' || !$row) continue;

            $col = static::firstExistingNumericColumnCached($table, $valueCandidates);
            if (!$col || !array_key_exists($col, $row)) continue;

            $v = static::toFloatOrNull($row[$col]);
            if ($v === null) continue;

            $values[] = $v;

            $sub[$table] ??= ['table' => $table, 'column' => $col, 'rows' => 0, 'sum' => 0.0];
            $sub[$table]['rows']++;
            $sub[$table]['sum'] += $v;
        }

        return [
            'per_table' => array_values($sub),
            'stats' => static::analyzeNumericSeries($values),
        ];
    }

    public function paymentsValueStats(?string $typeId = null): array
    {
        return ($this->paymentsValueSubtotals($typeId)['stats'] ?? []);
    }

    public function bankTransfers(?string $typeId = null): Collection
    {
        $id = trim((string) ($typeId ?? $this->normalizedTypeId() ?? ''));
        if ($id === '' || !Utility::looksLikeUuid($id)) return collect();

        $out = collect();

        $pivot = $this->collectRowsByFk(DC::TABLE_INV_BANK_TRANSFERS, [BC::COL_INV_ID, 'invoice'], $id);
        $out = $out->merge($pivot);

        $bankTransferIds = [];
        foreach ($pivot as $item) {
            $row = is_array($item['row'] ?? null) ? $item['row'] : [];
            $bt = static::rowFirstUuid($row, static::bankTransferRefCandidates());
            $bt && ($bankTransferIds[$bt] = true);
        }

        $bankTransferIds = array_keys($bankTransferIds);

        $bankTransferIds[] = $id;
        $bankTransferIds = array_values(array_unique(array_filter($bankTransferIds, fn($v) => is_string($v) && Utility::looksLikeUuid($v))));

        if ($bankTransferIds && static::stockReportSchemaHasTableCached(DC::TABLE_BNK_TRF)) {
            try {
                $out = $out->merge(
                    DB::table(DC::TABLE_BNK_TRF)->whereIn('id', $bankTransferIds)->get()->map(fn($r) => ['table' => DC::TABLE_BNK_TRF, 'row' => (array) $r])
                );
            } catch (\Throwable $e) {
            }
        }

        return $out->values();
    }

    public function bankTransfersValueSubtotals(?string $typeId = null): array
    {
        $rows = $this->bankTransfers($typeId);

        $valueCandidates = ['amount', 'price', 'value', 'total', 'paid', 'net'];
        $sub = [];
        $values = [];

        foreach ($rows as $item) {
            $table = (string) ($item['table'] ?? '');
            $row = is_array($item['row'] ?? null) ? $item['row'] : [];
            if ($table === '' || !$row) continue;

            $col = static::firstExistingNumericColumnCached($table, $valueCandidates);
            if (!$col || !array_key_exists($col, $row)) continue;

            $v = static::toFloatOrNull($row[$col]);
            if ($v === null) continue;

            $values[] = $v;

            $sub[$table] ??= ['table' => $table, 'column' => $col, 'rows' => 0, 'sum' => 0.0];
            $sub[$table]['rows']++;
            $sub[$table]['sum'] += $v;
        }

        return [
            'per_table' => array_values($sub),
            'stats' => static::analyzeNumericSeries($values),
        ];
    }

    public function bankTransfersValueStats(?string $typeId = null): array
    {
        return ($this->bankTransfersValueSubtotals($typeId)['stats'] ?? []);
    }

    public function getInventorySummaryAttribute(): array
    {
        return $this->inventoryQuantitySubtotals();
    }

    public function getPaymentsSummaryAttribute(): array
    {
        return $this->paymentsValueSubtotals();
    }

    public function getBankTransfersSummaryAttribute(): array
    {
        return $this->bankTransfersValueSubtotals();
    }

    protected static function schemaColumnTypeCached(string $table, string $column): ?string
    {
        $table = trim((string) $table);
        $column = trim((string) $column);
        if ($table === '' || $column === '') return null;

        if (isset(static::$columnTypeCache[$table][$column]))
            return static::$columnTypeCache[$table][$column];

        try {
            if (!static::stockReportSchemaHasTableCached($table) || !static::schemaHasColumnCached($table, $column))
                return static::$columnTypeCache[$table][$column] = null;

            return static::$columnTypeCache[$table][$column] = Schema::getColumnType($table, $column);
        } catch (\Throwable $e) {
            return static::$columnTypeCache[$table][$column] = null;
        }
    }

    protected static function schemaColumnIsNumericCached(string $table, string $column): bool
    {
        if (isset(static::$numericColumnCache[$table][$column]))
            return static::$numericColumnCache[$table][$column];

        $type = static::schemaColumnTypeCached($table, $column);
        if ($type !== null) {
            $type = strtolower(trim($type));
            $isNumeric = in_array($type, [
                'integer',
                'bigint',
                'smallint',
                'tinyint',
                'decimal',
                'float',
                'double',
                'real',
            ], true);

            return static::$numericColumnCache[$table][$column] = $isNumeric;
        }

        // Fallback: infer from first non-null value (safe, but heuristic)
        try {
            if (!static::stockReportSchemaHasTableCached($table) || !static::schemaHasColumnCached($table, $column))
                return static::$numericColumnCache[$table][$column] = false;

            $v = DB::table($table)->whereNotNull($column)->value($column);
            return static::$numericColumnCache[$table][$column] = is_numeric($v);
        } catch (\Throwable $e) {
            return static::$numericColumnCache[$table][$column] = false;
        }
    }

    protected static function firstExistingNumericColumnCached(string $table, array $candidates): ?string
    {
        $table = trim((string) $table);
        if ($table === '' || !$candidates) return null;

        $key = $table . '|' . md5(json_encode(array_values($candidates)));
        if (array_key_exists($key, static::$firstExistingNumericColumnCache))
            return static::$firstExistingNumericColumnCache[$key];

        foreach ($candidates as $candidate) {
            $col = is_string($candidate) ? trim($candidate) : '';
            if ($col === '') continue;
            if (!static::schemaHasColumnCached($table, $col)) continue;
            if (static::schemaColumnIsNumericCached($table, $col))
                return static::$firstExistingNumericColumnCache[$key] = $col;
        }

        return static::$firstExistingNumericColumnCache[$key] = null;
    }

    protected static function toFloatOrNull(mixed $v): ?float
    {
        if (is_int($v) || is_float($v)) return (float) $v;
        if (is_string($v) && is_numeric(trim($v))) return (float) trim($v);
        return null;
    }

    protected static function rowGet(mixed $row, string $col): mixed
    {
        if ($row instanceof Model) return $row->getAttribute($col);
        return is_object($row) ? ($row->{$col} ?? null) : null;
    }

    protected static function stockReportSchemaHasTableCached(string $table): bool
    {
        $table = trim((string) $table);
        if ($table === '') return false;

        if (isset(static::$stockReportSchemaHasTableCache[$table]))
            return static::$stockReportSchemaHasTableCache[$table];

        try {
            return static::$stockReportSchemaHasTableCache[$table] = Schema::hasTable($table);
        } catch (\Throwable $e) {
            static::$stockReportSchemaHasTableCache[$table] = false;
            return false;
        }
    }

    protected static function schemaHasColumnCached(string $table, string $column): bool
    {
        if (isset(static::$stockReportSchemaHasColumnCache[$table][$column]))
            return static::$stockReportSchemaHasColumnCache[$table][$column];

        try {
            return static::$stockReportSchemaHasColumnCache[$table][$column] = Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            self::logException($e, 'StockReport failed Schema::hasColumn', [
                'table' => $table,
                'column' => $column,
            ]);
            return static::$stockReportSchemaHasColumnCache[$table][$column] = false;
        }
    }

    protected static function firstExistingColumnCached(string $table, array $candidates): ?string
    {
        $table = trim((string) $table);
        if ($table === '' || !$candidates) return null;

        $key = $table . '|' . md5(json_encode(array_values($candidates)));
        if (array_key_exists($key, static::$firstExistingColumnCache))
            return static::$firstExistingColumnCache[$key];

        foreach ($candidates as $candidate) {
            $col = is_string($candidate) ? trim($candidate) : '';
            if ($col === '') continue;
            if (static::schemaHasColumnCached($table, $col))
                return static::$firstExistingColumnCache[$key] = $col;
        }

        return static::$firstExistingColumnCache[$key] = null;
    }

    protected static function logException(\Throwable $e, string $msg, array $ctx = []): void
    {
        Log::warning($msg . ': ' . $e->getMessage(), array_merge($ctx, [
            'exception' => get_class($e),
            'exception_file' => $e->getFile(),
            'exception_line' => $e->getLine(),
        ]));
    }

    protected static function posFkCandidates(): array
    {
        return [BC::COL_POS_ID, 'pos'];
    }

    protected static function productRefCandidates(): array
    {
        return [BC::COL_PRD_SV_ID, BC::COL_PRD_ID, 'product', 'product_service'];
    }

    protected static function bankTransferRefCandidates(): array
    {
        return [BC::COL_BNK_TRF_ID, 'bank_transfer'];
    }

    protected static function rowFirstUuid(array $row, array $keys): ?string
    {
        foreach ($keys as $k) {
            $k = is_string($k) ? trim($k) : '';
            if ($k === '' || !array_key_exists($k, $row)) continue;

            $v = $row[$k];
            $v = is_string($v) ? trim($v) : $v;

            if (is_string($v) && $v !== '' && Utility::looksLikeUuid($v))
                return $v;
        }
        return null;
    }

    protected static function rowFirstNumeric(array $row, array $keys): ?float
    {
        foreach ($keys as $k) {
            $k = is_string($k) ? trim($k) : '';
            if ($k === '' || !array_key_exists($k, $row)) continue;

            $f = static::toFloatOrNull($row[$k]);
            if ($f !== null) return $f;
        }
        return null;
    }

    protected function normalizedType(): string
    {
        return strtolower(trim((string) ($this->getAttribute('type') ?? '')));
    }

    protected function normalizedTypeId(): ?string
    {
        $id = trim((string) ($this->getAttribute(BC::COL_TP_ID) ?? ''));
        return $id !== '' ? $id : null;
    }

    protected function normalizedCoaId(): ?string
    {
        $id = trim((string) ($this->getAttribute(PJC::COL_COA_ID) ?? ''));
        return $id !== '' ? $id : null;
    }

    protected function normalizedJournalEntryId(): ?string
    {
        $id = trim((string) ($this->getAttribute(BC::COL_JRN_ENT_ID) ?? ''));
        return $id !== '' ? $id : null;
    }

    protected function normalizedEmployeeId(bool $allowInference = false): ?string
    {
        $filters = $this->getAttribute('filters');
        $filters = is_array($filters) ? $filters : [];

        foreach (['employee', 'employee_id', 'emp', 'emp_id', UC::COL_EMP_ID] as $k) {
            $v = trim((string) ($filters[$k] ?? ''));
            if ($v !== '') return $v;
        }

        $id = $this->normalizedTypeId();
        if (!$id || !$allowInference) return null;

        $hint = $this->hrTableHint(true);
        return $hint === DC::TABLE_EMPLOYEES ? $id : null;
    }

    protected function normalizedContractId(bool $allowInference = false): ?string
    {
        $filters = $this->getAttribute('filters');
        $filters = is_array($filters) ? $filters : [];

        foreach (['contract', 'contractId', 'contractKey', 'contractCode'] as $k) {
            $v = trim((string) ($filters[$k] ?? ''));
            if ($v !== '') return $v;
        }

        $id = $this->normalizedTypeId();
        if (!$id || !$allowInference) return $id;

        return $this->contractTableHint(true) === DC::TABLE_CONTRACTS ? $id : null;
    }

    protected function hrTableHint(bool $allowInference = false): ?string
    {
        $filters = $this->getAttribute('filters');
        $filters = is_array($filters) ? $filters : [];

        $table = trim((string) ($filters['table'] ?? ''));
        if ($table !== '' && self::isValidTypeTable(self::TYPE_HR, $table))
            return $table;

        $entity = strtolower(trim((string) ($filters['entity'] ?? $filters['entity_type'] ?? $filters['kind'] ?? '')));
        $entityMap = [
            'employee' => DC::TABLE_EMPLOYEES,
            'employee_document' => DC::TABLE_EDOCS,
            'document' => DC::TABLE_EDOCS,
            'attendance' => DC::TABLE_EATD,
            'employee_attendance' => DC::TABLE_EATD,
            'termination' => DC::TABLE_TERMINATIONS,
            'resignation' => DC::TABLE_RSG,
            'award' => DC::TABLE_AWD,
            'award_type' => DC::TABLE_AWD_TPS,
            'allowance' => DC::TABLE_ALW,
            'allowance_option' => DC::TABLE_ALLOWANCE_OPTS,
            'overtime' => DC::TABLE_OVT,
            'leave' => DC::TABLE_LV,
            'leave_type' => DC::TABLE_LEAVE_TYPES,
            'promotion' => DC::TABLE_PRMT,
            'appraisal' => DC::TABLE_APR,
            'contract' => DC::TABLE_CONTRACTS,
        ];
        if ($entity !== '' && isset($entityMap[$entity]))
            return $entityMap[$entity];

        if (!$allowInference) return null;

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id)) return null;

        if (isset(static::$hrTableHintCache[$id]))
            return static::$hrTableHintCache[$id];

        $candidates = static::getTablesByType(self::TYPE_HR);
        $candidates = $candidates ?: [
            DC::TABLE_EMPLOYEES,
            DC::TABLE_EDOCS,
            DC::TABLE_EATD,
            DC::TABLE_TERMINATIONS,
            DC::TABLE_RSG,
            DC::TABLE_AWD,
            DC::TABLE_AWD_TPS,
            DC::TABLE_ALW,
            DC::TABLE_ALLOWANCE_OPTS,
            DC::TABLE_OVT,
            DC::TABLE_LV,
            DC::TABLE_LEAVE_TYPES,
            DC::TABLE_PRMT,
            DC::TABLE_APR,
            DC::TABLE_CONTRACTS,
        ];

        foreach ($candidates as $t) {
            try {
                if (!static::stockReportSchemaHasTableCached($t)) continue;

                if ($t === DC::TABLE_EMPLOYEES && static::schemaHasColumnCached($t, UC::COL_EMP_ID)) {
                    if (DB::table($t)->where('id', $id)->orWhere(UC::COL_EMP_ID, $id)->exists())
                        return static::$hrTableHintCache[$id] = $t;
                } else {
                    if (DB::table($t)->where('id', $id)->exists())
                        return static::$hrTableHintCache[$id] = $t;
                }
            } catch (\Throwable $e) {
                static::$hrTableHintCache[$id] = null;
                return null;
            }
        }

        return static::$hrTableHintCache[$id] = null;
    }

    protected function accountingTableHint(bool $allowInference = false): ?string
    {
        $filters = $this->getAttribute('filters');
        $filters = is_array($filters) ? $filters : [];

        $table = trim((string) ($filters['table'] ?? ''));
        if ($table !== '' && self::isValidTypeTable(self::TYPE_ACCOUNTING, $table))
            return $table;

        $entity = strtolower(trim((string) ($filters['entity'] ?? $filters['entity_type'] ?? $filters['kind'] ?? '')));
        $entityMap = [
            'chart_of_account' => DC::TABLE_COAS,
            'coa' => DC::TABLE_COAS,
            'account' => DC::TABLE_COAS,
            'coa_type' => DC::TABLE_COA_TYPES,
            'coa_subtype' => DC::TABLE_COA_SUBTYPES,
            'journal_entry' => DC::TABLE_JOURNAL_ENTRIES,
            'journal' => DC::TABLE_JOURNAL_ENTRIES,
            'journal_item' => DC::TABLE_JRN_IT,
            'item' => DC::TABLE_JRN_IT,
            'contract' => DC::TABLE_CONTRACTS,
        ];
        if ($entity !== '' && isset($entityMap[$entity]))
            return $entityMap[$entity];

        if (!$allowInference) return null;

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id)) return null;

        if (isset(static::$accountingTableHintCache[$id]))
            return static::$accountingTableHintCache[$id];

        $candidates = [
            DC::TABLE_COAS,
            DC::TABLE_COA_TYPES,
            DC::TABLE_COA_SUBTYPES,
            DC::TABLE_JOURNAL_ENTRIES,
            DC::TABLE_JRN_IT,
            DC::TABLE_CONTRACTS,
        ];

        foreach ($candidates as $t) {
            try {
                if (!static::stockReportSchemaHasTableCached($t)) continue;
                if (DB::table($t)->where('id', $id)->exists())
                    return static::$accountingTableHintCache[$id] = $t;
            } catch (\Throwable $e) {
                static::$accountingTableHintCache[$id] = null;
                return null;
            }
        }

        return static::$accountingTableHintCache[$id] = null;
    }

    protected function contractTableHint(bool $allowInference = false): ?string
    {
        $filters = $this->getAttribute('filters');
        $filters = is_array($filters) ? $filters : [];

        $table = trim((string) ($filters['table'] ?? ''));
        if ($table === DC::TABLE_CONTRACTS) return DC::TABLE_CONTRACTS;

        $entity = strtolower(trim((string) ($filters['entity'] ?? $filters['entity_type'] ?? $filters['kind'] ?? '')));
        if ($entity === 'contract') return DC::TABLE_CONTRACTS;

        if (!$allowInference) return null;

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id)) return null;

        if (isset(static::$contractTableHintCache[$id]))
            return static::$contractTableHintCache[$id];

        try {
            if (!static::stockReportSchemaHasTableCached(DC::TABLE_CONTRACTS))
                return static::$contractTableHintCache[$id] = null;

            return static::$contractTableHintCache[$id] = DB::table(DC::TABLE_CONTRACTS)->where('id', $id)->exists()
                ? DC::TABLE_CONTRACTS
                : null;
        } catch (\Throwable $e) {
            return static::$contractTableHintCache[$id] = null;
        }
    }

    protected function financialTableHint(bool $allowInference = false): ?string
    {
        $filters = $this->getAttribute('filters');
        $filters = is_array($filters) ? $filters : [];

        $table = trim((string) ($filters['table'] ?? ''));
        if ($table !== '' && self::isValidTypeTable(self::TYPE_FINANCIAL, $table))
            return $table;

        $entity = strtolower(trim((string) ($filters['entity'] ?? $filters['entity_type'] ?? $filters['kind'] ?? '')));
        $entityMap = [
            'transaction' => DC::TABLE_TRS,
            'payment' => DC::TABLE_PAY,
            'invoice_payment' => DC::TABLE_INV_PAY,
            'invoice_bank_transfer' => DC::TABLE_INV_BANK_TRANSFERS,
            'revenue' => DC::TABLE_RVN,
            'expense' => DC::TABLE_EXP,
            'proposal' => DC::TABLE_PROPOSALS,
            'deal' => DC::TABLE_DEALS,
            'bill' => DC::TABLE_BILLS,
            'invoice' => DC::TABLE_INVS,
            'budget' => DC::TABLE_BDG,
            'credit_note' => DC::TABLE_CR_NOTES,
            'debit_note' => DC::TABLE_DB_NOTES,
            'contract' => DC::TABLE_CONTRACTS,
            'loan' => DC::TABLE_LN,
        ];
        if ($entity !== '' && isset($entityMap[$entity]))
            return $entityMap[$entity];

        if (!$allowInference) return null;

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id)) return null;

        if (isset(static::$financialTableHintCache[$id]))
            return static::$financialTableHintCache[$id];

        $candidates = [
            DC::TABLE_TRS,
            DC::TABLE_RVN,
            DC::TABLE_EXP,
            DC::TABLE_INVS,
            DC::TABLE_BILLS,
            DC::TABLE_BDG,
            DC::TABLE_CR_NOTES,
            DC::TABLE_DB_NOTES,
            DC::TABLE_LN,
            DC::TABLE_INV_PAY,
            DC::TABLE_INV_BANK_TRANSFERS,
            DC::TABLE_PAY,
            DC::TABLE_PROPOSALS,
            DC::TABLE_CONTRACTS,
            DC::TABLE_DEALS,
        ];

        foreach ($candidates as $t) {
            try {
                if (!static::stockReportSchemaHasTableCached($t)) continue;
                if (DB::table($t)->where('id', $id)->exists())
                    return static::$financialTableHintCache[$id] = $t;
            } catch (\Throwable $e) {
                static::$financialTableHintCache[$id] = null;
                return null;
            }
        }

        return static::$financialTableHintCache[$id] = null;
    }

    protected function dbRowById(string $table, string $id): mixed
    {
        $table = trim((string) $table);
        $id = trim((string) $id);
        if ($table === '' || $id === '' || !Utility::looksLikeUuid($id) || !static::stockReportSchemaHasTableCached($table)) return null;

        try {
            return DB::table($table)->where('id', $id)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function tableHintForType(string $type, bool $allowInference = false): ?string
    {
        $type = strtolower(trim((string) $type));
        if ($type === '') return null;

        $filters = $this->getAttribute('filters');
        $filters = is_array($filters) ? $filters : [];

        $table = trim((string) ($filters['table'] ?? ''));
        if ($table !== '' && self::isValidTypeTable($type, $table))
            return $table;

        if (!$allowInference) return null;

        $id = $this->normalizedTypeId();
        if (!$id || !Utility::looksLikeUuid($id)) return null;

        $cacheKey = $type . '|' . $id;
        if (array_key_exists($cacheKey, static::$genericTableHintCache))
            return static::$genericTableHintCache[$cacheKey];

        $candidates = static::getTablesByType($type);
        foreach ($candidates as $t) {
            try {
                if (!static::stockReportSchemaHasTableCached($t)) continue;
                if (DB::table($t)->where('id', $id)->exists())
                    return static::$genericTableHintCache[$cacheKey] = $t;
            } catch (\Throwable $e) {
                return static::$genericTableHintCache[$cacheKey] = null;
            }
        }

        return static::$genericTableHintCache[$cacheKey] = null;
    }

    protected function requiresProductReference(): bool
    {
        $type = $this->normalizedType();
        return in_array($type, [self::TYPE_POS, self::TYPE_PURCHASE, self::TYPE_WAREHOUSE, self::TYPE_SALES], true);
    }

    protected function assignCodeIfMissing(): void
    {
        $raw = trim((string) ($this->getAttribute('code') ?? ''));
        if ($raw !== '') return;

        $attempts = 0;
        $exists = true;
        $id = trim((string) ($this->getAttribute('id') ?? ''));

        do {
            $attempts++;
            $candidate = 'STK-RPT-' . strtoupper((string) Str::uuid());

            $q = DB::table($this->getTable())->where('code', $candidate);
            $id !== '' && $q->where('id', '!=', $id);
            $exists = $q->exists();
        } while ($exists && $attempts < 25);

        if ($exists)
            throw new \RuntimeException('StockReport failed to generate a unique code after 25 attempts');

        $this->setAttribute('code', $candidate);
    }

    protected function normalizeCoreFields(): void
    {
        $title = trim((string) ($this->getAttribute('title') ?? ''));
        if ($title === '')
            throw new \InvalidArgumentException('StockReport requires a non-empty title');
        $this->setAttribute('title', $title);

        $type = strtolower(trim((string) ($this->getAttribute('type') ?? '')));
        if ($type === '' || !in_array($type, static::getAllTypes(), true))
            throw new \InvalidArgumentException("Invalid StockReport type: {$type}");
        $this->setAttribute('type', $type);

        $typeId = trim((string) ($this->getAttribute(BC::COL_TP_ID) ?? ''));
        if ($typeId === '' || !Utility::looksLikeUuid($typeId))
            throw new \InvalidArgumentException('StockReport requires a valid type_id');
        $this->setAttribute(BC::COL_TP_ID, $typeId);

        $qRaw = $this->getAttribute('quantity');
        $q = is_numeric($qRaw) ? (int) $qRaw : 0;
        $this->setAttribute('quantity', $q < 0 ? 0 : $q);

        $desc = trim((string) ($this->getAttribute('description') ?? ''));
        $this->setAttribute('description', $desc === '' ? null : $desc);

        $table = $this->getTable();

        foreach ([BC::COL_PRD_ID, BC::COL_PRD_SV_ID, PJC::COL_COA_ID, PJC::COL_PLN_SCHD_ID, BC::COL_JRN_ENT_ID, BC::COL_SBM_BY, BC::COL_APV_BY, PJC::COL_REJ_BY] as $k) {
            if (!static::schemaHasColumnCached($table, $k)) continue;
            $v = $this->getAttribute($k);
            $v = is_string($v) ? trim($v) : $v;
            if ($v === '' || $v === null) $this->setAttribute($k, null);
        }

        foreach ([BC::COL_IS_PDF_AVL => false, BC::COL_IS_SST_AVL => false, BC::COL_IS_DOC_AVL => false, BC::COL_IS_WEB_AVL => true, BC::COL_IS_PBI_AVL => false] as $k => $default) {
            if (!static::schemaHasColumnCached($table, $k)) continue;
            $v = $this->getAttribute($k);
            $this->setAttribute($k, $v === null ? $default : (bool) $v);
        }
    }

    protected function normalizeJsonFields(): void
    {
        $this->ensureJsonAttributesAreEncoded(['receipts', 'attachments', 'filters', 'metadata']);

        foreach (['receipts', 'attachments', 'filters', 'metadata'] as $key) {
            $v = $this->getAttribute($key);
            if ($v === '' || $v === []) $this->setAttribute($key, null);
        }
    }

    protected function mirrorProductServiceIdFromProductIfPossible(): void
    {
        $prdId = trim((string) ($this->getAttribute(BC::COL_PRD_ID) ?? ''));
        $prdSet = !empty($prdId) && Utility::looksLikeUuid($prdId) && DB::table(DC::TABLE_PRODUCTS)->where('id', $prdId)->exists();
        if (!$prdSet) return;
        try {
            if (!static::stockReportSchemaHasTableCached(DC::TABLE_PRODUCTS)) return;
            $product = class_exists(Product::class)
                ? Product::query()->find($prdId)
                : DB::table(DC::TABLE_PRODUCTS)->where('id', $prdId)->first();
            if (!$product) {
                Log::debug('StockReport: product_id references non-existent product', [
                    'product_id' => $prdId,
                ]);
                return;
            }
            $candidate = is_object($product)
                ? trim((string) ($product->{BC::COL_PRD_SV_ID} ?? ''))
                : null;
            if ($candidate && Utility::looksLikeUuid($candidate) && DB::table(DC::TABLE_PROD_SERVS)->where('id', $candidate)->exists()) {
                $this->setAttribute(BC::COL_PRD_SV_ID, $candidate);
                Log::debug('StockReport: mirrored product_service_id from product', [
                    'product_id' => $prdId,
                    'product_service_id' => $candidate,
                ]);
            } else $this->setAttribute(BC::COL_PRD_SV_ID, null);
        } catch (\Throwable $e) {
            Log::warning('StockReport failed mirroring product_service_id from product: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'product_id' => $prdId,
            ]);
        }
    }

    protected function assertHasValidProductReference(): void
    {
        $prdId = $this->getAttribute(BC::COL_PRD_ID);
        $svId = $this->getAttribute(BC::COL_PRD_SV_ID);
        if (!(!empty($prdId) && Utility::looksLikeUuid($prdId)) && !(!empty($svId) && Utility::looksLikeUuid($svId)))
            throw new \InvalidArgumentException('StockReport requires a valid product_id or product_service_id');
    }

    protected function collectRowsByFk(string $table, array $fkCandidates, string $id): \Illuminate\Support\Collection
    {
        $table = trim((string) $table);
        $id = trim((string) $id);

        if ($table === '' || $id === '' || !Utility::looksLikeUuid($id)) return collect();
        if (!static::stockReportSchemaHasTableCached($table)) return collect();

        $fk = static::firstExistingColumnCached($table, $fkCandidates);
        if (!$fk) return collect();

        try {
            $rows = DB::table($table)->where($fk, $id)->get();
        } catch (\Throwable $e) {
            return collect();
        }

        return $rows->map(function ($r) use ($table, $fk) {
            return [
                'table' => $table,
                'fk' => $fk,
                'row' => (array) $r,
            ];
        });
    }

    /**
     * Resolve a product_id (or product_service_id) to a human-readable name.
     */
    public static function resolveProductName(int|string $productId): string
    {
        try {
            if (empty($productId)) {
                return '';
            }
            /** @var Product|null $product */
            $product = Product::find($productId);
            if ($product) {
                return (string) ($product->getAttribute('name') ?? $product->getKey());
            }
            /** @var ProductService|null $ps */
            $ps = ProductService::find($productId);
            if ($ps) {
                return (string) ($ps->getAttribute('name') ?? $ps->getKey());
            }
            return (string) $productId;
        } catch (\Throwable $e) {
            Log::debug(static::class . '::resolveProductName error', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return (string) $productId;
        }
    }
}
