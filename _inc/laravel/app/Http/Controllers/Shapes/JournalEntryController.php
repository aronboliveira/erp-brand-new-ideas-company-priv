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
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, View as ViewFacade};
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const INDEX_ROUTE = ViewsConstants::JRN_ET . '.index';

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::JRN_ET . '.index';

        return $this->measureProfile($action, function () use ($request, $class, $action, $sig, $viewPath) {
            Log::info("$sig start", ['user' => Auth::id()]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'manage journal entry', self::INDEX_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }

            try {
                $t = microtime(true);
                $entries = JournalEntry::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($t, $sig, 'fetchEntries');
                Log::info("$sig fetched", ['count' => $entries->count()]);

                $t = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    $this->logExecutionTime($t, $sig, 'viewExistsCheck');
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($t, $sig, 'viewExistsCheck');

                return view($viewPath, compact('entries'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$class::$action");
            }
        });
    }

    public function create(Request $request): View|JsonResponse|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::JRN_ET . '.create';

        return $this->measureProfile($action, function () use ($request, $class, $action, $sig, $viewPath) {
            Log::info("$sig start", ['user' => Auth::id()]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'create journal entry', self::INDEX_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }

            $t = microtime(true);
            $accounts = ChartOfAccount::selectRaw("CONCAT(code,' - ',name) AS code_name, id")
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->pluck('code_name', 'id');
            $this->logExecutionTime($t, $sig, 'pluckChartOfAccounts');

            $t = microtime(true);
            $journalId = $this->journalNumber(); // login already checked
            $this->logExecutionTime($t, $sig, 'generateJournalNumber');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, $sig, 'viewExistsCheck');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, $sig, 'viewExistsCheck');

            return view($viewPath, compact('accounts', 'journalId'));
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($request, $class, $action, $sig) {
            Log::info("$sig start", ['input' => $request->only('date', 'accounts')]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'create journal entry', self::INDEX_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }

            $t = microtime(true);
            $validator = Validator::make($request->all(), [
                'date'     => 'required|date',
                'accounts' => 'required|array|min:1'
            ]);
            if ($validator->fails()) {
                $this->logExecutionTime($t, $sig, 'validateFail');
                Log::warning("$sig validation failed", ['err' => $validator->errors()->first()]);
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $this->logExecutionTime($t, $sig, 'validateSuccess');

            $t = microtime(true);
            $accounts = $request->input('accounts');
            $totals = array_reduce($accounts, function ($tot, $item) {
                $tot['debit']  += $item['debit']  ?? 0;
                $tot['credit'] += $item['credit'] ?? 0;
                return $tot;
            }, ['debit' => 0, 'credit' => 0]);
            $this->logExecutionTime($t, $sig, 'computeTotals');

            if ($totals['debit'] !== $totals['credit']) {
                Log::warning("$sig imbalanced entry", $totals);
                return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($request, $accounts, $user, $sig) {
                    $journal = JournalEntry::create([
                        'journal_id'  => $this->journalNumber(),
                        'date'        => $request->date,
                        'reference'   => $request->reference,
                        'description' => $request->description,
                        DatabaseConstants::TABLE_CREATOR => $user?->creatorId(),
                    ]);
                    Log::info("$sig created journal", ['id' => $journal->id]);

                    foreach ($accounts as $item) {
                        $journalItem = $journal->items()->create([
                            'account'     => $item['account'],
                            'description' => $item['description'] ?? null,
                            'debit'       => $item['debit']  ?? 0,
                            'credit'      => $item['credit'] ?? 0,
                        ]);
                        Log::info("$sig created item", ['item_id' => $journalItem->id]);
                        $this->updateBankBalances($journalItem);
                    }
                });
                $this->logExecutionTime($t, $sig, 'transactionCommit');

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Journal entry successfully created.'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$class::$action");
            }
        });
    }

    public function show(JournalEntry $journalEntry): View|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::JRN_ET . '.view';

        return $this->measureProfile($action, function () use ($journalEntry, $class, $action, $sig, $viewPath) {
            Log::info("$sig start", ['id' => $journalEntry->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($c = self::guard(request(), 'show journal entry', self::INDEX_ROUTE)) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            if ($journalEntry[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("$sig forbidden", ['owner' => $journalEntry[DatabaseConstants::TABLE_CREATOR]]);
                return defaultPermissionDenial(request(), new AuthorizationException(), "$class::$action");
            }

            $t = microtime(true);
            $accounts = $journalEntry->items;
            $settings = Utility::settings();
            $this->logExecutionTime($t, $sig, 'loadRelationsAndSettings');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, $sig, 'viewExistsCheck');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, $sig, 'viewExistsCheck');

            return view($viewPath, compact('journalEntry', 'accounts', 'settings'));
        });
    }

    public function edit(JournalEntry $journalEntry): View|JsonResponse|RedirectResponse
    {
        $action   = __FUNCTION__;
        $class    = static::class;
        $sig      = "$class::$action";
        $viewPath = ViewsConstants::JRN_ET . '.edit';

        return $this->measureProfile($action, function () use ($journalEntry, $class, $action, $sig, $viewPath) {
            Log::info("$sig start", ['id' => $journalEntry->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($c = self::guard(request(), 'edit journal entry', self::INDEX_ROUTE)) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            if ($journalEntry[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("$sig forbidden", ['owner' => $journalEntry[DatabaseConstants::TABLE_CREATOR]]);
                return defaultPermissionDenial(request(), new AuthorizationException(), "$class::$action");
            }

            $t = microtime(true);
            $accounts = ChartOfAccount::selectRaw("CONCAT(code,' - ',name) AS code_name, id")
                ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                ->pluck('code_name', 'id');
            $this->logExecutionTime($t, $sig, 'pluckChartOfAccounts');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, $sig, 'viewExistsCheck');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, $sig, 'viewExistsCheck');

            return view($viewPath, compact('accounts', 'journalEntry'));
        });
    }

    public function update(Request $request, JournalEntry $journalEntry): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($request, $journalEntry, $class, $action, $sig) {
            Log::info("$sig start", ['id' => $journalEntry->id, 'input' => $request->only('date', 'accounts')]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'edit journal entry', self::INDEX_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            if ($journalEntry[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("$sig forbidden", ['owner' => $journalEntry[DatabaseConstants::TABLE_CREATOR]]);
                return defaultPermissionDenial($request, new AuthorizationException(), "$class::$action");
            }

            $t = microtime(true);
            $validator = Validator::make($request->all(), [
                'date'     => 'required|date',
                'accounts' => 'required|array|min:1'
            ]);
            if ($validator->fails()) {
                $this->logExecutionTime($t, $sig, 'validateFail');
                Log::warning("$sig validation failed", ['err' => $validator->errors()->first()]);
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $this->logExecutionTime($t, $sig, 'validateSuccess');

            $t = microtime(true);
            $accounts = $request->input('accounts');
            $totals = array_reduce($accounts, function ($tot, $item) {
                $tot['debit']  += $item['debit']  ?? 0;
                $tot['credit'] += $item['credit'] ?? 0;
                return $tot;
            }, ['debit' => 0, 'credit' => 0]);
            $this->logExecutionTime($t, $sig, 'computeTotals');

            if ($totals['debit'] !== $totals['credit']) {
                Log::warning("$sig imbalanced", $totals);
                return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($request, $journalEntry, $accounts, $sig) {
                    $journalEntry->update([
                        'date'        => $request->date,
                        'reference'   => $request->reference,
                        'description' => $request->description,
                    ]);
                    Log::info("$sig journal updated", ['id' => $journalEntry->id]);

                    $existingIds = collect($accounts)->pluck('id')->filter()->all();
                    JournalItem::where('journal', $journalEntry->id)
                        ->whereNotIn('id', $existingIds)
                        ->delete();

                    foreach ($accounts as $item) {
                        $ji = !empty($item['id'])
                            ? JournalItem::find($item['id'])
                            : new JournalItem(['journal' => $journalEntry->id]);

                        $ji->fill([
                            'account'     => $item['account'],
                            'description' => $item['description'] ?? null,
                            'debit'       => $item['debit']  ?? 0,
                            'credit'      => $item['credit'] ?? 0,
                        ])->save();

                        Log::info("$sig item saved", ['item_id' => $ji->id]);
                        $this->updateBankBalances($ji);
                    }
                });
                $this->logExecutionTime($t, $sig, 'transactionCommit');

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Journal entry successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$class::$action");
            }
        });
    }

    public function destroy(JournalEntry $journalEntry): RedirectResponse
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($journalEntry, $class, $action, $sig) {
            Log::info("$sig start", ['id' => $journalEntry->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($c = self::guard(request(), 'delete journal entry', self::INDEX_ROUTE)) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            if ($journalEntry[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                Log::warning("$sig forbidden", ['owner' => $journalEntry[DatabaseConstants::TABLE_CREATOR]]);
                return defaultPermissionDenial(request(), new AuthorizationException(), "$class::$action");
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($journalEntry, $sig) {
                    JournalItem::where('journal', $journalEntry->id)->delete();
                    $journalEntry->delete();
                    Log::info("$sig deleted", ['id' => $journalEntry->id]);
                });
                $this->logExecutionTime($t, $sig, 'transactionCommit');

                return redirect()->route(self::INDEX_ROUTE)
                    ->with('success', __('Journal entry successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException(request(), $e, "$class::$action");
            }
        });
    }

    public const ACC_DST = 'accountDestroy';
    public function accountDestroy(Request $request): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($request, $class, $action, $sig) {
            Log::info("$sig start", ['input' => $request->all(), 'user_id' => Auth::id()]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'delete journal entry', self::INDEX_ROUTE)) !== true) {
                Log::warning("$sig permission denied", ['user_id' => $user?->id]);
                return $c;
            }

            try {
                $t = microtime(true);
                DB::transaction(fn() => JournalItem::where('id', $request->input('id'))->delete());
                $this->logExecutionTime($t, $sig, 'transactionCommit');

                Log::info("$sig deleted journal item", ['item_id' => $request->input('id')]);
                return redirect()->back()
                    ->with('success', __('Journal entry account successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("$sig failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$class::$action", route(self::INDEX_ROUTE));
            }
        });
    }

    public const JRN_DST = 'journalDestroy';
    public function journalDestroy(Request $request, int|string $itemId): RedirectResponse|null
    {
        $action = __FUNCTION__;
        $class  = static::class;
        $sig    = "$class::$action";

        return $this->measureProfile($action, function () use ($request, $itemId, $class, $action, $sig) {
            Log::info("$sig start", ['item_id' => $itemId, 'user_id' => Auth::id()]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'delete journal entry', self::INDEX_ROUTE)) !== true) {
                Log::warning("$sig permission denied", ['user_id' => $user?->id]);
                return $c;
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($itemId) {
                    $journalItem = JournalItem::findOrFail($itemId);
                    $journalItem->delete();
                });
                $this->logExecutionTime($t, $sig, 'transactionCommit');

                Log::info("$sig deleted journal item", ['item_id' => $itemId]);
                return redirect()->back()
                    ->with('success', __('Journal account successfully deleted.'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::warning("$sig not found", ['item_id' => $itemId]);
                return redirect()->back()
                    ->with('error', __('Journal account not found.'));
            } catch (\Throwable $e) {
                Log::error("$sig failed", ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, "$class::$action", route(self::INDEX_ROUTE));
            }
        });
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
                'bank_id'      => $bank->id,
                'new_balance'  => $new
            ]);
        }
    }

    private function journalNumber(): int|string|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;

        $latest = JournalEntry::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->latest()
            ->first();

        return $latest
            ? (is_numeric($latest->journal_id) ? $latest->journal_id + 1 : $latest->journal_id)
            : 0;
    }
}
