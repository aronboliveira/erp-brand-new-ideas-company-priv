<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{Allowance, AllowanceOption, Employee};
use App\Traits\ChecksLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse, Response};
use Illuminate\Support\Facades\{DB, Log, Validator};
use Throwable;

final class AllowanceController extends Controller
{
  use ChecksLogin;

  public function __construct()
  {
    $this->middleware([MiddlewaresConstants::AUTH]);
  }

  public const ALW_CR = 'allowanceCreate';
  public function allowanceCreate(Request $req, int|string $id): View|RedirectResponse|JsonResponse|null
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::info('Redirecting to login in allowanceCreate');
      return $u;
    }
    $user = $u;
    if ($r = self::_authorize($req, 'create allowance'))
      return $r;
    Log::info('Rendering allowance create form', [UsersConstants::COL_USER_ID => $user?->id, UsersConstants::COL_EMP_ID => $id]);
    try {
      $creatorId = $user?->creatorId();
      $options  = AllowanceOption::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');
      $employee = Employee::findOrFail($id);
      $types    = Allowance::$Allowancetype;
      return view(ViewsConstants::ALW . '.create', compact('employee', 'options', 'types'));
    } catch (AuthorizationException $e) {
      Log::warning('Permission denied in allowanceCreate', [UsersConstants::COL_USER_ID => $user?->id]);
      return defaultPermissionDenial($req, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (Throwable $e) {
      return self::catchErr($req, $e);
    }
  }

  public function store(Request $req): RedirectResponse|JsonResponse|null
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::info('Redirecting to login in store');
      return $u;
    }
    $user = $u;
    if ($r = self::_authorize($req, 'create allowance'))
      return $r;
    if ($r = self::validateReq($req->all(), [
      UsersConstants::COL_EMP_ID      => 'required|exists:employees,id',
      'allowance_option' => 'required|exists:allowance_options,id',
      'title'            => 'required|string',
      'amount'           => 'required|numeric|min:0',
    ]))
      return $r;
    Log::info('Creating allowance', [
      UsersConstants::COL_USER_ID          => $user?->id,
      UsersConstants::COL_EMP_ID      => $req->employee_id,
      'allowance_option' => $req->allowance_option,
      'title'            => $req->title,
      'amount'           => $req->amount,
      'type'             => $req->type,
    ]);
    try {
      DB::transaction(function () use ($req, $user) {
        $allowance = Allowance::create([
          UsersConstants::COL_EMP_ID      => $req->employee_id,
          'allowance_option' => $req->allowance_option,
          'title'            => $req->title,
          'type'             => $req->type,
          'amount'           => $req->amount,
          DatabaseConstants::TABLE_CREATOR       => $user?->creatorId(),
        ]);
        Log::info('Allowance record created', [
          'allowance_id' => $allowance->id,
          UsersConstants::COL_EMP_ID  => $allowance->employee_id,
        ]);
      });
      return redirect()->back()->with('success', __('Allowance successfully created.'));
    } catch (AuthorizationException $e) {
      Log::warning('Permission denied in store', [UsersConstants::COL_USER_ID => $user?->id]);
      return defaultPermissionDenial($req, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (Throwable $e) {
      return self::catchErr($req, $e);
    }
  }

  public function edit(Request $req, int|string $allowanceId): View|RedirectResponse|JsonResponse|null
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::info('Redirecting to login in edit');
      return $u;
    }
    $user = $u;
    if ($r = self::_authorize($req, 'edit allowance'))
      return $r;
    Log::info('Rendering allowance edit form', [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowanceId]);
    try {
      $allowance = Allowance::findOrFail($allowanceId);
      if ($allowance[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
        throw new AuthorizationException();
      $creatorId = $user?->creatorId();
      $options  = AllowanceOption::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');
      $types    = Allowance::$Allowancetype;
      return view(ViewsConstants::ALW . '.' . __FUNCTION__, compact('allowance', 'options', 'types'));
    } catch (AuthorizationException $e) {
      Log::warning('Permission denied in edit', [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowanceId]);
      return defaultPermissionDenial($req, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (Throwable $e) {
      return self::catchErr($req, $e);
    }
  }

  public function update(Request $req, Allowance $allowance): RedirectResponse|JsonResponse|null
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::info('Redirecting to login in update');
      return $u;
    }
    $user = $u;
    if ($r = self::_authorize($req, 'edit allowance'))
      return $r;
    if ($allowance[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
      Log::warning('Permission denied on update', [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id]);
      return defaultPermissionDenial($req, new AuthorizationException(), __CLASS__ . '::' . __FUNCTION__);
    }
    if ($r = self::validateReq($req->all(), [
      'allowance_option' => 'required|exists:allowance_options,id',
      'title'            => 'required|string',
      'amount'           => 'required|numeric|min:0',
    ]))
      return $r;
    Log::info('Updating allowance', [
      UsersConstants::COL_USER_ID          => $user?->id,
      'allowance_id'     => $allowance->id,
      'new_option'       => $req->allowance_option,
      'new_title'        => $req->title,
      'new_amount'       => $req->amount,
      'new_type'         => $req->type,
    ]);
    try {
      DB::transaction(function () use ($req, $allowance) {
        $lock = Allowance::where('id', $allowance->id)->lockForUpdate()->firstOrFail();
        $lock->update([
          'allowance_option' => $req->allowance_option,
          'title'            => $req->title,
          'type'             => $req->type,
          'amount'           => $req->amount,
        ]);
        Log::info('Allowance record updated', ['allowance_id' => $lock->id]);
      });
      return redirect()->back()->with('success', __('Allowance successfully updated.'));
    } catch (AuthorizationException $e) {
      Log::warning('Permission denied in update transaction', [UsersConstants::COL_USER_ID => $user?->id]);
      return defaultPermissionDenial($req, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (Throwable $e) {
      return self::catchErr($req, $e);
    }
  }

  public function destroy(Request $req, Allowance $allowance): RedirectResponse|JsonResponse|null
  {
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::info('Redirecting to login in destroy');
      return $u;
    }
    $user = $u;
    if ($r = self::_authorize($req, 'delete allowance'))
      return $r;
    Log::info('Deleting allowance', [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id]);
    try {
      if ($allowance[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId())
        throw new AuthorizationException();
      DB::transaction(function () use ($allowance) {
        $lock = Allowance::where('id', $allowance->id)->lockForUpdate()->firstOrFail();
        $lock->delete();
        Log::info('Allowance record deleted', ['allowance_id' => $allowance->id]);
      });
      return redirect()->back()->with('success', __('Allowance successfully deleted.'));
    } catch (AuthorizationException $e) {
      Log::warning('Permission denied in destroy', [UsersConstants::COL_USER_ID => $user?->id, 'allowance_id' => $allowance->id]);
      return defaultPermissionDenial($req, $e, __CLASS__ . '::' . __FUNCTION__);
    } catch (Throwable $e) {
      return self::catchErr($req, $e);
    }
  }

  public function show(Request $req, Allowance $allowance): RedirectResponse
  {
    return redirect()->route(ViewsConstants::ALW . '.index');
  }

  private static function _authorize(Request $req, string $perm): RedirectResponse|JsonResponse|null
  {
    if (!$req->user()->can($perm)) {
      Log::warning("Authorization failed for {$perm}", [
        UsersConstants::COL_USER_ID => $req->user()->id,
        'route'   => $req->path(),
      ]);
      return defaultPermissionDenial(
        $req,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    }
    return null;
  }

  private static function validateReq(array $data, array $rules): RedirectResponse|JsonResponse|null
  {
    $v = Validator::make($data, $rules);
    if ($v->fails()) {
      Log::info('Validation failed', ['errors' => $v->errors()->all()]);
      return redirect()->back()->with('error', $v->errors()->first());
    }
    return null;
  }

  private static function catchErr(Request $req, Throwable $e): RedirectResponse|JsonResponse|null
  {
    Log::error('Unexpected error in ' . __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'], [
      'exception' => $e,
      UsersConstants::COL_USER_ID   => $req->user()?->id,
      'input'     => $req->all(),
    ]);
    return defaultUndefinedException(
      $req,
      $e,
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
    );
  }
}


// ! ALERT store and update accept raw amount; consider casting to numeric and validating range to prevent injection or overflow.