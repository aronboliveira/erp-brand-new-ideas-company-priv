
from django.db import transaction
from django.db.models import Q
from django.core.mail import send_mail, EmailMessage
from django.conf import settings as django_settings
from django.core.exceptions import ValidationError
from django.utils.translation import gettext as _
from django.db import connection
import os
from ..activity.lead_stage import LeadStage
from ..activity.source import Source
from ..activity.stage import Stage
from ..bugs.bug_status import BugStatus
from ..charts.chart_of_account import ChartOfAccount
from ..charts.chart_of_account_sub_type import ChartOfAccountSubType
from ..charts.chart_of_account_type import ChartOfAccountType
from ..configs.pipeline import Pipeline
from ..contact.email_template import EmailTemplate
from ..contact.email_template_lang import EmailTemplateLang
from ..contact.user_email_template import UserEmailTemplate
from ..individuals.employee import Employee
from ..individuals.job_stage import JobStage
from ..individuals.user import User
from ..planning.task_stage import TaskStage
from ..shapes.label import Label
from ..shapes.language import Language
import re
from datetime import datetime
import shutil

# Static dictionaries/lists
chart_of_account_type = {
    'assets': 'Assets',
    'liabilities': 'Liabilities',
    'equity': 'Equity',
    'income': 'Income',
    'costs of goods sold': 'Costs of Goods Sold',
    'expenses': 'Expenses',
}

