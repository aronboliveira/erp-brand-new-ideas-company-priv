<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    ChartsConstants as CTC,
    DatabaseConstants as DC,
};
use App\Models\{
    ChartOfAccount,
    ChartOfAccountType,
    ChartOfAccountSubType,
    BankAccount,
    BillAccount,
    BillPayment,
    BillProduct,
    InvoicePayment,
    InvoiceProduct,
    JournalItem,
    Payment,
    ProductService,
    Revenue,
    User,
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, DB, Log};

/**
 * AccountingService — extracted from Utility.php
 *
 * Handles Chart of Accounts seeding, Balance Sheet calculations,
 * and Trial Balance aggregation.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class AccountingService
{
    // ─────────────────────────────────────────────────────────
    //  Chart of Accounts Seeding
    // ─────────────────────────────────────────────────────────

    /**
     * Seed Chart of Account types and subtypes for a given company.
     *
     * Uses Model::unguard() to allow mass-assigning the predefined
     * UUID constants from ChartsConstants as primary keys.
     */
    public static function seedAccountTypes(string $companyId): void
    {
        $typeNames = [
            CTC::TP_ASSETS      => CTC::COA_TPS[CTC::AST],
            CTC::TP_LIABILITIES => CTC::COA_TPS[CTC::LBL],
            CTC::TP_EQUITY      => CTC::COA_TPS[CTC::EQT],
            CTC::TP_INCOME      => CTC::COA_TPS[CTC::ICM],
            CTC::TP_COGS        => CTC::COA_TPS[CTC::CGS],
            CTC::TP_EXPENSES    => CTC::COA_TPS[CTC::EXP],
        ];

        $wasUnguarded = Model::isUnguarded();
        Model::unguard();

        try {
            foreach (CTC::COA_SBTPS as $typeId => $subtypes) {
                ChartOfAccountType::updateOrCreate(
                    ['id' => $typeId],
                    [
                        'name'                => $typeNames[$typeId] ?? 'Undefined',
                        DC::COL_TABLE_CREATOR => $companyId,
                    ]
                );

                foreach ($subtypes as $subTypeId => $subName) {
                    ChartOfAccountSubType::updateOrCreate(
                        ['id' => $subTypeId],
                        [
                            'name'                => $subName,
                            'type'                => $typeId,
                            'type_name'           => $typeNames[$typeId] ?? 'Undefined',
                            DC::COL_TABLE_CREATOR => $companyId,
                        ]
                    );
                }
            }
        } finally {
            if (!$wasUnguarded) {
                Model::reguard();
            }
        }
    }

    /**
     * Seed Chart of Accounts using UUID-based type/sub_type references.
     *
     * @param list<array{code: int|string, name: string, type: string, sub_type: string}> $chartData
     */
    public static function seedAccounts(object $user, array $chartData): void
    {
        $wasUnguarded = Model::isUnguarded();
        Model::unguard();

        try {
            foreach ($chartData as $acct) {
                try {
                    ChartOfAccount::create([
                        CTC::COL_CD           => $acct[CTC::COL_CD],
                        CTC::COL_NM           => $acct[CTC::COL_NM],
                        CTC::COL_TP           => $acct[CTC::COL_TP],
                        CTC::COL_SUBTP        => $acct[CTC::COL_SUBTP],
                        CTC::COL_ENB          => 1,
                        DC::COL_TABLE_CREATOR => $user->id ?? $user->creatorId(),
                    ]);
                } catch (\Throwable $e) {
                    Log::error(
                        self::class . '::seedAccounts'
                        . " failed creating COA[{$acct[CTC::COL_CD]}]: {$e->getMessage()}"
                    );
                }
            }
        } finally {
            if (!$wasUnguarded) {
                Model::reguard();
            }
        }
    }

    /**
     * Seed Chart of Accounts by looking up type/sub_type by name.
     *
     * @param list<array{code: int|string, name: string, type: string, sub_type: string}> $chartData
     */
    public static function seedAccountsByName(string|int $userId, array $chartData): void
    {
        $wasUnguarded = Model::isUnguarded();
        Model::unguard();

        try {
            foreach ($chartData as $acct) {
                try {
                    DB::transaction(function () use ($acct, $userId) {
                        $typeName = $acct[CTC::COL_TP];
                        $type = ChartOfAccountType::where(DC::COL_TABLE_CREATOR, $userId)
                            ->where(CTC::COL_NM, $typeName)
                            ->firstOrFail();

                        $sub = ChartOfAccountSubType::where(CTC::COL_TP, $type->id)
                            ->where(CTC::COL_NM, $acct[CTC::COL_SUBTP])
                            ->firstOrFail();

                        ChartOfAccount::create([
                            CTC::COL_CD           => $acct[CTC::COL_CD],
                            CTC::COL_NM           => $acct[CTC::COL_NM],
                            CTC::COL_TP           => $type->id,
                            CTC::COL_SUBTP        => $sub->id,
                            CTC::COL_ENB          => 1,
                            DC::COL_TABLE_CREATOR => $userId,
                        ]);
                    });
                } catch (\Throwable $e) {
                    Log::error(
                        self::class . '::seedAccountsByName'
                        . " failed creating COA[{$acct[CTC::COL_CD]}]: {$e->getMessage()}"
                    );
                }
            }
        } finally {
            if (!$wasUnguarded) {
                Model::reguard();
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Balance Sheet
    // ─────────────────────────────────────────────────────────

    /**
     * Calculate credit sum for a chart-of-account across invoices,
     * invoice payments, and revenue.
     */
    public static function getBalanceSheetCredit(
        string|int $accountId,
        ?string    $startDate = null,
        ?string    $endDate = null
    ): float {
        $start = $startDate ?: date('Y-m-01');
        $end   = $endDate   ?: date('Y-m-t');

        $invoiceProducts = ProductService::where('sale_chart_account_id', $accountId)->pluck('id');

        $invoiceAmount = InvoiceProduct::whereIn('product_id', $invoiceProducts)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum(DB::raw('price * quantity'));

        $accountIds = BankAccount::where('chart_account_id', $accountId)->pluck('id');

        $invoicePaymentAmount = InvoicePayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');

        $revenueAmount = Revenue::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');

        return (float) ($invoiceAmount + $invoicePaymentAmount + $revenueAmount);
    }

    /**
     * Calculate debit sum for a chart-of-account across bill products,
     * bill accounts, bill payments, and payments.
     */
    public static function getBalanceSheetDebit(
        string|int $accountId,
        ?string    $startDate = null,
        ?string    $endDate = null
    ): float {
        $start = $startDate ?: date('Y-m-01');
        $end   = $endDate   ?: date('Y-m-t');

        $billProducts = ProductService::where('expense_chart_account_id', $accountId)->pluck('id');

        $billProductAmount = BillProduct::whereIn('product_id', $billProducts)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum('total');

        $billAmount = BillAccount::where('chart_account_id', $accountId)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum('price');

        $accountIds = BankAccount::where('chart_account_id', $accountId)->pluck('id');

        $billPaymentAmount = BillPayment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');

        $paymentAmount = Payment::whereIn('account_id', $accountIds)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('date', [$start, $end]))
            ->sum('amount');

        return (float) ($billProductAmount + $billAmount + $billPaymentAmount + $paymentAmount);
    }

    // ─────────────────────────────────────────────────────────
    //  Trial Balance
    // ─────────────────────────────────────────────────────────

    /**
     * Aggregate trial balance data for a given account type over a date range.
     *
     * @param string|int $accountType UUID of the account type (e.g. CTC::TP_ASSETS)
     * @return array<int, array<string, mixed>>|RedirectResponse
     */
    public static function trialBalance(
        string|int $accountType,
        string     $start,
        string     $end,
        ?User      $user = null
    ): array|RedirectResponse {
        if ($user === null) {
            if (!Auth::check()) {
                return redirect()->route('login');
            }
            /** @var User $user */
            $user = Auth::user();
        }

        $creatorId = $user->creatorId();

        $journalItem = JournalItem::select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('sum(debit) as totalDebit'), DB::raw('sum(credit) as totalCredit')
        )
            ->join(DC::TABLE_JOURNAL_ENTRIES, DC::TABLE_JOURNAL_ENTRIES . '.id', 'journal_items.journal')
            ->join(DC::TABLE_COAS, 'journal_items.account', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('journal_items.created_at', [$start, $end])
            ->groupBy('account')
            ->get()->toArray();

        // setEagerLoads([]) prevents $with eager-loads from conflicting with custom select() columns
        $invoice = InvoiceProduct::query()->setEagerLoads([])->select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('0 as totalDebit'), DB::raw('sum(price * invoice_products.quantity) as totalCredit')
        )
            ->join(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS . '.id', 'invoice_products.product_id')
            ->join(DC::TABLE_COAS, DC::TABLE_PROD_SERVS . '.sale_chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('invoice_products.created_at', [$start, $end])
            ->groupBy(DC::TABLE_PROD_SERVS . '.sale_chart_account_id')
            ->get()->toArray();

        $invoicePayment = InvoicePayment::query()->setEagerLoads([])->select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('sum(amount) as totalDebit'), DB::raw('0 as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'invoice_payments.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('invoice_payments.created_at', [$start, $end])
            ->groupBy('account_id')
            ->get()->toArray();

        $revenue = Revenue::query()->setEagerLoads([])->select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('0 as totalDebit'), DB::raw('sum(amount) as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'revenues.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('revenues.created_at', [$start, $end])
            ->groupBy('chart_account_id')
            ->get()->toArray();

        $bill = BillProduct::query()->setEagerLoads([])->select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('sum(bill_products.total) as totalDebit'), DB::raw('0 as totalCredit')
        )
            ->join(DC::TABLE_PROD_SERVS, DC::TABLE_PROD_SERVS . '.id', 'bill_products.product_id')
            ->join(DC::TABLE_COAS, DC::TABLE_PROD_SERVS . '.expense_chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('bill_products.created_at', [$start, $end])
            ->groupBy(DC::TABLE_PROD_SERVS . '.expense_chart_account_id')
            ->get()->toArray();

        $billAccount = BillAccount::query()->withoutGlobalScopes()->select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('sum(price) as totalDebit'), DB::raw('0 as totalCredit')
        )
            ->join(DC::TABLE_COAS, 'bill_accounts.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('bill_accounts.created_at', [$start, $end])
            ->groupBy('chart_account_id')
            ->get()->toArray();

        $billPayment = BillPayment::query()->setEagerLoads([])->select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('sum(amount) as totalDebit'), DB::raw('0 as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'bill_payments.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('bill_payments.created_at', [$start, $end])
            ->groupBy('account_id')
            ->get()->toArray();

        $payments = Payment::query()->setEagerLoads([])->select(
            DC::TABLE_COAS . '.id', DC::TABLE_COAS . '.code', DC::TABLE_COAS . '.name',
            DB::raw('sum(amount) as totalDebit'), DB::raw('0 as totalCredit')
        )
            ->join('bank_accounts', DC::TABLE_BANK_ACC . '.id', 'payments.account_id')
            ->join(DC::TABLE_COAS, DC::TABLE_BANK_ACC . '.chart_account_id', DC::TABLE_COAS . '.id')
            ->where(DC::TABLE_COAS . '.type', $accountType)
            ->where(DC::TABLE_COAS . '.' . DC::COL_TABLE_CREATOR, $creatorId)
            ->whereBetween('payments.created_at', [$start, $end])
            ->groupBy('account_id')
            ->get()->toArray();

        // Adjustment: reduce invoicePayment debits by billPayment debits
        if (!empty($billPayment) && !empty($invoicePayment)) {
            for ($i = 0; $i < count($invoicePayment); $i++) {
                $invoicePayment[$i]['totalDebit'] -= $billPayment[$i]['totalDebit'] ?? 0;
            }
        }

        return array_merge($invoice, $journalItem, $revenue, $bill, $billAccount, $payments, $invoicePayment);
    }
}
