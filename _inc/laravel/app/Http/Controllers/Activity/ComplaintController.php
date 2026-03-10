<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants,
  LangsConstants,
};
use App\Http\Controllers\Controller as AppController;
use App\Models\{Complaint, Employee, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Route, Validator, View as ViewFacade};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class ComplaintController extends AppController
{
  use ChecksLogin, ChecksPermissions;
  public function index(Request $request): View|Response
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::CPL . '.index';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      try {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (!$user?->can(PermissionsConstants::MNG_CPT)) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
        if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
          $empId = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->value('id');
          $complaints = Complaint::where('complaint_from', $empId)->with(['complaintFrom'])->get();
        } else $complaints = Complaint::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->with(['complaintFrom'])->get();
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('complaints'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function create(Request $request): Response|JsonResponse|View
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::CPL . '.create';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class, $viewPath) {
      try {
        $user = $req->user();
        if (!$user?->can('create complaint')) return response()->json(['error' => __('Permission denied.')], Response::HTTP_UNAUTHORIZED);
        $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->get()->pluck('name', 'id');
        $employees = strtolower($user[UsersConstants::COL_TP]) === 'employee'
          ? Employee::where(UsersConstants::COL_USER_ID, '!=', $user?->id)->get()->pluck('name', 'id')
          : Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get()->pluck('name', 'id');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('employees', 'currentEmployee'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function store(Request $request): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      try {
        $user = $req->user();
        if (!$user?->can('create complaint')) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
        $rules = ['complaint_against' => 'required', 'title' => 'required', 'complaint_date' => 'required'];
        if ($user->type != 'Employee') $rules['complaint_from'] = 'required';
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) return redirect()->back()->with('error', $validator->getMessageBag()->first());
        $complaint = new Complaint();
        $complaint->complaint_from = strtolower($user[UsersConstants::COL_TP]) === 'employee' ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first()->id : $req->complaint_from;
        $complaint->complaint_against = $req->complaint_against;
        $complaint->title = $req->title;
        $complaint->complaint_date = $req->complaint_date;
        $complaint->description = $req->description;
        $complaint->created_by = $user?->creatorId();
        $complaint->save();
        try {
          $settings = Utility::settings();
          if ($settings['complaint_resent'] ?? false) {
            $emp = Employee::find($complaint->complaint_against);
            $payload = ['complaint_name' => $emp->name, 'complaint_title' => $complaint->title, 'complaint_against' => $complaint->complaint_against, 'complaint_date' => $complaint->complaint_date, 'complaint_description' => $complaint->description];
            $resp = Utility::sendEmailTemplate('complaint_resent', [$emp->id => $emp->email], $payload);
            $errorHtml = !$resp['is_success'] && !empty($resp['error']) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : '';
            return redirect()->route(ViewsConstants::CPL . '.index')->with('success', __('Complaint successfully created.') . $errorHtml);
          }
        } catch (\Throwable $e) {
          Log::error("[{$class}::{$action}] email error", ['error' => $e]);
        }
        return redirect()->route(ViewsConstants::CPL . '.index')->with('success', __('Complaint successfully created.'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class]);
  }

  public function show(Request $request, Complaint $complaint): View|Response
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::CPL . '.show';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $complaint, $action, $class, $viewPath) {
      try {
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('complaint'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'complaint_id' => $complaint->id]);
  }

  public function edit(Request $request, Complaint $complaint): View|Response|JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $viewPath = ViewsConstants::CPL . '.edit';
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $complaint, $action, $class, $viewPath) {
      try {
        $user = $req->user();
        if (!$user?->can('edit complaint') || $complaint->created_by !== $user?->creatorId()) return response()->json(['error' => __('Permission denied.')], Response::HTTP_UNAUTHORIZED);
        $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->get()->pluck('name', 'id');
        $employees = strtolower($user[UsersConstants::COL_TP]) === 'employee' ? Employee::where(UsersConstants::COL_USER_ID, '!=', $user?->id)->get()->pluck('name', 'id') : Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get()->pluck('name', 'id');
        if (!ViewFacade::exists($viewPath)) return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
        return view($viewPath, compact('complaint', 'employees', 'currentEmployee'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'complaint_id' => $complaint->id]);
  }

  public function update(Request $request, Complaint $complaint): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $complaint, $action, $class) {
      try {
        $user = $req->user();
        if (!$user?->can('edit complaint') || $complaint->created_by !== $user?->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
        $rules = ['complaint_against' => 'required', 'title' => 'required', 'complaint_date' => 'required'];
        if ($user->type != 'Employee') $rules['complaint_from'] = 'required';
        $valStart = microtime(true);
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) return redirect()->back()->with('error', $validator->getMessageBag()->first());
        $this->logExecutionTime($valStart, $action, 'validateUpdate');
        $complaint->complaint_from = strtolower($user[UsersConstants::COL_TP]) === 'employee' ? Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first()->id : $req->complaint_from;
        $complaint->complaint_against = $req->complaint_against;
        $complaint->title = $req->title;
        $complaint->complaint_date = $req->complaint_date;
        $complaint->description = $req->description;
        $saveStart = microtime(true);
        $complaint->save();
        $this->logExecutionTime($saveStart, $action, 'saveComplaint');
        return redirect()->route(ViewsConstants::CPL . '.index')->with('success', __('Complaint successfully updated.'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'complaint_id' => $complaint->id]);
  }

  public function destroy(Request $request, Complaint $complaint): RedirectResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $complaint, $action, $class) {
      try {
        $user = $req->user();
        if (!$user?->can('delete complaint') || $complaint->created_by !== $user?->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action);
        $delStart = microtime(true);
        $complaint->delete();
        $this->logExecutionTime($delStart, $action, 'deleteComplaint');
        return redirect()->route(ViewsConstants::CPL . '.index')->with('success', __('Complaint successfully deleted.'));
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return defaultUndefinedException($req, $e, $class . '::' . $action);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'complaint_id' => $complaint->id]);
  }

  public const GET_EMP = 'getEmployee';
  public function getEmployee(Request $request): JsonResponse
  {
    $action = __FUNCTION__;
    $method = __METHOD__;
    $class = static::class;
    $req = $request;
    return $this->measureProfile($action, function () use ($req, $action, $class) {
      try {
        $employees = Employee::where('branch_id', $req->branch_id)->get();
        return response()->json([strtolower(class_basename(Employee::class)) => $employees]);
      } catch (\Throwable $e) {
        Log::error("[{$class}::{$action}] unexpected error", ['error' => $e]);
        return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $class, 'branch_id' => $request->branch_id]);
  }
}