chart_of_account = {
    '1060': {'code': '1060', 'name': 'Checking Account', 'type': 1, 'sub_type': 1},
    '1065': {'code': '1065', 'name': 'Petty Cash', 'type': 1, 'sub_type': 1},
    '1200': {'code': '1200', 'name': 'Account Receivables', 'type': 1, 'sub_type': 1},
    '1205': {'code': '1205', 'name': 'Allowance for doubtful accounts', 'type': 1, 'sub_type': 1},
    '1510': {'code': '1510', 'name': 'Inventory', 'type': 1, 'sub_type': 2},
    '1520': {'code': '1520', 'name': 'Stock of Raw Materials', 'type': 1, 'sub_type': 2},
    '1530': {'code': '1530', 'name': 'Stock of Work In Progress', 'type': 1, 'sub_type': 2},
    '1540': {'code': '1540', 'name': 'Stock of Finished Goods', 'type': 1, 'sub_type': 2},
    '1550': {'code': '1550', 'name': 'Goods Received Clearing account', 'type': 1, 'sub_type': 2},
    '1810': {'code': '1810', 'name': 'Land and Buildings', 'type': 1, 'sub_type': 3},
    '1820': {'code': '1820', 'name': 'Office Furniture and Equipement', 'type': 1, 'sub_type': 3},
    '1825': {'code': '1825', 'name': 'Accum.depreciation-Furn. and Equip', 'type': 1, 'sub_type': 3},
    '1840': {'code': '1840', 'name': 'Motor Vehicle', 'type': 1, 'sub_type': 3},
    '1845': {'code': '1845', 'name': 'Accum.depreciation-Motor Vehicle', 'type': 1, 'sub_type': 3},
    '2100': {'code': '2100', 'name': 'Account Payable', 'type': 2, 'sub_type': 4},
    '2105': {'code': '2105', 'name': 'Deferred Income', 'type': 2, 'sub_type': 4},
    '2110': {'code': '2110', 'name': 'Accrued Income Tax-Central', 'type': 2, 'sub_type': 4},
    '2120': {'code': '2120', 'name': 'Income Tax Payable', 'type': 2, 'sub_type': 4},
    '2130': {'code': '2130', 'name': 'Accrued Franchise Tax', 'type': 2, 'sub_type': 4},
    '2140': {'code': '2140', 'name': 'Vat Provision', 'type': 2, 'sub_type': 4},
    '2145': {'code': '2145', 'name': 'Purchase Tax', 'type': 2, 'sub_type': 4},
    '2150': {'code': '2150', 'name': 'VAT Pay / Refund', 'type': 2, 'sub_type': 4},
    '2151': {'code': '2151', 'name': 'Zero Rated', 'type': 2, 'sub_type': 4},
    '2152': {'code': '2152', 'name': 'Capital import', 'type': 2, 'sub_type': 4},
    '2153': {'code': '2153', 'name': 'Standard Import', 'type': 2, 'sub_type': 4},
    '2154': {'code': '2154', 'name': 'Capital Standard', 'type': 2, 'sub_type': 4},
    '2155': {'code': '2155', 'name': 'Vat Exempt', 'type': 2, 'sub_type': 4},
    '2160': {'code': '2160', 'name': 'Accrued Use Tax Payable', 'type': 2, 'sub_type': 4},
    '2210': {'code': '2210', 'name': 'Accrued Wages', 'type': 2, 'sub_type': 4},
    '2220': {'code': '2220', 'name': 'Accrued Comp Time', 'type': 2, 'sub_type': 4},
    '2230': {'code': '2230', 'name': 'Accrued Holiday Pay', 'type': 2, 'sub_type': 4},
    '2240': {'code': '2240', 'name': 'Accrued Vacation Pay', 'type': 2, 'sub_type': 4},
    '2310': {'code': '2310', 'name': 'Accr. Benefits - Central Provident Fund', 'type': 2, 'sub_type': 4},
    '2320': {'code': '2320', 'name': 'Accr. Benefits - Stock Purchase', 'type': 2, 'sub_type': 4},
    '2330': {'code': '2330', 'name': 'Accr. Benefits - Med, Den', 'type': 2, 'sub_type': 4},
    '2340': {'code': '2340', 'name': 'Accr. Benefits - Payroll Taxes', 'type': 2, 'sub_type': 4},
    '2350': {'code': '2350', 'name': 'Accr. Benefits - Credit Union', 'type': 2, 'sub_type': 4},
    '2360': {'code': '2360', 'name': 'Accr. Benefits - Savings Bond', 'type': 2, 'sub_type': 4},
    '2370': {'code': '2370', 'name': 'Accr. Benefits - Group Insurance', 'type': 2, 'sub_type': 4},
    '2380': {'code': '2380', 'name': 'Accr. Benefits - Charity Cont.', 'type': 2, 'sub_type': 4},
    '2620': {'code': '2620', 'name': 'Bank Loans', 'type': 2, 'sub_type': 5},
    '2680': {'code': '2680', 'name': 'Loans from Shareholders', 'type': 2, 'sub_type': 5},
    '3350': {'code': '3350', 'name': 'Common Shares', 'type': 2, 'sub_type': 6},
    '3590': {'code': '3590', 'name': 'Reserves and Surplus', 'type': 2, 'sub_type': 7},
    '3595': {'code': '3595', 'name': 'Owners Drawings', 'type': 2, 'sub_type': 7},
    '3020': {'code': '3020', 'name': 'Opening Balances and adjustments', 'type': 3, 'sub_type': 8},
    '3025': {'code': '3025', 'name': 'Owners Contribution', 'type': 3, 'sub_type': 8},
    '3030': {'code': '3030', 'name': 'Profit and Loss ( current Year)', 'type': 3, 'sub_type': 8},
    '3035': {'code': '3035', 'name': 'Retained income', 'type': 3, 'sub_type': 8},
    '4010': {'code': '4010', 'name': 'Sales Income', 'type': 4, 'sub_type': 9},
    '4020': {'code': '4020', 'name': 'Service Income', 'type': 4, 'sub_type': 9},
    '4430': {'code': '4430', 'name': 'Shipping and Handling', 'type': 4, 'sub_type': 10},
    '4435': {'code': '4435', 'name': 'Sundry Income', 'type': 4, 'sub_type': 10},
    '4440': {'code': '4440', 'name': 'Interest Received', 'type': 4, 'sub_type': 10},
    '4450': {'code': '4450', 'name': 'Foreign Exchange Gain', 'type': 4, 'sub_type': 10},
    '4500': {'code': '4500', 'name': 'Unallocated Income', 'type': 4, 'sub_type': 10},
    '4510': {'code': '4510', 'name': 'Discounts Received', 'type': 4, 'sub_type': 10},
    '5005': {'code': '5005', 'name': 'Cost of Sales- On Services', 'type': 5, 'sub_type': 11},
    '5010': {'code': '5010', 'name': 'Cost of Sales - Purchases', 'type': 5, 'sub_type': 11},
    '5015': {'code': '5015', 'name': 'Operating Costs', 'type': 5, 'sub_type': 11},
    '5020': {'code': '5020', 'name': 'Material Usage Varaiance', 'type': 5, 'sub_type': 11},
    '5025': {'code': '5025', 'name': 'Breakage and Replacement Costs', 'type': 5, 'sub_type': 11},
    '5030': {'code': '5030', 'name': 'Consumable Materials', 'type': 5, 'sub_type': 11},
    '5035': {'code': '5035', 'name': 'Sub-contractor Costs', 'type': 5, 'sub_type': 11},
    '5040': {'code': '5040', 'name': 'Purchase Price Variance', 'type': 5, 'sub_type': 11},
    '5045': {'code': '5045', 'name': 'Direct Labour - COS', 'type': 5, 'sub_type': 11},
    '5050': {'code': '5050', 'name': 'Purchases of Materials', 'type': 5, 'sub_type': 11},
    '5060': {'code': '5060', 'name': 'Discounts Received', 'type': 5, 'sub_type': 11},
    '5100': {'code': '5100', 'name': 'Freight Costs', 'type': 5, 'sub_type': 11},
    '5410': {'code': '5410', 'name': 'Salaries and Wages', 'type': 6, 'sub_type': 12},
    '5415': {'code': '5415', 'name': 'Directors Fees & Remuneration', 'type': 6, 'sub_type': 12},
    '5420': {'code': '5420', 'name': 'Wages - Overtime', 'type': 6, 'sub_type': 12},
    '5425': {'code': '5425', 'name': 'Members Salaries', 'type': 6, 'sub_type': 12},
    '5430': {'code': '5430', 'name': 'UIF Payments', 'type': 6, 'sub_type': 12},
    '5440': {'code': '5440', 'name': 'Payroll Taxes', 'type': 6, 'sub_type': 12},
    '5450': {'code': '5450', 'name': 'Workers Compensation ( Coida )', 'type': 6, 'sub_type': 12},
    '5460': {'code': '5460', 'name': 'Normal Taxation Paid', 'type': 6, 'sub_type': 12},
    '5470': {'code': '5470', 'name': 'General Benefits', 'type': 6, 'sub_type': 12},
    '5510': {'code': '5510', 'name': 'Provisional Tax Paid', 'type': 6, 'sub_type': 12},
    '5520': {'code': '5520', 'name': 'Inc Tax Exp - State', 'type': 6, 'sub_type': 12},
    '5530': {'code': '5530', 'name': 'Taxes - Real Estate', 'type': 6, 'sub_type': 12},
    '5540': {'code': '5540', 'name': 'Taxes - Personal Property', 'type': 6, 'sub_type': 12},
    '5550': {'code': '5550', 'name': 'Taxes - Franchise', 'type': 6, 'sub_type': 12},
    '5560': {'code': '5560', 'name': 'Taxes - Foreign Withholding', 'type': 6, 'sub_type': 12},
    '5610': {'code': '5610', 'name': 'Accounting Fees', 'type': 6, 'sub_type': 13},
    '5615': {'code': '5615', 'name': 'Advertising and Promotions', 'type': 6, 'sub_type': 13},
    '5620': {'code': '5620', 'name': 'Bad Debts', 'type': 6, 'sub_type': 13},
    '5625': {'code': '5625', 'name': 'Courier and Postage', 'type': 6, 'sub_type': 13},
    '5660': {'code': '5660', 'name': 'Depreciation Expense', 'type': 6, 'sub_type': 13},
    '5685': {'code': '5685', 'name': 'Insurance Expense', 'type': 6, 'sub_type': 13},
    '5690': {'code': '5690', 'name': 'Bank Charges', 'type': 6, 'sub_type': 13},
    '5695': {'code': '5695', 'name': 'Interest Paid', 'type': 6, 'sub_type': 13},
    '5700': {'code': '5700', 'name': 'Office Expenses - Consumables', 'type': 6, 'sub_type': 13},
    '5705': {'code': '5705', 'name': 'Printing and Stationary', 'type': 6, 'sub_type': 13},
    '5710': {'code': '5710', 'name': 'Security Expenses', 'type': 6, 'sub_type': 13},
    '5715': {'code': '5715', 'name': 'Subscription - Membership Fees', 'type': 6, 'sub_type': 13},
    '5755': {'code': '5755', 'name': 'Electricity, Gas and Water', 'type': 6, 'sub_type': 13},
    '5760': {'code': '5760', 'name': 'Rent Paid', 'type': 6, 'sub_type': 13},
    '5765': {'code': '5765', 'name': 'Repairs and Maintenance', 'type': 6, 'sub_type': 13},
    '5770': {'code': '5770', 'name': 'Motor Vehicle Expenses', 'type': 6, 'sub_type': 13},
    '5771': {'code': '5771', 'name': 'Petrol and Oil', 'type': 6, 'sub_type': 13},
    '5775': {'code': '5775', 'name': 'Equipment Hire - Rental', 'type': 6, 'sub_type': 13},
    '5780': {'code': '5780', 'name': 'Telephone and Internet', 'type': 6, 'sub_type': 13},
    '5785': {'code': '5785', 'name': 'Travel and Accommodation', 'type': 6, 'sub_type': 13},
    '5786': {'code': '5786', 'name': 'Meals and Entertainment', 'type': 6, 'sub_type': 13},
    '5787': {'code': '5787', 'name': 'Staff Training', 'type': 6, 'sub_type': 13},
    '5790': {'code': '5790', 'name': 'Utilities', 'type': 6, 'sub_type': 13},
    '5791': {'code': '5791', 'name': 'Computer Expenses', 'type': 6, 'sub_type': 13},
    '5795': {'code': '5795', 'name': 'Registrations', 'type': 6, 'sub_type': 13},
    '5800': {'code': '5800', 'name': 'Licenses', 'type': 6, 'sub_type': 13},
    '5810': {'code': '5810', 'name': 'Foreign Exchange Loss', 'type': 6, 'sub_type': 13},
    '9990': {'code': '9990', 'name': 'Profit and Loss', 'type': 6, 'sub_type': 13}
}

