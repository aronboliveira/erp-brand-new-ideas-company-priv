<?php

namespace App\Config\Constants;

use App\Config\Constants\PermissionsConstants as PC;

/**
 * * This is the list of the permissions registered via             
 * * Permission::firstOrCreate 
 * * and $role->givePermissionTo(...)
 * * and DB::table(DC::TABLE_SETTINGS)->insert(collect($settingsToInsert)->unique('name')*->values()->all())
 */
class SeedersTemplating
{
	public const SCAPE_MSG = 'There was an excessive number of attempts to generate the id. Breaking Loop.';
	public const PERM_PERMS = [
		['name' => PC::MNG_PERM], // * SA, CP
		['name' => PC::CR_PERM], // * SA, CP
		['name' => PC::ED_PERM], // * SA, CP
		['name' => PC::DEL_PERM], // * SA, CP
	];
	public const USER_PERMS = [
		['name' => PC::CR_USER], // * SA, CP
		['name' => PC::ED_USER], // * SA, CP
		['name' => PC::DEL_USER], // * SA, CP
		['name' => PC::MNG_USER], // * SA, CP
	];
	public const ROLE_PERMS = [
		['name' => PC::MNG_ROLE], // * SA, CP
		['name' => PC::CR_ROLE], // * SA, CP
		['name' => PC::ED_ROLE], // * SA, CP
		['name' => PC::DEL_ROLE], // * SA, CP
	];
	public const PL_PERMS = [
		['name' => PC::MNG_PL], // * SA, CP
		['name' => PC::CR_PL], // * SA, CP
		['name' => PC::ED_PL], // * SA, CP
		['name' => PC::VW_PL_RQ],
		['name' => PC::VW_PL_DT],
		['name' => PC::AC_PL_RQ],
		['name' => PC::RQ_PL],
		['name' => PC::VW_PL_DT],
	];
	public const CLT_PERMS = [
		['name' => PC::MNG_CLT],
		['name' => PC::CR_CLT],
		['name' => 'edit client'],
		['name' => 'delete client'],
	];
	public const ALW_PERMS = [
		['name' => 'create allowance'],
		['name' => 'edit allowance'],
		['name' => 'delete allowance'],
		['name' => 'manage allowance option'],
		['name' => 'create allowance option'],
		['name' => 'edit allowance option'],
		['name' => 'delete allowance option'],
	];
	public const COUPON_PERMS = [
		['name' => PC::MNG_CPN], // * SA
		['name' => 'create coupon'], // * SA
		['name' => 'edit coupon'], // * SA
		['name' => 'delete coupon'], // * SA
	];
	public const SUPER_PERMS = [
		...self::USER_PERMS,
		...self::PERM_PERMS,
		...self::ROLE_PERMS,
		...self::PL_PERMS,
	];
	public const SHOW_DSB_PERMS = [
		['name' => PC::SHW_POS_DSB], // * CP
		['name' => PC::SHW_CRM_DSB], // * CP
		['name' => PC::SHW_HRM_DSB], // * CP
		['name' => PC::SHW_PRJ_DSB], // * CP
	];
	public const ANC_PERMS = [
		['name' => PC::MNG_ANC],
		['name' => 'create announcement'],
		['name' => 'edit announcement'],
		['name' => 'delete announcement'],
	];
	public const APR_PERMS = [
		['name' => PC::MNG_APR],
		['name' => 'create appraisal'],
		['name' => 'edit appraisal'],
		['name' => 'show appraisal'],
		['name' => 'delete appraisal'],
	];
	public const ATD_PERMS = [
		['name' => PC::MNG_ATD],
		['name' => PC::CR_ATD],
		['name' => 'edit attendance'],
		['name' => 'delete attendance'],
	];
	public const AWD_PERMS = [
		['name' => PC::MNG_AWD],
		['name' => 'create award'],
		['name' => 'edit award'],
		['name' => 'delete award'],
		['name' => 'manage award type'],
		['name' => 'create award type'],
		['name' => 'edit award type'],
	];
	public const BA_PERMS = [
		['name' => PC::MNG_BACC],
		['name' => 'create bank account'],
		['name' => 'edit bank account'],
		['name' => 'delete bank account'],
	];
	public const BDG_PERMS = [
		['name' => 'create budget plan'],
		['name' => 'edit budget plan'],
		['name' => 'manage budget plan'],
		['name' => 'delete budget plan'],
		['name' => 'view budget plan'],
	];
	public const BRC_PERMS = [
		['name' => 'manage branch'],
		['name' => 'create branch'],
		['name' => 'edit branch'],
		['name' => 'delete branch'],
	];
	public const BT_PERMS = [
		['name' => PC::MNG_BTF],
		['name' => 'create bank transfer'],
		['name' => 'edit bank transfer'],
		['name' => 'delete bank transfer'],
	];
	public const BILL_PERMS = [
		['name' => PC::MNG_BIL],
		['name' => 'create bill'],
		['name' => 'edit bill'],
		['name' => 'delete bill'],
		['name' => 'show bill'],
		['name' => 'send bill'],
		['name' => 'create payment bill'],
		['name' => 'delete payment bill'],
	];
	public const BUG_RPT_PERMS = [
		['name' => PC::MNG_BUG_RPT],
		['name' => 'create bug report'],
		['name' => 'edit bug report'],
		['name' => 'delete bug report'],
		['name' => 'move bug report'],
	];
	public const BUG_STT_PERMS = [
		['name' => PC::MNG_BUG_STT],
		['name' => 'create bug status'],
		['name' => 'edit bug status'],
		['name' => 'delete bug status'],
	];
	public const COM_PERMS = [
		['name' => 'manage commission'],
		['name' => 'create commission'],
		['name' => 'edit commission'],
		['name' => 'delete commission'],
	];
	public const CPN_PL_PERMS = [
		['name' => PC::MNG_CPN_PL],
		['name' => 'create company policy'],
		['name' => 'edit company policy'],
	];
	public const CPT_PERMS = [
		['name' => PC::MNG_CPT],
		['name' => 'create complaint'],
		['name' => 'edit complaint'],
		['name' => 'delete complaint'],
	];
	public const CMPT_PERMS = [
		['name' => 'Manage Competencies'],
		['name' => 'Create Competencies'],
		['name' => 'Edit Competencies'],
		['name' => 'Delete Competencies'],
	];
	public const CRD_PERMS = [
		['name' => PC::MNG_CRD],
		['name' => 'create credit note'],
		['name' => 'edit credit note'],
		['name' => 'delete credit note'],
	];
	public const CST_QT_PERMS = [
		['name' => PC::MNG_CST_QT],
		['name' => 'create custom question'],
		['name' => 'edit custom question'],
		['name' => 'delete custom question'],
	];
	public const CT_CST_FD_PERMS = [
		['name' => PC::MNG_CT_CST_FD],
		['name' => 'create constant custom field'],
		['name' => 'edit constant custom field'],
		['name' => 'delete constant custom field'],
	];
	public const CTC_PERMS = [
		['name' => PC::MNG_CTC],
		['name' => 'create contract'],
		['name' => 'edit contract'],
		['name' => 'delete contract'],
		['name' => 'show contract'],
		['name' => 'manage contract type'],
		['name' => 'create contract type'],
		['name' => 'edit contract type'],
		['name' => 'delete contract type'],
	];
	public const CTM_PERMS = [
		['name' => PC::MNG_CST],
		['name' => 'create customer'],
		['name' => 'edit customer'],
		['name' => 'delete customer'],
		['name' => 'show customer'],
	];
	public const CAT_CONST_PERMS = [
		['name' => PC::MNG_CT_CAT],
		['name' => 'create constant category'],
		['name' => 'edit constant category'],
		['name' => 'delete constant category'],
	];
	public const COA_PERMS = [
		['name' => PC::MNG_COA],
		['name' => PC::CR_COA],
		['name' => PC::ED_COA],
		['name' => PC::DEL_COA],
		['name' => PC::MNG_COA_TYPE],
		['name' => PC::CR_COA_TYPE],
	];
	public const DBT_PERMS = [
		['name' => PC::MNG_DBT],
		['name' => 'create debit note'],
		['name' => 'edit debit note'],
		['name' => 'delete debit note'],
	];
	public const DDT_PERMS = [
		['name' => 'manage deduction option'],
		['name' => 'create deduction option'],
		['name' => 'edit deduction option'],
		['name' => 'delete deduction option'],
	];
	public const DL_PERMS = [
		['name' => PC::MNG_DL],
		['name' => 'create deal'],
		['name' => 'edit deal'],
		['name' => 'view deal'],
		['name' => 'delete deal'],
		['name' => 'move deal'],
		['name' => 'create deal call'],
		['name' => 'edit deal call'],
		['name' => 'delete deal call'],
		['name' => 'create deal email'],
	];
	public const DPT_PERMS = [
		['name' => 'manage department'],
		['name' => 'create department'],
		['name' => 'view department'],
		['name' => 'edit department'],
		['name' => 'delete department'],
	];
	public const DSG_PERMS = [
		['name' => 'manage designation'],
		['name' => 'create designation'],
		['name' => 'view designation'],
		['name' => 'edit designation'],
		['name' => 'delete designation'],
	];
	public const DOC_PERMS = [
		['name' => 'manage document type'],
		['name' => 'create document type'],
		['name' => 'edit document type'],
		['name' => 'delete document type'],
		['name' => PC::MNG_DOC],
		['name' => 'create document'],
		['name' => 'edit document'],
		['name' => 'delete document'],
	];
	public const EMP_PERMS = [
		['name' => PC::MNG_EMP],
		['name' => 'create employee'],
		['name' => 'view employee'],
		['name' => 'edit employee'],
		['name' => 'delete employee'],
		['name' => 'manage employee profile'],
		['name' => 'show employee profile'],
	];
	public const EST_PERMS = [
		['name' => 'manage estimation'],
		['name' => 'create estimation'],
		['name' => 'view estimation'],
		['name' => 'edit estimation'],
		['name' => 'delete estimation'],
	];
	public const EVT_PERMS = [
		['name' => PC::MNG_EVT],
		['name' => 'create event'],
		['name' => 'edit event'],
		['name' => 'delete event'],
	];
	public const EXP_PERMS = [
		['name' => 'manage expense'],
		['name' => 'create expense'],
		['name' => 'edit expense'],
		['name' => 'delete expense'],
	];
	public const FAQ_PERMS = [
		['name' => 'manage faq'],
		['name' => 'create faq'],
		['name' => 'edit faq'],
		['name' => 'delete faq'],
	];
	public const FM_BD_PERMS = [
		['name' => PC::MNG_FM_BD],
		['name' => 'create form builder'],
		['name' => 'edit form builder'],
		['name' => 'delete form builder'],
	];
	public const FM_FD_PERMS = [
		['name' => 'manage form field'],
		['name' => 'create form field'],
		['name' => 'edit form field'],
		['name' => 'delete form field'],
		['name' => 'view form response'],
	];
	public const GOAL_PERMS = [
		['name' => PC::MNG_GL],
		['name' => 'create goal'],
		['name' => 'edit goal'],
		['name' => 'delete goal'],
	];
	public const GL_PERMS = [
		['name' => PC::MNG_GTR],
		['name' => 'create goal tracking'],
		['name' => 'edit goal tracking'],
		['name' => 'delete goal tracking'],
		['name' => 'manage goal type'],
		['name' => 'create goal type'],
		['name' => 'edit goal type'],
	];
	public const HLD_PERMS = [
		['name' => PC::MNG_HLD],
		['name' => 'edit holiday'],
		['name' => 'create holiday'],
		['name' => 'delete holiday'],
	];
	public const IND_PERMS = [
		['name' => PC::MNG_IND],
		['name' => 'create indicator'],
		['name' => 'edit indicator'],
		['name' => 'show indicator'],
		['name' => 'delete indicator'],
	];
	public const INV_PERMS = [
		['name' => PC::MNG_INV],
		['name' => 'create invoice'],
		['name' => 'edit invoice'],
		['name' => 'delete invoice'],
		['name' => 'show invoice'],
		['name' => 'send invoice'],
		['name' => 'copy invoice'],
		['name' => 'convert invoice'],
		['name' => 'create payment invoice'],
		['name' => 'delete payment invoice'],
	];
	public const ITV_PERMS = [
		['name' => PC::CR_ITV_SCHD],
		['name' => 'edit interview schedule'],
		['name' => 'delete interview schedule'],
		['name' => PC::SHW_ITV_SCHD],
	];
	public const JB_PERMS = [
		['name' => PC::MNG_JB_APL],
		['name' => 'create job application'],
		['name' => 'show job application'],
		['name' => 'delete job application'],
		['name' => 'move job application'],
		['name' => 'add job application skill'],
		['name' => 'add job application note'],
		['name' => 'delete job application note'],
		['name' => 'manage job onBoard'],
		['name' => 'manage job category'],
		['name' => 'create job category'],
		['name' => 'edit job category'],
		['name' => 'delete job category'],
		['name' => PC::MNG_JB],
		['name' => PC::CR_JB],
		['name' => 'edit job'],
		['name' => 'show job'],
		['name' => 'delete job'],
	];
	public const JNL_PERMS = [
		['name' => PC::MNG_JNL],
		['name' => PC::CR_JNL],
		['name' => PC::ED_JNL],
		['name' => PC::DEL_JNL],
		['name' => PC::SHW_JNL],
	];
	public const JST_PERMS = [
		['name' => PC::MNG_JST],
		['name' => PC::CR_JST],
		['name' => PC::ED_JST],
		['name' => PC::DEL_JST],
	];
	public const LB_PERMS = [
		['name' => PC::MNG_LB],
		['name' => 'create label'],
		['name' => 'edit label'],
		['name' => 'delete label'],
	];
	public const LD_PERMS = [
		['name' => PC::MNG_LD],
		['name' => 'create lead'],
		['name' => 'view lead'],
		['name' => 'edit lead'],
		['name' => 'delete lead'],
		['name' => 'move lead'],
		['name' => PC::MNG_LD_ST],
		['name' => 'create lead stage'],
		['name' => 'edit lead stage'],
		['name' => 'delete lead stage'],
		['name' => 'convert lead to deal'],
		['name' => 'create lead call'],
		['name' => 'edit lead call'],
		['name' => 'delete lead call'],
		['name' => 'create lead email'],
	];
	public const LN_PERMS = [
		['name' => 'create loan'],
		['name' => 'edit loan'],
		['name' => 'delete loan'],
		['name' => 'manage loan option'],
		['name' => 'create loan option'],
		['name' => 'edit loan option'],
		['name' => 'delete loan option'],
	];
	public const LV_PERMS = [
		['name' => PC::MNG_LV],
		['name' => 'create leave'],
		['name' => 'edit leave'],
		['name' => 'delete leave'],
		['name' => 'manage leave type'],
		['name' => 'create leave type'],
		['name' => 'edit leave type'],
		['name' => 'delete leave type'],
	];
	public const ML_PERMS = [
		['name' => 'create milestone'],
		['name' => 'edit milestone'],
		['name' => 'delete milestone'],
		['name' => 'view milestone'],
	];
	public const MT_PERMS = [
		['name' => PC::MNG_MT],
		['name' => 'create meeting'],
		['name' => 'edit meeting'],
		['name' => 'delete meeting'],
	];
	public const NTF_PERMS = [
		['name' => 'manage notification'],
		['name' => 'create notification'],
		['name' => 'edit notification'],
		['name' => 'delete notification'],
		['name' => 'manage notification template'],
		['name' => 'create notification template'],
		['name' => 'edit notification template'],
		['name' => 'delete notification template'],
	];
	public const OT_PAY_PERMS = [
		['name' => 'manage other payment'],
		['name' => 'create other payment'],
		['name' => 'edit other payment'],
		['name' => 'delete other payment'],
	];
	public const OVT_PERMS = [
		['name' => 'manage overtime'],
		['name' => 'create overtime'],
		['name' => 'edit overtime'],
		['name' => 'delete overtime'],
	];
	public const PAY_PERMS = [
		['name' => PC::MNG_PMT],
		['name' => 'create payment'],
		['name' => 'edit payment'],
		['name' => 'delete payment'],
	];
	public const PAY_SLP_PERMS = [
		['name' => 'manage payslip type'],
		['name' => 'create payslip type'],
		['name' => 'edit payslip type'],
		['name' => 'delete payslip type'],
	];
	public const PPL_PERMS = [
		['name' => PC::MNG_PPL],
		['name' => 'create pipeline'],
		['name' => 'edit pipeline'],
		['name' => 'delete pipeline'],
	];
	public const PPS_PERMS = [
		['name' => PC::MNG_PPS],
		['name' => 'create proposal'],
		['name' => 'edit proposal'],
		['name' => 'delete proposal'],
		['name' => 'duplicate proposal'],
		['name' => 'show proposal'],
		['name' => 'send proposal'],
		['name' => 'delete proposal product']
	];
	public const PRC_PLN_PERMS = [
		['name' => 'manage pricing plan'],
		['name' => 'create pricing plan'],
		['name' => 'edit pricing plan'],
		['name' => 'delete pricing plan'],
	];
	public const PRC_PERMS = [
		['name' => PC::MNG_PRC],
		['name' => 'view purchase'],
		['name' => 'create purchase'],
		['name' => 'edit purchase'],
		['name' => 'show purchase'],
		['name' => 'delete purchase'],
		['name' => 'send purchase'],
		['name' => 'create payment purchase'],
		['name' => 'delete payment purchase'],
	];
	public const PRJ_PERMS = [
		['name' => PC::MNG_PRJ],
		['name' => 'create project'],
		['name' => 'view project'],
		['name' => 'edit project'],
		['name' => 'delete project'],
		['name' => 'share project'],
	];
	public const PRJ_STG_PERMS = [
		['name' => 'manage project stage'],
		['name' => 'create project stage'],
		['name' => 'edit project stage'],
		['name' => 'delete project stage'],
	];
	public const PRJ_TSK_PERMS = [
		['name' => PC::MNG_PRJ_TSK],
		['name' => 'create project task'],
		['name' => 'edit project task'],
		['name' => 'view project task'],
		['name' => 'delete project task'],
	];
	public const PRJ_RPT_PERMS = [
		['name' => 'manage project report'],
		['name' => 'create project report'],
		['name' => 'edit project report'],
		['name' => 'view project report'],
		['name' => 'delete project report'],
	];
	public const PRJ_TSK_STG_PERMS = [
		['name' => PC::MNG_PRJ_TSK_STG],
		['name' => 'create project task stage'],
		['name' => 'edit project task stage'],
		['name' => 'delete project task stage'],
	];
	public const PRM_PERMS = [
		['name' => PC::MNG_PRM],
		['name' => 'create promotion'],
		['name' => 'edit promotion'],
		['name' => 'delete promotion'],
	];
	public const PSL_PERMS = [
		['name' => PC::MNG_PSL],
		['name' => PC::CR_PSL],
	];
	public const PROD_SERV_PERMS = [
		['name' => PC::MNG_PRD_SV],
		['name' => 'create product & service'],
		['name' => 'delete product & service'],
		['name' => 'edit product & service'],
	];
	public const REPORT_PERMS = [
		['name' => PC::BIL_RPT],
		['name' => PC::EXP_RPT],
		['name' => PC::INC_RPT],
		['name' => PC::IE_RPT],
		['name' => PC::INV_RPT],
		['name' => PC::LP_RPT],
		['name' => PC::STK_RPT],
		['name' => PC::TAX_RPT],
	];
	public const REPORT_FIN_PERMS = [
		['name' => PC::BLC_RPT],
		['name' => PC::LDG_RPT],
		['name' => PC::TRL_RPT],
	];
	public const RSG_PERMS = [
		['name' => PC::MNG_RSG],
		['name' => 'create resignation'],
		['name' => 'edit resignation'],
		['name' => 'delete resignation'],
	];
	public const RVN_PERMS = [
		['name' => PC::MNG_RVN],
		['name' => 'create revenue'],
		['name' => 'edit revenue'],
		['name' => 'delete revenue'],
	];
	public const SPT_PERMS = [
		['name' => 'manage support'],
		['name' => 'create support'],
		['name' => 'view support'],
		['name' => 'edit support'],
		['name' => 'delete support'],
		['name' => 'reply support'],
	];
	public const SRC_PERMS = [
		['name' => PC::MNG_SRC],
		['name' => 'create source'],
		['name' => 'edit source'],
		['name' => 'delete source'],
	];
	public const SSL_PERMS = [
		['name' => PC::MNG_SSL],
		['name' => 'edit set salary'],
		['name' => 'create set salary'],
	];
	public const ST_PERMS = [
		['name' => PC::MNG_ST],
		['name' => 'create stage'],
		['name' => 'edit stage'],
		['name' => 'delete stage'],
	];
	public const STR_PERMS = [
		['name' => 'manage saturation deduction'],
		['name' => 'create saturation deduction'],
		['name' => 'edit saturation deduction'],
		['name' => 'delete saturation deduction'],
	];
	public const TAX_CONST_PERMS = [
		['name' => PC::MNG_CT_TX],
		['name' => 'create constant tax'],
		['name' => 'edit constant tax'],
		['name' => 'delete constant tax'],
	];
	public const TR_PERMS = [
		['name' => PC::MNG_TNG],
		['name' => 'create training'],
		['name' => 'edit training'],
		['name' => 'delete training'],
		['name' => PC::SHW_TNG],
		['name' => PC::MNG_TNR],
		['name' => 'create trainer'],
		['name' => 'edit trainer'],
		['name' => 'delete trainer'],
		['name' => 'manage training type'],
		['name' => 'create training type'],
		['name' => 'edit training type'],
		['name' => 'delete training type'],
	];
	public const TRF_PERMS = [
		['name' => PC::MNG_TRF],
		['name' => 'create transfer'],
		['name' => 'edit transfer'],
		['name' => 'delete transfer'],
	];
	public const TRM_PERMS = [
		['name' => PC::MNG_TRM],
		['name' => 'create termination'],
		['name' => 'edit termination'],
		['name' => 'delete termination'],
		['name' => 'manage termination type'],
		['name' => 'create termination type'],
		['name' => 'edit termination type'],
		['name' => 'delete termination type'],
	];
	public const TRV_PERMS = [
		['name' => PC::MNG_TRV],
		['name' => 'create travel'],
		['name' => 'edit travel'],
		['name' => 'delete travel'],
	];
	public const TS_PERMS = [
		['name' => PC::MNG_TS],
		['name' => 'create timesheet'],
		['name' => 'edit timesheet'],
		['name' => 'delete timesheet'],
	];
	public const TSK_PERMS = [
		['name' => 'view task'],
		['name' => 'create task'],
		['name' => 'edit task'],
		['name' => 'delete task'],
	];
	public const UNIT_CONST_PERMS = [
		['name' => PC::MNG_CT_UNT],
		['name' => 'create constant unit'],
		['name' => 'edit constant unit'],
		['name' => 'delete constant unit'],
	];
	public const VD_PERMS = [
		['name' => PC::MNG_VD],
		['name' => 'create vendor'],
		['name' => 'edit vendor'],
		['name' => 'delete vendor'],
		['name' => 'show vendor'],
	];
	public const WHK_PERMS = [
		['name' => 'create webhook'],
		['name' => PC::ED_WHK],
		['name' => PC::DEL_WHK],
	];
	public const WRH_PERMS = [
		['name' => PC::MNG_WRH],
		['name' => 'create warehouse'],
		['name' => 'edit warehouse'],
		['name' => 'show warehouse'],
		['name' => 'delete warehouse'],
	];
	public const WRN_PERMS = [
		['name' => PC::MNG_WRN],
		['name' => 'create warning'],
		['name' => 'edit warning'],
		['name' => 'delete warning'],
	];
	public const AD_PERMS = [
		...self::SUPER_PERMS,
		...self::COUPON_PERMS,
		...self::SPT_PERMS,
		['name' => PC::MNG_SA_DSB],
		['name' => PC::MNG_USER],
		['name' => 'create language'],
		['name' => PC::MNG_SYS_ST],
		['name' => 'manage stripe settings'],
		['name' => PC::MNG_OD],
	];
	public const COMPANY_PERMS = [
		...self::SUPER_PERMS,
		['name' => PC::MNG_CPN_SET],
		['name' => 'manage business settings'],
		...self::SHOW_DSB_PERMS,
		['name' => PC::SHW_ACC_DSB],
		...self::EXP_PERMS,
		...self::FAQ_PERMS,
		...self::INV_PERMS,
		['name' => 'duplicate invoice'],
		...self::TAX_CONST_PERMS,
		...self::CTM_PERMS,
		...self::CAT_CONST_PERMS,
		...self::BA_PERMS,
		...self::BT_PERMS,
		...self::BILL_PERMS,
		...self::RVN_PERMS,
		...self::PAY_PERMS,
		['name' => 'delete invoice product'],
		['name' => 'delete bill product'],
		...self::REPORT_PERMS,
		['name' => PC::TAX_RPT],
		['name' => PC::MNG_TRT],
		['name' => PC::MNG_OD],
		...self::CRD_PERMS,
		...self::DBT_PERMS,
		['name' => 'duplicate bill'],
		...self::PPS_PERMS,
		['name' => 'delete proposal product'],
		...self::AST_PERMS,
		['name' => PC::STT_RPT],
		...self::CT_CST_FD_PERMS,
		...self::COA_PERMS,
		...self::JST_PERMS,
		...self::JNL_PERMS,
		...self::REPORT_FIN_PERMS,
		...self::CLT_PERMS,
		...self::LD_PERMS,
		...self::PPL_PERMS,
		...self::SRC_PERMS,
		...self::LB_PERMS,
		...self::TSK_PERMS,
		...self::DL_PERMS,
		...self::ST_PERMS,
		...self::EMP_PERMS,
		...self::COM_PERMS,
		...self::DPT_PERMS,
		...self::DSG_PERMS,
		...self::BRC_PERMS,
		...self::DOC_PERMS,
		...self::PAY_SLP_PERMS,
		...self::ALW_PERMS,
		...self::LN_PERMS,
		...self::DDT_PERMS,
		...self::STR_PERMS,
		...self::OT_PAY_PERMS,
		...self::OVT_PERMS,
		...self::SSL_PERMS,
		...self::PSL_PERMS,
		...self::CPN_PL_PERMS,
		...self::APR_PERMS,
		...self::GOAL_PERMS,
		...self::GL_PERMS,
		...self::IND_PERMS,
		...self::EVT_PERMS,
		...self::MT_PERMS,
		...self::TR_PERMS,
		...self::AWD_PERMS,
		...self::RSG_PERMS,
		...self::TRV_PERMS,
		...self::PRM_PERMS,
		...self::CPT_PERMS,
		...self::WRN_PERMS,
		...self::TRM_PERMS,
		...self::JB_PERMS,
		...self::JST_PERMS,
		...self::CMPT_PERMS,
		...self::CST_QT_PERMS,
		...self::ITV_PERMS,
		...self::EST_PERMS,
		...self::HLD_PERMS,
		...self::OVT_PERMS,
		['name' => PC::SHW_CRR],
		...self::TRF_PERMS,
		...self::ANC_PERMS,
		...self::LV_PERMS,
		...self::ATD_PERMS,
		['name' => PC::MNG_RPT],
		...self::PRJ_PERMS,
		...self::ML_PERMS,
		['name' => 'view grant chart'],
		...self::PRJ_STG_PERMS,
		['name' => 'view timesheet'],
		['name' => 'view expense'],
		...self::PRJ_TSK_PERMS,
		...self::PRJ_RPT_PERMS,
		...self::PRJ_TSK_STG_PERMS,
		['name' => 'view activity'],
		['name' => PC::VW_CRM],
		...self::TS_PERMS,
		...self::BUG_RPT_PERMS,
		...self::BUG_STT_PERMS,
		['name' => PC::MNG_PRT],
		['name' => PC::MNG_CP_PL],
		['name' => 'buy plan'],
		['name' => PC::MNG_PL],
		...self::FM_BD_PERMS,
		['name' => PC::MNG_PRF_TP],
		['name' => PC::CRT_PRF_TP],
		['name' => PC::ED_PRF_TP],
		['name' => PC::DEL_PRF_TP],
		...self::FM_FD_PERMS,
		...self::BDG_PERMS,
		...self::WRH_PERMS,
		...self::PRC_PERMS,
		...self::PRC_PLN_PERMS,
		...self::SPT_PERMS,
		['name' => PC::MNG_POS],
		...self::CTC_PERMS,
		['name' => PC::CR_BC],
		...self::WHK_PERMS
	];
	public const VENDOR_EXCLUSIVE_PERMS = [
		['name' => 'vendor manage bill'],
		['name' => 'manage vendor bill'],
		['name' => 'manage vendor payment'],
		['name' => 'manage vendor transaction'],
	];
	public const CUSTOMER_EXCLUSIVE_PERMS = [
		['name' => 'manage customer payment'],
		['name' => 'manage customer transaction'],
		['name' => 'manage customer invoice'],
		['name' => 'manage customer proposal'],
	];
	public const AST_PERMS = [
		['name' => PC::MNG_AST],
		['name' => PC::CRT_AST],
		['name' => PC::ED_AST],
		['name' => PC::DEL_AST],
		['name' => PC::VIW_AST],
	];
	public const LP_PERMS = [
		['name' => PC::MNG_LP],
		['name' => PC::MNG_TT],
	];
	public const CUSTOMER_PERMS =  [
		...self::CUSTOMER_EXCLUSIVE_PERMS,
		['name' => 'show customer'],
		['name' => 'show invoice'],
		['name' => 'show proposal'],
	];
	public const VENDOR_PERMS = [
		...self::VENDOR_EXCLUSIVE_PERMS,
		['name' => 'show bill'],
		['name' => 'show vendor'],
	];
	public const ACCOUNTANT_PERMS = [
		...self::AST_PERMS,
		...self::BA_PERMS,
		['name' => PC::CR_BC],
		['name' => 'delete bill product'],
		...self::BILL_PERMS,
		...self::BT_PERMS,
		...self::BDG_PERMS,
		...self::CAT_CONST_PERMS,
		...self::COA_PERMS,
		...self::CT_CST_FD_PERMS,
		...self::CRD_PERMS,
		...self::CTM_PERMS,
		...self::DBT_PERMS,
		...self::EXP_PERMS,
		...self::GOAL_PERMS,
		...self::INV_PERMS,
		['name' => 'send invoice'],
		['name' => 'create payment invoice'],
		['name' => 'delete invoice product'],
		['name' => 'delete payment invoice'],
		...self::JNL_PERMS,
		...self::PAY_PERMS,
		...self::PPS_PERMS,
		['name' => PC::MNG_PRT],
		...self::PROD_SERV_PERMS,
		...self::REPORT_PERMS,
		...self::REPORT_FIN_PERMS,
		...self::RVN_PERMS,
		['name' => PC::SHW_ACC_DSB],
		['name' => PC::STT_RPT],
		...self::TAX_CONST_PERMS,
		['name' => PC::TAX_RPT],
		['name' => PC::MNG_TRT],
		...self::UNIT_CONST_PERMS,
		...self::VD_PERMS,
	];
	public const CLIENT_PERMS = [
		['name' => PC::MNG_CLT_DSB],
		...self::BUG_RPT_PERMS,
		['name' => 'view deal'],
		['name' => PC::MNG_DL],
		['name' => PC::MNG_PRJ],
		['name' => 'view project'],
		['name' => 'view grant chart'],
		['name' => 'view timesheet'],
		['name' => PC::MNG_TS],
		...self::PRJ_TSK_PERMS,
		['name' => 'view project report'],
		['name' => 'view activity'],
		['name' => 'view task'],
		['name' => PC::MNG_PPL],
		['name' => PC::MNG_LD_ST],
		['name' => PC::MNG_LB],
		['name' => PC::MNG_SRC],
		['name' => PC::MNG_ST],
		['name' => PC::MNG_CTC],
		['name' => 'move deal'],
		['name' => 'show contract'],
	];
	public const CLIENTLIKE_PERMS = [
		...self::CLIENT_PERMS,
		...self::VENDOR_PERMS,
		...self::CUSTOMER_PERMS,
	];
	public const PERMISSIONS = [
		...self::USER_PERMS,
		...self::LP_PERMS,
		...self::VENDOR_EXCLUSIVE_PERMS,
		...self::CUSTOMER_EXCLUSIVE_PERMS,
		['name' => 'create language'],
		['name' => 'change language'],
		['name' => PC::MNG_CPN_SET],
		['name' => PC::MNG_PRT],
		['name' => 'manage business settings'],
		['name' => 'manage stripe settings'],
		['name' => 'delete invoice product'],
		...self::SUPER_PERMS,
		...self::COUPON_PERMS,
		...self::SHOW_DSB_PERMS,
		['name' => PC::SHW_ACC_DSB], // * ACC, CP
		...self::EXP_PERMS,
		...self::FAQ_PERMS,
		...self::INV_PERMS,
		...self::PROD_SERV_PERMS,
		...self::TAX_CONST_PERMS,
		...self::UNIT_CONST_PERMS,
		...self::CTM_PERMS,
		...self::VD_PERMS,
		...self::CAT_CONST_PERMS,
		...self::BA_PERMS,
		...self::BT_PERMS,
		...self::BILL_PERMS,
		['name' => PC::MNG_TRT],
		...self::RVN_PERMS,
		...self::PAY_PERMS,
		...self::REPORT_PERMS,
		['name' => PC::MNG_OD],
		['name' => 'delete bill product'],
		['name' => 'manage customer payment'],
		['name' => 'manage customer transaction'],
		['name' => 'manage customer invoice'],
		['name' => 'duplicate invoice'],
		['name' => 'duplicate bill'],
		['name' => 'delete proposal product'],
		['name' => 'manage customer proposal'],
		...self::CRD_PERMS,
		...self::DBT_PERMS,
		...self::PPS_PERMS,
		...self::AST_PERMS,
		...self::NTF_PERMS,
		['name' => PC::STT_RPT],
		...self::CT_CST_FD_PERMS,
		...self::COA_PERMS,
		...self::JNL_PERMS,
		...self::REPORT_FIN_PERMS,
		...self::CLT_PERMS,
		...self::LD_PERMS,
		...self::PPL_PERMS,
		...self::SRC_PERMS,
		...self::LB_PERMS,
		...self::TSK_PERMS,
		...self::DL_PERMS,
		...self::ST_PERMS,
		...self::EMP_PERMS,
		...self::DPT_PERMS,
		...self::DSG_PERMS,
		...self::BRC_PERMS,
		...self::COM_PERMS,
		...self::DOC_PERMS,
		...self::PAY_SLP_PERMS,
		...self::ALW_PERMS,
		...self::LN_PERMS,
		...self::DDT_PERMS,
		...self::STR_PERMS,
		...self::OT_PAY_PERMS,
		...self::OVT_PERMS,
		...self::SSL_PERMS,
		...self::PSL_PERMS,
		...self::CPN_PL_PERMS,
		...self::APR_PERMS,
		...self::GOAL_PERMS,
		...self::GL_PERMS,
		...self::IND_PERMS,
		...self::TR_PERMS,
		...self::AWD_PERMS,
		...self::RSG_PERMS,
		...self::TRV_PERMS,
		...self::PRM_PERMS,
		...self::CPT_PERMS,
		...self::WRN_PERMS,
		...self::TRM_PERMS,
		...self::JB_PERMS,
		...self::JST_PERMS,
		...self::CMPT_PERMS,
		...self::CST_QT_PERMS,
		...self::ITV_PERMS,
		...self::EST_PERMS,
		...self::HLD_PERMS,
		['name' => PC::SHW_CRR],
		...self::MT_PERMS,
		...self::EVT_PERMS,
		...self::TRF_PERMS,
		...self::ANC_PERMS,
		...self::LV_PERMS,
		...self::ATD_PERMS,
		['name' => PC::MNG_RPT],
		...self::PRJ_PERMS,
		...self::PRJ_RPT_PERMS,
		...self::ML_PERMS,
		['name' => 'view grant chart'],
		...self::PRJ_STG_PERMS,
		['name' => 'view timesheet'],
		['name' => 'view expense'],
		...self::PRJ_TSK_PERMS,
		['name' => 'view activity'],
		['name' => PC::VW_CRM],
		...self::PRJ_TSK_STG_PERMS,
		...self::TS_PERMS,
		...self::BUG_RPT_PERMS,
		...self::BUG_STT_PERMS,
		['name' => PC::MNG_CLT_DSB],
		['name' => PC::MNG_SA_DSB], // * SA
		['name' => PC::MNG_SYS_ST],
		['name' => PC::MNG_CP_PL],
		['name' => 'buy plan'],
		...self::FM_BD_PERMS,
		['name' => PC::MNG_PRF_TP],
		['name' => PC::CRT_PRF_TP],
		['name' => PC::ED_PRF_TP],
		['name' => PC::DEL_PRF_TP],
		...self::FM_FD_PERMS,
		...self::BDG_PERMS,
		...self::WRH_PERMS,
		...self::PRC_PERMS,
		...self::PRC_PLN_PERMS,
		['name' => PC::MNG_POS],
		...self::CTC_PERMS,
		['name' => PC::CR_BC],
		...self::SPT_PERMS,
		...self::WHK_PERMS,
		['name' => PC::MNG_CT_PAY]
	];
	public const SA_PERMS = [
		...self::ACCOUNTANT_PERMS,
		...self::AD_PERMS,
		...self::CLIENT_PERMS,
		...self::COMPANY_PERMS,
		...self::VD_PERMS,
		...self::PERMISSIONS,
	];
	public const NOTIFICATIONS_DICT = [
		'new_lead' => 'New Lead',
		'lead_to_deal_conversion' => 'Lead to Deal Conversion',
		'new_project' => 'New Project',
		'task_stage_updated' => 'Task Stage Updated',
		'new_deal' => 'New Deal',
		'new_contract' => 'New Contract',
		'new_task' => 'New Task',
		'new_task_comment' => 'New Task Comment',
		'new_monthly_payslip' => 'New Monthly Payslip',
		'new_announcement' => 'New Announcement',
		'new_support_ticket' => 'New Support Ticket',
		'new_meeting' => 'New Meeting',
		'new_award' => 'New Award',
		'new_holiday' => 'New Holiday',
		'new_event' => 'New Event',
		'new_company_policy' => 'New Company Policy',
		'new_invoice' => 'New Invoice',
		'new_bill' => 'New Bill',
		'new_budget' => 'New Budget',
		'new_revenue' => 'New Revenue',
		'new_invoice_payment' => 'New Invoice Payment',
		'new_customer' => 'New Customer',
		'new_vendor' => 'New Vendor',
		'new_proposal' => 'New Proposal',
		'bill_payment' => 'New Payment',
		'invoice_payment_reminder' => 'Invoice Payment Reminder',
	];
	public const NOTIFICATIONS_DEFAULT_TEMPLATES = [
		'notification' => [
			'new_lead' => [
				'variables' => '{
						"Company Name": "user_name",
						"Lead Name": "lead_name",
						"Lead Email": "lead_email"
						}',
				'lang' => [
					'ar' => 'تم إنشاء عميل محتمل جديد بواسطة {user_name}',
					'zh' => '{user_name} 创建的新商机',
					'da' => 'Neuer Lead erstellt von {user_name}',
					'de' => 'Ny kundeemne oprettet af {user_name}',
					'en' => 'New Lead created by {user_name}',
					'es' => 'Nuevo cliente potencial creado por {user_name}',
					'fr' => 'Nouveau prospect créé par {user_name}',
					'he' => 'ביצוע חדש שנוצר על-ידי {user_name}',
					'it' => 'Nuovo lead creato da {user_name}',
					'ja' => '{user_name} によって作成された新しいリード',
					'nl' => 'Nieuwe lead gemaakt door {user_name}',
					'pl' => 'Nowy potencjalny klient utworzony przez użytkownika {user_name}',
					'ru' => 'Новый интерес создан пользователем {user_name}',
					'pt' => 'Novo lead criado por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan Yeni Lider',
					'pt-br' => 'Novo Lead criado por {user_name}',
				]
			],
			'lead_to_deal_conversion' => [
				'variables' => '{
						"Company Name": "user_name",
						"Lead User Name": "lead_user_name",
						"Lead Name": "lead_name",
						"Lead Email": "lead_email"
						}',
				'lang' => [
					'ar' => 'تم تحويل الصفقة من خلال العميل المحتمل {lead_user_name}',
					'zh' => '已通过商机 {lead_user_name} 进行转换',
					'da' => 'Aftale konverteret via kundeemne {lead_user_name}',
					'de' => 'Geschäftsabschluss durch Lead {lead_user_name}',
					'en' => 'Deal converted through lead {lead_user_name}',
					'es' => 'Trato convertido a través del cliente potencial {lead_user_name}',
					'fr' => 'Offre convertie via le prospect {lead_user_name}',
					'he' => 'העסקה הומרה באמצעות עופרת {lead_user_name}',
					'it' => 'Offerta convertita tramite il lead {lead_user_name}',
					'ja' => 'リード {lead_user_name} を通じて商談が成立',
					'nl' => 'Deal geconverteerd via lead {lead_user_name}',
					'pl' => 'Umowa przekonwertowana przez lead {lead_user_name}',
					'ru' => 'Конвертация сделки через лид {lead_user_name}',
					'pt' => 'Negócio convertido por meio do lead {lead_user_name}',
					'tr' => 'Baş { lead_user_name } ile dönüştürülen anlaşma',
					'pt-br' => 'Acordo convertido através do lead {lead_user_name}',
				]
			],
			'new_project' => [
				'variables' => '{
						"Company Name": "user_name",
						"Project Name": "project_name"
						}',
				'lang' => [
					'ar' => 'تم تكوين مشروع جديد { project_name } بواسطة { user_name }',
					'zh' => '{user_name} 创建了新的 {project_name} 项目',
					'da' => 'Nyt { project_name } projekt oprettet af { user_name }',
					'de' => 'Neues Projekt {project_name} erstellt von {user_name}',
					'en' => 'New {project_name} project created by {user_name}.',
					'es' => 'Nuevo proyecto {project_name} creado por {user_name}',
					'fr' => 'Nouveau projet { project_name } créé par { nom_utilisateur }',
					'he' => 'פרויקט {project_name} חדש שנוצר על ידי {user_name}',
					'it' => 'Nuovo progetto {project_name} creato da {user_name}',
					'ja' => '{user_name} によって作成された新規 {project_name} プロジェクト',
					'nl' => 'Nieuw project { project_name } gemaakt door { user_name }',
					'pl' => 'Nowy projekt {project_name } utworzony przez użytkownika {user_name }',
					'ru' => 'Новый проект { project_name }, созданный пользователем { user_name }',
					'pt' => 'Novo projeto {project_name} criado por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan yeni { project_name } projesi',
					'pt-br' => 'Novo projeto {project_name} criado por {user_name}',
				]
			],
			'task_stage_updated' => [
				'variables' => '{
						"Company Name": "user_name",
						"Task Name": "task_name",
						"Old Stage Name": "old_stage_name",
						"New Stage Name": "new_stage_name"
						}',
				'lang' => [
					'ar' => 'تم تغيير حالة { task_name } من { old_stage_name } الى { new_stage_name }',
					'zh' => '{task_name} 状态已从 {old_stage_name} 更改为 {new_stage_name}',
					'da' => 'Status for { task_name } er ændret fra { old_stage_name } til { new_stage_name }',
					'de' => 'Status {task_name} wurde von {old_stage_name} in {new_stage_name} geändert',
					'en' => '{task_name} status changed from {old_stage_name} to {new_stage_name}',
					'es' => 'El estado de {task_name} cambió de {old_stage_name} a {new_stage_name}',
					'fr' => 'Le statut de {task_name} est passé de {old_stage_name} à {new_stage_name}',
					'he' => 'הסטאטוס {task_name} השתנה מ - {old_stage_name} ל - {new_stage_name}',
					'it' => 'Lo stato di {task_name} è cambiato da {old_stage_name} a {new_stage_name}',
					'ja' => '{task_name} のステータスが {old_stage_name} から {new_stage_name} に変更されました',
					'nl' => '{task_name}-status gewijzigd van {old_stage_name} in {new_stage_name}',
					'pl' => 'Zmieniono status {task_name} z {old_stage_name} na {new_stage_name}',
					'ru' => 'Статус {task_name} изменен с {old_stage_name} на {new_stage_name}',
					'pt' => '{task_name} status alterado de {old_stage_name} para {new_stage_name}',
					'tr' => '{ task_name } durumu, { old_stage_name } tarafından { new_stage_name } olarak değiştirildi',
					'pt-br' => '{task_name} status alterado de {old_stage_name} para {new_stage_name}',
				]
			],
			'new_deal' => [
				'variables' => '{
						"Company Name": "user_name",
						"Lead Name": "deal_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء الصفقة الجديدة بواسطة {user_name}',
					'zh' => '{user_name} 创建的新政',
					'da' => 'Ny aftale oprettet af {user_name}',
					'de' => 'Neuer Deal erstellt von {user_name}',
					'en' => 'New Deal created by {user_name}',
					'es' => 'Nueva oferta creada por {user_name}',
					'fr' => 'Nouvelle offre créée par {user_name}',
					'he' => 'עסקה חדשה שנוצרה על-ידי {user_name}',
					'it' => 'New Deal creato da {user_name}',
					'ja' => '{user_name} によって作成された新しいディール',
					'nl' => 'Nieuwe deal gemaakt door {user_name}',
					'pl' => 'Nowa oferta utworzona przez użytkownika {user_name}',
					'ru' => 'Новая сделка создана пользователем {user_name}',
					'pt' => 'Novo negócio criado por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan Yeni Anlaşma',
					'pt-br' => 'Novo negócio criado por {user_name}',
				]
			],
			'new_contract' => [
				'variables' => '{
						"Company Name": "user_name",
						"Contract Name": "contract_subject",
						"Client Name": "contract_client",
						"Contract Price": "contract_value",
						"Contract Start Date": "contract_start_date",
						"Contract End Date": "contract_end_date"
						}',
				'lang' => [
					'ar' => 'تم إنشاء عقد {Contract_subject} لـ {contract_client} بواسطة {user_name}',
					'zh' => '{contract_subject } 合同已由 {user_name} 创建 { contract_client}',
					'da' => '{contract_subject} kontrakt oprettet for {contract_client} af {user_name}',
					'de' => '{contract_subject} Vertrag erstellt für {contract_client} von {user_name}',
					'en' => '{contract_subject} contract created for {contract_client} by {user_name}',
					'es' => '{contract_subject} contrato creado para {contract_client} por {user_name}',
					'fr' => 'Contrat {contract_subject} créé pour {contract_client} par {user_name}',
					'he' => '{contract_subject} חוזה שנוצר עבור {contract_client} על-ידי {user_name}',
					'it' => 'Contratto {contract_subject} creato per {contract_client} da {user_name}',
					'ja' => '{user_name} によって {contract_client} のために作成された {contract_subject} 契約',
					'nl' => '{contract_subject} contract gemaakt voor {contract_client} door {user_name}',
					'pl' => 'Umowa {contract_subject} utworzona dla {contract_client} przez {user_name}',
					'ru' => 'Контракт {contract_subject} создан для {contract_client} пользователем {user_name}',
					'pt' => 'Contrato {contract_subject} criado para {contract_client} por {user_name}',
					'tr' => '{ user_name } tarafından { contract_client } için { contract_subject } sözleşmesi oluşturuldu',
					'pt-br' => 'Contrato {contract_subject} criado para {contract_client} por {user_name}',
				]
			],
			'new_task' => [
				'variables' => '{
						"Company Name": "user_name",
						"Task Name": "task_name",
						"Project Name": "project_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء مهمة {task_name} لمشروع {project_name} بواسطة {user_name}',
					'zh' => '{user_name} 为 {project_name} 项目创建 {task_name} 任务',
					'da' => '{task_name} opgave oprettet for {project_name}-projekt af {user_name}',
					'de' => 'Aufgabe {task_name} erstellt für Projekt {project_name} von {user_name}',
					'en' => '{task_name} task create for {project_name} project by {user_name}.',
					'es' => '{task_name} tarea creada para {project_name} proyecto por {user_name}',
					'fr' => 'Tâche {task_name} créée pour le projet {project_name} par {user_name}',
					'he' => 'המשימה {task_name} יוצרת עבור {project_name} פרויקט על ידי {user_name}',
					'it' => 'Attività {task_name} creata per il progetto {project_name} da {user_name}',
					'ja' => '{user_name} による {project_name} プロジェクトの {task_name} タスク作成',
					'nl' => '{task_name} taak gemaakt voor {project_name} project door {user_name}',
					'pl' => 'Zadanie {task_name} utworzono dla projektu {project_name} przez użytkownika {user_name}',
					'ru' => 'Задача {task_name} создана для проекта {project_name} пользователем {user_name}',
					'pt' => 'Tarefa {task_name} criada para o projeto {project_name} por {user_name}',
					'tr' => '{ user_name } tarafından { proje_name } projesi için { task_name } görev oluşturma',
					'pt-br' => 'Tarefa {task_name} criada para o projeto {project_name} por {user_name}',
				]
			],
			'new_task_comment' => [
				'variables' => '{
						"Company Name": "user_name",
						"Task Name": "task_name",
						"Project Name": "project_name"
						}',
				'lang' => [
					'ar' => 'تمت إضافة تعليق جديد في المهمة {task_name} للمشروع {project_name}',
					'zh' => '项目 {project_name} 的任务 {task_name} 中添加了新注释',
					'da' => 'Ny kommentar tilføjet til opgave {task_name} i projekt {project_name}',
					'de' => 'Neuer Kommentar in Aufgabe {task_name} von Projekt {project_name} hinzugefügt',
					'en' => 'New Comment added in task {task_name} of project {project_name}.',
					'es' => 'Nuevo comentario agregado en la tarea {task_name} del proyecto {project_name}',
					'fr' => 'Nouveau commentaire ajouté dans la tâche {task_name} du projet {project_name}',
					'he' => 'הערה חדשה נוספה במשימה {task_name} של הפרויקט {project_name}',
					'it' => "Nuovo commento aggiunto nell'attività {task_name} del progetto {project_name}",
					'ja' => 'プロジェクト {project_name} のタスク {task_name} に新しいコメントが追加されました',
					'nl' => 'Nieuwe opmerking toegevoegd in taak {task_name} van project {project_name}',
					'pl' => 'Dodano nowy komentarz w zadaniu {task_name} projektu {project_name}',
					'ru' => 'Новый комментарий добавлен в задачу {task_name} проекта {project_name}',
					'pt' => 'Novo comentário adicionado na tarefa {task_name} do projeto {project_name}',
					'tr' => '{ project_name } projesinin { task_name } görevine yeni bir yorum eklendi',
					'pt-br' => 'Novo comentário adicionado na tarefa {task_name} do projeto {project_name}',
				]
			],
			'new_monthly_payslip' => [
				'variables' => '{
						"Year": "year"
						}',
				'lang' => [
					'ar' => 'تم إنشاء قسيمة دفع جديدة بتاريخ {year}',
					'zh' => '{ y年内} 生成的新 payslip',
					'da' => 'Ny lønseddel genereret af {year}',
					'de' => 'Neue Gehaltsabrechnung erstellt vom {year}',
					'en' => 'New payslip generated of {year}',
					'es' => 'Nueva nómina generada de {year}',
					'fr' => 'Nouvelle fiche de paie générée de {year}',
					'he' => 'תשלום חדש שהופק מ - {year}',
					'it' => 'Nuova busta paga generata di {year}',
					'ja' => '{year} の新しい給​​与明細が作成されました',
					'nl' => 'Nieuwe loonstrook gegenereerd van {year}',
					'pl' => 'Nowy odcinek wypłaty wygenerowany za {year}',
					'ru' => 'Новая расчетная ведомость создана за {year}',
					'pt' => 'Novo contracheque gerado de {year}',
					'tr' => '{ year } tarafından oluşturulan yeni payslip',
					'pt-br' => 'Novo contracheque gerado de {year}',
				]
			],
			'new_announcement' => [
				'variables' => '{
						"Announcement Title": "announcement_title",
						"Branch Name": "branch_name",
						"Start Date": "start_date",
						"End Date": "end_date"
						}',
				'lang' => [
					'ar' => 'تم إنشاء إعلان {calling_title} للفرع {Branch_name} من {start_date} إلى {end_date}',
					'zh' => '已为分支 {branch_name} 从 {start_date} 到 {end_date} 创建 {announcement_title} 声明',
					'da' => '{announcement_title}-meddelelse oprettet for filial {branch_name} fra {start_date} til {end_date}',
					'de' => '{announcement_title}-Ankündigung erstellt für Branche {branch_name} von {start_date} bis {end_date}',
					'en' => '{announcement_title} announcement created for branch {branch_name} from {start_date} to {end_date}.',
					'es' => 'Anuncio {announcement_title} creado para la sucursal {branch_name} desde el {start_date} hasta el {end_date}',
					'fr' => '{announcement_title} annonce créée pour la succursale {branch_name} du {start_date} au {end_date}',
					'he' => '{להכריז על הכרזה שנוצרה עבור ענף {מיתוג} מ - {start_date} ל - {end_date}',
					'it' => '{announcement_title} annuncio creato per la filiale {branch_name} dal {start_date} al {end_date}',
					'ja' => '{announcement_title} ブランチ {branch_name} の {start_date} から {end_date} までのお知らせが作成されました',
					'nl' => '{announcement_title} aankondiging gemaakt voor filiaal {branch_name} van {start_date} tot {end_date}',
					'pl' => 'Ogłoszenie {announcement_title} utworzone dla oddziału {branch_name} od {start_date} do {end_date}',
					'ru' => 'Объявление {announcement_title} создано для филиала {branch_name} с {start_date} по {end_date}',
					'pt' => 'Anúncio de {announcement_title} criado para a filial {branch_name} de {start_date} a {end_date}',
					'tr' => '{ branch_name } dalı için { start_date }-{ end_date } tarihleri arasında { announcement_title } duyurusu oluşturuldu',
					'pt-br' => 'Anúncio de {announcement_title} criado para a filial {branch_name} de {start_date} a {end_date}',
				]
			],
			'new_support_ticket' => [
				'variables' => '{
						"Support Priority": "support_priority",
						"Support User Name": "support_user_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء بطاقة دعم جديدة ذات أولوية {support_priority} لـ {support_user_name}',
					'zh' => '为 {support_user_name} 创建了 { support_priority} 优先级的新支持凭单',
					'da' => 'Ny supportbillet oprettet med prioritet {support_priority} til {support_user_name}',
					'de' => 'Neues Support-Ticket mit Priorität {support_priority} für {support_user_name} erstellt',
					'en' => 'New Support ticket created of {support_priority} priority for {support_user_name}',
					'es' => 'Nuevo ticket de soporte creado con prioridad {support_priority} para {support_user_name}',
					'fr' => "Nouveau ticket d'assistance créé avec la priorité {support_priority} pour {support_user_name}",
					'he' => "כרטיס תמיכה חדש שנוצר עבור קדימות {support_priority} עבור {support_user_name}",
					'it' => 'Nuovo ticket di assistenza creato con priorità {support_priority} per {support_user_name}',
					'ja' => '{support_user_name} の優先度 {support_priority} の新しいサポート チケットが作成されました',
					'nl' => 'Nieuw ondersteuningsticket gemaakt met prioriteit {support_priority} voor {support_user_name}',
					'pl' => 'Utworzono nowe zgłoszenie do pomocy technicznej o priorytecie {support_priority} dla użytkownika {support_user_name}',
					'ru' => 'Создан новый запрос в службу поддержки с приоритетом {support_priority} для {support_user_name}',
					'pt' => 'Novo tíquete de suporte criado com prioridade {support_priority} para {support_user_name}',
					'tr' => '{ support_user_name } için { support_priority } önceliğine ilişkin yeni Destek bileti oluşturuldu',
					'pt-br' => 'Novo tíquete de suporte criado com prioridade {support_priority} para {support_user_name}',
				]
			],
			'new_meeting' => [
				'variables' => '{
						"Meeting Title": "meeting_title",
						"Branch Name": "branch_name",
						"Meeting Date": "meeting_date",
						"Meeting Time": "meeting_time"
						}',
				'lang' => [
					'ar' => 'تم إنشاء اجتماع {meeting_title} للفرع {Branch_name} من {meeting_date} في {meeting_time}',
					'zh' => '已从 { meeting_time} 为分支 {branch_name} 创建了 { meetting_title } 会议 { meeting_date}',
					'da' => '{meeting_title} møde oprettet for filial {branch_name} fra {meeting_date} kl. {meeting_time}',
					'de' => '{meeting_title}-Meeting für Zweigstelle {branch_name} vom {meeting_date} um {meeting_time} erstellt',
					'en' => '{meeting_title} meeting created for branch {branch_name} from {meeting_date} at {meeting_time}.',
					'es' => '{meeting_title} reunión creada para la sucursal {branch_name} de {meeting_date} a las {meeting_time}',
					'fr' => 'Réunion {meeting_title} créée pour la succursale {branch_name} à partir du {meeting_date} à {meeting_time}',
					'he' => '{meeting_title} פגישה שנוצרה עבור ענף {מיתוג} מתוך {meeting_date} ב - {meeting_time}',
					'it' => 'Meeting {meeting_title} creato per la filiale {branch_name} da {meeting_date} alle {meeting_time}',
					'ja' => '{meeting_date} から {meeting_time} に {meeting_title} ブランチ {branch_name} 用に作成された {meeting_title} ミーティング',
					'nl' => '{meeting_title} vergadering gemaakt voor filiaal {branch_name} vanaf {meeting_date} om {meeting_time}',
					'pl' => 'Spotkanie {meeting_title} utworzone dla oddziału {branch_name} od {meeting_date} o {meeting_time}',
					'ru' => 'Встреча {meeting_title} создана для филиала {branch_name} с {meeting_date} в {meeting_time}',
					'pt' => 'Reunião {meeting_title} criada para a filial {branch_name} de {meeting_date} às {meeting_time}',
					'tr' => '{ meeting_title } { branch_name } dalı için { meeting_date } dalından { meeting_time } saatinde oluşturulan toplantı oluşturuldu',
					'pt-br' => 'Reunião {meeting_title} criada para a filial {branch_name} de {meeting_date} às {meeting_time}',
				]
			],
			'new_award' => [
				'variables' => '{
						"Award Name": "award_name",
						"Employee Name": "employee_name",
						"Award Date": "award_date"
						}',
				'lang' => [
					'ar' => 'تم إنشاء {Award_name} لـ {Employee_name} من {Award_date}',
					'zh' => '已从 {award_date} 为 {employe_name} 创建 {award_name}',
					'da' => '{award_name} oprettet til {employee_name} fra {award_date}',
					'de' => '{award_name} erstellt für {employee_name} vom {award_date}',
					'en' => '{award_name} created for {employee_name} from {award_date}',
					'es' => '{award_name} creado para {employee_name} de {award_date}',
					'fr' => '{award_name} créé pour {employee_name} à partir du {award_date}',
					'he' => '{award_name} שנוצר עבור {העובד ee_name} מ - {award_date}',
					'it' => '{award_name} creato per {employee_name} da {award_date}',
					'ja' => '{employee_name} のために {award_name} が {award_date} から作成されました',
					'nl' => '{award_name} gemaakt voor {employee_name} vanaf {award_date}',
					'pl' => '{award_name} utworzone dla {employee_name} od {award_date}',
					'ru' => '{award_name} создано для {employee_name} с {award_date}',
					'pt' => '{award_name} criado para {employee_name} de {award_date}',
					'tr' => '{ employee_name } için { award_date } içinden { award_name } oluşturuldu',
					'pt-br' => '{award_name} criado para {employee_name} de {award_date}',
				]
			],
			'new_holiday' => [
				'variables' => '{
						"Holiday Title": "holiday_title",
						"Holiday Date": "holiday_date"
						}',
				'lang' => [
					'ar' => '{holiday_title} عطلة يوم {holiday_date}',
					'zh' => '{holiday_date} 上的 {holiday_title} 假日',
					'da' => '{holiday_title} helligdag på {holiday_date}',
					'de' => '{holiday_title} Feiertag am {holiday_date}',
					'en' => '{holiday_title} holiday on {holiday_date}',
					'es' => '{holiday_title} feriado el {holiday_date}',
					'fr' => '{holiday_title} vacances le {holiday_date}',
					'he' => '{הולידיי _title} חגים ב - {הולידיי _date}',
					'it' => '{holiday_title} festività il giorno {holiday_date}',
					'ja' => '{holiday_date} の {holiday_title} 休日',
					'nl' => '{holiday_title} vakantie op {holiday_date}',
					'pl' => '{holiday_title} wakacje w dniu {holiday_date}',
					'ru' => '{holiday_title} праздник {holiday_date}',
					'pt' => '{holiday_title} feriado em {holiday_date}',
					'tr' => '{ holiday_date } tarihinde ({ holiday_date })',
					'pt-br' => '{holiday_title} feriado em {holiday_date}',
				]
			],
			'new_event' => [
				'variables' => '{
						"Event Title": "event_title",
						"Branch Name": "branch_name",
						"Event Start Date": "event_start_date",
						"Event End Date": "event_end_date"
						}',
				'lang' => [
					'ar' => 'تم إنشاء حدث {event_title} للفرع {Branch_name} من {event_start_date} إلى {event_end_date}',
					'zh' => '为分支 {branch_name} 从 {event_start_date} 创建的 {event_title } 事件为 {event_end_date}',
					'da' => '{event_title}-begivenhed oprettet for grenen {branch_name} fra {event_start_date} til {event_end_date}',
					'de' => '{event_title} Veranstaltung erstellt für Branche {branch_name} von {event_start_date} bis {event_end_date}',
					'en' => '{event_title} event created for branch {branch_name} from {event_start_date} to {event_end_date}',
					'es' => '{event_title} evento creado para la sucursal {branch_name} desde el {event_start_date} hasta el {event_end_date}',
					'fr' => 'Événement {event_title} créé pour la succursale {branch_name} du {event_start_date} au {event_end_date}',
					'he' => '{event_title} אירוע שנוצר עבור ענף {ברנch_name} מ - {event_start_date} אל {event_end_date}',
					'it' => 'Evento {event_title} creato per il ramo {branch_name} da {event_start_date} a {event_end_date}',
					'ja' => '{event_title} ブランチ {branch_name} に対して {event_start_date} から {event_end_date} まで作成された {event_title} イベント',
					'nl' => '{event_title} evenement gemaakt voor filiaal {branch_name} van {event_start_date} tot {event_end_date}',
					'pl' => 'Wydarzenie {event_title} utworzone dla oddziału {branch_name} od {event_start_date} do {event_end_date}',
					'ru' => 'Событие {event_title} создано для филиала {branch_name} с {event_start_date} по {event_end_date}',
					'pt' => 'Evento {event_title} criado para a ramificação {branch_name} de {event_start_date} a {event_end_date}',
					'tr' => '{ branch_name } dalı için { event_start_date }-{ event_end_date } tarihleri arasında { event_title } olayı yaratıldı',
					'pt-br' => 'Evento {event_title} criado para a ramificação {branch_name} de {event_start_date} a {event_end_date}',
				]
			],
			'new_company_policy' => [
				'variables' => '{
						"Company Policy Name": "company_policy_name",
						"Branch Name": "branch_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء سياسة {company_policy_name} لفرع {Branch_name}',
					'zh' => '已创建 {branch_name} 分支的 {company_policy_name} 策略',
					'da' => '{company_policy_name}-politik for filialen {branch_name} er oprettet',
					'de' => 'Richtlinie {company_policy_name} für Zweigstelle {branch_name} erstellt',
					'en' => '{company_policy_name} policy for {branch_name} branch created',
					'es' => 'Política {company_policy_name} para la sucursal {branch_name} creada',
					'fr' => 'Stratégie {company_policy_name} pour la succursale {branch_name} créée',
					'he' => '{company_policy_name} מדיניות עבור ענף {מיתוג} נוצרה',
					'it' => 'Politica {company_policy_name} per la filiale {branch_name} creata',
					'ja' => '{branch_name} ブランチの {company_policy_name} ポリシーが作成されました',
					'nl' => '{company_policy_name}-beleid voor filiaal {branch_name} gemaakt',
					'pl' => 'Polityka {company_policy_name} dla oddziału {branch_name} została utworzona',
					'ru' => 'Создана политика {company_policy_name} для филиала {branch_name}',
					'pt' => 'política {company_policy_name} para a filial {branch_name} criada',
					'tr' => '{ branch_name } şubesi için { company_policy_name } ilkesi oluşturuldu',
					'pt-br' => 'política {company_policy_name} para a filial {branch_name} criada',
				]
			],
			'new_invoice' => [
				'variables' => '{
						"Company Name": "user_name",
						"Invoice Number": "invoice_number",
						"Invoice Issue Date": "invoice_issue_date",
						"Invoice Due Date": "invoice_due_date",
						"Customer Name": "customer_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء الفاتورة الجديدة {invoice_number} بواسطة {user_name}',
					'zh' => '{user_name} 创建的新发票 {invoice_number}',
					'da' => 'Ny faktura {invoice_number} oprettet af {user_name}',
					'de' => 'Neue Rechnung {invoice_number} erstellt von {user_name}',
					'en' => 'New Invoice {invoice_number} created by {user_name}.',
					'es' => 'Nueva factura {invoice_number} creada por {user_name}',
					'fr' => 'Nouvelle facture {invoice_number} créée par {user_name}',
					'he' => 'חשבונית חדשה {invoice_number} נוצרה על-ידי {user_name}',
					'it' => 'Nuova fattura {invoice_number} creata da {user_name}',
					'ja' => '{user_name} によって作成された新しい請求書 {invoice_number}',
					'nl' => 'Nieuwe factuur {invoice_number} gemaakt door {user_name}',
					'pl' => 'Nowa faktura {invoice_number} utworzona przez użytkownika {user_name}',
					'ru' => 'Новый счет {invoice_number}, созданный {user_name}',
					'pt' => 'Nova fatura {invoice_number} criada por {user_name}',
					'tr' => 'Yeni Fatura { invoice_number }, { user_name } tarafından oluşturuldu',
					'pt-br' => 'Nova fatura {invoice_number} criada por {user_name}',
				]
			],
			'new_bill' => [
				'variables' => '{
						"Company Name": "user_name",
						"Bill Identifier": "bill_id",
						"Bill Date": "bill_date",
						"Bill Due Date": "bill_due_date",
						"Vendor Name": "vendor_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء الفاتورة الجديدة {bill_id} بواسطة {user_name}',
					'zh' => '{ user_name} 创建的新帐单 {bill_id}',
					'da' => 'Ny regning {bill_id} oprettet af {user_name}',
					'de' => 'Neue Rechnung {bill_id} erstellt von {user_name}',
					'en' => 'New Bill {bill_id} created by {user_name}',
					'es' => 'Nueva factura {bill_id} creada por {user_name}',
					'fr' => 'Nouvelle facture {bill_id} créée par {user_name}',
					'he' => '{ user_name} 创建的新帐单 {bill_id}',
					'it' => 'Nuova fattura {bill_id} creata da {user_name}',
					'ja' => '{user_name} によって作成された新しい請求書 {bill_id}',
					'nl' => 'Nieuwe factuur {bill_id} gemaakt door {user_name}',
					'pl' => 'Nowy rachunek {bill_id} utworzony przez użytkownika {user_name}',
					'ru' => 'Новый счет {bill_id}, созданный {user_name}',
					'pt' => 'Nova fatura {bill_id} criada por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan yeni Fatura { bill_id }',
					'pt-br' => 'Nova fatura {bill_id} criada por {user_name}',
				]
			],
			'new_budget' => [
				'variables' => '{
						"Budget Period": "budget_period",
						"Budget Year": "budget_year",
						"Budget Name": "budget_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء ميزانية {budget_period} البالغة {budget_year} لـ {budget_name}',
					'zh' => '已为 {budget_name} 创建 {budget_period} 预算 { budget_period }',
					'da' => '{budget_period} budget på {budget_year} oprettet for {budget_name}',
					'de' => '{budget_period} Budget von {budget_year} erstellt für {budget_name}',
					'en' => '{budget_period} budget of {budget_year} created for {budget_name}',
					'es' => '{budget_period} presupuesto de {budget_year} creado para {budget_name}',
					'fr' => '{budget_period} budget de {budget_year} créé pour {budget_name}',
					'he' => '{budget_לתקופת} תקציב של {budget_year} שנוצר עבור {budget_name}',
					'it' => '{budget_period} budget di {budget_year} creato per {budget_name}',
					'ja' => '{budget_name} 用に作成された {budget_year} の {budget_period} 予算',
					'nl' => '{budget_period} budget van {budget_year} gemaakt voor {budget_name}',
					'pl' => 'Budżet {budget_period} w wysokości {budget_year} został utworzony dla {budget_name}',
					'ru' => 'Бюджет {budget_period} на {budget_year} создан для {budget_name}',
					'pt' => 'Orçamento de {budget_period} de {budget_year} criado para {budget_name}',
					'tr' => '{ budget_year }, { budget_name } için { budget_period } bütçesi oluşturuldu',
					'pt-br' => 'Orçamento de {budget_period} de {budget_year} criado para {budget_name}',
				]
			],
			'new_revenue' => [
				'variables' => '{
						"Company Name": "user_name",
						"Revenue Amount": "revenue_amount",
						"Customer Name": "customer_name",
						"Revenue Date": "revenue_date"
						}',
				'lang' => [
					'ar' => 'تم إنشاء الإيرادات الجديدة من {الأرباح_amount} لـ {customer_name} بواسطة {user_name}',
					'zh' => '{user_name} 为 {customer_name} 创建的新收入 { 金额 }',
					'da' => 'Ny omsætning på {revenue_amount} oprettet for {customer_name} af {user_name}',
					'de' => 'Neuer Umsatz von {revenue_amount} erstellt für {customer_name} von {user_name}',
					'en' => 'New Revenue of {revenue_amount} created for {customer_name} by {user_name}',
					'es' => 'Nuevos ingresos de {revenue_amount} creados para {customer_name} por {user_name}',
					'fr' => 'Nouveau revenu de {revenue_amount} créé pour {customer_name} par {user_name}',
					'he' => 'הכנסה חדשה של {Revenue_סכום} שנוצרה עבור {customer_name} על-ידי {user_name}',
					'it' => 'Nuove entrate di {revenue_amount} create per {customer_name} da {user_name}',
					'ja' => '{user_name} によって {customer_name} に作成された {revenue_amount} の新しい収入',
					'nl' => 'Nieuwe opbrengst van {revenue_amount} gecreëerd voor {customer_name} door {user_name}',
					'pl' => 'Nowy przychód w wysokości {revenue_amount} utworzony dla klienta {customer_name} przez użytkownika {user_name}',
					'ru' => 'Новый доход в размере {revenue_amount} создан для {customer_name} пользователем {user_name}',
					'pt' => 'Nova receita de {revenue_amount} criada para {customer_name} por {user_name}',
					'tr' => '{ user_name } tarafından { customer_name } için yeni { revenue_amount } Geliri oluşturuldu',
					'pt-br' => 'Nova receita de {revenue_amount} criada para {customer_name} por {user_name}',
				]
			],
			'new_invoice_payment' => [
				'variables' => '{
						"Payment Price": "payment_price",
						"Customer Name": "customer_name",
						"Payment Type": "invoice_payment_type"
						}',
				'lang' => [
					'ar' => 'تم إنشاء دفعة جديدة بقيمة {payment_price} لـ {customer_name} بواسطة {invoice_payment_type}',
					'zh' => '{invoice_payment_type} 为 {customer_name} 创建了新支付 { payment_price}',
					'da' => 'Ny betaling på {payment_price} oprettet for {customer_name} af {invoice_payment_type}',
					'de' => 'Neue Zahlung von {payment_price} erstellt für {customer_name} von {invoice_payment_type}',
					'en' => 'New payment of {payment_price} created for {customer_name} by {invoice_payment_type}',
					'es' => 'Nuevo pago de {payment_price} creado para {customer_name} por {invoice_payment_type}',
					'fr' => 'Nouveau paiement de {payment_price} créé pour {customer_name} par {invoice_payment_type}',
					'he' => 'תשלום חדש של {payment_פרייס} שנוצר עבור {customer_name} על-ידי {invoice_payment_type}',
					'it' => 'Nuovo pagamento di {payment_price} creato per {customer_name} da {invoice_payment_type}',
					'ja' => '{invoice_payment_type} によって {customer_name} のために作成された {payment_price} の新しい支払い',
					'nl' => 'Nieuwe betaling van {payment_price} gemaakt voor {customer_name} door {invoice_payment_type}',
					'pl' => 'Nowa płatność {payment_price} utworzona dla {customer_name} przez {invoice_payment_type}',
					'ru' => 'Создан новый платеж {payment_price} для {customer_name} по {invoice_payment_type}',
					'pt' => 'Novo pagamento de {payment_price} criado para {customer_name} por {invoice_payment_type}',
					'tr' => '{ customer_name } için { invoice_payment_type } tarafından oluşturulan { payment_price } için yeni ödeme',
					'pt-br' => 'Novo pagamento de {payment_price} criado para {customer_name} por {invoice_payment_type}',
				]
			],
			'new_customer' => [
				'variables' => '{
						"Customer Name": "customer_name",
						"Customer Email": "customer_email"
						}',
				'lang' => [
					'ar' => 'عميل جديد أنشأه {user_name}',
					'zh' => '由 {user_name} 创建的新客户',
					'da' => 'Ny kunde oprettet af {user_name}',
					'de' => 'Neuer Kunde erstellt von {user_name}',
					'en' => 'New Customer created by {user_name}',
					'es' => 'Nuevo cliente creado por {user_name}',
					'fr' => 'Nouveau client créé par {user_name}',
					'he' => 'לקוח חדש נוצר על-ידי {user_name}',
					'it' => 'Nuovo cliente creato da {user_name}',
					'ja' => '{user_name} によって作成された新しい顧客',
					'nl' => 'Nieuwe klant gemaakt door {user_name}',
					'pl' => 'Nowy klient utworzony przez {user_name}',
					'ru' => 'Новый клиент создан {user_name}',
					'pt' => 'Novo cliente criado por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan yeni Müşteri',
					'pt-br' => 'Novo cliente criado por {user_name}',
				]
			],
			'new_vendor' => [
				'variables' => '{
						"Vendor Name": "vendor_name",
						"Vendor Email": "vendor_email"
						}',
				'lang' => [
					'ar' => 'تم إنشاء بائع جديد بواسطة {user_name}',
					'zh' => '{user_name} 创建的新供应商',
					'da' => 'Ny leverandør oprettet af {user_name}',
					'de' => 'Neuer Anbieter erstellt von {user_name}',
					'en' => 'New Vendor created by {user_name}',
					'es' => 'Nuevo proveedor creado por {user_name}',
					'fr' => 'Nouveau fournisseur créé par {user_name}',
					'he' => 'משווק חדש שנוצר על-ידי {user_name}',
					'it' => 'Nuovo fornitore creato da {user_name}',
					'ja' => '{user_name} によって作成された新しいベンダー',
					'nl' => 'Nieuwe leverancier gemaakt door {user_name}',
					'pl' => 'Nowy dostawca utworzony przez {user_name}',
					'ru' => 'Новый поставщик создан пользователем {user_name}',
					'pt' => 'Novo fornecedor criado por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan Yeni Satıcı',
					'pt-br' => 'Novo fornecedor criado por {user_name}',
				]
			],
			'new_proposal' => [
				'variables' => '{
						"Proposal Number": "proposal_number",
						"Company Name": "user_name",
						"Customer Name": "customer_name",
						"Proposal Issue Date": "proposal_issue_date"
						}',
				'lang' => [
					'ar' => 'تم إنشاء اقتراح جديد بواسطة {user_name}',
					'zh' => '{user_name} 创建的新建议',
					'da' => 'Nyt forslag oprettet af {user_name}',
					'de' => 'Neues Angebot erstellt von {user_name}',
					'en' => 'New Proposal created by {user_name}',
					'es' => 'Nueva propuesta creada por {user_name}',
					'fr' => 'Nouvelle proposition créée par {user_name}',
					'he' => 'הצעה חדשה שנוצרה על-ידי {user_name}',
					'it' => 'Nuova proposta creata da {user_name}',
					'ja' => '{user_name} によって作成された新しい提案',
					'nl' => 'Nieuw voorstel gemaakt door {user_name}',
					'pl' => 'Nowa propozycja utworzona przez użytkownika {user_name}',
					'ru' => 'Новое предложение, созданное {user_name}',
					'pt' => 'Nova proposta criada por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan Yeni Teklif',
					'pt-br' => 'Nova proposta criada por {user_name}',
				]
			],
			'bill_payment' => [
				'variables' => '{
						"Payment Amount": "payment_amount",
						"Vendor Name": "vendor_name",
						"Payment Type": "payment_type"
						}',
				'lang' => [
					'ar' => 'تم إنشاء دفعة جديدة بقيمة {payment_amount} لـ {vendor_name} بواسطة {payment_type}',
					'zh' => '{payment_type} 为 {vendor_name} 创建了新的支付 { payment_金额}',
					'da' => 'Ny betaling på {payment_amount} oprettet for {vendor_name} af {payment_type}',
					'de' => 'Neue Zahlung in Höhe von {payment_amount} erstellt für {vendor_name} von {payment_type}',
					'en' => 'New payment of {payment_amount} created for {vendor_name} by {payment_type}',
					'es' => 'Nuevo pago de {pago_cantidad} creado para {vendor_name} por {pago_tipo}',
					'fr' => 'Nouveau paiement de {payment_amount} créé pour {vendor_name} par {payment_type}',
					'he' => 'תשלום חדש של {payment_מאונט} שנוצר עבור {vendor_name} על-ידי {payment_type}',
					'it' => 'Nuovo pagamento di {payment_amount} creato per {vendor_name} da {payment_type}',
					'ja' => '{payment_type} によって {vendor_name} に対して作成された {payment_mount} の新しい支払い',
					'nl' => 'Nieuwe betaling van {payment_amount} gemaakt voor {vendor_name} door {payment_type}',
					'pl' => 'Nowa płatność {payment_amount} utworzona dla {vendor_name} przez {payment_type}',
					'ru' => 'Создан новый платеж {payment_amount} для {vendor_name} по {payment_type}',
					'pt' => 'Novo pagamento de {payment_amount} criado para {vendor_name} por {payment_type}',
					'tr' => '{ payment_type } tarafından { vendor_name } için yeni { payment_amount } ödemesi oluşturuldu',
					'pt-br' => 'Novo pagamento de {payment_amount} criado para {vendor_name} por {payment_type}',
				]
			],
			'invoice_payment_reminder' => [
				'variables' => '{
						"Customer Name": "customer_name",
						"Invoice Number": "invoice_number",
						"Company Name": "user_name"
						}',
				'lang' => [
					'ar' => 'تم إنشاء تذكير دفع جديد لـ {invoice_number} بواسطة {user_name}',
					'zh' => '{ user_name} 创建的 { invoice_number} 的新支付提醒',
					'da' => 'Ny betalingspåmindelse om {invoice_number} oprettet af {user_name}',
					'de' => 'Neue Zahlungserinnerung von {invoice_number} erstellt von {user_name}',
					'en' => 'New Payment Reminder of {invoice_number} created by {user_name}',
					'es' => 'Nuevo recordatorio de pago de {invoice_number} creado por {user_name}',
					'fr' => 'Nouveau rappel de paiement de {invoice_number} créé par {user_name}',
					'he' => 'תזכורת חדשה לתשלום עבור {invoice_number} שנוצרה על-ידי {user_name}',
					'it' => 'Nuovo sollecito di pagamento di {invoice_number} creato da {user_name}',
					'ja' => '{user_name} によって作成された {invoice_number} の新しい支払い通知',
					'nl' => 'Nieuwe betalingsherinnering van {invoice_number} gemaakt door {user_name}',
					'pl' => 'Nowe przypomnienie o płatności {invoice_number} utworzone przez użytkownika {user_name}',
					'ru' => 'Новое напоминание об оплате {invoice_number}, созданное {user_name}',
					'pt' => 'Novo lembrete de pagamento de {invoice_number} criado por {user_name}',
					'tr' => '{ user_name } tarafından oluşturulan { invoice_number } adlı yeni Ödeme Anımsatıcısı',
					'pt-br' => 'Novo lembrete de pagamento de {invoice_number} criado por {user_name}',
				]
			]
		],
	];
	public static function aiDefaultTemplate(): array
	{
		return [
			[
				'template_name' => 'leave_reason',
				'prompt' => "Generate a comma-separated string of common leave reasons that employees may provide to their employers. Include both personal and professional reasons for taking leave, such only ##type## . Aim to generate a diverse range of leave reasons that can be used in different situations. Please provide a comprehensive and varied list of leave reasons that can help employers understand and accommodate their employees' needs.",
				'module' => 'leave',
				'field_json' => '{"field":[{"label":"Leave Type","placeholder":"e.g.illness, family emergencies,vacation","field_type":"text_box","field_name":"type"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'subject',
				'prompt' => "Generate a goal subject for an employee's goal related type to ##type##.",
				'module' => 'goal tracking',
				'field_json' => '{"field":[{"label":"Goal Type","placeholder":"e.g.invoice, production,hiring","field_type":"text_box","field_name":"type"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a goal descriptions for an employee's goal title is ##title##.",
				'module' => 'goal tracking',
				'field_json' => '{"field":[{"label":"Goal Title","placeholder":"e.g.Invoice Accuracy","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a job training descriptions for a ##position## position. The training description should include responsibilities such as ##responsibilities##. Please ensure the descriptions are concise, informative, and accurately reflect the key responsibilities of a ##position##.",
				'module' => 'training',
				'field_json' => '{"field":[{"label":"Position","placeholder":"job training descriptions","field_type":"text_box","field_name":"position"},{"label":"Responsibilities","placeholder":"Managing training logistics","field_type":"textarea","field_name":"responsibilities"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "Generate a list of job titles commonly found in an ##work_place##. The job titles should cover a range of roles and responsibilities within the field of ##field##. Include positions such as ##positions##. Please provide a diverse selection of job titles that accurately reflect the various positions found in an ##work_place##.",
				'module' => 'job',
				'field_json' => '{"field":[{"label":"Work Place","placeholder":"e.g.IT Company,hospital","field_type":"text_box","field_name":"work_place"},{"label":"Field ","placeholder":"e.g.Backend","field_type":"text_box","field_name":"field"},{"label":"Positions","placeholder":"e.g.developer,tester","field_type":"text_box","field_name":"positions"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a job descriptions for a ##position## position. The job description should include responsibilities such as ##responsibilities##. Please ensure the descriptions are concise, informative, and accurately reflect the key responsibilities of a ##position##.",
				'module' => 'job',
				'field_json' => '{"field":[{"label":"Position","placeholder":"job for a position","field_type":"text_box","field_name":"position"},{"label":"Responsibilities","placeholder":"","field_type":"textarea","field_name":"responsibilities"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'requirement',
				'prompt' => "Generate a comma-separated string of job requirements for a ##position## position. The requirements should include ##description##. Please provide the requirements in a comma-separated string format.",
				'module' => 'job',
				'field_json' => '{"field":[{"label":"Position","placeholder":"requirement of job","field_type":"text_box","field_name":"position"},{"label":"Description","placeholder":"","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a description for presenting the Award. The description should highlight ##reasons##. Emphasize the significance of the  Award as a symbol of recognition for employee's remarkable accomplishments and its representation of her '##reasons##' and impact on the organization. Please create a personalized and engaging description that conveys appreciation, pride, and gratitude for employee's contributions to the company's sucess",
				'module' => 'award',
				'field_json' => '{"field":[{"label":"Award reasons","placeholder":"e.g.skilled, focused ,efficiency","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a list of common reasons for employee transfers within an organization. Include reasons such as ##reasons##. Please provide a comprehensive and varied list of reasons that can help employers understand and address employee transfer situations effectively.",
				'module' => 'transfer',
				'field_json' => '{"field":[{"label":"Transfer reasons","placeholder":"e.g.career development,special projects or initiatives","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a description why an employee might choose to resign and request a transfer to another location within the company. Include both professional and personal reasons that could contribute to this decision. Examples may include ##reasons##. Aim to provide a comprehensive and varied description that can help employers understand and accommodate employees' needs when considering a transfer request",
				'module' => 'resignation',
				'field_json' => '{"field":[{"label":"Resignation reasons","placeholder":"e.g.career development,health issues","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a description for organizing a company trip. The trip aims to ##aims## . Please provide a diverse description that highlight the benefits and positive outcomes associated with organizing a company trip. Focus on creating an engaging and enjoyable experience for employees while also achieving business objectives and cultivating a positive work environment.",
				'module' => 'travel',
				'field_json' => '{"field":[{"label":"Aims","placeholder":"e.g.career development,health issues","field_type":"textarea","field_name":"aims"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'promotion_title',
				'prompt' => "Generate a list of promotion title suggestions for an ##role##. The promotion titles should reflect ##reasons##, and recognition of the ##role##'s accomplishments. Please provide a diverse range of promotion titles that align with ##role## job roles and levels within the company. Aim to create titles that are both professional and descriptive, highlighting the ##role##'s progression and impact within the organization.",
				'module' => 'promotion',
				'field_json' => '{"field":[{"label":"Job","placeholder":"e.g.doctor, developer","field_type":"text_box","field_name":"role"},{"label":"Promotion Reasons","placeholder":"e.g.increased responsibility, higher position","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a promotion description for this title:##title##. ",
				'module' => 'promotion',
				'field_json' => '{"field":[{"label":"Promotion Title","placeholder":"e.g.Medical Director","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "Generate a list of titles for complaints related to employee and company issues. ##reasons##. Please provide a range of titles that accurately reflect common complaint categories, ensuring they are concise, descriptive, and effective in conveying the nature of the complaint. ",
				'module' => 'complaint',
				'field_json' => '{"field":[{"label":"Complaint reasons","placeholder":"e.g.unprofessional behavior, harassment,","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a Complaint description for this title:##title##. ",
				'module' => 'complaint',
				'field_json' => '{"field":[{"label":"Complaint Title","placeholder":"e.g.Unprofessional Behavior Complaint","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a warning description for an employee who consistently ##reasons##. The warning should address the employee's ##reasons##, including further disciplinary action or termination of employment. Please provide a clear and firm warning message that encourages the employee to review the policy and make immediate improvements.",
				'module' => 'warning',
				'field_json' => '{"field":[{"label":"Warning reasons","placeholder":"e.g.break attendance policy","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a termination description for  the reason :##reason##. The description should convey the company's regret over the decision and outline the specific concerns, such as ##reasons##. Please provide a clear and professional message that explains the decision while expressing appreciation for the employee's contributions. Aim to offer guidance for personal and professional growth and provide necessary instructions regarding final paycheck and return of company property.",
				'module' => 'termination',
				'field_json' => '{"field":[{"label":"Termination reasons","placeholder":"e.g.Poor Performance","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate an announcement title for ##reasons##. The title should be attention-grabbing and informative, effectively conveying the key message to the intended audience. Please ensure the title is appropriate for the given situation, whether it's about a ##reasons##. Aim to create a title that captures the essence of the announcement and sparks interest or curiosity among the readers.",
				'module' => 'announcement',
				'field_json' => '{"field":[{"label":"Announcement reasons","placeholder":"e.g.Growth Opportunities","field_type":"textarea","field_name":"reasons"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'occasion',
				'prompt' => "Generate a list of holiday occasions for celebrations and gatherings. The occasions should cover a variety of holidays and events throughout the year, such as ##name##. Please provide a diverse range of occasions that can be used for hosting parties, organizing special events, or planning festive activities. Aim to offer unique and creative ideas that cater to different cultures, traditions, and preferences.",
				'module' => 'holiday',
				'field_json' => '{"field":[{"label":"Any Specific occasions","placeholder":"e.g.Cultural Celebration","field_type":"text_box","field_name":"name"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "Generate a creative and engaging event title for an up'comi'ng event. The event can be a ##type##. Please focus on creating a title that captures the essence of the event, sparks curiosity, and encourages attendance. Aim to make the title memorable, intriguing, and aligned with the purpose and theme of the event. Consider the target audience, event objectives, and any specific keywords or ideas you would like to incorporate",
				'module' => 'event',
				'field_json' => '{"field":[{"label":"Specific type of event","placeholder":"e.g.conference, workshop, seminar","field_type":"text_box","field_name":"name"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "Generate a meeting title that is catchy and informative. The title should effectively convey the purpose and focus of the meeting, whether it's for ##purpose##. Please aim to create a title that grabs the attention of participants, reflects the importance of the meeting, and provides a clear understanding of what will be discussed or accomplished during the session.",
				'module' => 'meeting',
				'field_json' => '{"field":[{"label":"Meeting purpose","placeholder":"e.g.conference, workshop","field_type":"textarea","field_name":"purpose"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a descriptive response for a given ##title##. The response should be detailed, engaging, and informative, providing relevant information and capturing the reader's interest",
				'module' => 'account asset',
				'field_json' => '{"field":[{"label":"Asset name","placeholder":"HR may provide some devices ","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a description based on a given document name:##name##. The document name: ##name## represents a specific file or document, and you need a descriptive summary or overview of its contents. Please provide a clear and concise description that captures the main points, purpose, or key information contained within the document. Aim to create a brief but informative description that gives the reader an understanding of what they can expect when accessing or reviewing the document.",
				'module' => 'document',
				'field_json' => '{"field":[{"label":"Asset name","placeholder":"e.g. Employee handbook","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "Generate a suitable title for the company policy regarding ##description##. The title should be clear, concise, and informative, effectively conveying the purpose and scope of the policy. Please ensure that the title reflects the importance of ##description##. Aim to create a title that is professional, easily understandable, and aligned with the company's culture and values.",
				'module' => 'company policy',
				'field_json' => '{"field":[{"label":"Description of policy","placeholder":"e.g.Leave policies,Performance management","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "generate description for this title ##title##",
				'module' => 'chart of account',
				'field_json' => '{"field":[{"label":" Title ","placeholder":"e.g.Accounts Receivable,Office Equipment","field_type":"textarea","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "generate description for this title ##title##",
				'module' => 'journal entry',
				'field_json' => '{"field":[{"label":" Title ","placeholder":"e.g.Accounts Receivable,Office Equipment","field_type":"textarea","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'subject',
				'prompt' => "Generate a lead subject line for a marketing campaign targeting potential customers for a software development company specializing in web and mobile applications.",
				'module' => 'lead',
				'field_json' => '{"field":[{"label":"Description","placeholder":"e.g. Leads represent potential sales opportunities for a business","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'name',
				'prompt' => "generate deal name for this proposal description ##description##",
				'module' => 'deal',
				'field_json' => '{"field":[{"label":"Proposal Description","placeholder":"e.g.Collaboration and Partnerships","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'subject',
				'prompt' => "generate contract subject for this contract description ##description##",
				'module' => 'contract',
				'field_json' => '{"field":[{"label":"Proposal Description","placeholder":"e.g.Terms and Conditions","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "generate contract description for this contract subject ##subject##",
				'module' => 'contract',
				'field_json' => '{"field":[{"label":"Contract Subject","placeholder":"e.g.Legal Protection,Terms and Conditions","field_type":"textarea","field_name":"subject"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'project_name',
				'prompt' => "Create creative product names:  ##description## \n\nSeed words: ##keywords## \n\n",
				'module' => 'project',
				'field_json' => '{"field":[{"label":"Project Description","placeholder":"e.g.Efficiency and Optimization,Business Growth and Expansion","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'name',
				'prompt' => "Generate a task name for a project in an ##project_name##, specifically related to ##instruction##.",
				'module' => 'project task',
				'field_json' => '{"field":[{"label":"Project name","placeholder":"e.g.Solving Problems","field_type":"text_box","field_name":"project_name"},{"label":"Task Instruction","placeholder":"e.g.Data Analysis","field_type":"textarea","field_name":"instruction"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "Generate a milestone name for a ##project_name##,specifically related to ##instruction##.",
				'module' => 'project milestone',
				'field_json' => '{"field":[{"label":"Milestone Description","placeholder":"e.g.Design Approved","field_type":"textarea","field_name":"description"},{"label":" Instruction","placeholder":"e.g. incorporated feedback and revisions","field_type":"textarea","field_name":"instruction"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "You are a software developer working on a platform or service, and you're experiencing a bug where ##description##. You need to come up with a descriptive bug title for this issue. Please generate a few bug titles that could be used to report this problem.",
				'module' => 'project bug',
				'field_json' => '{"field":[{"label":"Description of Bug","placeholder":"e.g.identify bugs and issues","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Write a long creative product description for: ##title## \n\nTarget audience is: ##audience## \n\nUse this description: ##description## \n\nTone of generated text must be:\n ##tone_language## \n\n",
				'module' => 'product service',
				'field_json' => '{"field":[{"label":"Product name","placeholder":"e.g. VR, Honda","field_type":"text_box","field_name":"title"},{"label":"Audience","placeholder":"e.g. Women, Aliens","field_type":"text_box","field_name":"audience"},{"label":"Product Description","placeholder":"e.g. VR is an innovative device that can allow you to be part of virtual world","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'name',
				'prompt' => "generate warehouse name for ##description##",
				'module' => 'warehouse',
				'field_json' => '{"field":[{"label":"Description","placeholder":"e.g.North Warehouse","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'subject',
				'prompt' => "generate example of  subject for bug in ecommerce base website support ticket",
				'module' => 'support',
				'field_json' => '{"field":[{"label":"Ticket Description of Bug","placeholder":"e.g.Bug Summary","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "generate support ticket description of  subject for ##subject## ",
				'module' => 'support',
				'field_json' => '{"field":[{"label":"Ticket Subject","placeholder":"e.g.Error Message Displayed","field_type":"textarea","field_name":"subject"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'title',
				'prompt' => "Generate a list of Zoom meeting topics for ##description## metting. The purpose of the meeting is to  ##description##. Structure the topics to ensure a productive discussion.",
				'module' => 'zoom meeting',
				'field_json' => '{"field":[{"label":"Meeting description ","placeholder":"e.g.Remote Collaboration","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'content',
				'prompt' => "Generate a meeting notification message for an ##topic## meeting. Include the date, time, location, and a brief agenda with three key discussion points.",
				'module' => 'notification template',
				'field_json' => '{"field":[{"label":"Notification Message","placeholder":"e.g.brief explanation of the purpose or background of the notification","field_type":"textarea","field_name":"topic"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'name',
				'prompt' => "please suggest subscription plan  name  for this  :  ##description##  for my business",
				'module' => 'plan',
				'field_json' => '{"field":[{"label":"What is your plan about?","placeholder":"e.g. Describe your plan details ","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "please suggest subscription plan  description  for this  :  ##title##:  for my business",
				'module' => 'plan',
				'field_json' => '{"field":[{"label":"What is your plan title?","placeholder":"e.g. Pro Resller,Exclusive Access","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'name',
				'prompt' => "give 10 catchy only name of Offer or discount Coupon for : ##keywords##",
				'module' => 'coupon',
				'field_json' => '{"field":[{"label":"Seed words","placeholder":"e.g.coupon will provide you with a discount on your selected plan","field_type":"text_box","field_name":"keywords"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'meta_title',
				'prompt' => "Write SEO meta title for:\n\n ##description## \n\nWebsite name is:\n ##title## \n\nSeed words:\n ##keywords## \n\n",
				'module' => 'seo',
				'field_json' => '{"field":[{"label":"Website Name","placeholder":"e.g. Amazon, Google","field_type":"text_box","field_name":"title"},{"label":"Website Description","placeholder":"e.g. Describe what your website or business do","field_type":"textarea","field_name":"description"},{"label":"Keywords","placeholder":"e.g.  cloud services, databases","field_type":"text_box","field_name":"keywords"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'meta_desc',
				'prompt' => "Write SEO meta description for:\n\n ##description## \n\nWebsite name is:\n ##title## \n\nSeed words:\n ##keywords## \n\n",
				'module' => 'seo',
				'field_json' => '{"field":[{"label":"Website Name","placeholder":"e.g. Amazon, Google","field_type":"text_box","field_name":"title"},{"label":"Website Description","placeholder":"e.g. Describe what your website or business do","field_type":"textarea","field_name":"description"},{"label":"Keywords","placeholder":"e.g.  cloud services, databases","field_type":"text_box","field_name":"keywords"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'cookie_title',
				'prompt' => "please suggest me cookie title for this ##description## website which i can use in my website cookie",
				'module' => 'cookie',
				'field_json' => '{"field":[{"label":"Website name or info","placeholder":"e.g. example website ","field_type":"textarea","field_name":"title"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'cookie_description',
				'prompt' => "please suggest me  Cookie description for this cookie title ##title##  which i can use in my website cookie",
				'module' => 'cookie',
				'field_json' => '{"field":[{"label":"Cookie Title ","placeholder":"e.g. example website ","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'strictly_cookie_title',
				'prompt' => "please suggest me only Strictly Cookie Title for this ##description## website which i can use in my website cookie",
				'module' => 'cookie',
				'field_json' => '{"field":[{"label":"Website name or info","placeholder":"e.g. example website ","field_type":"textarea","field_name":"title"}]}',
				'is_tone' => '0',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'strictly_cookie_description',
				'prompt' => "please suggest me Strictly Cookie description for this Strictly cookie title ##title##  which i can use in my website cookie",
				'module' => 'cookie',
				'field_json' => '{"field":[{"label":"Strictly Cookie Title ","placeholder":"e.g. example website ","field_type":"text_box","field_name":"title"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'more_information_description',
				'prompt' => "I need assistance in crafting compelling content for my ##web_name## website's 'Contact Us' page of my website. The page should provide relevant information to users, encourage them to reach out for inquiries, support, and feedback, and reflect the unique value proposition of my business.",
				'module' => 'cookie',
				'field_json' => '{"field":[{"label":"Websit Name","placeholder":"e.g. example website ","field_type":"text_box","field_name":"web_name"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'content',
				'prompt' => "generate email template for ##type##",
				'module' => 'email template',
				'field_json' => '{"field":[{"label":"Email Type","placeholder":"e.g. new user,new client","field_type":"text_box","field_name":"type"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'note',
				'prompt' => "Generate short description Note for lead ##description##",
				'module' => 'lead',
				'field_json' => '{"field":[{"label":"Lead description","placeholder":"e.g. create notes for lead user ","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a short note summarizing the key points discussed during a lead ##name## call. The purpose of the note is to capture important details and action items discussed with the ##name## lead. Please structure the note in a concise and organized manner.",
				'module' => 'lead',
				'field_json' => '{"field":[{"label":"Lead name","placeholder":"e.g. create description for lead user ","field_type":"textarea","field_name":"name"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'note',
				'prompt' => "Generate short description Note for deal ##description##",
				'module' => 'deal',
				'field_json' => '{"field":[{"label":"Deal description","placeholder":"e.g.create note for deal client","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
			[
				'template_name' => 'description',
				'prompt' => "Generate a short note summarizing a deal call. Imagine you just had a call with a potential client or partner to discuss a ##description## deal. Write a concise summary of the key points discussed during the call. Include the important details such as the client's name, the purpose of the call, any agreements or decisions made, and next steps to be taken.",
				'module' => 'deal',
				'field_json' => '{"field":[{"label":"Deal name","placeholder":"e.g. Establishing Communication ","field_type":"textarea","field_name":"description"}]}',
				'is_tone' => '1',
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s'),
			],
		];
	}
	//            [
	//                'template_name'=>'grammar',
	//                'prompt'=>"please correct grammar mistakes and spelling mistakes in this: '###description##'",
	//                'module'=>'grammar',
	//                'field_json'=>'',
	//                'is_tone'=>'1',
	//                "created_at" => date('Y-m-d H:i:s'),
	//                "updated_at" => date('Y-m-d H:i:s'),
	//            ],
	//          [
	//                'template_name'=>'content',
	//                'prompt'=>"Generate a joining offer letter for {applicant_name} who has been selected for the position of {job_title} at {app_name}. Customize the letter by filling in the appropriate details and placeholders enclosed in {}. Please structure the letter to include the mentioned variables in their respective sections. Sign off the letter with the company name.Variables:{applicant_name},{app_name},{job_title},{start_date},{workplace_location},{days_of_week},{salary},{salary_type},{offer_expiration_date} and  letter must be descriptive with eficient information",
	//                'module'=>'offer letter',
	//                'field_json'=>'',
	//                'is_tone'=>'1',
	//                "created_at" => date('Y-m-d H:i:s'),
	//                "updated_at" => date('Y-m-d H:i:s'),
	//            ],
	//            [
	//                'template_name'=>'content',
	//                'prompt'=>"Generate a joining letter for {employee_name} who has been selected for the position of {designation} at {app_name}. Customize the letter by filling in the appropriate details and placeholders enclosed in {}. Please structure the letter to include the mentioned Variables:{date},{employee_name}{address},{designation},{start_date},{branch},{start_time},{end_time}, {total_hours} in their respective sections. Sign off the letter with the company name and the date. alse add subject ,company conditions for ##conditions##",
	//                'module'=>'joining letter',
	//                'field_json'=>'{"field":[{"label":"Company  Policy/Condition (comma seperated string)","placeholder":"e.g.,"leave,holiday,salary":"textarea","field_name":"conditions"}]}',
	//                'is_tone'=>'1',
	//                "created_at" => date('Y-m-d H:i:s'),
	//                "updated_at" => date('Y-m-d H:i:s'),
	//          ],
	// TODO EVALUATE DYNAMIC VERSION LATER
	// $arrPermissions = self::permissions();
	// Permission::insert($arrPermissions);

	// $superAdminRole = Role::create(
	//     ['name' => 'super admin', DatabaseConstants::COL_TABLE_CREATOR => 0]
	// );
	// $superAdminPermissions = [];
	// foreach ([
	//     'super##admin##dashboard' => ['manage'],
	//     'user' => self::STANDARD_PERMISSIONS,
	//     'role' => self::STANDARD_PERMISSIONS,
	//     'permission' => self::STANDARD_PERMISSIONS,
	//     'plan' => ['create', 'edit'],
	//     'order' => ['manage'],
	//     'coupon' => self::STANDARD_PERMISSIONS,
	// ] as $suffix => $extraPerms) {
	//     $remove = $suffix === 'plan' ? ['delete'] : [];
	//     $superAdminPermissions = array_merge(
	//         $superAdminPermissions,
	//         self::defaultedPermissions($suffix, $extraPerms, $remove)
	//     );
	// }
	// $superAdminRole->givePermissionTo($superAdminPermissions);
	// $superAdmin = User::create(
	//     [
	//         'name' => 'Super Admin',
	//         'email' => 'superadmin@example.com',
	//         'password' => Hash::make('1234'),
	//         'type' => 'super admin',
	//         'lang' => 'en',
	//         'avatar' => '',
	//         DatabaseConstants::COL_TABLE_CREATOR => 0,
	//         'email_verified_at' => now(),
	//     ]
	// );
	// $superAdmin->assignRole($superAdminRole);

	// $customerRole = Role::create([
	//     'name' => 'customer', DatabaseConstants::COL_TABLE_CREATOR => 0,
	// ]);
	// $customerPermissions = [];
	// foreach ([
	//     'customer##payment' => ['manage'],
	//     'customer##transaction' => ['manage'],
	//     'customer##invoice' => ['manage'],
	//     'invoice' => ['show'],
	//     'proposal' => ['show'],
	//     'customer##proposal' => ['manage'],
	//     'customer' => ['show'],
	// ] as $suffix => $extraPerms) {
	//     $customerPermissions = array_merge(
	//         $customerPermissions,
	//         self::defaultedPermissions($suffix, $extraPerms)
	//     );
	// }
	// $customerRole->givePermissionTo($customerPermissions);

	// // Vendor
	// $vendorRole = Role::create(['name' => 'vendor', DatabaseConstants::COL_TABLE_CREATOR => 0]);
	// $vendorPermissions = [];
	// foreach ([
	//     'vendor' => ['show'],
	//     'bill' => ['show'],
	//     'vendor##payment' => ['manage'],
	//     'vendor##transaction' => ['manage'],
	// ] as $suffix => $extraPerms) {
	//     $vendorPermissions = array_merge(
	//         $vendorPermissions,
	//         self::defaultedPermissions($suffix, $extraPerms)
	//     );
	// }
	// $vendorPermissions[] = ['name' => 'vendor manage bill', 'guard_name' => 'web'] + self::timestamps();
	// $vendorRole->givePermissionTo($vendorPermissions);

	// // Company
	// $companyRole = Role::create(['name' => 'company', DatabaseConstants::COL_TABLE_CREATOR => 0]);
	// $companyPermissions = [];
	// foreach ([
	//     // dashboards
	//     'pos##dashboard' => ['show'],
	//     'crm##dashboard' => ['show'],
	//     'hrm##dashboard' => ['show'],
	//     'project##dashboard' => ['show'],
	//     'account##dashboard' => ['show'],

	//     // core entities
	//     'user' => self::STANDARD_PERMISSIONS,
	//     'role' => self::STANDARD_PERMISSIONS,
	//     'permission' => self::STANDARD_PERMISSIONS,

	//     // settings
	//     'company##settings' => ['manage'],
	//     'business##settings' => ['manage'],
	//     'print##settings' => ['manage'],
	//     'stripe##settings' => ['manage'],
	//     'system##settings' => ['manage'],

	//     // finance
	//     'expense' => self::STANDARD_PERMISSIONS,
	//     'invoice' => self::STANDARD_PERMISSIONS,
	//     'invoice##report' => ['manage'],
	//     'payment##invoice' => ['create', 'delete'],
	//     'delete##invoice##product' => ['delete'],
	//     'bill' => array_merge(self::STANDARD_PERMISSIONS, ['show', 'duplicate']),
	//     'bill##report' => ['manage'],
	//     'payment##bill' => ['create', 'delete'],
	//     'delete##bill##product' => ['delete'],
	//     'revenue' => self::STANDARD_PERMISSIONS,
	//     'payment' => array_merge(self::STANDARD_PERMISSIONS, ['send']),
	//     'order' => ['manage'],
	//     'transaction' => ['manage'],
	//     'income##report' => ['manage'],
	//     'expense##report' => ['manage'],
	//     'income##vs##expense##report' => ['manage'],
	//     'stock##report' => ['manage'],
	//     'tax##report' => ['manage'],
	//     'loss##&##profit##report' => ['manage'],
	//     'statement##report' => ['manage'],

	//     // constants
	//     'unit' => self::STANDARD_PERMISSIONS,
	//     'tax' => self::STANDARD_PERMISSIONS,
	//     'category' => self::STANDARD_PERMISSIONS,
	//     'custom##field' => self::STANDARD_PERMISSIONS,

	//     // products
	//     'product##&##service' => self::STANDARD_PERMISSIONS,

	//     // customers / vendors
	//     'customer' => array_merge(self::STANDARD_PERMISSIONS, ['show']),
	//     'customer##payment' => ['manage'],
	//     'customer##transaction' => ['manage'],
	//     'customer##invoice' => ['manage'],
	//     'vendor' => array_merge(self::STANDARD_PERMISSIONS, ['show']),
	//     'vendor##bill' => ['manage'],
	//     'vendor##payment' => ['manage'],
	//     'vendor##transaction' => ['manage'],
	//     'vendor##manage##bill' => ['manage'],

	//     // bank
	//     'bank##account' => self::STANDARD_PERMISSIONS,
	//     'bank##transfer' => self::STANDARD_PERMISSIONS,

	//     // notes
	//     'credit##note' => self::STANDARD_PERMISSIONS,
	//     'debit##note' => self::STANDARD_PERMISSIONS,

	//     // proposal
	//     'proposal' => array_merge(self::STANDARD_PERMISSIONS, ['show', 'duplicate', 'send']),
	//     'proposal##product' => ['delete', 'manage'],

	//     // goals
	//     'goal' => self::STANDARD_PERMISSIONS,
	//     'goal##tracking' => self::STANDARD_PERMISSIONS,
	//     'goal##type' => self::STANDARD_PERMISSIONS,

	//     // assets
	//     'assets' => self::STANDARD_PERMISSIONS,

	//     // chart / journal / sheet / ledger / balance
	//     'chart of account' => self::STANDARD_PERMISSIONS,
	//     'grant##chart' => ['view'],
	//     'journal entry' => array_merge(self::STANDARD_PERMISSIONS, ['show']),
	//     'balance##sheet##report' => ['manage'],
	//     'timesheet' => array_merge(self::STANDARD_PERMISSIONS, ['view']),
	//     'ledger##report' => ['manage'],
	//     'trial balance##report' => ['manage'],

	//     // CRM (clients, leads, stages, etc.)
	//     'client' => self::STANDARD_PERMISSIONS,
	//     'lead' => array_merge(self::STANDARD_PERMISSIONS, ['view', 'move']),
	//     'lead##call' => ['create', 'edit', 'delete'],
	//     'lead##email' => ['create', 'edit', 'delete'],
	//     'stage' => self::STANDARD_PERMISSIONS,

	//     // super admin dashboard
	//     'super##admin##dashboard' => ['manage'],

	// ] as $suffix => $extraPerms) {
	//     $companyPermissions = array_merge(
	//         $companyPermissions,
	//         self::defaultedPermissions($suffix, $extraPerms)
	//     );
	// }
	// $companyRole->givePermissionTo($companyPermissions);
	// $company = User::create([
	//     'name'              => 'company',
	//     'email'             => 'company@example.com',
	//     'password'          => Hash::make('1234'),
	//     'type'              => 'company',
	//     'default_pipeline'  => 1,
	//     'plan'              => 1,
	//     'lang'              => 'en',
	//     'avatar'            => '',
	//     DatabaseConstants::COL_TABLE_CREATOR        => 1,
	//     'email_verified_at' => now(),
	// ]);
	// $company->assignRole($companyRole);

	// // Accountant
	// $accountantRole = Role::create(['name' => 'accountant', DatabaseConstants::COL_TABLE_CREATOR => $company->id]);
	// $accountantPermissions = [];
	// foreach ([
	//     'account##dashboard' => ['show'],
	//     'expense' => self::STANDARD_PERMISSIONS,
	//     'invoice' => array_merge(self::STANDARD_PERMISSIONS, ['show', 'convert', 'duplicate']),
	//     'invoice##report' => ['manage'],
	//     'payment##invoice' => ['create', 'delete'],
	//     'delete##invoice##product' => ['delete'],
	//     'product##&##service' => self::STANDARD_PERMISSIONS,
	//     'constant##tax' => self::STANDARD_PERMISSIONS,
	//     'constant##category' => self::STANDARD_PERMISSIONS,
	//     'constant##unit' => self::STANDARD_PERMISSIONS,
	//     'customer' => array_merge(self::STANDARD_PERMISSIONS, ['show']),
	//     'vendor' => array_merge(self::STANDARD_PERMISSIONS, ['show']),
	//     'bank##account' => self::STANDARD_PERMISSIONS,
	//     'bank##transfer' => self::STANDARD_PERMISSIONS,
	//     'revenue' => self::STANDARD_PERMISSIONS,
	//     'bill' => array_merge(self::STANDARD_PERMISSIONS, ['show', 'duplicate']),
	//     'bill##report' => ['manage'],
	//     'payment##bill' => ['create', 'delete'],
	//     'delete##bill##product' => ['delete'],
	//     'send##invoice' => ['send'],
	//     'send##bill' => ['send'],
	//     'transaction' => ['manage'],
	//     'credit##note' => self::STANDARD_PERMISSIONS,
	//     'debit##note' => self::STANDARD_PERMISSIONS,
	//     'proposal' => array_merge(self::STANDARD_PERMISSIONS, ['duplicate', 'show', 'send']),
	//     'proposal##product' => ['delete'],
	//     'goal' => self::STANDARD_PERMISSIONS,
	//     'assets' => self::STANDARD_PERMISSIONS,
	//     'statement##report' => ['manage'],
	//     'constant##custom##field' => self::STANDARD_PERMISSIONS,
	//     'chart of account' => self::STANDARD_PERMISSIONS,
	//     'grant##chart' => ['view'],
	//     'journal entry' => array_merge(self::STANDARD_PERMISSIONS, ['show']),
	//     'balance##sheet##report' => ['manage'],
	//     'timesheet' => array_merge(self::STANDARD_PERMISSIONS, ['view']),
	//     'ledger##report' => ['manage'],
	//     'trial balance##report' => ['manage'],
	//     'print##settings' => ['manage'],
	//     'budget##plan' => ['manage', 'create', 'edit', 'delete', 'view'],
	//     'barcode' => ['create'],
	//     'webhook' => ['create', 'edit', 'delete'],
	// ] as $suffix => $extraPerms) {
	//     $accountantPermissions = array_merge(
	//         $accountantPermissions,
	//         self::defaultedPermissions($suffix, $extraPerms)
	//     );
	// }
	// $accountantRole->givePermissionTo($accountantPermissions);
	// $accountant = User::create([
	//     'name' => 'accountant',
	//     'email' => 'accountant@example.com',
	//     'password' => Hash::make('1234'),
	//     'type' => 'accountant',
	//     'default_pipeline' => 1,
	//     'lang' => 'en',
	//     'avatar' => '',
	//     DatabaseConstants::COL_TABLE_CREATOR => $company->id,
	//     'email_verified_at' => now(),
	// ]);
	// $accountant->assignRole($accountantRole);

	// \App\Models\BankAccount::create(
	//     [
	//         'holder_name' => 'cash',
	//         'bank_name' => '',
	//         'account_number' => '-',
	//         'opening_balance' => '0.00',
	//         'contact_number' => '-',
	//         'bank_address' => '-',
	//         DatabaseConstants::COL_TABLE_CREATOR => $company->id,
	//     ]
	// );

	// // Client
	// $clientRole = Role::create(['name' => 'client', DatabaseConstants::COL_TABLE_CREATOR => $company->id]);
	// $clientPermissions = [];
	// foreach ([
	//     'client##dashboard' => ['manage'],
	//     'bug##report' => array_merge(self::STANDARD_PERMISSIONS, ['move']),
	//     'deal' => ['view', 'manage', 'move'],
	//     'project' => ['manage', 'view'],
	//     'grant##chart' => ['view'],
	//     'timesheet' => ['view', 'manage'],
	//     'project##task' => array_merge(self::STANDARD_PERMISSIONS, ['view']),
	//     'activity' => ['view'],
	//     'CRM##activity' => ['view'],
	//     'task' => ['view'],
	//     'pipeline' => ['manage'],
	//     'lead##stage' => ['manage'],
	//     'label' => ['manage'],
	//     'source' => ['manage'],
	//     'stage' => ['manage'],
	//     'contract' => array_merge(self::STANDARD_PERMISSIONS, ['show']),
	// ] as $suffix => $extraPerms) {
	//     $clientPermissions = array_merge(
	//         $clientPermissions,
	//         self::defaultedPermissions($suffix, $extraPerms)
	//     );
	// }
	// $clientRole->givePermissionTo($clientPermissions);
	// $client = User::create([
	//     'name' => 'client',
	//     'email' => 'client@example.com',
	//     'password' => Hash::make('1234'),
	//     'type' => 'client',
	//     'default_pipeline' => 1,
	//     'lang' => 'en',
	//     'avatar' => '',
	//     DatabaseConstants::COL_TABLE_CREATOR => $company->id,
	//     'email_verified_at' => now(),
	// ]);
	// $client->assignRole($clientRole);

	// Utility::employeeDetails($client->id,$company->id);

	// ! OLD PERMISSIONS
	// private function permissions(): array
	// {
	//     $base = ['guard_name' => 'web'] + self::timestamps();

	//     // 1) DASHBOARDS
	//     $dashboardPermissions = collect(['pos', 'crm', 'hrm', 'project', 'account'])
	//         ->flatMap(fn ($dt) => self::variatePerm("show##{$dt}##dashboard"))
	//         ->map(fn ($name) => ['name' => $name] + $base)
	//         ->all();

	//     // 2) CORE ENTITIES
	//     $userPermissions      = self::defaultedPermissions('user');
	//     $rolePermissions      = self::defaultedPermissions('role');
	//     $permissionPermissions = self::defaultedPermissions('permission');

	//     // 3) LANGUAGE
	//     $languagePermissions = collect(self::variatePerm('create##language'))
	//         ->map(fn ($name) => ['name' => $name] + $base)
	//         ->all();

	//     // 4) SETTINGS
	//     $settingsPermissions = collect(['company', 'print', 'business', 'stripe', 'system'])
	//         ->flatMap(fn ($group) => self::defaultedPermissions("{$group}##settings"))
	//         ->all();

	//     // 5) INVOICE BLOCK
	//     $invoiceCorePermissions = self::defaultedPermissions(
	//         suffix: 'invoice',
	//         addPerms: ['copy', 'show', 'send', 'convert', 'duplicate']
	//     );
	//     $paymentInvoicePermissions = self::defaultedPermissions('payment##invoice');
	//     $deleteInvoiceProductPermissions = collect(self::variatePerm('delete##invoice##product'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();
	//     $invoiceReportPermissions = collect(self::variatePerm('invoice##report'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();
	//     $invoicePermissions = array_merge(
	//         $invoiceCorePermissions,
	//         $paymentInvoicePermissions,
	//         $deleteInvoiceProductPermissions,
	//         $invoiceReportPermissions
	//     );

	//     // 6) BILLS
	//     $billCorePermissions = self::defaultedPermissions(
	//         suffix: 'bill',
	//         addPerms: ['show', 'duplicate']
	//     );
	//     $paymentBillPermissions = self::defaultedPermissions(
	//         suffix: 'payment##bill',
	//         addPerms: ['create', 'delete'],
	//         removePerms: ['manage']
	//     );
	//     $deleteBillProductPermissions = collect(self::variatePerm('delete##bill##product'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();
	//     $billReportPermissions = collect(self::variatePerm('bill##report'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();
	//     $billPermissions = array_merge(
	//         $billCorePermissions,
	//         $paymentBillPermissions,
	//         $deleteBillProductPermissions,
	//         $billReportPermissions
	//     );

	//     // 7) EXPENSE
	//     $expensePermissions = self::defaultedPermissions('expense', addPerms: ['view']);

	//     // 8) CONSTANTS
	//     $constantPermissions = collect(['unit', 'tax', 'category', 'custom##field'])
	//         ->flatMap(fn ($grp) => self::defaultedPermissions("constant##{$grp}"))
	//         ->all();

	//     // 9) PRODUCT & SERVICE
	//     $productServicePermissions = self::defaultedPermissions('product##&##service');

	//     // 10) CUSTOMERS
	//     $customerCrudPlusShow = self::defaultedPermissions('customer', addPerms: ['show']);
	//     $customerManageSub  = collect(['payment', 'transaction', 'invoice'])
	//         ->flatMap(fn ($sub) => self::defaultedPermissions("customer##{$sub}", removePerms: ['create', 'edit', 'delete']))
	//         ->all();
	//     $customerPermissions = array_merge($customerCrudPlusShow, $customerManageSub);

	//     // 11) VENDORS
	//     $vendorCrudPlusShow = self::defaultedPermissions('vendor', addPerms: ['show']);
	//     $vendorManageSub  = collect(['bill', 'payment', 'transaction'])
	//         ->flatMap(fn ($sub) => self::defaultedPermissions("vendor##{$sub}", removePerms: ['create', 'edit', 'delete']))
	//         ->all();
	//     $vendorExtraBill  = collect(self::variatePerm('vendor##manage##bill'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();
	//     $vendorPermissions = array_merge($vendorCrudPlusShow, $vendorManageSub, $vendorExtraBill);

	//     // 12) BANK
	//     $bankAccountPermissions = self::defaultedPermissions('bank##account');
	//     $bankTransferPermissions = self::defaultedPermissions('bank##transfer');

	//     // 13) TRANSACTION & ORDER
	//     $transactionPermissions = collect(self::variatePerm('manage##transaction'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();
	//     $orderPermissions = collect(self::variatePerm('manage##order'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();

	//     // 14) REVENUE & PAYMENT
	//     $revenuePermissions = self::defaultedPermissions('revenue');
	//     $paymentPermissions = self::defaultedPermissions('payment', addPerms: ['send']);

	//     // 15) REPORT GROUPS
	//     $reportGroups = ['income', 'expense', 'income##vs##expense', 'stock', 'tax', 'loss##&##profit'];
	//     $reportPermissions = collect($reportGroups)
	//         ->flatMap(fn ($grp) => self::variatePerm("{$grp}##report"))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();

	//     // 16) CREDIT / DEBIT / PROPOSAL
	//     $creditPermissions = self::defaultedPermissions('credit##note');
	//     $debitPermissions  = self::defaultedPermissions('debit##note');
	//     $proposalMain      = self::defaultedPermissions('proposal', addPerms: ['duplicate', 'show', 'send']);
	//     $proposalProductDel = collect(self::variatePerm('delete##proposal##product'))
	//         ->map(fn ($n) => ['name' => $n] + $base)
	//         ->all();
	//     $proposalPermissions = array_merge($proposalMain, $proposalProductDel);

	//     // 17) GOALS
	//     $goalPermissions = collect(['goal', 'goal tracking', 'goal type'])
	//         ->flatMap(fn ($g) => self::defaultedPermissions($g))
	//         ->all();

	//     // 18) Assets
	//     $assetsPermissions = self::defaultedPermissions('assets');

	//     // 19) Statement report
	//     $statementPermissions = collect(self::variatePerm('statement##report'))
	//         ->map(fn ($name) => ['name' => $name] + $base)
	//         ->all();

	//     // 20) Chart of account + grant chart
	//     $chartPermissions = array_merge(
	//         // chart of account (manage/create/edit/delete)
	//         self::defaultedPermissions('chart of account'),
	//         // view grant chart
	//         collect(self::variatePerm('view##grant##chart'))
	//             ->map(fn ($name) => ['name' => $name] + $base)
	//             ->all()
	//     );

	//     // 21) Journal entry (+ show)
	//     $journalPermissions = self::defaultedPermissions(
	//         suffix: 'journal entry',
	//         addPerms: ['show']
	//     );

	//     // 22) Balance sheet + timesheet
	//     $sheetPermissions = array_merge(
	//         // balance sheet report
	//         collect(self::variatePerm('balance##sheet##report'))
	//             ->map(fn ($name) => ['name' => $name] + $base)
	//             ->all(),
	//         // timesheet (manage/create/edit/delete + view)
	//         self::defaultedPermissions(
	//             suffix: 'timesheet',
	//             addPerms: ['view']
	//         )
	//     );

	//     // 23) Ledger report
	//     $ledgerPermissions = collect(self::variatePerm('ledger##report'))
	//         ->map(fn ($name) => ['name' => $name] + $base)
	//         ->all();

	//     // 24) Trial balance report
	//     $balancePermissions = collect(self::variatePerm('trial balance##report'))
	//         ->map(fn ($name) => ['name' => $name] + $base)
	//         ->all();

	//     // 25) Client + client dashboard
	//     $clientPermissions = array_merge(
	//         self::defaultedPermissions('client'),
	//         self::defaultedPermissions(
	//             suffix: 'client##dashboard',
	//             removePerms: ['create', 'edit', 'delete']
	//         )
	//     );

	//     // 26) Lead, lead call, lead email
	//     $leadPermissions = array_merge(
	//         self::defaultedPermissions(
	//             suffix: 'lead',
	//             addPerms: ['view', 'move']
	//         ),
	//         self::defaultedPermissions(
	//             suffix: 'lead##call',
	//             removePerms: ['manage']
	//         ),
	//         self::defaultedPermissions(
	//             suffix: 'lead##email',
	//             removePerms: ['manage', 'edit', 'delete']
	//         )
	//     );

	//     // 27) Stage
	//     $stagePermissions = self::defaultedPermissions('stage');

	//     // 28) Employee + profile
	//     $employeePermissions = array_merge(
	//         self::defaultedPermissions(
	//             suffix: 'employee',
	//             addPerms: ['view']
	//         ),
	//         self::defaultedPermissions(
	//             suffix: 'employee##profile',
	//             addPerms: ['show'],
	//             removePerms: ['create', 'edit', 'delete']
	//         )
	//     );

	//     // 29) Department
	//     $departmentPermissions = self::defaultedPermissions(
	//         suffix: 'department',
	//         addPerms: ['view']
	//     );

	//     // 30) Designation
	//     $designationPermissions = self::defaultedPermissions(
	//         suffix: 'designation',
	//         addPerms: ['view']
	//     );

	//     // 31) Branch
	//     $branchPermissions = self::defaultedPermissions('branch');

	//     // 32) Document + document type
	//     $documentPermissions = array_merge(
	//         self::defaultedPermissions('document##type'),
	//         self::defaultedPermissions('document')
	//     );

	//     // 33) Payslip type + pay slip
	//     $payslipPermissions = array_merge(
	//         self::defaultedPermissions('payslip##type'),
	//         self::defaultedPermissions(
	//             suffix: 'pay##slip',
	//             addPerms: ['create'],
	//             removePerms: ['edit', 'delete']
	//         )
	//     );

	//     // 34) Allowance + allowance option
	//     $allowancePermissions = array_merge(
	//         self::defaultedPermissions(
	//             suffix: 'allowance',
	//             removePerms: ['manage']
	//         ),
	//         self::defaultedPermissions('allowance##option')
	//     );

	//     // 35) Commission
	//     $commissionPermissions = self::defaultedPermissions(
	//         suffix: 'commission',
	//         removePerms: ['manage']
	//     );

	//     // 36) Loan option + loan
	//     $loanPermissions = array_merge(
	//         self::defaultedPermissions('loan##option'),
	//         self::defaultedPermissions(
	//             suffix: 'loan',
	//             removePerms: ['manage']
	//         )
	//     );

	//     // 37) Deduction option + saturation deduction
	//     $deductionPermissions = array_merge(
	//         self::defaultedPermissions('deduction##option'),
	//         self::defaultedPermissions(
	//             suffix: 'saturation##deduction',
	//             removePerms: ['manage']
	//         )
	//     );

	//     // 38) Overtime (create/edit/delete)
	//     $overtimePermissions = self::defaultedPermissions(
	//         suffix: 'overtime',
	//         removePerms: ['manage']
	//     );

	//     // 39) Set salary (manage/edit/create)
	//     $salaryPermissions = self::defaultedPermissions(
	//         suffix: 'set##salary',
	//         removePerms: ['delete']
	//     );

	//     // 40) Company policy (manage/create/edit)
	//     $policyPermissions = self::defaultedPermissions(
	//         suffix: 'company##policy',
	//         removePerms: ['delete']
	//     );

	//     // 41) Appraisal (manage/create/edit/delete + show)
	//     $appraisalPermissions = self::defaultedPermissions(
	//         suffix: 'appraisal',
	//         addPerms: ['show']
	//     );

	//     // 42) Indicator (manage/create/edit/delete + show)
	//     $indicatorPermissions = self::defaultedPermissions(
	//         suffix: 'indicator',
	//         addPerms: ['show']
	//     );

	//     // 43) Training + training type
	//     $trainingPermissions = array_merge(
	//         self::defaultedPermissions(
	//             suffix: 'training',
	//             addPerms: ['show']
	//         ),
	//         self::defaultedPermissions('training##type')
	//     );

	//     // 44) Trainer
	//     $trainerPermissions = self::defaultedPermissions('trainer');

	//     // 45) Award + award type
	//     $awardPermissions = array_merge(
	//         self::defaultedPermissions('award'),
	//         self::defaultedPermissions('award##type')
	//     );

	//     // 46) Resignation
	//     $resignationPermissions = self::defaultedPermissions('resignation');

	//     // 47) Travel
	//     $travelPermissions = self::defaultedPermissions('travel');

	//     // 48) Promotion (add “view”)
	//     $promotionPermissions = self::defaultedPermissions(
	//         suffix: 'promotion',
	//         addPerms: ['view']
	//     );

	//     // 49) Complaint
	//     $complaintPermissions = self::defaultedPermissions('complaint');

	//     // 50) Warning
	//     $warningPermissions = self::defaultedPermissions('warning');

	//     // 51) Termination + termination type
	//     $terminationPermissions = array_merge(
	//         self::defaultedPermissions('termination'),
	//         self::defaultedPermissions('termination##type')
	//     );

	//     // 52) Job‐related
	//     $jobPermissions = array_merge(
	//         // job application (add “show” & “move”)
	//         self::defaultedPermissions(
	//             suffix: 'job##application',
	//             addPerms: ['show', 'move']
	//         ),
	//         // job application skill (only “add”)
	//         self::defaultedPermissions(
	//             suffix: 'job##application##skill',
	//             addPerms: ['add'],
	//             removePerms: ['manage', 'create', 'edit', 'delete']
	//         ),
	//         // job application note (only “add” & “delete”)
	//         self::defaultedPermissions(
	//             suffix: 'job##application##note',
	//             addPerms: ['add', 'delete'],
	//             removePerms: ['manage', 'create', 'edit']
	//         ),
	//         // on-board (only “manage”)
	//         self::defaultedPermissions(
	//             suffix: 'job##on##board',
	//             removePerms: ['create', 'edit', 'delete']
	//         ),
	//         // job category
	//         self::defaultedPermissions('job##category'),
	//         // job (add “show”)
	//         self::defaultedPermissions(
	//             suffix: 'job',
	//             addPerms: ['show']
	//         ),
	//         // job stage
	//         self::defaultedPermissions('job##stage')
	//     );

	//     // 53) Competencies
	//     $competenciesPermissions = self::defaultedPermissions('competencies');

	//     // 54) Custom question
	//     $questionPermissions = self::defaultedPermissions('custom##question');

	//     // 55) Interview schedule (create/edit/delete/show)
	//     $interviewPermissions = self::defaultedPermissions(
	//         suffix: 'interview##schedule',
	//         addPerms: ['show'],
	//         removePerms: ['manage']
	//     );

	//     // 56) Estimation (create/view/edit/delete)
	//     $estimationPermissions = self::defaultedPermissions(
	//         suffix: 'estimation',
	//         addPerms: ['view'],
	//         removePerms: ['manage']
	//     );

	//     // 57) Holiday (manage/create/edit/delete)
	//     $holidayPermissions = self::defaultedPermissions('holiday');

	//     // 58) Career (show only)
	//     $careerPermissions = collect(self::variatePerm('show##career'))
	//         ->map(fn ($name) => ['name' => $name, 'guard_name' => 'web'] + self::timestamps())
	//         ->all();

	//     // 59) Meeting
	//     $meetingPermissions = self::defaultedPermissions('meeting');

	//     // 60) Event
	//     $eventPermissions = self::defaultedPermissions('event');

	//     // 61) Transfer
	//     $transferPermissions = self::defaultedPermissions('transfer');

	//     // 62) Announcement (manage/create/edit)
	//     $announcementPermissions = self::defaultedPermissions(
	//         suffix: 'announcement',
	//         removePerms: ['delete']
	//     );

	//     // 63) Leave + leave type
	//     $leavePermissions = array_merge(
	//         self::defaultedPermissions('leave'),
	//         self::defaultedPermissions('leave##type')
	//     );

	//     // 64) Attendance
	//     $attendancePermissions = self::defaultedPermissions('attendance');

	//     // 65) Report (manage only)
	//     $reportPermissions = self::defaultedPermissions(
	//         suffix: 'report',
	//         removePerms: ['create', 'edit', 'delete']
	//     );

	//     // 66) Project, project stage, task, task stage
	//     $projectPermissions = array_merge(
	//         self::defaultedPermissions(
	//             suffix: 'project',
	//             addPerms: ['share']
	//         ),
	//         self::defaultedPermissions('project##stage'),
	//         self::defaultedPermissions(
	//             suffix: 'project##task',
	//             addPerms: ['view']
	//         ),
	//         self::defaultedPermissions('project##task##stage')
	//     );

	//     // 67) Milestone (create/edit/delete/view)
	//     $milestonePermissions = self::defaultedPermissions(
	//         suffix: 'milestone',
	//         addPerms: ['view'],
	//         removePerms: ['manage']
	//     );

	//     // 68) Activity & CRM activity (view only)
	//     $activityPermissions = array_merge(
	//         collect(self::variatePerm('view##activity'))
	//             ->map(fn ($name) => ['name' => $name, 'guard_name' => 'web'] + self::timestamps())
	//             ->all(),
	//         collect(self::variatePerm('view##CRM##activity'))
	//             ->map(fn ($name) => ['name' => $name, 'guard_name' => 'web'] + self::timestamps())
	//             ->all()
	//     );

	//     // 69) Bug report + status (add “move”)
	//     $bugPermissions = array_merge(
	//         self::defaultedPermissions(
	//             suffix: 'bug##report',
	//             addPerms: ['move']
	//         ),
	//         self::defaultedPermissions('bug##status')
	//     );

	//     // 70) Super admin dashboard (manage only)
	//     $superAdminPermissions = collect(self::variatePerm('manage##super##admin##dashboard'))
	//         ->map(fn ($name) => ['name' => $name, 'guard_name' => 'web'] + self::timestamps())
	//         ->all();

	//     // 71) Plan, company plan, buy plan
	//     $planPermissions = array_merge(
	//         // plan (manage/create/edit)
	//         self::defaultedPermissions(
	//             suffix: 'plan',
	//             removePerms: ['delete']
	//         ),
	//         // company plan (manage only)
	//         self::defaultedPermissions(
	//             suffix: 'company##plan',
	//             removePerms: ['create', 'edit', 'delete']
	//         ),
	//         // buy plan (buy only)
	//         self::defaultedPermissions(
	//             suffix: 'buy##plan',
	//             addPerms: ['buy'],
	//             removePerms: ['manage', 'create', 'edit', 'delete']
	//         )
	//     );

	//     // 72) Coupon
	//     $couponPermissions = self::defaultedPermissions('coupon');

	//     // 73) Form builder / field / response (view only)
	//     $formPermissions = array_merge(
	//         self::defaultedPermissions('form##builder'),
	//         self::defaultedPermissions('form##field'),
	//         self::defaultedPermissions(
	//             suffix: 'form##response',
	//             addPerms: ['view'],
	//             removePerms: ['manage', 'create', 'edit', 'delete']
	//         )
	//     );

	//     // 74) Performance type
	//     $performancePermissions = self::defaultedPermissions('performance##type');

	//     // 75) Budget plan (create/edit/manage/delete/view)
	//     $budgetPermissions = self::defaultedPermissions(
	//         suffix: 'budget##plan',
	//         addPerms: ['view']
	//     );

	//     // 76) Warehouse (add “show”)
	//     $warehousePermissions = self::defaultedPermissions(
	//         suffix: 'warehouse',
	//         addPerms: ['show']
	//     );

	//     // 77) Purchase + payment purchase
	//     $purchasePermissions = array_merge(
	//         self::defaultedPermissions(
	//             suffix: 'purchase',
	//             addPerms: ['send']
	//         ),
	//         self::defaultedPermissions(
	//             suffix: 'payment##purchase',
	//             addPerms: ['create', 'delete'],
	//             removePerms: ['manage', 'edit']
	//         )
	//     );

	//     // 78) POS (manage only)
	//     $posPermissions = self::defaultedPermissions(
	//         suffix: 'pos',
	//         removePerms: ['create', 'edit', 'delete']
	//     );

	//     // 79) Contract type + contract (add “show”)
	//     $contractPermissions = array_merge(
	//         self::defaultedPermissions('contract##type'),
	//         self::defaultedPermissions(
	//             suffix: 'contract',
	//             addPerms: ['show']
	//         )
	//     );

	//     // 80) Barcode (create only)
	//     $barcodePermissions = collect(self::variatePerm('create##barcode'))
	//         ->map(fn ($name) => ['name' => $name, 'guard_name' => 'web'] + self::timestamps())
	//         ->all();

	//     // 81) Webhook (create/edit/delete)
	//     $webhookPermissions = self::defaultedPermissions(
	//         suffix: 'webhook',
	//         removePerms: ['manage']
	//     );


	//     // FINAL MERGE
	//     return array_merge(
	//         $dashboardPermissions,
	//         $userPermissions,
	//         $rolePermissions,
	//         $permissionPermissions,
	//         $languagePermissions,
	//         $settingsPermissions,
	//         $invoicePermissions,
	//         $billPermissions,
	//         $expensePermissions,
	//         $constantPermissions,
	//         $productServicePermissions,
	//         $customerPermissions,
	//         $vendorPermissions,
	//         $bankAccountPermissions,
	//         $bankTransferPermissions,
	//         $transactionPermissions,
	//         $orderPermissions,
	//         $revenuePermissions,
	//         $paymentPermissions,
	//         $reportPermissions,
	//         $creditPermissions,
	//         $debitPermissions,
	//         $proposalPermissions,
	//         $goalPermissions,
	//         $assetsPermissions,
	//         $statementPermissions,
	//         $chartPermissions,
	//         $journalPermissions,
	//         $sheetPermissions,
	//         $ledgerPermissions,
	//         $balancePermissions,
	//         $clientPermissions,
	//         $leadPermissions,
	//         $stagePermissions,
	//         $employeePermissions,
	//         $departmentPermissions,
	//         $designationPermissions,
	//         $branchPermissions,
	//         $documentPermissions,
	//         $payslipPermissions,
	//         $allowancePermissions,
	//         $commissionPermissions,
	//         $loanPermissions,
	//         $deductionPermissions,
	//         $overtimePermissions,
	//         $salaryPermissions,
	//         $policyPermissions,
	//         $appraisalPermissions,
	//         $indicatorPermissions,
	//         $trainingPermissions,
	//         $trainerPermissions,
	//         $awardPermissions,
	//         $resignationPermissions,
	//         $travelPermissions,
	//         $promotionPermissions,
	//         $complaintPermissions,
	//         $warningPermissions,
	//         $terminationPermissions,
	//         $jobPermissions,
	//         $competenciesPermissions,
	//         $questionPermissions,
	//         $interviewPermissions,
	//         $estimationPermissions,
	//         $holidayPermissions,
	//         $careerPermissions,
	//         $meetingPermissions,
	//         $eventPermissions,
	//         $transferPermissions,
	//         $announcementPermissions,
	//         $leavePermissions,
	//         $attendancePermissions,
	//         $reportPermissions,
	//         $projectPermissions,
	//         $milestonePermissions,
	//         $activityPermissions,
	//         $bugPermissions,
	//         $superAdminPermissions,
	//         $planPermissions,
	//         $couponPermissions,
	//         $formPermissions,
	//         $performancePermissions,
	//         $budgetPermissions,
	//         $warehousePermissions,
	//         $purchasePermissions,
	//         $posPermissions,
	//         $contractPermissions,
	//         $barcodePermissions,
	//         $webhookPermissions
	//     );
	// }
}
