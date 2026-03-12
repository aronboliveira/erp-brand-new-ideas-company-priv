<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants as VW
};
use App\Mail\TestMail;
use App\Models\{
    Config,
    EmailTemplate,
    ExperienceCertificate,
    GeneratedOfferLetter,
    IpRestrict,
    JoiningLetter,
    Language,
    Noc,
    User,
    Utility,
    WebhookSettings
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Artisan, Auth, DB, File, Log, Mail, Validator, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class SystemController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = VW::SET . '.index';
    private const REDIRECT_COMPANY = VW::SET . '.' . PermissionsConstants::CPN;

    public function index(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $settings = Utility::settings();
                $adminPaymentSetting = Utility::getAdminPaymentSetting();
                $totalBytes = 0;
                foreach (File::allFiles(storage_path('framework')) as $f) $totalBytes += $f->getSize();
                $fileSize = number_format($totalBytes / 1_000_000, 4);
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'fileSizeMB' => $fileSize]);
                $view = VW::SET . '.' . $action;
                if (ViewFacade::exists($view)) {
                    return ViewFacade::make($view, compact(DatabaseConstants::TABLE_SETTINGS, 'adminPaymentSetting', 'fileSize'));
                }
                Log::warning(__METHOD__ . ' view missing', ['view' => $view]);
                return defaultUndefinedException($request, new \RuntimeException('View not found'), __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                DB::transaction(function () use ($request, $user) {
                    $uploads = [
                        SettingsConstants::CPN_LG_LT  => SettingsConstants::CPN_LG_LT_DEF,
                        SettingsConstants::CPN_LG_DK  => SettingsConstants::CPN_LG_DK_DEF,
                        SettingsConstants::FAV_ICN    => 'favicon.png',
                    ];
                    $dir = 'uploads/logo/';
                    foreach ($uploads as $field => $filename) {
                        if (!$request->hasFile($field)) continue;
                        $path = Utility::uploadFile($request, $field, $filename, $dir, ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]);
                        if (($path['flag'] ?? 0) !== 1) throw new \RuntimeException($path['msg'] ?? 'Upload failed');
                        $uploads[$field] = $path['url'];
                    }
                    $post = $request->except('_token');
                    foreach (['SITE_RTL', 'displayLandingPage', 'gdprCookie', 'enableSignup', 'emailVerification', 'custThemeBg', 'custDarklayout'] as $b) {
                        $post[$b] = $request->has($b) ? 'on' : 'off';
                    }
                    $this->saveSettings($post, $user?->creatorId());
                });
                Log::debug(__METHOD__ . ' completed', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Brand setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_EM_ST = 'saveEmailSettings';
    public function saveEmailSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $data = $request->validate([
                    'mailDriver'      => 'required|string|max:255',
                    'mailHost'        => 'required|string|max:255',
                    'mailPort'        => 'required|string|max:255',
                    'mailUsername'    => 'required|string|max:255',
                    'mailPassword'    => 'required|string|max:255',
                    'mailEncryption'  => 'required|string|max:255',
                    'mailFromAddress' => 'required|string|max:255',
                    'mailFromName'    => 'required|string|max:255',
                ]);
                DB::transaction(fn() => $this->saveSettings($data, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_CP_EM_ST = 'saveCompanyEmailSettings';
    public function saveCompanyEmailSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($user->type != PermissionsConstants::CPN) return defaultPermissionDenial($request, new \Exception('Not a ' . PermissionsConstants::CPN . ' user'), __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $data = $request->validate([
                    'mailDriver'      => 'required|string|max:255',
                    'mailHost'        => 'required|string|max:255',
                    'mailPort'        => 'required|string|max:255',
                    'mailUsername'    => 'required|string|max:255',
                    'mailPassword'    => 'required|string|max:255',
                    'mailEncryption'  => 'required|string|max:255',
                    'mailFromAddress' => 'required|string|max:255',
                    'mailFromName'    => 'required|string|max:255',
                ]);
                DB::transaction(fn() => $this->saveSettings($data, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_CP_ST = 'saveCompanySettings';
    public function saveCompanySettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $data = $request->validate(['companyName' => 'required|string|max:255']);
                $data['vatGstNumberSwitch'] = $request->has('vatGstNumberSwitch') ? 'on' : 'off';
                $data['ipRestrict'] = $request->has('ipRestrict') ? 'on' : 'off';
                DB::transaction(fn() => $this->saveSettings($data, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_PAY_ST = 'savePaymentSettings';
    public function savePaymentSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage stripe ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $validator = Validator::make($request->all(), [
                'currency'       => 'required|string|max:255',
                'currencySymbol' => 'required|string|max:255',
            ]);
            if ($validator->fails()) return redirect()->back()->with('error', $validator->getMessageBag()->first());
            try {
                DB::transaction(fn() => $this->adminPaymentSettings($request));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Payment setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_SYS_ST = 'saveSystemSettings';
    public function saveSystemSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $data = $request->validate(['siteCurrency' => 'required']);
                $data['shippingDisplay'] = $request->has('shippingDisplay') ? 'on' : 'off';
                DB::transaction(fn() => $this->saveSettings($data, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_ZM_ST = 'saveZoomSettings';
    public function saveZoomSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $data = $request->except(['_token']);
                DB::transaction(fn() => $this->saveSettings($data, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Setting successfully saved.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_BS_ST = 'saveBusinessSettings';
    public function saveBusinessSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage business ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                DB::transaction(function () use ($request, $user) {
                    $files = [SettingsConstants::CPN_LG_DK, SettingsConstants::CPN_LG_LT, SettingsConstants::CPN_FAVICON_K];
                    $dir = 'uploads/logo/';
                    foreach ($files as $field) {
                        if (!$request->hasFile($field)) continue;
                        $filename = $user?->id . '-' . lcfirst($field) . '.png';
                        $path = Utility::uploadFile($request, $field, $filename, $dir, ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]);
                        if (($path['flag'] ?? 0) !== 1) throw new \RuntimeException($path['msg'] ?? 'Upload failed');
                        $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
                        DB::insert(
                            'insert into settings (`value`,`name`,`' . $creatorCol . '`) values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                            [$filename, lcfirst($field), $user?->creatorId()]
                        );
                    }
                    $data = $request->except(array_merge(['_token'], $files));
                    foreach (['SITE_RTL', 'custThemeBg', 'custDarklayout'] as $b) $data[$b] = $request->has($b) ? 'on' : 'off';
                    $this->saveSettings($data, $user?->creatorId());
                });
                Log::debug($action . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Brand setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const CP = 'companyIndex';
    public function companyIndex(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_COMPANY)) !== true) return $redirect;
            Log::debug($action . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $offer = $request->input('offerlangs', 'en');
                $joining = $request->input('joininglangs', 'en');
                $exp = $request->input('explangs', 'en');
                $noc = $request->input('noclangs', 'en');
                $data = ['offerLang' => $offer, 'joiningLang' => $joining, 'expLang' => $exp, 'nocLang' => $noc];
                foreach ($data as $k => $v) $data[$k . 'Name'] = Language::languageData($v);
                $viewData = array_merge($data, [
                    'setting' => Utility::settingsById($user?->creatorId()),
                    'timezones' => config('timezones'),
                    'companyPaymentSetting' => Utility::getCompanyPaymentSetting($user?->creatorId()),
                    'emailTemplates' => EmailTemplate::all(),
                    'ips' => IpRestrict::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get(),
                    'offerLetters' => GeneratedOfferLetter::all(),
                    'currOfferLetter' => GeneratedOfferLetter::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->id)->where('lang', $offer)->first(),
                    'joiningLetters' => JoiningLetter::all(),
                    'currJoiningLetter' => JoiningLetter::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->id)->where('lang', $joining)->first(),
                    'expCertificates' => ExperienceCertificate::all(),
                    'currExpCert' => ExperienceCertificate::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->id)->where('lang', $exp)->first(),
                    'nocCertificates' => Noc::all(),
                    'currNocCert' => Noc::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->id)->where('lang', $noc)->first(),
                ]);
                Log::debug($action . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                $view = VW::SET . '.company';
                if (ViewFacade::exists($view)) {
                    return ViewFacade::make($view, $viewData);
                }
                Log::warning($action . ' view missing', ['view' => $view]);
                return defaultUndefinedException($request, new \RuntimeException('View not found'), $action, route(self::REDIRECT_COMPANY)); // ! ALERT
            } catch (\Throwable $e) {
                Log::error($action . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_COMPANY)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_CP_PAY_ST = 'saveCompanyPaymentSettings';
    public function saveCompanyPaymentSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_COMPANY)) !== true) return $redirect;
            Log::debug($action . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                DB::transaction(function () use ($request, $user) {
                    $methods = [
                        'bankTransfer' => ['bank_details'],
                        'stripe' => ['stripe_key', 'stripe_secret'],
                        'xendit' => ['xendit_token', 'xendit_api'],
                    ];
                    $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
                    foreach ($methods as $k => $fields) {
                        $enable = 'is_' . $k . '_enabled';
                        if ($request->input($enable) === 'on') {
                            $rules = [];
                            foreach ($fields as $f) $rules[$f] = 'required|string';
                            $request->validate($rules);
                            DB::insert(
                                'insert into company_payment_settings (`value`,`name`,`' . $creatorCol . '`) values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                                ['on', $enable, $user?->id]
                            );
                            foreach ($fields as $f) {
                                DB::insert(
                                    'insert into company_payment_settings (`value`,`name`,`' . $creatorCol . '`) values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                                    [$request->input($f), $f, $user?->id]
                                );
                            }
                        } else {
                            DB::insert(
                                'insert into company_payment_settings (`value`,`name`,`' . $creatorCol . '`) values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                                ['off', $enable, $user?->id]
                            );
                        }
                    }
                });
                Log::debug($action . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Payment setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_COMPANY)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const TT_MAIL = 'testMail';
    public function testMail(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $data = $request->only([
                    'mail_driver',
                    'mail_host',
                    'mail_port',
                    'mail_username',
                    'mail_password',
                    'mail_encryption',
                    'mail_from_address',
                    'mail_from_name'
                ]);
                $view = VW::SET . '.test_mail';
                if (ViewFacade::exists($view)) {
                    Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                    return ViewFacade::make($view, compact('data'));
                }
                Log::warning(__METHOD__ . ' view missing', ['view' => $view]);
                return defaultUndefinedException($request, new \RuntimeException('View not found'), __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const TT_SMAIL = 'testSendMail';
    public function testSendMail(Request $request): JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $data = $request->validate([
                'email'             => 'required|email',
                'mail_driver'       => 'required|string',
                'mail_host'         => 'required|string',
                'mail_port'         => 'required|string',
                'mail_username'     => 'required|string',
                'mail_password'     => 'required|string',
                'mail_encryption'   => 'nullable|string',
                'mail_from_address' => 'required|string',
                'mail_from_name'    => 'required|string',
            ]);
            try {
                config([
                    'mail.driver'       => $data['mail_driver'],
                    'mail.host'         => $data['mail_host'],
                    'mail.port'         => $data['mail_port'],
                    'mail.encryption'   => $data['mail_encryption'] ?? null,
                    'mail.username'     => $data['mail_username'],
                    'mail.password'     => $data['mail_password'],
                    'mail.from.address' => $data['mail_from_address'],
                    'mail.from.name'    => $data['mail_from_name'],
                ]);
                Mail::to($data['email'])->send(new TestMail());
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return response()->json(['success' => true, 'message' => __('Email sent successfully')], 200);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const PRT = 'printIndex';
    public function printIndex(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage print ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $settings = Utility::settings();
                $view = VW::SET . '.print';
                if (ViewFacade::exists($view)) {
                    Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                    return ViewFacade::make($view, compact('' . DatabaseConstants::TABLE_SETTINGS));
                }
                Log::warning(__METHOD__ . ' view missing', ['view' => $view]);
                return defaultUndefinedException($request, new \RuntimeException('View not found'), __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const POS_PRT = 'posPrintIndex';
    public function posPrintIndex(Request $request): View|RedirectResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage print ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $settings = Utility::settings();
                $view = VW::SET . '.pos';
                if (ViewFacade::exists($view)) {
                    Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                    return ViewFacade::make($view, compact('' . DatabaseConstants::TABLE_SETTINGS));
                }
                Log::warning(__METHOD__ . ' view missing', ['view' => $view]);
                return defaultUndefinedException($request, new \RuntimeException('View not found'), __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const ADM_PAY_ST = 'adminPaymentSettings';
    public function adminPaymentSettings(Request $request): void
    {
        Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $request->user()->id]);
        try {
            DB::transaction(function () use ($request) {
                $settings = [
                    'currency'       => $request->input('currency'),
                    'currencySymbol' => $request->input('currency_symbol')
                ];
                $methods = [
                    'manuallyPayment' => [],
                    'bankTransfer'    => ['bank_details'],
                    'stripe'          => ['stripe_key', 'stripe_secret'],
                    'paypal'          => ['paypal_mode', 'paypal_client_id', 'paypal_secret_key'],
                    'paystack'        => ['paystack_public_key', 'paystack_secret_key'],
                    'flutterwave'     => ['flutterwave_public_key', 'flutterwave_secret_key'],
                    'razorpay'        => ['razorpay_public_key', 'razorpay_secret_key'],
                    'mercado'         => ['mercado_access_token', 'mercado_mode'],
                    'paytm'           => ['paytm_mode', 'paytm_merchant_id', 'paytm_merchant_key', 'paytm_industry_type'],
                    'mollie'          => ['mollie_api_key', 'mollie_profile_id', 'mollie_partner_id'],
                    'skrill'          => ['skrill_email'],
                    'coingate'        => ['coingate_mode', 'coingate_auth_token'],
                    'paymentwall'     => ['paymentwall_public_key', 'paymentwall_secret_key'],
                    'toyyibpay'       => ['toyyibpay_category_code', 'toyyibpay_secret_key']
                ];
                foreach ($methods as $key => $fields) {
                    $flag = 'is_' . $key . '_enabled';
                    $enabled = $request->input($flag) === 'on' ? 'on' : 'off';
                    $settings[$flag] = $enabled;
                    if ($enabled === 'on' && $fields) {
                        $rules = [];
                        foreach ($fields as $f) $rules[$f] = 'required|string';
                        $request->validate($rules);
                        foreach ($fields as $f) $settings[$f] = $request->input($f);
                    }
                }
                $userId = $request->user()->creatorId();
                $creatorCol = DatabaseConstants::COL_TABLE_CREATOR;
                foreach ($settings as $name => $value) {
                    DB::insert(
                        'insert into admin_payment_settings (`value`,`name`,`' . $creatorCol . '`) values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                        [$value, $name, $userId]
                    );
                }
            });
            Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $request->user()->id]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $request->user()->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public const SV_PSR_ST = 'savePusherSettings';
    public function savePusherSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if ($user->type != PermissionsConstants::SA) return defaultPermissionDenial($request, new \Exception('Unauthorized'), __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $data = $request->validate([
                'pusher_app_id'      => 'required',
                'pusher_app_key'     => 'required',
                'pusher_app_secret'  => 'required',
                'pusher_app_cluster' => 'required'
            ]);
            $settings = [
                'pusherAppId'      => $data['pusher_app_id'],
                'pusherAppKey'     => $data['pusher_app_key'],
                'pusherAppSecret'  => $data['pusher_app_secret'],
                'pusherAppCluster' => $data['pusher_app_cluster']
            ];
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Pusher Settings updated successfully'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_SLK_ST = 'saveSlackSettings';
    public function saveSlackSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $settings = ['slackWebhook' => $request->input('slack_webhook')];
            $flags = [
                'leadNotification',
                'dealNotification',
                'leadtodealNotification',
                'contractNotification',
                'projectNotification',
                'taskNotification',
                'taskmoveNotification',
                'taskcommentNotification',
                'payslipNotification',
                'awardNotification',
                'announcementNotification',
                'holidayNotification',
                'supportNotification',
                'eventNotification',
                'meetingNotification',
                'policyNotification',
                'invoiceNotification',
                'revenueNotification',
                'billNotification',
                'paymentNotification',
                'budgetNotification'
            ];
            foreach ($flags as $flag) {
                $snake = Str::snake($flag);
                $settings[$flag] = $request->has($snake) ? 1 : 0;
            }
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Slack updated successfully.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_TLG_ST = 'saveTelegramSettings';
    public function saveTelegramSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $settings = [
                'telegramAccesToken' => $request->input('telegram_accesstoken'),
                'telegramChatId'     => $request->input('telegram_chatid')
            ];
            $flags = [
                'telegramLeadNotification',
                'telegramDealNotification',
                'telegramLeadtodealNotification',
                'telegramContractNotification',
                'telegramProjectNotification',
                'telegramTaskNotification',
                'telegramTaskmoveNotification',
                'telegramTaskcommentNotification',
                'telegramPayslipNotification',
                'telegramAwardNotification',
                'telegramAnnouncementNotification',
                'telegramHolidayNotification',
                'telegramSupportNotification',
                'telegramEventNotification',
                'telegramMeetingNotification',
                'telegramPolicyNotification',
                'telegramInvoiceNotification',
                'telegramRevenueNotification',
                'telegramBillNotification',
                'telegramPaymentNotification',
                'telegramBudgetNotification'
            ];
            foreach ($flags as $flag) {
                $snake = Str::snake($flag);
                $settings[$flag] = $request->has($snake) ? 1 : 0;
            }
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Telegram updated successfully.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_TWL_ST = 'saveTwilioSettings';
    public function saveTwilioSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $settings = [
                'twilioSid'   => $request->input('twilio_sid'),
                'twilioToken' => $request->input('twilio_token'),
                'twilioFrom'  => $request->input('twilio_from')
            ];
            $flags = [
                'twilioCustomerNotification',
                'twilioVendorNotification',
                'twilioInvoiceNotification',
                'twilioRevenueNotification',
                'twilioBillNotification',
                'twilioProposalNotification',
                'twilioPaymentNotification',
                'twilioReminderNotification'
            ];
            foreach ($flags as $flag) {
                $snake = Str::snake($flag);
                $settings[$flag] = $request->has($snake) ? 1 : 0;
            }
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Twilio updated successfully.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const RCP_ST_STR = 'recaptchaSettingStore';
    public function recaptchaSettingStore(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $settings = [];
            if ($request->has(SettingsConstants::RCPT_MDL) && $request->input(SettingsConstants::RCPT_MDL) === 'on') {
                $data = $request->validate([
                    SettingsConstants::G_RCPT_K   => 'required|string|max:50',
                    SettingsConstants::G_RCPT_SC  => 'required|string|max:50'
                ]);
                $settings = [
                    SettingsConstants::RCPT_MDL   => 'on',
                    SettingsConstants::G_RCPT_K   => $data[SettingsConstants::G_RCPT_K],
                    SettingsConstants::G_RCPT_SC  => $data[SettingsConstants::G_RCPT_SC]
                ];
            } else {
                $settings = [
                    SettingsConstants::RCPT_MDL   => 'off',
                    SettingsConstants::G_RCPT_K   => $request->input(SettingsConstants::G_RCPT_K, ''),
                    SettingsConstants::G_RCPT_SC  => $request->input(SettingsConstants::G_RCPT_SC, '')
                ];
            }
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Recaptcha Settings updated successfully'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const STG_ST_STR = 'storageSettingStore';
    public function storageSettingStore(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $post = [];
            $type = $request->input(SettingsConstants::STR_STT);
            if ($type === SettingsConstants::LC) {
                $data = $request->validate([
                    SettingsConstants::LC_ST_VL  => 'required|array',
                    SettingsConstants::LC_ST_M_UP => 'required|integer',
                ]);
                $post = [
                    SettingsConstants::STR_STT     => SettingsConstants::LC,
                    SettingsConstants::LC_ST_VL    => implode(',', $data[SettingsConstants::LC_ST_VL]),
                    SettingsConstants::LC_ST_M_UP  => $data[SettingsConstants::LC_ST_M_UP],
                ];
            } elseif ($type === SettingsConstants::S3 || $type === SettingsConstants::WSB) {
                $fields = $type === SettingsConstants::S3
                    ? [
                        SettingsConstants::S3_K,
                        SettingsConstants::S3_SC,
                        SettingsConstants::S3_RG,
                        SettingsConstants::S3_BK,
                        SettingsConstants::S3_URL,
                        SettingsConstants::S3_EP,
                        SettingsConstants::S3_M_UP,
                        SettingsConstants::S3_STG_VL,
                    ]
                    : [
                        SettingsConstants::WSB_K,
                        SettingsConstants::WSB_SC,
                        SettingsConstants::WSB_RG,
                        SettingsConstants::WSB_BK,
                        SettingsConstants::WSB_URL,
                        SettingsConstants::WSB_RT,
                        SettingsConstants::WSB_M_UP,
                        SettingsConstants::WSB_STG_VL,
                    ];
                $rules = array_fill_keys($fields, 'required|string');
                $validationField = $type === SettingsConstants::S3 ? SettingsConstants::S3_STG_VL : SettingsConstants::WSB_STG_VL;
                $rules[$validationField] = 'required|array';
                $data = $request->validate($rules);
                $post = [SettingsConstants::STR_STT => $type];
                foreach ($fields as $field) {
                    $key = Str::studly($field);
                    $post[$key] = $field === $validationField ? implode(',', $data[$field]) : $data[$field];
                }
            }
            try {
                DB::transaction(fn() => $this->saveSettings($post, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Storage setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const OF_LTR_UPD = 'offerLetterUpdate';
    public function offerLetterUpdate(string $lang, Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $lang) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            try {
                DB::transaction(fn() => GeneratedOfferLetter::updateOrCreate(
                    ['lang' => $lang, DatabaseConstants::COL_TABLE_CREATOR => $user?->id],
                    ['content' => $request->input('content', '')]
                ));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
                return response()->json(['is_success' => true, 'success' => __('Offer Letter successfully saved!')], 200);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return response()->json(['is_success' => false, 'message' => $e->getMessage()], 500);
            }
        }, ['lang' => $lang, 'ip' => $request->ip()]);
    }

    public const JN_LTR_UPD = 'joiningLetterUpdate';
    public function joiningLetterUpdate(string $lang, Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $lang) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            try {
                DB::transaction(fn() => JoiningLetter::updateOrCreate(
                    ['lang' => $lang, DatabaseConstants::COL_TABLE_CREATOR => $user?->id],
                    ['content' => $request->input('content', '')]
                ));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
                return response()->json(['is_success' => true, 'success' => __('Joining Letter successfully saved!')], 200);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return response()->json(['is_success' => false, 'message' => $e->getMessage()], 500);
            }
        }, ['lang' => $lang, 'ip' => $request->ip()]);
    }

    public const EXP_CT_UPD = 'experienceCertificateUpdate';
    public function experienceCertificateUpdate(string $lang, Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $lang) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['is_success' => false, 'message' => 'Unauthorized'], 403);
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            try {
                DB::transaction(fn() => ExperienceCertificate::updateOrCreate(
                    ['lang' => $lang, DatabaseConstants::COL_TABLE_CREATOR => $user?->id],
                    ['content' => $request->input('content', '')]
                ));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
                return response()->json(['is_success' => true, 'success' => __('Experience Certificate successfully saved!')], 200);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return response()->json(['is_success' => false, 'message' => $e->getMessage()], 500);
            }
        }, ['lang' => $lang, 'ip' => $request->ip()]);
    }

    public const NOC_UPD = 'nocUpdate';
    public function nocUpdate(string $lang, Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $lang) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['is_success' => false, 'message' => 'Unauthorized'], 403);
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            try {
                DB::transaction(fn() => Noc::updateOrCreate(
                    ['lang' => $lang, DatabaseConstants::COL_TABLE_CREATOR => $user?->id],
                    ['content' => $request->input('content', '')]
                ));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
                return response()->json(['is_success' => true, 'success' => __('NOC successfully saved!')], 200);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return response()->json(['is_success' => false, 'message' => $e->getMessage()], 500);
            }
        }, ['lang' => $lang, 'ip' => $request->ip()]);
    }

    public const SV_GG_CLD_ST = 'saveGoogleCalendarSettings';
    public function saveGoogleCalendarSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $settings = [];
            if ($request->input('google_calendar_enable') === 'on') {
                $data = $request->validate([
                    'google_calendar_json_file' => 'required|file',
                    'google_clender_id'         => 'required|string'
                ]);
                $settings['google_calendar_enable'] = 'on';
                $dir = storage_path('google_calendar') . '/' . md5(now());
                if (!File::isDirectory($dir)) File::makeDirectory($dir, 0755, true, true);
                $file = $request->file('google_calendar_json_file');
                $path = $file->storeAs($dir, preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName()));
                $settings['google_calendar_json_file'] = $path;
                $settings['google_clender_id'] = $data['google_clender_id'];
            } else {
                $settings['google_calendar_enable'] = 'off';
            }
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Google Calendar setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SEO_ST = 'seoSettings';
    public function seoSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'manage system ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $data = $request->validate([
                SettingsConstants::MT_TTL_K => 'required|string',
                SettingsConstants::MT_DSC_K => 'required|string',
                SettingsConstants::MT_IMG_K => 'required|file'
            ]);
            $dir = storage_path('uploads/meta');
            if (!File::isDirectory($dir)) File::makeDirectory($dir, 0755, true, true);
            $image = $request->file(SettingsConstants::MT_IMG_K);
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $image->getClientOriginalName());
            $image->move($dir, $filename);
            $settings = [
                SettingsConstants::MT_TTL_K => $data[SettingsConstants::MT_TTL_K],
                SettingsConstants::MT_DSC_K => $data[SettingsConstants::MT_DSC_K],
                SettingsConstants::MT_IMG_K => $filename
            ];
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('SEO setting successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX)); // ! ALERT
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function webhook(Request $request): RedirectResponse|View|null
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'create webhook', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $webhookSettings = WebhookSettings::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())->get();
                $view = 'webhook.index';
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'count' => $webhookSettings->count()]);
                return ViewFacade::exists($view)
                    ? view($view, compact('webhookSettings'))
                    : defaultUndefinedException($request, new \RuntimeException('View not found'), __METHOD__, route(self::REDIRECT_INDEX));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const WHK_CRT = 'webhookCreate';
    public function webhookCreate(Request $request): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['error' => 'Unauthorized'], 401);
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'create webhook', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                $modules = WebhookSettings::$modules;
                $methods = WebhookSettings::$method;
                $view = 'webhook.create';
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return ViewFacade::exists($view)
                    ? view($view, compact('modules', 'methods'))
                    : response()->json(['error' => 'View not found'], 500);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return response()->json(['error' => 'Server error'], 500);
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const WHK_STR = 'webhookStore';
    public function webhookStore(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, 'create webhook', self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $data = $request->validate([
                'module' => 'required',
                'url'    => 'required|url',
                'method' => 'required'
            ]);
            try {
                WebhookSettings::create([
                    'module'                         => $data['module'],
                    'url'                            => $data['url'],
                    'method'                         => $data['method'],
                    DatabaseConstants::COL_TABLE_CREATOR => $user?->creatorId()
                ]);
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Webhook successfully created.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const WHK_EDT = 'webhookEdit';
    public function webhookEdit(Request $request, int $id): View|JsonResponse|RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $id) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return response()->json(['error' => 'Unauthorized'], 401);
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::ED_WHK, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
            try {
                $webhook = WebhookSettings::findOrFail($id);
                $modules = WebhookSettings::$modules;
                $methods = WebhookSettings::$method;
                $view = 'webhook.edit';
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return ViewFacade::exists($view)
                    ? view($view, compact('webhook', 'modules', 'methods'))
                    : response()->json(['error' => 'View not found'], 500);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const WHK_UPD = 'webhookUpdate';
    public function webhookUpdate(Request $request, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $id) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::ED_WHK, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
            $data = $request->validate([
                'module' => 'required',
                'url'    => 'required|url',
                'method' => 'required'
            ]);
            try {
                $wh = WebhookSettings::findOrFail($id);
                $wh->update([
                    'module' => $data['module'],
                    'url'    => $data['url'],
                    'method' => $data['method']
                ]);
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Webhook successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const WHK_DST = 'webhookDestroy';
    public function webhookDestroy(Request $request, int $id): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $id) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($redirect = self::guard($request, PermissionsConstants::DEL_WHK, self::REDIRECT_INDEX)) !== true) return $redirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
            try {
                WebhookSettings::findOrFail($id)->delete();
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Webhook successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const SV_CK_ST = 'saveCookieSettings';
    public function saveCookieSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            $data = $request->validate([
                'cookie_title'                 => 'required',
                'cookie_description'           => 'required',
                'strictly_cookie_title'        => 'required',
                'strictly_cookie_description'  => 'required',
                'more_information_description' => 'required',
                'contactus_url'                => 'required'
            ]);
            $settings = [];
            foreach (['enableCookie', 'cookieLogging'] as $flag) $settings[$flag] = $request->has(Str::snake($flag)) ? 'on' : 'off';
            foreach (
                [
                    'cookieTitle',
                    'cookieDescription',
                    'strictlyCookieTitle',
                    'strictlyCookieDescription',
                    'moreInformationDescription',
                    'contactusUrl'
                ] as $field
            ) $settings[$field] = $request->input(Str::snake($field));
            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Cookie setting successfully saved.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const CK_CST = 'cookieConsent';
    public function cookieConsent(Request $request): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            $settings = Utility::settings();
            if (($settings['enableCookie'] ?? '') === 'on' && ($settings['cookieLogging'] ?? '') === 'on') {
                try {
                    $allowed = ['necessary', 'analytics', 'targeting'];
                    $levels = array_filter($request->input('cookie', []), fn($l) => in_array($l, $allowed));
                    $parser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
                    $info = [$parser->browser->name ?? '', $parser->os->name ?? ''];
                    $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
                    $device = Utility::getDeviceType($_SERVER['HTTP_USER_AGENT']);
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $geo = @json_decode((string) file_get_contents('https://ip-api.com/json/' . $ip), true) ?: [];
                    $row = implode(',', [
                        $ip,
                        date('Y-m-d'),
                        date('H:i:s') . ' UTC',
                        json_encode($levels),
                        $device,
                        substr($lang, 0, 2),
                        $info[0],
                        $info[1],
                        $geo['country'] ?? '',
                        $geo['region'] ?? '',
                        $geo['regionName'] ?? '',
                        $geo['city'] ?? '',
                        $geo['zip'] ?? '',
                        $geo['lat'] ?? '',
                        $geo['lon'] ?? ''
                    ]);
                    $csv = storage_path('uploads/sample/data.csv');
                    if (!File::exists($csv)) {
                        $header = 'IP,Date,Time,Accepted cookies,Device type,Browser language,Browser name,OS Name,Country,Region,RegionName,City,Zipcode,Lat,Lon';
                        File::append($csv, $header . PHP_EOL);
                    }
                    File::append($csv, $row . PHP_EOL);
                    return response()->json('success');
                } catch (\Throwable $e) {
                    Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
                    return response()->json('error', 500);
                }
            }
            return response()->json('error', 400);
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const CC_ST_STR = 'cacheSettingStore';
    public function cacheSettingStore(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
            try {
                Artisan::call('cache:clear');
                Artisan::call('optimize:clear');
                Log::debug(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
                return redirect()->back()->with('success', __('Cache cleared successfully'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public const FT_NT_STR = 'footerNoteStore';
    public function footerNoteStore(Request $request, ?int $user_id = null): JsonResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $user_id) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) {
                return response()->json(['success' => false], 403);
            }
            $auth = $userOrRedirect;
            $user = $user_id ? User::find($user_id) : $auth;

            Log::info(__METHOD__ . ' started', ['authId' => $auth->id, UsersConstants::COL_USER_ID => $user?->id]);

            $settings = ['footerNotes' => $request->input('notes', '')];

            try {
                DB::transaction(fn() => $this->saveSettings(
                    $settings,
                    $user?->creatorId() // store per-company like other settings
                ));
                Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);

                return response()->json([
                    'is_success' => true,
                    'success'    => __('Note successfully saved!')
                ], 200);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);

                return response()->json([
                    'is_success' => false,
                    'message'    => $e->getMessage()
                ], 500);
            }
        });
    }

    public const SV_TK_ST = 'saveTrackerSettings';
    public function saveTrackerSettings(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);

            $data = $request->validate(['interval_time' => 'required|numeric']);

            $settings = ['intervalTime' => $data['interval_time']];

            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);

                return redirect()->back()->with('success', __('Time Tracker successfully updated.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);

                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        });
    }

    public const CGPT_ST = 'chatGptSetting';
    public function chatGptSetting(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);

            $post = $request->except('_token');
            $settings = [];
            foreach ($post as $k => $v) $settings[Str::camel($k)] = $v;

            try {
                DB::transaction(fn() => $this->saveSettings($settings, $user?->creatorId()));
                Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);

                return redirect()->back()->with('success', __('ChatGPT Setting successfully saved.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);

                return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
            }
        });
    }

    public const CR_IP = 'createIp';
    public function createIp(Request $request): View|RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;

        if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;

        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);

        $view = 'restrict_ip.create';
        return ViewFacade::exists($view)
            ? view($view)
            : defaultUndefinedException($request, new \RuntimeException('View not found'), __METHOD__, route(self::REDIRECT_INDEX));
    }

    public const STR_IP = 'storeIp';
    public function storeIp(Request $request): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;

        if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;

        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);

        $data = $request->validate(['ip' => 'required']);

        try {
            DB::transaction(fn() => IpRestrict::create([
                'ip'                              => $data['ip'],
                DatabaseConstants::COL_TABLE_CREATOR  => $user?->creatorId()
            ]));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);

            return redirect()->back()->with('success', __('IP successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);

            return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
        }
    }

    public const ED_IP = 'editIp';
    public function editIp(Request $request, int $id): View|RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;

        if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;

        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);

        $ip = IpRestrict::findOrFail($id);
        if ($ip[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
            return defaultPermissionDenial($request, null, __METHOD__, route(self::REDIRECT_INDEX));
        }

        $view = 'restrict_ip.edit';
        return ViewFacade::exists($view)
            ? view($view, compact('ip'))
            : defaultUndefinedException($request, new \RuntimeException('View not found'), __METHOD__, route(self::REDIRECT_INDEX));
    }

    public const UPD_IP = 'updateIp';
    public function updateIp(Request $request, int $id): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;

        if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;

        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);

        $data = $request->validate(['ip' => 'required']);

        try {
            $ip = IpRestrict::findOrFail($id);
            if ($ip[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                return defaultPermissionDenial($request, null, __METHOD__, route(self::REDIRECT_INDEX));
            }

            DB::transaction(fn() => $ip->update(['ip' => $data['ip']]));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);

            return redirect()->back()->with('success', __('IP successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);

            return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
        }
    }

    public const DST_IP = 'destroyIp';
    public function destroyIp(Request $request, int $id): RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;

        if (($redirect = self::guard($request, 'manage company ' . DatabaseConstants::TABLE_SETTINGS, self::REDIRECT_INDEX)) !== true) return $redirect;

        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);

        try {
            $ip = IpRestrict::findOrFail($id);
            if ($ip[DatabaseConstants::COL_TABLE_CREATOR] !== $user?->creatorId()) {
                return defaultPermissionDenial($request, null, __METHOD__, route(self::REDIRECT_INDEX));
            }

            DB::transaction(fn() => $ip->delete());
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);

            return redirect()->back()->with('success', __('IP successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);

            return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * Persist an associative array of settings for a given creator.
     *
     * Each key/value pair is upserted into the `settings` table so that
     * existing keys are updated and new keys are inserted.
     *
     * @param  array<string,mixed>  $data       Key-value pairs to save
     * @param  string|int|null      $creatorId  The owner / creator ID
     */
    protected function saveSettings(array $data, string|int|null $creatorId): void
    {
        $table = DatabaseConstants::TABLE_SETTINGS;
        $col   = DatabaseConstants::COL_TABLE_CREATOR;
        foreach ($data as $name => $value) {
            DB::table($table)->updateOrInsert(
                ['name' => $name, $col => $creatorId ?? DatabaseConstants::DEFAULT_UUID],
                ['value' => is_array($value) ? json_encode($value) : (string) $value],
            );
        }
    }
}
