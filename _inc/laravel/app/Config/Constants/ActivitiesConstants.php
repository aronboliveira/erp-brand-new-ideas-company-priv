<?php

namespace App\Config\Constants;

class ActivitiesConstants
{
	public const COL_MD = 'module';
	public const COL_U = 'user_id';
	public const COL_PJ = 'project_id';
	public const COL_PJ_NM = 'project_name';
	public const COL_TSK = 'task_id';
	public const COL_DL = 'deal_id';
	public const COL_BUG = 'bug_id';
	public const COL_MI = self::COL_MD . '_id';
	public const COL_MT = self::COL_MD . '_type';
	public const COL_LT = 'log_type';
	public const COL_NT = 'note';
	public const COL_A_O_M = 'agent_or_manager';
	public const COL_DESC = 'description';
	public const COL_TT = 'title';
	public const COL_TSK_TIME = 'time';
	public const COL_TSK_DATE = 'date';
	public const COL_TSK_ID = 'task_id';
	public const COL_TSK_STT = 'status';
	public const COL_OD = 'order';
	public const COL_TP = 'type';
	public const COL_ST_TIME = 'start_time';
	public const COL_E_TIME = 'end_time';
	public const COL_TTL_TIME = 'total_time';
	public const COL_ITV_TIME = 'interval_time';
	public const COL_SCHD_TP = 'schedule_type';
	public const COL_IA = 'is_active';
	public const COL_PW = 'password';
	public const COL_CPT = 'complete';
	public const COL_MUNIT = 'measurement_unit';
	public const COL_AV_FROM = 'available_from';
	public const COL_AV_UNTIL = 'available_until';
	public const COL_RES_ID = 'responsible_id';
	public const COL_EV_ID = 'event_id';
	public const COL_CLK_IN = 'clock_in';
	public const COL_CLK_OUT = 'clock_out';
	public const COL_ERL_ARV = 'early_arrival';
	public const COL_ERL_LV = 'early_leaving';
	public const COL_ERL_AV_CT = 'early_arrival_count';
	public const COL_LT_CT = 'late_count';
	public const COL_ERL_LV_CT = 'early_leaving_count';
	public const COL_OVT_CT = 'overtime_count';
	public const COL_TT_RST = 'total_rest';
	public const COL_TT_WRK = 'total_work';
	public const COL_OVT_ID = 'overtime_id';
}
