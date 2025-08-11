<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  DatabaseConstants,
  PermissionsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Models\{Complaint, Employee, Utility};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Validator};
use Symfony\Component\HttpFoundation\Response;

class ComplaintController extends Controller
{
  public function index(Request $request): Response
  {
    try {
      $user = $request->user();
      if (!$user?->can(PermissionsConstants::MNG_CPT)) {
        return defaultPermissionDenial(
          $request,
          new \Illuminate\Auth\Access\AuthorizationException(),
          __CLASS__ . '::' . __FUNCTION__
        );
      }
      $complaints = strtolower($user[UsersConstants::COL_TP]) === 'employee'
        ? Complaint::where(
          'complaint_from',
          Employee::where('user_id', $user?->id)->first()->id
        )
        ->with(['complaintFrom'])
        ->get()
        : Complaint::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
        ->with(['complaintFrom'])
        ->get();
      return view(ViewsConstants::CPL . '.index', compact('complaints'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function create(Request $request): Response|JsonResponse
  {
    try {
      $user = $request->user();
      if (!$user?->can('create complaint')) {
        return response()->json(
          ['error' => __('Permission denied.')],
          Response::HTTP_UNAUTHORIZED
        );
      }
      $currentEmployee = Employee::where('user_id', $user?->id)
        ->get()
        ->pluck('name', 'id');
      $employees = strtolower($user[UsersConstants::COL_TP]) === 'employee'
        ? Employee::where('user_id', '!=', $user?->id)
        ->get()
        ->pluck('name', 'id')
        : Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
        ->get()
        ->pluck('name', 'id');
      return view(
        ViewsConstants::CPL . '.create',
        compact('employees', 'currentEmployee')
      );
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function store(Request $request): RedirectResponse
  {
    try {
      $user = $request->user();
      if (!$user?->can('create complaint')) {
        return defaultPermissionDenial(
          $request,
          new \Illuminate\Auth\Access\AuthorizationException(),
          __CLASS__ . '::' . __FUNCTION__
        );
      }
      $rules = [
        'complaint_against' => 'required',
        'title'             => 'required',
        'complaint_date'    => 'required'
      ];
      if ($user->type != 'Employee') {
        $rules['complaint_from'] = 'required';
      }
      $validator = Validator::make($request->all(), $rules);
      if ($validator->fails()) {
        return redirect()
          ->back()
          ->with('error', $validator->getMessageBag()->first());
      }
      $complaint = new Complaint();
      $complaint->complaint_from = strtolower($user[UsersConstants::COL_TP]) === 'employee'
        ? Employee::where('user_id', $user?->id)->first()->id
        : $request->complaint_from;
      $complaint->complaint_against = $request->complaint_against;
      $complaint->title            = $request->title;
      $complaint->complaint_date   = $request->complaint_date;
      $complaint->description      = $request->description;
      $complaint->created_by       = $user?->creatorId();
      $complaint->save();
      try {
        $settings = Utility::settings();
        if ($settings['complaint_resent'] ?? false) {
          $emp = Employee::find($complaint->complaint_against);
          $payload = [
            'complaint_name'        => $emp->name,
            'complaint_title'       => $complaint->title,
            'complaint_against'     => $complaint->complaint_against,
            'complaint_date'        => $complaint->complaint_date,
            'complaint_description' => $complaint->description
          ];
          $resp = Utility::sendEmailTemplate(
            'complaint_resent',
            [$emp->id => $emp->email],
            $payload
          );
          $errorHtml = !$resp['is_success'] && !empty($resp['error'])
            ? '<br> <span class="text-danger">' . $resp['error'] . '</span>'
            : '';
          return redirect()
            ->route(ViewsConstants::CPL . '.index')
            ->with('success', __('Complaint successfully created.') . $errorHtml);
        }
      } catch (\Throwable $e) {
        Log::error(__CLASS__ . '::' . __FUNCTION__ . ' email error', ['error' => $e]);
      }
      return redirect()
        ->route(ViewsConstants::CPL . '.index')
        ->with('success', __('Complaint successfully created.'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function show(Request $request, Complaint $complaint): Response
  {
    try {
      return view(ViewsConstants::CPL . '.show', compact('complaint'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function edit(Request $request, Complaint $complaint): Response|JsonResponse
  {
    try {
      $user = $request->user();
      if (
        !$user?->can('edit complaint') ||
        $complaint->created_by !== $user?->creatorId()
      ) {
        return response()->json(
          ['error' => __('Permission denied.')],
          Response::HTTP_UNAUTHORIZED
        );
      }
      $currentEmployee = Employee::where('user_id', $user?->id)
        ->get()
        ->pluck('name', 'id');
      $employees = strtolower($user[UsersConstants::COL_TP]) === 'employee'
        ? Employee::where('user_id', '!=', $user?->id)
        ->get()
        ->pluck('name', 'id')
        : Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
        ->get()
        ->pluck('name', 'id');
      return view(
        ViewsConstants::CPL . '.edit',
        compact('complaint', 'employees', 'currentEmployee')
      );
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function update(Request $request, Complaint $complaint): RedirectResponse
  {
    try {
      $user = $request->user();
      if (
        !$user?->can('edit complaint') ||
        $complaint->created_by !== $user?->creatorId()
      ) {
        return defaultPermissionDenial(
          $request,
          new \Illuminate\Auth\Access\AuthorizationException(),
          __CLASS__ . '::' . __FUNCTION__
        );
      }
      $rules = [
        'complaint_against' => 'required',
        'title'             => 'required',
        'complaint_date'    => 'required'
      ];
      if ($user->type != 'Employee') {
        $rules['complaint_from'] = 'required';
      }
      $validator = Validator::make($request->all(), $rules);
      if ($validator->fails()) {
        return redirect()
          ->back()
          ->with('error', $validator->getMessageBag()->first());
      }
      $complaint->complaint_from = strtolower($user[UsersConstants::COL_TP]) === 'employee'
        ? Employee::where('user_id', $user?->id)->first()->id
        : $request->complaint_from;
      $complaint->complaint_against = $request->complaint_against;
      $complaint->title            = $request->title;
      $complaint->complaint_date   = $request->complaint_date;
      $complaint->description      = $request->description;
      $complaint->save();
      return redirect()
        ->route(ViewsConstants::CPL . '.index')
        ->with('success', __('Complaint successfully updated.'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function destroy(Request $request, Complaint $complaint): RedirectResponse
  {
    try {
      $user = $request->user();
      if (
        !$user?->can('delete complaint') ||
        $complaint->created_by !== $user?->creatorId()
      ) {
        return defaultPermissionDenial(
          $request,
          new \Illuminate\Auth\Access\AuthorizationException(),
          __CLASS__ . '::' . __FUNCTION__
        );
      }
      $complaint->delete();
      return redirect()
        ->route(ViewsConstants::CPL . '.index')
        ->with('success', __('Complaint successfully deleted.'));
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function getEmployee(Request $request): JsonResponse
  {
    try {
      $employees = Employee::where('branch_id', $request->branch_id)->get();
      return response()->json(['employee' => $employees]);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . ' unexpected error', ['error' => $e]);
      return response()->json(
        ['error' => 'Server error'],
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }
}
