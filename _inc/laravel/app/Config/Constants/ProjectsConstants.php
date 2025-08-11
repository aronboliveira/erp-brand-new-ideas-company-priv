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
	public const COL_STAGE_ID       = 'project_stage_id';
	public const COL_DESCRIPTION    = 'description';
	public const COL_STATUS         = 'status';
	public const COL_PASSWORD       = 'password';
	public const COL_COPYLINK       = 'copylinksetting';
	public const COL_TAGS           = 'tags';
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
