<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{
    BankAccount,
    ChartOfAccount,
    JournalEntry,
    JournalItem,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};

class JournalEntryController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const INDEX_ROUTE = ViewsConstants::JRN_ET . '.index';

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }


    public function index(Request $request): \Illuminate\View\View|RedirectResponse|JsonResponse
    {
        Log::info(__METHOD__ . ' start', ['user' => Auth::id()]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($c = self::guard($request, 'manage journal entry', self::INDEX_ROUTE)) {
            Log::warning('index denied', ['user' => Auth::id()]);
            return $c;
        }
        try {
            $entries = JournalEntry::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            Log::info('index fetched', ['count' => $entries->count()]);
            return view(ViewsConstants::JRN_ET . '.' . __FUNCTION__, compact('entries'));
        } catch (\Throwable $e) {
            Log::error('index error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $request): \Illuminate\View\View|JsonResponse
    {
        Log::info(__METHOD__ . ' start', ['user' => Auth::id()]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($c = self::guard($request, 'create journal entry', self::INDEX_ROUTE)) {
            Log::warning('create denied', ['user' => Auth::id()]);
            return $c;
        }
        $accounts = ChartOfAccount::selectRaw("CONCAT(code,' - ',name) AS code_name, id")
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck('code_name', 'id');
        $journalId = $this->journalNumber();
        return view(ViewsConstants::JRN_ET . '.' . __FUNCTION__, compact('accounts', 'journalId'));
    }

    public function store(Request $request): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', ['input' => $request->only('date', 'accounts')]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($c = self::guard($request, 'create journal entry', self::INDEX_ROUTE)) {
            Log::warning('store denied', ['user' => Auth::id()]);
            return $c;
        }

        $validator = Validator::make($request->all(), [
            'date'     => 'required|date',
            'accounts' => 'required|array|min:1'
        ]);
        if ($validator->fails()) {
            Log::warning('store validation failed', ['err' => $validator->errors()->first()]);
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $accounts = $request->input('accounts');
        $totals = array_reduce($accounts, function ($tot, $item) {
            $tot['debit']  += $item['debit']  ?? 0;
            $tot['credit'] += $item['credit'] ?? 0;
            return $tot;
        }, ['debit' => 0, 'credit' => 0]);

        if ($totals['debit'] !== $totals['credit']) {
            Log::warning('imbalanced entry', $totals);
            return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
        }

        try {
            DB::transaction(function () use ($request, $accounts, $user) {
                $journal = JournalEntry::create([
                    'journal_id'  => $this->journalNumber(),
                    'date'        => $request->date,
                    'reference'   => $request->reference,
                    'description' => $request->description,
                    DatabaseConstants::TABLE_CREATOR  => $user?->creatorId(),
                ]);
                Log::info('store created journal', ['id' => $journal->id]);

                foreach ($accounts as $item) {
                    $journalItem = $journal->items()->create([
                        'account'     => $item['account'],
                        'description' => $item['description'],
                        'debit'       => $item['debit']  ?? 0,
                        'credit'      => $item['credit'] ?? 0,
                    ]);
                    Log::info('store created item', ['item_id' => $journalItem->id]);
                    $this->updateBankBalances($journalItem);
                }
            });
            Log::info('store transaction committed');
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Journal entry successfully created.'));
        } catch (\Throwable $e) {
            Log::error('store error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(JournalEntry $journalEntry): \Illuminate\View\View|RedirectResponse
    {
        Log::info(__METHOD__ . ' start', ['id' => $journalEntry->id]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($c = self::guard(request(), 'show journal entry', self::INDEX_ROUTE)) {
            Log::warning('show denied', ['user' => Auth::id()]);
            return $c;
        }
        if ($journalEntry->created_by !== $user?->creatorId()) {
            Log::warning('show forbidden', ['owner' => $journalEntry->created_by]);
            return defaultPermissionDenial(request(), new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
        }
        $accounts = $journalEntry->items;
        $settings = Utility::settings();
        return view(ViewsConstants::JRN_ET . '.view', compact('journalEntry', 'accounts', 'settings'));
    }

    public function edit(JournalEntry $journalEntry): \Illuminate\View\View|JsonResponse
    {
        Log::info(__METHOD__ . ' start', ['id' => $journalEntry->id]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($c = self::guard(request(), 'edit journal entry', self::INDEX_ROUTE)) {
            Log::warning('edit denied', ['user' => Auth::id()]);
            return $c;
        }
        if ($journalEntry->created_by !== $user?->creatorId()) {
            Log::warning('edit forbidden', ['owner' => $journalEntry->created_by]);
            return defaultPermissionDenial(request(), new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
        }
        $accounts = ChartOfAccount::selectRaw("CONCAT(code,' - ',name) AS code_name, id")
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->pluck('code_name', 'id');
        return view(ViewsConstants::JRN_ET . '.edit', compact('accounts', 'journalEntry'));
    }

    public function update(Request $request, JournalEntry $journalEntry): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', ['id' => $journalEntry->id, 'input' => $request->only('date', 'accounts')]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($c = self::guard($request, 'edit journal entry', self::INDEX_ROUTE)) {
            Log::warning('update denied', ['user' => Auth::id()]);
            return $c;
        }
        if ($journalEntry->created_by !== $user?->creatorId()) {
            Log::warning('update forbidden', ['owner' => $journalEntry->created_by]);
            return defaultPermissionDenial($request, new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
        }

        $validator = Validator::make($request->all(), [
            'date'     => 'required|date',
            'accounts' => 'required|array|min:1'
        ]);
        if ($validator->fails()) {
            Log::warning('update validation failed', ['err' => $validator->errors()->first()]);
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $accounts = $request->input('accounts');
        $totals = array_reduce($accounts, function ($tot, $item) {
            $tot['debit']  += $item['debit']  ?? 0;
            $tot['credit'] += $item['credit'] ?? 0;
            return $tot;
        }, ['debit' => 0, 'credit' => 0]);
        if ($totals['debit'] !== $totals['credit']) {
            Log::warning('update imbalanced', $totals);
            return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
        }

        try {
            DB::transaction(function () use ($request, $journalEntry, $accounts) {
                $journalEntry->update([
                    'date'        => $request->date,
                    'reference'   => $request->reference,
                    'description' => $request->description,
                ]);
                Log::info('update journal updated', ['id' => $journalEntry->id]);

                // delete removed items, update or create others
                $existingIds = collect($accounts)->pluck('id')->filter()->all();
                JournalItem::where('journal', $journalEntry->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                foreach ($accounts as $item) {
                    $ji = $item['id']
                        ? JournalItem::find($item['id'])
                        : new JournalItem(['journal' => $journalEntry->id]);
                    $ji->fill([
                        'account'     => $item['account'],
                        'description' => $item['description'],
                        'debit'       => $item['debit']  ?? 0,
                        'credit'      => $item['credit'] ?? 0,
                    ])->save();
                    Log::info('update item saved', ['item_id' => $ji->id]);
                    $this->updateBankBalances($ji);
                }
            });
            Log::info('update transaction committed');
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Journal entry successfully updated.'));
        } catch (\Throwable $e) {
            Log::error('update error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(JournalEntry $journalEntry): RedirectResponse
    {
        Log::info(__METHOD__ . ' start', ['id' => $journalEntry->id]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if ($c = self::guard(request(), 'delete journal entry', self::INDEX_ROUTE)) {
            Log::warning('destroy denied', ['user' => Auth::id()]);
            return $c;
        }
        if ($journalEntry->created_by !== $user?->creatorId()) {
            Log::warning('destroy forbidden', ['owner' => $journalEntry->created_by]);
            return defaultPermissionDenial(request(), new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
        }
        try {
            DB::transaction(function () use ($journalEntry) {
                JournalItem::where('journal', $journalEntry->id)->delete();
                $journalEntry->delete();
                Log::info('destroy committed', ['id' => $journalEntry->id]);
            });
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Journal entry successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error('destroy error', ['err' => $e->getMessage()]);
            return defaultUndefinedException(request(), $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function accountDestroy(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        Log::info("$action start", ['input' => $request->all(), 'user_id' => Auth::id()]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($c = self::guard($request, 'delete journal entry', self::INDEX_ROUTE)) {
            Log::warning("$action permission denied", ['user_id' => $user?->id]);
            return $c;
        }

        try {
            DB::transaction(fn () => JournalItem::where('id', $request->input('id'))->delete());
            Log::info("$action deleted journal item", ['item_id' => $request->input('id')]);
            return redirect()->back()
                ->with('success', __('Journal entry account successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::INDEX_ROUTE)
            );
        }
    }

    public function journalDestroy(Request $request, int $itemId): RedirectResponse|null
    {
        $action = __METHOD__;
        Log::info("$action start", ['item_id' => $itemId, 'user_id' => Auth::id()]);
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        if ($c = self::guard($request, 'delete journal entry', self::INDEX_ROUTE)) {
            Log::warning("$action permission denied", ['user_id' => $user?->id]);
            return $c;
        }

        try {
            DB::transaction(function () use ($itemId) {
                $journalItem = JournalItem::findOrFail($itemId);
                $journalItem->delete();
            });
            Log::info("$action deleted journal item", ['item_id' => $itemId]);
            return redirect()->back()
                ->with('success', __('Journal account successfully deleted.'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("$action not found", ['item_id' => $itemId]);
            return redirect()->back()
                ->with('error', __('Journal account not found.'));
        } catch (\Throwable $e) {
            Log::error("$action failed", ['error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                $action,
                route(self::INDEX_ROUTE)
            );
        }
    }

    private function updateBankBalances(JournalItem $item): void
    {
        $banks = BankAccount::where('chart_account_id', $item->account)->get();
        foreach ($banks as $bank) {
            $old = $bank->opening_balance;
            $new = $old
                - ($item->debit  ?? 0)
                + ($item->credit ?? 0);
            $bank->opening_balance = $new;
            $bank->save();
            Log::info('bank balance updated', [
                'bank_id' => $bank->id, 'new_balance' => $new
            ]);
        }
    }

    private function journalNumber(): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $latest = JournalEntry::where(
            DatabaseConstants::TABLE_CREATOR,
            $user?->creatorId()
        )->latest()->first();
        return $latest
            ? $latest->journal_id + 1
            : 1;
    }
}
