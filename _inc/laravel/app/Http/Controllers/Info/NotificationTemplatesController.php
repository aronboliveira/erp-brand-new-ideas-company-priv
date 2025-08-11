<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants,
    ViewsConstants
};
use App\Models\{
    Language,
    NotificationTemplateLangs,
    NotificationTemplates,
    Utility
};
use App\Traits\ChecksLogin;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Log,
    Validator
};
use Symfony\Component\HttpFoundation\Response;

class NotificationTemplatesController extends Controller
{
    use ChecksLogin;

    public function __construct()
    {
        $this->middleware([MiddlewaresConstants::XSS]);
    }

    public function index(Request $request, ?int $id = null, string $lang = 'en'): Response
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->_authorize($request, 'manage notification template');

            $template = $id
                ? NotificationTemplates::find($id)
                : NotificationTemplates::first();
            if (!$template)
                return redirect()->back()->with('error', __('Not exists in notification template.'));

            $languages    = Utility::languages();
            $langName     = Language::where('code', $lang)->first();
            $translation = NotificationTemplateLangs::where('parent_id', $template->id)
                ->where('lang', $lang)
                ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->first()
                ?: NotificationTemplateLangs::where('parent_id', $template->id)
                ->where('lang', $lang)
                ->first()
                ?: tap(
                    NotificationTemplateLangs::where('parent_id', $template->id)
                        ->where('lang', DatabaseConstants::DEFAULT_LANG)
                        ->first(),
                    function ($t) use ($lang) {
                        if ($t) $t->lang = $lang;
                    }
                );
            $allTemplates = NotificationTemplates::all();
            return view(ViewsConstants::NTF_TMP . '.' . __FUNCTION__, [
                'notificationTemplate'      => $template,
                'notificationTemplates'     => $allTemplates,
                'currentTemplateLang'       => $translation,
                DatabaseConstants::TABLE_LANGS                 => $languages,
                'langName'                  => $langName,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Update or create a translation for a notification template.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->_authorize($request, 'edit notification template');

            $validator = Validator::make($request->all(), ['content' => 'required']);
            if ($validator->fails())
                return redirect()->back()->with('error', $validator->errors()->first());

            $lang     = $request->input('lang');
            $content  = $request->input('content');
            $creatorId = $request->user()->creatorId();

            $record = NotificationTemplateLangs::where('parent_id', $id)
                ->where('lang', $lang)
                ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
                ->first();
            if (!$record) {
                $variables = NotificationTemplateLangs::where('parent_id', $id)
                    ->where('lang', $lang)
                    ->first()
                    ?->variables;
                $record = new NotificationTemplateLangs();
                $record->parent_id = $id;
                $record->lang      = $lang;
                $record->variables = $variables;
                $record->created_by = $creatorId;
            }
            $record->content = $content;
            $record->save();
            Log::info('Notification template lang saved', ['id' => $record->id]);

            return redirect()->route('notification-templates.index', [$id, $lang])
                ->with('success', __('Notification Template successfully updated.'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return defaultPermissionDenial($request, $e, __CLASS__ . '::' . __FUNCTION__);
        } catch (\Throwable $e) {
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }
}
