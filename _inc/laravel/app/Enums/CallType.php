<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum CallType: string
{
	case Phone = 'phone';
	case WhatsApp = 'whatsapp';
	case GoogleMeet = 'google_meet';
	case Zoom = 'zoom';
	case MicrosoftTeams = 'microsoft_teams';
	case Slack = 'slack';
	case NextCloud = 'nextcloud';
	case Skype = 'skype';
	case Telegram = 'telegram';
	case Signal = 'signal';
	case Discord = 'discord';
	case FacebookMessenger = 'facebook_messenger';
	case Instagram = 'instagram';
	case WeChat = 'wechat';
	case Viber = 'viber';
	case Line = 'line';
	case Webex = 'webex';
	case Jitsi = 'jitsi';
	case GoToMeeting = 'goto_meeting';
	case BlueJeans = 'bluejeans';
	case RingCentral = 'ringcentral';
	case Vonage = 'vonage';
	case Twilio = 'twilio';
	case WebRTC = 'webrtc';
	case SIP = 'sip';
	case VoIP = 'voip';
	case VideoConference = 'video_conference';
	case AudioConference = 'audio_conference';
	case ScreenShare = 'screen_share';
	case Other = 'other';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Other;

		$v = strtolower(trim($value));

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// PHONE
			'phone' => self::Phone,
			'phone_call' => self::Phone,
			'telephone' => self::Phone,
			'landline' => self::Phone,
			'mobile' => self::Phone,
			'cellular' => self::Phone,
			'celular' => self::Phone,
			'telefone' => self::Phone,
			'telefonia' => self::Phone,
			'pstn' => self::Phone,
			'public_switched_telephone_network' => self::Phone,
			'telefónica' => self::Phone,
			'téléphone' => self::Phone,
			'telefon' => self::Phone,
			'telefono' => self::Phone,
			'電話' => self::Phone,
			'手机' => self::Phone,

			// WHATSAPP
			'whatsapp' => self::WhatsApp,
			'whatsapp_call' => self::WhatsApp,
			'whatsapp_voice' => self::WhatsApp,
			'whatsapp_video' => self::WhatsApp,
			'whatsapp_audio' => self::WhatsApp,
			'whatsapp_chamada' => self::WhatsApp,
			'whatsapp_llamada' => self::WhatsApp,
			'whatsapp_通話' => self::WhatsApp,
			'whatsapp通话' => self::WhatsApp,

			// GOOGLE MEET
			'google_meet' => self::GoogleMeet,
			'googlemeet' => self::GoogleMeet,
			'google_meet_call' => self::GoogleMeet,
			'google_meeting' => self::GoogleMeet,
			'google_meet_video' => self::GoogleMeet,
			'google_meet_audio' => self::GoogleMeet,
			'google_hangouts' => self::GoogleMeet,
			'hangouts' => self::GoogleMeet,
			'google_chat' => self::GoogleMeet,
			'google_workspace' => self::GoogleMeet,
			'gmeet' => self::GoogleMeet,
			'グーグルミート' => self::GoogleMeet,
			'谷歌会议' => self::GoogleMeet,

			// ZOOM
			'zoom' => self::Zoom,
			'zoom_call' => self::Zoom,
			'zoom_meeting' => self::Zoom,
			'zoom_video' => self::Zoom,
			'zoom_audio' => self::Zoom,
			'zoom_webinar' => self::Zoom,
			'zoom_conference' => self::Zoom,
			'zoom_会議' => self::Zoom,
			'zoom会议' => self::Zoom,

			// MICROSOFT TEAMS
			'microsoft_teams' => self::MicrosoftTeams,
			'teams' => self::MicrosoftTeams,
			'ms_teams' => self::MicrosoftTeams,
			'microsoft_teams_call' => self::MicrosoftTeams,
			'microsoft_teams_meeting' => self::MicrosoftTeams,
			'teams_call' => self::MicrosoftTeams,
			'teams_meeting' => self::MicrosoftTeams,
			'ms_teams_call' => self::MicrosoftTeams,
			'office_365_teams' => self::MicrosoftTeams,
			'マイクロソフトチームズ' => self::MicrosoftTeams,
			'微软团队' => self::MicrosoftTeams,

			// SLACK
			'slack' => self::Slack,
			'slack_call' => self::Slack,
			'slack_huddle' => self::Slack,
			'slack_audio' => self::Slack,
			'slack_video' => self::Slack,
			'slack_meeting' => self::Slack,
			'slack_conference' => self::Slack,
			'slack_通話' => self::Slack,
			'slack通话' => self::Slack,

			// NEXTCLOUD
			'nextcloud' => self::NextCloud,
			'nextcloud_talk' => self::NextCloud,
			'nextcloud_call' => self::NextCloud,
			'nextcloud_meeting' => self::NextCloud,
			'nextcloud_video' => self::NextCloud,
			'nextcloud_audio' => self::NextCloud,
			'nextcloud_conference' => self::NextCloud,
			'ネクストクラウド' => self::NextCloud,
			'NextCloud通话' => self::NextCloud,

			// SKYPE
			'skype' => self::Skype,
			'skype_call' => self::Skype,
			'skype_voice' => self::Skype,
			'skype_video' => self::Skype,
			'skype_audio' => self::Skype,
			'skype_for_business' => self::Skype,
			'skype_meeting' => self::Skype,
			'skype_通話' => self::Skype,
			'skype通话' => self::Skype,

			// TELEGRAM
			'telegram' => self::Telegram,
			'telegram_call' => self::Telegram,
			'telegram_voice' => self::Telegram,
			'telegram_video' => self::Telegram,
			'telegram_audio' => self::Telegram,
			'telegram_voice_chat' => self::Telegram,
			'telegram_meeting' => self::Telegram,
			'テレグラム' => self::Telegram,
			'电报通话' => self::Telegram,

			// SIGNAL
			'signal' => self::Signal,
			'signal_call' => self::Signal,
			'signal_voice' => self::Signal,
			'signal_video' => self::Signal,
			'signal_audio' => self::Signal,
			'signal_meeting' => self::Signal,
			'シグナル' => self::Signal,
			'信号通话' => self::Signal,

			// DISCORD
			'discord' => self::Discord,
			'discord_call' => self::Discord,
			'discord_voice' => self::Discord,
			'discord_video' => self::Discord,
			'discord_audio' => self::Discord,
			'discord_voice_chat' => self::Discord,
			'discord_meeting' => self::Discord,
			'ディスコード' => self::Discord,
			'Discord通话' => self::Discord,

			// FACEBOOK MESSENGER
			'facebook_messenger' => self::FacebookMessenger,
			'messenger' => self::FacebookMessenger,
			'facebook_call' => self::FacebookMessenger,
			'facebook_messenger_call' => self::FacebookMessenger,
			'facebook_voice' => self::FacebookMessenger,
			'facebook_video' => self::FacebookMessenger,
			'facebook_audio' => self::FacebookMessenger,
			'facebook_meeting' => self::FacebookMessenger,
			'フェイスブックメッセンジャー' => self::FacebookMessenger,
			'脸书Messenger通话' => self::FacebookMessenger,

			// INSTAGRAM
			'instagram' => self::Instagram,
			'instagram_call' => self::Instagram,
			'instagram_voice' => self::Instagram,
			'instagram_video' => self::Instagram,
			'instagram_audio' => self::Instagram,
			'instagram_direct' => self::Instagram,
			'instagram_meeting' => self::Instagram,
			'インスタグラム' => self::Instagram,
			'Instagram通话' => self::Instagram,

			// WECHAT
			'wechat' => self::WeChat,
			'wechat_call' => self::WeChat,
			'wechat_voice' => self::WeChat,
			'wechat_video' => self::WeChat,
			'wechat_audio' => self::WeChat,
			'wechat_meeting' => self::WeChat,
			'微信' => self::WeChat,
			'weixin' => self::WeChat,

			// VIBER
			'viber' => self::Viber,
			'viber_call' => self::Viber,
			'viber_voice' => self::Viber,
			'viber_video' => self::Viber,
			'viber_audio' => self::Viber,
			'viber_meeting' => self::Viber,
			'バイバー' => self::Viber,
			'Viber通话' => self::Viber,

			// LINE
			'line' => self::Line,
			'line_call' => self::Line,
			'line_voice' => self::Line,
			'line_video' => self::Line,
			'line_audio' => self::Line,
			'line_meeting' => self::Line,
			'ライン' => self::Line,
			'Line通话' => self::Line,

			// WEBEX
			'webex' => self::Webex,
			'cisco_webex' => self::Webex,
			'webex_call' => self::Webex,
			'webex_meeting' => self::Webex,
			'webex_video' => self::Webex,
			'webex_audio' => self::Webex,
			'webex_conference' => self::Webex,
			'ウェベックス' => self::Webex,
			'Webex通话' => self::Webex,

			// JITSI
			'jitsi' => self::Jitsi,
			'jitsi_meet' => self::Jitsi,
			'jitsi_call' => self::Jitsi,
			'jitsi_meeting' => self::Jitsi,
			'jitsi_video' => self::Jitsi,
			'jitsi_audio' => self::Jitsi,
			'jitsi_conference' => self::Jitsi,
			'ジツィ' => self::Jitsi,
			'Jitsi通话' => self::Jitsi,

			// GOTO MEETING
			'goto_meeting' => self::GoToMeeting,
			'goto' => self::GoToMeeting,
			'go_to_meeting' => self::GoToMeeting,
			'gotomeeting' => self::GoToMeeting,
			'goto_meeting_call' => self::GoToMeeting,
			'goto_meeting_meeting' => self::GoToMeeting,
			'goto_meeting_video' => self::GoToMeeting,
			'goto_meeting_audio' => self::GoToMeeting,
			'ゴートゥーミーティング' => self::GoToMeeting,
			'GoToMeeting通话' => self::GoToMeeting,

			// BLUEJEANS
			'bluejeans' => self::BlueJeans,
			'blue_jeans' => self::BlueJeans,
			'bluejeans_call' => self::BlueJeans,
			'bluejeans_meeting' => self::BlueJeans,
			'bluejeans_video' => self::BlueJeans,
			'bluejeans_audio' => self::BlueJeans,
			'bluejeans_conference' => self::BlueJeans,
			'ブルージーンズ' => self::BlueJeans,
			'BlueJeans通话' => self::BlueJeans,

			// RINGCENTRAL
			'ringcentral' => self::RingCentral,
			'ring_central' => self::RingCentral,
			'ringcentral_call' => self::RingCentral,
			'ringcentral_meeting' => self::RingCentral,
			'ringcentral_video' => self::RingCentral,
			'ringcentral_audio' => self::RingCentral,
			'リングセントラル' => self::RingCentral,
			'RingCentral通话' => self::RingCentral,

			// VONAGE
			'vonage' => self::Vonage,
			'vonage_call' => self::Vonage,
			'vonage_meeting' => self::Vonage,
			'vonage_video' => self::Vonage,
			'vonage_audio' => self::Vonage,
			'vonage_conference' => self::Vonage,
			'ボナージ' => self::Vonage,
			'Vonage通话' => self::Vonage,

			// TWILIO
			'twilio' => self::Twilio,
			'twilio_call' => self::Twilio,
			'twilio_voice' => self::Twilio,
			'twilio_video' => self::Twilio,
			'twilio_audio' => self::Twilio,
			'twilio_conference' => self::Twilio,
			'トゥイリオ' => self::Twilio,
			'Twilio通话' => self::Twilio,

			// WEBRTC
			'webrtc' => self::WebRTC,
			'web_rtc' => self::WebRTC,
			'webrtc_call' => self::WebRTC,
			'webrtc_meeting' => self::WebRTC,
			'webrtc_video' => self::WebRTC,
			'webrtc_audio' => self::WebRTC,
			'webrtc_conference' => self::WebRTC,
			'ウェブアールティーシー' => self::WebRTC,
			'WebRTC通话' => self::WebRTC,

			// SIP
			'sip' => self::SIP,
			'sip_call' => self::SIP,
			'sip_voice' => self::SIP,
			'sip_video' => self::SIP,
			'sip_audio' => self::SIP,
			'sip_conference' => self::SIP,
			'session_initiation_protocol' => self::SIP,
			'SIP通話' => self::SIP,

			// VOIP
			'voip' => self::VoIP,
			'voip_call' => self::VoIP,
			'voip_voice' => self::VoIP,
			'voip_video' => self::VoIP,
			'voip_audio' => self::VoIP,
			'voip_conference' => self::VoIP,
			'voice_over_ip' => self::VoIP,
			'voice_over_internet_protocol' => self::VoIP,
			'VOIP通話' => self::VoIP,

			// VIDEO CONFERENCE
			'video_conference' => self::VideoConference,
			'video_conference_call' => self::VideoConference,
			'video_conference_meeting' => self::VideoConference,
			'video_meeting' => self::VideoConference,
			'video_call' => self::VideoConference,
			'videoconference' => self::VideoConference,
			'ビデオ会議' => self::VideoConference,
			'视频会议' => self::VideoConference,

			// AUDIO CONFERENCE
			'audio_conference' => self::AudioConference,
			'audio_conference_call' => self::AudioConference,
			'audio_conference_meeting' => self::AudioConference,
			'audio_meeting' => self::AudioConference,
			'audio_call' => self::AudioConference,
			'audioconference' => self::AudioConference,
			'音声会議' => self::AudioConference,
			'音频会议' => self::AudioConference,

			// SCREEN SHARE
			'screen_share' => self::ScreenShare,
			'screen_share_call' => self::ScreenShare,
			'screen_sharing' => self::ScreenShare,
			'screen_share_meeting' => self::ScreenShare,
			'screen_share_video' => self::ScreenShare,
			'screen_share_audio' => self::ScreenShare,
			'画面共有' => self::ScreenShare,
			'屏幕共享' => self::ScreenShare,

			// OTHER
			'other' => self::Other,
			'others' => self::Other,
			'misc' => self::Other,
			'miscellaneous' => self::Other,
			'diverse' => self::Other,
			'outro' => self::Other,
			'otros' => self::Other,
			'autre' => self::Other,
			'andere' => self::Other,
			'altro' => self::Other,
			'その他' => self::Other,
			'其他' => self::Other,
		];

		return $map[$v] ?? self::Other;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Phone => 'Phone Call',
			self::WhatsApp => 'WhatsApp Call',
			self::GoogleMeet => 'Google Meet',
			self::Zoom => 'Zoom',
			self::MicrosoftTeams => 'Microsoft Teams',
			self::Slack => 'Slack',
			self::NextCloud => 'NextCloud',
			self::Skype => 'Skype',
			self::Telegram => 'Telegram',
			self::Signal => 'Signal',
			self::Discord => 'Discord',
			self::FacebookMessenger => 'Facebook Messenger',
			self::Instagram => 'Instagram',
			self::WeChat => 'WeChat',
			self::Viber => 'Viber',
			self::Line => 'Line',
			self::Webex => 'Webex',
			self::Jitsi => 'Jitsi',
			self::GoToMeeting => 'GoToMeeting',
			self::BlueJeans => 'BlueJeans',
			self::RingCentral => 'RingCentral',
			self::Vonage => 'Vonage',
			self::Twilio => 'Twilio',
			self::WebRTC => 'WebRTC',
			self::SIP => 'SIP',
			self::VoIP => 'VoIP',
			self::VideoConference => 'Video Conference',
			self::AudioConference => 'Audio Conference',
			self::ScreenShare => 'Screen Share',
			self::Other => 'Other',
		};
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
			self::Phone->value => 'Telefone',
			self::WhatsApp->value => 'WhatsApp',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Videoconferência',
			self::AudioConference->value => 'Conferência de Áudio',
			self::ScreenShare->value => 'Compartilhamento de Tela',
			self::Other->value => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Phone->value => 'Phone Call',
			self::WhatsApp->value => 'WhatsApp Call',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Video Conference',
			self::AudioConference->value => 'Audio Conference',
			self::ScreenShare->value => 'Screen Share',
			self::Other->value => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Phone->value => 'Llamada Telefónica',
			self::WhatsApp->value => 'Llamada de WhatsApp',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Videoconferencia',
			self::AudioConference->value => 'Conferencia de Audio',
			self::ScreenShare->value => 'Compartir Pantalla',
			self::Other->value => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Phone->value => 'مكالمة هاتفية',
			self::WhatsApp->value => 'مكالمة واتساب',
			self::GoogleMeet->value => 'جوجل ميت',
			self::Zoom->value => 'زووم',
			self::MicrosoftTeams->value => 'مايكروسوفت تيمز',
			self::Slack->value => 'سلاك',
			self::NextCloud->value => 'نكست كلاود',
			self::Skype->value => 'سكايب',
			self::Telegram->value => 'تيليجرام',
			self::Signal->value => 'سيجنال',
			self::Discord->value => 'ديسكورد',
			self::FacebookMessenger->value => 'فيسبوك ماسنجر',
			self::Instagram->value => 'انستجرام',
			self::WeChat->value => 'وي تشات',
			self::Viber->value => 'فايبر',
			self::Line->value => 'لاين',
			self::Webex->value => 'ويبكس',
			self::Jitsi->value => 'جيتسي',
			self::GoToMeeting->value => 'جو تو ميتينغ',
			self::BlueJeans->value => 'بلو جينز',
			self::RingCentral->value => 'رينغ سنترال',
			self::Vonage->value => 'فوناج',
			self::Twilio->value => 'تويليو',
			self::WebRTC->value => 'ويب آر تي سي',
			self::SIP->value => 'أس أي بي',
			self::VoIP->value => 'فو آي بي',
			self::VideoConference->value => 'مؤتمر فيديو',
			self::AudioConference->value => 'مؤتمر صوتي',
			self::ScreenShare->value => 'مشاركة الشاشة',
			self::Other->value => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Phone->value => 'Telefonopkald',
			self::WhatsApp->value => 'WhatsApp-opkald',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Videokonference',
			self::AudioConference->value => 'Audiokonference',
			self::ScreenShare->value => 'Skærmdeling',
			self::Other->value => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Phone->value => 'Telefonanruf',
			self::WhatsApp->value => 'WhatsApp-Anruf',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Videokonferenz',
			self::AudioConference->value => 'Audiokonferenz',
			self::ScreenShare->value => 'Bildschirmfreigabe',
			self::Other->value => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Phone->value => 'Appel téléphonique',
			self::WhatsApp->value => 'Appel WhatsApp',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Visioconférence',
			self::AudioConference->value => 'Conférence audio',
			self::ScreenShare->value => 'Partage d\'écran',
			self::Other->value => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Phone->value => 'שיחת טלפון',
			self::WhatsApp->value => 'שיחת WhatsApp',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'ועידת וידאו',
			self::AudioConference->value => 'ועידת שמע',
			self::ScreenShare->value => 'שיתוף מסך',
			self::Other->value => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Phone->value => 'Chiamata telefonica',
			self::WhatsApp->value => 'Chiamata WhatsApp',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Videoconferenza',
			self::AudioConference->value => 'Conferenza audio',
			self::ScreenShare->value => 'Condivisione schermo',
			self::Other->value => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Phone->value => '電話',
			self::WhatsApp->value => 'WhatsApp通話',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'ビデオ会議',
			self::AudioConference->value => '音声会議',
			self::ScreenShare->value => '画面共有',
			self::Other->value => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Phone->value => 'Telefoongesprek',
			self::WhatsApp->value => 'WhatsApp-gesprek',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Videoconferentie',
			self::AudioConference->value => 'Audioconferentie',
			self::ScreenShare->value => 'Schermdeling',
			self::Other->value => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Phone->value => 'Rozmowa telefoniczna',
			self::WhatsApp->value => 'Rozmowa WhatsApp',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Wideokonferencja',
			self::AudioConference->value => 'Konferencja audio',
			self::ScreenShare->value => 'Udostępnianie ekranu',
			self::Other->value => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Phone->value => 'Телефонный звонок',
			self::WhatsApp->value => 'Звонок WhatsApp',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Видеоконференция',
			self::AudioConference->value => 'Аудиоконференция',
			self::ScreenShare->value => 'Демонстрация экрана',
			self::Other->value => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Phone->value => 'Telefon Görüşmesi',
			self::WhatsApp->value => 'WhatsApp Görüşmesi',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => 'WeChat',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => 'Video Konferans',
			self::AudioConference->value => 'Sesli Konferans',
			self::ScreenShare->value => 'Ekran Paylaşımı',
			self::Other->value => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Phone->value => '电话',
			self::WhatsApp->value => 'WhatsApp通话',
			self::GoogleMeet->value => 'Google Meet',
			self::Zoom->value => 'Zoom',
			self::MicrosoftTeams->value => 'Microsoft Teams',
			self::Slack->value => 'Slack',
			self::NextCloud->value => 'NextCloud',
			self::Skype->value => 'Skype',
			self::Telegram->value => 'Telegram',
			self::Signal->value => 'Signal',
			self::Discord->value => 'Discord',
			self::FacebookMessenger->value => 'Facebook Messenger',
			self::Instagram->value => 'Instagram',
			self::WeChat->value => '微信',
			self::Viber->value => 'Viber',
			self::Line->value => 'Line',
			self::Webex->value => 'Webex',
			self::Jitsi->value => 'Jitsi',
			self::GoToMeeting->value => 'GoToMeeting',
			self::BlueJeans->value => 'BlueJeans',
			self::RingCentral->value => 'RingCentral',
			self::Vonage->value => 'Vonage',
			self::Twilio->value => 'Twilio',
			self::WebRTC->value => 'WebRTC',
			self::SIP->value => 'SIP',
			self::VoIP->value => 'VoIP',
			self::VideoConference->value => '视频会议',
			self::AudioConference->value => '音频会议',
			self::ScreenShare->value => '屏幕共享',
			self::Other->value => '其他',
		];
	}

	// Helper methods for business logic
	public function isVideoCapable(): bool
	{
		return in_array($this, [
			self::WhatsApp,
			self::GoogleMeet,
			self::Zoom,
			self::MicrosoftTeams,
			self::Slack,
			self::NextCloud,
			self::Skype,
			self::Telegram,
			self::Signal,
			self::Discord,
			self::FacebookMessenger,
			self::Instagram,
			self::WeChat,
			self::Viber,
			self::Line,
			self::Webex,
			self::Jitsi,
			self::GoToMeeting,
			self::BlueJeans,
			self::RingCentral,
			self::Vonage,
			self::Twilio,
			self::WebRTC,
			self::VideoConference,
			self::ScreenShare,
		]);
	}

	public function isAudioOnly(): bool
	{
		return in_array($this, [
			self::Phone,
			self::AudioConference,
			self::SIP,
			self::VoIP,
		]);
	}

	public function isMessagingApp(): bool
	{
		return in_array($this, [
			self::WhatsApp,
			self::Telegram,
			self::Signal,
			self::FacebookMessenger,
			self::Instagram,
			self::WeChat,
			self::Viber,
			self::Line,
			self::Skype,
		]);
	}

	public function isBusinessPlatform(): bool
	{
		return in_array($this, [
			self::GoogleMeet,
			self::Zoom,
			self::MicrosoftTeams,
			self::Slack,
			self::Webex,
			self::GoToMeeting,
			self::BlueJeans,
			self::RingCentral,
			self::Vonage,
			self::Twilio,
			self::NextCloud,
			self::Jitsi,
		]);
	}

	public function isConsumerApp(): bool
	{
		return in_array($this, [
			self::WhatsApp,
			self::Skype,
			self::Telegram,
			self::Signal,
			self::Discord,
			self::FacebookMessenger,
			self::Instagram,
			self::WeChat,
			self::Viber,
			self::Line,
		]);
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::Phone => '📞',
			self::WhatsApp => '💚',
			self::GoogleMeet => '📹',
			self::Zoom => '🔍',
			self::MicrosoftTeams => '👥',
			self::Slack => '💬',
			self::NextCloud => '☁️',
			self::Skype => '🔵',
			self::Telegram => '📨',
			self::Signal => '🔒',
			self::Discord => '🎮',
			self::FacebookMessenger => '👤',
			self::Instagram => '📸',
			self::WeChat => '💬',
			self::Viber => '💜',
			self::Line => '💚',
			self::Webex => '🔴',
			self::Jitsi => '🎥',
			self::GoToMeeting => '🎯',
			self::BlueJeans => '👖',
			self::RingCentral => '💍',
			self::Vonage => '📡',
			self::Twilio => '🦉',
			self::WebRTC => '🌐',
			self::SIP => '📡',
			self::VoIP => '📱',
			self::VideoConference => '🎥',
			self::AudioConference => '🎤',
			self::ScreenShare => '🖥️',
			self::Other => '📞',
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Phone => '#4CAF50',
			self::WhatsApp => '#25D366',
			self::GoogleMeet => '#00897B',
			self::Zoom => '#2D8CFF',
			self::MicrosoftTeams => '#6264A7',
			self::Slack => '#4A154B',
			self::NextCloud => '#0082C9',
			self::Skype => '#00AFF0',
			self::Telegram => '#0088CC',
			self::Signal => '#3A76F0',
			self::Discord => '#5865F2',
			self::FacebookMessenger => '#006AFF',
			self::Instagram => '#E4405F',
			self::WeChat => '#07C160',
			self::Viber => '#7360F2',
			self::Line => '#00C300',
			self::Webex => '#FF1B1B',
			self::Jitsi => '#3A76F0',
			self::GoToMeeting => '#FF6900',
			self::BlueJeans => '#00A4DC',
			self::RingCentral => '#0072C6',
			self::Vonage => '#FF0000',
			self::Twilio => '#F22F46',
			self::WebRTC => '#333333',
			self::SIP => '#3F51B5',
			self::VoIP => '#2196F3',
			self::VideoConference => '#9C27B0',
			self::AudioConference => '#FF9800',
			self::ScreenShare => '#607D8B',
			self::Other => '#9E9E9E',
		};
	}

	public function getPlatformType(): string
	{
		return match ($this) {
			self::Phone => 'traditional',
			self::WhatsApp, self::Telegram, self::Signal, self::FacebookMessenger,
			self::Instagram, self::WeChat, self::Viber, self::Line, self::Skype => 'messaging',
			self::GoogleMeet, self::Zoom, self::MicrosoftTeams, self::Slack, self::Webex,
			self::GoToMeeting, self::BlueJeans, self::RingCentral, self::NextCloud, self::Jitsi => 'business',
			self::Discord => 'community',
			self::Vonage, self::Twilio => 'api',
			self::WebRTC, self::SIP, self::VoIP => 'protocol',
			self::VideoConference, self::AudioConference, self::ScreenShare => 'feature',
			self::Other => 'unknown',
		};
	}
}
