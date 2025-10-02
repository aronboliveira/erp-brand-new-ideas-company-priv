<?php

namespace App\Config\Constants;

class PermissionsConstants
{
	// * PERMISSIONS
	public const MNG_PERM = 'manage permission';
	public const CR_PERM = 'create permission';
	public const ED_PERM = 'edit permission';
	public const DEL_PERM = 'delete permission';

	// * ROLES
	public const MNG_ROLE = 'manage role';
	public const CR_ROLE = 'create role';
	public const ED_ROLE = 'edit role';
	public const DEL_ROLE = 'delete role';
	public const SA = 'super admin';
	public const CPN = 'company';
	public const CL = 'client';
	public const CT = 'customer';
	public const VD = 'vendor';
	public const ADM = 'admin';
	public const ACT = 'accountant';
	public const HR = 'hr';

	// * DASHBOARDS
	public const SHW_ACC_DSB = 'show account dashboard';
	public const SHW_CRM_DSB = 'show crm dashboard';
	public const SHW_HRM_DSB = 'show hrm dashboard';
	public const SHW_POS_DSB = 'show pos dashboard';
	public const SHW_PRJ_DSB = 'show project dashboard';
	public const MNG_CLT_DSB = 'manage client dashboard';
	public const MNG_SA_DSB = 'manage super admin dashboard';

	// * CLIENTS
	public const CR_CLT = 'create client';
	public const MNG_CLT = 'manage client';

	// * CUSTOMERS
	public const MNG_CST = 'manage customer';

	// * VENDORS
	public const MNG_VD = 'manage vendor';

	// * USERS
	public const MNG_USER = 'manage user';
	public const CR_USER = 'create user';
	public const ED_USER = 'edit user';
	public const DEL_USER = 'delete user';

	// * AI TEMPLATES
	public const MNG_AI_TPL = 'manage ai template';

	// * ANNOUNCEMENTS
	public const MNG_ANC = 'manage announcement';

	// * APPRAISALS
	public const MNG_APR = 'manage appraisal';

	// * ASSETS
	public const MNG_AST = 'manage assets';
	public const CRT_AST = 'create assets';
	public const ED_AST = 'edit assets';
	public const VIW_AST = 'view assets';
	public const DEL_AST = 'delete assets';

	// * ATTENDANCES
	public const MNG_ATD = 'manage attendance';
	public const CR_ATD = 'create attendance';

	// * AWARDS
	public const MNG_AWD = 'manage award';

	// * BANK ACCOUNTS
	public const MNG_BACC = 'manage bank account';

	// * BANK TRANSFERS
	public const MNG_BTF = 'manage bank transfer';

	// * BARCODES
	public const CR_BC = 'create barcode';

	// * BILL
	public const MNG_BIL = 'manage bill';

	// * BUGS
	public const MNG_BUG_RPT = 'manage bug report';
	public const MNG_BUG_STT = 'manage bug status';

	// * CATEGORIES
	public const MNG_CT_CAT = 'manage constant category';

	// * CAREERS
	public const SHW_CRR = 'show career';

	// * COA
	public const MNG_COA = 'manage chart of account';
	public const CR_COA = 'create chart of account';
	public const ED_COA = 'edit chart of account';
	public const DEL_COA = 'delete chart of account';
	public const MNG_COA_TYPE = 'manage constant chart of account type';
	public const CR_COA_TYPE = 'create constant chart of account type';

	// * COMPANY POLICIES
	public const MNG_CPN_PL = 'manage company policy';

	// * COMPLAINTS
	public const MNG_CPT = 'manage complaint';

	// * CONTRACTS
	public const MNG_CTC = 'manage contract';

	// * COUPON
	public const MNG_CPN = 'manage coupon';

	// * CREDIT NOTES
	public const MNG_CRD = 'manage credit note';

	// * CRM
	public const VW_CRM = 'view crm activity';

	// * CUSTOM FIELDS
	public const MNG_CT_CST_FD = 'manage constant custom field';

	// * CUSTOM QUESTIONS
	public const MNG_CST_QT = 'manage custom question';

	// * DEAL
	public const MNG_DL = 'manage deal';

	// * DEBIT NOTE
	public const MNG_DBT = 'manage debit note';

	// * DOCUMENTS
	public const MNG_DOC = 'manage document';

	// * EMPLOYEES
	public const MNG_EMP = 'manage employee';

	// * EVENTS
	public const MNG_EVT = 'manage event';

	// * FINANCIAL REPORTS
	public const BLC_RPT = 'balance sheet report';
	public const LDG_RPT = 'ledge report';
	public const TRL_RPT = 'trial balance report';

	// * FORMS
	public const MNG_FM_BD = 'manage form builder';

	// * GOALS
	public const MNG_GL = 'manage goal';

	// * GOAL TRACKINGS
	public const MNG_GTR = 'manage goal tracking';

	// * HOLIDAYS
	public const MNG_HLD = 'manage holiday';

	// * INDICATORS
	public const MNG_IND = 'manage indicator';

