<?php

namespace Modules\LandingPage\Config\Constants;

class SettingsConstants
{
	public const TB_STT_K      = 'topbar_status';
	public const TB_STT_DEF    = 'on';
	public const TB_NTF_MSG_K  = 'topbar_notification_msg';
	public const TB_NTF_MSG_DEF = '70% Special Offer. Don’t Miss it. The offer ends in 72 hours.';
	public const TOPBAR_SETTINGS = [
		self::TB_STT_K     => self::TB_STT_DEF,
		self::TB_NTF_MSG_K => self::TB_NTF_MSG_DEF,
	];
	public const MB_STT_K  = 'menubar_status';
	public const MB_STT_DEF = 'on';
	public const MB_PG_K   = 'menubar_page';
	public const MB_PG_DEF = '[]';
	public const MB_PG_CT = 'menubar_page_content';
	public const MB_PG_NM = 'menubar_page_name';
	public const MENUBAR_SETTINGS = [
		self::MB_STT_K => self::MB_STT_DEF,
		self::MB_PG_K  => self::MB_PG_DEF,
	];
	public const SL_K  = 'site_logo';
	public const SL_DEF = '';
	public const SD_K  = 'site_description';
	public const SD_DEF = '';
	public const SITE_SETTINGS = [
		self::SL_K => self::SL_DEF,
		self::SD_K => self::SD_DEF,
	];
	public const HM_STT_K      = 'home_status';
	public const HM_STT_DEF    = 'on';
	public const HM_OFF_TXT_K  = 'home_offer_text';
	public const HM_OFF_TXT_DEF = '';
	public const HM_TTL_K      = 'home_title';
	public const HM_TTL_DEF    = 'Home';
	public const HM_HDG_K      = 'home_heading';
	public const HM_HDG_DEF    = '';
	public const HM_DESC_K     = 'home_description';
	public const HM_DESC_DEF   = '';
	public const HM_TRST_BY_K  = 'home_trusted_by';
	public const HM_TRST_BY_DEF = '';
	public const HM_DEMO_LNK_K = 'home_live_demo_link';
	public const HM_DEMO_LNK_DEF = '';
	public const HM_BUY_LNK_K  = 'home_buy_now_link';
	public const HM_BUY_LNK_DEF = '';
	public const HM_BNR_K      = 'home_banner';
	public const HM_BNR_DEF    = '';
	public const HM_LGO_K      = 'home_logo';
	public const HM_LGO_DEF    = '';
	public const HOME_SETTINGS = [
		self::HM_STT_K      => self::HM_STT_DEF,
		self::HM_OFF_TXT_K  => self::HM_OFF_TXT_DEF,
		self::HM_TTL_K      => self::HM_TTL_DEF,
		self::HM_HDG_K      => self::HM_HDG_DEF,
		self::HM_DESC_K     => self::HM_DESC_DEF,
		self::HM_TRST_BY_K  => self::HM_TRST_BY_DEF,
		self::HM_DEMO_LNK_K => self::HM_DEMO_LNK_DEF,
		self::HM_BUY_LNK_K  => self::HM_BUY_LNK_DEF,
		self::HM_BNR_K      => self::HM_BNR_DEF,
		self::HM_LGO_K      => self::HM_LGO_DEF,
	];
	public const FT_STT_K       = 'feature_status';
	public const FT_STT_DEF     = 'on';
	public const FT_TTL_K       = 'feature_title';
	public const FT_TTL_DEF     = 'Features';
	public const FT_HDG_K       = 'feature_heading';
	public const FT_HDG_DEF     = '';
	public const FT_DESC_K      = 'feature_description';
	public const FT_DESC_DEF    = '';
	public const FT_BUY_LNK_K   = 'feature_buy_now_link';
	public const FT_BUY_LNK_DEF = '';
	public const FT_OF_FTS_K    = 'feature_of_features';
	public const FT_OF_FTS_DEF  = '';
	public const FT_BNR_K    		 = 'feature_banner';
	public const FT_BNR_DEF     = '';
	public const FT_BNR_HD_K		 = 'feature_banner_heading';
	public const FT_BNR_HD_DEF	 = '';
	public const FT_BNR_DESC_K  = 'feature_banner_description';
	public const FT_BNR_DESC_DEF = '';
	public const FEATURE_SETTINGS = [
		self::FT_STT_K      => self::FT_STT_DEF,
		self::FT_TTL_K      => self::FT_TTL_DEF,
		self::FT_HDG_K      => self::FT_HDG_DEF,
		self::FT_DESC_K     => self::FT_DESC_DEF,
		self::FT_BUY_LNK_K  => self::FT_BUY_LNK_DEF,
		self::FT_OF_FTS_K   => self::FT_OF_FTS_DEF,
		self::FT_BNR_HD_K   => self::FT_BNR_HD_DEF,
		self::FT_BNR_DESC_K => self::FT_BNR_DESC_DEF
	];
	public const HF_HDG_K  = 'highlight_feature_heading';
	public const HF_HDG_DEF = '';
	public const HF_DESC_K = 'highlight_feature_description';
	public const HF_DESC_DEF = '';
	public const HF_IMG_K  = 'highlight_feature_image';
	public const HF_IMG_DEF = '';
	public const HIGHLIGHT_FEATURE_SETTINGS = [
		self::HF_HDG_K  => self::HF_HDG_DEF,
		self::HF_DESC_K => self::HF_DESC_DEF,
		self::HF_IMG_K  => self::HF_IMG_DEF,
	];
	public const OT_FTS_K  = 'other_features';
	public const OT_FTS_DEF = '';
	public const OTHER_FEATURES_SETTINGS = [
		self::OT_FTS_K => self::OT_FTS_DEF,
	];
	public const DC_STT_K      = 'discover_status';
	public const DC_STT_DEF    = 'on';
	public const DC_HDG_K      = 'discover_heading';
	public const DC_HDG_DEF    = '';
	public const DC_DESC_K     = 'discover_description';
	public const DC_DESC_DEF   = '';
	public const DC_DEMO_LNK_K = 'discover_live_demo_link';
	public const DC_DEMO_LNK_DEF = '';
	public const DC_BUY_LNK_K  = 'discover_buy_now_link';
	public const DC_BUY_LNK_DEF = '';
	public const DC_OF_FTS_K   = 'discover_of_features';
	public const DC_OF_FTS_DEF = '';
	public const DISCOVER_SETTINGS = [
		self::DC_STT_K     => self::DC_STT_DEF,
		self::DC_HDG_K     => self::DC_HDG_DEF,
		self::DC_DESC_K    => self::DC_DESC_DEF,
		self::DC_DEMO_LNK_K => self::DC_DEMO_LNK_DEF,
		self::DC_BUY_LNK_K => self::DC_BUY_LNK_DEF,
		self::DC_OF_FTS_K  => self::DC_OF_FTS_DEF,
	];
	public const SC_STT_K   = 'screenshots_status';
	public const SC_STT_DEF = 'on';
	public const SC_HDG_K   = 'screenshots_heading';
	public const SC_HDG_DEF = '';
	public const SC_DESC_K  = 'screenshots_description';
	public const SC_DESC_DEF = '';
	public const SC_SHTS_K  = 'screenshots';
	public const SC_SHTS_DEF = '';
	public const SCREENSHOTS_SETTINGS = [
		self::SC_STT_K   => self::SC_STT_DEF,
		self::SC_HDG_K   => self::SC_HDG_DEF,
		self::SC_DESC_K  => self::SC_DESC_DEF,
		self::SC_SHTS_K  => self::SC_SHTS_DEF,
	];
	public const PN_STT_K  = 'plan_status';
	public const PN_STT_DEF = 'on';
	public const PN_TTL_K  = 'plan_title';
	public const PN_TTL_DEF = 'Plan';
	public const PN_HDG_K  = 'plan_heading';
	public const PN_HDG_DEF = '';
	public const PN_DESC_K = 'plan_description';
	public const PN_DESC_DEF = '';
	public const PLAN_SETTINGS = [
		self::PN_STT_K  => self::PN_STT_DEF,
		self::PN_TTL_K  => self::PN_TTL_DEF,
		self::PN_HDG_K  => self::PN_HDG_DEF,
		self::PN_DESC_K => self::PN_DESC_DEF,
	];
	public const FAQ_STT_K   = 'faq_status';
	public const FAQ_STT_DEF = 'on';
	public const FAQ_TTL_K   = 'faq_title';
	public const FAQ_TTL_DEF = 'Faq';
	public const FAQ_HDG_K   = 'faq_heading';
	public const FAQ_HDG_DEF = '';
	public const FAQ_DESC_K  = 'faq_description';
	public const FAQ_DESC_DEF = '';
	public const FAQ_FQS_K   = 'faqs';
	public const FAQ_FQS_DEF = '';
	public const FAQ_SETTINGS = [
		self::FAQ_STT_K  => self::FAQ_STT_DEF,
		self::FAQ_TTL_K  => self::FAQ_TTL_DEF,
		self::FAQ_HDG_K  => self::FAQ_HDG_DEF,
		self::FAQ_DESC_K => self::FAQ_DESC_DEF,
		self::FAQ_FQS_K  => self::FAQ_FQS_DEF,
	];
	public const TM_TTL_K					 = 'testimonials_title';
	public const TM_TTL_DEF				 = 'Testimonial';
	public const TM_STT_K          = 'testimonials_status';
	public const TM_STT_DEF        = 'on';
	public const TM_HDG_K          = 'testimonials_heading';
	public const TM_HDG_DEF        = '';
	public const TM_DESC_K         = 'testimonials_description';
	public const TM_DESC_DEF       = '';
	public const TM_LONG_DESC_K    = 'testimonials_long_description';
	public const TM_LONG_DESC_DEF  = '';
	public const TM_TMS_K          = 'testimonials';
	public const TM_TMS_DEF        = '';
	public const TM_USR            = 'testimonials_user';
	public const TM_USR_AV				 = 'testimonials_user_avatar';
	public const TM_USR_DSG				 = 'testimonials_designation';
	public const TM_STR						 = 'testimonials_star';
	public const TESTIMONIALS_SETTINGS = [
		self::TM_TTL_K				=> self::TM_TTL_DEF,
		self::TM_STT_K        => self::TM_STT_DEF,
		self::TM_HDG_K        => self::TM_HDG_DEF,
		self::TM_DESC_K       => self::TM_DESC_DEF,
		self::TM_LONG_DESC_K  => self::TM_LONG_DESC_DEF,
		self::TM_TMS_K        => self::TM_TMS_DEF,
	];
	public const FTR_STT_K      = 'footer_status';
	public const FTR_STT_DEF    = 'on';
	public const FOOTER_SETTINGS = [
		self::FTR_STT_K => self::FTR_STT_DEF,
	];
	public const JU_STT_K       = 'joinus_status';
	public const JU_STT_DEF     = 'on';
	public const JU_HDG_K       = 'joinus_heading';
	public const JU_HDG_DEF     = '';
	public const JU_DESC_K      = 'joinus_description';
	public const JU_DESC_DEF    = '';
	public const JU_SETTINGS = [
		self::JU_STT_K  => self::JU_STT_DEF,
		self::JU_HDG_K  => self::JU_HDG_DEF,
		self::JU_DESC_K => self::JU_DESC_DEF,
	];
	public const JU_USR_SETTINGS = [
		'email' => 'comercial@prestech.com.br',
	];
	public const PG_SLG = 'page_slug';
	public const LANDING_PAGE_SETTINGS = [
		...self::TOPBAR_SETTINGS,
		...self::MENUBAR_SETTINGS,
		...self::SITE_SETTINGS,
		...self::HOME_SETTINGS,
		...self::FEATURE_SETTINGS,
		...self::HIGHLIGHT_FEATURE_SETTINGS,
		...self::OTHER_FEATURES_SETTINGS,
		...self::DISCOVER_SETTINGS,
		...self::SCREENSHOTS_SETTINGS,
		...self::PLAN_SETTINGS,
		...self::FAQ_SETTINGS,
		...self::TESTIMONIALS_SETTINGS,
		...self::FOOTER_SETTINGS,
		...self::JU_SETTINGS,
		...self::JU_USR_SETTINGS,
	];
	public const CPN_FAVICON_K = 'company_favicon';
	public const URI_FAVICON = 'https://prestech.com.br/site/wp-content/uploads/2024/10/Favicon-Prestech-Fundo-Branco.svg';
}
