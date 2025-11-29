<?php

namespace App\Config\Constants;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Schema};

class DatabaseConstants
{

	// * TABLE NAMES

	public const COA = 'chart_of_account';
	public const TABLE_USERS = 'users';
	public const TABLE_CREATOR = 'created_by';
	public const TABLE_UPDATER = 'updated_by';
	public const TABLE_PLANS = 'plans';
	public const TABLE_PROJECTS = 'projects';
	public const TABLE_TASKS = 'tasks';
	public const TABLE_PIPELINES = 'pipelines';
	public const TABLE_DEALS = 'deals';
	public const TABLE_BUGS = 'bugs';
	public const TABLE_EMPLOYEES = 'employees';
	public const TABLE_DOCS = 'documents';
	public const TABLE_EDOCS = 'employee_documents';
	public const TABLE_TERMINATIONS = 'terminations';
	public const TABLE_TERMINATION_TYPES = 'termination_types';
	public const TABLE_RSG = 'resignations';
	public const TABLE_DESIGNS = 'designations';
	public const TABLE_COAS = self::COA . 's';
	public const TABLE_COA_TYPES = self::COA . '_types';
	public const TABLE_COA_SUBTYPES = self::COA . '_sub_types';
	public const TABLE_BILLS = 'bills';
	public const TABLE_PROD_SERVS = 'product_services';
	public const TABLE_PROD_SERV_CATS = 'product_service_categories';
	public const TABLE_PROD_SERV_UNITS = 'product_service_units';
	public const TABLE_VENDORS = 'vendors';
	public const TABLE_CUSTOMERS = 'customers';
	public const TABLE_TAXES = 'taxes';
	public const TABLE_BRANCHES = 'branches';
	public const TABLE_DEPARTMENTS = 'departments';
	public const TABLE_JOB_CATS = 'job_categories';
	public const TABLE_ALLOWANCE_OPTS = 'allowance_options';
	public const TABLE_DEDUCTION_OPTS = 'deductions_options';
	public const TABLE_CONTRACT_TYPES = 'contract_types';
	public const TABLE_BANK_ACC = 'bank_accounts';
	public const TABLE_BNK_TRF = 'bank_transfers';
	public const TABLE_INVS = 'invoices';
	public const TABLE_LEAVE_TYPES = 'leave_types';
	public const TABLE_LV = 'leaves';
	public const TABLE_LEADS = 'leads';
	public const TABLE_COUPONS = 'coupons';
	public const TABLE_CUSTOM_FIELDS = 'custom_fields';
	public const TABLE_EMAIL_TEMPLATES = 'email_templates';
	public const TABLE_TRAINING_TYPES = 'training_types';
	public const TABLE_TRAINERS = 'trainers';
	public const TABLE_JOURNAL_ENTRIES = 'journal_entries';
	public const TABLE_JOB_APPS = 'job_applications';
	public const TABLE_MSS = 'milestones';
	public const TABLE_TSK_STGS = 'task_stages';
	public const TABLE_PROJ_TSKS = 'project_tasks';
	public const TABLE_FORM_BUILD = 'form_builders';
	public const TABLE_FORM_FIELDS = 'form_fields';
	public const TABLE_SUPPORTS = 'supports';
	public const TABLE_WHS = 'warehouses';
	public const TABLE_CONTRACTS = 'contracts';
	public const TABLE_PURCHASES = 'purchases';
	public const TABLE_POS = 'pos';
	public const TABLE_ORDERS = 'orders';
	public const TABLE_CLIENTS = 'clients';
	public const TABLE_INV_BANK_TRANSFERS = 'invoice_bank_transfers';
	public const TABLE_PROJ_STAGES = 'project_stages';
	public const TABLE_STAGES = 'stages';
	public const TABLE_LOAN_OPTS = 'loan_options';
	public const TABLE_MEETINGS = 'meetings';
	public const TABLE_EVENTS = 'events';
	public const TABLE_LEAD_STAGES = 'lead_stages';
	public const TABLE_PROPOSALS = 'proposals';
	public const TABLE_PRODUCTS = 'products';
	public const TABLE_GOAL_TYPES = 'goal_types';
	public const TABLE_CUSTOM_QUESTIONS = 'custom_questions';
	public const TABLE_JOBS = 'jobs';
	public const TABLE_NOTIFICATION_TEMPLATES = 'notification_templates';
	public const TABLE_SETTINGS = 'settings';
	public const TABLE_NOTIFICATION_TEMPLATE_LANGS = 'notification_template_langs';
	public const TABLE_LANGS = 'languages';
	public const TABLE_LPS = 'landing_page_settings';
	public const TABLE_PERMISSIONS = 'permissions';
	public const TABLE_ROLES = 'roles';
	public const TABLE_TEMPLATES = 'templates';
	public const TABLE_ACTIVITIES = 'activities';
	public const TABLE_NOTES = 'notes';
	public const TABLE_EMAILS = 'emails';
	public const TABLE_LOG_ACTS = 'log_activities';
	public const TABLE_SCHEDULES = 'schedules';
	public const TABLE_GOL = 'generated_offer_letters';
	public const TABLE_EC = 'experience_certificates';
	public const TABLE_NOC = 'noc_certificates';
	public const TABLE_JL = 'joining_letters';
	public const TABLE_TRS = 'transactions';
	public const TABLE_LBL = 'labels';
	public const TABLE_PAY_SLP_TP = 'payslip_types';
	public const TABLE_PAY_SLP = 'payslips';
	public const TABLE_AWD = 'awards';
	public const TABLE_AWD_TPS = 'award_types';
	public const TABLE_TRAVELS = 'travels';
	public const TABLE_PRMT = 'promotions';
	public const TABLE_TRFS = 'transfers';
	public const TABLE_WRN = 'warnings';
	public const TABLE_CPT = 'complaints';
	public const TABLE_SSLR = 'set_salaries';
	public const TABLE_ALW = 'allowances';
	public const TABLE_CMS = 'commissions';
	public const TABLE_LN = 'loans';
	public const TABLE_ST_DD = 'saturation_deductions';
	public const TABLE_OT_PYMTS = 'other_payments';
	public const TABLE_OVT = 'overtimes';
	public const TABLE_PRF_TP = 'performance_types';
	public const TABLE_PSLP = 'payslips';
	public const TABLE_ANC = 'announcements';
	public const TABLE_BL_PRD = 'bill_products';
	public const TABLE_WRH = 'warehouses';
	public const TABLE_PAY = 'payments';
	public const TABLE_POS_PAY = 'pos_payments';
	public const TABLE_MET_EMP = 'meeting_employees';

