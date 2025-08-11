<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  MiddlewaresConstants,
  PermissionsConstants,
  SettingsConstants,
  ViewsConstants,
  UsersConstants
};
use App\Models\{Award, AwardType, Employee, Utility};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\{DB, Log};

final class AwardController extends Controller
{

  public function __construct()
  {
    $this->middleware(MiddlewaresConstants::AUTH);
  }

  public function index(Request $request): View|RedirectResponse|JsonResponse
  {
    $action = 'index';
    Log::info(__CLASS__ . "::{$action} start", [UsersConstants::COL_USER_ID => $request->user()->id]);
    if ($deny = $this->authorizeOrDeny($request, PermissionsConstants::MNG_AWD, $action))
      return $deny;
    try {
      $user     = $request->user();
      $creatorId = $user?->creatorId();
      $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      $types     = AwardType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      $query = Award::with(['employee', 'awardType']);
      if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
        $empId = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id');
        $query->where('employee_id', $empId);
      } else
        $query->where(DatabaseConstants::TABLE_CREATOR, $creatorId);
      $awards = $query->get();
      Log::info(__CLASS__ . "::{$action} success", ['count' => $awards->count()]);
      return view(ViewsConstants::AWD . '.' . __FUNCTION__, compact('awards', 'employees', 'types'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . "::{$action} error", [
        'err'     => $e->getMessage(),
      ]);
      Log::channel(SettingsConstants::ERR_TRACE)->debug(__CLASS__ . "::{$action} error", [
        'err'     => $e->getMessage(),
        'trace'   => $e->getTraceAsString(),
      ]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
    }
  }

  public function create(Request $request): View|JsonResponse
  {
    $action = 'create';
    Log::info(__CLASS__ . "::{$action} start", [UsersConstants::COL_USER_ID => $request->user()->id]);
    if ($deny = $this->authorizeOrDeny($request, 'create award', $action))
      return $deny;
    try {
      $creatorId = $request->user()->creatorId();
      $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');
      $types     = AwardType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');

      Log::info(__CLASS__ . "::{$action} success");
      return view(ViewsConstants::AWD . '.' . __FUNCTION__, compact('employees', 'types'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . "::{$action} error", ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
    }
  }

  public function store(Request $request): RedirectResponse|JsonResponse
  {
    $action = 'store';
    Log::info(__CLASS__ . "::{$action} start", [UsersConstants::COL_USER_ID => $request->user()->id, 'input' => $request->all()]);

    if ($deny = $this->authorizeOrDeny($request, 'create award', $action)) {
      return $deny;
    }

    $validated = $request->validate([
      'employee_id' => 'required|exists:employees,id',
      'award_type'  => 'required|exists:award_types,id',
      'date'        => 'required|date',
      'gift'        => 'required|string',
    ]);

    try {
      $award = DB::transaction(function () use ($validated, $request) {
        return Award::create([
          'employee_id' => $validated['employee_id'],
          'award_type'  => $validated['award_type'],
          'date'        => $validated['date'],
          'gift'        => $validated['gift'],
          'description' => $request->input('description'),
          DatabaseConstants::TABLE_CREATOR  => $request->user()->creatorId(),
        ]);
      });

      Log::info(__CLASS__ . "::{$action} created award", ['award_id' => $award->id]);

      // send notifications but don’t block save
      try {
        $creatorId = $request->user()->creatorId();
        $setting   = Utility::settings($creatorId);
        $employee  = Employee::findOrFail($award->employee_id);
        $awardType = AwardType::findOrFail($award->award_type);
        $data      = [
          'award_name'    => $awardType->name,
          'employee_name' => $employee->name,
          'award_date'    => $award->date,
        ];

        if (!empty($setting['award_notification'])) {
          Utility::sendSlackMsg('new_award', $data);
        }
        if (!empty($setting['telegram_award_notification'])) {
          Utility::sendTelegramMsg('new_award', $data);
        }
        if (!empty(Utility::settings()['new_award'])) {
          Utility::sendEmailTemplate(
            'new_award',
            [$employee->id => $employee->email],
            ['award_name' => $awardType->name, 'award_email' => $employee->email]
          );
        }
      } catch (\Throwable $notifEx) {
        Log::warning(__CLASS__ . "::{$action} notifications failed", ['err' => $notifEx->getMessage()]);
      }

      // webhook
      try {
        if ($hook = Utility::webhookSetting('New Award')) {
          if (!Utility::webhookCall($hook['url'], $award->toJson(), $hook['method'])) {
            Log::warning(__CLASS__ . "::{$action} webhook returned false");
            return redirect()->back()->with('error', __('Webhook call failed.'));
          }
        }
      } catch (\Throwable $hookEx) {
        Log::warning(__CLASS__ . "::{$action} webhook error", ['err' => $hookEx->getMessage()]);
      }

      return redirect()->route(ViewsConstants::AWD . '.index')->with('success', __('Award successfully created.'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . $action);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . "::{$action} error", ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
    }
  }

  public function show(Award $award): RedirectResponse
  {
    return redirect()->route(ViewsConstants::AWD . '.index');
  }

  public function edit(Request $request, Award $award): View|JsonResponse
  {
    $action = 'edit';
    Log::info(__CLASS__ . "::{$action} start", ['award_id' => $award->id, UsersConstants::COL_USER_ID => $request->user()->id]);

    if ($deny = $this->authorizeOrDeny($request, 'edit award', $action)) {
      return $deny;
    }
    if ($award->created_by !== $request->user()->creatorId()) {
      Log::warning(__CLASS__ . "::{$action} forbidden owner-mismatch", [
        'award_id' => $award->id, UsersConstants::COL_USER_ID => $request->user()->id
      ]);
      return response()->json(['error' => __('Permission denied.')], 401);
    }

    try {
      $creatorId = $request->user()->creatorId();
      $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');
      $types     = AwardType::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->pluck('name', 'id');

      Log::info(__CLASS__ . "::{$action} success");
      return view(ViewsConstants::AWD . '.edit', compact('award', 'employees', 'types'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . "::{$action} error", ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
    }
  }

  public function update(Request $request, Award $award): RedirectResponse
  {
    $action = 'update';
    Log::info(__CLASS__ . "::{$action} start", ['award_id' => $award->id, 'input' => $request->all()]);

    if ($deny = $this->authorizeOrDeny($request, 'edit award', $action)) {
      return $deny;
    }
    if ($award->created_by !== $request->user()->creatorId()) {
      return redirect()->back()->with('error', __('Permission denied.'));
    }

    $validated = $request->validate([
      'employee_id' => 'required|exists:employees,id',
      'award_type'  => 'required|exists:award_types,id',
      'date'        => 'required|date',
      'gift'        => 'required|string',
    ]);

    try {
      DB::transaction(
        fn () => Award::where('id', $award->id)
          ->lockForUpdate()
          ->firstOrFail()
          ->update(array_merge($validated, [
            'description' => $request->input('description')
          ]))
      );

      Log::info(__CLASS__ . "::{$action} success", ['award_id' => $award->id]);
      return redirect()->route(ViewsConstants::AWD . '.index')->with('success', __('Award successfully updated.'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . $action);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . "::{$action} error", ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
    }
  }

  public function destroy(Request $request, Award $award): RedirectResponse
  {
    $action = 'destroy';
    Log::info(__CLASS__ . "::{$action} start", ['award_id' => $award->id]);

    if ($deny = $this->authorizeOrDeny($request, 'delete award', $action)) {
      return $deny;
    }
    if ($award->created_by !== $request->user()->creatorId()) {
      return redirect()->back()->with('error', __('Permission denied.'));
    }

    try {
      DB::transaction(
        fn () => Award::where('id', $award->id)
          ->lockForUpdate()
          ->firstOrFail()
          ->delete()
      );

      Log::info(__CLASS__ . "::{$action} success", ['award_id' => $award->id]);
      return redirect()->route(ViewsConstants::AWD . '.index')->with('success', __('Award successfully deleted.'));
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial($request, $e, __CLASS__ . '::' . $action);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . "::{$action} error", ['err' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . $action);
    }
  }

  /** 
   * Centralize authorization and log failures 
   * @return null|RedirectResponse
   */
  private function authorizeOrDeny(Request $req, string $permission, string $action): ?RedirectResponse
  {
    if (!$req->user()->can($permission)) {
      Log::warning(__CLASS__ . "::{$action} unauthorized", [
        UsersConstants::COL_USER_ID   => $req->user()->id,
        'permission' => $permission,
      ]);
      return defaultPermissionDenial(
        $req,
        new AuthorizationException(),
        __CLASS__ . '::' . $action
      );
    }
    return null;
  }
}
