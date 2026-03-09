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
	public const EMP = 'employee';

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
	public const VW_CLT = 'view client';
	public const ED_CLT = 'edit client';
	public const DEL_CLT = 'delete client';

	// * CUSTOMERS
	public const MNG_CST = 'manage customer';
	public const CR_CST = 'create customer';
	public const ED_CST = 'edit customer';
	public const DEL_CST = 'delete customer';
	public const VW_CST = 'view customer';
	public const MNG_CST_PMT = 'manage customer payment';

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
	public const CR_ANC = 'create announcement';
	public const VW_ANC = 'view announcement';
	public const ED_ANC = 'edit announcement';
	public const DEL_ANC = 'delete announcement';

	// * APPRAISALS
	public const MNG_APR = 'manage appraisal';
	public const CR_APR = 'create appraisal';
	public const ED_APR = 'edit appraisal';
	public const DEL_APR = 'delete appraisal';

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
	public const CR_AWD = 'create award';
	public const ED_AWD = 'edit award';
	public const DEL_AWD = 'delete award';

	// * AWARD TYPES
	public const MNG_AWD_TP = 'manage award type';
	public const CR_AWD_TP = 'create award type';
	public const ED_AWD_TP = 'edit award type';
	public const DEL_AWD_TP = 'delete award type';

	// * BANK ACCOUNTS
	public const MNG_BACC = 'manage bank account';
	public const CR_BACC = 'create bank account';
	public const ED_BACC = 'edit bank account';
	public const DEL_BACC = 'delete bank account';
	public const VW_BACC = 'view bank account';

	// * BANK TRANSFERS
	public const MNG_BTF = 'manage bank transfer';
	public const CR_BTF = 'create bank transfer';

	// * BARCODES
	public const CR_BC = 'create barcode';

	// * BILL
	public const MNG_BIL = 'manage bill';
	public const CR_BIL = 'create bill';
	public const ED_BIL = 'edit bill';
	public const DEL_BIL = 'delete bill';
	public const SHW_BIL = 'show bill';
	public const SND_BIL = 'send bill';
	public const DUP_BIL = 'duplicate bill';
	public const DEL_BIL_PRD = 'delete bill product';
	public const CR_PMT_BIL = 'create payment bill';
	public const MNG_VD_BIL = 'manage vendor bill';

	// * BUGS
	public const MNG_BUG_RPT = 'manage bug report';
	public const MNG_BUG_STT = 'manage bug status';
	public const CR_BUG_STT = 'create bug status';
	public const ED_BUG_STT = 'edit bug status';
	public const DEL_BUG_STT = 'delete bug status';

	// * BUDGET PLAN
	public const MNG_BDG_PL = 'manage budget plan';
	public const CR_BDG_PL = 'create budget plan';
	public const VW_BDG_PL = 'view budget plan';
	public const ED_BDG_PL = 'edit budget plan';
	public const DEL_BDG_PL = 'delete budget plan';

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
	public const DEL_CPN_PL = 'delete company policy';

	public const VW_COM = 'view commission';
	public const CR_COM = 'create commission';
	public const ED_COM = 'edit commission';
	public const DEL_COM = 'delete commission';

	// * COMPLAINTS
	public const MNG_CPT = 'manage complaint';
	public const CR_CPT = 'create complaint';
	public const ED_CPT = 'edit complaint';
	public const DEL_CPT = 'delete complaint';

	// * CONTRACTS
	public const MNG_CTC = 'manage contract';
	public const CR_CTC = 'create contract';
	public const ED_CTC = 'edit contract';
	public const DEL_CTC = 'delete contract';
	public const VW_CTC = 'show contract';

	// * COUPON
	public const MNG_CPN = 'manage coupon';

	// * CREDIT NOTES
	public const MNG_CRD = 'manage credit note';
	public const CR_CRD = 'create credit note';
	public const ED_CRD = 'edit credit note';
	public const DEL_CRD = 'delete credit note';

	// * CRM
	public const VW_CRM = 'view crm activity';

	// * CUSTOM FIELDS
	public const MNG_CT_CST_FD = 'manage constant custom field';

	// * CUSTOM QUESTIONS
	public const MNG_CST_QT = 'manage custom question';

	// * DEAL
	public const MNG_DL = 'manage deal';
	public const CR_DL = 'create deal';
	public const ED_DL = 'edit deal';
	public const DEL_DL = 'delete deal';
	public const MV_DL = 'move deal';
	public const VW_DL = 'view deal';
	public const CR_DL_TSK = 'create task';
	public const ED_DL_TSK = 'edit task';
	public const DEL_DL_TSK = 'delete task';
	public const VW_DL_TSK = 'view task';
	public const CR_DL_CL = 'create deal call';
	public const ED_DL_CL = 'edit deal call';
	public const DEL_DL_CL = 'delete deal call';
	public const CR_DL_EM = 'create deal email';

	// * DEBIT NOTE
	public const MNG_DBT = 'manage debit note';
	public const CR_DBT = 'create debit note';
	public const ED_DBT = 'edit debit note';
	public const DEL_DBT = 'delete debit note';

	// * DEDUCTION OPTION
	public const MNG_DDT_OPT = 'manage deduction option';
	public const CR_DDT_OPT = 'create deduction option';
	public const ED_DDT_OPT = 'edit deduction option';
	public const DEL_DDT_OPT = 'delete deduction option';

	// * DOCUMENTS
	public const MNG_DOC = 'manage document';

	// * EMPLOYEES
	public const MNG_EMP = 'manage employee';

	// * EVENTS
	public const MNG_EVT = 'manage event';
	public const CR_EVT = 'create event';
	public const ED_EVT = 'edit event';
	public const DEL_EVT = 'delete event';

	// * FINANCIAL REPORTS
	public const BLC_RPT = 'balance sheet report';
	public const LDG_RPT = 'ledge report';
	public const TRL_RPT = 'trial balance report';

	// * FORMS
	public const MNG_FM_BD = 'manage form builder';
	public const CR_FM_BD = 'create form builder';
	public const ED_FM_BD = 'edit form builder';
	public const DEL_FM_BD = 'delete form builder';
	public const MNG_FM_FD = 'manage form field';
	public const CR_FM_FD = 'create form field';
	public const ED_FM_FD = 'edit form field';
	public const DEL_FM_FD = 'delete form field';
	public const VW_FM_RES = 'view form response';

	// * GOALS
	public const MNG_GL = 'manage goal';

	// * GOAL TRACKINGS
	public const MNG_GTR = 'manage goal tracking';

	// * HOLIDAYS
	public const MNG_HLD = 'manage holiday';
	public const CR_HLD = 'create holiday';
	public const ED_HLD = 'edit holiday';
	public const DEL_HLD = 'delete holiday';
	public const VW_HLD = 'show holiday';

	// * INDICATORS
	public const MNG_IND = 'manage indicator';

	// * INTERVIEW
	public const CR_ITV_SCHD = 'create interview schedule';
	public const SHW_ITV_SCHD = 'show interview schedule';

	// * INVOICES
	public const MNG_INV = 'manage invoice';
	public const CR_INV = 'create invoice';
	public const ED_INV = 'edit invoice';
	public const DEL_INV = 'delete invoice';
	public const SHW_INV = 'show invoice';
	public const SND_INV = 'send invoice';
	public const DUP_INV = 'duplicate invoice';
	public const MNG_CST_INV = 'manage customer invoice';
	public const CR_PMT_INV = 'create payment invoice';
	public const DEL_PMT_INV = 'delete payment invoice';

	// * JOBS
	public const MNG_JB = 'manage job';
	public const CR_JB = 'create job';
	public const ED_JB = 'edit job';
	public const DEL_JB = 'delete job';
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
	public const CR_LD = 'create lead';
	public const VW_LD = 'view lead';
	public const ED_LD = 'edit lead';
	public const DEL_LD = 'delete lead';
	public const MV_LD = 'move lead';
	public const CNV_LD = 'convert lead';
	public const CR_LD_CL = 'create lead call';
	public const ED_LD_CL = 'edit lead call';
	public const DEL_LD_CL = 'delete lead call';
	public const CR_LD_EM = 'create lead email';
	public const MNG_LD_ST = 'manage lead stage';
	public const CR_LD_ST = 'create lead stage';
	public const ED_LD_ST = 'edit lead stage';
	public const DEL_LD_ST = 'delete lead stage';

	// * LEAVES
	public const MNG_LV = 'manage leave';

	// * LOAN
	public const MNG_LN = 'manage loan';
	public const CR_LN = 'create loan';
	public const ED_LN = 'edit loan';
	public const DEL_LN = 'delete loan';

	// * LOAN OPTION
	public const MNG_LN_OPT = 'manage loan option';
	public const CR_LN_OPT = 'create loan option';
	public const ED_LN_OPT = 'edit loan option';
	public const DEL_LN_OPT = 'delete loan option';

	// * MEETINGS
	public const MNG_MT = 'manage meeting';
	public const CR_MT = 'create meeting';
	public const VW_MT = 'view meeting';
	public const ED_MT = 'edit meeting';
	public const DEL_MT = 'delete meeting';

	// * ORDERS
	public const MNG_OD = 'manage order';

	// * OVERTIME
	public const MNG_OVT = 'manage overtime';
	public const CR_OVT = 'create overtime';
	public const ED_OVT = 'edit overtime';
	public const DEL_OVT = 'delete overtime';

	// * OTHER PAYMENT
	public const CR_OT_PAY = 'create other payment';
	public const SHW_OT_PAY = 'show other payment';
	public const ED_OT_PAY = 'edit other payment';
	public const DEL_OT_PAY = 'delete other payment';

	// * PAYMENT
	public const MNG_PMT = 'manage payment';
	public const MNG_CT_PAY = 'manage constant payment method';

	// * PAYSLIP
	public const MNG_PSL = 'manage payslip';
	public const CR_PSL = 'create payslip';

	// * PAYSLIP TYPE
	public const MNG_PSL_TP = 'manage payslip type';
	public const CR_PSL_TP = 'create payslip type';
	public const ED_PSL_TP = 'edit payslip type';
	public const DEL_PSL_TP = 'delete payslip type';

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
	public const CR_PRJ_TSK_STG = 'create project task stage';
	public const DEL_PRJ_TSK_STG = 'delete project task stage';

	// * PROMOTIONS
	public const MNG_PRM = 'manage promotion';

	// * PROPOSALS
	public const MNG_PPS = 'manage proposal';

	// * PURCHASES
	public const MNG_PRC = 'manage purchase';
	public const CR_PRC = 'create purchase';
	public const ED_PRC = 'edit purchase';
	public const DEL_PRC = 'delete purchase';
	public const SND_PRC = 'send purchase';
	public const VW_PRC = 'view purchase';
	public const SHW_PRC = 'show purchase';
	public const CR_PRC_PAY = 'create payment purchase';
	public const DEL_PRC_PAY = 'delete payment purchase';

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
	public const LD_RPT = 'lead report';
	public const DL_RPT = 'deal report';
	public const SAL_RPT = 'sales report';
	public const RCV_RPT = 'receivable report';
	public const PAY_RPT = 'payable report';

	// * RESIGNATION
	public const MNG_RSG = 'manage resignation';

	// * REVENUE
	public const MNG_RVN = 'manage revenue';
	public const CR_RVN = 'create revenue';
	public const ED_RVN = 'edit revenue';
	public const DEL_RVN = 'delete revenue';

	// * SET SALARIES
	public const MNG_SSL = 'manage set salary';

	// * SOURCES
	public const MNG_SRC = 'manage source';
	public const CR_SRC = 'create source';
	public const VW_SRC = 'view source';
	public const ED_SRC = 'edit source';
	public const DEL_SRC = 'delete source';
	public const MNG_SUP = 'manage support';
	public const CR_SUP = 'create support';
	public const VW_SUP = 'view support';
	public const ED_SUP = 'edit support';
	public const DEL_SUP = 'delete support';
	public const RPL_SUP = 'reply support';

	// * SATURATION DEDUCTION
	public const CR_SAT_DD = 'create saturation deduction';
	public const VW_SAT_DD = 'view saturation deduction';
	public const ED_SAT_DD = 'edit saturation deduction';
	public const DEL_SAT_DD = 'delete saturation deduction';

	// * STAGES
	public const MNG_ST = 'manage stage';
	public const CR_STG = 'create stage';
	public const ED_STG = 'edit stage';
	public const DEL_STG = 'delete stage';

	// * SYSTEM
	public const MNG_CPN_SET = 'manage company settings';
	public const MNG_BSN_SET = 'manage business settings';
	public const MNG_STR_ST = 'manage stripe settings';
	public const MNG_SYS_ST = 'manage system settings';

	// * TAXES
	public const MNG_CT_TX = 'manage constant tax';
	public const CR_CT_TX = 'create constant tax';
	public const VW_CT_TX = 'view constant tax';
	public const ED_CT_TX = 'edit constant tax';
	public const DEL_CT_TX = 'delete constant tax';

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
	public const CR_TNG = 'create training';
	public const SHW_TNG = 'show training';
	public const VW_TNG = 'view training';
	public const ED_TNG = 'edit training';
	public const DEL_TNG = 'delete training';
	public const MNG_TNG_TP = 'manage training type';
	public const CR_TNG_TP = 'create training type';
	public const ED_TNG_TP = 'edit training type';
	public const DEL_TNG_TP = 'delete training type';

	// * ZOOM MEETING
	public const CRT_ZM = 'create zoom meeting';
	public const VW_ZM = 'view zoom meeting';
	public const DEL_ZM = 'delete zoom meeting';

	// * TRANSACTIONS
	public const MNG_TRT = 'manage transaction';

	// * TRANSFER
	public const MNG_TRF = 'manage transfer';
	public const CR_TRF = 'create transfer';
	public const ED_TRF = 'edit transfer';
	public const DEL_TRF = 'delete transfer';

	// * TRAVELS
	public const MNG_TRV = 'manage travel';

	// * UNIT
	public const MNG_CT_UNT = 'manage constant unit';

	// * WAREHOUSES
	public const MNG_WRH = 'manage warehouse';
	public const CR_WRH = 'create warehouse';
	public const ED_WRH = 'edit warehouse';
	public const SHW_WRH = 'show warehouse';
	public const DEL_WRH = 'delete warehouse';

	// * WARNINGS
	public const MNG_WRN = 'manage warning';

	// * WEBHOOK
	public const CR_WHK = 'create webhook';
	public const ED_WHK = 'edit webhook';
	public const DEL_WHK = 'delete webhook';
	public const ADD_JB_APL_NT = 'add job application note';
	public const ADD_JB_APL_SKL = 'add job application skill';
	public const ARC_JB_APL = 'archive job application';
	public const CHG_LNG = 'change language';
	public const CNV_INV = 'convert invoice';
	public const CR_BR = 'create branch';
	public const CR_CPN_PL = 'create company policy';
	public const CR_CST_QT = 'create custom question';
	public const CR_CT_CAT = 'create constant category';
	public const CR_CT_CST_FD = 'create constant custom field';
	public const CR_DEP = 'create department';
	public const CR_DOC = 'create document';
	public const CR_DOC_TP = 'create document type';
	public const CR_EMP = 'create employee';
	public const CR_GTR = 'create goal tracking';
	public const CR_IND = 'create indicator';
	public const CR_JB_APL = 'create job application';
	public const CR_JB_CAT = 'create job category';
	public const CR_LB = 'create label';
	public const CR_LNG = 'create language';
	public const CR_LV = 'create leave';
	public const CR_LV_TP = 'create leave type';
	public const CR_MLST = 'create milestone';
	public const CR_PPL = 'create pipeline';
	public const CR_PPS = 'create proposal';
	public const CR_PPS_PRD = 'create proposal product';
	public const CR_PRD_SV = 'create product & service';
	public const CR_PRJ = 'create project';
	public const CR_PRJ_STG = 'create project stage';
	public const CR_PRJ_TSK = 'create project task';
	public const CR_PRM = 'create promotion';
	public const CR_RSG = 'create resignation';
	public const CR_TNR = 'create trainer';
	public const CR_TRM = 'create termination';
	public const CR_TRM_TP = 'create termination type';
	public const CR_TRV = 'create travel';
	public const CR_TS = 'create timesheet';
	public const CR_TTR = 'create time tracker';
	public const CR_VD = 'create vendor';
	public const CR_WRN = 'create warning';
	public const DEL_ATD = 'delete attendance';
	public const DEL_BR = 'delete branch';
	public const DEL_COA_TYPE = 'delete constant chart of account type';
	public const DEL_CST_QT = 'delete custom question';
	public const DEL_CT_CAT = 'delete constant category';
	public const DEL_CT_CST_FD = 'delete constant custom field';
	public const DEL_DEP = 'delete department';
	public const DEL_DOC = 'delete document';
	public const DEL_DOC_TP = 'delete document type';
	public const DEL_EMP = 'delete employee';
	public const DEL_GTR = 'delete goal tracking';
	public const DEL_IND = 'delete indicator';
	public const DEL_ITV_SCHD = 'delete interview schedule';
	public const DEL_JB_APL = 'delete job application';
	public const DEL_JB_APL_NT = 'delete job application note';
	public const DEL_JB_CAT = 'delete job category';
	public const DEL_LB = 'delete label';
	public const DEL_LNG = 'delete language';
	public const DEL_LV = 'delete leave';
	public const DEL_LV_TP = 'delete leave type';
	public const DEL_MLST = 'delete milestone';
	public const DEL_PPL = 'delete pipeline';
	public const DEL_PPS = 'delete proposal';
	public const DEL_PPS_PRD = 'delete proposal product';
	public const DEL_PRD_SV = 'delete product & service';
	public const DEL_PRJ = 'delete project';
	public const DEL_PRJ_STG = 'delete project stage';
	public const DEL_PRJ_TSK = 'delete project task';
	public const DEL_PRM = 'delete promotion';
	public const DEL_RSG = 'delete resignation';
	public const DEL_TNR = 'delete trainer';
	public const DEL_TRM = 'delete termination';
	public const DEL_TRM_TP = 'delete termination type';
	public const DEL_TRV = 'delete travel';
	public const DEL_TS = 'delete timesheet';
	public const DEL_TTR = 'delete time tracker';
	public const DEL_VD = 'delete vendor';
	public const DEL_WRN = 'delete warning';
	public const DUP_PPS = 'duplicate proposal';
	public const ED_ATD = 'edit attendance';
	public const ED_BR = 'edit branch';
	public const ED_COA_TYPE = 'edit constant chart of account type';
	public const ED_CPN_PL = 'edit company policy';
	public const ED_CST_QT = 'edit custom question';
	public const ED_CT_CAT = 'edit constant category';
	public const ED_CT_CST_FD = 'edit constant custom field';
	public const ED_DEP = 'edit department';
	public const ED_DOC = 'edit document';
	public const ED_DOC_TP = 'edit document type';
	public const ED_EMP = 'edit employee';
	public const ED_GTR = 'edit goal tracking';
	public const ED_IND = 'edit indicator';
	public const ED_ITV_SCHD = 'edit interview schedule';
	public const ED_JB_APL = 'edit job application';
	public const ED_JB_CAT = 'edit job category';
	public const ED_LB = 'edit label';
	public const ED_LV = 'edit leave';
	public const ED_LV_TP = 'edit leave type';
	public const ED_MLST = 'edit milestone';
	public const ED_NTF_TPL = 'edit notification template';
	public const ED_PPL = 'edit pipeline';
	public const ED_PPS = 'edit proposal';
	public const ED_PPS_PRD = 'edit proposal product';
	public const ED_PRD_SV = 'edit product & service';
	public const ED_PRJ = 'edit project';
	public const ED_PRJ_STG = 'edit project stage';
	public const ED_PRJ_TSK = 'edit project task';
	public const ED_PRM = 'edit promotion';
	public const ED_RSG = 'edit resignation';
	public const ED_SSL = 'edit set salary';
	public const ED_TNR = 'edit trainer';
	public const ED_TRM = 'edit termination';
	public const ED_TRM_TP = 'edit termination type';
	public const ED_TRV = 'edit travel';
	public const ED_TS = 'edit timesheet';
	public const ED_TTR = 'edit time tracker';
	public const ED_VD = 'edit vendor';
	public const ED_WRN = 'edit warning';
	public const EXP_PRJ_RPT = 'export project report';
	public const MNG_BR = 'manage branch';
	public const MNG_CST_PPS = 'manage customer proposal';
	public const MNG_DEP = 'manage department';
	public const MNG_DOC_TP = 'manage document type';
	public const MNG_EMP_PRF = 'manage employee profile';
	public const MNG_JB_CAT = 'manage job category';
	public const MNG_JB_ONB = 'manage job onBoard';
	public const MNG_LNG = 'manage language';
	public const MNG_LV_TP = 'manage leave type';
	public const MNG_NTF_TPL = 'manage notification template';
	public const MNG_PPS_PRD = 'manage proposal product';
	public const MNG_PRJ_STG = 'manage project stage';
	public const MNG_TRM_TP = 'manage termination type';
	public const MNG_TTR = 'manage time tracker';
	public const MNG_VD_PAY = 'manage vendor payment';
	public const MNG_VD_TRN = 'manage vendor transaction';
	public const MV_JB_APL = 'move job application';
	public const MV_PRJ_STG = 'move project stage';
	public const SHW_EMP_PRF = 'show employee profile';
	public const SHW_JB_APL = 'show job application';
	public const SHW_PPS = 'show proposal';
	public const SND_PPS = 'send proposal';
	public const VW_CST_QT = 'view custom question';
	public const VW_CT_CST_FD = 'view constant custom field';
	public const VW_EMP = 'view employee';
	public const VW_GRNT_CHT = 'view grant chart';
	public const VW_GTR = 'view goal tracking';
	public const VW_IND = 'view indicator';
	public const VW_ITV_SCHD = 'view interview schedule';
	public const VW_JB_CAT = 'view job category';
	public const VW_LV = 'view leave';
	public const VW_MLST = 'view milestone';
	public const VW_PPS_PRD = 'view proposal product';
	public const VW_PRD_SV = 'view product & service';
	public const VW_PRJ = 'view project';
	public const VW_PRJ_RPT = 'view project report';
	public const VW_PRJ_TSK = 'view project task';
	public const VW_RSG = 'view resignation';
	public const VW_SSL = 'view set salary';
	public const VW_TNR = 'view trainer';
	public const VW_TRM = 'view termination';
	public const VW_TTR = 'view time tracker';
	public const VW_WRN = 'view warning';
}
