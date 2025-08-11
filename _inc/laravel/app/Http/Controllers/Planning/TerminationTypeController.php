<?php

namespace App\Http\Controllers;

use App\Config\Constants\DatabaseConstants;
use App\Models\TerminationType;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\View\View;

class TerminationTypeController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    private const REDIRECT_INDEX = 'terminationtype.index';

    public function index(Request $request): View|RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage termination type',
            self::REDIRECT_INDEX
        )) !== true)
            return $redirect;
        $terminationTypes = TerminationType::where(
            DatabaseConstants::TABLE_CREATOR,
            $user?->creatorId()
        )->get();
        return view(
            'terminationtype.index',
            compact('terminationTypes')
        );
    }

    public function show(
        Request $request,
        TerminationType $terminationType
    ): View|RedirectResponse|null {
        if (($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage termination type',
            self::REDIRECT_INDEX
        )) !== true)
            return $redirect;
        if (
            $terminationType->created_by
            !== $user?->creatorId()
        )
            return defaultPermissionDenial(
                $request,
                new \Exception('Ownership mismatch'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        try {
            return view(
                'terminationtype.show',
                compact('terminationType')
            );
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' failed: ' .
                    $e->getMessage()
            );
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        }
    }
    public function create(Request $request): View|RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create termination type',
            self::REDIRECT_INDEX
        )) !== true)
            return $redirect;
        return view('terminationtype.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse|null
    {
        if (($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create termination type',
            self::REDIRECT_INDEX
        )) !== true)
            return $redirect;
        try {
            $data = $request->validate([
                'name' => 'required|max:20'
            ]);
            return DB::transaction(function () use (
                $data,
                $request,
                $user
            ) {
                $terminationType = new TerminationType();
                $terminationType->name = $data['name'];
                $terminationType->created_by = $user?->creatorId();
                $terminationType->save();
                return redirect()
                    ->route(self::REDIRECT_INDEX)
                    ->with(
                        'success',
                        __('Termination type successfully created.')
                    );
            });
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' failed: ' .
                    $e->getMessage()
            );
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function edit(
        Request $request,
        TerminationType $terminationType
    ): View|RedirectResponse|null {
        if (($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit termination type',
            self::REDIRECT_INDEX
        )) !== true)
            return $redirect;
        if (
            $terminationType->created_by
            !== $user?->creatorId()
        )
            return defaultPermissionDenial(
                $request,
                new \Exception('Ownership mismatch'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        return view(
            'terminationtype.edit',
            compact('terminationType')
        );
    }

    public function update(
        Request $request,
        TerminationType $terminationType
    ): RedirectResponse|JsonResponse|null {
        if (($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'edit termination type',
            self::REDIRECT_INDEX
        )) !== true)
            return $redirect;
        if (
            $terminationType->created_by
            !== $user?->creatorId()
        )
            return defaultPermissionDenial(
                $request,
                new \Exception('Ownership mismatch'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        try {
            $data = $request->validate([
                'name' => 'required|max:20'
            ]);
            DB::transaction(function () use (
                $data,
                $terminationType
            ) {
                $terminationType->name = $data['name'];
                $terminationType->save();
            });
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with(
                    'success',
                    __('Termination type successfully updated.')
                );
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' failed: ' .
                    $e->getMessage()
            );
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function destroy(
        Request $request,
        TerminationType $terminationType
    ): RedirectResponse|JsonResponse|null {
        if (($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'delete termination type',
            self::REDIRECT_INDEX
        )) !== true)
            return $redirect;
        if (
            $terminationType->created_by
            !== $user?->creatorId()
        )
            return defaultPermissionDenial(
                $request,
                new \Exception('Ownership mismatch'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        try {
            DB::transaction(function () use (
                $terminationType
            ) {
                $terminationType->delete();
            });
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with(
                    'success',
                    __('Termination type successfully deleted.')
                );
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' failed: ' .
                    $e->getMessage()
            );
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
