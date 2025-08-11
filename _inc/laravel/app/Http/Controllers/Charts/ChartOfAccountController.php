<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    ChartsConstants,
    DatabaseConstants,
    PermissionsConstants
};
use App\Models\{
    ChartOfAccount,
    ChartOfAccountSubType,
    ChartOfAccountType,
    JournalItem
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

final class ChartOfAccountController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_SINGULAR = 'chart-of-account';
    private const REDIRECT_INDEX = self::ROUTE_SINGULAR . '.index';

    public function index(Request $req)
    {
        if (($uor = self::_checkLogin()) instanceof RedirectResponse)
            return $uor;
        $user = $uor;
        if ($c = self::guard($req, PermissionsConstants::MNG_COA, self::REDIRECT_INDEX))
            return $c;
        return self::safe($req, __FUNCTION__, function () use ($req, $user) {
            [$start, $end] = $req->filled(['start_date', 'end_date'])
                ? [$req->start_date, $req->end_date]
                : [date('Y-01-01'), date('Y-m-d', strtotime('+1 day'))];
            $types = ChartOfAccountType::whereCreatedBy($user?->creatorId())->get();
            $chartAccounts = $types->mapWithKeys(fn ($t) => [
                $t->name => ChartOfAccount::where([
                    [ChartsConstants::COL_TP,        $t->id],
                    [DatabaseConstants::TABLE_CREATOR, $user?->creatorId()]
                ])
                    ->with(ChartsConstants::COL_SUBTP)
                    ->get(),
            ]);
            return view(
                self::ROUTE_SINGULAR . '.' . __FUNCTION__,
                [
                    'chartAccounts' => $chartAccounts,
                    'types'         => $types,
                    'filter'        => [
                        'startDateRange' => $start,
                        'endDateRange'   => $end,
                    ],
                ]
            );
        });
    }

    public function create(Request $req)
    {
        if (($uor = self::_checkLogin()) instanceof RedirectResponse)
            return $uor;
        $user = $uor;
        if ($c = self::guard($req, PermissionsConstants::CR_COA, self::REDIRECT_INDEX))
            return $c;
        return self::safe($req, __FUNCTION__, function () use ($user) {
            $types = ChartOfAccountType::whereCreatedBy($user?->creatorId())
                ->pluck(ChartsConstants::COL_NM, 'id')
                ->prepend('Select Account Type', '');
            return view(
                self::ROUTE_SINGULAR . '.' . __FUNCTION__,
                compact('types')
            );
        });
    }

    public function store(Request $req)
    {
        if (($uor = self::_checkLogin()) instanceof RedirectResponse)
            return $uor;
        $user = $uor;
        if ($c = self::guard($req, PermissionsConstants::CR_COA, self::REDIRECT_INDEX))
            return $c;
        // validate name & type
        if ($c = self::v($req, [
            ChartsConstants::COL_NM    => 'required|string|max:255',
            ChartsConstants::COL_CD    => 'required|integer',
            ChartsConstants::COL_TP    => 'required|uuid|exists:chart_of_account_types,id',
            ChartsConstants::COL_SUBTP => 'required|uuid|exists:chart_of_account_sub_types,id',
        ]))
            return $c;
        return self::safe($req, __FUNCTION__, function () use ($req, $user) {
            ChartOfAccount::create([
                ChartsConstants::COL_NM    => $req->name,
                ChartsConstants::COL_CD    => $req->code,
                ChartsConstants::COL_TP    => $req->type,
                ChartsConstants::COL_SUBTP => $req->sub_type,
                ChartsConstants::COL_DESC  => $req->description,
                ChartsConstants::COL_ENB   => $req->has(ChartsConstants::COL_ENB) ? 1 : 0,
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
            ]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Account successfully created.'));
        });
    }

    public function show(ChartOfAccount $chartOfAccount, Request $req)
    {
        if (($uor = self::_checkLogin()) instanceof RedirectResponse)
            return $uor;
        $user = $uor;
        if ($c = self::guard($req, PermissionsConstants::LDG_RPT, self::REDIRECT_INDEX))
            return $c;
        return self::safe($req, __FUNCTION__, function () use ($req, $user, $chartOfAccount) {
            [$start, $end] = $req->filled(['start_date', 'end_date'])
                ? [$req->start_date, $req->end_date]
                : [date('Y-m-01'), date('Y-m-t')];
            $colCd = ChartsConstants::COL_CD;
            $colNm = ChartsConstants::COL_NM;
            $accounts = ChartOfAccount::selectRaw(
                "CONCAT({$colCd}, ' - ', {$colNm}) AS code_name, id"
            )
                ->whereCreatedBy($user?->creatorId())
                ->when(
                    $req->filled(['start_date', 'end_date']),
                    fn ($q) => $q->whereBetween('created_at', [$start, $end])
                )
                ->pluck('code_name', 'id')
                ->prepend('Select Account', '');
            $account = $req->account
                ? ChartOfAccount::find($req->account)
                : $chartOfAccount;
            $journalItems = JournalItem::select(
                DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.journal_id',
                DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.date AS transaction_date',
                'journal_items.debit',
                'journal_items.credit'
            )
                ->join(
                    'journal_entries',
                    DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.id',
                    '=',
                    'journal_items.journal'
                )
                ->where(
                    DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.' . DatabaseConstants::TABLE_CREATOR,
                    $user?->creatorId()
                )
                ->where('journal_items.account', $account->id)
                ->whereBetween(
                    DatabaseConstants::TABLE_JOURNAL_ENTRIES . '.date',
                    [$start, $end]
                )
                ->get();
            $debit  = $journalItems->sum('debit');
            $credit = $journalItems->sum('credit');
            $balance = $credit - $debit;
            return view(
                self::ROUTE_SINGULAR . '.' . __FUNCTION__,
                [
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
                ]
            );
        });
    }

    public function edit(Request $req, ChartOfAccount $chartOfAccount)
    {
        if ($c = self::guard($req, 'edit chart of account', self::REDIRECT_INDEX))
            return $c;
        return self::safe($req, __FUNCTION__, function () use ($chartOfAccount) {
            $types = ChartOfAccountType::pluck(ChartsConstants::COL_NM, 'id')
                ->prepend('Select Account Type', '');
            return view(
                self::ROUTE_SINGULAR . '.' . __FUNCTION__,
                compact('chartOfAccount', 'types')
            );
        });
    }

    public function update(Request $req, ChartOfAccount $chartOfAccount)
    {
        if ($c = self::guard($req, 'edit chart of account', self::REDIRECT_INDEX))
            return $c;
        if ($c = self::v($req, [ChartsConstants::COL_NM => 'required']))
            return $c;
        return self::safe($req, __FUNCTION__, function () use ($req, $chartOfAccount) {
            $chartOfAccount->update([
                ChartsConstants::COL_NM   => $req->name,
                ChartsConstants::COL_CD   => $req->code,
                ChartsConstants::COL_DESC => $req->description,
                ChartsConstants::COL_ENB  => $req->has(ChartsConstants::COL_ENB) ? 1 : 0,
            ]);
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Account successfully updated.'));
        });
    }

    public function destroy(Request $req, ChartOfAccount $chartOfAccount)
    {
        if ($c = self::guard($req, PermissionsConstants::DEL_COA, self::REDIRECT_INDEX))
            return $c;
        return self::safe($req, __FUNCTION__, function () use ($chartOfAccount) {
            $chartOfAccount->delete();
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Account successfully deleted.'));
        });
    }

    public function getSubType(Request $req): JsonResponse
    {
        return self::safe($req, __FUNCTION__, function () use ($req) {
            $types = ChartOfAccountSubType::where(
                ChartsConstants::COL_TP,
                $req->type
            )
                ->pluck(ChartsConstants::COL_NM, 'id');
            return response()->json($types);
        });
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
