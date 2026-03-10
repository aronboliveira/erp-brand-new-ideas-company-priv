<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PMC, SettingsConstants as SC, UsersConstants as UC, ViewsConstants as VW};
use App\Models\{
    Employee,
    Termination,
    TerminationType,
    Utility
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Validator, View as ViewFacade};

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
class TerminationController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = VW::TMN . '.index';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, PMC::MNG_TRM, self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            Log::info("$action called", [UC::COL_USER_ID => $user?->id]);

            try {
                $creatorId = $user?->creatorId();
                $query = Termination::with(['termination_type', 'employee'])
                    ->where(DC::COL_TABLE_CREATOR, $creatorId);

                if (strtolower($user[UC::COL_TP]) === 'employee') {
                    $emp = Employee::where(UC::COL_USER_ID, $user?->id)->first();
                    $query->where(UC::COL_EMP_ID, $emp->id);
                }

                $terminations = $query->get();
                Log::debug("$action fetched", ['count' => $terminations->count()]);

                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                }

                return view($view, compact('terminations'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                Log::channel(SC::ERR_TRACE)->debug("$action failed", [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN . '.create';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'create termination', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            Log::info("$action called", [UC::COL_USER_ID => $user?->id]);

            $creatorId = $user?->creatorId();
            $employees = Employee::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
            $terminationtypes = TerminationType::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('employees', 'terminationtypes'));
        });
    }

    public function show(Request $request, Termination $termination): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN . '.show';

        return $this->measureProfile($action, function () use ($request, $termination, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'view termination', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($termination[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            }

            Log::info($action, ['terminationId' => $termination->id, UC::COL_USER_ID => $user?->id]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('termination'));
        });
    }

    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'create termination', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            Log::info("$action called", ['input' => $request->all()]);

            $validator = Validator::make($request->all(), [
                UC::COL_EMP_ID => 'required',
                'termination_type'         => 'required',
                'notice_date'              => 'required|date',
                'termination_date'         => 'required|date'
            ]);

            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("$action validation failed", ['message' => $msg]);
                return redirect()->back()->with('error', $msg);
            }

            try {
                DB::transaction(function () use ($request, $user) {
                    Termination::create([
                        UC::COL_EMP_ID => $request->employee_id,
                        'termination_type'         => $request->termination_type,
                        'notice_date'              => $request->notice_date,
                        'termination_date'         => $request->termination_date,
                        'description'              => $request->description,
                        DC::COL_TABLE_CREATOR => $user?->creatorId(),
                    ]);
                });

                Log::info("$action committed");

                $settings = Utility::settings();
                if (!empty($settings['termination_sent'])) {
                    $termination = Termination::latest()
                        ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                        ->first();

                    $emp = Employee::find($termination->employee_id);

                    $terminationArr = [
                        'termination_name'  => $emp->name,
                        'termination_email' => $emp->email,
                        'notice_date'       => $termination->notice_date,
                        'termination_date'  => $termination->termination_date,
                        'termination_type'  => TerminationType::find($termination->termination_type)->name,
                    ];

                    $resp = Utility::sendEmailTemplate(
                        'termination_sent',
                        [$emp->id => $emp->email],
                        $terminationArr
                    );

                    return redirect()
                        ->route(self::REDIRECT_INDEX)
                        ->with(
                            'success',
                            __('Termination successfully created.')
                                . (
                                    empty($resp['is_success']) && !empty($resp['error'])
                                    ? '<br><span class="text-danger">' . $resp['error'] . '</span>'
                                    : ''
                                )
                        );
                }

                return redirect()
                    ->route(self::REDIRECT_INDEX)
                    ->with('success', __('Termination successfully created.'));
            } catch (\Throwable $e) {
                Log::error("$action failed", ['error' => $e->getMessage()]);
                Log::channel(SC::ERR_TRACE)->debug("$action failed", [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, Termination $termination): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN . '.edit';

        return $this->measureProfile($action, function () use ($request, $termination, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'edit termination', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($termination[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            }

            Log::info("$action called", ['terminationId' => $termination->id]);

            $creatorId = $user?->creatorId();
            $employees = Employee::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');
            $terminationtypes = TerminationType::where(DC::COL_TABLE_CREATOR, $creatorId)->pluck('name', 'id');

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('termination', 'employees', 'terminationtypes'));
        });
    }

    public function update(Request $request, Termination $termination): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $termination, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'edit termination', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($termination[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            }

            Log::info("$action called", ['terminationId' => $termination->id, 'input' => $request->all()]);

            $validator = Validator::make($request->all(), [
                UC::COL_EMP_ID => 'required',
                'termination_type'         => 'required',
                'notice_date'              => 'required|date',
                'termination_date'         => 'required|date',
            ]);

            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->first();
                Log::warning("$action validation failed", ['message' => $msg]);
                return redirect()->back()->with('error', $msg);
            }

            try {
                DB::transaction(function () use ($request, $termination) {
                    $termination->update([
                        UC::COL_EMP_ID => $request->employee_id,
                        'termination_type'         => $request->termination_type,
                        'notice_date'              => $request->notice_date,
                        'termination_date'         => $request->termination_date,
                        'description'              => $request->description,
                    ]);
                });

                Log::info("$action committed", ['terminationId' => $termination->id]);

                return redirect()
                    ->route(self::REDIRECT_INDEX)
                    ->with('success', __('Termination successfully updated.'));
            } catch (\Throwable $e) {
                Log::channel(SC::ERR_TRACE)->debug("$action failed", ['error' => $e->getMessage()]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, Termination $termination): RedirectResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $termination, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'delete termination', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($termination[DC::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            }

            Log::info("$action called", ['terminationId' => $termination->id]);

            try {
                DB::transaction(fn() => $termination->delete());
                Log::info("$action committed", ['terminationId' => $termination->id]);

                return redirect()
                    ->route(self::REDIRECT_INDEX)
                    ->with('success', __('Termination successfully deleted.'));
            } catch (\Throwable $e) {
                Log::channel(SC::ERR_TRACE)->debug("$action failed", ['error' => $e->getMessage()]);
                Log::error("$action failed", ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function description(Request $request, string|int $id): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN . '.description';

        return $this->measureProfile($action, function () use ($request, $id, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            if (($redirect = self::guard($request, PMC::MNG_TRM, self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            $termination = Termination::findOrFail($id);
            Log::info("$action called", ['terminationId' => $id]);

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('termination'));
        });
    }
}
