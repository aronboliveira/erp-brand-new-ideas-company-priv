<?php
// TODO STOPPED MEASURING HERE

namespace App\Http\Controllers\Bills;

use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Concerns\HandlesFinanceReliability;

use App\Config\Constants\{
  DatabaseConstants as DC,
  MiddlewaresConstants as MWC,
  PermissionsConstants as PMC,
  UsersConstants as UC,
  ViewsConstants as VW
};
use App\Models\{BankAccount, BankTransfer, OperationLedger, Utility};
use App\Services\Reliability\{CriticalOperationService, ReliabilityPolicy};
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse, Response};
use Illuminate\Support\Facades\{Auth, DB, Log, Redirect, Route, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\DefinesResourceActions;
final class BankTransferController extends Controller
{
	use DefinesResourceActions;

  use ChecksLogin, HandlesFinanceReliability;
  public const IDX = 'index';
  public const CRT = 'create';
  public const STR = 'store';
  public const SHW = 'show';
  public const EDT = 'edit';
  public const UPD = 'update';
  public const DEL = 'destroy';

  public function __construct()
  {
    $this->middleware([MWC::AUTH]);
  }

  public function index(Request $request): View|Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    $viewPath = VW::BNK_TRF . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
      if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$base}::{$action}] unauthenticated access");
        return $redirect;
      }
      $user = $req->user();
      Log::info("[{$base}::{$action}] listing bank transfers", [UC::COL_USER_ID => $user?->id, 'filters' => $req->only(['date', 'fromAccount', 'toAccount']), 'method' => $method]);
      try {
        if (!$user?->can(PMC::MNG_BTF)) {
          Log::warning("[{$base}::{$action}] permission denied", [UC::COL_USER_ID => $user?->id]);
          throw new \Illuminate\Auth\Access\AuthorizationException;
        }
        $buildStart = microtime(true);
        $query = BankTransfer::where(DC::COL_TABLE_CREATOR, $user?->creatorId());
        if ($range = $req->input('date')) {
          [$start, $end] = count($p = explode(' to ', $range)) > 1 ? $p : [$range, $range];
          $query->whereBetween('date', [$start, $end]);
        }
        if ($from = $req->input('fromAccount')) $query->where('from_account', $from);
        if ($to = $req->input('toAccount')) $query->where('to_account', $to);
        $this->logExecutionTime($buildStart, $action, 'buildQuery');
        $fetchStart = microtime(true);
        $transfers = $query->get();
        $this->logExecutionTime($fetchStart, $action, 'fetchTransfers');
        Log::info("[{$base}::{$action}] loaded transfers", ['count' => $transfers->count()]);
        $acctStart = microtime(true);
        $accounts = BankAccount::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('holder_name', 'id')->prepend(__('Select Account'), '');
        $this->logExecutionTime($acctStart, $action, 'fetchAccounts');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfers', 'accounts']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('transfers', 'accounts'));
        $this->logExecutionTime($renderStart, $action, 'renderIndex');
        return $resp;
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] authorization exception", ['message' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] auth debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function create(Request $request): Response|JsonResponse|RedirectResponse|View|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    $viewPath = VW::BNK_TRF . '.' . $action;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base, $viewPath) {
      if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$base}::{$action}] unauthenticated access");
        return $redirect;
      }
      $user = $req->user();
      Log::info("[{$base}::{$action}] showing create transfer form", [UC::COL_USER_ID => $user?->id, 'method' => $method]);
      try {
        if (!$user?->can('create bank transfer')) {
          Log::warning("[{$base}::{$action}] permission denied", [UC::COL_USER_ID => $user?->id]);
          throw new \Illuminate\Auth\Access\AuthorizationException;
        }
        $acctStart = microtime(true);
        $bankAccounts = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name, id")->where(DC::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
        $this->logExecutionTime($acctStart, $action, 'fetchAccounts');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bankAccounts']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('bankAccounts'));
        $this->logExecutionTime($renderStart, $action, 'renderCreate');
        return $resp;
      } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] authorization exception", ['message' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] auth debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function store(Request $request): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
      if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$base}::{$action}] unauthenticated store attempt");
        return $redirect;
      }
      $user = $req->user();
      Log::info("[{$base}::{$action}] attempting to store transfer", [UC::COL_USER_ID => $user?->id, 'input' => $req->only(['fromAccount', 'toAccount', 'amount', 'date']), 'method' => $method]);
      try {
        if (!$user?->can('create bank transfer')) {
          Log::warning("[{$base}::{$action}] permission denied", [UC::COL_USER_ID => $user?->id]);
          throw new \Illuminate\Auth\Access\AuthorizationException;
        }
        $valStart = microtime(true);
        $data = $req->validate(['fromAccount' => 'required|numeric', 'toAccount' => 'required|numeric', 'amount' => 'required|numeric|min:0', 'date' => 'required|date']);
        $this->logExecutionTime($valStart, $action, 'validateRequest');
        $txnStart = microtime(true);
        $financeOperation = $this->runFinanceReliabilityOperation('finance.bank_transfer.create', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($req, $user, $data, $action, $base): array {
          $createStart = microtime(true);
          $transfer = BankTransfer::create([
            'from_account' => $data['fromAccount'],
            'to_account' => $data['toAccount'],
            'amount' => $data['amount'],
            'date' => $data['date'],
            'payment_method' => 0,
            'reference' => $req->input('reference'),
            'description' => $req->input('description'),
            DC::COL_TABLE_CREATOR => $user?->creatorId(),
          ]);
          $this->logExecutionTime($createStart, $action, 'createTransfer');
          $balStart = microtime(true);
          Utility::bankAccountBalance($data['fromAccount'], $data['amount'], 'debit');
          Utility::bankAccountBalance($data['toAccount'], $data['amount'], 'credit');
          $this->logExecutionTime($balStart, $action, 'updateBalances');
          Log::info("[{$base}::{$action}] transfer created", ['transfer_id' => $transfer->id]);
          $payload = $this->bankTransferReliabilityPayload($transfer, 'bank_transfer');
          $operations->recordStep($ledger, 'finance.bank_transfer.persisted', 'Persist bank transfer and account balances', [
            'step_type' => 'db_write',
            'sequence' => 50,
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
            'payload' => $payload,
            'started_at' => now(),
            'finished_at' => now(),
          ]);

          return $payload;
        }, [
          'summary' => 'Create bank transfer',
          'subject_type' => BankTransfer::class,
          'subject_id' => 'bank-transfer:create',
          'actor_id' => $req->user()?->id,
          'context' => ['amount' => (float) ($data['amount'] ?? 0), 'from_account' => $data['fromAccount'] ?? null, 'to_account' => $data['toAccount'] ?? null],
          'event_type' => 'finance.bank_transfer.created',
          'post_write_validation' => true,
          'payload' => fn(array $result): array => $result,
        ]);
        $dispatchReport = $this->dispatchFinanceReliabilityOutbox($financeOperation);
        $this->logExecutionTime($txnStart, $action, 'transaction');
        return Redirect::route(VW::BNK_TRF . '.index')
          ->with('success', __('Amount successfully transferred.'))
          ->with('reliability_operation', $this->financeReliabilityClientPayload($financeOperation, $dispatchReport));
      } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning("[{$base}::{$action}] validation failed", ['errors' => $e->errors()]);
        Log::debug("[{$base}::{$action}] validation debug", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
        return Redirect::back()->with('error', $e->validator->errors()->first());
      } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] authorization exception", ['message' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] auth debug", ['route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage()]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
  }

  public function show(Request $request, BankTransfer $transfer): View|Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    $viewPath = VW::BNK_TRF . '.show';
    return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base, $viewPath) {
      if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$base}::{$action}] unauthenticated access");
        return $redirect;
      }
      Log::info("[{$base}::{$action}] showing transfer", ['transfer_id' => $transfer->id, UC::COL_USER_ID => $req->user()->id, 'method' => $method]);
      try {
        $authStart = microtime(true);
        if (($r = $this->authorizeOwnership($req, $transfer, PMC::MNG_BTF)) !== true) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwnership');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath, 'transfer_id' => $transfer->id]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['transfer']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, ['transfer' => $transfer]);
        $this->logExecutionTime($renderStart, $action, 'renderShow');
        return $resp;
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] permission denied", ['message' => $e->getMessage(), 'transfer_id' => $transfer->id]);
        Log::debug("[{$base}::{$action}] auth debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'transfer_id' => $transfer->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
  }

  public function edit(Request $request, string|int $id): View|Response|RedirectResponse|JsonResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    $viewPath = VW::BNK_TRF . '.edit';
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base, $viewPath) {
      if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$base}::{$action}] unauthenticated edit access");
        return $redirect;
      }
      Log::info("[{$base}::{$action}] editing transfer request", ['transfer_id' => $id, UC::COL_USER_ID => $req->user()->id, 'method' => $method]);
      try {
        $fetchStart = microtime(true);
        $transfer = BankTransfer::findOrFail($id);
        $this->logExecutionTime($fetchStart, $action, 'fetchTransfer');
        $authStart = microtime(true);
        if (($r = $this->authorizeOwnership($req, $transfer, 'edit bank transfer')) !== true) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwnership');
        $acctStart = microtime(true);
        $bankAccounts = BankAccount::selectRaw("CONCAT(bank_name,' ',holder_name) AS name, id")->where(DC::COL_TABLE_CREATOR, $req->user()->creatorId())->pluck('name', 'id');
        $this->logExecutionTime($acctStart, $action, 'fetchAccounts');
        if (!ViewFacade::exists($viewPath)) {
          Log::error("[{$base}::{$action}] missing view", ['view_path' => $viewPath]);
          Log::debug("[{$base}::{$action}] view missing context", ['route' => Route::getCurrentRoute()?->getName(), 'compact_vars' => ['bankAccounts', 'transfer']]);
          return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        }
        $renderStart = microtime(true);
        $resp = view($viewPath, compact('bankAccounts', 'transfer'));
        $this->logExecutionTime($renderStart, $action, 'renderEdit');
        return $resp;
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] authorization exception", ['message' => $e->getMessage(), 'transfer_id' => $id]);
        Log::debug("[{$base}::{$action}] auth debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'transfer_id' => $id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $id]);
  }

  public function update(Request $request, string|int $id): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    return $this->measureProfile($action, function () use ($req, $id, $action, $method, $class, $base) {
      if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$base}::{$action}] unauthenticated update access");
        return $redirect;
      }
      Log::info("[{$base}::{$action}] updating transfer", ['transfer_id' => $id, 'input' => $req->only(['fromAccount', 'toAccount', 'amount', 'date']), 'method' => $method]);
      try {
        $fetchStart = microtime(true);
        $transfer = BankTransfer::findOrFail($id);
        $this->logExecutionTime($fetchStart, $action, 'fetchTransfer');
        $authStart = microtime(true);
        if (($r = $this->authorizeOwnership($req, $transfer, 'edit bank transfer')) !== true) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwnership');
        $valStart = microtime(true);
        $data = $req->validate(['fromAccount' => 'required|numeric', 'toAccount' => 'required|numeric', 'amount' => 'required|numeric|min:0', 'date' => 'required|date']);
        $this->logExecutionTime($valStart, $action, 'validateRequest');
        $prevFrom = $transfer->from_account;
        $prevTo   = $transfer->to_account;
        $prevAmt  = $transfer->amount;
        $txnStart = microtime(true);
        $financeOperation = $this->runFinanceReliabilityOperation('finance.bank_transfer.update', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($req, $transfer, $data, $prevFrom, $prevTo, $prevAmt, $action): array {
          $revertStart = microtime(true);
          Utility::bankAccountBalance($prevFrom, $prevAmt, 'credit');
          Utility::bankAccountBalance($prevTo, $prevAmt, 'debit');
          $this->logExecutionTime($revertStart, $action, 'revertBalances');
          $updStart = microtime(true);
          $transfer->update([
            'from_account' => $data['fromAccount'],
            'to_account' => $data['toAccount'],
            'amount' => $data['amount'],
            'date' => $data['date'],
            'reference' => $req->input('reference'),
            'description' => $req->input('description'),
          ]);
          $this->logExecutionTime($updStart, $action, 'updateTransfer');
          $applyStart = microtime(true);
          Utility::bankAccountBalance($data['fromAccount'], $data['amount'], 'debit');
          Utility::bankAccountBalance($data['toAccount'], $data['amount'], 'credit');
          $this->logExecutionTime($applyStart, $action, 'applyBalances');
          $payload = $this->bankTransferReliabilityPayload($transfer, 'bank_transfer_update');
          $operations->recordStep($ledger, 'finance.bank_transfer.updated', 'Update bank transfer and account balances', [
            'step_type' => 'db_write',
            'sequence' => 50,
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
            'payload' => array_merge($payload, [
              'previous_from_account' => $prevFrom ? (string) $prevFrom : null,
              'previous_to_account' => $prevTo ? (string) $prevTo : null,
              'previous_amount' => (float) $prevAmt,
            ]),
            'started_at' => now(),
            'finished_at' => now(),
          ]);

          return $payload;
        }, [
          'summary' => 'Update bank transfer',
          'subject_type' => BankTransfer::class,
          'subject_id' => (string) $transfer->id,
          'actor_id' => $req->user()?->id,
          'context' => ['bank_transfer_id' => (string) $transfer->id, 'amount' => (float) ($data['amount'] ?? 0)],
          'event_type' => 'finance.bank_transfer.updated',
          'post_write_validation' => true,
          'payload' => fn(array $result): array => $result,
        ]);
        $dispatchReport = $this->dispatchFinanceReliabilityOutbox($financeOperation);
        $this->logExecutionTime($txnStart, $action, 'transaction');
        Log::info("[{$base}::{$action}] transfer updated", ['transfer_id' => $transfer->id]);
        return Redirect::route(VW::BNK_TRF . '.index')
          ->with('success', __('Amount transfer successfully updated.'))
          ->with('reliability_operation', $this->financeReliabilityClientPayload($financeOperation, $dispatchReport));
      } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning("[{$base}::{$action}] validation failed", ['errors' => $e->errors()]);
        Log::debug("[{$base}::{$action}] validation debug", ['route' => Route::getCurrentRoute()?->getName(), 'input_keys' => array_keys($req->all())]);
        return Redirect::back()->with('error', $e->validator->errors()->first());
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] authorization exception", ['message' => $e->getMessage(), 'transfer_id' => $id]);
        Log::debug("[{$base}::{$action}] auth debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'transfer_id' => $id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $id]);
  }

  public function destroy(Request $request, BankTransfer $transfer): RedirectResponse|null
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class  = static::class;
    $base   = class_basename($class);
    $req    = $request;
    return $this->measureProfile($action, function () use ($req, $transfer, $action, $method, $class, $base) {
      if (($redirect = self::_checkLogin()) instanceof RedirectResponse) {
        Log::warning("[{$base}::{$action}] unauthenticated destroy access");
        return $redirect;
      }
      Log::info("[{$base}::{$action}] destroying transfer", ['transfer_id' => $transfer->id, UC::COL_USER_ID => $req->user()->id, 'method' => $method]);
      try {
        $authStart = microtime(true);
        if (($r = $this->authorizeOwnership($req, $transfer, 'delete bank transfer')) !== true) return $r;
        $this->logExecutionTime($authStart, $action, 'authorizeOwnership');
        $txnStart = microtime(true);
        $financeOperation = $this->runFinanceReliabilityOperation('finance.bank_transfer.delete', function (?OperationLedger $ledger, CriticalOperationService $operations) use ($transfer, $action): array {
          $payload = $this->bankTransferReliabilityPayload($transfer, 'bank_transfer_reversal');
          $delStart = microtime(true);
          $transfer->delete();
          $this->logExecutionTime($delStart, $action, 'deleteTransfer');
          $balStart = microtime(true);
          Utility::bankAccountBalance($transfer->from_account, $transfer->amount, 'credit');
          Utility::bankAccountBalance($transfer->to_account, $transfer->amount, 'debit');
          $this->logExecutionTime($balStart, $action, 'updateBalances');
          $operations->recordStep($ledger, 'finance.bank_transfer.deleted', 'Delete bank transfer and reverse account balances', [
            'step_type' => 'db_write',
            'sequence' => 50,
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
            'payload' => $payload,
            'started_at' => now(),
            'finished_at' => now(),
          ]);

          return $payload;
        }, [
          'summary' => 'Delete bank transfer',
          'subject_type' => BankTransfer::class,
          'subject_id' => (string) $transfer->id,
          'actor_id' => $req->user()?->id,
          'context' => ['bank_transfer_id' => (string) $transfer->id, 'amount' => (float) ($transfer->amount ?? 0)],
          'event_type' => 'finance.bank_transfer.deleted',
          'post_write_validation' => true,
          'payload' => fn(array $result): array => $result,
        ]);
        $dispatchReport = $this->dispatchFinanceReliabilityOutbox($financeOperation);
        $this->logExecutionTime($txnStart, $action, 'transaction');
        Log::info("[{$base}::{$action}] transfer deleted", ['transfer_id' => $transfer->id]);
        return Redirect::route(VW::BNK_TRF . '.index')
          ->with('success', __('Transfer successfully deleted.'))
          ->with('reliability_operation', $this->financeReliabilityClientPayload($financeOperation, $dispatchReport));
      } catch (AuthorizationException $e) {
        Log::warning("[{$base}::{$action}] authorization exception", ['message' => $e->getMessage(), 'transfer_id' => $transfer->id]);
        Log::debug("[{$base}::{$action}] auth debug", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultPermissionDenial($req, $e, $class . '::' . $action);
      } catch (\Throwable $e) {
        Log::error("[{$base}::{$action}] failed", ['error' => $e->getMessage(), 'transfer_id' => $transfer->id]);
        Log::debug("[{$base}::{$action}] debug context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'route' => Route::getCurrentRoute()?->getName()]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'transfer_id' => $transfer->id]);
  }

  /**
   * @return array<string, mixed>
   */
  private function bankTransferReliabilityPayload(BankTransfer $transfer, string $direction): array
  {
    return [
      'bank_transfer_id' => (string) $transfer->id,
      'transfer_id' => (string) $transfer->id,
      'from_account' => $transfer->from_account ? (string) $transfer->from_account : null,
      'to_account' => $transfer->to_account ? (string) $transfer->to_account : null,
      'amount' => (float) ($transfer->amount ?? 0),
      'date' => (string) ($transfer->date ?? ''),
      'direction' => $direction,
    ];
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
    if (strtolower((string)($user?->type ?? '')) === \App\Config\Constants\PermissionsConstants::SA) {
      Log::notice('SA bypass for transfer ownership', [
        UC::COL_USER_ID => $user?->id,
        'transfer_id' => $transfer->id,
        'permission'  => $perm
      ]);
      return;
    }
    if (!$user->can($perm) || $transfer->created_by != $user?->creatorId()) {
      Log::warning('Unauthorized transfer access', [
        UC::COL_USER_ID     => $user?->id,
        'transfer_id' => $transfer->id,
        'permission'  => $perm
      ]);
      throw new \Illuminate\Auth\Access\AuthorizationException;
    }
    Log::info('Ownership authorized', [
      UC::COL_USER_ID     => $user?->id,
      'transfer_id' => $transfer->id,
      'permission'  => $perm
    ]);
  }
}
