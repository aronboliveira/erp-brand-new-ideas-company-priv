<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    ViewsConstants
};
use App\Models\GoalType;
use App\Traits\ChecksLogin;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GoalTypeController extends Controller
{
    use ChecksLogin;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            if (($redirect = $this->_authorize(
                $request,
                'manage goal type'
            )) instanceof RedirectResponse)
                return $redirect;
            $user = $request->user();
            $goalTypes = GoalType::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->get();
            return view(ViewsConstants::GL_TP . '.' . __FUNCTION__, compact('goalTypes'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->_authorize(
            $request,
            'create goal type'
        )) instanceof RedirectResponse)
            return $redirect;
        return view(ViewsConstants::GL_TP . '.' . __FUNCTION__);
    }

    public function store(Request $request): RedirectResponse
    {
        if (($redirect = $this->_authorize(
            $request,
            'create goal type'
        )) instanceof RedirectResponse)
            return $redirect;
        $validator = Validator::make(
            $request->all(),
            ['name' => 'required']
        );
        if ($validator->fails())
            return redirect()->back()
                ->with('error', $validator
                    ->getMessageBag()->first());
        try {
            $user = $request->user();
            GoalType::create([
                'name' => $request->name,
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
            ]);
            return redirect()->route(
                ViewsConstants::GL_TP . '.index'
            )->with(
                'success',
                __('GoalType successfully created.')
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function show(
        Request $request,
        GoalType $goalType
    ): View|RedirectResponse {
        if (($redirect = $this->_authorize(
            $request,
            'manage goal type'
        )) instanceof RedirectResponse)
            return $redirect;
        return view(
            ViewsConstants::GL_TP . '.' . __FUNCTION__,
            compact('goalType')
        );
    }

    public function edit(
        Request $request,
        string $id
    ): View|RedirectResponse {
        if (($redirect = $this->_authorize(
            $request,
            'edit goal type'
        )) instanceof RedirectResponse)
            return $redirect;
        $goalType = GoalType::findOrFail($id);
        return view(ViewsConstants::GL_TP . '.' . __FUNCTION__, compact('goalType'));
    }

    public function update(
        Request $request,
        string $id
    ): RedirectResponse {
        if (($redirect = $this->_authorize(
            $request,
            'edit goal type'
        )) instanceof RedirectResponse)
            return $redirect;
        $validator = Validator::make(
            $request->all(),
            ['name' => 'required']
        );
        if ($validator->fails())
            return redirect()->back()
                ->with('error', $validator
                    ->getMessageBag()->first());
        try {
            $goalType = GoalType::findOrFail($id);
            $goalType->name = $request->name;
            $goalType->save();
            return redirect()->route(
                ViewsConstants::GL_TP . '.index'
            )->with(
                'success',
                __('GoalType successfully updated.')
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(
        Request $request,
        string $id
    ): RedirectResponse {
        if (($redirect = $this->_authorize(
            $request,
            'delete goal type'
        )) instanceof RedirectResponse)
            return $redirect;
        try {
            $goalType = GoalType::findOrFail($id);
            if (
                $goalType->created_by !==
                $request->user()->creatorId()
            )
                return defaultPermissionDenial(
                    $request,
                    null,
                    __CLASS__ . '::' . __FUNCTION__,
                    route(ViewsConstants::GL_TP . '.index')
                );
            $goalType->delete();
            return redirect()->route(
                ViewsConstants::GL_TP . '.index'
            )->with(
                'success',
                __('GoalType successfully deleted.')
            );
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    private function _authorize(Request $request, string $ability): RedirectResponse|null
    {
        if (!$request->user()->can($ability))
            return defaultPermissionDenial(
                $request,
                null,
                __CLASS__ . '::' . __FUNCTION__,
                route(ViewsConstants::GL_TP . '.index')
            );
        return null;
    }
}
