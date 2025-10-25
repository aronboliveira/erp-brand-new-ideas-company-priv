<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Models\{Branch, CompanyPolicy, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    File,
    Log,
    Route,
    Validator,
    View as ViewFacade,
};

final class CompanyPolicyController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = '/';

    public function index(Request $r)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($r, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'manage company policy', self::REDIRECT_INDEX)) !== true) return $c; // ! ALERT
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $r->user()?->id, 'method' => $method]);
            try {
                $qStart = microtime(true);
                $companyPolicy = CompanyPolicy::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->with('branches')->get();
                $this->logExecutionTime($qStart, $action, 'fetchPolicies');
                $viewPath = ViewsConstants::CPN_PL . '.' . $action;
                if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
                $renderStart = microtime(true);
                $resp = view($viewPath, compact('companyPolicy'));
                $this->logExecutionTime($renderStart, $action, 'renderView');
                return $resp;
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($r, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $r)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($r, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'create company policy', self::REDIRECT_INDEX)) !== true) return $c; // ! ALERT
            Log::debug("[{$base}::{$action}] start", [UsersConstants::COL_USER_ID => $r->user()?->id, 'method' => $method]);
            $branch = self::branches($u->creatorId());
            $viewPath = ViewsConstants::CPN_PL . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('branch'));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($r, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'create company policy', self::REDIRECT_INDEX)) !== true) return $c; // ! ALERT
            if ($c = self::v($r, ['branch' => 'required', 'title' => 'required'])) return $c;
            try {
                $uplStart = microtime(true);
                $file = $r->hasFile('attachment') ? self::upload($r, 'attachment') : null;
                $this->logExecutionTime($uplStart, $action, 'uploadAttachment');
                $crtStart = microtime(true);
                $policy = CompanyPolicy::create([
                    'branch' => $r->branch,
                    'title' => $r->title,
                    'description' => $r->description,
                    'attachment' => $file,
                    DatabaseConstants::TABLE_CREATOR => $u->creatorId()
                ]);
                $this->logExecutionTime($crtStart, $action, 'createPolicy');
                try {
                    $settings = Utility::settings($u->creatorId());
                    $branch = Branch::find($r->branch);
                    $payload = [
                        'company_policy_name' => $policy->title,
                        'branch_name' => $branch?->name ?? ''
                    ];
                    ($settings['policy_notification'] ?? false) && Utility::sendSlackMsg('new_company_policy', $payload);
                    ($settings['telegram_policy_notification'] ?? false) && Utility::sendTelegramMsg('new_company_policy', $payload);
                } catch (\Throwable $e) {
                    Log::warning($class . '::' . $action . ' notify ' . $e->getMessage());
                }
                try {
                    if ($hook = Utility::webhookSetting('New Company Policy')) {
                        $ok = Utility::webhookCall($hook['url'], $policy->toJson(), $hook['method']);
                        $ok || Log::warning($class . '::' . $action . ' webhook failed');
                    }
                } catch (\Throwable $e) {
                    Log::warning($class . '::' . $action . ' webhook ' . $e->getMessage());
                }
                Log::info("[{$base}::{$action}] created", ['policy_id' => $policy->id, UsersConstants::COL_USER_ID => $r->user()?->id]);
                return redirect()->route(ViewsConstants::CPN_PL . '.index')->with('success', __('Company policy successfully created.'));
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage()]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($r, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function edit(Request $r, CompanyPolicy $companyPolicy)
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($r, $companyPolicy, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit company policy', self::REDIRECT_INDEX)) !== true) return $c; // ! ALERT
            $branch = self::branches($u->creatorId());
            $viewPath = ViewsConstants::CPN_PL . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('branch', 'companyPolicy'));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'policy_id' => $companyPolicy->id]);
    }

    public function update(Request $r, CompanyPolicy $companyPolicy): RedirectResponse|JsonResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($r, $companyPolicy, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit company policy', self::REDIRECT_INDEX)) !== true) return $c; // ! ALERT
            if ($c = self::v($r, ['branch' => 'required', 'title' => 'required'])) return $c;
            try {
                $data = [
                    'branch' => $r->branch,
                    'title' => $r->title,
                    'description' => $r->description
                ];
                if ($r->hasFile('attachment')) {
                    $uplStart = microtime(true);
                    $data['attachment'] = self::upload($r, 'attachment');
                    $this->logExecutionTime($uplStart, $action, 'uploadAttachment');
                }
                $updStart = microtime(true);
                $companyPolicy->update($data);
                $this->logExecutionTime($updStart, $action, 'updatePolicy');
                Log::info("[{$base}::{$action}] updated", ['policy_id' => $companyPolicy->id, UsersConstants::COL_USER_ID => $r->user()?->id]);
                return redirect()->route(ViewsConstants::CPN_PL . '.index')->with('success', __('Company policy successfully updated.'));
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'policy_id' => $companyPolicy->id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($r, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'policy_id' => $companyPolicy->id]);
    }

    public function destroy(Request $r, CompanyPolicy $companyPolicy): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($r, $companyPolicy, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'delete document', self::REDIRECT_INDEX)) !== true) return $c; // ! ALERT
            if ($companyPolicy->created_by !== $u->creatorId()) return back()->with('error', __('Permission denied.'));
            try {
                if ($companyPolicy->attachment) {
                    $file = 'uploads/companyPolicy/' . $companyPolicy->attachment;
                    File::exists($file) && File::delete($file);
                }
                $delStart = microtime(true);
                $companyPolicy->delete();
                $this->logExecutionTime($delStart, $action, 'deletePolicy');
                Log::info("[{$base}::{$action}] deleted", ['policy_id' => $companyPolicy->id, UsersConstants::COL_USER_ID => $r->user()?->id]);
                return redirect()->route(ViewsConstants::CPN_PL . '.index')->with('success', __('Company policy successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error("[{$base}::{$action}] error", ['error' => $e->getMessage(), 'policy_id' => $companyPolicy->id]);
                Log::debug("[{$base}::{$action}] exception context", ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return defaultUndefinedException($r, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'policy_id' => $companyPolicy->id]);
    }

    public function show(): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () {
            return redirect()->route(ViewsConstants::CPN_PL . '.index');
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    private static function v(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }

    private static function branches(int $creator): array
    {
        try {
            return Branch::where(DatabaseConstants::TABLE_CREATOR, $creator)
                ->pluck(CompaniesConstants::COL_BRC_NM, 'id')
                ->prepend(__('Select Branch'), '')
                ->all();
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' ' . $e->getMessage());
            return [];
        }
    }

    private static function upload(
        Request $r,
        string  $field,
        string  $dir = 'uploads/companyPolicy/'
    ): string {
        $file     = $r->file($field);
        $nameParts = pathinfo($file->getClientOriginalName());
        $name     = "{$nameParts['filename']}_" . time() . '.' . $nameParts['extension'];
        try {
            $path = Utility::uploadFile($r, $field, $name, $dir, []);
            return $path['flag'] ? $name : throw new \RuntimeException($path['msg']);
        } catch (\Throwable $e) {
            throw new \RuntimeException('upload', 0, $e); // ! ALERT bubbling msg
        }
    }
}
