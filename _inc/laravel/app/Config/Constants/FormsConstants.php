<?php

namespace App\Config\Constants;

class FormsConstants
{
	public const COL_FM_ID = 'form_id';
	public const COL_CT_FD_ID = 'custom_field_id';
	public const COL_CT_QT_ID = 'custom_question_id';
	public const COL_SUBJ_ID = 'subject_id';
	public const COL_NM_ID = 'name_id';
	public const COL_EML_ID = 'email_id';
	public const COL_PPL_ID = 'pipeline_id';
	public const COL_HTML_ID = 'html_id';
	public const COL_HTML_LB = 'html_label';
	public const COL_FM_DT_RSP_ID = 'form_data_response_id';
	public const COL_RDR_URL = 'redirect_url';
	public const COL_SBM_CNT = 'submission_count';
	public const COL_SBM_URL = 'submission_data_url';
	public const COL_DPL_URL = 'deployment_url';
	public const COL_OTHER_URLS = 'other_urls';
	public const COL_ACPT_SBM = 'accepting_submissions';
	public const COL_DPL_BY = 'deployed_by';
	public const COL_DPL_AT = 'deployed_at';
	public const COL_AUTH_DPLS = 'authorized_deployment_roles';
	public const COL_PUB_AT = 'published_at';
	public const COL_PUB_BY = 'published_by';
	public const COL_AUTH_PUBS = 'authorized_publishing_roles';
	public const COL_ALW_DMS = 'allowed_domains';
	public const COL_LMT_ONE_PRSN = 'limit_one_per_user';
	public const COL_EXP_AT = 'expires_at';
	public const COL_CAPTCHA_PRV = 'captcha_provider';
	public const COL_CAPTCHA_APV = 'captcha_approved';
	public const COL_RT_LMT = 'rate_limit';
	public const COL_BLK_IP_RG = 'blocked_ip_ranges';
	public const COL_ALW_EDT_AFT_SB = 'allow_edit_after_submission';
	public const COL_WBHKS = 'webhooks';
	public const COL_NTFS = 'notifications';
	public const COL_RQ_LOGIN = 'requires_login';
	public const COL_RTT_DAYS = 'retention_days';
	public const COL_CST_RQ = 'consent_required';
	public const COL_CST_DOC = 'consent_document';
	public const COL_CST_CHK = 'consent_checked';
	public const COL_CSRF_CHK_REQ = 'csrf_check_required';
	public const COl_CSRF_TKN_APV = 'csrf_token_approved';
	public const COL_PRV_PL_DOC = 'privacy_policy_document';
	public const COL_SBM_EML = 'submission_email';
	public const COL_SBM_IP = 'submission_ip';
	public const COL_SBM_UA = 'submission_user_agent';
	public const PERMISSIONS = [
		PermissionsConstants::MNG_FM_BD,
		'create form builder',
		'edit form builder',
		'delete form builder',
		'manage form field',
		'create form field',
		'edit form field',
		'delete form field',
		'view form response',
		PermissionsConstants::MNG_PRF_TP,
		PermissionsConstants::CRT_PRF_TP,
		PermissionsConstants::ED_PRF_TP,
		PermissionsConstants::DEL_PRF_TP,
		'manage budget plan',
		'create budget plan',
		'edit budget plan',
		'delete budget plan',
		'view budget plan',
		PermissionsConstants::STK_RPT,
		PermissionsConstants::MNG_WRH,
		'create warehouse',
		'edit warehouse',
		'show warehouse',
		'delete warehouse',
		PermissionsConstants::MNG_PRC,
		'create purchase',
		'edit purchase',
		'view purchase',
		'show purchase',
		'delete purchase',
		'send purchase',
		'create payment purchase',
		PermissionsConstants::MNG_POS,
		'manage contract type',
		'create contract type',
		'edit contract type',
		'delete contract type',
		PermissionsConstants::CR_BC,
		PermissionsConstants::SHW_CRM_DSB,
		'share project',
		PermissionsConstants::SHW_POS_DSB,
		'create webhook',
		PermissionsConstants::ED_WHK,
		PermissionsConstants::DEL_WHK,
	];
	public const COMPANY_PERMISSIONS = [
		PermissionsConstants::MNG_FM_BD,
		'create form builder',
		'edit form builder',
		'delete form builder',
		'manage form field',
		'create form field',
		'edit form field',
		'delete form field',
		'view form response',
		PermissionsConstants::MNG_PRF_TP,
		PermissionsConstants::CRT_PRF_TP,
		PermissionsConstants::ED_PRF_TP,
		PermissionsConstants::DEL_PRF_TP,
		'manage budget plan',
		'create budget plan',
		'edit budget plan',
		'delete budget plan',
		'view budget plan',
		PermissionsConstants::STK_RPT,
		PermissionsConstants::MNG_WRH,
		'create warehouse',
		'edit warehouse',
		'show warehouse',
		'delete warehouse',
		PermissionsConstants::MNG_PRC,
		'create purchase',
		'edit purchase',
		'show purchase',
		'delete purchase',
		'send purchase',
		'create payment purchase',
		PermissionsConstants::MNG_POS,
		'manage contract type',
		'create contract type',
		'edit contract type',
		'delete contract type',
		PermissionsConstants::CR_BC,
		PermissionsConstants::SHW_CRM_DSB,
		'share project',
		PermissionsConstants::SHW_POS_DSB,
		'create webhook',
		PermissionsConstants::ED_WHK,
		PermissionsConstants::DEL_WHK,
	];
}
