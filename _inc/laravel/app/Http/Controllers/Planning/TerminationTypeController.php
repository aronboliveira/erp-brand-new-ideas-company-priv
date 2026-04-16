<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Abstracts\Controller;

use App\Config\Constants\{DatabaseConstants as DC, ViewsConstants as VW};
use App\Models\TerminationType;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\DefinesResourceActions;
class TerminationTypeController extends Controller
{
	use DefinesResourceActions;

    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = 'terminationtype.index';
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
        $view   = VW::TMN_TP . '.index';

        return $this->measureProfile($action, function () use ($request, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'manage termination type', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            $terminationtypes = TerminationType::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('terminationtypes'));
        });
    }

    public function show(Request $request, TerminationType $terminationType): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN_TP . '.show';

        return $this->measureProfile($action, function () use ($request, $terminationType, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'manage termination type', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($terminationType->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('Ownership mismatch'), $action, route(self::REDIRECT_INDEX));
            }

            try {
                if (!ViewFacade::exists($view)) {
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
                }
                return view($view, compact('terminationType'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function create(Request $request): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN_TP . '.create';

        return $this->measureProfile($action, function () use ($request, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;

            if (($redirect = self::guard($request, 'create termination type', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view);
        });
    }

    public function store(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'create termination type', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            try {
                $data = $request->validate([
                    'name' => 'required|max:20',
                ]);

                return DB::transaction(function () use ($data, $user) {
                    $terminationType = new TerminationType();
                    $terminationType->name       = $data['name'];
                    $terminationType->created_by = $user?->creatorId();
                    $terminationType->save();

                    return redirect()
                        ->route(self::REDIRECT_INDEX)
                        ->with('success', __('Termination type successfully created.'));
                });
            } catch (\Throwable $e) {
                Log::error($action . ' failed: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function edit(Request $request, TerminationType $terminationType): View|RedirectResponse|null
    {
        $action = __METHOD__;
        $view   = VW::TMN_TP . '.edit';

        return $this->measureProfile($action, function () use ($request, $terminationType, $action, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'edit termination type', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($terminationType->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('Ownership mismatch'), $action, route(self::REDIRECT_INDEX));
            }

            if (!ViewFacade::exists($view)) {
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_INDEX));
            }

            return view($view, compact('terminationType'));
        });
    }

    public function update(Request $request, TerminationType $terminationType): RedirectResponse|JsonResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $terminationType, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'edit termination type', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($terminationType->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('Ownership mismatch'), $action, route(self::REDIRECT_INDEX));
            }

            try {
                $data = $request->validate([
                    'name' => 'required|max:20',
                ]);

                DB::transaction(function () use ($data, $terminationType) {
                    $terminationType->name = $data['name'];
                    $terminationType->save();
                });

                return redirect()
                    ->route(self::REDIRECT_INDEX)
                    ->with('success', __('Termination type successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }

    public function destroy(Request $request, TerminationType $terminationType): RedirectResponse|JsonResponse|null
    {
        $action = __METHOD__;

        return $this->measureProfile($action, function () use ($request, $terminationType, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            if (($redirect = self::guard($request, 'delete termination type', self::REDIRECT_INDEX)) !== true) {
                return $redirect;
            }

            if ($terminationType->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('Ownership mismatch'), $action, route(self::REDIRECT_INDEX));
            }

            try {
                DB::transaction(function () use ($terminationType) {
                    $terminationType->delete();
                });

                return redirect()
                    ->route(self::REDIRECT_INDEX)
                    ->with('success', __('Termination type successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed: ' . $e->getMessage());
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
            }
        });
    }
}
