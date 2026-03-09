<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum MessagingPlatform: string
{
	// Core messaging platforms
	case Email       = 'email';
	case Sms         = 'sms';
	case Internal    = 'internal';

		// Popular messaging apps
	case WhatsApp    = 'whatsapp';
	case Slack       = 'slack';
	case Signal      = 'signal';
	case Telegram    = 'telegram';
	case Messenger   = 'messenger';
	case Instagram   = 'instagram';

		// Corporate/enterprise platforms
	case Twilio      = 'twilio';
	case Teams       = 'teams';
	case Discord     = 'discord';
	case WeChat      = 'wechat';
	case Viber       = 'viber';
	case Line        = 'line';
	case Skype       = 'skype';
	case Zoom        = 'zoom';
	case GoogleChat  = 'google_chat';
	case Mattermost  = 'mattermost';
	case RocketChat  = 'rocket_chat';
	case Nextcloud   = 'nextcloud';
	case Zendesk     = 'zendesk';
	case Intercom    = 'intercom';
	case Freshchat   = 'freshchat';
	case HelpScout   = 'helpscout';
	case ZohoDesk    = 'zoho_desk';
	case LiveChat    = 'livechat';
	case Crisp       = 'crisp';
	case Drift       = 'drift';

		// Other platforms
	case Facebook    = 'facebook';
	case Twitter     = 'twitter';
	case LinkedIn    = 'linkedin';
	case Pinterest   = 'pinterest';
	case Snapchat    = 'snapchat';
	case TikTok      = 'tiktok';

		// Voice/video platforms
	case VoiceCall   = 'voice_call';
	case VideoCall   = 'video_call';

		// Other
	case Other       = 'other';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Email;

		$normalizedValue = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($value ?? '')));
		return match ($normalizedValue) {
			// Core messaging platforms
			'email', 'mail', 'e-mail' => self::Email,
			'sms', 'text', 'textmessage' => self::Sms,
			'internal', 'inapp', 'in_app', 'app' => self::Internal,

			// Popular messaging apps
			'whatsapp', 'whatsappbusiness', 'whatsappbusinessapi' => self::WhatsApp,
			'slack', 'slackapi' => self::Slack,
			'signal' => self::Signal,
			'telegram', 'telegrambot', 'telegramapi' => self::Telegram,
			'messenger', 'facebookmessenger', 'fbmesaenger' => self::Messenger,
			'instagram', 'instagramdirect', 'ig', 'igdm' => self::Instagram,

			// Corporate/enterprise platforms
			'twilio', 'twiliosms', 'twiliowhatsapp' => self::Twilio,
			'teams', 'microsoftteams', 'teamschat' => self::Teams,
			'discord', 'discordbot' => self::Discord,
			'wechat', 'wechatwork', 'wecom' => self::WeChat,
			'viber', 'viberbusiness' => self::Viber,
			'line', 'linebusiness' => self::Line,
			'skype', 'skypeforbusiness' => self::Skype,
			'zoom', 'zoomchat' => self::Zoom,
			'googlechat', 'googleworkspace', 'gchat' => self::GoogleChat,
			'mattermost' => self::Mattermost,
			'rocketchat', 'rocket' => self::RocketChat,
			'nextcloud', 'nextcloudtalk', 'nc', 'nctalk' => self::Nextcloud,
			'zendesk', 'zendesksupport' => self::Zendesk,
			'intercom' => self::Intercom,
			'freshchat', 'freshworks' => self::Freshchat,
			'helpscout' => self::HelpScout,
			'zohodesk', 'zohosupport' => self::ZohoDesk,
			'livechat' => self::LiveChat,
			'crisp', 'crispchat' => self::Crisp,
			'drift' => self::Drift,

			// Other platforms
			'facebook', 'fb', 'facebookpage' => self::Facebook,
			'twitter', 'x', 'twitterdm' => self::Twitter,
			'linkedin', 'linkedinmessaging' => self::LinkedIn,
			'pinterest', 'pinterestmessage' => self::Pinterest,
			'snapchat', 'snap' => self::Snapchat,
			'tiktok', 'tiktokmessage' => self::TikTok,

			// Voice/video platforms
			'voicecall', 'voice', 'call' => self::VoiceCall,
			'videocall', 'video', 'videoconference' => self::VideoCall,

			// Other
			'other', 'custom', 'unknown' => self::Other,

			default => self::Email,
		};
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public static function labels($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::labelsPtBr(),
			'es', 'es-es' => self::labelsEs(),
			'ar', 'ar-sa' => self::labelsAr(),
			'da', 'da-dk' => self::labelsDa(),
			'de', 'de-de' => self::labelsDe(),
			'fr', 'fr-fr' => self::labelsFr(),
			'he', 'he-il' => self::labelsHe(),
			'it', 'it-it' => self::labelsIt(),
			'ja', 'ja-jp' => self::labelsJa(),
			'nl', 'nl-nl' => self::labelsNl(),
			'pl', 'pl-pl' => self::labelsPl(),
			'ru', 'ru-ru' => self::labelsRu(),
			'tr', 'tr-tr' => self::labelsTr(),
			'zh', 'zh-cn' => self::labelsZh(),
			default => self::labelsEn(),
		};
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Email->value      => 'E-mail',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Interno',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Chamada de Voz',
			self::VideoCall->value  => 'Videoconferência',
			self::Other->value      => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Email->value      => 'Email',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Internal',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Voice Call',
			self::VideoCall->value  => 'Video Call',
			self::Other->value      => 'Other',
		];
	}
	public static function labelsEs(): array
	{
		return [
			self::Email->value      => 'Correo Electrónico',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Interno',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Llamada de Voz',
			self::VideoCall->value  => 'Videollamada',
			self::Other->value      => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Email->value      => 'البريد الإلكتروني',
			self::Sms->value        => 'رسالة نصية',
			self::Internal->value   => 'داخلي',
			self::WhatsApp->value   => 'واتساب',
			self::Slack->value      => 'سلاك',
			self::Signal->value     => 'سجنال',
			self::Telegram->value   => 'تيليجرام',
			self::Messenger->value  => 'ماسنجر',
			self::Instagram->value  => 'إنستغرام',
			self::Twilio->value     => 'تويلو',
			self::Teams->value      => 'مايكروسوفت تيمز',
			self::Discord->value    => 'ديسكورد',
			self::WeChat->value     => 'وي تشات',
			self::Viber->value      => 'فايبر',
			self::Line->value       => 'لاين',
			self::Skype->value      => 'سكايب',
			self::Zoom->value       => 'زووم',
			self::GoogleChat->value => 'جوجل شات',
			self::Mattermost->value => 'ماترموست',
			self::RocketChat->value => 'روكت.شات',
			self::Nextcloud->value  => 'نكست كلاود توك',
			self::Zendesk->value    => 'زينديسك',
			self::Intercom->value   => 'إنتركوم',
			self::Freshchat->value  => 'فريش شات',
			self::HelpScout->value  => 'هيلب سكاوت',
			self::ZohoDesk->value   => 'زوهو ديسك',
			self::LiveChat->value   => 'لايف شات',
			self::Crisp->value      => 'كريسب',
			self::Drift->value      => 'درافت',
			self::Facebook->value   => 'فيسبوك',
			self::Twitter->value    => 'تويتر',
			self::LinkedIn->value   => 'لينكد إن',
			self::Pinterest->value  => 'بينتريست',
			self::Snapchat->value   => 'سناب شات',
			self::TikTok->value     => 'تيك توك',
			self::VoiceCall->value  => 'مكالمة صوتية',
			self::VideoCall->value  => 'مكالمة فيديو',
			self::Other->value      => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Email->value      => 'E-mail',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Internt',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Stemmeopkald',
			self::VideoCall->value  => 'Videoopkald',
			self::Other->value      => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Email->value      => 'E-Mail',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Intern',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Sprachanruf',
			self::VideoCall->value  => 'Videoanruf',
			self::Other->value      => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Email->value      => 'E-mail',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Interne',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Appel Vocal',
			self::VideoCall->value  => 'Appel Vidéo',
			self::Other->value      => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Email->value      => 'אימייל',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'פנימי',
			self::WhatsApp->value   => 'וואטסאפ',
			self::Slack->value      => 'סלאק',
			self::Signal->value     => 'סיגנל',
			self::Telegram->value   => 'טלגרם',
			self::Messenger->value  => 'מסנג\'ר',
			self::Instagram->value  => 'אינסטגרם',
			self::Twilio->value     => 'טוויליו',
			self::Teams->value      => 'מייקרוסופט טימס',
			self::Discord->value    => 'דיסקורד',
			self::WeChat->value     => 'וויצ\'ט',
			self::Viber->value      => 'וויבר',
			self::Line->value       => 'ליין',
			self::Skype->value      => 'סקייפ',
			self::Zoom->value       => 'זום',
			self::GoogleChat->value => 'גוגל צ\'אט',
			self::Mattermost->value => 'מטר מוסט',
			self::RocketChat->value => 'רוקט.צ\'אט',
			self::Nextcloud->value  => 'נקסט קלאוד טוק',
			self::Zendesk->value    => 'זנדסק',
			self::Intercom->value   => 'אינטרקום',
			self::Freshchat->value  => 'פרשצ\'אט',
			self::HelpScout->value  => 'הלפ סקאוט',
			self::ZohoDesk->value   => 'זוהו דסק',
			self::LiveChat->value   => 'לייב צ\'אט',
			self::Crisp->value      => 'קריספ',
			self::Drift->value      => 'דריפט',
			self::Facebook->value   => 'פייסבוק',
			self::Twitter->value    => 'טוויטר',
			self::LinkedIn->value   => 'לינקדאין',
			self::Pinterest->value  => 'פינטרסט',
			self::Snapchat->value   => 'סנאפצ\'אט',
			self::TikTok->value     => 'טיקטוק',
			self::VoiceCall->value  => 'שיחת קול',
			self::VideoCall->value  => 'שיחת וידאו',
			self::Other->value      => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Email->value      => 'E-mail',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Interno',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Chiamata Vocale',
			self::VideoCall->value  => 'Videochiamata',
			self::Other->value      => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Email->value      => 'メール',
			self::Sms->value        => 'SMS',
			self::Internal->value   => '内部',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => '音声通話',
			self::VideoCall->value  => 'ビデオ通話',
			self::Other->value      => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Email->value      => 'E-mail',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Intern',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Spraakgesprek',
			self::VideoCall->value  => 'Videogesprek',
			self::Other->value      => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Email->value      => 'E-mail',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Wewnętrzny',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Rozmowa głosowa',
			self::VideoCall->value  => 'Rozmowa wideo',
			self::Other->value      => 'Inny',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Email->value      => 'Электронная почта',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Внутренний',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Голосовой вызов',
			self::VideoCall->value  => 'Видеовызов',
			self::Other->value      => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Email->value      => 'E-posta',
			self::Sms->value        => 'SMS',
			self::Internal->value   => 'Dahili',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => 'WeChat',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => 'Sesli Görüşme',
			self::VideoCall->value  => 'Görüntülü Görüşme',
			self::Other->value      => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Email->value      => '电子邮件',
			self::Sms->value        => '短信',
			self::Internal->value   => '内部',
			self::WhatsApp->value   => 'WhatsApp',
			self::Slack->value      => 'Slack',
			self::Signal->value     => 'Signal',
			self::Telegram->value   => 'Telegram',
			self::Messenger->value  => 'Messenger',
			self::Instagram->value  => 'Instagram',
			self::Twilio->value     => 'Twilio',
			self::Teams->value      => 'Microsoft Teams',
			self::Discord->value    => 'Discord',
			self::WeChat->value     => '微信',
			self::Viber->value      => 'Viber',
			self::Line->value       => 'Line',
			self::Skype->value      => 'Skype',
			self::Zoom->value       => 'Zoom',
			self::GoogleChat->value => 'Google Chat',
			self::Mattermost->value => 'Mattermost',
			self::RocketChat->value => 'Rocket.Chat',
			self::Nextcloud->value  => 'Nextcloud Talk',
			self::Zendesk->value    => 'Zendesk',
			self::Intercom->value   => 'Intercom',
			self::Freshchat->value  => 'Freshchat',
			self::HelpScout->value  => 'Help Scout',
			self::ZohoDesk->value   => 'Zoho Desk',
			self::LiveChat->value   => 'LiveChat',
			self::Crisp->value      => 'Crisp',
			self::Drift->value      => 'Drift',
			self::Facebook->value   => 'Facebook',
			self::Twitter->value    => 'Twitter',
			self::LinkedIn->value   => 'LinkedIn',
			self::Pinterest->value  => 'Pinterest',
			self::Snapchat->value   => 'Snapchat',
			self::TikTok->value     => 'TikTok',
			self::VoiceCall->value  => '语音通话',
			self::VideoCall->value  => '视频通话',
			self::Other->value      => '其他',
		];
	}
}
