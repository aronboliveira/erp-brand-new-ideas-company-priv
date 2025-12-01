<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
};
use App\Models\{
    Branch,
    CustomQuestion,
    Job,
    JobApplication,
    JobApplicationNote,
    JobCategory,
    JobStage,
    Utility,
    User
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request,
    Response
};
use Illuminate\Support\Facades\{
    App,
    Auth,
    DB,
    File,
    Log,
    Route,
    Validator,
    View as ViewFacade,
};
use Illuminate\View\View;

final class JobController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const SINGULAR = ViewsConstants::JB;
    private const PERM_CREATE = PermissionsConstants::CR_JB;
    private const PERM_DELETE = 'delete job';
    private const PERM_EDIT  = 'edit job';
    private const PERM_MANAGE = PermissionsConstants::MNG_JB;
    private const REDIRECT_INDEX = self::SINGULAR . '.index';
    private static array $jobRules = [
        'title'       => 'required',
        'branch'      => 'required',
        'category'    => 'required',
        'description' => 'required',
        'startDate'   => 'required|date',
        'endDate'     => 'required|date|after_or_equal:startDate',
        'position'    => 'required|integer',
        'requirement' => 'required',
        'skill'       => 'required',
    ];

    public function index(Request $req): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) !== true) return $c;
            Log::debug("[$base::$action] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method]);
            $qStart = microtime(true);
            $jobs = Job::with([DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::COL_TABLE_CREATOR])
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->get();
            $this->logExecutionTime($qStart, $action, 'fetchJobs');
            $cStart = microtime(true);
            $data = [
                'active'   => Job::where('status', 'active')->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->count(),
                'inActive' => Job::where('status', 'in_active')->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->count(),
                'total'    => Job::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->count(),
            ];
            $this->logExecutionTime($cStart, $action, 'aggregateCounts');
            $viewPath = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('data', DatabaseConstants::TABLE_JOBS));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function create(Request $req): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $c;
            Log::debug("[$base::$action] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'method' => $method]);
            ${DatabaseConstants::TABLE_JOB_CATS} = JobCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck('title', 'id')->prepend('--', '');
            ${DatabaseConstants::TABLE_BRANCHES} = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend('All', 0);
            $status = Job::$status;
            $custom_question = CustomQuestion::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
            $viewPath = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::TABLE_JOB_CATS, 'custom_question', 'status'));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function store(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) !== true) return $c;
            $v = Validator::make($req->all(), self::$jobRules);
            if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
            try {
                $crtStart = microtime(true);
                Job::create($this->buildJobPayload($req, $u->creatorId()));
                $this->logExecutionTime($crtStart, $action, 'createJob');
                Log::info("[$base::$action] created");
                return redirect()->route(self::SINGULAR . '.index') // ! ALERT
                    ->with('success', __('Job successfully created.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base]);
    }

    public function show(Request $req, Job $job): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $job, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) !== true) return $c;
            Log::debug("[$base::$action] start", [UsersConstants::COL_USER_ID => $req->user()?->id, 'job_id' => $job->id, 'method' => $method]);
            $job->applicant = explode(',', (string) $job->applicant);
            $job->customQuestion = explode(',', (string) $job->custom_question);
            $job->skill = explode(',', (string) $job->skill);
            $job->visibility = explode(',', (string) $job->visibility);
            $status = Job::$status;
            $viewPath = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(self::SINGULAR, 'status'));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'job_id' => $job->id]);
    }

    public function edit(Request $req, Job $job): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $job, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) !== true) return $c;
            if ($job->created_by !== $u->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::SINGULAR . '.index')); // ! ALERT
            ${DatabaseConstants::TABLE_BRANCHES} = Branch::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend('All', 0);
            ${DatabaseConstants::TABLE_JOB_CATS} = JobCategory::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->pluck('title', 'id')->prepend('--', '');
            $custom_question = CustomQuestion::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())->get();
            $status = Job::$status;
            $job->applicant = explode(',', (string) $job->applicant);
            $job->customQuestion = explode(',', (string) $job->custom_question);
            $job->skill = explode(',', (string) $job->skill);
            $job->visibility = explode(',', (string) $job->visibility);
            $viewPath = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($viewPath)) return back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact(DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::TABLE_JOB_CATS, 'custom_question', self::SINGULAR, 'status'));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'job_id' => $job->id]);
    }

    public function update(Request $req, Job $job): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $job, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) !== true) return $c;
            if ($job->created_by !== $u->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::SINGULAR . '.index')); // ! ALERT
            $v = Validator::make($req->all(), self::$jobRules);
            if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
            try {
                $updStart = microtime(true);
                $job->update($this->buildJobAttributes($req));
                $this->logExecutionTime($updStart, $action, 'updateJob');
                Log::info("[$base::$action] updated", ['job_id' => $job->id]);
                return redirect()->route(self::SINGULAR . '.index') // ! ALERT
                    ->with('success', __('Job successfully updated.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'job_id' => $job->id]);
    }

    public function destroy(Request $req, Job $job): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $job, $action, $method, $class, $base) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($req, self::PERM_DELETE, self::REDIRECT_INDEX)) !== true) return $c;
            if ($job->created_by !== $u->creatorId()) return defaultPermissionDenial($req, new AuthorizationException(), $class . '::' . $action, route(self::SINGULAR . '.index')); // ! ALERT
            try {
                $delStart = microtime(true);
                JobApplicationNote::whereIn('application_id', JobApplication::where(self::SINGULAR, $job->id)->pluck('id'))->delete();
                JobApplication::where(self::SINGULAR, $job->id)->delete();
                $job->delete();
                $this->logExecutionTime($delStart, $action, 'deleteJob');
                Log::info("[$base::$action] deleted", ['job_id' => $job->id]);
                return redirect()->route(self::SINGULAR . '.index') // ! ALERT
                    ->with('success', __('Job successfully deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($req, $e, $class . '::' . $action);
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'job_id' => $job->id]);
    }

    public function career(int|string $companyId, string $lang): View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($companyId, $lang, $action, $method, $class, $base) {
            Log::debug("[$base::$action] start", ['companyId' => $companyId, 'lang' => $lang, 'method' => $method]);
            $qStart = microtime(true);
            $jobs = Job::where(DatabaseConstants::COL_TABLE_CREATOR, $companyId)->with([DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::COL_TABLE_CREATOR])->get();
            $this->logExecutionTime($qStart, $action, 'fetchJobs');
            App::setLocale($lang);
            session(['lang' => $lang]);
            $sStart = microtime(true);
            $settings = DB::table(DatabaseConstants::TABLE_SETTINGS)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $companyId)
                ->whereIn('name', [SettingsConstants::CPN_FAVICON_K, SettingsConstants::CPN_LG, SettingsConstants::FT_TXT, 'title_text'])
                ->pluck('value', 'name')
                ->toArray();
            $this->logExecutionTime($sStart, $action, 'loadSettings');
            $languages = Utility::languages();
            $currLang = session('lang') ?? User::find($companyId)->lang ?? DatabaseConstants::DEFAULT_LANG;
            $viewPath = self::SINGULAR . '.' . $action;
            if (!ViewFacade::exists($viewPath)) abort(404, "Page {$viewPath} not found");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('companyId', 'currLang', DatabaseConstants::TABLE_JOBS, DatabaseConstants::TABLE_LANGS, DatabaseConstants::TABLE_SETTINGS));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'companyId' => $companyId, 'lang' => $lang]);
    }

    public const JB_RQ = 'jobRequirement';
    public function jobRequirement(string $code, string $lang): RedirectResponse|View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($code, $lang, $action, $method, $class, $base) {
            Log::debug("[$base::$action] start", ['code' => $code, 'lang' => $lang, 'method' => $method]);
            $qStart = microtime(true);
            $job = Job::where('code', $code)->firstOrFail();
            $this->logExecutionTime($qStart, $action, 'fetchJob');
            if ($job->status === 'in_active') return back()->with('error', __('This job is not active.'));
            App::setLocale($lang);
            session(['lang' => $lang]);
            $sStart = microtime(true);
            $settings = DB::table(DatabaseConstants::TABLE_SETTINGS)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $job->created_by)
                ->whereIn('name', [SettingsConstants::CPN_FAVICON_K, SettingsConstants::CPN_LG, SettingsConstants::FT_TXT, 'title_text'])
                ->pluck('value', 'name')
                ->toArray();
            $this->logExecutionTime($sStart, $action, 'loadSettings');
            $languages = Utility::languages();
            $currLang = session('lang') ?? $job->createdBy->lang ?? DatabaseConstants::DEFAULT_LANG;
            $viewPath = self::SINGULAR . '.requirement';
            if (!ViewFacade::exists($viewPath)) abort(404, "Page {$viewPath} not found");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('currLang', self::SINGULAR, DatabaseConstants::TABLE_LANGS, DatabaseConstants::TABLE_SETTINGS));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'code' => $code, 'lang' => $lang]);
    }

    public const JB_AP = 'jobApply';
    public function jobApply(string $code, string $lang): View
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($code, $lang, $action, $method, $class, $base) {
            Log::debug("[$base::$action] start", ['code' => $code, 'lang' => $lang, 'method' => $method]);
            $qStart = microtime(true);
            $job = Job::where('code', $code)->firstOrFail();
            $this->logExecutionTime($qStart, $action, 'fetchJob');
            App::setLocale($lang);
            session(['lang' => $lang]);
            $sStart = microtime(true);
            $settings = DB::table(DatabaseConstants::TABLE_SETTINGS)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $job->created_by)
                ->whereIn('name', [SettingsConstants::CPN_FAVICON_K, SettingsConstants::CPN_LG, SettingsConstants::FT_TXT, 'title_text'])
                ->pluck('value', 'name')
                ->toArray();
            $this->logExecutionTime($sStart, $action, 'loadSettings');
            $qsStart = microtime(true);
            $questions = CustomQuestion::where(DatabaseConstants::COL_TABLE_CREATOR, $job->created_by)->get();
            $this->logExecutionTime($qsStart, $action, 'loadQuestions');
            $languages = Utility::languages();
            $currLang = session('lang') ?? $job->createdBy->lang ?? DatabaseConstants::DEFAULT_LANG;
            $viewPath = self::SINGULAR . '.apply';
            if (!ViewFacade::exists($viewPath)) abort(404, "Page {$viewPath} not found");
            $renderStart = microtime(true);
            $resp = view($viewPath, compact('currLang', self::SINGULAR, DatabaseConstants::TABLE_LANGS, 'questions', DatabaseConstants::TABLE_SETTINGS));
            $this->logExecutionTime($renderStart, $action, 'renderView');
            return $resp;
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'code' => $code, 'lang' => $lang]);
    }

    public const JB_AP_DT = 'jobApplyData';
    public function jobApplyData(Request $req, string $code): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        $class = static::class;
        $base = class_basename($class);
        return $this->measureProfile($action, function () use ($req, $code, $action, $method, $class, $base) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            $v = Validator::make($req->all(), ['name' => 'required', 'email' => 'required|email', 'phone' => 'required']);
            if ($v->fails()) return back()->with('error', $v->errors()->first());
            $job = Job::where('code', $code)->firstOrFail();
            try {
                $files = [];
                foreach (['profile', 'resume'] as $f) {
                    if ($req->hasFile($f)) {
                        $size = $req->file($f)->getSize();
                        $limStart = microtime(true);
                        $limit = Utility::updateStorageLimit($user?->creatorId(), $size);
                        $this->logExecutionTime($limStart, $action, "checkStorage:$f");
                        if ($limit !== 1) continue;
                        $orig = $req->file($f)->getClientOriginalName();
                        $stored = pathinfo($orig, PATHINFO_FILENAME) . '_' . time() . '.' . $req->file($f)->getClientOriginalExtension();
                        $upStart = microtime(true);
                        Utility::uploadFile($req, $f, $stored, "uploads/job/$f", []);
                        $this->logExecutionTime($upStart, $action, "upload:$f");
                        $files[$f] = $stored;
                    }
                }
                $stage = JobStage::where(DatabaseConstants::COL_TABLE_CREATOR, $job->created_by)->first()?->id;
                $appData = [self::SINGULAR => $job->id];
                foreach (['name', 'email', 'phone', 'cover_letter', 'dob', 'gender', 'country', 'state', 'city'] as $field) $appData[$field] = $req->input($field, '');
                $appData['custom_question'] = json_encode($req->input('question', []));
                foreach (['profile', 'resume'] as $f) $appData[$f] = $files[$f] ?? '';
                $appData['stage'] = $stage;
                $appData[DatabaseConstants::COL_TABLE_CREATOR] = $job->created_by;
                $crtStart = microtime(true);
                JobApplication::create($appData);
                $this->logExecutionTime($crtStart, $action, 'createApplication');
                Log::info("[$base::$action] application created", ['job_id' => $job->id]);
                return back()->with('success', __('Job application successfully sent.'));
            } catch (\Throwable $e) {
                Log::error("[$base::$action] failed", ['error' => $e->getMessage(), 'job_code' => $code]);
                return defaultUndefinedException($req, $e, $class . '::' . $action, route(self::SINGULAR . '.apply', ['code' => $code, 'lang' => session('lang')])); // ! ALERT
            }
        }, ['route' => Route::getCurrentRoute()?->getName(), 'method' => $method, 'class' => $base, 'code' => $code]);
    }

    private function buildJobAttributes(Request $req, int|string|null $userId = null, bool $includeMeta = false): array
    {
        $simple = ['title', 'branch', 'category', 'description', 'position', 'requirement', 'skill', 'status'];
        $dates = ['start_date' => 'startDate', 'end_date' => 'endDate'];
        $attrs = [];
        foreach ($simple as $key)
            $attrs[$key] = $req->input($key);
        foreach ($dates as $col => $inp)
            $attrs[$col] = $req->input($inp);
        foreach (['visibility', 'applicant', 'custom_question'] as $list) {
            $col = $list === 'custom_question' ? 'custom_question' : $list;
            $attrs[$col] = implode(',', $req->input($list, []));
        }
        if ($includeMeta) {
            $attrs[DatabaseConstants::COL_TABLE_CREATOR] = $userId;
            $attrs['code']      = uniqid();
        }
        return $attrs;
    }
}
