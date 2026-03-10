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
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
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
        $action = __FUNCTION__;
        $cls = static::class;
        $sig = "$cls::$action";
        return $this->measureProfile(function () use ($request, $action, $sig) {
            Log::info("$sig start", ['user' => Auth::id()]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::MNG_CST_QT, self::REDIRECT_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            try {
                $t = microtime(true);
                $questions = CustomQuestion::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $this->logExecutionTime($t, "$sig::fetchQuestions", 'completed');

                $viewPath = ViewsConstants::CST_QT . '.index';
                $t = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

                Log::info("$sig fetched", ['count' => $questions->count()]);
                return view($viewPath, compact('questions'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $sig);
            }
        });
    }

    public function create(Request $request): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = static::class;
        $sig = "$cls::$action";
        return $this->measureProfile(function () use ($request, $action, $sig) {
            Log::info("$sig start", ['user' => Auth::id()]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, 'create custom question', self::REDIRECT_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            try {
                $t = microtime(true);
                $isRequired = CustomQuestion::$is_required;
                $this->logExecutionTime($t, "$sig::prepareData", 'completed');

                $viewPath = ViewsConstants::CST_QT . '.create';
                $t = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

                return view($viewPath, compact('isRequired'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $sig);
            }
        });
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $cls = static::class;
        $sig = "$cls::$action";
        return $this->measureProfile(function () use ($request, $action, $sig) {
            Log::info("$sig start", ['input' => $request->only('question', 'is_required')]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'create custom question', self::REDIRECT_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }

            $t = microtime(true);
            $validator = Validator::make($request->all(), ['question' => 'required']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');
            if ($validator->fails()) {
                $msg = $validator->errors()->first();
                Log::warning("$sig validation failed", ['error' => $msg]);
                return redirect()->back()->with('error', $msg);
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($request, $user) {
                    $q = new CustomQuestion();
                    foreach (['question', 'is_required'] as $field) {
                        $q->$field = $request->input($field);
                    }
                    $q[DatabaseConstants::COL_TABLE_CREATOR] = $user?->creatorId();
                    $q->save();
                    Log::info('store: created', ['id' => $q->id]);
                });
                $this->logExecutionTime($t, "$sig::transaction", 'completed');

                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Question successfully created.'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $sig);
            }
        });
    }

    public function show(CustomQuestion $customQuestion, Request $request): View|RedirectResponse
    {
        $action = __FUNCTION__;
        $cls = static::class;
        $sig = "$cls::$action";
        return $this->measureProfile(function () use ($customQuestion, $request, $action, $sig) {
            Log::info("$sig start", ['id' => $customQuestion->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, 'view custom question', self::REDIRECT_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }

            try {
                $viewPath = ViewsConstants::CST_QT . '.show';
                $t = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

                return view($viewPath, compact('customQuestion'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $sig);
            }
        });
    }

    public function edit(CustomQuestion $customQuestion, Request $request): View|RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $cls = static::class;
        $sig = "$cls::$action";
        return $this->measureProfile(function () use ($customQuestion, $request, $action, $sig) {
            Log::info("$sig start", ['id' => $customQuestion->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, 'edit custom question', self::REDIRECT_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            if (!$this->isOwner($customQuestion)) {
                Log::warning("$sig not owner", ['user' => Auth::id()]);
                return defaultPermissionDenial($request, new AuthorizationException, $sig);
            }

            try {
                $t = microtime(true);
                $isRequired = CustomQuestion::$is_required;
                $this->logExecutionTime($t, "$sig::prepareData", 'completed');

                $viewPath = ViewsConstants::CST_QT . '.edit';
                $t = microtime(true);
                if (!ViewFacade::exists($viewPath)) {
                    $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                    return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                }
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

                return view($viewPath, compact('customQuestion', 'isRequired'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $sig);
            }
        });
    }

    public function update(Request $request, CustomQuestion $customQuestion): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $cls = static::class;
        $sig = "$cls::$action";
        return $this->measureProfile(function () use ($request, $customQuestion, $action, $sig) {
            Log::info("$sig start", ['id' => $customQuestion->id, 'input' => $request->only('question', 'is_required')]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, 'edit custom question', self::REDIRECT_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            if (!$this->isOwner($customQuestion)) {
                Log::warning("$sig not owner", ['user' => Auth::id()]);
                return defaultPermissionDenial($request, new AuthorizationException, $sig);
            }

            $t = microtime(true);
            $validator = Validator::make($request->all(), ['question' => 'required']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');
            if ($validator->fails()) {
                $msg = $validator->errors()->first();
                Log::warning("$sig validation failed", ['error' => $msg]);
                return redirect()->back()->with('error', $msg);
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($request, $customQuestion) {
                    foreach (['question', 'is_required'] as $field) {
                        $customQuestion->$field = $request->input($field);
                    }
                    $customQuestion->save();
                    Log::info('update: saved', ['id' => $customQuestion->id]);
                });
                $this->logExecutionTime($t, "$sig::transaction", 'completed');

                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Question successfully updated.'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $sig);
            }
        });
    }

    public function destroy(CustomQuestion $customQuestion, Request $request): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $cls = static::class;
        $sig = "$cls::$action";
        return $this->measureProfile(function () use ($customQuestion, $request, $action, $sig) {
            Log::info("$sig start", ['id' => $customQuestion->id]);
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, 'delete custom question', self::REDIRECT_ROUTE)) !== true) {
                Log::warning("$sig denied", ['user' => Auth::id()]);
                return $c;
            }
            if (!$this->isOwner($customQuestion)) {
                Log::warning("$sig not owner", ['user' => Auth::id()]);
                return defaultPermissionDenial($request, new AuthorizationException, $sig);
            }

            try {
                $t = microtime(true);
                DB::transaction(function () use ($customQuestion) {
                    $customQuestion->delete();
                    Log::info('destroy: deleted', ['id' => $customQuestion->id]);
                });
                $this->logExecutionTime($t, "$sig::transaction", 'completed');

                return redirect()->route(self::REDIRECT_ROUTE)->with('success', __('Question successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("$sig error", ['err' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $sig);
            }
        });
    }

    private function isOwner(CustomQuestion $question): bool|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
            return $userOrRedirect;
        }
        $user = $userOrRedirect;
        return $question[DatabaseConstants::COL_TABLE_CREATOR] === $user?->creatorId();
    }
}
