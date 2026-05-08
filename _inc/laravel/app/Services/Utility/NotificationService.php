<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    SettingsConstants as SC,
    UsersConstants as UC,
};
use App\Mail\CommonEmailTemplate;
use App\Models\{
    Budget,
    EmailTemplate,
    EmailTemplateLang,
    NotificationTemplate,
    NotificationTemplateLang,
    User,
    UserEmailTemplate,
    Utility,
    WebhookSettings,
};
use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, Config, Http, Log, Mail};
use Twilio\Rest\Client as TwilioClient;

/**
 * NotificationService — extracted from Utility.php
 *
 * Handles email template dispatch, Slack/Telegram/Twilio messaging,
 * email template variable replacement, webhook settings and calls,
 * SMTP configuration, Pusher settings, and budget notifications.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class NotificationService
{
    use ChecksLogin;

    /**
     * Test seam — when set, sendTwilioMsg() invokes this callable
     * instead of constructing a real Twilio\Rest\Client and calling
     * ->messages->create(). Receives ($sid, $token, $to, $from, $msg).
     *
     * Production code MUST leave this null. Tests must reset to null
     * via resetTestSeams() in finally{} to prevent bleed.
     */
    public static ?\Closure $sendTwilioOverride = null;

    /**
     * Reset all NotificationService test seams to null.
     */
    public static function resetTestSeams(): void
    {
        self::$sendTwilioOverride = null;
    }

    // ─────────────────────────────────────────────────────────
    //  Email Template Dispatch
    // ─────────────────────────────────────────────────────────

    public static function sendEmailTemplate(string $emailTemplate, array $mailTo, array $obj): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        /** @var User $user */
        $user = $userOrRedirect;
        $mailTo = array_values($mailTo);
        if ($user->type != PMC::SA) {
            $template = EmailTemplate::where('slug', 'LIKE', $emailTemplate)->first();
            if (!$template) {
                return ['is_success' => false, 'error' => __('Mail not send, email not found')];
            }
            $isActiveRecord = $user->type != PMC::SA
                ? UserEmailTemplate::where('template_id', $template->id)
                ->where(UC::COL_USER_ID, $user?->creatorId())->first()
                : (object)['is_active' => 1];
            if (!$isActiveRecord || $isActiveRecord->is_active != 1) {
                return ['is_success' => true, 'error' => false];
            }
            $settings = Utility::settingsById($user?->id);
            $content = EmailTemplateLang::where('parent_id', $template->id)
                ->where('lang', 'LIKE', $user?->lang)->first();
            $content->from = $template->from;
            if (empty($content->content)) {
                return ['is_success' => false, 'error' => __('Mail not send, email is empty')];
            }
            $content->content = self::replaceVariable($content->content, $obj);
            try {
                config([
                    'mail.driver'       => $settings['mail_driver'],
                    'mail.host'         => $settings['mail_host'],
                    'mail.port'         => $settings['mail_port'],
                    'mail.encryption'   => $settings['mail_encryption'],
                    'mail.username'     => $settings['mail_username'],
                    'mail.password'     => $settings['mail_password'],
                    'mail.from.address' => $settings['mail_from_address'],
                    'mail.from.name'    => $settings['mail_from_name'],
                ]);
                Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
                return ['is_success' => true, 'error' => false];
            } catch (\Throwable $e) {
                $err = $e->getMessage();
                return ['is_success' => false, 'error' => $err];
            }
        }
        return [];
    }

    public static function sendUserEmailTemplate(string $emailTemplate, array $mailTo, array $obj): array|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        /** @var User $user */
        $user = $userOrRedirect;
        $mailTo = array_values($mailTo);
        $template = EmailTemplate::where('slug', 'LIKE', $emailTemplate)->first();
        if (!$template) {
            return ['is_success' => false, 'error' => __('Mail not send, email not found')];
        }
        $isActiveRecord = UserEmailTemplate::where('template_id', $template->id)
            ->where(UC::COL_USER_ID, $user?->creatorId())->first();
        if (!$isActiveRecord || $isActiveRecord->is_active != 1) {
            return ['is_success' => true, 'error' => false];
        }
        $settings = Utility::settingsById(1);
        $content = EmailTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'LIKE', $user?->lang)->first();
        $content->from = $template->from;
        if (empty($content->content)) {
            return ['is_success' => false, 'error' => __('Mail not send, email is empty')];
        }
        $content->content = self::replaceVariable($content->content, $obj);
        try {
            config([
                'mail.driver'       => $settings['mail_driver'],
                'mail.host'         => $settings['mail_host'],
                'mail.port'         => $settings['mail_port'],
                'mail.encryption'   => $settings['mail_encryption'],
                'mail.username'     => $settings['mail_username'],
                'mail.password'     => $settings['mail_password'],
                'mail.from.address' => $settings['mail_from_address'],
                'mail.from.name'    => $settings['mail_from_name'],
            ]);
            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
            return ['is_success' => true, 'error' => false];
        } catch (\Throwable $e) {
            $err = $e->getMessage();
            return ['is_success' => false, 'error' => $err];
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Template Variable Replacement
    // ─────────────────────────────────────────────────────────

    public static function replaceVariable($content, $obj): array|string
    {
        $arrVariable = [
            '{app_name}',
            '{company_name}',
            '{app_url}',
            '{email}',
            '{password}',
            '{client_name}',
            '{client_email}',
            '{client_password}',
            '{support_name}',
            '{support_title}',
            '{support_priority}',
            '{support_end_date}',
            '{support_description}',
            '{lead_name}',
            '{lead_email}',
            '{lead_subject}',
            '{lead_pipeline}',
            '{lead_stage}',
            '{deal_name}',
            '{deal_pipeline}',
            '{deal_stage}',
            '{deal_status}',
            '{deal_price}',
            '{award_name}',
            '{award_email}',
            '{customer_name}',
            '{customer_email}',
            '{invoice_name}',
            '{invoice_number}',
            '{invoice_url}',
            '{invoice_payment_name}',
            '{invoice_payment_amount}',
            '{invoice_payment_date}',
            '{payment_dueAmount}',
            '{payment_reminder_name}',
            '{invoice_payment_number}',
            '{invoice_payment_dueAmount}',
            '{payment_reminder_date}',
            '{payment_name}',
            '{payment_bill}',
            '{payment_amount}',
            '{payment_date}',
            '{payment_method}',
            '{vendor_name}',
            '{vendor_email}',
            '{bill_name}',
            '{bill_id}',
            '{bill_url}',
            '{proposal_name}',
            '{proposal_number}',
            '{proposal_url}',
            '{complaint_name}',
            '{complaint_title}',
            '{complaint_against}',
            '{complaint_date}',
            '{complaint_description}',
            '{leave_name}',
            '{leave_status}',
            '{leave_reason}',
            '{leave_start_date}',
            '{leave_end_date}',
            '{total_leave_days}',
            '{employee_name}',
            '{employee_email}',
            '{payslip_name}',
            '{payslip_salary_month}',
            '{payslip_url}',
            '{promotion_designation}',
            '{promotion_title}',
            '{promotion_date}',
            '{resignation_email}',
            '{assign_user}',
            '{resignation_date}',
            '{notice_date}',
            '{termination_name}',
            '{termination_email}',
            '{termination_date}',
            '{termination_type}',
            '{transfer_name}',
            '{transfer_email}',
            '{transfer_date}',
            '{transfer_department}',
            '{transfer_branch}',
            '{transfer_description}',
            '{trip_name}',
            '{purpose_of_visit}',
            '{start_date}',
            '{end_date}',
            '{place_of_visit}',
            '{trip_description}',
            '{vendor_bill_name}',
            '{vendor_bill_id}',
            '{vendor_bill_url}',
            '{employee_warning_name}',
            '{warning_subject}',
            '{warning_description}',
            '{contract_client}',
            '{contract_subject}',
            '{contract_start_date}',
            '{contract_end_date}',
            '{user_name}',
            '{lead_user_name}',
            '{project_name}',
            '{payment_price}',
            '{invoice_payment_type}',
            '{task_name}',
            '{old_stage_name}',
            '{new_stage_name}',
            '{year}',
            '{announcement_title}',
            '{branch_name}',
            '{support_user_name}',
            '{meeting_title}',
            '{meeting_date}',
            '{meeting_time}',
            '{award_date}',
            '{holiday_title}',
            '{holiday_date}',
            '{event_title}',
            '{event_start_date}',
            '{event_end_date}',
            '{company_policy_name}',
            '{budget_period}',
            '{budget_year}',
            '{budget_name}',
            '{revenue_amount}',
            '{vendor_name}',
            '{payment_type}',
            '{bill_due_date}',
            '{bill_date}',
        ];
        $arrValue   = [
            'app_name' => '-',
            'company_name' => '-',
            'app_url' => '-',
            'email' => '-',
            'password' => '-',
            'client_name' => '-',
            'client_email' => '-',
            'client_password' => '-',
            'support_name' => '-',
            'support_title' => '-',
            'support_priority' => '-',
            'support_end_date' => '-',
            'support_description' => '-',
            'lead_name' => '-',
            'lead_email' => '-',
            'lead_subject' => '-',
            'lead_pipeline' => '-',
            'lead_stage' => '-',
            'deal_name' => '-',
            'deal_pipeline' => '-',
            'deal_stage' => '-',
            'deal_status' => '-',
            'deal_price' => '-',
            'award_name' => '-',
            'award_email' => '-',
            'customer_name' => '-',
            'customer_email' => '-',
            'invoice_name' => '-',
            'invoice_number' => '-',
            'invoice_url' => '-',
            'invoice_payment_name' => '-',
            'invoice_payment_amount' => '-',
            'invoice_payment_date' => '-',
            'payment_dueAmount' => '-',
            'payment_reminder_name' => '-',
            'invoice_payment_number' => '-',
            'invoice_payment_dueAmount' => '-',
            'payment_reminder_date' => '-',
            'payment_name' => '-',
            'payment_bill' => '-',
            'payment_amount' => '-',
            'payment_date' => '-',
            'payment_method' => '-',
            'vendor_name' => '-',
            'vendor_email' => '-',
            'bill_name' => '-',
            'bill_id' => '-',
            'bill_url' => '-',
            'proposal_name' => '-',
            'proposal_number' => '-',
            'proposal_url' => '-',
            'complaint_name' => '-',
            'complaint_title' => '-',
            'complaint_against' => '-',
            'complaint_date' => '-',
            'complaint_description' => '-',
            'leave_name' => '-',
            'leave_status' => '-',
            'leave_reason' => '-',
            'leave_start_date' => '-',
            'leave_end_date' => '-',
            'total_leave_days' => '-',
            'employee_name' => '-',
            'employee_email' => '-',
            'payslip_name' => '-',
            'payslip_salary_month' => '-',
            'payslip_url' => '-',
            'promotion_designation' => '-',
            'promotion_title' => '-',
            'promotion_date' => '-',
            'resignation_email' => '-',
            'assign_user' => '-',
            'resignation_date' => '-',
            'notice_date' => '-',
            'termination_name' => '-',
            'termination_email' => '-',
            'termination_date' => '-',
            'termination_type' => '-',
            'transfer_name' => '-',
            'transfer_email' => '-',
            'transfer_date' => '-',
            'transfer_department' => '-',
            'transfer_branch' => '-',
            'transfer_description' => '-',
            'trip_name' => '-',
            'purpose_of_visit' => '-',
            'start_date' => '-',
            'end_date' => '-',
            'place_of_visit' => '-',
            'trip_description' => '-',
            'vendor_bill_name' => '-',
            'vendor_bill_id' => '-',
            'vendor_bill_url' => '-',
            'employee_warning_name' => '-',
            'warning_subject' => '-',
            'warning_description' => '-',
            'contract_client' => '-',
            'contract_subject' => '-',
            'contract_start_date' => '-',
            'contract_end_date' => '-',
            'user_name' => '-',
            'lead_user_name' => '-',
            'project_name' => '-',
            'payment_price' => '-',
            'invoice_payment_type' => '-',
            'task_name' => '-',
            'old_stage_name' => '-',
            'new_stage_name' => '-',
            'year' => '-',
            'announcement_title' => '-',
            'branch_name' => '-',
            'support_user_name' => '-',
            'meeting_title' => '-',
            'meeting_date' => '-',
            'meeting_time' => '-',
            'award_date' => '-',
            'holiday_title' => '-',
            'holiday_date' => '-',
            'event_title' => '-',
            'event_start_date' => '-',
            'event_end_date' => '-',
            'company_policy_name' => '-',
            'budget_period' => '-',
            'budget_year' => '-',
            'budget_name' => '-',
            'revenue_amount' => '-',
            'vendor_bill_payment_name' => '-',
            'payment_type' => '-',
            'bill_due_date' => '-',
            'bill_date' => '-',
        ];

        foreach ($obj as $key => $val)
            $arrValue[$key] = $val;
        $settings = Utility::settings();
        $company_name = $settings['company_name'];
        $arrValue['app_name']    =  !empty($company_name) ? $company_name : env('APP_NAME');
        $arrValue['company_name'] = Utility::settings()['mail_from_name'];
        $arrValue['app_url']     = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';

        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    // ─────────────────────────────────────────────────────────
    //  Channel Messaging (Slack, Telegram, Twilio)
    // ─────────────────────────────────────────────────────────

    public static function sendSlackMsg(string $slug, array $obj, string|int|null $userId = null): void
    {
        $template = NotificationTemplate::where('slug', $slug)->first();
        if (!$template || empty($obj)) return;
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return;
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(DC::COL_TABLE_CREATOR, $user?->id)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'en')
            ->first();
        if (!$notiLang || empty($notiLang->content)) return;
        $msg = self::replaceVariable($notiLang->content, $obj);
        $settings = Utility::settingsById($user?->id);
        $webhook = $settings['slack_webhook'] ?? '';
        if (!$webhook) return;
        try {
            Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($webhook, ['text' => $msg]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " Slack send failed: {$e->getMessage()}");
        }
    }

    public static function sendTelegramMsg(string $slug, array $obj, string|int|null $userId = null): void
    {
        $template = NotificationTemplate::where('slug', $slug)->first();
        if (!$template || empty($obj)) {
            return;
        }
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) {
            return;
        }
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(DC::COL_TABLE_CREATOR, $user?->id)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'en')
            ->first();
        if (!$notiLang || empty($notiLang->content))
            return;
        $msg = self::replaceVariable($notiLang->content, $obj);
        $settings = Utility::settingsById($user?->id);
        $bot  = $settings['telegram_accesstoken'] ?? '';
        $chat = $settings['telegram_chatid'] ?? '';
        if (!$bot || !$chat)
            return;
        try {
            $url = "https://api.telegram.org/bot{$bot}/sendMessage";
            Http::asForm()->post($url, [
                'chat_id' => $chat,
                'text'    => $msg,
            ]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " Telegram send failed: {$e->getMessage()}");
        }
    }

    public static function sendTwilioMsg(string $to, string $slug, array $obj, string|int|null $userId = null): void
    {
        $template = NotificationTemplate::where('slug', $slug)->first();
        if (!$template || empty($obj)) return;
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return;
        $lang = $user?->lang;
        $notiLang = NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->where(DC::COL_TABLE_CREATOR, $user?->id)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', $lang)
            ->first()
            ?: NotificationTemplateLang::where('parent_id', $template->id)
            ->where('lang', 'en')
            ->first();
        if (!$notiLang || empty($notiLang->content)) return;
        $msg = self::replaceVariable($notiLang->content, $obj);
        $settings  = Utility::settingsById($user?->id);
        $sid       = $settings['twilio_sid'] ?? '';
        $token     = $settings['twilio_token'] ?? '';
        $fromNumber = $settings['twilio_from'] ?? '';
        if (!$sid || !$token || !$fromNumber) return;
        try {
            if (self::$sendTwilioOverride !== null) {
                (self::$sendTwilioOverride)($sid, $token, $to, $fromNumber, $msg);
            } else {
                $client = new TwilioClient($sid, $token);
                $client->messages->create($to, [
                    'from' => $fromNumber,
                    'body' => $msg,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " Twilio send failed: {$e->getMessage()}");
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Webhook Settings & Calls
    // ─────────────────────────────────────────────────────────

    public static function webhookSetting(string $module, string|int|null $userId = null): array|bool
    {
        $user = $userId ? User::find($userId) : Auth::user();
        if (!$user) return false;
        $webhook = WebhookSettings::where('module', $module)
            ->where('created_by', $user?->id)
            ->first();
        if (!$webhook) return false;
        $reference = sprintf('https://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
        return [
            'method'        => $webhook->method,
            'reference_url' => $reference,
            'url'           => $webhook->url,
        ];
    }

    public static function webhookCall(?string $url, $parameter = null, string $method = 'POST'): bool
    {
        if (empty($url) || empty($parameter)) return false;
        try {
            $response = Http::withOptions(['verify' => false])
                ->send(strtoupper($method), $url, ['form_params' => $parameter]);
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────
    //  SMTP & Pusher Configuration
    // ─────────────────────────────────────────────────────────

    public static function smtpDetail(string|int $userId): array
    {
        $settings = Utility::settingsById($userId);
        $smtpConfig = [
            'mail.driver'       => $settings['mail_driver']       ?? '',
            'mail.host'         => $settings['mail_host']         ?? '',
            'mail.port'         => $settings['mail_port']         ?? '',
            'mail.encryption'   => $settings['mail_encryption']   ?? '',
            'mail.username'     => $settings['mail_username']     ?? '',
            'mail.password'     => $settings['mail_password']     ?? '',
            'mail.from.address' => $settings['mail_from_address'] ?? '',
            'mail.from.name'    => $settings['mail_from_name']    ?? '',
        ];
        Config::set($smtpConfig);
        return $smtpConfig;
    }

    public static function getPusherSetting(): array
    {
        $settings = Utility::settingsById(DC::DEFAULT_UUID);
        if (empty($settings['pusher_app_key'])) {
            return [];
        }
        $pusherConfig = [
            'chatify.pusher.key'            => $settings['pusher_app_key']      ?? '',
            'chatify.pusher.secret'         => $settings['pusher_app_secret']   ?? '',
            'chatify.pusher.app_id'         => $settings['pusher_app_id']       ?? '',
            'chatify.pusher.options.cluster' => $settings['pusher_app_cluster']  ?? '',
        ];
        Config::set($pusherConfig);
        return $settings;
    }

    // ─────────────────────────────────────────────────────────
    //  Budget Notifications
    // ─────────────────────────────────────────────────────────

    /**
     * Send notifications for a newly created Budget.
     */
    public static function notifyNewBudget(Budget $budget): void
    {
        try {
            $creator = User::find($budget->{DC::COL_TABLE_CREATOR}); // @phpstan-ignore property.notFound
            if (!$creator) return;

            $emailObj = [
                'budget_name'   => $budget->name ?? '',
                'budget_period' => $budget->period ?? '',
                'budget_year'   => $budget->from ?? '',
            ];

            $adminUsers = User::where('type', PMC::CPN)
                ->where(DC::COL_TABLE_CREATOR, $creator->creatorId())
                ->get();

            foreach ($adminUsers as $admin) {
                if (!empty($admin->email)) {
                    self::sendEmailTemplate('new_budget', [$admin->email], $emailObj);
                }
            }

            $webhook = self::webhookSetting('new_budget', $creator->id);
            if (is_array($webhook) && !empty($webhook['url'])) {
                self::webhookCall($webhook['url'], $budget->toArray(), $webhook['method'] ?? 'POST');
            }
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['budget_id' => $budget->id ?? null, 'error' => $e->getMessage()]);
        }
    }
}
