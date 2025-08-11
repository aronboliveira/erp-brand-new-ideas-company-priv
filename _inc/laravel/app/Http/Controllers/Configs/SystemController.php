<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants
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
    WebhookSetting
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Artisan, Auth, DB, File, Log, Mail, Validator};
use Illuminate\View\View;

class SystemController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::SYS . '.index';
    private const REDIRECT_COMPANY = ViewsConstants::SYS . '.' . PermissionsConstants::CPN;

    public function index(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage system ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $settings           = Utility::settings();
            $adminPaymentSetting = Utility::getAdminPaymentSetting();
            $totalBytes = 0;
            foreach (File::allFiles(storage_path('framework')) as $f)
                $totalBytes += $f->getSize();
            $fileSize = number_format($totalBytes / 1_000_000, 4);
            Log::info(__METHOD__ . ' succeeded', [
                UsersConstants::COL_USER_ID => $user?->id,
                'fileSizeMB' => $fileSize
            ]);
            return view(
                ViewsConstants::SYS . '.' . __FUNCTION__,
                compact(DatabaseConstants::TABLE_SETTINGS, 'adminPaymentSetting', 'fileSize')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                UsersConstants::COL_USER_ID => $user?->id,
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function store(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage system ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            DB::transaction(function () use ($request, $user) {
                $uploads = [
                    SettingsConstants::CPN_LG_LT  => SettingsConstants::CPN_LG_LT_DEF,
                    SettingsConstants::CPN_LG_DK => SettingsConstants::CPN_LG_DK_DEF,
                    SettingsConstants::FAV_ICN   => 'favicon.png'
                ];
                $dir = 'uploads/logo/';
                foreach ($uploads as $field => $filename) {
                    if (!$request->hasFile($field)) continue;
                    $path = Utility::uploadFile(
                        $request,
                        $field,
                        $filename,
                        $dir,
                        ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]
                    );
                    if ($path['flag'] !== 1)
                        throw new \RuntimeException($path['msg']);
                    $uploads[$field] = $path['url'];
                }
                $post = $request->except('_token');
                foreach (
                    [
                        'SITE_RTL',
                        'displayLandingPage',
                        'gdprCookie',
                        'enableSignup',
                        'emailVerification',
                        'custThemeBg',
                        'custDarklayout'
                    ] as $b
                )
                    $post[$b] = $request->has($b) ? 'on' : 'off';
                $this->saveSettings($post, $user?->creatorId());
            });
            Log::info(__METHOD__ . ' completed', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()
                ->back()
                ->with('success', __('Brand setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                UsersConstants::COL_USER_ID => $user?->id,
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_EM_ST = 'saveEmailSettings';
    public function saveEmailSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage system ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $data = $request->validate([
                'mailDriver'     => 'required|string|max:255',
                'mailHost'       => 'required|string|max:255',
                'mailPort'       => 'required|string|max:255',
                'mailUsername'   => 'required|string|max:255',
                'mailPassword'   => 'required|string|max:255',
                'mailEncryption' => 'required|string|max:255',
                'mailFromAddress' => 'required|string|max:255',
                'mailFromName'   => 'required|string|max:255'
            ]);
            DB::transaction(fn() => $this->saveSettings(
                $data,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()
                ->back()
                ->with('success', __('Setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                UsersConstants::COL_USER_ID => $user?->id,
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_CP_EM_ST = 'saveCompanyEmailSettings';
    public function saveCompanyEmailSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($user->type != PermissionsConstants::CPN)
            return defaultPermissionDenial(
                $request,
                new \Exception('Not a ' . PermissionsConstants::CPN . ' user'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $data = $request->validate([
                'mailDriver'     => 'required|string|max:255',
                'mailHost'       => 'required|string|max:255',
                'mailPort'       => 'required|string|max:255',
                'mailUsername'   => 'required|string|max:255',
                'mailPassword'   => 'required|string|max:255',
                'mailEncryption' => 'required|string|max:255',
                'mailFromAddress' => 'required|string|max:255',
                'mailFromName'   => 'required|string|max:255'
            ]);
            DB::transaction(fn() => $this->saveSettings(
                $data,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()
                ->back()
                ->with('success', __('Setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                UsersConstants::COL_USER_ID => $user?->id,
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_CP_ST = 'saveCompanySettings';
    public function saveCompanySettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $data = $request->validate([
                'companyName' => 'required|string|max:255'
            ]);
            $data['vatGstNumberSwitch'] =
                $request->has('vatGstNumberSwitch') ? 'on' : 'off';
            $data['ipRestrict'] =
                $request->has('ipRestrict') ? 'on' : 'off';
            DB::transaction(fn() => $this->saveSettings(
                $data,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()
                ->back()
                ->with('success', __('Setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                UsersConstants::COL_USER_ID => $user?->id,
                'error' => $e->getMessage()
            ]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_PAY_ST = 'savePaymentSettings';
    public function savePaymentSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage stripe ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $validator = Validator::make($request->all(), [
            'currency'       => 'required|string|max:255',
            'currencySymbol' => 'required|string|max:255'
        ]);
        if ($validator->fails())
            return redirect()->back()
                ->with('error', $validator->getMessageBag()->first());
        try {
            DB::transaction(fn() => Utility::adminPaymentSettings($request));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Payment setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_SYS_ST = 'saveSystemSettings';
    public function saveSystemSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $data = $request->validate(['siteCurrency' => 'required']);
            $data['shippingDisplay'] =
                $request->has('shippingDisplay') ? 'on' : 'off';
            DB::transaction(fn() => $this->saveSettings(
                $data,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_ZM_ST = 'saveZoomSettings';
    public function saveZoomSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage system ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $data = $request->except(['_token']);
            DB::transaction(fn() => $this->saveSettings(
                $data,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Setting successfully saved.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_BS_ST = 'saveBusinessSettings';
    public function saveBusinessSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage business ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            DB::transaction(function () use ($request, $user) {
                $files = [
                    SettingsConstants::CPN_LG_DK,
                    SettingsConstants::CPN_LG_LT,
                    SettingsConstants::CPN_FAVICON_K
                ];
                $dir = 'uploads/logo/';
                foreach ($files as $field) {
                    if (!$request->hasFile($field)) continue;
                    $filename = $user?->id . '-' . lcfirst($field) . '.png';
                    $path = Utility::uploadFile(
                        $request,
                        $field,
                        $filename,
                        $dir,
                        ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]
                    );
                    if ($path['flag'] !== 1) throw new \RuntimeException($path['msg']);
                    $creatorCol = DatabaseConstants::TABLE_CREATOR;
                    DB::insert(
                        'insert into settings (`value`,`name`,`' . $creatorCol . '`) values(?,?,?) '
                            . 'ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                        [$filename, lcfirst($field), $user?->creatorId()]
                    );
                }
                $data = $request->except(array_merge(['_token'], $files));
                foreach (['SITE_RTL', 'custThemeBg', 'custDarklayout'] as $b)
                    $data[$b] = $request->has($b) ? 'on' : 'off';
                $this->saveSettings($data, $user?->creatorId());
            });
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Brand setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const CP = 'companyIndex';
    public function companyIndex(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_COMPANY
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $offer  = $request->input('offerlangs', 'en');
            $joining = $request->input('joininglangs', 'en');
            $exp    = $request->input('explangs', 'en');
            $noc    = $request->input('noclangs', 'en');
            $data = [
                'offerLang' => $offer,
                'joiningLang' => $joining,
                'expLang' => $exp,
                'nocLang' => $noc
            ];
            foreach ($data as $k => $v)
                $data[$k . 'Name'] = Language::languageData($v);
            $viewData = array_merge($data, [
                'setting' => Utility::settingsById($user?->creatorId()),
                'timezones' => config('timezones'),
                'companyPaymentSetting' => Utility::getCompanyPaymentSetting($user?->creatorId()),
                'emailTemplates' => EmailTemplate::all(),
                'ips' => IpRestrict::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get(),
                'offerLetters' => GeneratedOfferLetter::all(),
                'currOfferLetter' => GeneratedOfferLetter::where(DatabaseConstants::TABLE_CREATOR, $user?->id)
                    ->where('lang', $offer)->first(),
                'joiningLetters' => JoiningLetter::all(),
                'currJoiningLetter' => JoiningLetter::where(DatabaseConstants::TABLE_CREATOR, $user?->id)
                    ->where('lang', $joining)->first(),
                'expCertificates' => ExperienceCertificate::all(),
                'currExpCert' => ExperienceCertificate::where(DatabaseConstants::TABLE_CREATOR, $user?->id)
                    ->where('lang', $exp)->first(),
                'nocCertificates' => Noc::all(),
                'currNocCert' => Noc::where(DatabaseConstants::TABLE_CREATOR, $user?->id)
                    ->where('lang', $noc)->first()
            ]);
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return view(DatabaseConstants::TABLE_SETTINGS . '.company', $viewData);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_COMPANY)
            );
        }
    }

    public const SV_CP_PAY_ST = 'saveCompanyPaymentSettings';
    public function saveCompanyPaymentSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_COMPANY
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            DB::transaction(function () use ($request, $user) {
                $methods = [
                    'bankTransfer' => ['bank_details'],
                    'stripe' => ['stripe_key', 'stripe_secret'],
                    // ... etc ...
                    'xendit' => ['xendit_token', 'xendit_api']
                ];
                $creatorCol = DatabaseConstants::TABLE_CREATOR;
                foreach ($methods as $k => $fields) {
                    $enable = 'is_' . $k . '_enabled';
                    if ($request->input($enable) === 'on') {
                        $rules = [];
                        foreach ($fields as $f) $rules[$f] = 'required|string';
                        $request->validate($rules);
                        DB::insert(
                            'insert into company_payment_settings (`value`,`name`,`' . $creatorCol . '`) '
                                . 'values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                            ['on', $enable, $user?->id]
                        );
                        foreach ($fields as $f)
                            DB::insert(
                                'insert into company_payment_settings (`value`,`name`,`' . $creatorCol . '`) '
                                    . 'values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                                [$request->input($f), $f, $user?->id]
                            );
                    } else
                        DB::insert(
                            'insert into company_payment_settings (`value`,`name`,`' . $creatorCol . '`) '
                                . 'values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                            ['off', 'is_' . $k . '_enabled', $user?->id]
                        );
                }
            });
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Payment setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_COMPANY)
            );
        }
    }

    public const TT_MAIL = 'testMail';
    public function testMail(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage system ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
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
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return view(DatabaseConstants::TABLE_SETTINGS . '.test_mail', compact('data'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const TT_SMAIL = 'testSendMail';
    public function testSendMail(Request $request): JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(
            ['success' => false, 'message' => 'Unauthorized'],
            403
        );
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $data = $request->validate([
            'email'            => 'required|email',
            'mail_driver'      => 'required|string',
            'mail_host'        => 'required|string',
            'mail_port'        => 'required|string',
            'mail_username'    => 'required|string',
            'mail_password'    => 'required|string',
            'mail_encryption'  => 'nullable|string',
            'mail_from_address' => 'required|string',
            'mail_from_name'   => 'required|string',
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
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return response()->json([
                'success' => true,
                'message' => __('Email sent successfully'),
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public const PRT = 'printIndex';
    public function printIndex(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage print ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $settings = Utility::settings();
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return view(DatabaseConstants::TABLE_SETTINGS . '.print', compact('' . DatabaseConstants::TABLE_SETTINGS));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const POS_PRT = 'posPrintIndex';
    public function posPrintIndex(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage print ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $settings = Utility::settings();
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return view(DatabaseConstants::TABLE_SETTINGS . '.pos', compact('' . DatabaseConstants::TABLE_SETTINGS));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const ADM_PAY_ST = 'adminPaymentSettings';
    public function adminPaymentSettings(Request $request): void
    {
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $request->user()->id]);
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
                    'paypal'          => [
                        'paypal_mode',
                        'paypal_client_id',
                        'paypal_secret_key'
                    ],
                    'paystack'        => [
                        'paystack_public_key',
                        'paystack_secret_key'
                    ],
                    'flutterwave'     => [
                        'flutterwave_public_key',
                        'flutterwave_secret_key'
                    ],
                    'razorpay'        => [
                        'razorpay_public_key',
                        'razorpay_secret_key'
                    ],
                    'mercado'         => [
                        'mercado_access_token',
                        'mercado_mode'
                    ],
                    'paytm'           => [
                        'paytm_mode',
                        'paytm_merchant_id',
                        'paytm_merchant_key',
                        'paytm_industry_type'
                    ],
                    'mollie'          => [
                        'mollie_api_key',
                        'mollie_profile_id',
                        'mollie_partner_id'
                    ],
                    'skrill'          => ['skrill_email'],
                    'coingate'        => [
                        'coingate_mode',
                        'coingate_auth_token'
                    ],
                    'paymentwall'     => [
                        'paymentwall_public_key',
                        'paymentwall_secret_key'
                    ],
                    'toyyibpay'       => [
                        'toyyibpay_category_code',
                        'toyyibpay_secret_key'
                    ]
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
                $creatorCol = DatabaseConstants::TABLE_CREATOR;
                foreach ($settings as $name => $value)
                    DB::insert(
                        'insert into admin_payment_settings '
                            . '(`value`,`name`,`' . $creatorCol . '`) values(?,?,?) '
                            . 'ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                        [$value, $name, $userId]
                    );
            });
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $request->user()->id]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [
                UsersConstants::COL_USER_ID => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public const SV_PSR_ST = 'savePusherSettings';
    public function savePusherSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if ($user->type != PermissionsConstants::SA)
            return defaultPermissionDenial(
                $request,
                new \Exception('Unauthorized'),
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $rules = [
            'pusher_app_id'      => 'required',
            'pusher_app_key'     => 'required',
            'pusher_app_secret'  => 'required',
            'pusher_app_cluster' => 'required'
        ];
        $data = $request->validate($rules);
        $settings = [
            'pusherAppId'      => $data['pusher_app_id'],
            'pusherAppKey'     => $data['pusher_app_key'],
            'pusherAppSecret'  => $data['pusher_app_secret'],
            'pusherAppCluster' => $data['pusher_app_cluster']
        ];
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Pusher Settings updated successfully'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_SLK_ST = 'saveSlackSettings';
    public function saveSlackSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $settings = [
            'slackWebhook' => $request->input('slack_webhook')
        ];
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
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Slack updated successfully.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_TLG_ST = 'saveTelegramSettings';
    public function saveTelegramSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $settings = [
            'telegramAccesToken' => $request->input('telegram_accestoken'),
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
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Telegram updated successfully.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_TWL_ST = 'saveTwilioSettings';
    public function saveTwilioSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
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
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Twilio updated successfully.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const RCP_ST_STR = 'recaptchaSettingStore';
    public function recaptchaSettingStore(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $settings = [];
        if ($request->has(SettingsConstants::RCPT_MDL) && $request->input(SettingsConstants::RCPT_MDL) === 'on') {
            $data = $request->validate([
                SettingsConstants::G_RCPT_K    => 'required|string|max:50',
                SettingsConstants::G_RCPT_SC => 'required|string|max:50'
            ]);
            $settings = [
                SettingsConstants::RCPT_MDL        => 'on',
                SettingsConstants::G_RCPT_K     => $data[SettingsConstants::G_RCPT_K],
                SettingsConstants::G_RCPT_SC  => $data[SettingsConstants::G_RCPT_SC]
            ];
        } else
            $settings = [
                SettingsConstants::RCPT_MDL        => 'off',
                SettingsConstants::G_RCPT_K     => $request->input(SettingsConstants::G_RCPT_K, ''),
                SettingsConstants::G_RCPT_SC  => $request->input(SettingsConstants::G_RCPT_SC, '')
            ];
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Recaptcha Settings updated successfully'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const STG_ST_STR = 'storageSettingStore';
    public function storageSettingStore(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $post = [];
        $type = $request->input(SettingsConstants::STR_STT);
        if ($type === SettingsConstants::LC) {
            $data = $request->validate([
                SettingsConstants::LC_ST_VL       => 'required|array',
                SettingsConstants::LC_ST_M_UP  => 'required|integer',
            ]);
            $post = [
                SettingsConstants::STR_STT               => SettingsConstants::LC,
                SettingsConstants::LC_ST_VL      => implode(',', $data[SettingsConstants::LC_ST_VL]),
                SettingsConstants::LC_ST_M_UP => $data[SettingsConstants::LC_ST_M_UP],
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
            $validationField = $type === SettingsConstants::S3
                ? SettingsConstants::S3_STG_VL
                : SettingsConstants::WSB_STG_VL;
            $rules[$validationField] = 'required|array';
            $data = $request->validate($rules);
            $post = [SettingsConstants::STR_STT => $type];
            foreach ($fields as $field) {
                $key  = Str::studly($field);
                $value = $field === $validationField
                    ? implode(',', $data[$field])
                    : $data[$field];
                $post[$key] = $value;
            }
        }
        try {
            DB::transaction(fn() => $this->saveSettings(
                $post,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Storage setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const OF_LTR_UPD = 'offerLetterUpdate';
    public function offerLetterUpdate(string $lang, Request $request): JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
        try {
            DB::transaction(fn() => GeneratedOfferLetter::updateOrCreate(
                ['lang' => $lang, DatabaseConstants::TABLE_CREATOR => $user?->id],
                ['content' => $request->input('content', '')]
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            return response()->json([
                'is_success' => true,
                'success' => __('Offer Letter successfully saved!')
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return response()->json([
                'is_success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public const JN_LTR_UPD = 'joiningLetterUpdate';
    public function joiningLetterUpdate(string $lang, Request $request): JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
        try {
            DB::transaction(fn() => JoiningLetter::updateOrCreate(
                ['lang' => $lang, DatabaseConstants::TABLE_CREATOR => $user?->id],
                ['content' => $request->input('content', '')]
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            return response()->json([
                'is_success' => true,
                'success' => __('Joining Letter successfully saved!')
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return response()->json([
                'is_success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public const EXP_CT_UPD = 'experienceCertificateUpdate';
    public function experienceCertificateUpdate(string $lang, Request $request): JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
        try {
            DB::transaction(fn() => ExperienceCertificate::updateOrCreate(
                ['lang' => $lang, DatabaseConstants::TABLE_CREATOR => $user?->id],
                ['content' => $request->input('content', '')]
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            return response()->json([
                'is_success' => true,
                'success' => __('Experience Certificate successfully saved!')
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return response()->json([
                'is_success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public const NOC_UPD = 'nocUpdate';
    public function nocUpdate(string $lang, Request $request): JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
        try {
            DB::transaction(fn() => Noc::updateOrCreate(
                ['lang' => $lang, DatabaseConstants::TABLE_CREATOR => $user?->id],
                ['content' => $request->input('content', '')]
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id, 'lang' => $lang]);
            return response()->json([
                'is_success' => true,
                'success' => __('NOC successfully saved!')
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return response()->json([
                'is_success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public const SV_GG_CLD_ST = 'saveGoogleCalendarSettings';
    public function saveGoogleCalendarSettings(Request $request): RedirectResponse|JsonResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage system ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $settings = [];
        if ($request->input('google_calendar_enable') === 'on') {
            $data = $request->validate([
                'google_calendar_json_file' => 'required|file',
                'google_clender_id'           => 'required|string'
            ]);
            $settings['google_calendar_enable'] = 'on';
            // handle JSON file upload
            $dir = storage_path('google_calendar') . '/' . md5(now());
            if (!File::isDirectory($dir)) File::makeDirectory($dir, 0777, true, true);
            $file = $request->file('google_calendar_json_file');
            $path = $file->storeAs($dir, $file->getClientOriginalName());
            $settings['google_calendar_json_file'] = $path;
            $settings['google_clender_id'] = $data['google_clender_id'];
        } else {
            $settings['google_calendar_enable'] = 'off';
        }
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Google Calendar setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SEO_ST = 'seoSettings';
    public function seoSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage system ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $data = $request->validate([
            SettingsConstants::MT_TTL_K => 'required|string',
            SettingsConstants::MT_DSC_K => 'required|string',
            SettingsConstants::MT_IMG_K => 'required|file'
        ]);
        $dir = storage_path('uploads/meta');
        if (!File::isDirectory($dir)) File::makeDirectory($dir, 0777, true, true);
        $image = $request->file(SettingsConstants::MT_IMG_K);
        $filename = $image->getClientOriginalName();
        $image->move($dir, $filename);
        $settings = [
            SettingsConstants::MT_TTL_K => $data[SettingsConstants::MT_TTL_K],
            SettingsConstants::MT_DSC_K => $data[SettingsConstants::MT_DSC_K],
            SettingsConstants::MT_IMG_K => $filename
        ];
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('SEO setting successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public function webhook(Request $request): RedirectResponse|View|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create webhook',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $webhookSettings = WebhookSetting::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->get();
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return view('webhook.index', compact('webhookSettings'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const WHK_CRT = 'webhookCreate';
    public function webhookCreate(Request $request): View|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(['error' => 'Unauthorized'], 401);
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create webhook',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $modules = WebhookSetting::$modules;
            $methods = WebhookSetting::$method;
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return view('webhook.create', compact('modules', 'methods'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public const WHK_STR = 'webhookStore';
    public function webhookStore(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'create webhook',
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $data = $request->validate([
            'module' => 'required',
            'url' => 'required|url',
            'method' => 'required'
        ]);
        try {
            WebhookSetting::create([
                'module' => $data['module'],
                'url' => $data['url'],
                'method' => $data['method'],
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
            ]);
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Webhook successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const WHK_EDT = 'webhookEdit';
    public function webhookEdit(Request $request, int $id): View|JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(['error' => 'Unauthorized'], 401);
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::ED_WHK,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
        try {
            $webhook = WebhookSetting::findOrFail($id);
            $modules = WebhookSetting::$modules;
            $methods = WebhookSetting::$method;
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return view('webhook.edit', compact('webhook', 'modules', 'methods'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const WHK_UPD = 'webhookUpdate';
    public function webhookUpdate(Request $request, int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::ED_WHK,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
        $data = $request->validate([
            'module' => 'required',
            'url' => 'required|url',
            'method' => 'required'
        ]);
        try {
            $wh = WebhookSetting::findOrFail($id);
            $wh->update([
                'module' => $data['module'],
                'url' => $data['url'],
                'method' => $data['method']
            ]);
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Webhook successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const WHK_DST = 'webhookDestroy';
    public function webhookDestroy(Request $request, int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            PermissionsConstants::DEL_WHK,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
        try {
            WebhookSetting::findOrFail($id)->delete();
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Webhook successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const SV_CK_ST = 'saveCookieSettings';
    public function saveCookieSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $data = $request->validate([
            'cookie_title'                => 'required',
            'cookie_description'          => 'required',
            'strictly_cookie_title'       => 'required',
            'strictly_cookie_description' => 'required',
            'more_information_description' => 'required',
            'contactus_url'               => 'required'
        ]);
        $settings = [];
        foreach (
            [
                'enableCookie',
                'cookieLogging'
            ] as $flag
        ) {
            $settings[$flag] = $request->has(
                Str::snake($flag)
            ) ? 'on' : 'off';
        }
        foreach (
            [
                'cookieTitle',
                'cookieDescription',
                'strictlyCookieTitle',
                'strictlyCookieDescription',
                'moreInformationDescription',
                'contactusUrl'
            ] as $field
        ) {
            $settings[$field] = $request->input(
                Str::snake($field)
            );
        }
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Cookie setting successfully saved.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const CK_CST = 'cookieConsent';
    public function cookieConsent(Request $request): JsonResponse
    {
        $settings = Utility::settings();
        if (
            ($settings['enableCookie'] ?? '') === 'on'
            && ($settings['cookieLogging'] ?? '') === 'on'
        ) {
            try {
                $allowed = ['necessary', 'analytics', 'targeting'];
                $levels = array_filter(
                    $request->input('cookie', []),
                    fn($l) => in_array($l, $allowed)
                );
                $parser = new \WhichBrowser\Parser(
                    $_SERVER['HTTP_USER_AGENT']
                );
                $info = [
                    $parser->browser->name ?? '',
                    $parser->os->name      ?? ''
                ];
                $lang  = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
                $device = Utility::getDeviceType(
                    $_SERVER['HTTP_USER_AGENT']
                );
                $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $geo   = @unserialize(
                    file_get_contents('http://ip-api.com/php/' . $ip)
                ) ?: [];
                $row = implode(',', [
                    $ip,
                    date('Y-m-d'),
                    date('H:i:s') . ' UTC',
                    json_encode($levels),
                    $device,
                    substr($lang, 0, 2),
                    $info[0],
                    $info[1],
                    $geo['country']    ?? '',
                    $geo['region']     ?? '',
                    $geo['regionName'] ?? '',
                    $geo['city']       ?? '',
                    $geo['zip']        ?? '',
                    $geo['lat']        ?? '',
                    $geo['lon']        ?? ''
                ]);
                $csv = storage_path('uploads/sample/data.csv');
                if (!File::exists($csv)) {
                    $header = 'IP,Date,Time,Accepted cookies,Device type,'
                        . 'Browser language,Browser name,OS Name,Country,'
                        . 'Region,RegionName,City,Zipcode,Lat,Lon';
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
    }

    public const CC_ST_STR = 'cacheSettingStore';
    public function cacheSettingStore(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            Artisan::call('cache:clear');
            Artisan::call('optimize:clear');
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Cache cleared successfully'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const FT_NT_STR = 'footerNoteStore';
    public function footerNoteStore(Request $request, ?int $user_id = null): JsonResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return response()->json(['success' => false], 403);
        $auth = $userOrRedirect;
        $user = $user_id
            ? User::find($user_id)
            : $auth;
        Log::info(__METHOD__ . ' started', ['authId' => $auth->id, UsersConstants::COL_USER_ID => $user?->id]);
        $settings = ['footerNotes' => $request->input('notes', '')];
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->id
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return response()->json([
                'is_success' => true,
                'success' => __('Note successfully saved!')
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return response()->json([
                'is_success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public const SV_TK_ST = 'saveTrackerSettings';
    public function saveTrackerSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $data = $request->validate(['interval_time' => 'required']);
        $settings = ['intervalTime' => $data['interval_time']];
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('Time Tracker successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const CGPT_ST = 'chatGptSetting';
    public function chatGptSetting(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $post = $request->except('_token');
        $settings = [];
        foreach ($post as $k => $v)
            $settings[Str::camel($k)] = $v;
        try {
            DB::transaction(fn() => $this->saveSettings(
                $settings,
                $user?->creatorId()
            ));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('ChatGPT Setting successfully saved.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const CR_IP = 'createIp';
    public function createIp(Request $request): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        return view('restrict_ip.create');
    }

    public const STR_IP = 'storeIp';
    public function storeIp(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id]);
        $data = $request->validate(['ip' => 'required']);
        try {
            DB::transaction(fn() => IpRestrict::create([
                'ip' => $data['ip'],
                DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
            ]));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('IP successfully created.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const ED_IP = 'editIp';
    public function editIp(Request $request, int $id): View|RedirectResponse|null
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
        $ip = IpRestrict::findOrFail($id);
        return view('restrict_ip.edit', compact('ip'));
    }

    public const UPD_IP = 'updateIp';
    public function updateIp(Request $request, int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
        $data = $request->validate(['ip' => 'required']);
        try {
            DB::transaction(fn() => IpRestrict::whereKey($id)
                ->update(['ip' => $data['ip']]));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('IP successfully updated.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    public const DST_IP = 'destroyIp';
    public function destroyIp(Request $request, int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard(
            $request,
            'manage company ' . DatabaseConstants::TABLE_SETTINGS,
            self::REDIRECT_INDEX
        )) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', [UsersConstants::COL_USER_ID => $user?->id, 'id' => $id]);
        try {
            DB::transaction(fn() => IpRestrict::destroy($id));
            Log::info(__METHOD__ . ' succeeded', [UsersConstants::COL_USER_ID => $user?->id]);
            return redirect()->back()
                ->with('success', __('IP successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', [UsersConstants::COL_USER_ID => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::REDIRECT_INDEX)
            );
        }
    }
}