chart_of_account1 = {
    '1060': {'code': '1060', 'name': 'Checking Account', 'type': 'Assets', 'sub_type': 'Current Asset'},
    '1065': {'code': '1065', 'name': 'Petty Cash', 'type': 'Assets', 'sub_type': 'Current Asset'},
    '1200': {'code': '1200', 'name': 'Account Receivables', 'type': 'Assets', 'sub_type': 'Current Asset'},
    '1205': {'code': '1205', 'name': 'Allowance for doubtful accounts', 'type': 'Assets', 'sub_type': 'Current Asset'},
    '1510': {'code': '1510', 'name': 'Inventory', 'type': 'Assets', 'sub_type': 'Inventory Asset'},
    '1520': {'code': '1520', 'name': 'Stock of Raw Materials', 'type': 'Assets', 'sub_type': 'Inventory Asset'},
    '1530': {'code': '1530', 'name': 'Stock of Work In Progress', 'type': 'Assets', 'sub_type': 'Inventory Asset'},
    '1540': {'code': '1540', 'name': 'Stock of Finished Goods', 'type': 'Assets', 'sub_type': 'Inventory Asset'},
    '1550': {'code': '1550', 'name': 'Goods Received Clearing account', 'type': 'Assets', 'sub_type': 'Inventory Asset'},
    '1810': {'code': '1810', 'name': 'Land and Buildings', 'type': 'Assets', 'sub_type': 'Non-current Asset'},
    '1820': {'code': '1820', 'name': 'Office Furniture and Equipement', 'type': 'Assets', 'sub_type': 'Non-current Asset'},
    '1825': {'code': '1825', 'name': 'Accum.depreciation-Furn. and Equip', 'type': 'Assets', 'sub_type': 'Non-current Asset'},
    '1840': {'code': '1840', 'name': 'Motor Vehicle', 'type': 'Assets', 'sub_type': 'Non-current Asset'},
    '1845': {'code': '1845', 'name': 'Accum.depreciation-Motor Vehicle', 'type': 'Assets', 'sub_type': 'Non-current Asset'},
    '2100': {'code': '2100', 'name': 'Account Payable', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2105': {'code': '2105', 'name': 'Deferred Income', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2110': {'code': '2110', 'name': 'Accrued Income Tax-Central', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2120': {'code': '2120', 'name': 'Income Tax Payable', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2130': {'code': '2130', 'name': 'Accrued Franchise Tax', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2140': {'code': '2140', 'name': 'Vat Provision', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2145': {'code': '2145', 'name': 'Purchase Tax', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2150': {'code': '2150', 'name': 'VAT Pay / Refund', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2151': {'code': '2151', 'name': 'Zero Rated', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2152': {'code': '2152', 'name': 'Capital import', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2153': {'code': '2153', 'name': 'Standard Import', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2154': {'code': '2154', 'name': 'Capital Standard', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2155': {'code': '2155', 'name': 'Vat Exempt', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2160': {'code': '2160', 'name': 'Accrued Use Tax Payable', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2210': {'code': '2210', 'name': 'Accrued Wages', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2220': {'code': '2220', 'name': 'Accrued Comp Time', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2230': {'code': '2230', 'name': 'Accrued Holiday Pay', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2240': {'code': '2240', 'name': 'Accrued Vacation Pay', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2310': {'code': '2310', 'name': 'Accr. Benefits - Central Provident Fund', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2320': {'code': '2320', 'name': 'Accr. Benefits - Stock Purchase', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2330': {'code': '2330', 'name': 'Accr. Benefits - Med, Den', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2340': {'code': '2340', 'name': 'Accr. Benefits - Payroll Taxes', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2350': {'code': '2350', 'name': 'Accr. Benefits - Credit Union', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2360': {'code': '2360', 'name': 'Accr. Benefits - Savings Bond', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2370': {'code': '2370', 'name': 'Accr. Benefits - Group Insurance', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2380': {'code': '2380', 'name': 'Accr. Benefits - Charity Cont.', 'type': 'Liabilities', 'sub_type': 'Current Liabilities'},
    '2620': {'code': '2620', 'name': 'Bank Loans', 'type': 'Liabilities', 'sub_type': 'Long Term Liabilities'},
    '2680': {'code': '2680', 'name': 'Loans from Shareholders', 'type': 'Liabilities', 'sub_type': 'Long Term Liabilities'},
    '3350': {'code': '3350', 'name': 'Common Shares', 'type': 'Liabilities', 'sub_type': 'Share Capital'},
    '3590': {'code': '3590', 'name': 'Reserves and Surplus', 'type': 'Liabilities', 'sub_type': 'Retained Earnings'},
    '3595': {'code': '3595', 'name': 'Owners Drawings', 'type': 'Liabilities', 'sub_type': 'Retained Earnings'},
    '3020': {'code': '3020', 'name': 'Opening Balances and adjustments', 'type': 'Equity', 'sub_type': 'Owners Equity'},
    '3025': {'code': '3025', 'name': 'Owners Contribution', 'type': 'Equity', 'sub_type': 'Owners Equity'},
    '3030': {'code': '3030', 'name': 'Profit and Loss ( current Year)', 'type': 'Equity', 'sub_type': 'Owners Equity'},
    '3035': {'code': '3035', 'name': 'Retained income', 'type': 'Equity', 'sub_type': 'Owners Equity'},
    '4010': {'code': '4010', 'name': 'Sales Income', 'type': 'Income', 'sub_type': 'Sales Revenue'},
    '4020': {'code': '4020', 'name': 'Service Income', 'type': 'Income', 'sub_type': 'Sales Revenue'},
    '4430': {'code': '4430', 'name': 'Shipping and Handling', 'type': 'Income', 'sub_type': 'Other Revenue'},
    '4435': {'code': '4435', 'name': 'Sundry Income', 'type': 'Income', 'sub_type': 'Other Revenue'},
    '4440': {'code': '4440', 'name': 'Interest Received', 'type': 'Income', 'sub_type': 'Other Revenue'},
    '4450': {'code': '4450', 'name': 'Foreign Exchange Gain', 'type': 'Income', 'sub_type': 'Other Revenue'},
    '4500': {'code': '4500', 'name': 'Unallocated Income', 'type': 'Income', 'sub_type': 'Other Revenue'},
    '4510': {'code': '4510', 'name': 'Discounts Received', 'type': 'Income', 'sub_type': 'Other Revenue'},
    '5005': {'code': '5005', 'name': 'Cost of Sales- On Services', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5010': {'code': '5010', 'name': 'Cost of Sales - Purchases', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5015': {'code': '5015', 'name': 'Operating Costs', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5020': {'code': '5020', 'name': 'Material Usage Varaiance', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5025': {'code': '5025', 'name': 'Breakage and Replacement Costs', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5030': {'code': '5030', 'name': 'Consumable Materials', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5035': {'code': '5035', 'name': 'Sub-contractor Costs', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5040': {'code': '5040', 'name': 'Purchase Price Variance', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5045': {'code': '5045', 'name': 'Direct Labour - COS', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5050': {'code': '5050', 'name': 'Purchases of Materials', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5060': {'code': '5060', 'name': 'Discounts Received', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5100': {'code': '5100', 'name': 'Freight Costs', 'type': 'Costs of Goods Sold', 'sub_type': 'Costs of Goods Sold'},
    '5410': {'code': '5410', 'name': 'Salaries and Wages', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5415': {'code': '5415', 'name': 'Directors Fees & Remuneration', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5420': {'code': '5420', 'name': 'Wages - Overtime', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5425': {'code': '5425', 'name': 'Members Salaries', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5430': {'code': '5430', 'name': 'UIF Payments', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5440': {'code': '5440', 'name': 'Payroll Taxes', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5450': {'code': '5450', 'name': 'Workers Compensation ( Coida )', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5460': {'code': '5460', 'name': 'Normal Taxation Paid', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5470': {'code': '5470', 'name': 'General Benefits', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5510': {'code': '5510', 'name': 'Provisional Tax Paid', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5520': {'code': '5520', 'name': 'Inc Tax Exp - State', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5530': {'code': '5530', 'name': 'Taxes - Real Estate', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5540': {'code': '5540', 'name': 'Taxes - Personal Property', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5550': {'code': '5550', 'name': 'Taxes - Franchise', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5560': {'code': '5560', 'name': 'Taxes - Foreign Withholding', 'type': 'Expenses', 'sub_type': 'Payroll Expenses'},
    '5610': {'code': '5610', 'name': 'Accounting Fees', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5615': {'code': '5615', 'name': 'Advertising and Promotions', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5620': {'code': '5620', 'name': 'Bad Debts', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5625': {'code': '5625', 'name': 'Courier and Postage', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5660': {'code': '5660', 'name': 'Depreciation Expense', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5685': {'code': '5685', 'name': 'Insurance Expense', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5690': {'code': '5690', 'name': 'Bank Charges', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5695': {'code': '5695', 'name': 'Interest Paid', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5700': {'code': '5700', 'name': 'Office Expenses - Consumables', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5705': {'code': '5705', 'name': 'Printing and Stationary', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5710': {'code': '5710', 'name': 'Security Expenses', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5715': {'code': '5715', 'name': 'Subscription - Membership Fees', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5755': {'code': '5755', 'name': 'Electricity, Gas and Water', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5760': {'code': '5760', 'name': 'Rent Paid', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5765': {'code': '5765', 'name': 'Repairs and Maintenance', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5770': {'code': '5770', 'name': 'Motor Vehicle Expenses', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5771': {'code': '5771', 'name': 'Petrol and Oil', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5775': {'code': '5775', 'name': 'Equipment Hire - Rental', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5780': {'code': '5780', 'name': 'Telephone and Internet', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5785': {'code': '5785', 'name': 'Travel and Accommodation', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5786': {'code': '5786', 'name': 'Meals and Entertainment', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5787': {'code': '5787', 'name': 'Staff Training', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5790': {'code': '5790', 'name': 'Utilities', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5791': {'code': '5791', 'name': 'Computer Expenses', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5795': {'code': '5795', 'name': 'Registrations', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5800': {'code': '5800', 'name': 'Licenses', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '5810': {'code': '5810', 'name': 'Foreign Exchange Loss', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'},
    '9990': {'code': '9990', 'name': 'Profit and Loss', 'type': 'Expenses', 'sub_type': 'General and Administrative expenses'}
}

chart_of_account_sub_type = {
    "assets": {
        '1': 'Current Asset',
        '2': 'Inventory Asset',
        '3': 'Non-current Asset',
    },
    "liabilities": {
        '1': 'Current Liabilities',
        '2': 'Long Term Liabilities',
        '3': 'Share Capital',
        '4': 'Retained Earnings',
    },
    "equity": {
        '1': 'Owners Equity',
    },
    "income": {
        '1': 'Sales Revenue',
        '2': 'Other Revenue',
    },
    "costs of goods sold": {
        '1': 'Costs of Goods Sold',
    },
    "expenses": {
        '1': 'Payroll Expenses',
        '2': 'General and Administrative expenses',
    },
}

# -----------------------
# Employee and Model Helpers
# -----------------------

class Utility:
    getsettings = None
    getsettingsid = None
    taxsData = None
    taxRateData = None
    taxData = None
    taxes = None
    languageSetting = None
    
    @staticmethod
    def get_setting():
        """
        Retrieves settings where created_by equals 1.
        """
        if Utility.getsettings is None:
            # Assuming Setting is your Django model for settings.
            from ..configs.settings import Setting  # adjust import as needed
            data = list(Setting.objects.filter(created_by=1))
            # If no data is found, try again (mimicking PHP behavior)
            if not data:
                data = list(Setting.objects.filter(created_by=1))
            Utility.getsettings = data
        return Utility.getsettings

    @staticmethod
    def get_setting_by_id(user_id):
        """
        Retrieves settings for the given user_id.
        Falls back to settings with created_by 1 if none found.
        """
        if Utility.getsettingsid is None:
            from ..configs.settings import Setting  # adjust import as needed
            data = list(Setting.objects.filter(created_by=user_id))
            if not data:
                data = list(Setting.objects.filter(created_by=1))
            Utility.getsettingsid = data
        return Utility.getsettingsid

    @staticmethod
    def settings():
        """
        Retrieves and returns a dictionary of settings.
        Defaults are provided and then overridden by any setting records.
        """
        # Here you should replace this with your actual authentication check.
        # For example, if using Django's authentication:
        from django.contrib.auth import get_user
        request_user = get_user(django_settings)  # placeholder: adjust to your context

        if request_user.is_authenticated:
            data = Utility.get_setting_by_id(request_user.creator_id)
            if not data:
                data = Utility.get_setting()
        else:
            data = Utility.get_setting()

        settings_dict = {
            "site_currency": "USD",
            "site_currency_symbol": "$",
            "site_currency_symbol_position": "pre",
            "site_date_format": "M j, Y",
            "site_time_format": "g:i A",
            "timezone": '',
            "company_name": "",
            "company_address": "",
            "company_city": "",
            "company_state": "",
            "company_zipcode": "",
            "company_country": "",
            "company_telephone": "",
            "invoice_prefix": "#INVO",
            "journal_prefix": "#JUR",
            "invoice_color": "ffffff",
            "proposal_prefix": "#PROP",
            "proposal_color": "ffffff",
            "bill_prefix": "#BILL",
            "expense_prefix": "#EXP",
            "bill_color": "ffffff",
            "customer_prefix": "#CUST",
            "vendor_prefix": "#VEND",
            "footer_title": "",
            "footer_notes": "",
            "invoice_template": "template1",
            "bill_template": "template1",
            "proposal_template": "template1",
            "registration_number": "",
            "vat_number": "",
            "default_language": "en",
            "enable_stripe": "",
            "enable_paypal": "",
            "paypal_mode": "",
            "paypal_client_id": "",
            "paypal_secret_key": "",
            "stripe_key": "",
            "stripe_secret": "",
            "decimal_number": "2",
            "tax_type": "",
            "shipping_display": "on",
            "display_landing_page": "on",
            "employee_prefix": "#EMP00",
            "leave_status": "1",
            "bug_prefix": "#ISSUE",
            "title_text": "",
            "footer_text": "",
            "company_start_time": "09:00",
            "company_end_time": "18:00",
            "gdpr_cookie": "off",
            "interval_time": "",
            "zoom_apikey": "",
            "zoom_apisecret": "",
            "slack_webhook": "",
            "telegram_accestoken": "",
            "telegram_chatid": "",
            "enable_signup": "on",
            "email_verification": "on",
            "cookie_text": ("We use cookies to ensure that we give you the best experience on our website. "
                            "If you continue to use this site we will assume that you are happy with it.\n"),
            "company_logo_light": "logo-light.png",
            "company_logo_dark": "logo-dark.png",
            "company_favicon": "favicon.png",
            "cust_theme_bg": "on",
            "cust_darklayout": "off",
            "color": "",
            "SITE_RTL": "off",
            "purchase_prefix": "#PUR",
            "purchase_color": "ffffff",
            "purchase_template": "template1",
            "pos_color": "ffffff",
            "pos_template": "template1",
            "pos_prefix": "#POS",
            "storage_setting": "local",
            "local_storage_validation": "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size": "2048000",
            "s3_key": "",
            "s3_secret": "",
            "s3_region": "",
            "s3_bucket": "",
            "s3_url": "",
            "s3_endpoint": "",
            "s3_max_upload_size": "",
            "s3_storage_validation": "",
            "wasabi_key": "",
            "wasabi_secret": "",
            "wasabi_region": "",
            "wasabi_bucket": "",
            "wasabi_url": "",
            "wasabi_root": "",
            "wasabi_max_upload_size": "",
            "wasabi_storage_validation": "",
            "purchase_logo": "",
            "proposal_logo": "",
            "invoice_logo": "",
            "bill_logo": "",
            "pos_logo": "",
            "contract_prefix": "#CON",
            "barcode_type": "code128",
            "barcode_format": "css",
            "new_user": "1",
            "new_client": "1",
            "new_support_ticket": "1",
            "lead_assigned": "1",
            "deal_assigned": "1",
            "new_award": "1",
            "customer_invoice_sent": "1",
            "new_invoice_payment": "1",
            "new_payment_reminder": "1",
            "new_bill_payment": "1",
            "bill_resent": "1",
            "proposal_sent": "1",
            "complaint_resent": "1",
            "leave_action_sent": "1",
            "payslip_sent": "1",
            "promotion_sent": "1",
            "resignation_sent": "1",
            "termination_sent": "1",
            "transfer_sent": "1",
            "trip_sent": "1",
            "vendor_bill_sent": "1",
            "warning_sent": "1",
            "new_contract": "1",
            "vat_gst_number_switch": "off",
            "google_calendar_enable": "off",
            "google_calendar_json_file": "",
            "meta_title": "",
            "meta_desc": "",
            "meta_image": "",
            "enable_cookie": "on",
            "necessary_cookies": "on",
            "cookie_logging": "on",
            "cookie_title": "We use cookies!",
            "cookie_description": ("Hi, this website uses essential cookies to ensure its proper operation and tracking cookies "
                                   "to understand how you interact with it"),
            "strictly_cookie_title": "Strictly necessary cookies",
            "strictly_cookie_description": ("These cookies are essential for the proper functioning of my website. "
                                            "Without these cookies, the website would not work properly"),
            "more_information_description": "For any queries in relation to our policy on cookies and your choices, please contact us",
            "contactus_url": "#",
            "twilio_sid": "",
            "twilio_token": "",
            "twilio_from": "",
            "chat_gpt_key": "",
            "ip_restrict": "off",
            "mail_driver": "",
            "mail_host": "",
            "mail_port": "",
            "mail_username": "",
            "mail_password": "",
            "mail_encryption": "",
            "mail_from_address": "",
            "mail_from_name": "",
            "recaptcha_module": "",
            "google_recaptcha_key": "",
            "google_recaptcha_secret": "",
            "pusher_app_id": "",
            "pusher_app_key": "",
            "pusher_app_secret": "",
            "pusher_app_cluster": "",
        }
        # Override defaults with data from the database
        for row in data:
            # Assuming each row has attributes 'name' and 'value'
            settings_dict[row.name] = row.value

        # Example: Here you might want to configure additional settings (e.g. captcha options)
        # Skipping dynamic configuration in this Django version.
        return settings_dict

    @staticmethod
    def settings_by_id(user_id):
        """
        Similar to settings() but for a given user_id.
        """
        data = Utility.get_setting_by_id(user_id)
        settings_dict = {
            "site_currency": "USD",
            "site_currency_symbol": "$",
            "site_currency_symbol_position": "pre",
            "site_date_format": "M j, Y",
            "site_time_format": "g:i A",
            "company_name": "",
            "company_address": "",
            "company_city": "",
            "company_state": "",
            "company_zipcode": "",
            "company_country": "",
            "company_telephone": "",
            "invoice_prefix": "#INVO",
            "invoice_color": "ffffff",
            "proposal_prefix": "#PROP",
            "proposal_color": "ffffff",
            "bill_prefix": "#BILL",
            "expense_prefix": "#EXP",
            "bill_color": "ffffff",
            "customer_prefix": "#CUST",
            "vendor_prefix": "#VEND",
            "footer_title": "",
            "footer_notes": "",
            "invoice_template": "template1",
            "bill_template": "template1",
            "proposal_template": "template1",
            "registration_number": "",
            "vat_number": "",
            "default_language": "en",
            "enable_stripe": "",
            "enable_paypal": "",
            "paypal_mode": "",
            "paypal_client_id": "",
            "paypal_secret_key": "",
            "stripe_key": "",
            "stripe_secret": "",
            "decimal_number": "2",
            "tax_type": "",
            "shipping_display": "on",
            "journal_prefix": "#JUR",
            "display_landing_page": "on",
            "employee_prefix": "#EMP00",
            "leave_status": "1",
            "bug_prefix": "#ISSUE",
            "title_text": "",
            "footer_text": "",
            "company_start_time": "09:00",
            "company_end_time": "18:00",
            "gdpr_cookie": "off",
            "interval_time": "",
            "zoom_apikey": "",
            "zoom_apisecret": "",
            "slack_webhook": "",
            "telegram_accestoken": "",
            "telegram_chatid": "",
            "enable_signup": "on",
            "email_verification": "on",
            "cookie_text": ("We use cookies to ensure that we give you the best experience on our website. "
                            "If you continue to use this site we will assume that you are happy with it.\n"),
            "company_logo_light": "logo-light.png",
            "company_logo_dark": "logo-dark.png",
            "company_favicon": "favicon.png",
            "cust_theme_bg": "on",
            "cust_darklayout": "off",
            "color": "",
            "SITE_RTL": "off",
            "purchase_prefix": "#PUR",
            "purchase_color": "ffffff",
            "purchase_template": "template1",
            "proposal_logo": "",
            "purchase_logo": "",
            "invoice_logo": "",
            "bill_logo": "",
            "pos_logo": "",
            "pos_color": "ffffff",
            "pos_template": "template1",
            "storage_setting": "local",
            "local_storage_validation": "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size": "2048000",
            "s3_key": "",
            "s3_secret": "",
            "s3_region": "",
            "s3_bucket": "",
            "s3_url": "",
            "s3_endpoint": "",
            "s3_max_upload_size": "",
            "s3_storage_validation": "",
            "wasabi_key": "",
            "wasabi_secret": "",
            "wasabi_region": "",
            "wasabi_bucket": "",
            "wasabi_url": "",
            "wasabi_root": "",
            "wasabi_max_upload_size": "",
            "wasabi_storage_validation": "",
            "barcode_type": "code128",
            "barcode_format": "css",
            "new_user": "1",
            "new_client": "1",
            "new_support_ticket": "1",
            "lead_assigned": "1",
            "deal_assigned": "1",
            "new_award": "1",
            "customer_invoice_sent": "1",
            "new_invoice_payment": "1",
            "new_payment_reminder": "1",
            "new_bill_payment": "1",
            "bill_resent": "1",
            "proposal_sent": "1",
            "complaint_resent": "1",
            "leave_action_sent": "1",
            "payslip_sent": "1",
            "promotion_sent": "1",
            "resignation_sent": "1",
            "termination_sent": "1",
            "transfer_sent": "1",
            "trip_sent": "1",
            "vendor_bill_sent": "1",
            "warning_sent": "1",
            "new_contract": "1",
            "vat_gst_number_switch": "off",
            "google_calendar_enable": "on",
            "google_calendar_json_file": "",
            "meta_title": "",
            "meta_desc": "",
            "meta_image": "",
            "enable_cookie": "on",
            "necessary_cookies": "on",
            "cookie_logging": "on",
            "cookie_title": "We use cookies!",
            "cookie_description": ("Hi, this website uses essential cookies to ensure its proper operation and tracking cookies "
                                   "to understand how you interact with it"),
            "strictly_cookie_title": "Strictly necessary cookies",
            "strictly_cookie_description": ("These cookies are essential for the proper functioning of my website. "
                                            "Without these cookies, the website would not work properly"),
            "more_information_description": "For any queries in relation to our policy on cookies and your choices, please contact us",
            "contactus_url": "#",
            "twilio_sid": "",
            "twilio_token": "",
            "twilio_from": "",
            "chat_gpt_key": "",
            "ip_restrict": "off",
            "timezone": '',
            "pusher_app_id": "",
            "pusher_app_key": "",
            "pusher_app_secret": "",
            "pusher_app_cluster": "",
        }
        for row in Utility.get_setting_by_id(user_id):
            settings_dict[row.name] = row.value
        return settings_dict

    # Static property for email status mapping
    emailStatus = {
        'new_user': 'New User',
        'new_client': 'New Client',
        'new_support_ticket': 'New Support Ticket',
        'lead_assigned': 'Lead Assigned',
        'deal_assigned': 'Deal Assigned',
        'new_award': 'New Award',
        'customer_invoice_sent': 'Customer Invoice Sent',
        'new_invoice_payment': 'New Invoice Payment',
        'new_payment_reminder': 'New Payment Reminder',
        'new_bill_payment': 'New Bill Payment',
        'bill_resent': 'Bill Resent',
        'proposal_sent': 'Proposal Sent',
        'complaint_resent': 'Complaint Resent',
        'leave_action_sent': 'Leave Action Sent',
        'payslip_sent': 'Payslip Sent',
        'promotion_sent': 'Promotion Sent',
        'resignation_sent': 'Resignation Sent',
        'termination_sent': 'Termination Sent',
        'transfer_sent': 'Transfer Sent',
        'trip_sent': 'Trip Sent',
        'vendor_bill_sent': 'Vendor Bill Sent',
        'warning_sent': 'Warning Sent',
        'new_contract': 'New Contract',
    }

    @staticmethod
    def languages():
        """
        Returns a mapping of language codes to full names.
        If the languages table exists, filters out disabled languages per settings.
        """
        if Utility.languageSetting is None:
            # Assume lang_list() returns a dict mapping codes to names.
            languages = Utility.lang_list()
            # If Language table exists, adjust the list based on settings.
            try:
                from ..shapes.language import Language  # adjust import
                settings_data = Utility.settings()
                if settings_data.get('disable_lang'):
                    disabled = [x.strip() for x in settings_data['disable_lang'].split(',')]
                    qs = Language.objects.exclude(code__in=disabled)
                    languages = {lang.code: lang.full_name for lang in qs}
                else:
                    qs = Language.objects.all()
                    languages = {lang.code: lang.full_name for lang in qs}
            except Exception:
                # Fallback to default list if Language table not available.
                pass
            Utility.languageSetting = languages
        return Utility.languageSetting

    @staticmethod
    def get_val_by_name(key):
        """
        Returns the setting value for the given key.
        """
        settings_data = Utility.settings()
        return settings_data.get(key, "")

    @staticmethod
    def set_environment_value(values):
        """
        Updates the environment file with the given key/value pairs.
        Assumes a .env file in the BASE_DIR.
        """
        env_file = os.path.join(django_settings.BASE_DIR, ".env")
        try:
            with open(env_file, "r") as f:
                content = f.read()
            for env_key, env_value in values.items():
                pattern = rf'^{env_key}=.*$'
                replacement = f'{env_key}="{env_value}"'
                if re.search(pattern, content, flags=re.MULTILINE):
                    content = re.sub(pattern, replacement, content, flags=re.MULTILINE)
                else:
                    content += f'\n{replacement}'
            # Ensure the file ends with a newline
            if not content.endswith("\n"):
                content += "\n"
            with open(env_file, "w") as f:
                f.write(content)
            return True
        except Exception:
            return False

    @staticmethod
    def template_data():
        """
        Returns a dictionary with colors and templates.
        """
        return {
            "colors": [
                "003580", "666666", "6676ef", "f50102", "f9b034", "fbdd03", "c1d82f",
                "37a4e4", "8a7966", "6a737b", "050f2c", "0e3666", "3baeff", "3368e6",
                "b84592", "f64f81", "f66c5f", "fac168", "46de98", "40c7d0", "be0028",
                "2f9f45", "371676", "52325d", "511378", "0f3866", "48c0b6", "297cc0",
                "ffffff", "000"
            ],
            "templates": {
                "template1": "New York",
                "template2": "Toronto",
                "template3": "Rio",
                "template4": "London",
                "template5": "Istanbul",
                "template6": "Mumbai",
                "template7": "Hong Kong",
                "template8": "Tokyo",
                "template9": "Sydney",
                "template10": "Paris",
            }
        }

    @staticmethod
    def price_format(settings_data, price):
        """
        Formats the price according to currency symbol position and decimal setting.
        """
        decimal_number = int(settings_data.get("decimal_number", 2))
        formatted = f"{price:.{decimal_number}f}"
        if settings_data.get("site_currency_symbol_position") == "pre":
            return f"{settings_data.get('site_currency_symbol', '')}{formatted}"
        else:
            return f"{formatted}{settings_data.get('site_currency_symbol', '')}"

    @staticmethod
    def currency_symbol(settings_data):
        return settings_data.get("site_currency_symbol", "")

    @staticmethod
    def date_format(settings_data, date_str):
        """
        Formats a date string using the provided format.
        Assumes date_str is ISO formatted.
        """
        dt = datetime.fromisoformat(date_str)
        return dt.strftime(settings_data.get("site_date_format", "%b %d, %Y"))

    @staticmethod
    def time_format(settings_data, time_str):
        dt = datetime.fromisoformat(time_str)
        return dt.strftime(settings_data.get("site_time_format", "%I:%M %p"))

    @staticmethod
    def purchase_number_format(number):
        settings_data = Utility.settings()
        return f"{settings_data.get('purchase_prefix','')}{number:05d}"

    @staticmethod
    def pos_number_format(number):
        settings_data = Utility.settings()
        return f"{settings_data.get('pos_prefix','')}{number:05d}"

    @staticmethod
    def contract_number_format(number):
        settings_data = Utility.settings()
        return f"{settings_data.get('contract_prefix','')}{number:05d}"

    @staticmethod
    def invoice_number_format(settings_data, number):
        return f"{settings_data.get('invoice_prefix','')}{number:05d}"

    @staticmethod
    def proposal_number_format(settings_data, number):
        return f"{settings_data.get('proposal_prefix','')}{number:05d}"

    @staticmethod
    def customer_proposal_number_format(number):
        settings_data = Utility.settings()
        return f"{settings_data.get('proposal_prefix','')}{number:05d}"

    @staticmethod
    def customer_invoice_number_format(number):
        settings_data = Utility.settings()
        return f"{settings_data.get('invoice_prefix','')}{number:05d}"

    @staticmethod
    def customer_pos_number_format(number):
        settings_data = Utility.settings()
        return f"{settings_data.get('pos_prefix','')}{number:05d}"

    @staticmethod
    def bill_number_format(settings_data, number):
        return f"{settings_data.get('bill_prefix','')}{number:05d}"

    @staticmethod
    def vendor_bill_number_format(number):
        settings_data = Utility.settings()
        return f"{settings_data.get('bill_prefix','')}{number:05d}"

    @staticmethod
    def get_tax(tax_id):
        """
        Retrieves a Tax instance by id and caches it.
        """
        if Utility.taxes is None:
            from ..bills.tax import Tax  # adjust import as needed
            Utility.taxes = Tax.objects.get(id=tax_id)
        return Utility.taxes

    @staticmethod
    def tax(taxes_str):
        """
        Given a comma-separated string of tax ids, returns a list of Tax objects.
        """
        if Utility.taxsData is None:
            tax_ids = taxes_str.split(',')
            taxes_list = []
            for tax_id in tax_ids:
                taxes_list.append(Utility.get_tax(tax_id.strip()))
            Utility.taxsData = taxes_list
        return Utility.taxsData

    @staticmethod
    def tax_rate(tax_rate, price, quantity, discount=0):
        return ((price * quantity) - discount) * (tax_rate / 100.0)

    @staticmethod
    def total_tax_rate(taxes_str):
        if Utility.taxRateData is None:
            tax_rate_total = 0
            for tax_id in taxes_str.split(','):
                tax = Utility.get_tax(tax_id.strip())
                tax_rate_total += getattr(tax, 'rate', 0) or 0
            Utility.taxRateData = tax_rate_total
        return Utility.taxRateData

    @staticmethod
    def user_balance(user_type, user_id, amount, trans_type):
        """
        Updates the balance for a customer or vendor.
        """
        if user_type == 'customer':
            from ..individuals.customer import Customer
            user = Customer.objects.filter(id=user_id).first()
        else:
            from ..companies.vendor import Vendor
            user = Vendor.objects.filter(id=user_id).first()
        if user:
            if trans_type == 'credit':
                user.balance += amount
            elif trans_type == 'debit':
                user.balance -= amount
            user.save()

    @staticmethod
    def update_user_balance(user_type, user_id, amount, trans_type):
        """
        Updates the balance for a customer or vendor (reverse operation).
        """
        if user_type == 'customer':
            from ..individuals.customer import Customer
            user = Customer.objects.filter(id=user_id).first()
        else:
            from ..companies.vendor import Vendor
            user = Vendor.objects.filter(id=user_id).first()
        if user:
            if trans_type == 'credit':
                user.balance -= amount
            elif trans_type == 'debit':
                user.balance += amount
            user.save()

    @staticmethod
    def bank_account_balance(account_id, amount, trans_type):
        from ..companies.bank_account import BankAccount
        bank_account = BankAccount.objects.filter(id=account_id).first()
        if bank_account:
            if trans_type == 'credit':
                bank_account.opening_balance += amount
            elif trans_type == 'debit':
                bank_account.opening_balance -= amount
            bank_account.save()

    @staticmethod
    def hex2rgb(hex_str):
        """
        Converts a hex color string to an (R, G, B) tuple.
        """
        hex_str = hex_str.replace("#", "")
        if len(hex_str) == 3:
            r = int(hex_str[0] * 2, 16)
            g = int(hex_str[1] * 2, 16)
            b = int(hex_str[2] * 2, 16)
        else:
            r = int(hex_str[0:2], 16)
            g = int(hex_str[2:4], 16)
            b = int(hex_str[4:6], 16)
        return (r, g, b)

    @staticmethod
    def get_font_color(color_code):
        """
        Returns 'black' or 'white' based on the luminance of the background color.
        """
        r, g, b = Utility.hex2rgb(color_code)
        def adjust(c):
            c = c / 255.0
            return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4
        R, G, B = adjust(r), adjust(g), adjust(b)
        L = 0.2126 * R + 0.7152 * G + 0.0722 * B
        return "black" if L > 0.179 else "white"

    @staticmethod
    def delete_directory(dir_path):
        """
        Recursively deletes a directory.
        """
        if not os.path.exists(dir_path):
            return True
        if not os.path.isdir(dir_path):
            os.remove(dir_path)
            return True
        shutil.rmtree(dir_path)
        return True

    @staticmethod
    def employee_number(created_by):
        """
        Generate a new employee number.
        For simplicity, this returns the count of employees for the creator plus one.
        Adjust logic as needed.
        """
        count = Employee.objects.filter(created_by=created_by).count()
        return count + 1

    @staticmethod
    def employee_details(user_id, created_by):
        """
        Given a user_id and created_by value, creates an Employee record for the given user.
        Assumes the Employee model has fields: user, name, email, password, employee_id, created_by.
        The employee_number() function should be implemented to generate a new employee number.
        """
        try:
            user = User.objects.get(id=user_id)
        except User.DoesNotExist:
            raise ValueError("User not found")
        
        employee = Employee.objects.create(
            user_id=user.id,
            name=user.name,
            email=user.email,
            password=user.password,  # Make sure to handle hashing appropriately
            employee_id=Utility.employee_number(created_by),
            created_by=created_by,
        )
        return employee

    # -----------------------
    # Chart of Account Data
    # -----------------------

    @staticmethod
    def chart_of_account_type_data(company_id):
        """
        For each account type in chart_of_account_type, creates a ChartOfAccountType
        record and its corresponding sub-types.
        """
        for key, type_name in chart_of_account_type.items():
            account_type = ChartOfAccountType.objects.create(
                name=type_name,
                created_by=company_id
            )
            subtypes = chart_of_account_sub_type.get(key, {})
            for sub in subtypes.values():
                ChartOfAccountSubType.objects.create(
                    name=sub,
                    type=account_type.id
                )

    @staticmethod
    def chart_of_account_data1(user_id):
        """
        For a new company, create ChartOfAccount records from chart_of_account1.
        """
        for account in chart_of_account1:
            account_type = ChartOfAccountType.objects.filter(created_by=user_id, name=account['type']).first()
            if account_type:
                sub_type = ChartOfAccountSubType.objects.filter(type=account_type.id, name=account['sub_type']).first()
                if sub_type:
                    ChartOfAccount.objects.create(
                        code=account['code'],
                        name=account['name'],
                        type=account_type.id,
                        sub_type=sub_type.id,
                        is_enabled=True,
                        created_by=user_id
                    )

    @staticmethod
    def chart_of_account_data(user):
        """
        Create ChartOfAccount records from chart_of_account.
        """
        for account in chart_of_account:
            ChartOfAccount.objects.create(
                code=account['code'],
                name=account['name'],
                type=account['type'],
                sub_type=account['sub_type'],
                is_enabled=True,
                created_by=user.id
            )

    # -----------------------
    # Email Template Sending
    # -----------------------

    @staticmethod
    def replace_variable(content, obj):
        """
        Replaces variables in content with values from obj.
        For example, replace {name} with obj.get('name').
        This is a simple implementation using regular expressions.
        """
        # Pattern to match {variable}
        pattern = r'\{(\w+)\}'
        def replacer(match):
            key = match.group(1)
            return str(obj.get(key, match.group(0)))
        return re.sub(pattern, replacer, content)

    def settings_by_id(user_id):
        """
        Retrieve mail settings for the given user ID.
        This function assumes you have a settings table or model.
        For simplicity, we return a dict with dummy values.
        """
        # Example: fetch settings from a model or return defaults.
        return {
            'mail_driver': django_settings.EMAIL_BACKEND,
            'mail_host': django_settings.EMAIL_HOST,
            'mail_port': django_settings.EMAIL_PORT,
            'mail_encryption': django_settings.EMAIL_USE_TLS and 'tls' or '',
            'mail_username': django_settings.EMAIL_HOST_USER,
            'mail_password': django_settings.EMAIL_HOST_PASSWORD,
            'mail_from_address': django_settings.DEFAULT_FROM_EMAIL,
            'mail_from_name': 'Your Site',
        }

    @staticmethod
    def send_email_template(email_template_name, mail_to, obj):
        """
        Sends an email using a template.
        mail_to: list of email addresses.
        obj: dictionary of variables for replacement.
        """
        usr = User.objects.first()  # Replace with actual authenticated user retrieval
        mail_to = list(mail_to)  # Ensure it's a list
        # If user is not Super Admin, find template and check if active
        if usr.type != 'Super Admin':
            template = EmailTemplate.objects.filter(name__icontains=email_template_name).first()
            if template:
                is_active = UserEmailTemplate.objects.filter(
                    template_id=template.id,
                    user_id=usr.creator_id()
                ).first()
            else:
                return {'is_success': False, 'error': _('Mail not send, email not found')}
        else:
            template = EmailTemplate.objects.filter(name__icontains=email_template_name).first()
            is_active = type('dummy', (object,), {'is_active': 1})
        if is_active and is_active.is_active == 1:
            settings_data = Utility.settings_by_id(usr.id)
            content_obj = EmailTemplateLang.objects.filter(parent_id=template.id, lang__icontains=usr.lang).first()
            if content_obj and content_obj.content:
                content_obj.content = Utility.replace_variable(content_obj.content, obj)
                content_obj.from_field = template.from_field  # Adjust field name as needed
                try:
                    # Configure email settings dynamically if needed; here we use Django's send_mail
                    email = EmailMessage(
                        subject=template.subject,
                        body=content_obj.content,
                        from_email=settings_data['mail_from_address'],
                        to=mail_to,
                    )
                    email.send(fail_silently=False)
                    return {'is_success': True, 'error': False}
                except Exception as e:
                    return {'is_success': False, 'error': str(e)}
            else:
                return {'is_success': False, 'error': _('Mail not send, email is empty')}
        else:
            return {'is_success': True, 'error': False}

    @staticmethod
    def send_user_email_template(email_template_name, mail_to, obj):
        """
        Similar to send_email_template, but uses settings_by_id(1) (i.e. default settings).
        """
        usr = User.objects.first()  # Replace with actual authenticated user
        mail_to = list(mail_to)
        template = EmailTemplate.objects.filter(name__icontains=email_template_name).first()
        if template:
            is_active = UserEmailTemplate.objects.filter(
                template_id=template.id,
                user_id=usr.creator_id()
            ).first()
            if is_active and is_active.is_active == 1:
                settings_data = Utility.settings_by_id(1)
                content_obj = EmailTemplateLang.objects.filter(parent_id=template.id, lang__icontains=usr.lang).first()
                content_obj.from_field = template.from_field
                if content_obj and content_obj.content:
                    content_obj.content = Utility.replace_variable(content_obj.content, obj)
                    try:
                        email = EmailMessage(
                            subject=template.subject,
                            body=content_obj.content,
                            from_email=settings_data['mail_from_address'],
                            to=mail_to,
                        )
                        email.send(fail_silently=False)
                        return {'is_success': True, 'error': False}
                    except Exception as e:
                        return {'is_success': False, 'error': str(e)}
                else:
                    return {'is_success': False, 'error': _('Mail not send, email is empty')}
            else:
                return {'is_success': True, 'error': False}
        else:
            return {'is_success': False, 'error': _('Mail not send, email not found')}

    # -----------------------
    # Pipeline and Stages
    # -----------------------

    @staticmethod
    def pipeline_lead_deal_stage(created_id):
        """
        Creates a pipeline named 'Sales' and corresponding lead stages and general stages.
        """
        pipeline = Pipeline.objects.create(
            name='Sales',
            created_by=created_id
        )
        stages = ['Draft', 'Sent', 'Open', 'Revised', 'Declined']
        for stage in stages:
            LeadStage.objects.create(
                name=stage,
                pipeline_id=pipeline.id,
                created_by=created_id,
            )
            Stage.objects.create(
                name=stage,
                pipeline_id=pipeline.id,
                created_by=created_id,
            )

    @staticmethod
    def project_task_stages(created_id):
        """
        Creates project task stages for a new company.
        """
        project_stages = ['To Do', 'In Progress', 'Review', 'Done']
        for key, stage in enumerate(project_stages):
            TaskStage.objects.create(
                name=stage,
                order=key,
                created_by=created_id,
            )

    @staticmethod
    def labels(created_id):
        """
        Creates default labels and bug statuses.
        """
        label_data = [
            {'name': 'On Hold', 'color': 'primary'},
            {'name': 'New', 'color': 'info'},
            {'name': 'Pending', 'color': 'warning'},
            {'name': 'Loss', 'color': 'danger'},
            {'name': 'Win', 'color': 'success'},
        ]
        for label in label_data:
            Label.objects.create(
                name=label['name'],
                color=label['color'],
                pipeline_id=1,  # Adjust as needed
                created_by=created_id,
            )
        bug_statuses = ['Confirmed', 'Resolved', 'Unconfirmed', 'In Progress', 'Verified']
        for status in bug_statuses:
            BugStatus.objects.create(
                title=status,
                created_by=created_id,
            )

    @staticmethod
    def sources(created_id):
        """
        Creates default lead sources.
        """
        source_names = ['Websites', 'Facebook', 'Naukari.com', 'Phone', 'LinkedIn']
        for name in source_names:
            Source.objects.create(
                name=name,
                created_by=created_id,
            )

    @staticmethod
    def job_stage(created_id):
        """
        Creates default job stages.
        """
        stages = ['Applied', 'Phone Screen', 'Interview', 'Hired', 'Rejected']
        for stage in stages:
            JobStage.objects.create(
                title=stage,
                created_by=created_id,
            )

    # -----------------------
    # Language Settings
    # -----------------------

    @staticmethod
    def lang_list():
        """
        Returns a dictionary of language codes to full names.
        """
        return {
            "ar": "Arabic",
            "zh": "Chinese",
            "da": "Danish",
            "de": "German",
            "en": "English",
            "es": "Spanish",
            "fr": "French",
            "he": "Hebrew",
            "it": "Italian",
            "ja": "Japanese",
            "nl": "Dutch",
            "pl": "Polish",
            "pt": "Portuguese",
            "ru": "Russian",
            "tr": "Turkish",
            "pt-br": "Portuguese (Brazil)",
        }

    @staticmethod
    def language_create():
        """
        Creates Language records from lang_list if they do not already exist.
        """
        languages = Utility.lang_list()
        for code, full_name in languages.items():
            if not Language.objects.filter(code=code).exists():
                language = Language(code=code, full_name=full_name)
                language.save()

    @staticmethod
    def lang_setting():
        """
        Retrieves settings from the 'settings' table (assumed to be managed via a Django model)
        filtered by created_by = 1. If none found, tries again.
        """
        from ..configs.settings import Setting
        data = Setting.objects.filter(created_by=1)
        if not data.exists():
            data = Setting.objects.filter(created_by=1)
        settings_dict = {row.name: row.value for row in data}
        return settings_dict

    @staticmethod
    def get_chatgpt_settings():
        """
        Equivalent of:
            public static function getChatGPTSettings()
            {
                $user = User::find(\Auth::user()->creatorId());
                $plan = \App\Models\Plan::find($user->plan);
                return $plan;
            }
        """
        # Example authentication approach:
        # user = get_user(your_request_object)
        # actual_user = User.objects.get(id=user.creator_id)
        # plan = Plan.objects.filter(id=actual_user.plan).first()
        # return plan
        pass

    @staticmethod
    def get_account_balance(account_id, start_date=None, end_date=None):
        """
        Equivalent of:
            public static function getAccountBalance($account_id,$start_date=null,$end_date=null) { ... }
        Translates Eloquent queries into Django ORM queries.
        """
        # 1) Determine date range
        # 2) Sum invoice-related amounts, payments, revenues, etc.
        # 3) Return final computed balance

        pass

    @staticmethod
    def get_account_data(account_id, start_date=None, end_date=None):
        """
        Equivalent of:
            public static function getAccountData($account_id,$start_date=null,$end_date=null) { ... }
        Returns a dict of related data for the specified account within the date range.
        """
        pass

    @staticmethod
    def get_balance_sheet_credit(account_id, start_date=None, end_date=None):
        """
        Equivalent of:
            public static function getBalanceSheetCredit($account_id,$start_date=null,$end_date=null) { ... }
        Summation logic for 'credit' side of a balance sheet.
        """
        pass

    @staticmethod
    def get_balance_sheet_debit(account_id, start_date=None, end_date=None):
        """
        Equivalent of:
            public static function getBalanceSheetDebit($account_id,$start_date=null,$end_date=null) { ... }
        Summation logic for 'debit' side of a balance sheet.
        """
        pass

    @staticmethod
    def trial_balance(account_id, start, end):
        """
        Equivalent of:
            public static function trialBalance($account_id, $start, $end) { ... }
        Collects and merges invoice, journal, revenue, bill, payment data, etc.
        Returns an array (list) of combined results.
        """
        pass

    @staticmethod
    def smtp_detail(user_id):
        """
        Equivalent of:
            public static function smtpDetail($user_id) { ... }
        Dynamically configures/returns SMTP settings.
        """
        pass

    @staticmethod
    def get_pusher_setting():
        """
        Equivalent of:
            public static function getPusherSetting() { ... }
        Returns pusher settings after optionally calling config([...]) in Django context.
        """
        pass