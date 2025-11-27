<?php

namespace App\Config\Constants;

class ProjectsConstants
{
	public const COL_PJ_ID = 'project_id';
	public const COL_ASGN = 'assign_to';
	public const COL_ML_ID = 'milestone_id';
	public const COL_PPL_ID = 'pipeline_id';
	public const COL_CL = 'color';
	public const COL_NM = 'name';
	public const COL_LB_NM = 'name';
	public const COL_STG_NM = 'name';
	public const COL_PPL_NM = 'name';
	public const COL_STG = 'stage';
	public const COL_S_DT = 'start_date';
	public const COL_E_DT = 'end_date';
	public const COL_PLN_ST = 'planned_start';
	public const COL_D_DATE = 'due_date';
	public const COL_E_HRS = 'estimated_hrs';
	public const COL_PRT = 'priority';
	public const COL_IS_CP = 'is_complete';
	public const COL_IS_FV = 'is_favorite';
	public const COL_M_AT = 'marked_at';
	public const COL_PGR = 'progress';
	public const COL_PR_CL = 'priority_color';
	public const COL_IMG            = 'project_image';
	public const COL_BUDGET         = 'budget';
	public const COL_CLIENT_ID      = 'client_id';
	public const COL_CLIENT_NAME    = 'client_name';
	public const COL_STAGE_ID       = 'project_stage_id';
	public const COL_DESCRIPTION    = 'description';
	public const COL_STATUS         = 'status';
	public const COL_PASSWORD       = 'password';
	public const COL_COPYLINK       = 'copylinksetting';
	public const COL_TAGS           = 'tags';
	public const COL_CN = 'contract_number';
	public const COL_CDESC = 'contract_description';
	public const COL_ARNW = 'auto_renew';
	public const COL_OBG_NAME = 'obligee_name';
	public const COL_OBL_NAME = 'obligor_name';
	public const COL_OBG_IDF = 'obligee_identifier';
	public const COL_OBL_IDF = 'obligor_identifier';
	public const COL_OBG_ADDR = 'obligee_address';
	public const COL_OBL_ADDR = 'obligor_address';
	public const COL_OBG_CTC = 'obligee_contact';
	public const COL_OBL_CTC = 'obligor_contact';
	public const COL_WT_NM = 'witness_one_name';
	public const COL_WT2_NM = 'witness_two_name';
	public const COL_WT_IDF = 'witness_one_identifier';
	public const COL_WT2_IDF = 'witness_two_identifier';
	public const COL_WT_SIG = 'witness_one_signature';
	public const COL_WT2_SIG = 'witness_two_signature';
	public const COL_WT_SIGN_AT = 'witness_one_signed_at';
	public const COL_WT2_SIGN_AT = 'witness_two_signed_at';
	public const COL_CL_SIG = 'client_signature';
	public const COL_CO_SIG = 'company_signature';
	public const COL_CL_SIGN_AT = 'client_signed_at';
	public const COL_CO_SIGN_AT = 'company_signed_at';
	public const COL_F_PATH = 'file_path';
	public const COL_ATC_PATHS = 'attachment_paths';
	public const COL_APV_BY = 'approved_by';
	public const COL_M_METRIC = 'main_metric';
	public const COL_CRT = 'is_critical';


	public const STT_INP_K					 = 'in_progress';
	public const STT_ONH_K					 = 'on_hold';
	public const STT_CPT_K					 = 'complete';
	public const STT_CCL_K					 = 'canceled';
	public const STT_DEF_K					 = self::STT_INP_K;
	public const STT_INF						 = 'info';
	public const STT_WRN						 = 'warning';
	public const STT_SCS						 = 'success';
	public const STT_DGR						 = 'danger';
	public const STT_DEF_CL					 = self::STT_INF;
	public const VST_PPS = 'purpose_of_visit';
	public const VST_PLC = 'place_of_visit';
	public static array $projectStatus = [
		self::STT_INP_K => 'In Progress',
		self::STT_ONH_K     => 'On Hold',
		self::STT_CPT_K    => 'Complete',
		self::STT_CCL_K    => 'Canceled',
	];
	public static array $statusColor = [
		self::STT_INP_K => self::STT_INF,
		self::STT_ONH_K     => self::STT_WRN,
		self::STT_CPT_K    => self::STT_SCS,
		self::STT_CCL_K    => self::STT_DGR,
	];
}
