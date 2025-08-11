<?php

namespace App\Http\Controllers;

use App\Models\{Employee, Overtime};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Log, Validator};

class OvertimeController extends Controller
{
  private const PERM_CREATE = 'create overtime';
  private const PERM_DELETE = 'delete overtime';
  private const PERM_EDIT = 'edit overtime';
  private const PERM_MANAGE = 'manage overtime';

  /** @return \Illuminate\View\View|RedirectResponse|JsonResponse */
  public function index(Request $request)
  {
    if (!self::authorizePerm($request, self::PERM_MANAGE)) return redirect()->back();
    $overtimes = Overtime::where('created_by', $request->user()->creatorId())->get();
    return view('overtime.index', compact('overtimes'));
  }

  /** @return \Illuminate\View\View|RedirectResponse */
  public function overtimeCreate(int|string $id)
  {
    $employee = Employee::find($id);
    return view('overtime.create', compact('employee'));
  }

  /** @return RedirectResponse|JsonResponse|null */
  public function store(Request $request)
  {
    if (!self::authorizePerm($request, self::PERM_CREATE)) return redirect()->back();
    $rules = [
      'employee_id' => 'required',
      'title' => 'required',
      'number_of_days' => 'required',
      'hours' => 'required',
      'rate' => 'required'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return redirect()->back()
      ->with('error', $validator->getMessageBag()->first());
    try {
      $overtime = new Overtime();
      foreach (['employee_id', 'title', 'number_of_days', 'hours', 'rate'] as $k)
        $overtime->$k = $request->input($k);
      $overtime->created_by = $request->user()->creatorId();
      $overtime->save();
      return redirect()->back()->with('success', __('Overtime successfully created.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  /** @return RedirectResponse */
  public function show(Overtime $overtime)
  {
    return redirect()->route('commision.index');
  }

  /** @return \Illuminate\View\View|JsonResponse|RedirectResponse */
  public function edit(Request $request, int|string $overtime)
  {
    if (!self::authorizePerm($request, self::PERM_EDIT))
      return response()->json(['error' => __('Permission denied.')], 401);
    $ot = self::getOvertime($request, $overtime);
    if (!$ot) return response()->json(['error' => __('Permission denied.')], 401);
    return view('overtime.edit', compact('overtime'));
  }

  /** @return RedirectResponse|JsonResponse|null */
  public function update(Request $request, int|string $overtime)
  {
    if (!self::authorizePerm($request, self::PERM_EDIT)) return redirect()->back();
    $ot = self::getOvertime($request, $overtime);
    if (!$ot) return redirect()->back();
    $rules = ['title' => 'required', 'number_of_days' => 'required', 'hours' => 'required', 'rate' => 'required'];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return redirect()->back()
      ->with('error', $validator->getMessageBag()->first());
    try {
      foreach (['title', 'number_of_days', 'hours', 'rate'] as $k)
        $ot->$k = $request->input($k);
      $ot->save();
      return redirect()->back()->with('success', __('Overtime successfully updated.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  /** @return RedirectResponse */
  public function destroy(Request $request, Overtime $overtime)
  {
    if (!self::authorizePerm($request, self::PERM_DELETE)) return redirect()->back();
    if ($overtime->created_by !== $request->user()->creatorId())
      return defaultPermissionDenial(
        $request,
        new AuthorizationException(),
        __CLASS__ . '::' . __FUNCTION__
      );
    try {
      $overtime->delete();
      return redirect()->back()->with('success', __('Overtime successfully deleted.'));
    } catch (\Throwable $e) {
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  protected static function authorizePerm(Request $request, string $perm): bool
  {
    if ($request->user()->can($perm)) return true;
    defaultPermissionDenial(
      $request,
      new AuthorizationException(),
      __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
    );
    return false;
  }

  protected static function getOvertime(Request $request, int|string $id): ?Overtime
  {
    $ot = Overtime::find($id);
    if (!$ot || $ot->created_by !== $request->user()->creatorId()) {
      defaultPermissionDenial(
        $request,
        new AuthorizationException(),
        __CLASS__ . '::' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function']
      );
      return null;
    }
    return $ot;
  }
}