	// * FIELD NAMES

	// invoice
	public const INV_BANK_TRANSFER_INV = 'invoice_id';
	public const INV_BANK_TRANSFER_ORDER = 'order_id';

	// * DEFAULTS

	public const DEFAULT_UUID = 'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7';
	public const DEFAULT_PIPELINE = "f47ac10b-58cc-4372-a567-0e02b2c3d479";
	public const DEFAULT_PLAN = 'b04d7e2c-9a15-4f38-8c62-3e01f5a7b9d6';
	public const DEFAULT_LANG_LONG = 'English';
	public const DEFAULT_LANG = 'en';
	public const DEFAULT_TT = 'No given title';
	public const DEFAULT_DESC = 'No given description';
	public const DEFAULT_NOTES = 'No notes taken';
	public const COL_C_AT = 'created_at';
	public const COL_U_AT = 'updated_at';
	public const COL_FL_AT = 'failed_at';
	public const COL_FLD_RS = 'failed_reason';
	public const COL_ER_LG = 'error_log';
	public const COL_RTR_CT = 'retry_count';
	public const COL_LST_RTR_AT = 'last_retry_at';
	public const COL_TP_LB = 'type_label';
	public const COL_RL_CAT = 'related_categories';
	public const COL_PRO_IMG = 'pro_image';
	public const COL_FL_PT = 'file_path';
	public const COL_MM_TP = 'mime_type';
	public const COL_EXP_DT = 'expiration_date';
	public const COL_DL_TP = 'download_count';
	public const COL_PERM_RLS = 'permission_rules';
	public const COL_IR = 'is_required';
	public const COL_IPV = 'is_private';
	public const COL_LA = 'last_accessed';
	public const ORDER_ID = 'orderById';
	public const ORDER_C_AT = 'orderByCreatedAt';
	public const ORDER_NEW = 'orderByNewest';
	public const MININUM_WAGE_BR = 1518.00; // TODO RECUPERAR DE API REAL POSTERIORMENTE
}
