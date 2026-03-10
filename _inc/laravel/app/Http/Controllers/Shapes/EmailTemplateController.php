<?php

namespace App\Http\Controllers\Shapes;

use App\Config\Constants\{
    DatabaseConstants as DC,
    EmailsConstants as EC,
    MiddlewaresConstants as MWC,
    PermissionsConstants as PMC,
    ViewsConstants as VW
};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\{
    EmailTemplate,
    EmailTemplateLang,
    Language,
    UserEmailTemplate,
    Utility
};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request,
    Response
};
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Log,
    Validator,
    View as ViewFacade
};
use Illuminate\Support\Str;

use function App\Http\Controllers\Helpers\defaultPermissionDenial;
class EmailTemplateController extends Controller
{
    private const REDIRECT_BACK = 'back';
    private const SINGULAR = 'email_template';

    public function __construct()
    {
        $this->middleware([MWC::AUTH, MWC::XSS]);
    }

    public function index(): Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $cls      = static::class;
        $sig      = "$cls::$action";
        $viewPath = VW::SET . '.company';

        return $this->measureProfile($action, function () use ($sig, $viewPath) {
            Log::info("$sig start", ['user_id' => Auth::id()]);

            $user = Auth::user();
            if (!in_array($user?->type, [PMC::SA, PMC::CPN])) {
                Log::warning("$sig denied", ['user_id' => $user?->id]);
                return redirect(self::REDIRECT_BACK)->with('error', __('Permission denied.'));
            }

            $t = microtime(true);
            $templates = EmailTemplate::all();
            $this->logExecutionTime($t, "$sig::fetchTemplates", 'completed');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return response()->view($viewPath, compact('templates'));
        });
    }

    public function create(): Response
    {
        $action   = __FUNCTION__;
        $cls      = static::class;
        $sig      = "$cls::$action";
        $viewPath = self::SINGULAR . '.' . $action;

        return $this->measureProfile($action, function () use ($sig, $viewPath) {
            Log::info("$sig start");

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return response()->noContent(404);
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return response()->view($viewPath);
        });
    }

    public function store(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($req, $sig) {
            Log::info("$sig start", ['input' => $req->all()]);

            $t = microtime(true);
            $req->validate([EC::COL_TT => 'required']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');

            $user  = Auth::user();
            $title = $req->input(EC::COL_TT);

            DB::beginTransaction();
            try {
                $t = microtime(true);
                $tpl = EmailTemplate::create([
                    EC::COL_TT         => $title,
                    EC::COL_FROM       => config('app.name'),
                    EC::COL_SLG        => Str::slug($title) ?? '',
                    DC::COL_TABLE_CREATOR => $user?->id,
                ]);
                DB::commit();
                $this->logExecutionTime($t, "$sig::transaction", 'completed');

                Log::info("$sig created", ['id' => $tpl->id]);
                return redirect()->route(self::SINGULAR . '.index')
                    ->with('success', __('Email Template successfully created.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("$sig failed", ['err' => $e->getMessage()]);
                return redirect()->back()->with('error', __('Unexpected error.'));
            }
        });
    }

    public function show(int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($id, $sig) {
            Log::info("$sig start", ['id' => $id]);
            return redirect(self::REDIRECT_BACK)->with('error', __('Permission denied.'));
        });
    }

    public function edit(int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($id, $sig) {
            Log::info("$sig start", ['id' => $id]);
            return redirect(self::REDIRECT_BACK)->with('error', __('Permission denied.'));
        });
    }

    public function update(Request $req, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($req, $id, $sig) {
            Log::info("$sig start", ['id' => $id, 'input' => $req->all()]);

            $t = microtime(true);
            $req->validate([
                'from'    => 'required',
                'subject' => 'required',
                'content' => 'required',
                'lang'    => 'required',
            ]);
            $this->logExecutionTime($t, "$sig::validate", 'completed');

            $template = EmailTemplate::findOrFail($id);
            $template->update(['from' => $req->from]);

            $langData = $req->only(['lang', 'subject', 'content']);
            $langTpl  = EmailTemplateLang::firstOrNew([
                'parent_id' => $id,
                'lang'      => $langData['lang']
            ]);
            $langTpl->fill($langData)->save();

            Log::info("$sig lang saved", ['parent_id' => $id, 'lang' => $langData['lang']]);
            return redirect()->route(VW::EMLS . '.manage.language', [$id, $langData['lang']])
                ->with('success', __('Email Template successfully updated.'));
        });
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($id, $sig) {
            Log::info("$sig denied", ['id' => $id]);
            return redirect(self::REDIRECT_BACK)->with('error', __('Permission denied.'));
        });
    }

    public const MNG_EM_LNG = 'manageEmailLang';
    public function manageEmailLang(int|string $id, string $lang = DC::DEFAULT_LANG): Response|RedirectResponse
    {
        $action   = __FUNCTION__;
        $cls      = static::class;
        $sig      = "$cls::$action";
        $viewPath = VW::EML_TMP . '.show';

        return $this->measureProfile($action, function () use ($id, $lang, $sig, $viewPath) {
            $user = Auth::user();
            Log::info("$sig start", ['id' => $id, 'lang' => $lang, 'user_id' => $user?->id]);

            if ($user->type != PMC::SA) {
                return redirect(self::REDIRECT_BACK)->with('error', __('Permission denied.'));
            }

            $t = microtime(true);
            $languages     = Utility::languages();
            $langName      = Language::where('code', $lang)->first();
            $template      = EmailTemplate::findOrFail($id);
            $langTemplate  = EmailTemplateLang::firstOrNew(['parent_id' => $id, 'lang' => $lang]);
            $allTemplates  = EmailTemplate::all();
            $this->logExecutionTime($t, "$sig::fetchData", 'completed');

            $t = microtime(true);
            if (!ViewFacade::exists($viewPath)) {
                $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');
                return redirect()->back()->with('error', "HTTP 404: Page {$viewPath} not found!");
            }
            $this->logExecutionTime($t, "$sig::viewExistsCheck", 'completed');

            return response()->view($viewPath, compact(
                'template',
                DC::TABLE_LANGS,
                'langTemplate',
                'allTemplates',
                'langName',
                'languages'
            ));
        });
    }

    public const STR_EM_LNG = 'storeEmailLang';
    public function storeEmailLang(Request $req, int|string $id): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($req, $id, $sig) {
            Log::info("$sig start", ['id' => $id, 'input' => $req->all()]);

            $t = microtime(true);
            $req->validate(['subject' => 'required', 'content' => 'required', 'lang' => 'required']);
            $this->logExecutionTime($t, "$sig::validate", 'completed');

            $data    = $req->only(['lang', 'subject', 'content']);
            $langTpl = EmailTemplateLang::firstOrNew([
                'parent_id' => $id,
                'lang'      => $data['lang']
            ]);
            $langTpl->fill($data)->save();

            Log::info("$sig upsert", ['parent_id' => $id, 'lang' => $data['lang']]);
            return redirect()->route(VW::EMLS . '.manage.language', [$id, $data['lang']])
                ->with('success', __('Email Template Detail successfully updated.'));
        });
    }

    public const UPD_STT = 'updateStatus';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function updateStatus(Request $req): RedirectResponse
    {
        $action = __FUNCTION__;
        $cls    = static::class;
        $sig    = "$cls::$action";

        return $this->measureProfile($action, function () use ($req, $sig) {
            $user = Auth::user();
            Log::info("$sig start", ['user_id' => $user?->id, 'input' => $req->all()]);

            if (!in_array($user?->type, [PMC::SA, PMC::CPN])) {
                Log::warning("$sig denied", ['user_id' => $user?->id]);
                return redirect(self::REDIRECT_BACK)->with('error', __('Permission Denied.'));
            }

            $statuses = $req->except('_token');

            $t = microtime(true);
            DB::transaction(function () use ($user, $statuses) {
                // Reset then apply toggles
                UserEmailTemplate::where('user_id', $user->id)->update([EC::COL_IA => 0]);
                collect($statuses)->each(function ($active, $tplId) use ($user) {
                    UserEmailTemplate::updateOrCreate(
                        ['user_id' => $user?->id, 'template_id' => $tplId],
                        [EC::COL_IA => (int) (bool) $active]
                    );
                });
            });
            $this->logExecutionTime($t, "$sig::transaction", 'completed');

            Log::info("$sig complete", ['user_id' => $user?->id]);
            return redirect(self::REDIRECT_BACK)->with('success', __('Status successfully updated!'));
        });
    }

    protected function _authorize(Request $req, string $perm): ?RedirectResponse
    {
        $user = $req->user();
        if (!$user?->can($perm)) {
            Log::warning('Permission denied', [
                'method'     => __METHOD__,
                'user_id'    => $user?->id,
                'permission' => $perm,
            ]);
            return defaultPermissionDenial($req, new \Exception($perm), __CLASS__ . '::authorize');
        }
        Log::info('Permission granted', ['method' => __METHOD__, 'perm' => $perm, 'user_id' => $user?->id]);
        return null;
    }
}
