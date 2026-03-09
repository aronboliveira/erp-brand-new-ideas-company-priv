<?php

namespace App\Http\Controllers\Individuals;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{
  CompaniesConstants as CPC,
  DatabaseConstants as DC,
  PermissionsConstants as PMC,
  UsersConstants as UC,
  ViewsConstants as VW
};
use App\Imports\AttendanceImport;
use App\Models\{
  Branch,
  Department,
  Employee,
  EmployeeAttendance,
  IpRestrict,
  Template,
  User,
  Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\{
  Auth,
  DB,
  Log,
  Validator,
  View as ViewFacade
};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use function App\Http\Controllers\Helpers\{defaultUndefinedException};

final class EmployeeAttendanceController extends Controller
{
  use ChecksLogin, ChecksPermissions;
  private const REDIRECT_INDEX = '/';

  public function index(Request $req): View|RedirectResponse
  {
    $action = 'EmployeeAttendanceController@index';
    $view = VW::EMP_ATD . '.index';

    return $this->measureProfile($action, function () use ($req, $action, $view) {
      // login check
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      // guard
      $t = microtime(true);
      if (($c = self::guard($req, PMC::MNG_ATD, self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // dropdown data
      $t = microtime(true);
      $branches = Branch::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->pluck(CPC::COL_BRC_NM, 'id')->prepend('Select Branch', '');
      $departments = Department::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->pluck(CPC::COL_DEP_NM, 'id')->prepend('Select Department', '');
      $this->logExecutionTime($t, $action . '::loadFilters', 'branches:' . $branches->count() . ', departments:' . $departments->count());

      // query attendances
      $t = microtime(true);
      $query = EmployeeAttendance::query();
      if (!in_array($user[UC::COL_TP], [PMC::CL, PMC::CPN])) {
        $empId = $user?->employee->id ?? 0;
        $query->where(UC::COL_EMP_ID, $empId);
      } else {
        $empIds = Employee::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
          ->when($req->branch, fn($q) => $q->where(CPC::COL_BRC_ID, $req->branch))
          ->when($req->department, fn($q) => $q->where(CPC::COL_DEP_ID, $req->department))
          ->pluck('id');
        $query->whereIn(UC::COL_EMP_ID, $empIds);
      }

      $query->when($req->type === 'monthly' && $req->month, function ($q) use ($req) {
        [$m, $y] = [date('m', strtotime($req->month)), date('Y', strtotime($req->month))];
        $q->whereBetween('date', ["{$y}-{$m}-01", "{$y}-{$m}-t"]);
      })->when($req->type === 'daily' && $req->date, fn($q) => $q->where('date', $req->date))
        ->when(!$req->type || !in_array($req->type, ['daily', 'monthly']), function ($q) {
          $m = date('m');
          $y = date('Y');
          $q->whereBetween('date', ["{$y}-{$m}-01", "{$y}-{$m}-t"]);
        });

      $attendances = $query->get();
      $this->logExecutionTime($t, $action . '::query', 'rows: ' . $attendances->count());

      // view check
      $t = microtime(true);
      if (!ViewFacade::exists($view)) {
        $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
        return defaultUndefinedException($req, new \RuntimeException("View not found: $view"), $action);
      }
      $this->logExecutionTime($t, $action . '::viewCheck', 'exists');

      return view($view, compact('attendances', 'branches', 'departments'));
    }, ['uri' => $req->getRequestUri()]);
  }

  public function create(Request $req): View|RedirectResponse
  {
    $action = 'EmployeeAttendanceController@create';
    $view = VW::EMP_ATD . '.create';

    return $this->measureProfile($action, function () use ($req, $action, $view) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      $t = microtime(true);
      if (($c = self::guard($req, PMC::CR_ATD, self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // load employees
      $t = microtime(true);
      $employees = User::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->where(UC::COL_TP, 'employee')
        ->pluck(UC::COL_NM, 'id');
      $this->logExecutionTime($t, $action . '::loadEmployees', 'count: ' . $employees->count());

      // view check
      $t = microtime(true);
      if (!ViewFacade::exists($view)) {
        $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
        return defaultUndefinedException($req, new \RuntimeException("View not found: $view"), $action);
      }
      $this->logExecutionTime($t, $action . '::viewCheck', 'exists');

      return view($view, compact('employees'));
    }, ['uri' => $req->getRequestUri()]);
  }

  public function store(Request $req): RedirectResponse|JsonResponse
  {
    $action = 'EmployeeAttendanceController@store';

    return $this->measureProfile($action, function () use ($req, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      // guard
      $t = microtime(true);
      if (($c = self::guard($req, PMC::CR_ATD, self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // validate
      $t = microtime(true);
      if ($c = self::v($req, [
        UC::COL_EMP_ID => 'required',
        'date' => 'required|date',
        'clock_in' => 'required',
        'clock_out' => 'required'
      ])) {
        $this->logExecutionTime($t, $action . '::validate', 'failed');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::validate', 'ok');

      try {
        // compute & persist
        $t = microtime(true);
        $start = Utility::getValByName('company_start_time');
        $end = Utility::getValByName('company_end_time');

        $exists = EmployeeAttendance::where([
          [UC::COL_EMP_ID, $req[UC::COL_EMP_ID]],
          ['date', $req->date],
          ['clock_out', '00:00:00']
        ])->exists();
        if ($exists) {
          $this->logExecutionTime($t, $action . '::precheck', 'duplicate');
          return redirect()->route(VW::EMP_ATD . '.index')
            ->with('error', __('Employee Attendance Already Created.'));
        }

        $lateSecs = strtotime($req->clock_in) - strtotime("{$req->date}{$start}");
        $late = gmdate('H:i:s', max($lateSecs, 0));
        $earlySecs = strtotime("{$req->date}{$end}") - strtotime($req->clock_out);
        $early = gmdate('H:i:s', max($earlySecs, 0));
        $overtime = strtotime($req->clock_out) > strtotime("{$req->date}{$end}")
          ? gmdate('H:i:s', strtotime($req->clock_out) - strtotime("{$req->date}{$end}"))
          : '00:00:00';

        EmployeeAttendance::create([
          UC::COL_EMP_ID => $req[UC::COL_EMP_ID],
          'date' => $req->date,
          'status' => 'Present',
          'clock_in' => "{$req->clock_in}:00",
          'clock_out' => "{$req->clock_out}:00",
          'late' => $late,
          'early_leaving' => $early,
          'overtime' => $overtime,
          'total_rest' => '00:00:00',
          DC::COL_TABLE_CREATOR => $user?->creatorId(),
        ]);
        $this->logExecutionTime($t, $action . '::persist', 'created');

        return redirect()->route(VW::EMP_ATD . '.index')
          ->with('success', __('Employee attendance successfully created.'));
      } catch (\Throwable $e) {
        Log::error($action . ' ' . $e->getMessage());
        return defaultUndefinedException($req, $e, $action);
      }
    }, ['uri' => $req->getRequestUri()]);
  }

  public function show(): RedirectResponse
  {
    // simple redirect; no profiling necessary
    return redirect()->route(VW::EMP_ATD . '.index');
  }

  public function edit(Request $req, int|string $id): View|RedirectResponse
  {
    $action = 'EmployeeAttendanceController@edit';
    $view = VW::EMP_ATD . '.edit';

    return $this->measureProfile($action, function () use ($req, $id, $action, $view) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      // guard
      $t = microtime(true);
      if (($c = self::guard($req, 'edit attendance', self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // load data
      $t = microtime(true);
      $attendance = EmployeeAttendance::findOrFail($id);
      $employees = Employee::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->pluck(UC::COL_NM, 'id');
      $this->logExecutionTime($t, $action . '::loadData', 'employees: ' . $employees->count());

      // view check
      $t = microtime(true);
      if (!ViewFacade::exists($view)) {
        $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
        return defaultUndefinedException($req, new \RuntimeException("View not found: $view"), $action);
      }
      $this->logExecutionTime($t, $action . '::viewCheck', 'exists');

      return view($view, compact('attendance', 'employees'));
    }, ['uri' => $req->getRequestUri(), 'id' => $id]);
  }

  public function update(Request $req, int|string $id): RedirectResponse|JsonResponse
  {
    $action = 'EmployeeAttendanceController@update';

    return $this->measureProfile($action, function () use ($req, $id, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

      // guard
      $t = microtime(true);
      if (($c = self::guard($req, 'edit attendance', self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // compute & persist
      $t = microtime(true);
      $attendance = EmployeeAttendance::findOrFail($id);
      $inRaw = $req->clock_in;
      $outRaw = $req->clock_out;
      $date = $attendance->date;
      $start = Utility::getValByName('company_start_time');
      $end = Utility::getValByName('company_end_time');

      $in = $inRaw ? date('H:i:s', strtotime($inRaw)) : $attendance->clock_in;
      $out = $outRaw ? date('H:i:s', strtotime($outRaw)) : $attendance->clock_out;

      ['late' => $late, 'earlyLeaving' => $early, 'overtime' => $ovt]
        = self::computeDurations($in, $out, $date, $start, $end);

      $attendance->clock_in = $in;
      $attendance->clock_out = $out;
      $attendance->late = $late;
      $attendance->early_leaving = $early;
      $attendance->overtime = $ovt;
      $attendance->save();
      $this->logExecutionTime($t, $action . '::persist', 'updated');

      return redirect()->route(VW::EMP_ATD . '.index')
        ->with('success', __('Employee attendance successfully updated.'));
    }, ['uri' => $req->getRequestUri(), 'id' => $id]);
  }

  public function destroy(int|string $id): RedirectResponse|JsonResponse
  {
    $action = 'EmployeeAttendanceController@destroy';

    return $this->measureProfile($action, function () use ($id, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

      $t = microtime(true);
      if (($c = self::guard(request(), 'delete attendance', self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      $t = microtime(true);
      EmployeeAttendance::whereKey($id)->delete();
      $this->logExecutionTime($t, $action . '::delete', 'id: ' . $id);

      return redirect()->route(VW::EMP_ATD . '.index')
        ->with('success', __('Attendance successfully deleted.'));
    }, ['id' => $id]);
  }

  public function attendance(Request $req): RedirectResponse|JsonResponse
  {
    $action = 'EmployeeAttendanceController@attendance';

    return $this->measureProfile($action, function () use ($req, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      $t = microtime(true);
      $settings = Utility::settings();
      $this->logExecutionTime($t, $action . '::loadSettings', 'ok');

      // IP restriction check
      $t = microtime(true);
      if (
        $settings['ip_restrict'] === 'on' &&
        IpRestrict::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->where('ip', request()->ip())->exists()
      ) {
        $this->logExecutionTime($t, $action . '::ipCheck', 'blocked');
        return redirect()->back()->with('error', __('This ip is not allowed to clock in & clock out.'));
      }
      $this->logExecutionTime($t, $action . '::ipCheck', 'ok');

      try {
        // close previous open attendance
        $t = microtime(true);
        $start = Utility::getValByName('company_start_time');
        $end = Utility::getValByName('company_end_time');
        $empId = $user?->employee->id ?? 0;

        $last = EmployeeAttendance::where([
          [UC::COL_EMP_ID, $empId],
          ['clock_out', '00:00:00']
        ])->latest('id')->first();
        if ($last) $last->update(['clock_out' => $end]);

        $date = date('Y-m-d');
        $time = date('H:i:s');
        $late = gmdate('H:i:s', max(time() - strtotime("{$date}{$start}"), 0));

        EmployeeAttendance::create([
          UC::COL_EMP_ID => $empId,
          'date' => $date,
          'status' => 'Present',
          'clock_in' => $time,
          'clock_out' => '00:00:00',
          'late' => $late,
          'early_leaving' => '00:00:00',
          'overtime' => '00:00:00',
          'total_rest' => '00:00:00',
          DC::COL_TABLE_CREATOR => $user?->id,
        ]);
        $this->logExecutionTime($t, $action . '::persist', 'clock-in');

        return redirect()->back()->with('success', __('Employee Successfully Clock In.'));
      } catch (\Throwable $e) {
        Log::error($action . ' ' . $e->getMessage());
        return defaultUndefinedException($req, $e, $action);
      }
    }, ['uri' => $req->getRequestUri()]);
  }

  public const BK_ATD = 'bulkAttendance';
  public function bulkAttendance(Request $req): View|RedirectResponse
  {
    $action = 'EmployeeAttendanceController@bulkAttendance';
    // The bulk-attendance view lives under resources/views/attendances/,
    // not under employee_attendances/ (which only has index, create, import).
    $view = VW::ATD . '.bulk';

    return $this->measureProfile($action, function () use ($req, $action, $view) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      // guard
      $t = microtime(true);
      if (($c = self::guard($req, PMC::CR_ATD, self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // load data
      $t = microtime(true);
      $branch = Branch::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->pluck(CPC::COL_BRC_NM, 'id')->prepend('Select Branch', '');
      $department = Department::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->pluck(CPC::COL_DEP_NM, 'id')->prepend('Select Department', '');
      $employees = Employee::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
        ->when($req->branch, fn($q) => $q->where(CPC::COL_BRC_ID, $req->branch))
        ->when($req->department, fn($q) => $q->where(CPC::COL_DEP_ID, $req->department))
        ->get();
      $this->logExecutionTime($t, $action . '::loadData', 'employees: ' . $employees->count());

      // view check
      $t = microtime(true);
      if (!ViewFacade::exists($view)) {
        $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
        return defaultUndefinedException($req, new \RuntimeException("View not found: $view"), $action);
      }
      $this->logExecutionTime($t, $action . '::viewCheck', 'exists');

      return view($view, compact('employees', 'branch', 'department'));
    }, ['uri' => $req->getRequestUri()]);
  }

  public const BK_ATD_DT = 'bulkAttendanceData';
  public const IDX = 'index';
  public const CRT = 'create';
  public const STR = 'store';
  public const SHW = 'show';
  public const EDT = 'edit';
  public const UPD = 'update';
  public const DEL = 'destroy';

  public function bulkAttendanceData(Request $req): RedirectResponse
  {
    $action = 'EmployeeAttendanceController@bulkAttendanceData';

    return $this->measureProfile($action, function () use ($req, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      // guard
      $t = microtime(true);
      if (($c = self::guard($req, PMC::CR_ATD, self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // process rows
      $t = microtime(true);
      $start = Utility::getValByName('company_start_time');
      $end = Utility::getValByName('company_end_time');

      foreach ($req[UC::COL_EMP_ID] as $emp) {
        $present = $req->input("present-{$emp}") === 'on';
        $date = $req->date;

        if ($present) {
          $in = date('H:i:s', strtotime($req->input("in-{$emp}")));
          $out = date('H:i:s', strtotime($req->input("out-{$emp}")));
          ['late' => $late, 'earlyLeaving' => $early, 'overtime' => $ovt]
            = self::computeDurations($in, $out, $date, $start, $end);
          $status = 'Present';
        } else {
          $in = $out = $late = $early = $ovt = '00:00:00';
          $status = 'Leave';
        }

        $attendance = EmployeeAttendance::where([
          [UC::COL_EMP_ID, $emp],
          ['date', $date],
        ])->first() ?? new EmployeeAttendance();

        $attendance[UC::COL_EMP_ID] = $emp;
        $attendance->date = $date;
        $attendance->status = $status;
        $attendance->clock_in = $in;
        $attendance->clock_out = $out;
        $attendance->late = $late;
        $attendance->early_leaving = $early;
        $attendance->overtime = $ovt;
        $attendance->total_rest = '00:00:00';
        $attendance->created_by = $user?->creatorId();
        $attendance->save();
      }
      $this->logExecutionTime($t, $action . '::persist', 'bulk-done');

      return redirect()->back()->with('success', __('Employee attendance successfully created.'));
    }, ['uri' => $req->getRequestUri()]);
  }

  public function importFile(): View
  {
    $action = 'EmployeeAttendanceController@importFile';
    $view = VW::EMP_ATD . '.import';

    // this one is trivial, but we still validate the view and profile it for consistency
    return $this->measureProfile($action, function () use ($view, $action) {
      $t = microtime(true);
      if (!ViewFacade::exists($view)) {
        $this->logExecutionTime($t, $action . '::viewCheck', 'missing');
        // there's no Request here; provide a minimal exception
        throw new \RuntimeException("View not found: $view");
      }
      $this->logExecutionTime($t, $action . '::viewCheck', 'exists');
      return view($view);
    }, []);
  }

  public function import(Request $req): RedirectResponse
  {
    $action = 'EmployeeAttendanceController@import';

    return $this->measureProfile($action, function () use ($req, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $user = $userOrRedirect;

      // guard
      $t = microtime(true);
      if (($c = self::guard($req, PMC::CR_ATD, self::REDIRECT_INDEX)) !== true) {
        $this->logExecutionTime($t, $action . '::guard', 'redirect');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::guard', 'ok');

      // validate file
      $t = microtime(true);
      if ($c = self::v($req, ['file' => 'required|mimes:csv,txt,xlsx'])) {
        $this->logExecutionTime($t, $action . '::validate', 'failed');
        return $c;
      }
      $this->logExecutionTime($t, $action . '::validate', 'ok');

      try {
        // parse + persist
        $t = microtime(true);
        $rows = (new AttendanceImport())->toArray($req->file('file'))[0];
        $errors = [];
        $start = Utility::getValByName('company_start_time');
        $end = Utility::getValByName('company_end_time');

        foreach ($rows as $i => $row) if ($i) {
          [$email, $date, $inRaw, $outRaw] = $row;
          $emp = Employee::where('email', $email)
            ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
            ->first();
          if (!$emp) {
            $errors[] = $email;
            continue;
          }
          $in = date('H:i:s', strtotime($inRaw));
          $out = date('H:i:s', strtotime($outRaw));
          ['late' => $late, 'earlyLeaving' => $early, 'overtime' => $ovt]
            = self::computeDurations($in, $out, $date, $start, $end);

          $attendance = EmployeeAttendance::where([
            [UC::COL_EMP_ID, $emp->id],
            ['date', $date],
          ])->first() ?? new EmployeeAttendance();

          $attendance[UC::COL_EMP_ID] = $emp->id;
          $attendance->date = $date;
          $attendance->status = 'Present';
          $attendance->clock_in = $in;
          $attendance->clock_out = $out;
          $attendance->late = $late;
          $attendance->early_leaving = $early;
          $attendance->overtime = $ovt;
          $attendance->total_rest = '00:00:00';
          $attendance->created_by = $user?->creatorId();
          $attendance->save();
        }

        $this->logExecutionTime($t, $action . '::persist', 'imported');

        if ($errors) {
          return redirect()->back()
            ->with('error', __('These records failed: ') . implode(',', $errors));
        }
        return redirect()->back()->with('success', __('Record successfully imported'));
      } catch (\Throwable $e) {
        Log::error($action . ' ' . $e->getMessage());
        return defaultUndefinedException($req, $e, $action);
      }
    }, ['uri' => $req->getRequestUri()]);
  }

  private static function v(Request $req, array $rules): ?RedirectResponse
  {
    $v = Validator::make($req->all(), $rules);
    return $v->fails()
      ? redirect()->back()->with('error', $v->getMessageBag()->first())
      : null;
  }

  /**
   * @param string $in
   * @param string $out
   * @param string $date
   * @param string $start
   * @param string $end
   * @return array{late:string,earlyLeaving:string,overtime:string}
   */
  private static function computeDurations(
    string $in,
    string $out,
    string $date,
    string $start,
    string $end
  ): array {
    $lateSecs = strtotime("{$date}{$in}") - strtotime("{$date}{$start}");
    $late = gmdate('H:i:s', max($lateSecs, 0));
    $earlySecs = strtotime("{$date}{$end}") - strtotime("{$date}{$out}");
    $early = gmdate('H:i:s', max($earlySecs, 0));
    $overtime = strtotime("{$date}{$out}") > strtotime("{$date}{$end}")
      ? gmdate('H:i:s', strtotime("{$date}{$out}") - strtotime("{$date}{$end}"))
      : '00:00:00';
    return ['late' => $late, 'earlyLeaving' => $early, 'overtime' => $overtime];
  }
}
