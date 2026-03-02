<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MiddlewaresConstants as MWC,
    ViewsConstants as VW
};
use App\Models\{
    Language,
    NotificationTemplateLang,
    NotificationTemplate,
    Utility
};
use App\Traits\ChecksLogin;
use App\Traits\ChecksPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Log,
    Validator,
    View as ViewFacade
};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};

class NotificationTemplateController extends Controller
{
    use ChecksLogin, ChecksPermissions;
    public const IDX = 'index';
    public const UPD = 'update';


    public function __construct()
    {
        $this->middleware([MWC::XSS]);
    }

    public function index(Request $request, int|string|null $id = null, string $lang = DC::DEFAULT_LANG): View|Response
    {
        $cls    = __CLASS__;
        $action = __FUNCTION__;
        $view   = VW::NTF_TMP . '.index';

        return $this->measureProfile("$cls::$action", function () use ($request, $id, $lang, $cls, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($denial = $this->guard($request, 'manage notification template')) !== true) return $denial;
                $template = $id ? NotificationTemplate::find($id) : NotificationTemplate::first();
                if (!$template) return redirect()->back()->with('error', __('Not exists in notification template.'));
                $languages  = Utility::languages();
                $langName   = Language::where('code', $lang)->first();
                $translation = NotificationTemplateLang::where('parent_id', $template->id)
                    ->where('lang', $lang)
                    ->where(DC::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->first()
                    ?: NotificationTemplateLang::where('parent_id', $template->id)
                    ->where('lang', $lang)
                    ->first()
                    ?: tap(
                        NotificationTemplateLang::where('parent_id', $template->id)
                            ->where('lang', DC::DEFAULT_LANG)
                            ->first(),
                        function ($t) use ($lang) {
                            if ($t) $t->lang = $lang;
                        }
                    );

                $allTemplates = NotificationTemplate::all();

                if (!ViewFacade::exists($view))
                    return defaultUndefinedException($request, new \RuntimeException('View not found'), "$cls::$action");

                return view($view, [
                    'notificationTemplate'  => $template,
                    'notificationTemplates' => $allTemplates,
                    'currentTemplateLang'   => $translation,
                    DC::TABLE_LANGS => $languages,
                    'langName'              => $langName,
                ]);
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    /**
     * Update or create a translation for a notification template.
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $cls    = __CLASS__;
        $action = __FUNCTION__;
        $route  = VW::NTF_TMP . '.index';

        return $this->measureProfile("$cls::$action", function () use ($request, $id, $cls, $action, $route) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (!($resp = $this->guard($request, 'edit notification template'))) return $resp;

                $v = Validator::make($request->all(), ['content' => 'required']);
                if ($v->fails()) return redirect()->back()->with('error', $v->errors()->first());

                $lang      = $request->input('lang');
                $content   = $request->input('content');
                $creatorId = $request->user()->creatorId();

                $record = NotificationTemplateLang::where('parent_id', $id)
                    ->where('lang', $lang)
                    ->where(DC::COL_TABLE_CREATOR, $creatorId)
                    ->first();

                if (!$record) {
                    $variables = NotificationTemplateLang::where('parent_id', $id)
                        ->where('lang', $lang)
                        ->first()
                        ?->variables;

                    $record = new NotificationTemplateLang();
                    $record->parent_id  = $id;
                    $record->lang       = $lang;
                    $record->variables  = $variables;
                    $record->created_by = $creatorId;
                }

                $record->content = $content;
                $record->save();
                Log::info('Notification template lang saved', ['id' => $record->id]);

                return redirect()->route($route, [$id, $lang])
                    ->with('success', __('Notification Template successfully updated.'));
            } catch (AuthorizationException $e) {
                return defaultPermissionDenial($request, $e, "$cls::$action");
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$cls::$action");
            }
        });
    }

    public const CRT = 'create';
    /**
     * Show the form for creating a new notification template.
     * Note: Templates are predefined, so this redirects to index.
     */
    public function create(Request $request): RedirectResponse
    {
        return redirect()->route(VW::NTF_TMP . '.index')
            ->with('info', __('Notification templates are predefined. Please select a template to edit.'));
    }

    public const STR = 'store';
    /**
     * Store a new notification template.
     * Note: Templates are predefined, so this redirects to index.
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect()->route(VW::NTF_TMP . '.index')
            ->with('info', __('Notification templates are predefined and cannot be created.'));
    }

    public const SHW = 'show';
    /**
     * Display a notification template.
     */
    public function show(Request $request, int|string $notificationTemplate): View|RedirectResponse
    {
        return $this->index($request, (int)$notificationTemplate);
    }

    public const EDT = 'edit';
    /**
     * Show the form for editing a notification template.
     */
    public function edit(Request $request, int|string $notificationTemplate): View|RedirectResponse
    {
        return $this->index($request, (int)$notificationTemplate);
    }

    public const DEL = 'destroy';
    /**
     * Delete a notification template.
     * Note: Templates are predefined and cannot be deleted.
     */
    public function destroy(Request $request, int|string $notificationTemplate): RedirectResponse
    {
        return redirect()->route(VW::NTF_TMP . '.index')
            ->with('error', __('Notification templates are predefined and cannot be deleted.'));
    }
}
