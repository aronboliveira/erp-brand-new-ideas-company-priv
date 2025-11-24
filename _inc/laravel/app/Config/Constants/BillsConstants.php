<?php

namespace App\Config\Constants;

class BillsConstants
{
	public const COL_PRC_TMP = 'purchase_template';
	public const COL_INV_TMP = 'invoice_template';
	public const COL_PPS_TMP = 'proposal_template';
	public const COL_BIL_TMP = 'bill_template';
	public const COL_POS_TMP = 'pos_template';
	public const COL_PAY_SLP_NM = 'name';
	public const COL_TAX_NM = 'name';
	public const COL_TAX_RT = 'rate';
	public const COL_MIN_AMT = 'min_amount';
	public const COL_MAX_AMT = 'max_amount';
	public const COL_RL_APL = 'roles_applicable';
	public const COL_EXP_BDG = 'expected_budget';
	public const COL_MAX_BDG = 'maximum_budget';
	public const COL_VLD_TO = 'valid_to';
	public const COL_VLD_FRM = 'valid_from';
	public const COL_MIN_ITM = 'min_installments';
	public const COL_MAX_ITM = 'max_installments';
	public const COL_FGTS_PCT = 'fgts_guarantee_percentage';
	public const COL_SVR_GRT = 'allows_severance_guarantee';
	public const COL_RNGT = 'allows_renegotiation';
	public const COL_GRC_PRD_DYS = 'grace_period_days';
	public const COL_TC = 'terms_and_conditions';
	public const COL_DD_TYPE = 'deduction_type';
	public const COL_CCL_BS = 'calculation_basis';
	public const COL_MIN_PCT = 'minimum_percentage';
	public const COL_MAX_PCT = 'maximum_percentage';
	public const COL_MDAY_LMT = 'month_day_limit';
	public const COL_ALW_OPT = 'allowance_option';
	public const COL_LN_OPT = 'loan_option';
	public const COL_DD_OPT = 'deduction_option';
	public const COL_MIN_V = 'minimum_value';
	public const COL_MAX_V = 'maximum_value';
	public const COL_MIN_M = 'minimum_months';
	public const COL_MAX_M = 'maximum_months';
	public const COL_DEF_TRMC = 'defines_termination_conditions';
	public const COL_ST_DD = 'saturation_deduction';
	public const COL_OT_PAY = 'other_payment';
	public const COL_G_SLR = 'gross_salary';
	public const COL_N_SLR = 'net_salary';
	public const COL_NET_PAYABLE = 'net_payable';
	public const COL_SLR_M = 'salary_month';
	public const COL_P_DAY = 'pay_day';
	public const COL_BS_PRC = 'base_price';
	public const COL_CUR_ID = 'currency_id';
	public const COL_TX_N = 'tax_number';
	public const COL_BL_NAME = 'bill_name';
	public const COL_BL_EMAIL = 'bill_email';
	public const COL_BL_ADR = 'bill_address';
	public const COL_BL_TEL = 'bill_phone';
	public const COL_BL_ZIP = 'bill_zip';
	public const COL_BL_CTY = 'bill_city';
	public const COL_BL_ST = 'bill_state';
	public const COL_BL_CTR = 'bill_country';
	public const COL_SHIP_NAME = 'shipping_name';
	public const COL_SHIP_EMAIL = 'shipping_email';
	public const COL_SHIP_ADR = 'shipping_address';
	public const COL_SHIP_TEL = 'shipping_phone';
	public const COL_SHIP_ZIP = 'shipping_zip';
	public const COL_SHIP_CTY = 'shipping_city';
	public const COL_SHIP_ST = 'shipping_state';
	public const COL_SHIP_CTR = 'shipping_country';
	public const COL_CST_ID = 'customer_id';
	public const COL_OT_TX_ID = 'other_taxes_ids';
	public const COL_OD_C = 'order_counter';
	public const COL_IS_PRM = 'is_premium';

	// * VALORES

	public const VL_GRS_SL = 'gross_salary';
	public const VL_NET_SL = 'net_salary';
	public const VL_SPC_AMT = 'specific_amount';
	public const VL_PRG_TBL = 'progressive_table';
	public const VL_CTRB_SL = 'contribution_salary';
}
