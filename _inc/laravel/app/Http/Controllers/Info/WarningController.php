<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{Employee, Utility, Warning};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class WarningController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::WRN . '.index';

    public function index(Request $request): Renderable|RedirectResponse
    {
        $cls = __CLASS__;
        $fn  = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::WRN . '.index';

        return $this->measureProfile($action, function () use ($request, $cls, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $guard = $this->guard($request, PermissionsConstants::MNG_WRN, self::ROUTE_INDEX);
                if ($guard !== true) return $guard;

                $user = $request->user();
                if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                    $emp = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first();
                    $warnings = Warning::where('warning_by', $emp?->id ?? 0)
                        ->with('warningTo')
                        ->get();
                } else {
                    $warnings = Warning::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
                        ->with('warningTo')
                        ->get();
                }

                Log::info($action . ' fetched warnings', [
                    UsersConstants::COL_USER_ID => $user?->id,
                    'count' => $warnings->count(),
                ]);

                if (!ViewFacade::exists($view))
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::ROUTE_INDEX));

                return view($view, compact('warnings'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }

    public function create(Request $request): Renderable|RedirectResponse
    {
        $cls = __CLASS__;
        $fn  = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::WRN . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $guard = $this->guard($request, 'create warning', self::ROUTE_INDEX);
            if ($guard !== true) return $guard;

            $user = $request->user();
            if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
                $employees = Employee::where(UsersConstants::COL_USER_ID, '!=', $user?->id)->pluck('name', 'id');
            } else {
                $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
                $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            }

            if (!ViewFacade::exists($view))
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::ROUTE_INDEX));

            return view($view, compact('employees', 'currentEmployee'));
        });
    }

    public function show(Request $request, Warning $warning): Renderable|RedirectResponse
    {
        $cls = __CLASS__;
        $fn  = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::WRN . '.show';

        return $this->measureProfile($action, function () use ($request, $warning, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $guard = $this->guard($request, 'view warning', self::ROUTE_INDEX);
                if ($guard !== true) return $guard;

                if ($warning->created_by !== $request->user()->creatorId())
                    return defaultPermissionDenial($request, null, $action, route(self::ROUTE_INDEX));

                if (!ViewFacade::exists($view))
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::ROUTE_INDEX));

                return view($view, compact('warning'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn  = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $guard = $this->guard($request, 'create warning', self::ROUTE_INDEX);
                if ($guard !== true) return $guard;

                $rules = [
                    'warning_to'   => 'required|integer',
                    'subject'      => 'required|string|max:255',
                    'warning_date' => 'required|date',
                    'description'  => 'nullable|string',
                ];
                if ($request->user()->type !== 'Employee') $rules['warning_by'] = 'required|integer';

                $data = $request->validate($rules);

                DB::transaction(function () use ($data, $request, $action) {
                    $warning = new Warning();
                    $warning->warning_by = $request->user()->type === 'Employee'
                        ? Employee::where(UsersConstants::COL_USER_ID, $request->user()->id)->value('id')
                        : $data['warning_by'];
                    $warning->warning_to   = $data['warning_to'];
                    $warning->subject      = $data['subject'];
                    $warning->warning_date = $data['warning_date'];
                    $warning->description  = $data['description'] ?? '';
                    $warning->created_by   = $request->user()->creatorId();
                    $warning->save();
                    Log::info($action . ' created warning', ['id' => $warning->id]);
                });

                $settings = Utility::settings($request->user()->creatorId());
                if (!empty($settings['warning_sent'])) {
                    $employee = Employee::find($data['warning_to']);
                    $mailData = [
                        'employee_warning_name' => $employee->name ?? '',
                        'warning_subject'       => $data['subject'],
                        'warning_description'   => $data['description'] ?? '',
                    ];
                    $resp = Utility::sendEmailTemplate('warning_sent', [$employee->id => $employee->email], $mailData);
                    if (!($resp['is_success'] ?? false)) {
                        Log::warning($action . ' email failed', ['error' => $resp['error'] ?? 'unknown']);
                        session()->flash('error', $resp['error'] ?? __('Mail failed'));
                    }
                }

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Warning successfully created.'));
            } catch (ValidationException $ve) {
                Log::warning($action . ' validation failed', ['errors' => $ve->errors()]);
                return redirect()->back()->with('error', array_values($ve->errors())[0][0]);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }

    public function edit(Request $request, Warning $warning): Renderable|RedirectResponse
    {
        $cls = __CLASS__;
        $fn  = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::WRN . '.edit';

        return $this->measureProfile($action, function () use ($request, $warning, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $guard = $this->guard($request, 'edit warning', self::ROUTE_INDEX);
                if ($guard !== true) return $guard;

                if ($warning->created_by !== $request->user()->creatorId())
                    return defaultPermissionDenial($request, null, $action, route(self::ROUTE_INDEX));

                $user = $request->user();
                if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                    $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
                    $employees = Employee::where(UsersConstants::COL_USER_ID, '!=', $user?->id)->pluck('name', 'id');
                } else {
                    $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
                    $employees = Employee::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
                }

                if (!ViewFacade::exists($view))
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::ROUTE_INDEX));

                return view($view, compact('warning', 'employees', 'currentEmployee'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }

    public function update(Request $request, Warning $warning): RedirectResponse
    {
        $cls = __CLASS__;
        $fn  = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $warning, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $guard = $this->guard($request, 'edit warning', self::ROUTE_INDEX);
                if ($guard !== true) return $guard;

                if ($warning->created_by !== $request->user()->creatorId())
                    return defaultPermissionDenial($request, null, $action, route(self::ROUTE_INDEX));

                $rules = [
                    'warning_to'   => 'required|integer',
                    'subject'      => 'required|string|max:255',
                    'warning_date' => 'required|date',
                    'description'  => 'nullable|string',
                ];
                if ($request->user()->type !== 'Employee') $rules['warning_by'] = 'required|integer';

                $data = $request->validate($rules);

                DB::transaction(function () use ($data, $request, $warning, $action) {
                    $warning->warning_by = $request->user()->type === 'Employee'
                        ? Employee::where(UsersConstants::COL_USER_ID, $request->user()->id)->value('id')
                        : $data['warning_by'];
                    $warning->warning_to   = $data['warning_to'];
                    $warning->subject      = $data['subject'];
                    $warning->warning_date = $data['warning_date'];
                    $warning->description  = $data['description'] ?? '';
                    $warning->save();
                    Log::info($action . ' updated warning', ['id' => $warning->id]);
                });

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Warning successfully updated.'));
            } catch (ValidationException $ve) {
                Log::warning($action . ' validation failed', ['errors' => $ve->errors()]);
                return redirect()->back()->with('error', array_values($ve->errors())[0][0]);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }

    public function destroy(Request $request, Warning $warning): RedirectResponse
    {
        $cls = __CLASS__;
        $fn  = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $warning, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                $guard = $this->guard($request, 'delete warning', self::ROUTE_INDEX);
                if ($guard !== true) return $guard;

                if ($warning->created_by !== $request->user()->creatorId())
                    return defaultPermissionDenial($request, null, $action, route(self::ROUTE_INDEX));

                DB::transaction(function () use ($warning, $action) {
                    $warning->delete();
                    Log::info($action . ' deleted warning', ['id' => $warning->id]);
                });

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Warning successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        });
    }
}
