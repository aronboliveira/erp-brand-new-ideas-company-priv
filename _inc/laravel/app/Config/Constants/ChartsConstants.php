<?php

namespace App\Config\Constants;

class ChartsConstants
{
	public const COL_NM = 'name';
	public const COL_CD = 'code';
	public const COL_TP = 'type';
	public const COL_TP_NM = 'type_name';
	public const COL_SUBTP = 'sub_type';
	public const COL_ENB = 'is_enabled';
	public const COL_DESC = 'description';
	public const TP_ASSETS =
	'e0f9ce71-8b28-4ddc-a5b3-682059eeec4d';
	public const TP_LIABILITIES =
	'0c191432-ccd6-4983-a6e7-7e549f8f8c33';
	public const TP_EQUITY =
	'26d0f823-aaab-42b7-b1bd-0fefafc9d149';
	public const TP_INCOME =
	'e24bb0d2-2bf6-4a1b-9cf0-33785d64724f';
	public const TP_COGS =
	'55cd3f05-101e-4b9c-b91f-54ead3d764b5';
	public const TP_EXPENSES =
	'50a13f60-7de7-4dab-a28b-49cb380dd4bc';
	public const ST_CURRENT_ASSET =
	'2f0ceea0-3679-4e45-a329-2a8d2e0fbc6f';
	public const ST_INVENTORY_ASSET =
	'7e824b2d-6669-4432-98a8-52ff844d2c97';
	public const ST_NONCURRENT_ASSET =
	'e33a3310-49be-4238-bf2c-3dc1f00a0818';
	public const ST_CURRENT_LIABILITIES =
	'ce083376-ffcf-43a6-b8b6-a96689b54e3d';
	public const ST_LONGTERM_LIABILITIES =
	'a9db3850-9eea-4959-aa7c-94deebb269d3';
	public const ST_SHARE_CAPITAL =
	'77d50938-8d21-42fd-926c-8436271c806e';
	public const ST_RETAINED_EARNINGS =
	'94acbe8f-d2dc-43b5-a93c-5c4c4b10ec06';
	public const ST_OWNERS_EQUITY =
	'f9ab3f73-0b61-4871-bcc8-d96e48ca262a';
	public const ST_SALES_REVENUE =
	'fd0fe5ad-6c19-4648-8bd4-df6eb4ac000b';
	public const ST_OTHER_REVENUE =
	'e020d6ee-cf7a-47fe-afca-bf0147093977';
	public const ST_COGS =
	'bbdc812a-9f24-49ba-b30a-e3bd337e1a0f';
	public const ST_PAYROLL_EXPENSES =
	'6b108221-4d46-4c37-ba05-a32370ef077e';
	public const ST_GA_EXPENSES =
	'd7035d47-c006-4781-b628-71e8ad631800';
	public const AST = 'assets';
	public const LBL = 'liabilities';
	public const EQT = 'equity';
	public const ICM = 'income';
	public const CGS = 'costs of goods sold';
	public const EXP = 'expenses';
	public const COA_TPS = [
		self::AST => 'Assets',
		self::LBL => 'Liabilities',
		self::EQT => 'Equity',
		self::ICM => 'Income',
		self::CGS => 'Costs of Goods Sold',
		self::EXP => 'Expenses',
	];
	public const COA_SBTPS = array(
		self::TP_ASSETS => array(
			self::ST_CURRENT_ASSET => 'Current Asset',
			self::ST_INVENTORY_ASSET => 'Inventory Asset',
			self::ST_NONCURRENT_ASSET => 'Non-current Asset',
		),
		self::TP_LIABILITIES => array(
			self::ST_CURRENT_LIABILITIES => 'Current Liabilities',
			self::ST_LONGTERM_LIABILITIES => 'Long Term Liabilities',
			self::ST_SHARE_CAPITAL => 'Share Capital',
			self::ST_RETAINED_EARNINGS => 'Retained Earnings',
		),
		self::TP_EQUITY => array(
			self::ST_OWNERS_EQUITY => 'Owners Equity',
		),
		self::TP_INCOME => array(
			self::ST_SALES_REVENUE => 'Sales Revenue',
			self::ST_OTHER_REVENUE => 'Other Revenue',
		),
		self::TP_COGS => array(
			self::ST_COGS => 'Costs of Goods Sold',
		),
		self::TP_EXPENSES => array(
			self::ST_PAYROLL_EXPENSES => 'Payroll Expenses',
			self::ST_GA_EXPENSES => 'General and Administrative expenses',
		),
	);
	public const COA_SETTINGS_0 = array(
		[
			'code' => '1060',
			'name' => 'Checking Account',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_CURRENT_ASSET,
		],
		[
			'code' => '1065',
			'name' => 'Petty Cash',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_CURRENT_ASSET,
		],
		[
			'code' => '1200',
			'name' => 'Account Receivables',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_CURRENT_ASSET,
		],
		[
			'code' => '1205',
			'name' => 'Allowance for doubtful accounts',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_CURRENT_ASSET,
		],
		[
			'code' => '1510',
			'name' => 'Inventory',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_INVENTORY_ASSET,
		],
		[
			'code' => '1520',
			'name' => 'Stock of Raw Materials',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_INVENTORY_ASSET,
		],
		[
			'code' => '1530',
			'name' => 'Stock of Work In Progress',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_INVENTORY_ASSET,
		],
		[
			'code' => '1540',
			'name' => 'Stock of Finished Goods',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_INVENTORY_ASSET,
		],
		[
			'code' => '1550',
			'name' => 'Goods Received Clearing account',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_INVENTORY_ASSET,
		],
		[
			'code' => '1810',
			'name' => 'Land and Buildings',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_NONCURRENT_ASSET,
		],
		[
			'code' => '1820',
			'name' => 'Office Furniture and Equipement',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_NONCURRENT_ASSET,
		],
		[
			'code' => '1825',
			'name' => 'Accum.depreciation-Furn. and Equip',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_NONCURRENT_ASSET,
		],
		[
			'code' => '1840',
			'name' => 'Motor Vehicle',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_NONCURRENT_ASSET,
		],
		[
			'code' => '1845',
			'name' => 'Accum.depreciation-Motor Vehicle',
			'type' => self::TP_ASSETS,
			'sub_type' => self::ST_NONCURRENT_ASSET,
		],
		[
			'code' => '2100',
			'name' => 'Account Payable',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2105',
			'name' => 'Deferred Income',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2110',
			'name' => 'Accrued Income Tax-Central',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2120',
			'name' => 'Income Tax Payable',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2130',
			'name' => 'Accrued Franchise Tax',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2140',
			'name' => 'Vat Provision',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2145',
			'name' => 'Purchase Tax',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		], [
			'code' => '2150',
			'name' => 'VAT Pay / Refund',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2151',
			'name' => 'Zero Rated',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2152',
			'name' => 'Capital import',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2153',
			'name' => 'Standard Import',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2154',
			'name' => 'Capital Standard',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2155',
			'name' => 'Vat Exempt',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2160',
			'name' => 'Accrued Use Tax Payable',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2210',
			'name' => 'Accrued Wages',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2220',
			'name' => 'Accrued Comp Time',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2230',
			'name' => 'Accrued Holiday Pay',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2240',
			'name' => 'Accrued Vacation Pay',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2310',
			'name' => 'Accr. Benefits - Central Provident Fund',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		], [
			'code' => '2320',
			'name' => 'Accr. Benefits - Stock Purchase',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2330',
			'name' => 'Accr. Benefits - Med, Den',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2340',
			'name' => 'Accr. Benefits - Payroll Taxes',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2350',
			'name' => 'Accr. Benefits - Credit Union',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2360',
			'name' => 'Accr. Benefits - Savings Bond',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2370',
			'name' => 'Accr. Benefits - Group Insurance',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2380',
			'name' => 'Accr. Benefits - Charity Cont.',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_CURRENT_LIABILITIES,
		],
		[
			'code' => '2620',
			'name' => 'Bank Loans',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_LONGTERM_LIABILITIES,
		],
		[
			'code' => '2680',
			'name' => 'Loans from Shareholders',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_LONGTERM_LIABILITIES,
		],
		[
			'code' => '3350',
			'name' => 'Common Shares',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_SHARE_CAPITAL,
		],
		[
			'code' => '3590',
			'name' => 'Reserves and Surplus',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_RETAINED_EARNINGS,
		],
		[
			'code' => '3595',
			'name' => 'Owners Drawings',
			'type' => self::TP_LIABILITIES,
			'sub_type' => self::ST_RETAINED_EARNINGS,
		],
		[
			'code' => '3020',
			'name' => 'Opening Balances and adjustments',
			'type' => self::TP_EQUITY,
			'sub_type' => self::ST_OWNERS_EQUITY,
		],
		[
			'code' => '3025',
			'name' => 'Owners Contribution',
			'type' => self::TP_EQUITY,
			'sub_type' => self::ST_OWNERS_EQUITY,
		],
		[
			'code' => '3030',
			'name' => 'Profit and Loss ( current Year)',
			'type' => self::TP_EQUITY,
			'sub_type' => self::ST_OWNERS_EQUITY,
		],
		[
			'code' => '3035',
			'name' => 'Retained income',
			'type' => self::TP_EQUITY,
			'sub_type' => self::ST_OWNERS_EQUITY,
		],
		[
			'code' => '4010',
			'name' => 'Sales Income',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_SALES_REVENUE,
		],
		[
			'code' => '4020',
			'name' => 'Service Income',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_SALES_REVENUE,
		],
		[
			'code' => '4430',
			'name' => 'Shipping and Handling',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_OTHER_REVENUE,
		],
		[
			'code' => '4435',
			'name' => 'Sundry Income',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_OTHER_REVENUE,
		],
		[
			'code' => '4440',
			'name' => 'Interest Received',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_OTHER_REVENUE,
		],
		[
			'code' => '4450',
			'name' => 'Foreign Exchange Gain',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_OTHER_REVENUE,
		],
		[
			'code' => '4500',
			'name' => 'Unallocated Income',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_OTHER_REVENUE,
		],
		[
			'code' => '4510',
			'name' => 'Discounts Received',
			'type' => self::TP_INCOME,
			'sub_type' => self::ST_OTHER_REVENUE,
		],
		[
			'code' => '5005',
			'name' => 'Cost of Sales- On Services',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5010',
			'name' => 'Cost of Sales - Purchases',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5015',
			'name' => 'Operating Costs',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5020',
			'name' => 'Material Usage Varaiance',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5025',
			'name' => 'Breakage and Replacement Costs',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5030',
			'name' => 'Consumable Materials',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5035',
			'name' => 'Sub-contractor Costs',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5040',
			'name' => 'Purchase Price Variance',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5045',
			'name' => 'Direct Labour - COS',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5050',
			'name' => 'Purchases of Materials',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5060',
			'name' => 'Discounts Received',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5100',
			'name' => 'Freight Costs',
			'type' => self::TP_COGS,
			'sub_type' => self::ST_COGS,
		],
		[
			'code' => '5410',
			'name' => 'Salaries and Wages',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5415',
			'name' => 'Directors Fees & Remuneration',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5420',
			'name' => 'Wages - Overtime',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5425',
			'name' => 'Members Salaries',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5430',
			'name' => 'UIF Payments',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5440',
			'name' => 'Payroll Taxes',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5450',
			'name' => 'Workers Compensation ( Coida )',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5460',
			'name' => 'Normal Taxation Paid',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5470',
			'name' => 'General Benefits',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5510',
			'name' => 'Provisional Tax Paid',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5520',
			'name' => 'Inc Tax Exp - State',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5530',
			'name' => 'Taxes - Real Estate',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5540',
			'name' => 'Taxes - Personal Property',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5550',
			'name' => 'Taxes - Franchise',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5560',
			'name' => 'Taxes - Foreign Withholding',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_PAYROLL_EXPENSES,
		],
		[
			'code' => '5610',
			'name' => 'Accounting Fees',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5615',
			'name' => 'Advertising and Promotions',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5620',
			'name' => 'Bad Debts',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5625',
			'name' => 'Courier and Postage',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5660',
			'name' => 'Depreciation Expense',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5685',
			'name' => 'Insurance Expense',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5690',
			'name' => 'Bank Charges',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5695',
			'name' => 'Interest Paid',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5700',
			'name' => 'Office Expenses - Consumables',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5705',
			'name' => 'Printing and Stationary',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5710',
			'name' => 'Security Expenses',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5715',
			'name' => 'Subscription - Membership Fees',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5755',
			'name' => 'Electricity, Gas and Water',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5760',
			'name' => 'Rent Paid',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5765',
			'name' => 'Repairs and Maintenance',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5770',
			'name' => 'Motor Vehicle Expenses',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5771',
			'name' => 'Petrol and Oil',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5775',
			'name' => 'Equipment Hire - Rental',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5780',
			'name' => 'Telephone and Internet',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5785',
			'name' => 'Travel and Accommodation',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5786',
			'name' => 'Meals and Entertainment',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5787',
			'name' => 'Staff Training',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5790',
			'name' => 'Utilities',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5791',
			'name' => 'Computer Expenses',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5795',
			'name' => 'Registrations',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5800',
			'name' => 'Licenses',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '5810',
			'name' => 'Foreign Exchange Loss',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
		[
			'code' => '9990',
			'name' => 'Profit and Loss',
			'type' => self::TP_EXPENSES,
			'sub_type' => self::ST_GA_EXPENSES,
		],
	);
	public const COA_SETTINGS_1 = array(
		[
			'code' => '1060',
			'name' => 'Checking Account',
			'type' => 'Assets',
			'sub_type' => 'Current Asset',
		],
		[
			'code' => '1065',
			'name' => 'Petty Cash',
			'type' => 'Assets',
			'sub_type' => 'Current Asset',
		],
		[
			'code' => '1200',
			'name' => 'Account Receivables',
			'type' => 'Assets',
			'sub_type' => 'Current Asset',
		],
		[
			'code' => '1205',
			'name' => 'Allowance for doubtful accounts',
			'type' => 'Assets',
			'sub_type' => 'Current Asset',
		],
		[
			'code' => '1510',
			'name' => 'Inventory',
			'type' => 'Assets',
			'sub_type' => 'Inventory Asset',
		],
		[
			'code' => '1520',
			'name' => 'Stock of Raw Materials',
			'type' => 'Assets',
			'sub_type' => 'Inventory Asset',
		],
		[
			'code' => '1530',
			'name' => 'Stock of Work In Progress',
			'type' => 'Assets',
			'sub_type' => 'Inventory Asset',
		],
		[
			'code' => '1540',
			'name' => 'Stock of Finished Goods',
			'type' => 'Assets',
			'sub_type' => 'Inventory Asset',
		],
		[
			'code' => '1550',
			'name' => 'Goods Received Clearing account',
			'type' => 'Assets',
			'sub_type' => 'Inventory Asset',
		],
		[
			'code' => '1810',
			'name' => 'Land and Buildings',
			'type' => 'Assets',
			'sub_type' => 'Non-current Asset',
		],
		[
			'code' => '1820',
			'name' => 'Office Furniture and Equipement',
			'type' => 'Assets',
			'sub_type' => 'Non-current Asset',
		],
		[
			'code' => '1825',
			'name' => 'Accum.depreciation-Furn. and Equip',
			'type' => 'Assets',
			'sub_type' => 'Non-current Asset',
		],
		[
			'code' => '1840',
			'name' => 'Motor Vehicle',
			'type' => 'Assets',
			'sub_type' => 'Non-current Asset',
		],
		[
			'code' => '1845',
			'name' => 'Accum.depreciation-Motor Vehicle',
			'type' => 'Assets',
			'sub_type' => 'Non-current Asset',
		],
		[
			'code' => '2100',
			'name' => 'Account Payable',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2105',
			'name' => 'Deferred Income',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2110',
			'name' => 'Accrued Income Tax-Central',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2120',
			'name' => 'Income Tax Payable',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2130',
			'name' => 'Accrued Franchise Tax',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2140',
			'name' => 'Vat Provision',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2145',
			'name' => 'Purchase Tax',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		], [
			'code' => '2150',
			'name' => 'VAT Pay / Refund',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2151',
			'name' => 'Zero Rated',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2152',
			'name' => 'Capital import',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2153',
			'name' => 'Standard Import',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2154',
			'name' => 'Capital Standard',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2155',
			'name' => 'Vat Exempt',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2160',
			'name' => 'Accrued Use Tax Payable',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2210',
			'name' => 'Accrued Wages',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2220',
			'name' => 'Accrued Comp Time',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2230',
			'name' => 'Accrued Holiday Pay',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2240',
			'name' => 'Accrued Vacation Pay',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2310',
			'name' => 'Accr. Benefits - Central Provident Fund',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		], [
			'code' => '2320',
			'name' => 'Accr. Benefits - Stock Purchase',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2330',
			'name' => 'Accr. Benefits - Med, Den',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2340',
			'name' => 'Accr. Benefits - Payroll Taxes',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2350',
			'name' => 'Accr. Benefits - Credit Union',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2360',
			'name' => 'Accr. Benefits - Savings Bond',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2370',
			'name' => 'Accr. Benefits - Group Insurance',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2380',
			'name' => 'Accr. Benefits - Charity Cont.',
			'type' => 'Liabilities',
			'sub_type' => 'Current Liabilities',
		],
		[
			'code' => '2620',
			'name' => 'Bank Loans',
			'type' => 'Liabilities',
			'sub_type' => 'Long Term Liabilities',
		],
		[
			'code' => '2680',
			'name' => 'Loans from Shareholders',
			'type' => 'Liabilities',
			'sub_type' => 'Long Term Liabilities',
		],
		[
			'code' => '3350',
			'name' => 'Common Shares',
			'type' => 'Liabilities',
			'sub_type' => 'Share Capital',
		],
		[
			'code' => '3590',
			'name' => 'Reserves and Surplus',
			'type' => 'Liabilities',
			'sub_type' => 'Retained Earnings',
		],
		[
			'code' => '3595',
			'name' => 'Owners Drawings',
			'type' => 'Liabilities',
			'sub_type' => 'Retained Earnings',
		],
		[
			'code' => '3020',
			'name' => 'Opening Balances and adjustments',
			'type' => 'Equity',
			'sub_type' => 'Owners Equity',
		],
		[
			'code' => '3025',
			'name' => 'Owners Contribution',
			'type' => 'Equity',
			'sub_type' => 'Owners Equity',
		],
		[
			'code' => '3030',
			'name' => 'Profit and Loss ( current Year)',
			'type' => 'Equity',
			'sub_type' => 'Owners Equity',
		],
		[
			'code' => '3035',
			'name' => 'Retained income',
			'type' => 'Equity',
			'sub_type' => 'Owners Equity',
		],
		[
			'code' => '4010',
			'name' => 'Sales Income',
			'type' => 'Income',
			'sub_type' => 'Sales Revenue',
		],
		[
			'code' => '4020',
			'name' => 'Service Income',
			'type' => 'Income',
			'sub_type' => 'Sales Revenue',
		],
		[
			'code' => '4430',
			'name' => 'Shipping and Handling',
			'type' => 'Income',
			'sub_type' => 'Other Revenue',
		],
		[
			'code' => '4435',
			'name' => 'Sundry Income',
			'type' => 'Income',
			'sub_type' => 'Other Revenue',
		],
		[
			'code' => '4440',
			'name' => 'Interest Received',
			'type' => 'Income',
			'sub_type' => 'Other Revenue',
		],
		[
			'code' => '4450',
			'name' => 'Foreign Exchange Gain',
			'type' => 'Income',
			'sub_type' => 'Other Revenue',
		],
		[
			'code' => '4500',
			'name' => 'Unallocated Income',
			'type' => 'Income',
			'sub_type' => 'Other Revenue',
		],
		[
			'code' => '4510',
			'name' => 'Discounts Received',
			'type' => 'Income',
			'sub_type' => 'Other Revenue',
		],
		[
			'code' => '5005',
			'name' => 'Cost of Sales- On Services',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5010',
			'name' => 'Cost of Sales - Purchases',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5015',
			'name' => 'Operating Costs',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5020',
			'name' => 'Material Usage Varaiance',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5025',
			'name' => 'Breakage and Replacement Costs',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5030',
			'name' => 'Consumable Materials',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5035',
			'name' => 'Sub-contractor Costs',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5040',
			'name' => 'Purchase Price Variance',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5045',
			'name' => 'Direct Labour - COS',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5050',
			'name' => 'Purchases of Materials',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5060',
			'name' => 'Discounts Received',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5100',
			'name' => 'Freight Costs',
			'type' => 'Costs of Goods Sold',
			'sub_type' => 'Costs of Goods Sold',
		],
		[
			'code' => '5410',
			'name' => 'Salaries and Wages',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5415',
			'name' => 'Directors Fees & Remuneration',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5420',
			'name' => 'Wages - Overtime',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5425',
			'name' => 'Members Salaries',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5430',
			'name' => 'UIF Payments',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5440',
			'name' => 'Payroll Taxes',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5450',
			'name' => 'Workers Compensation ( Coida )',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5460',
			'name' => 'Normal Taxation Paid',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5470',
			'name' => 'General Benefits',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5510',
			'name' => 'Provisional Tax Paid',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5520',
			'name' => 'Inc Tax Exp - State',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5530',
			'name' => 'Taxes - Real Estate',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5540',
			'name' => 'Taxes - Personal Property',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5550',
			'name' => 'Taxes - Franchise',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5560',
			'name' => 'Taxes - Foreign Withholding',
			'type' => 'Expenses',
			'sub_type' => 'Payroll Expenses',
		],
		[
			'code' => '5610',
			'name' => 'Accounting Fees',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5615',
			'name' => 'Advertising and Promotions',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5620',
			'name' => 'Bad Debts',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5625',
			'name' => 'Courier and Postage',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5660',
			'name' => 'Depreciation Expense',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5685',
			'name' => 'Insurance Expense',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5690',
			'name' => 'Bank Charges',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5695',
			'name' => 'Interest Paid',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5700',
			'name' => 'Office Expenses - Consumables',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5705',
			'name' => 'Printing and Stationary',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5710',
			'name' => 'Security Expenses',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5715',
			'name' => 'Subscription - Membership Fees',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5755',
			'name' => 'Electricity, Gas and Water',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5760',
			'name' => 'Rent Paid',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5765',
			'name' => 'Repairs and Maintenance',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5770',
			'name' => 'Motor Vehicle Expenses',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5771',
			'name' => 'Petrol and Oil',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5775',
			'name' => 'Equipment Hire - Rental',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5780',
			'name' => 'Telephone and Internet',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5785',
			'name' => 'Travel and Accommodation',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5786',
			'name' => 'Meals and Entertainment',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5787',
			'name' => 'Staff Training',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5790',
			'name' => 'Utilities',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5791',
			'name' => 'Computer Expenses',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5795',
			'name' => 'Registrations',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5800',
			'name' => 'Licenses',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '5810',
			'name' => 'Foreign Exchange Loss',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],
		[
			'code' => '9990',
			'name' => 'Profit and Loss',
			'type' => 'Expenses',
			'sub_type' => 'General and Administrative expenses',
		],

	);
}
