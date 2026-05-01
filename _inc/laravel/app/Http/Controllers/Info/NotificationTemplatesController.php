<?php

namespace App\Http\Controllers\Info;

use App\Http\Controllers\Abstracts\Controller;
use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    ViewsConstants
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
use App\Traits\DefinesResourceActions;
class NotificationTemplateController extends Controller
{
	use DefinesResourceActions;

    use ChecksLogin, ChecksPermissions;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::XSS]);
    }

    public function index(Request $request, int|string $id, string $lang = DatabaseConstants::DEFAULT_LANG): View|Response
    {
        $cls    = __CLASS__;
        $action = __FUNCTION__;
        $view   = ViewsConstants::NTF_TMP . '.index';

        return $this->measureProfile("$cls::$action", function () use ($request, $id, $lang, $cls, $action, $view) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (!($denial = $this->guard($request, 'manage notification template'))) return $denial;
                $template = $id ? NotificationTemplate::find($id) : NotificationTemplate::first();
                if (!$template) return redirect()->back()->with('error', __('Not exists in notification template.'));
                $languages  = Utility::languages();
                $langName   = Language::where('code', $lang)->first();
                $translation = NotificationTemplateLang::where('parent_id', $template->id)
                    ->where('lang', $lang)
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $request->user()->creatorId())
                    ->first()
                    ?: NotificationTemplateLang::where('parent_id', $template->id)
                    ->where('lang', $lang)
                    ->first()
                    ?: tap(
                        NotificationTemplateLang::where('parent_id', $template->id)
                            ->where('lang', DatabaseConstants::DEFAULT_LANG)
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
                    DatabaseConstants::TABLE_LANGS => $languages,
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
        $route  = ViewsConstants::NTF_TMP . '.index';

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
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $creatorId)
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
}
