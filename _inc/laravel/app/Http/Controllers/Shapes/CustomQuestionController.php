<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\CustomQuestion;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Illuminate\View\View;

class CustomQuestionController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_ROUTE = ViewsConstants::CST_QT . '.index';

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(Request $request): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['user' => Auth::id()]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard($request, PermissionsConstants::MNG_CST_QT, self::REDIRECT_ROUTE)) {
            Log::warning('index: permission denied', ['user' => Auth::id()]);
            return $c;
        }
        try {
            $questions = CustomQuestion::where(
                'created_by',
                $user?->creatorId()
            )->get();
            Log::info('index: fetched', ['count' => $questions->count()]);
            return view('customQuestion.index', compact('questions'));
        } catch (\Throwable $e) {
            Log::error('index error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function create(Request $request): View|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['user' => Auth::id()]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($c = self::guard($request, 'create custom question', self::REDIRECT_ROUTE)) {
            Log::warning('create: permission denied', ['user' => Auth::id()]);
            return $c;
        }
        $isRequired = CustomQuestion::$is_required;
        return view('customQuestion.create', compact('isRequired'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'input' => $request->only('question', 'is_required')
        ]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($c = self::guard($request, 'create custom question', self::REDIRECT_ROUTE)) {
            Log::warning('store: permission denied', ['user' => Auth::id()]);
            return $c;
        }
        $validator = Validator::make($request->all(), ['question' => 'required']);
        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            Log::warning('store: validation failed', ['error' => $msg]);
            return redirect()->back()->with('error', $msg);
        }
        try {
            DB::transaction(function () use ($request, $user) {
                $q = new CustomQuestion();
                foreach (['question', 'is_required'] as $field) {
                    $prop = $field === 'is_required' ? 'is_required' : $field;
                    $q->$prop = $request->input($field);
                }
                $q[DatabaseConstants::TABLE_CREATOR] = $user?->creatorId();
                $q->save();
                Log::info('store: created', ['id' => $q->id]);
            });
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Question successfully created.'));
        } catch (\Throwable $e) {
            Log::error('store error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(CustomQuestion $customQuestion): View|RedirectResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $customQuestion->id]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($c = self::guard(request(), 'view custom question', self::REDIRECT_ROUTE)) {
            Log::warning('show: permission denied', ['user' => Auth::id()]);
            return $c;
        }
        return view('customQuestion.show', compact('customQuestion'));
    }

    public function edit(CustomQuestion $customQuestion, Request $request): View|RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $customQuestion->id]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($c = self::guard($request, 'edit custom question', self::REDIRECT_ROUTE)) {
            Log::warning('edit: permission denied', ['user' => Auth::id()]);
            return $c;
        }
        if (!$this->isOwner($customQuestion)) {
            Log::warning('edit: not owner', ['user' => Auth::id()]);
            return defaultPermissionDenial($request, new AuthorizationException, __CLASS__ . '::' . __FUNCTION__);
        }
        $isRequired = CustomQuestion::$is_required;
        return view('customQuestion.edit', compact('customQuestion', 'isRequired'));
    }

    public function update(Request $request, CustomQuestion $customQuestion): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', [
            'id' => $customQuestion->id,
            'input' => $request->only('question', 'is_required')
        ]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($c = self::guard($request, 'edit custom question', self::REDIRECT_ROUTE)) {
            Log::warning('update: permission denied', ['user' => Auth::id()]);
            return $c;
        }
        if (!$this->isOwner($customQuestion)) {
            Log::warning('update: not owner', ['user' => Auth::id()]);
            return defaultPermissionDenial($request, new AuthorizationException, __CLASS__ . '::' . __FUNCTION__);
        }
        $validator = Validator::make($request->all(), ['question' => 'required']);
        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            Log::warning('update: validation failed', ['error' => $msg]);
            return redirect()->back()->with('error', $msg);
        }
        try {
            DB::transaction(function () use ($request, $customQuestion) {
                foreach (['question', 'is_required'] as $field) {
                    $customQuestion->$field = $request->input($field);
                }
                $customQuestion->save();
                Log::info('update: saved', ['id' => $customQuestion->id]);
            });
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Question successfully updated.'));
        } catch (\Throwable $e) {
            Log::error('update error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function destroy(CustomQuestion $customQuestion, Request $request): RedirectResponse|JsonResponse
    {
        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' start', ['id' => $customQuestion->id]);
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        if ($c = self::guard($request, 'delete custom question', self::REDIRECT_ROUTE)) {
            Log::warning('destroy: permission denied', ['user' => Auth::id()]);
            return $c;
        }
        if (!$this->isOwner($customQuestion)) {
            Log::warning('destroy: not owner', ['user' => Auth::id()]);
            return defaultPermissionDenial($request, new AuthorizationException, __CLASS__ . '::' . __FUNCTION__);
        }
        try {
            DB::transaction(function () use ($customQuestion) {
                $customQuestion->delete();
                Log::info('destroy: deleted', ['id' => $customQuestion->id]);
            });
            return redirect()->route(self::REDIRECT_ROUTE)
                ->with('success', __('Question successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error('destroy error', ['err' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    private function isOwner(CustomQuestion $question): bool
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $question[DatabaseConstants::TABLE_CREATOR] === $user?->creatorId();
    }
}
