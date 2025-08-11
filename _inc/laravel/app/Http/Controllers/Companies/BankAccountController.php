<?php

namespace App\Http\Controllers;

use App\Config\Constants\{BanksConstants, DatabaseConstants};
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
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};

class BankAccountController extends Controller
{
    use ChecksLogin;

    private const SINGULAR = 'account';
    private const ROUTE_SINGULAR = 'bank-' . self::SINGULAR;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            $user = $userOrRedirect;
            $this->_authorize($request, 'view bank account');
            $accounts = BankAccount::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(
                self::ROUTE_SINGULAR . '.' . __FUNCTION__,
                compact(DatabaseConstants::TABLE_BANK_ACC . ",")
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            $this->_authorize($request, 'create bank account');
            $creatorId = $request->user()->creatorId();
            $chartAccounts = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('code_name', 'id')
                ->prepend(__('Select Account'), '');
            $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('module', self::SINGULAR)
                ->get();
            return view(
                self::ROUTE_SINGULAR . '.' . __FUNCTION__,
                compact('chartAccounts', 'customFields')
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::create error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                    return $userOrRedirect;
                }
                $this->_authorize($request, 'create bank account');
                $creatorId = $request->user()->creatorId();
                $validator = Validator::make($request->all(), [
                    BanksConstants::COL_HNM     => 'required|string',
                    BanksConstants::COL_NM       => 'required|string',
                    BanksConstants::COL_ACC_N  => "required|string|unique:," . DatabaseConstants::TABLE_BANK_ACC . ", " .
                        BanksConstants::COL_ACC_N . ",NULL,id," .
                        DatabaseConstants::TABLE_CREATOR . ",{$creatorId}",
                    BanksConstants::COL_OB => 'required|numeric',
                    BanksConstants::COL_CT  => 'required|regex:/^([0-9\s\-\+\(\)]*)$/'
                ]);
                if ($validator->fails()) {
                    return redirect()->route(self::ROUTE_SINGULAR . 'index')
                        ->with('error', $validator->errors()->first());
                }
                $data = $request->only([
                    BanksConstants::COL_COA, BanksConstants::COL_HNM, BanksConstants::COL_NM,
                    BanksConstants::COL_ACC_N, BanksConstants::COL_OB,
                    BanksConstants::COL_CT, BanksConstants::COL_ADR
                ]) + [DatabaseConstants::TABLE_CREATOR => $creatorId];
                $account = BankAccount::create($data);
                CustomField::saveData($account, $request->customField);
                return redirect()->route(self::ROUTE_SINGULAR . 'index')
                    ->with('success', __('Account successfully created.'));
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
                throw $e;
            }
        }, 3);
    }

    public function show(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        Log::info(__METHOD__, [
            BanksConstants::COL_REL_USER => Auth::id(),
            BanksConstants::COL_REL_ID => $bankAccount->id
        ]);
        if (
            ($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse
        ) return $userOrRedirect;
        try {
            $this->_authorize($request, 'view bank account');
            if ($bankAccount->created_by !== $request->user()->creatorId()) {
                Log::warning(__METHOD__ . ' access denied', [
                    BanksConstants::COL_REL_USER      => Auth::id(),
                    BanksConstants::COL_REL_ID   => $bankAccount->id
                ]);
                throw new \Illuminate\Auth\Access\AuthorizationException;
            }
            Log::info(__METHOD__ . ' redirecting to index', [BanksConstants::COL_REL_ID => $bankAccount->id]);
            return redirect()->route(self::ROUTE_SINGULAR . 'index');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning(__METHOD__ . ' authorization exception', ['error' => $e->getMessage()]);
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function edit(Request $request, BankAccount $bankAccount): View|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
                return $userOrRedirect;
            $this->_authorize($request, 'edit bank account');
            if ($bankAccount->created_by !== $request->user()->creatorId()) {
                throw new \Illuminate\Auth\Access\AuthorizationException;
            }
            $creatorId = $request->user()->creatorId();
            $chartAccounts = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->pluck('code_name', 'id')
                ->prepend(__('Select Account'), '');
            $bankAccount->customField = CustomField::getData($bankAccount, self::SINGULAR);
            $customFields = CustomField::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->where('module', self::SINGULAR)
                ->get();
            return view(
                self::ROUTE_SINGULAR . '.' . __FUNCTION__,
                compact('bankAccount', 'chartAccounts', 'customFields')
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        return DB::transaction(function () use ($request, $bankAccount) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                    return $userOrRedirect;
                }
                $this->_authorize($request, 'edit bank account');
                $creatorId = $request->user()->creatorId();
                $lock = BankAccount::where('id', $bankAccount->id)
                    ->lockForUpdate()->firstOrFail();
                $validator = Validator::make($request->all(), [
                    BanksConstants::COL_HNM     => 'required|string',
                    BanksConstants::COL_NM       => 'required|string',
                    BanksConstants::COL_ACC_N  => "required|string|unique:" .
                        DatabaseConstants::TABLE_BANK_ACC . "," .
                        BanksConstants::COL_ACC_N . ",{$bankAccount->id},id," .
                        DatabaseConstants::TABLE_CREATOR . ",{$creatorId}",
                    BanksConstants::COL_OB => 'required|numeric',
                    BanksConstants::COL_CT  => 'required|regex:/^([0-9\s\-\+\(\)]*)$/'
                ]);
                if ($validator->fails())
                    return redirect()->route(self::ROUTE_SINGULAR . 'index')
                        ->with('error', $validator->errors()->first());
                $data = $request->only([
                    BanksConstants::COL_COA, BanksConstants::COL_HNM, BanksConstants::COL_NM,
                    BanksConstants::COL_ACC_N, BanksConstants::COL_OB, BanksConstants::COL_CT,
                    BanksConstants::COL_ADR
                ]) + [DatabaseConstants::TABLE_CREATOR => $creatorId];
                $bankAccount->update($data);
                CustomField::saveData($bankAccount, $request->customField);
                return redirect()->route(self::ROUTE_SINGULAR . 'index')
                    ->with('success', __('Account successfully updated.'));
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['err' => $e->getMessage()]);
                throw $e;
            }
        }, 3);
    }

    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        return DB::transaction(function () use ($request, $bankAccount) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                    return $userOrRedirect;
                }
                $this->_authorize($request, 'delete bank account');
                $lock = BankAccount::where('id', $bankAccount->id)
                    ->lockForUpdate()->firstOrFail();
                if ($lock->created_by !== $request->user()->creatorId())
                    throw new \Illuminate\Auth\Access\AuthorizationException;
                $relations = [
                    Revenue::where(BanksConstants::COL_REL_ID, $lock->id)->exists(),
                    InvoicePayment::where(BanksConstants::COL_REL_ID, $lock->id)->exists(),
                    Transaction::where(self::SINGULAR, $lock->id)->exists(),
                    Payment::where(BanksConstants::COL_REL_ID, $lock->id)->exists(),
                    BillPayment::where(BanksConstants::COL_REL_ID, $lock->id)->exists()
                ];
                if (in_array(true, $relations, true))
                    return redirect()->route(self::ROUTE_SINGULAR . 'index')
                        ->with('error', __('Please delete related records first.'));
                $lock->delete();
                return redirect()->route(self::ROUTE_SINGULAR . 'index')
                    ->with('success', __('Account successfully deleted.'));
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error(
                    __CLASS__ . '::' . __FUNCTION__ . ' failed',
                    ['err' => $e->getMessage()]
                );
                throw $e;
            }
        }, 3);
    }
}
