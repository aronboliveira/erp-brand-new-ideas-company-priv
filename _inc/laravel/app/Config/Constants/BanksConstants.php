<?php

namespace App\Config\Constants;

class BanksConstants
{
	public const COL_REL_USER = 'user_id';
	public const COL_REL_ID = 'account_id';
	public const COL_ACC_N = 'account_number';
	public const COL_ADR = 'bank_address';
	public const COL_NM = 'bank_name';
	public const COL_COA = 'chart_account_id';
	public const COL_SL_COA = 'sale_' . self::COL_COA;
	public const COL_EXP_COA = 'expense_' . self::COL_COA;
	public const COL_CT = 'contact_number';
	public const COL_HNM = 'holder_name';
	public const COL_OB = 'opening_balance';
	public const COL_TRF_DT = 'transfer_date';
	public const COL_HD_ID = 'holder_id';
	public const COL_HD_ADDR = 'holder_address';
	public const COL_BANK_IDF = 'bank_identifier';
	public const COL_RSK = 'risk_level';
	public const COL_HAS_CRD = 'has_credit_card';
	public const COL_HAS_DBT = 'has_debit_card';
	public const COL_AMT_STR = 'total_amount_stored';
	public const COL_AM_LK = 'total_amount_locked';
	public const COL_PIX_KEYS = 'pix_keys';
	public const COL_ACPT_PIX = 'accepts_pix';
	public const COL_CRD_CD = 'credit_cards';
	public const COL_DBT_CD = 'debit_cards';
	public const COL_ACPTS_CRD_CD = 'accepts_credit_cards';
	public const COL_ACPTS_DBT_CD = 'accepts_debit_cards';
	public const COL_HAS_PND_STT = 'has_pending_statements';
	public const COL_IS_VRT = 'is_virtual_account';
	public const COL_AG_N = 'agency_number';
	public const COL_AG_DG = 'agency_digit';
	public const COL_INT_PRV = 'integration_provider';
	public const COL_TRF_CD = 'transfer_code';
}
