<?php
// TODO STOPPED MEASURING HERE

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{BankAccount, BankTransfer, Utility};
use App\Traits\ChecksLogin;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Redirect};

final class BankTransferController extends Controller
{
  use ChecksLogin;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH]);
  }

  public function index(Request $request): Response|RedirectResponse|JsonResponse|null
  {
    if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('Unauthenticated index access');
      return $redirect;
    }
    $user = $request->user();
    Log::info('Listing bank transfers', [
      UsersConstants::COL_USER_ID => $user?->id,
      'filters' => $request->only(['date', 'fromAccount', 'toAccount'])
    ]);
    try {
      if (!$user?->can(PermissionsConstants::MNG_BTF)) {
        Log::warning('Permission denied for listing transfers', [UsersConstants::COL_USER_ID => $user?->id]);
        throw new \Illuminate\Auth\Access\AuthorizationException;
      }
      $query = BankTransfer::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId());
      if ($range = $request->input('date')) {
        [$start, $end] = count($p = explode(' to ', $range)) > 1 ? $p : [$range, $range];
        $query->whereBetween('date', [$start, $end]);
      }
      if ($from = $request->input('fromAccount'))
        $query->where('from_account', $from);
      if ($to = $request->input('toAccount'))
        $query->where('to_account', $to);
      $transfers = $query->get();
      Log::info('Loaded transfers', ['count' => $transfers->count()]);
      $accounts = BankAccount::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
        ->pluck('holder_name', 'id')
        ->prepend(__('Select Account'), '');
      return view(ViewsConstants::BNK_TRF . '.' . __FUNCTION__, compact('transfers', 'accounts'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function create(Request $request): Response|JsonResponse|null
  {
    if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('Unauthenticated create access');
      return $redirect;
    }
    $user = $request->user();
    Log::info('Showing create transfer form', [UsersConstants::COL_USER_ID => $user?->id]);

    try {
      if (!$user?->can('create bank transfer')) {
        Log::warning('Permission denied for create transfer', [UsersConstants::COL_USER_ID => $user?->id]);
        throw new \Illuminate\Auth\Access\AuthorizationException;
      }
      $bankAccounts = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name", 'id')
        ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
        ->pluck('name', 'id');
      return view(ViewsConstants::BNK_TRF . '.' . __FUNCTION__, compact('bankAccounts'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function store(Request $request): RedirectResponse|null
  {
    if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('Unauthenticated store attempt');
      return $redirect;
    }
    $user = $request->user();
    Log::info('Attempting to store transfer', [
      UsersConstants::COL_USER_ID => $user?->id,
      'input' => $request->only(['fromAccount', 'toAccount', 'amount', 'date'])
    ]);
    try {
      if (!$user?->can('create bank transfer')) {
        Log::warning('Permission denied for store transfer', [UsersConstants::COL_USER_ID => $user?->id]);
        throw new \Illuminate\Auth\Access\AuthorizationException;
      }
      $data = $request->validate([
        'fromAccount' => 'required|numeric',
        'toAccount' => 'required|numeric',
        'amount' => 'required|numeric|min:0',
        'date' => 'required|date'
      ]);
      DB::beginTransaction();
      $transfer = BankTransfer::create([
        'from_account'   => $data['fromAccount'],
        'to_account'     => $data['toAccount'],
        'amount'         => $data['amount'],
        'date'           => $data['date'],
        'payment_method' => 0,
        'reference'      => $request->input('reference'),
        'description'    => $request->input('description'),
        DatabaseConstants::TABLE_CREATOR     => $user?->creatorId(),
      ]);
      Utility::bankAccountBalance($data['fromAccount'], $data['amount'], 'debit');
      Utility::bankAccountBalance($data['toAccount'], $data['amount'], 'credit');
      DB::commit();
      Log::info('Transfer created', ['transfer_id' => $transfer->id]);
      return Redirect::route(ViewsConstants::BNK_TRF . '.index')
        ->with('success', __('Amount successfully transferred.'));
    } catch (\Illuminate\Validation\ValidationException $e) {
      Log::warning('Validation failed for store transfer', ['errors' => $e->errors()]);
      return Redirect::back()->with('error', $e->validator->errors()->first());
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      DB::rollBack();
      return defaultPermissionDenial($request, $e, __CLASS__ . '::store');
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error(__CLASS__ . '::store failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::store');
    }
  }

  public function show(Request $request, BankTransfer $transfer): Response|RedirectResponse|JsonResponse|null
  {
    if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('Unauthenticated show access');
      return $redirect;
    }
    Log::info('Showing transfer', [
      'transfer_id' => $transfer->id,
      UsersConstants::COL_USER_ID => $request->user()->id
    ]);
    try {
      $this->authorizeOwnership($request, $transfer, PermissionsConstants::MNG_BTF);
      return view(ViewsConstants::BNK_TRF . '.' . __FUNCTION__, ['transfer' => $transfer]);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function edit(Request $request, string|int $id): Response|JsonResponse|null
  {
    if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('Unauthenticated edit access');
      return $redirect;
    }
    Log::info('Editing transfer request', ['transfer_id' => $id, UsersConstants::COL_USER_ID => $request->user()->id]);
    try {
      $transfer = BankTransfer::findOrFail($id);
      $this->authorizeOwnership($request, $transfer, 'edit bank transfer');
      $bankAccounts = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name", 'id')
        ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->pluck('name', 'id');
      return view(ViewsConstants::BNK_TRF . '.' . __FUNCTION__, compact('bankAccounts', 'transfer'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function update(Request $request, string|int $id): RedirectResponse|null
  {
    if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('Unauthenticated update access');
      return $redirect;
    }
    Log::info('Updating transfer', [
      'transfer_id' => $id,
      'input' => $request->only(['fromAccount', 'toAccount', 'amount', 'date'])
    ]);
    try {
      $transfer = BankTransfer::findOrFail($id);
      $this->authorizeOwnership($request, $transfer, 'edit bank transfer');
      $data = $request->validate([
        'fromAccount' => 'required|numeric',
        'toAccount' => 'required|numeric',
        'amount' => 'required|numeric|min:0',
        'date' => 'required|date'
      ]);
      DB::beginTransaction();
      Utility::bankAccountBalance($transfer->from_account, $transfer->amount, 'credit');
      Utility::bankAccountBalance($transfer->to_account, $transfer->amount, 'debit');
      $transfer->update([
        'from_account' => $data['fromAccount'],
        'to_account'  => $data['toAccount'],
        'amount'      => $data['amount'],
        'date'        => $data['date'],
        'reference'   => $request->input('reference'),
        'description' => $request->input('description'),
      ]);
      Utility::bankAccountBalance($data['fromAccount'], $data['amount'], 'debit');
      Utility::bankAccountBalance($data['toAccount'], $data['amount'], 'credit');
      DB::commit();
      Log::info('Transfer updated', ['transfer_id' => $transfer->id]);
      return Redirect::route(ViewsConstants::BNK_TRF . '.index')
        ->with('success', __('Amount transfer successfully updated.'));
    } catch (\Illuminate\Validation\ValidationException $e) {
      DB::rollBack();
      Log::warning('Validation failed for update transfer', ['errors' => $e->errors()]);
      return Redirect::back()->with('error', $e->validator->errors()->first());
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      DB::rollBack();
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function destroy(Request $request, BankTransfer $transfer): RedirectResponse|null
  {
    if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('Unauthenticated destroy access');
      return $redirect;
    }
    Log::info('Destroying transfer', [
      'transfer_id' => $transfer->id,
      UsersConstants::COL_USER_ID => $request->user()->id
    ]);
    try {
      $this->authorizeOwnership($request, $transfer, 'delete bank transfer');
      DB::beginTransaction();
      $transfer->delete();
      Utility::bankAccountBalance($transfer->from_account, $transfer->amount, 'credit');
      Utility::bankAccountBalance($transfer->to_account, $transfer->amount, 'debit');
      DB::commit();
      Log::info('Transfer deleted', ['transfer_id' => $transfer->id]);
      return Redirect::route(ViewsConstants::BNK_TRF . '.index')
        ->with('success', __('Transfer successfully deleted.'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      DB::rollBack();
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  private function authorizeOwnership(Request $request, BankTransfer $transfer, string $perm): void
  {
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::warning('Unauthenticated access to transfer authorization', [
        'route' => __METHOD__
      ]);
      throw new \Illuminate\Auth\Access\AuthorizationException;
    }
    $user = $userOrRedirect;
    if (!$user->can($perm) || $transfer->created_by != $user?->creatorId()) {
      Log::warning('Unauthorized transfer access', [
        UsersConstants::COL_USER_ID     => $user?->id,
        'transfer_id' => $transfer->id,
        'permission'  => $perm
      ]);
      throw new \Illuminate\Auth\Access\AuthorizationException;
    }
    Log::info('Ownership authorized', [
      UsersConstants::COL_USER_ID     => $user?->id,
      'transfer_id' => $transfer->id,
      'permission'  => $perm
    ]);
  }
}
