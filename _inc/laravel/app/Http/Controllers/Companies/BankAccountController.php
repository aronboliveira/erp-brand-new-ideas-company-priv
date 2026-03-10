<?php

namespace App\Http\Controllers;

use App\Config\Constants\{BanksConstants, DatabaseConstants, UsersConstants};
use App\Models\{
    BankAccount,
    BillPayment,
    ChartOfAccount,
    CustomField,
    InvoicePayment,
    Payment,
    Revenue,
    Transaction
};
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, View as ViewFacade};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class BankAccountController extends Controller
{
    use ChecksLogin;

    private const SINGULAR = 'account';
    private const ROUTE_SINGULAR = 'bank-' . self::SINGULAR;

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $func, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            try {
                $this->_authorize($request, 'view bank account');

                $t = microtime(true);
                $accounts = BankAccount::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($t, $action, 'loadAccounts');

                $view = self::ROUTE_SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \Exception('view'), $action, route(self::ROUTE_SINGULAR . '.index'));
                }

                return ViewFacade::make($view, ['accounts' => $accounts]);
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' error', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $func, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            try {
                $this->_authorize($request, 'create bank account');
                $creatorId = $request->user()->creatorId();

                $t = microtime(true);
                $chartAccounts = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck('code_name', 'id')
                    ->prepend(__('Select Account'), '');
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->where('module', self::SINGULAR)
                    ->get();
                $this->logExecutionTime($t, $action, 'loadCreateFormData');

                $view = self::ROUTE_SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \Exception('view'), $action, route(self::ROUTE_SINGULAR . '.index'));
                }

                return ViewFacade::make($view, compact('chartAccounts', 'customFields'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' error', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, [UsersConstants::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            try {
                $this->_authorize($request, 'create bank account');
                $creatorId = $request->user()->creatorId();

                $v = Validator::make($request->all(), [
                    BanksConstants::COL_HNM   => 'required|string',
                    BanksConstants::COL_NM    => 'required|string',
                    // unique:table,column,NULL,id,created_by,{$creatorId}
                    BanksConstants::COL_ACC_N => 'required|string|unique:' .
                        DatabaseConstants::TABLE_BANK_ACC . ',' . BanksConstants::COL_ACC_N .
                        ',NULL,id,' . DatabaseConstants::COL_TABLE_CREATOR . ',' . $creatorId,
                    BanksConstants::COL_OB    => 'required|numeric',
                    BanksConstants::COL_CT    => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ]);
                if ($v->fails()) {
                    return redirect()->route(self::ROUTE_SINGULAR . '.index')
                        ->with('error', $v->errors()->first());
                }

                DB::beginTransaction();
                $t = microtime(true);
                $data = $request->only([
                    BanksConstants::COL_COA,
                    BanksConstants::COL_HNM,
                    BanksConstants::COL_NM,
                    BanksConstants::COL_ACC_N,
                    BanksConstants::COL_OB,
                    BanksConstants::COL_CT,
                    BanksConstants::COL_ADR
                ]) + [DatabaseConstants::COL_TABLE_CREATOR => $creatorId];

                $account = BankAccount::create($data);
                CustomField::saveData($account, $request->customField);

                $this->logExecutionTime($t, $action, 'createAccount');
                DB::commit();

                return redirect()->route(self::ROUTE_SINGULAR . '.index')
                    ->with('success', __('Account successfully created.'));
            } catch (AuthorizationException $e) {
                DB::rollBack();
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($action . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['input' => $request->only(BanksConstants::COL_NM, BanksConstants::COL_ACC_N)]);
    }

    public function show(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $bankAccount, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            try {
                $this->_authorize($request, 'view bank account');
                if ($bankAccount->created_by !== $request->user()->creatorId()) {
                    throw new AuthorizationException;
                }
                return redirect()->route(self::ROUTE_SINGULAR . '.index');
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['bank_account_id' => $bankAccount->id ?? null]);
    }

    public function edit(Request $request, BankAccount $bankAccount): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $bankAccount, $func, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            try {
                $this->_authorize($request, 'edit bank account');
                if ($bankAccount->created_by !== $request->user()->creatorId()) {
                    throw new AuthorizationException;
                }

                $creatorId = $request->user()->creatorId();

                $t = microtime(true);
                $chartAccounts = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->pluck('code_name', 'id')
                    ->prepend(__('Select Account'), '');
                $bankAccount->customField = CustomField::getData($bankAccount, self::SINGULAR);
                $customFields = CustomField::where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
                    ->where('module', self::SINGULAR)
                    ->get();
                $this->logExecutionTime($t, $action, 'loadEditFormData');

                $view = self::ROUTE_SINGULAR . '.' . $func;
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \Exception('view'), $action, route(self::ROUTE_SINGULAR . '.index'));
                }

                return ViewFacade::make($view, compact('bankAccount', 'chartAccounts', 'customFields'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                Log::error($action . ' error', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['bank_account_id' => $bankAccount->id ?? null]);
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $bankAccount, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            try {
                $this->_authorize($request, 'edit bank account');
                $creatorId = $request->user()->creatorId();

                $lock = BankAccount::where('id', $bankAccount->id)->lockForUpdate()->firstOrFail();

                $v = Validator::make($request->all(), [
                    BanksConstants::COL_HNM   => 'required|string',
                    BanksConstants::COL_NM    => 'required|string',
                    // unique:table,column,{ignore_id},id,created_by,{$creatorId}
                    BanksConstants::COL_ACC_N => 'required|string|unique:' .
                        DatabaseConstants::TABLE_BANK_ACC . ',' . BanksConstants::COL_ACC_N . ',' .
                        $bankAccount->id . ',id,' . DatabaseConstants::COL_TABLE_CREATOR . ',' . $creatorId,
                    BanksConstants::COL_OB    => 'required|numeric',
                    BanksConstants::COL_CT    => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ]);
                if ($v->fails()) {
                    return redirect()->route(self::ROUTE_SINGULAR . '.index')
                        ->with('error', $v->errors()->first());
                }

                DB::beginTransaction();
                $t = microtime(true);
                $data = $request->only([
                    BanksConstants::COL_COA,
                    BanksConstants::COL_HNM,
                    BanksConstants::COL_NM,
                    BanksConstants::COL_ACC_N,
                    BanksConstants::COL_OB,
                    BanksConstants::COL_CT,
                    BanksConstants::COL_ADR
                ]) + [DatabaseConstants::COL_TABLE_CREATOR => $creatorId];

                $bankAccount->update($data);
                CustomField::saveData($bankAccount, $request->customField);

                $this->logExecutionTime($t, $action, 'updateAccount');
                DB::commit();

                return redirect()->route(self::ROUTE_SINGULAR . '.index')
                    ->with('success', __('Account successfully updated.'));
            } catch (AuthorizationException $e) {
                DB::rollBack();
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($action . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['bank_account_id' => $bankAccount->id ?? null, 'input' => $request->only(BanksConstants::COL_NM, BanksConstants::COL_ACC_N)]);
    }

    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $bankAccount, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            try {
                $this->_authorize($request, 'delete bank account');

                DB::beginTransaction();
                $t = microtime(true);

                $lock = BankAccount::where('id', $bankAccount->id)->lockForUpdate()->firstOrFail();
                if ($lock->created_by !== $request->user()->creatorId()) {
                    throw new AuthorizationException;
                }

                $relationsExist = (
                    Revenue::where(BanksConstants::COL_REL_ID, $lock->id)->exists()
                    || InvoicePayment::where(BanksConstants::COL_REL_ID, $lock->id)->exists()
                    || Transaction::where(self::SINGULAR, $lock->id)->exists()
                    || Payment::where(BanksConstants::COL_REL_ID, $lock->id)->exists()
                    || BillPayment::where(BanksConstants::COL_REL_ID, $lock->id)->exists()
                );

                if ($relationsExist) {
                    DB::rollBack();
                    return redirect()->route(self::ROUTE_SINGULAR . '.index')
                        ->with('error', __('Please delete related records first.'));
                }

                $lock->delete();
                $this->logExecutionTime($t, $action, 'deleteAccount');

                DB::commit();
                return redirect()->route(self::ROUTE_SINGULAR . '.index')
                    ->with('success', __('Account successfully deleted.'));
            } catch (AuthorizationException $e) {
                DB::rollBack();
                return defaultPermissionDenial($request, $e, $action);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error($action . ' failed', ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['bank_account_id' => $bankAccount->id ?? null]);
    }

    /**
     * Authorize the current user for a given ability.
     *
     * @throws AuthorizationException
     */
    private function _authorize(Request $request, string $ability): void
    {
        if (!$request->user()?->can($ability)) {
            Log::warning(__METHOD__ . ' permission denied', [
                'user_id' => Auth::id(),
                'ability' => $ability,
            ]);
            throw new AuthorizationException("Unauthorized: {$ability}");
        }
    }
}
