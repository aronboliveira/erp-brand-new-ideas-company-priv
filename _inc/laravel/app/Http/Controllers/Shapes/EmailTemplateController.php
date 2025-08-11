<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    EmailsConstants,
    MiddlewaresConstants,
    PermissionsConstants
};
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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Log,
    Validator
};

class EmailTemplateController extends Controller
{
    private const REDIRECT_BACK = 'back';
    private const SINGULAR = 'email_template';

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    }

    public function index(): Response|RedirectResponse
    {
        $user = Auth::user();
        Log::info('EmailTemplate:' . __FUNCTION__, ['user_id' => $user?->id]);
        if (!in_array($user?->type, [PermissionsConstants::SA, PermissionsConstants::CPN])) {
            Log::warning('EmailTemplate:index denied', ['user_id' => $user?->id]);
            return redirect()->route(self::REDIRECT_BACK)->with('error', __('Permission denied.'));
        }
        $templates = EmailTemplate::all();
        return response()->view(DatabaseConstants::TABLE_SETTINGS . '.company', compact('templates'));
    }

    public function create(): Response
    {
        Log::info('EmailTemplate:' . __FUNCTION__);
        return response()->view(self::SINGULAR . '.' . __FUNCTION__);
    }

    public function store(Request $req): RedirectResponse
    {
        Log::info('EmailTemplate:' . __FUNCTION__, ['input' => $req->all()]);
        $req->validate([EmailsConstants::COL_TT => 'required']);
        $user = Auth::user();
        $tpl = EmailTemplate::create([
            EmailsConstants::COL_TT       => $req->name,
            EmailsConstants::COL_FROM       => config('app.name'),
            EmailsConstants::COL_SLG           => Str::slug($req->input(EmailsConstants::COL_TT)) ?? '',
            DatabaseConstants::TABLE_CREATOR => $user?->id,
        ]);
        Log::info('EmailTemplate created', ['id' => $tpl->id]);
        return redirect()->route(self::SINGULAR . 'index')
            ->with('success', __('Email Template successfully created.'));
    }

    public function show(int $id): RedirectResponse
    {
        Log::info('EmailTemplate:' . __FUNCTION__, ['id' => $id]);
        return redirect()->route(self::REDIRECT_BACK)
            ->with('error', __('Permission denied.'));
    }

    public function edit(int $id): RedirectResponse
    {
        Log::info('EmailTemplate:' . __FUNCTION__, ['id' => $id]);
        return redirect()->route(self::REDIRECT_BACK)
            ->with('error', __('Permission denied.'));
    }

    public function update(Request $req, int $id): RedirectResponse
    {
        Log::info('EmailTemplate:' . __FUNCTION__, ['id' => $id, 'input' => $req->all()]);
        $req->validate([
            'from' => 'required',
            'subject' => 'required',
            'content' => 'required',
            'lang' => 'required',
        ]);
        $template = EmailTemplate::findOrFail($id);
        $template->update(['from' => $req->from]);
        $langData = $req->only(['lang', 'subject', 'content']);
        $langTpl = EmailTemplateLang::firstOrNew([
            'parent_id' => $id, 'lang' => $langData['lang']
        ]);
        $langTpl->fill($langData)->save();
        Log::info('EmailTemplateLang saved', ['parent_id' => $id, 'lang' => $langData['lang']]);
        return redirect()->route('manage.email.language', [$id, $langData['lang']])
            ->with('success', __('Email Template successfully updated.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        Log::info('EmailTemplate:' . __FUNCTION__ . ' denied', ['id' => $id]);
        return redirect()->route(self::REDIRECT_BACK)
            ->with('error', __('Permission denied.'));
    }

    public function manageEmailLang(int $id, string $lang = 'en'): Response|RedirectResponse
    {
        $user = Auth::user();
        Log::info('manageEmailLanguage', ['id' => $id, 'lang' => $lang, 'user_id' => $user?->id]);
        if ($user->type != PermissionsConstants::SA)
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission denied.'));
        $languages     = Utility::languages();
        $langName      = Language::where('code', $lang)->first();
        $template      = EmailTemplate::findOrFail($id);
        $langTemplate  = EmailTemplateLang::where('parent_id', $id)
            ->where('lang', $lang)->firstOrFail();
        $allTemplates  = EmailTemplate::all();
        return response()->view('email_templates.show', compact(
            'template',
            DatabaseConstants::TABLE_LANGS,
            'langTemplate',
            'allTemplates',
            'langName'
        ));
    }

    public function storeEmailLang(Request $req, int $id): RedirectResponse
    {
        Log::info('storeEmailLanguage', ['id' => $id, 'input' => $req->all()]);
        $req->validate(['subject' => 'required', 'content' => 'required', 'lang' => 'required']);
        $data = $req->only(['lang', 'subject', 'content']);
        $langTpl = EmailTemplateLang::firstOrNew([
            'parent_id' => $id, 'lang' => $data['lang']
        ]);
        $langTpl->fill($data)->save();
        Log::info('EmailTemplateLang upsert', ['parent_id' => $id, 'lang' => $data['lang']]);
        return redirect()->route('manage.email.language', [$id, $data['lang']])
            ->with('success', __('Email Template Detail successfully updated.'));
    }

    public function updateStatus(Request $req): RedirectResponse
    {
        $user = Auth::user();
        Log::info(__FUNCTION__ . ' start', ['user_id' => $user?->id, 'input' => $req->all()]);
        if (!in_array($user?->type, [PermissionsConstants::SA, PermissionsConstants::CPN])) {
            Log::warning(__FUNCTION__ . ' denied', ['user_id' => $user?->id]);
            return redirect()->route(self::REDIRECT_BACK)
                ->with('error', __('Permission Denied.'));
        }
        $statuses = $req->except('_token');
        DB::transaction(fn () => [
            UserEmailTemplate::where('user_id', $user->id)->update(['is_active' > 0]),
            collect($statuses)->each(fn ($active, $tplId) => UserEmailTemplate::updateOrCreate(
                ['user_id' => $user?->id, 'template_id' => $tplId],
                ['is_active' => $active]
            ))
        ]);
        Log::info(__FUNCTION__ . ' complete', ['user_id' => $user?->id]);
        return redirect()->route(self::REDIRECT_BACK)
            ->with('success', __('Status successfully updated!'));
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