	// * INTERVIEW
	public const CR_ITV_SCHD = 'create interview schedule';
	public const SHW_ITV_SCHD = 'show interview schedule';

	// * INVOICES
	public const MNG_INV = 'manage invoice';

	// * JOBS
	public const MNG_JB = 'manage job';
	public const CR_JB = 'create job';
	public const MNG_JB_APL = 'manage job application';

	// * JOB STAGE
	public const CR_JST = 'create job stage';
	public const ED_JST = 'edit job stage';
	public const DEL_JST = 'delete job stage';
	public const MNG_JST = 'manage job stage';

	// * JOURNAL ENTRY
	public const MNG_JNL = 'manage journal entry';
	public const CR_JNL = 'create journal entry';
	public const ED_JNL = 'edit journal entry';
	public const DEL_JNL = 'delete journal entry';
	public const SHW_JNL = 'show journal entry';

	// * LABELS
	public const MNG_LB = 'manage label';

	// * LANDING PAGE
	public const MNG_LP = 'manage landing page';

	// * LEADS
	public const MNG_LD = 'manage lead';
	public const MNG_LD_ST = 'manage lead stage';

	// * LEAVES
	public const MNG_LV = 'manage leave';

	// * MEETINGS
	public const MNG_MT = 'manage meeting';

	// * ORDERS
	public const MNG_OD = 'manage order';

	// * PAYMENT
	public const MNG_PMT = 'manage payment';
	public const MNG_CT_PAY = 'manage constant payment method';

	// * PAYSLIP
	public const MNG_PSL = 'manage payslip';
	public const CR_PSL = 'create payslip';

	// * PERFORMANCE
	public const CRT_PRF_TP = 'create performance type';
	public const ED_PRF_TP = 'edit performance type';
	public const DEL_PRF_TP = 'delete performance type';
	public const MNG_PRF_TP = 'manage performance type';

	// * PIPELINES
	public const MNG_PPL = 'manage pipeline';

	// * PLAN
	public const MNG_PL = 'manage plan';
	public const CR_PL = 'create plan';
	public const ED_PL = 'edit plan';
	public const MNG_CP_PL = 'manage company plan';
	public const AC_PL_RQ = 'accept plan request';
	public const RQ_PL = 'request plan';
	public const VW_PL_RQ = 'view plan request';
	public const VW_PL_DT = 'view plan details';

	// * POS
	public const MNG_POS = 'manage pos';

	// * PRINTS
	public const MNG_PRT = 'manage print settings';

	// * PRODUCTS
	public const MNG_PRD_SV = 'manage product & service';

	// * PROJECTS
	public const MNG_PRJ = 'manage project';
	public const MNG_PRJ_TSK = 'manage project task';
	public const MNG_PRJ_TSK_STG = 'manage project task stage';

	// * PROMOTIONS
	public const MNG_PRM = 'manage promotion';

	// * PROPOSALS
	public const MNG_PPS = 'manage proposal';

	// * PURCHASES
	public const MNG_PRC = 'manage purchase';

	// * REPORTS
	public const MNG_RPT = 'manage report';
	public const BIL_RPT = 'bill report';
	public const EXP_RPT = 'expense report';
	public const INC_RPT = 'income report';
	public const IE_RPT = 'income vs expense report';
	public const INV_RPT = 'invoice report';
	public const LP_RPT = 'loss & profit report';
	public const STT_RPT = 'statement report';
	public const STK_RPT = 'stock report';
	public const TAX_RPT = 'tax report';

	// * RESIGNATION
	public const MNG_RSG = 'manage resignation';

	// * REVENUE
	public const MNG_RVN = 'manage revenue';

	// * SET SALARIES
	public const MNG_SSL = 'manage set salary';

	// * SOURCES
	public const MNG_SRC = 'manage source';

	// * STAGES
	public const MNG_ST = 'manage stage';

	// * SYSTEM
	public const MNG_CPN_SET = 'manage company settings';
	public const MNG_SYS_ST = 'manage system settings';

	// * TAXES
	public const MNG_CT_TX = 'manage constant tax';

	// * TERMINATIONS
	public const MNG_TRM = 'manage termination';

	// * TESTIMONIALS
	public const MNG_TT = 'manage testimonials';

	// * TIMESHEETS
	public const MNG_TS = 'manage timesheet';

	// * TRAINER
	public const MNG_TNR = 'manage trainer';

	// * TRAINING
	public const MNG_TNG = 'manage training';
	public const SHW_TNG = 'show training';

	// * TRANSACTIONS
	public const MNG_TRT = 'manage transaction';

	// * TRANSFER
	public const MNG_TRF = 'manage transfer';

	// * TRAVELS
	public const MNG_TRV = 'manage travel';

	// * UNIT
	public const MNG_CT_UNT = 'manage constant unit';

	// * WAREHOUSES
	public const MNG_WRH = 'manage warehouse';

	// * WARNINGS
	public const MNG_WRN = 'manage warning';

	// * WEBHOOK
	public const ED_WHK = 'edit webhook';
	public const DEL_WHK = 'delete webhook';
}
