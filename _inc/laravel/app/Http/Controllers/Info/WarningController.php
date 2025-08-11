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
use Illuminate\Support\Facades\{DB, Log};
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class WarningController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::WRN . '.index';

    public function index(Request $request): Renderable|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, PermissionsConstants::MNG_WRN, self::ROUTE_INDEX);
            $user = $request->user();
            if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                $emp = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->first();
                $warnings = Warning::where('warning_by', $emp->id)
                    ->with('warningTo')
                    ->get();
            } else {
                $warnings = Warning::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
                    ->with('warningTo')
                    ->get();
            }
            Log::info(__METHOD__ . ' fetched warnings', [
                UsersConstants::COL_USER_ID   => $user?->id,
                'count'     => $warnings->count(),
            ]);

            return view(ViewsConstants::WRN . '.' . __FUNCTION__, compact('warnings'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function create(Request $request): Renderable|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $this->guard($request, 'create warning', self::ROUTE_INDEX);

        $user = $request->user();
        if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
            $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
            $employees = Employee::where(UsersConstants::COL_USER_ID, '!=', $user?->id)->pluck('name', 'id');
        } else {
            $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
            $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
        }

        return view(ViewsConstants::WRN . '.create', compact('employees', 'currentEmployee'));
    }

    public function show(Request $request, Warning $warning): Renderable|RedirectResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof RedirectResponse
            ) return $userOrRedirect;
            $this->guard($request, 'view warning', self::ROUTE_INDEX);
            if ($warning->created_by !== $request->user()->creatorId())
                return defaultPermissionDenial(
                    $request,
                    null,
                    __CLASS__ . '::' . __FUNCTION__,
                    route(self::ROUTE_INDEX)
                );
            return view(ViewsConstants::WRN . '.show', compact('warning'));
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' failed',
                ['exception' => $e]
            );
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'create warning', self::ROUTE_INDEX);

            $rules = [
                'warning_to'   => 'required|integer',
                'subject'      => 'required|string|max:255',
                'warning_date' => 'required|date',
                'description'  => 'nullable|string',
            ];
            if ($request->user()->type !== 'Employee') {
                $rules['warning_by'] = 'required|integer';
            }

            $data = $request->validate($rules);

            DB::transaction(function () use ($data, $request) {
                $warning = new Warning();
                $warning->warning_by = $request->user()->type === 'Employee'
                    ? Employee::where(UsersConstants::COL_USER_ID, $request->user()->id)->value('id')
                    : $data['warning_by'];
                $warning->warning_to  = $data['warning_to'];
                $warning->subject     = $data['subject'];
                $warning->warning_date = $data['warning_date'];
                $warning->description = $data['description'] ?? '';
                $warning->created_by  = $request->user()->creatorId();
                $warning->save();
                Log::info(__METHOD__ . ' created warning', ['id' => $warning->id]);
            });

            $settings = Utility::settings($request->user()->creatorId());
            if (!empty($settings['warning_sent'])) {
                $employee = Employee::find($data['warning_to']);
                $mailData = [
                    'employee_warning_name' => $employee->name,
                    'warning_subject'       => $data['subject'],
                    'warning_description'   => $data['description'] ?? '',
                ];
                $resp = Utility::sendEmailTemplate(
                    'warning_sent',
                    [$employee->id => $employee->email],
                    $mailData
                );
                if (!$resp['is_success']) {
                    Log::warning(__METHOD__ . ' email failed', ['error' => $resp['error']]);
                    session()->flash('error', $resp['error']);
                }
            }

            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Warning successfully created.'));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $ve->errors()]);
            return redirect()->back()
                ->with('error', array_values($ve->errors())[0][0]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function edit(Request $request, Warning $warning): Renderable|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'edit warning', self::ROUTE_INDEX);
            if ($warning->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));
            }

            $user = $request->user();
            if (strtolower($user[UsersConstants::COL_TP]) === 'employee') {
                $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
                $employees = Employee::where(UsersConstants::COL_USER_ID, '!=', $user?->id)->pluck('name', 'id');
            } else {
                $currentEmployee = Employee::where(UsersConstants::COL_USER_ID, $user?->id)->pluck('name', 'id');
                $employees = Employee::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->pluck('name', 'id');
            }
            return view(ViewsConstants::WRN . '.' . __FUNCTION__, compact(
                'warning',
                'employees',
                'currentEmployee'
            ));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function update(Request $request, Warning $warning): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'edit warning', self::ROUTE_INDEX);
            if ($warning->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));
            }

            $rules = [
                'warning_to'   => 'required|integer',
                'subject'      => 'required|string|max:255',
                'warning_date' => 'required|date',
                'description'  => 'nullable|string',
            ];
            if ($request->user()->type !== 'Employee') {
                $rules['warning_by'] = 'required|integer';
            }
            $data = $request->validate($rules);

            DB::transaction(function () use ($data, $request, $warning) {
                $warning->warning_by = $request->user()->type === 'Employee'
                    ? Employee::where(UsersConstants::COL_USER_ID, $request->user()->id)->value('id')
                    : $data['warning_by'];
                $warning->warning_to  = $data['warning_to'];
                $warning->subject     = $data['subject'];
                $warning->warning_date = $data['warning_date'];
                $warning->description = $data['description'] ?? '';
                $warning->save();
                Log::info(__METHOD__ . ' updated warning', ['id' => $warning->id]);
            });

            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Warning successfully updated.'));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $ve->errors()]);
            return redirect()->back()
                ->with('error', array_values($ve->errors())[0][0]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function destroy(Request $request, Warning $warning): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'delete warning', self::ROUTE_INDEX);
            if ($warning->created_by !== $request->user()->creatorId()) {
                return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));
            }

            DB::transaction(function () use ($warning) {
                $warning->delete();
                Log::info(__METHOD__ . ' deleted warning', ['id' => $warning->id]);
            });

            return redirect()->route(self::ROUTE_INDEX)
                ->with('success', __('Warning successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }
}
