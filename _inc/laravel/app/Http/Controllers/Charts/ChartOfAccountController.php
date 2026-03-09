<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ChartsConstants,
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
};
use App\Models\{
    ChartOfAccount,
    ChartOfAccountSubType,
    ChartOfAccountType,
    JournalItem
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Validator, View as ViewFacade};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ChartOfAccountController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    private const ROUTE_SINGULAR = 'chart-of-account'; // ! ALERT
    private const REDIRECT_INDEX = self::ROUTE_SINGULAR . '.index'; // ! ALERT

    public function index(Request $req): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $func, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            $user = $uor;
            if (($c = self::guard($req, PermissionsConstants::MNG_COA, self::REDIRECT_INDEX)) !== true) return $c;

            try {
                [$start, $end] = $req->filled(['start_date', 'end_date'])
                    ? [$req->start_date, $req->end_date]
                    : [date('Y-01-01'), date('Y-m-d', strtotime('+1 day'))];

                $t = microtime(true);
                $types = ChartOfAccountType::whereCreatedBy($user?->creatorId())->get();
                $this->logExecutionTime($t, $action, 'loadTypes');

                $t2 = microtime(true);
                $chartAccounts = $types->mapWithKeys(fn($t) => [
                    $t->name => ChartOfAccount::where([
                        [ChartsConstants::COL_TP, $t->id],
                        [DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId()]
                    ])->with(ChartsConstants::COL_SUBTP)->get(),
                ]);
                $this->logExecutionTime($t2, $action, 'loadAccountsByType');

                $view = self::ROUTE_SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \Exception('view'), $action, route(self::REDIRECT_INDEX));

                return ViewFacade::make($view, [
                    'chartAccounts' => $chartAccounts,
                    'types'         => $types,
                    'filter'        => [
                        'startDateRange' => $start,
                        'endDateRange'   => $end,
                    ],
                ]);
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, [
            UsersConstants::COL_USER_ID => $req->user()?->id ?? null,
            'start_date' => $req->input('start_date'),
            'end_date' => $req->input('end_date'),
        ]);
    }

    public function create(Request $req): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $func, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            $user = $uor;
            if (($c = self::guard($req, PermissionsConstants::CR_COA, self::REDIRECT_INDEX)) !== true) return $c;

            try {
                $t = microtime(true);
                $types = ChartOfAccountType::whereCreatedBy($user?->creatorId())
                    ->pluck(ChartsConstants::COL_NM, 'id')
                    ->prepend('Select Account Type', '');
                $this->logExecutionTime($t, $action, 'loadTypesForCreate');

                $view = self::ROUTE_SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \Exception('view'), $action, route(self::REDIRECT_INDEX));

                return ViewFacade::make($view, compact('types'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, [UsersConstants::COL_USER_ID => $req->user()?->id ?? null]);
    }

    public function store(Request $req): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            $user = $uor;
            if (($c = self::guard($req, PermissionsConstants::CR_COA, self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($req, [
                ChartsConstants::COL_NM    => 'required|string|max:255',
                ChartsConstants::COL_CD    => 'required|integer',
                ChartsConstants::COL_TP    => 'required|uuid|exists:chart_of_account_types,id',
                ChartsConstants::COL_SUBTP => 'required|uuid|exists:chart_of_account_sub_types,id',
            ])) return $c;

            try {
                $t = microtime(true);
                ChartOfAccount::create([
                    ChartsConstants::COL_NM    => $req->name,
                    ChartsConstants::COL_CD    => $req->code,
                    ChartsConstants::COL_TP    => $req->type,
                    ChartsConstants::COL_SUBTP => $req->sub_type,
                    ChartsConstants::COL_DESC  => $req->description,
                    ChartsConstants::COL_ENB   => $req->has(ChartsConstants::COL_ENB) ? 1 : 0,
                    DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId(),
                ]);
                $this->logExecutionTime($t, $action, 'createAccount');

                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Account successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, [
            UsersConstants::COL_USER_ID => $req->user()?->id ?? null,
            'input' => $req->only('name', 'code', 'type', 'sub_type'),
        ]);
    }

    public function show(ChartOfAccount $chartOfAccount, Request $req): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $chartOfAccount, $func, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            $user = $uor;
            if (($c = self::guard($req, PermissionsConstants::LDG_RPT, self::REDIRECT_INDEX)) !== true) return $c;

            try {
                [$start, $end] = $req->filled(['start_date', 'end_date'])
                    ? [$req->start_date, $req->end_date]
                    : [date('Y-m-01'), date('Y-m-t')];

                $colCd = ChartsConstants::COL_CD;
                $colNm = ChartsConstants::COL_NM;

                $t = microtime(true);
                $accounts = ChartOfAccount::selectRaw("CONCAT({$colCd}, ' - ', {$colNm}) AS code_name, id")
                    ->whereCreatedBy($user?->creatorId())
                    ->when($req->filled(['start_date', 'end_date']), fn($q) => $q->whereBetween('created_at', [$start, $end]))
                    ->pluck('code_name', 'id')
                    ->prepend('Select Account', '');
                $this->logExecutionTime($t, $action, 'loadAccountList');

                $account = $req->account ? ChartOfAccount::find($req->account) : $chartOfAccount;

                $t2 = microtime(true);
                $journalItems = JournalItem::select(
                    DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.journal_id',
                    DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.date AS transaction_date',
                    'journal_items.debit',
                    'journal_items.credit'
                )
                    ->join('journal_entries', DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.id', '=', 'journal_items.journal')
                    ->where(DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.' . DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                    ->where('journal_items.account', $account->id)
                    ->whereBetween(DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.date', [$start, $end])
                    ->get();
                $this->logExecutionTime($t2, $action, 'loadJournalItems');

                $debit  = $journalItems->sum('debit');
                $credit = $journalItems->sum('credit');
                $balance = $credit - $debit;

                $view = self::ROUTE_SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \Exception('view'), $action, route(self::REDIRECT_INDEX));

                return ViewFacade::make($view, [
                    'filter'       => [
                        'startDateRange' => $start,
                        'endDateRange'   => $end,
                    ],
                    'account'      => $account,
                    'accounts'     => $accounts,
                    'journalItems' => $journalItems,
                    'debit'        => $debit,
                    'credit'       => $credit,
                    'balance'      => $balance,
                ]);
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, [
            UsersConstants::COL_USER_ID => $req->user()?->id ?? null,
            'account_id' => $chartOfAccount->id ?? null,
        ]);
    }

    public function edit(Request $req, ChartOfAccount $chartOfAccount): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $chartOfAccount, $func, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, 'edit chart of account', self::REDIRECT_INDEX)) !== true) return $c;

            try {
                $t = microtime(true);
                $types = ChartOfAccountType::pluck(ChartsConstants::COL_NM, 'id')->prepend('Select Account Type', '');
                $this->logExecutionTime($t, $action, 'loadTypesForEdit');

                $view = self::ROUTE_SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) return defaultUndefinedException($req, new \Exception('view'), $action, route(self::REDIRECT_INDEX));

                return ViewFacade::make($view, compact('chartOfAccount', 'types'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['account_id' => $chartOfAccount->id ?? null]);
    }

    public function update(Request $req, ChartOfAccount $chartOfAccount): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $chartOfAccount, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, 'edit chart of account', self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($req, [ChartsConstants::COL_NM => 'required'])) return $c;

            try {
                $t = microtime(true);
                $chartOfAccount->update([
                    ChartsConstants::COL_NM   => $req->name,
                    ChartsConstants::COL_CD   => $req->code,
                    ChartsConstants::COL_DESC => $req->description,
                    ChartsConstants::COL_ENB  => $req->has(ChartsConstants::COL_ENB) ? 1 : 0,
                ]);
                $this->logExecutionTime($t, $action, 'updateAccount');

                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Account successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['account_id' => $chartOfAccount->id ?? null, 'input' => $req->only('name', 'code')]);
    }

    public function destroy(Request $req, ChartOfAccount $chartOfAccount): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $chartOfAccount, $action) {
            if (($uor = self::_checkLogin()) instanceof RedirectResponse) return $uor;
            if (($c = self::guard($req, PermissionsConstants::DEL_COA, self::REDIRECT_INDEX)) !== true) return $c;

            try {
                $t = microtime(true);
                $chartOfAccount->delete();
                $this->logExecutionTime($t, $action, 'deleteAccount');

                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Account successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $action, route(self::REDIRECT_INDEX));
            }
        }, ['account_id' => $chartOfAccount->id ?? null]);
    }

    public const GET_SBT = 'getSubType';
    public function getSubType(Request $req): JsonResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($req, $action) {
            try {
                $t = microtime(true);
                $types = ChartOfAccountSubType::where(ChartsConstants::COL_TP, $req->type)
                    ->pluck(ChartsConstants::COL_NM, 'id');
                $this->logExecutionTime($t, $action, 'loadSubTypes');

                return response()->json($types, Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json(['error' => __('An unexpected error occurred.')], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }, ['type' => $req->input('type')]);
    }

    private static function v(Request $req, array $rules): ?RedirectResponse
    {
        $v = Validator::make($req->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }

    private static function safe(
        Request  $req,
        string   $ref,
        \Closure $fn
    ): RedirectResponse|JsonResponse|View {
        try {
            return $fn();
        } catch (Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . $ref,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
