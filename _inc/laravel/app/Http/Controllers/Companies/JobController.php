<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
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
    Validator
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
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) return $c;
        $jobs = Job::with([DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::TABLE_CREATOR])
            ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->get();
        $data = [
            'active'  => Job::where('status', 'active')
                ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->count(),
            'inActive' => Job::where('status', 'in_active')
                ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->count(),
            'total'   => Job::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->count()
        ];
        return view(self::SINGULAR . '.' . __FUNCTION__, compact('data', DatabaseConstants::TABLE_JOBS));
    }

    public function create(Request $req): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) return $c;
        $categories    = JobCategory::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck('title', 'id')->prepend('--', '');
        $branches      = Branch::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend('All', 0);
        $status        = Job::$status;
        $customQuestion = CustomQuestion::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
        return view(
            self::SINGULAR . '.' . __FUNCTION__,
            compact(
                DatabaseConstants::TABLE_BRANCHES,
                DatabaseConstants::TABLE_JOB_CATS,
                'custom_question',
                'status'
            )
        );
    }

    public function store(Request $req): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_CREATE, self::REDIRECT_INDEX)) return $c;
        $v = Validator::make($req->all(), self::$jobRules);
        if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());
        try {
            Job::create($this->buildJobPayload($req, $u->creatorId()));
            return redirect()->route(self::SINGULAR . '.index')->with('success', __('Job successfully created.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($req, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }
    public function show(Request $req, Job $job): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_MANAGE, self::REDIRECT_INDEX)) return $c;
        $job->applicant      = explode(',', $job->applicant);
        $job->customQuestion = explode(',', $job->custom_question);
        $job->skill          = explode(',', $job->skill);
        $job->visibility     = explode(',', $job->visibility);
        $status              = Job::$status;
        return view(self::SINGULAR . '.' . __FUNCTION__, compact(self::SINGULAR, 'status'));
    }

    public function edit(Request $req, Job $job): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) return $c;
        if ($job->created_by !== $u->creatorId()) return defaultPermissionDenial(
            $req,
            new AuthorizationException(),
            __CLASS__ . '::' . __FUNCTION__,
            route(self::SINGULAR . '.index')
        );
        $branches      = Branch::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck(CompaniesConstants::COL_BRC_NM, 'id')->prepend('All', 0);
        $categories    = JobCategory::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->pluck('title', 'id')->prepend('--', '');
        $customQuestion = CustomQuestion::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
        $status        = Job::$status;
        $job->applicant      = explode(',', $job->applicant);
        $job->customQuestion = explode(',', $job->custom_question);
        $job->skill          = explode(',', $job->skill);
        $job->visibility     = explode(',', $job->visibility);
        return view(
            self::SINGULAR . '.' . __FUNCTION__,
            compact(
                DatabaseConstants::TABLE_BRANCHES,
                DatabaseConstants::TABLE_JOB_CATS,
                'custom_question',
                self::SINGULAR,
                'status'
            )
        );
    }

    public function update(Request $req, Job $job): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_EDIT, self::REDIRECT_INDEX)) return $c;
        if ($job->created_by !== $u->creatorId()) return defaultPermissionDenial(
            $req,
            new AuthorizationException(),
            __CLASS__ . '::' . __FUNCTION__,
            route(self::SINGULAR . '.index')
        );
        $v = Validator::make($req->all(), self::$jobRules);
        if ($v->fails()) return redirect()->back()
            ->with('error', $v->errors()->first());
        try {
            $job->update($this->buildJobAttributes($req));
            return redirect()->route(self::SINGULAR . '.index')
                ->with('success', __('Job successfully updated.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function destroy(Request $req, Job $job): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = self::guard($req, self::PERM_DELETE, self::REDIRECT_INDEX)) return $c;
        if ($job->created_by !== $u->creatorId()) return defaultPermissionDenial(
            $req,
            new AuthorizationException(),
            __CLASS__ . '::' . __FUNCTION__,
            route(self::SINGULAR . '.index')
        );
        try {
            JobApplicationNote::whereIn(
                'application_id',
                JobApplication::where(self::SINGULAR, $job->id)->pluck('id')
            )->delete();
            JobApplication::where(self::SINGULAR, $job->id)->delete();
            $job->delete();
            return redirect()->route(self::SINGULAR . '.index')
                ->with('success', __('Job successfully deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__
            );
        }
    }

    public function career(int|string $companyId, string $lang): View
    {
        $jobs = Job::where(DatabaseConstants::TABLE_CREATOR, $companyId)
            ->with([DatabaseConstants::TABLE_BRANCHES, DatabaseConstants::TABLE_CREATOR])->get();
        App::setLocale($lang);
        session(['lang' => $lang]);
        $settings = DB::table(DatabaseConstants::TABLE_SETTINGS)
            ->where(DatabaseConstants::TABLE_CREATOR, $companyId)
            ->whereIn('name', [
                SettingsConstants::CPN_FAVICON_K,
                SettingsConstants::CPN_LG,
                SettingsConstants::FT_TXT,
                'title_text'
            ])->pluck('value', 'name')->toArray();
        $languages = Utility::languages();
        $currLang = session('lang') ?? User::find($companyId)->lang ?? DatabaseConstants::DEFAULT_LANG;
        return view(
            self::SINGULAR . '.' . __FUNCTION__,
            compact(
                'companyId',
                'currLang',
                DatabaseConstants::TABLE_JOBS,
                DatabaseConstants::TABLE_LANGS,
                DatabaseConstants::TABLE_SETTINGS
            )
        );
    }

    public const JB_RQ = 'jobRequirement';
    public function jobRequirement(string $code, string $lang): RedirectResponse|View
    {
        $job = Job::where('code', $code)->firstOrFail();
        if ($job->status === 'in_active') return redirect()->back()
            ->with('error', __('Permission denied.'));
        App::setLocale($lang);
        session(['lang' => $lang]);
        $settings = DB::table(DatabaseConstants::TABLE_SETTINGS)
            ->where(DatabaseConstants::TABLE_CREATOR, $job->created_by)
            ->whereIn('name', [
                SettingsConstants::CPN_FAVICON_K,
                SettingsConstants::CPN_LG,
                SettingsConstants::FT_TXT,
                'title_text'
            ])->pluck('value', 'name')->toArray();
        $languages = Utility::languages();
        $currLang = session('lang') ?? $job->createdBy->lang ?? DatabaseConstants::DEFAULT_LANG;
        return view(
            self::SINGULAR . '.requirement',
            compact(
                'currLang',
                self::SINGULAR,
                DatabaseConstants::TABLE_LANGS,
                DatabaseConstants::TABLE_SETTINGS
            )
        );
    }

    public const JB_AP = 'jobApply';
    public function jobApply(string $code, string $lang): View
    {
        $job = Job::where('code', $code)->firstOrFail();
        App::setLocale($lang);
        session(['lang' => $lang]);
        $settings = DB::table(DatabaseConstants::TABLE_SETTINGS)
            ->where(DatabaseConstants::TABLE_CREATOR, $job->created_by)
            ->whereIn('name', [
                SettingsConstants::CPN_FAVICON_K,
                SettingsConstants::CPN_LG,
                SettingsConstants::FT_TXT,
                'title_text'
            ])->pluck('value', 'name')->toArray();
        $questions = CustomQuestion::where(
            DatabaseConstants::TABLE_CREATOR,
            $job->created_by
        )->get();
        $languages = Utility::languages();
        $currLang = session('lang') ?? $job->createdBy->lang ?? DatabaseConstants::DEFAULT_LANG;
        return view(
            self::SINGULAR . '.apply',
            compact('currLang', self::SINGULAR, DatabaseConstants::TABLE_LANGS, 'questions', DatabaseConstants::TABLE_SETTINGS)
        );
    }

    public const JB_AP_DT = 'jobApplyData';
    public function jobApplyData(Request $req, string $code): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $v = Validator::make($req->all(), [
            'name'  => 'required',
            'email' => 'required|email',
            'phone' => 'required'
        ]);
        if ($v->fails()) return redirect()->back()
            ->with('error', $v->errors()->first());
        $job = Job::where('code', $code)->firstOrFail();
        try {
            $files = [];
            foreach (['profile', 'resume'] as $f) {
                if ($req->hasFile($f)) {
                    $size  = $req->file($f)->getSize();
                    $limit = Utility::updateStorageLimit(
                        $user?->creatorId(),
                        $size
                    );
                    if ($limit !== 1) continue;
                    $orig  = $req->file($f)->getClientOriginalName();
                    $stored = pathinfo($orig, PATHINFO_FILENAME)
                        . '_' . time() . '.' . $req->file($f)->getClientOriginalExtension();
                    Utility::uploadFile(
                        $req,
                        $f,
                        $stored,
                        "uploads/job/$f",
                        []
                    );
                    $files[$f] = $stored;
                }
            }
            $stage = JobStage::where(
                DatabaseConstants::TABLE_CREATOR,
                $job->created_by
            )->first()->id ?? null;
            $appData = [self::SINGULAR => $job->id];
            foreach (
                [
                    'name',
                    'email',
                    'phone',
                    'cover_letter',
                    'dob',
                    'gender',
                    'country',
                    'state',
                    'city'
                ] as $field
            )
                $appData[$field] = $req->input($field, '');
            $appData['custom_question'] = json_encode($req->input('question', []));
            foreach (['profile', 'resume'] as $f)
                $appData[$f] = $files[$f] ?? '';
            $appData['stage']     = $stage;
            $appData[DatabaseConstants::TABLE_CREATOR] = $job->created_by;
            JobApplication::create($appData);
            return redirect()->back()->with(
                'success',
                __('Job application successfully sent.')
            );
        } catch (\Throwable $e) {
            Log::error('JobApplyData failed: ' . $e->getMessage());
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::SINGULAR . '.apply', ['code' => $code, 'lang' => session('lang')])
            );
        }
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
            $attrs[DatabaseConstants::TABLE_CREATOR] = $userId;
            $attrs['code']      = uniqid();
        }
        return $attrs;
    }
}
