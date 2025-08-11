import re
from datetime import datetime
from typing import List, Dict, Any
from django.core.management.base import BaseCommand
from django.utils import timezone
from django.contrib.auth.hashers import make_password
from django.db import transaction
# Import your models – adjust the import paths as needed.
from ...Models.ssr.experience_certificate import ExperienceCertificate
from ...Models.ssr.generated_offer_letter import GeneratedOfferLetter 
from ...Models.ssr.joining_letter import JoiningLetter
from ...Models.ssr.noc import Noc
from ...Models.individuals.user import User
from ...Models.individuals.role import Role
from ...Models.utils.utility import Utility
from ...Models.companies.bank_account import BankAccount
from ...Models.configs.settings import Settings
from ...Models.configs.permission import Permission
now = timezone.now()

def variate_perm(base: str) -> List[str]:
    if not re.search(r'[a-z0-9]##[a-zA-Z0-9]', base):
        return [base]
    variants = []
    for sep in (' ', '-', '_'):
        variants.append(base.replace('##', sep))
    return variants
def auto_now(create:datetime=timezone.now(), update:datetime=timezone.now()) -> Dict[str, datetime]:
    return {
        "created_at": create,
        "updated_at": update
    }
STANDARD_PERMISSIONS = ['manage', 'create', 'edit', 'delete']
def defaulted_permissions(sufix:str='#REFERENCE_ERROR',
                          add_perms:List[str] = [], 
                          remove_perms:List[str] = [],
                          guard_name:str='web',
                          add_data:Dict[str,Any]=auto_now()) -> List[Dict[str, str]]:
    base_actions = STANDARD_PERMISSIONS
    if add_perms: base_actions.extend(add_perms)
    if remove_perms: base_actions = [action for action in base_actions if action not in remove_perms]
    return [{"name": perm, "guard_name": guard_name, **add_data}
    for action_type in base_actions
    for perm in variate_perm(f"{action_type}##{sufix}")]

dashboard_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for dt in ('pos', 'crm', 'hrm', 'project', 'account')
    for perm in variate_perm(f'show##{dt}##dashboard')
]
invoice_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in (
        *[perm for at in ('copy', *STANDARD_PERMISSIONS,
                          'show', 'send', 'convert', 'duplicate')
          for perm in variate_perm(f'{at}##invoice')],
        *[p_perm for p_at in STANDARD_PERMISSIONS
          for p_perm in variate_perm(f'{p_at}##payment##invoice')],
    )
] + [{"name": perm, "guard_name": "web", **auto_now()} for perm in variate_perm('delete##invoice##product')]
+ [{"name": perm, "guard_name": "web", **auto_now()} for perm in variate_perm('invoice##report')],
user_permissions = defaulted_permissions('user')
language_permissions = [
    {
        "name": perm,
        "guard_name": "web",
        "created_at": now,
        "updated_at": now,
    } for perm in variate_perm('create##language')
]
role_permissions = defaulted_permissions('role')
permission_permissions = defaulted_permissions('permission')
settings_permissions = [
   {"name": perm, "guard_name": "web", **auto_now()}
   for at in STANDARD_PERMISSIONS
   for settings_type in ('company', 'print', 'business', 'stripe', 'system')
   for perm in variate_perm(f"{at}##{settings_type}##settings")
]
expense_permissions = defaulted_permissions(sufix='expense', add_perms=['view'])
constant_permissions = [
    {'name': perm, 'guard_name': 'web', **auto_now()}
    for at in STANDARD_PERMISSIONS
    for grp in ('unit', 'tax', 'category', 'custom##field', )
    for perm in variate_perm(f'{at}##constant##{grp}')
]
product_service_permissions = defaulted_permissions('product##&##service')
customer_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in (
        *[perm for at in (*STANDARD_PERMISSIONS, 'show')
          for perm in variate_perm(f'{at}##customer')],
        *[m_perm for m_at in ('payment', 'transaction', 'invoice')
          for m_perm in variate_perm(f'manage##customer##{m_at}')]
    )
]
vendor_permissions = [
    {'name': perm, 'guard_name': 'web', **auto_now()}
    for perm in (
        *[perm for at in (*STANDARD_PERMISSIONS, 'show')
          for perm in variate_perm(f'{at}##vendor')],
        *[m_perm for m_at in ('bill', 'payment', 'transaction')
          for m_perm in variate_perm(f'manage##vendor##{m_at}')]
    )
] + [{"name": perm, "guard_name": "web", **auto_now()} for perm in variate_perm('vendor##manage##bill')]
bank_account_permissions = defaulted_permissions('bank##acount')
bank_transfer_permissions = defaulted_permissions('bank##transfer')
transaction_permissions = [
	{
        "name": p,
        "guard_name": "web",
        "created_at": now,
        "updated_at": now,
	} for p in variate_perm('manage##transaction')
]
revenue_permissions = defaulted_permissions('revenue'),
bill_permissions = [
    {'name': perm, 'guard_name': 'web', **auto_now()}
    for at in (*STANDARD_PERMISSIONS, 'show', 'duplicate')
    for perm in variate_perm(f'{at}##bill')
] + [{
        "name": perm,
        "guard_name": "web",
        "created_at": now,
        "updated_at": now,
    } for perm in variate_perm('bill##report')]
payment_permissions = defaulted_permissions(sufix='payment', add_perms=['send'])
+ [{
    "name": p,
    "guard_name": "web",
    "created_at": now,
    "updated_at": now,
} for p in variate_perm('delete##bill##product')] + [
    {"name": p, "guard_name": "web", **auto_now()} 
    for at in ('create', 'delete') 
    for p in variate_perm(f'{at}##payment##bill')
] + defaulted_permissions(sufix='other##payment', remove_perms=['manage'])
order_permissions = [
	{
        "name": p,
        "guard_name": "web",
        "created_at": now,
        "updated_at": now,
	} for p in variate_perm('manage##order')
]
income_expense_permissions = [
    {
        "name": p,
        "guard_name": "web",
        **auto_now()
    } 
 for grp in ('income', 'expense', 'income##vs##expense')
 for p in variate_perm(f'{grp}##report')
]
stock_permissions = [
    {
        "name": p,
        "guard_name": "web",
        **auto_now()
    } for p in variate_perm('stock##report')
]
tax_permissions = [
    {
        "name": p,
        "guard_name": "web",
        **auto_now()
    } for p in variate_perm('tax##report')
]
profit_permissions = [
    {
        "name": p,
        "guard_name": "web",
        **auto_now()
    } for p in variate_perm('loss##&##profit##report')
]
credit_permissions = defaulted_permissions('credit##note')
debit_permissions = defaulted_permissions('debit##note')
proposal_permissions = defaulted_permissions(sufix='proposal', add_perms=['duplicate', 'show', 'send']) + [
        {'name': p, 'guard_name': 'web', **auto_now()}
        for at in ('delete', 'manage')
        for p in variate_perm(f'{at}proposal##product')
    ]
goal_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix in ("goal", "goal tracking", "goal type")
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##{suffix}")
]

assets_permission = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##assets")
]

statement_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("statement##report")
]

chart_permissions = (
    [
        {"name": perm, "guard_name": "web", **auto_now()}
        for action in STANDARD_PERMISSIONS
        for perm in variate_perm(f"{action}##chart of account")
    ] + [
        {"name": perm, "guard_name": "web", **auto_now()}
        for perm in variate_perm("view##grant##chart")
    ]
)

journal_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in (*STANDARD_PERMISSIONS, "show")
    for perm in variate_perm(f"{action}##journal entry")
]

sheet_permissions = (
    [
        {"name": perm, "guard_name": "web", **auto_now()}
        for perm in variate_perm("balance##sheet##report")
    ] + [
        {"name": perm, "guard_name": "web", **auto_now()}
        for action in (*STANDARD_PERMISSIONS, "view")
        for perm in variate_perm(f"{action}##timesheet")
    ]
)

ledger_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("ledger##report")
]

balance_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("trial balance##report")
]

client_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("client", STANDARD_PERMISSIONS),
        ("client##dashboard", ("manage")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

lead_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("lead", (*STANDARD_PERMISSIONS, "view", "move")),
        ("lead##call", ("create", "edit", "delete")),
        ("lead##email", ("create")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

stage_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##stage")
]

employee_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("employee", (*STANDARD_PERMISSIONS, "view")),
        ("employee##profile", ("manage", "show")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

department_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in (*STANDARD_PERMISSIONS, "view")
    for perm in variate_perm(f"{action}##department")
]

designation_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in (*STANDARD_PERMISSIONS, "view")
    for perm in variate_perm(f"{action}##designation")
]

branch_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##branch")
]

document_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix in ("document##type", "document")
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##{suffix}")
]

payslip_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("payslip##type", STANDARD_PERMISSIONS),
        ("pay##slip", ("manage", "create")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

allowance_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("allowance", ("create", "edit", "delete")),
        ("allowance##option", STANDARD_PERMISSIONS),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

commission_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("create", "edit", "delete")
    for perm in variate_perm(f"{action}##commission")
]

loan_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("loan##option", STANDARD_PERMISSIONS),
        ("loan", ("create", "edit", "delete")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

deduction_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("deduction##option", STANDARD_PERMISSIONS),
        ("saturation##deduction", ("create", "edit", "delete")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

overtime_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("create", "edit", "delete")
    for perm in variate_perm("create##overtime".replace("create##", "") and f"{action}##overtime")
]

salary_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("manage", "edit", "create")
    for perm in variate_perm(f"{action}##set##salary")
]

policy_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("manage", "create", "edit")
    for perm in variate_perm(f"{action}##company##policy")
]

appraisal_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in (*STANDARD_PERMISSIONS, "show")
    for perm in variate_perm(f"{action}##appraisal")
]

indicator_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in (*STANDARD_PERMISSIONS, "show")
    for perm in variate_perm(f"{action}##indicator")
]

training_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("training", (*STANDARD_PERMISSIONS, "show")),
        ("training##type", STANDARD_PERMISSIONS),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

trainer_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##trainer")
]

award_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix in ("award", "award##type")
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##{suffix}")
]

resignation_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##resignation")
]

travel_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##travel")
]

promotion_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in (*STANDARD_PERMISSIONS, 'view')
    for perm in variate_perm(f"{action}##promotion")
]

complaint_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##complaint")
]

warning_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##warning")
]

termination_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix in ("termination", "termination##type")
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##{suffix}")
]

job_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("job##application", (*STANDARD_PERMISSIONS, "show", "move")),
        ("job##application##skill", ("add",)),
        ("job##application##note", ("add", "delete")),
        ("job##on##board", ("manage",)),
        
        ("job##category", STANDARD_PERMISSIONS),
        ("job", (*STANDARD_PERMISSIONS, "show")),
        ("job##stage", STANDARD_PERMISSIONS),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

competencies_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##competencies")
]

question_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##custom##question")
]

interview_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("create", "edit", "delete", "show")
    for perm in variate_perm(f"{action}##interview##schedule")
]

estimation_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("create", "view", "edit", "delete")
    for perm in variate_perm(f"{action}##estimation")
]

holiday_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("manage", "create", "edit", "delete")
    for perm in variate_perm(f"{action}##holiday")
]

career_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("show##career")
]

meeting_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##meeting")
]

event_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##event")
]

transfer_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##transfer")
]

announcement_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("manage", "create", "edit")
    for perm in variate_perm(f"{action}##announcement")
]

leave_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("leave", STANDARD_PERMISSIONS),
        ("leave##type", STANDARD_PERMISSIONS),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

attendance_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##attendance")
]
report_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("manage##report")
]

project_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("project", (*STANDARD_PERMISSIONS, "share")),
        ("project##stage", STANDARD_PERMISSIONS),
        ("project##task", (*STANDARD_PERMISSIONS, "view")),
        ("project##task##stage", STANDARD_PERMISSIONS),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

milestone_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("create", "edit", "delete", "view")
    for perm in variate_perm(f"{action}##milestone")
]

activity_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in (
        *variate_perm("view##activity"),
        *variate_perm("view##CRM##activity"),
    )
]

bug_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("bug##report", (*STANDARD_PERMISSIONS, "move")),
        ("bug##status", STANDARD_PERMISSIONS),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

super_admin_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("manage##super##admin##dashboard")
]

plan_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("plan", ("manage", "create", "edit")),
        ("company##plan", ("manage",)),
        ("buy##plan", ("buy",)),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

coupon_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##coupon")
]

form_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("form##builder", STANDARD_PERMISSIONS),
        ("form##field", STANDARD_PERMISSIONS),
        ("form##response", ("view",)),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

performance_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in STANDARD_PERMISSIONS
    for perm in variate_perm(f"{action}##performance##type")
]

budget_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("create", "edit", "manage", "delete", "view")
    for perm in variate_perm(f"{action}##budget##plan")
]

warehouse_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in (*STANDARD_PERMISSIONS, "show")
    for perm in variate_perm(f"{action}##warehouse")
]

purchase_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("purchase", (*STANDARD_PERMISSIONS, "send")),
        ("payment##purchase", ("create", "delete")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

pos_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("manage##pos")
]

contract_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for suffix, actions in [
        ("contract##type", STANDARD_PERMISSIONS),
        ("contract", (*STANDARD_PERMISSIONS, "show")),
    ]
    for action in actions
    for perm in variate_perm(f"{action}##{suffix}")
]

barcode_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for perm in variate_perm("create##barcode")
]

webhook_permissions = [
    {"name": perm, "guard_name": "web", **auto_now()}
    for action in ("create", "edit", "delete")
    for perm in variate_perm(f"{action}##webhook")
]

ARR_PERMISSIONS = dashboard_permissions + invoice_permissions + user_permissions + language_permissions
+ role_permissions + permission_permissions + settings_permissions + expense_permissions + constant_permissions
+ product_service_permissions + customer_permissions + vendor_permissions + bank_account_permissions
+ bank_transfer_permissions + transaction_permissions + revenue_permissions + bill_permissions
+ payment_permissions + order_permissions + income_expense_permissions + stock_permissions + tax_permissions
+ profit_permissions + credit_permissions + debit_permissions + proposal_permissions + goal_permissions 
+ assets_permission + statement_permissions + chart_permissions + journal_permissions + sheet_permissions
+ ledger_permissions + balance_permissions + client_permissions + lead_permissions + stage_permissions 
+ employee_permissions + department_permissions + designation_permissions + branch_permissions
+ document_permissions + payslip_permissions + allowance_permissions + commission_permissions + loan_permissions
+ deduction_permissions + overtime_permissions + salary_permissions + policy_permissions + appraisal_permissions
+ indicator_permissions + training_permissions + trainer_permissions + award_permissions + resignation_permissions
+ travel_permissions + promotion_permissions + complaint_permissions + warning_permissions + termination_permissions
+ job_permissions + competencies_permissions + question_permissions + interview_permissions + estimation_permissions
+ holiday_permissions + career_permissions + meeting_permissions + event_permissions + transfer_permissions
+ announcement_permissions + leave_permissions + attendance_permissions + report_permissions + project_permissions
+ milestone_permissions + activity_permissions + bug_permissions + super_admin_permissions + plan_permissions
+ coupon_permissions + form_permissions + performance_permissions + budget_permissions + warehouse_permissions
+ purchase_permissions + pos_permissions + contract_permissions + barcode_permissions + webhook_permissions

# TODO IN PROGRESS

employee_friendly_permissions = {
    **{var: 'Managing employees data' for var in variate_perm('manage##employee')},
    **{var: 'Creating employee profile' for var in variate_perm('create##employee')}
}

DICT_FRIENDLY_PERMISSIONS = {
    **employee_friendly_permissions
}
# TODO IN PROGRESS

def assign_permissions(role, permissions):
    """
    Helper function to assign a list of permissions (each as dict with key 'name')
    to a role.
    """
    for perm_data in permissions:
        permission, created = Permission.objects.get_or_create(name=perm_data['name'])
        role.permissions.add(permission)

class UsersTableSeeder(BaseCommand):
    help = "Seeds the users, roles, permissions, and related data."

    @transaction.atomic
    def handle(self, *args, **options):
        now = timezone.now()

        # Insert permissions from ARR_PERMISSIONS (if provided)
        if ARR_PERMISSIONS:
            # Bulk create only those that do not already exist.
            existing_names = set(Permission.objects.values_list('name', flat=True))
            new_permissions = [
                Permission(name=perm['name'])
                for perm in ARR_PERMISSIONS
                if perm['name'] not in existing_names
            ]
            if new_permissions:
                Permission.objects.bulk_create(new_permissions)
                self.stdout.write("Inserted additional permissions.")

        # --- Super Admin ---
        super_admin_role = Role.objects.create(name='super admin', created_by=0)
        super_admin_permissions = [
            {'name': 'manage super admin dashboard'},
            {'name': 'manage user'},
            {'name': 'create user'},
            {'name': 'edit user'},
            {'name': 'delete user'},
            {'name': 'create language'},
            {'name': 'manage system settings'},
            {'name': 'manage stripe settings'},
            {'name': 'manage role'},
            {'name': 'create role'},
            {'name': 'edit role'},
            {'name': 'delete role'},
            {'name': 'manage permission'},
            {'name': 'create permission'},
            {'name': 'edit permission'},
            {'name': 'delete permission'},
            {'name': 'manage plan'},
            {'name': 'create plan'},
            {'name': 'edit plan'},
            {'name': 'manage order'},
            {'name': 'manage coupon'},
            {'name': 'create coupon'},
            {'name': 'edit coupon'},
            {'name': 'delete coupon'},
        ]
        assign_permissions(super_admin_role, super_admin_permissions)

        super_admin = User.objects.create(
            name='Super Admin',
            email='superadmin@example.com',
            password=make_password('1234'),
            type='super admin',
            lang='en',
            avatar='',
            created_by=0,
            email_verified_at=now,
        )
        # Assuming a many-to-many field 'roles' on User.
        super_admin.roles.add(super_admin_role)
        self.stdout.write("Created Super Admin user.")

        # --- Customer Role ---
        customer_role = Role.objects.create(name='customer', created_by=0)
        customer_permissions = [
            {'name': 'manage customer payment'},
            {'name': 'manage customer transaction'},
            {'name': 'manage customer invoice'},
            {'name': 'show invoice'},
            {'name': 'show proposal'},
            {'name': 'manage customer proposal'},
            {'name': 'show customer'},
        ]
        assign_permissions(customer_role, customer_permissions)
        self.stdout.write("Created Customer role.")

        # --- Vendor Role ---
        vendor_role = Role.objects.create(name='vendor', created_by=0)
        vendor_permissions = [
            {'name': 'vendor manage bill'},
            {'name': 'manage vendor bill'},
            {'name': 'manage vendor payment'},
            {'name': 'manage vendor transaction'},
            {'name': 'show vendor'},
            {'name': 'show bill'},
        ]
        assign_permissions(vendor_role, vendor_permissions)
        self.stdout.write("Created Vendor role.")

        # --- Company Role ---
        company_role = Role.objects.create(name='company', created_by=0)
        company_permissions = [
            {'name': 'show pos dashboard'},
            {'name': 'show crm dashboard'},
            {'name': 'show hrm dashboard'},
            {'name': 'show project dashboard'},
            {'name': 'show account dashboard'},
            {'name': 'manage user'},
            {'name': 'create user'},
            {'name': 'edit user'},
            {'name': 'delete user'},
            {'name': 'manage role'},
            {'name': 'create role'},
            {'name': 'edit role'},
            {'name': 'delete role'},
            {'name': 'manage permission'},
            {'name': 'create permission'},
            {'name': 'edit permission'},
            {'name': 'delete permission'},
            {'name': 'manage company settings'},
            {'name': 'manage business settings'},
            {'name': 'manage expense'},
            {'name': 'create expense'},
            {'name': 'edit expense'},
            {'name': 'delete expense'},
            {'name': 'manage invoice'},
            {'name': 'create invoice'},
            {'name': 'edit invoice'},
            {'name': 'delete invoice'},
            {'name': 'show invoice'},
            {'name': 'manage product & service'},
            {'name': 'create product & service'},
            {'name': 'delete product & service'},
            {'name': 'edit product & service'},
            {'name': 'manage constant tax'},
            {'name': 'create constant tax'},
            {'name': 'edit constant tax'},
            {'name': 'delete constant tax'},
            {'name': 'manage constant category'},
            {'name': 'create constant category'},
            {'name': 'edit constant category'},
            {'name': 'delete constant category'},
            {'name': 'manage constant unit'},
            {'name': 'create constant unit'},
            {'name': 'edit constant unit'},
            {'name': 'delete constant unit'},
            {'name': 'manage customer'},
            {'name': 'create customer'},
            {'name': 'edit customer'},
            {'name': 'delete customer'},
            {'name': 'show customer'},
            {'name': 'manage vendor'},
            {'name': 'create vendor'},
            {'name': 'edit vendor'},
            {'name': 'delete vendor'},
            {'name': 'show vendor'},
            {'name': 'manage bank account'},
            {'name': 'create bank account'},
            {'name': 'edit bank account'},
            {'name': 'delete bank account'},
            {'name': 'manage bank transfer'},
            {'name': 'create bank transfer'},
            {'name': 'edit bank transfer'},
            {'name': 'delete bank transfer'},
            {'name': 'manage revenue'},
            {'name': 'create revenue'},
            {'name': 'edit revenue'},
            {'name': 'delete revenue'},
            {'name': 'manage bill'},
            {'name': 'create bill'},
            {'name': 'edit bill'},
            {'name': 'delete bill'},
            {'name': 'show bill'},
            {'name': 'manage payment'},
            {'name': 'create payment'},
            {'name': 'edit payment'},
            {'name': 'delete payment'},
            {'name': 'delete invoice product'},
            {'name': 'delete bill product'},
            {'name': 'send invoice'},
            {'name': 'create payment invoice'},
            {'name': 'delete payment invoice'},
            {'name': 'send bill'},
            {'name': 'create payment bill'},
            {'name': 'delete payment bill'},
            {'name': 'income report'},
            {'name': 'expense report'},
            {'name': 'income vs expense report'},
            {'name': 'invoice report'},
            {'name': 'bill report'},
            {'name': 'stock report'},
            {'name': 'tax report'},
            {'name': 'loss & profit report'},
            {'name': 'manage transaction'},
            {'name': 'manage order'},
            {'name': 'manage credit note'},
            {'name': 'create credit note'},
            {'name': 'edit credit note'},
            {'name': 'delete credit note'},
            {'name': 'manage debit note'},
            {'name': 'create debit note'},
            {'name': 'edit debit note'},
            {'name': 'delete debit note'},
            {'name': 'duplicate invoice'},
            {'name': 'convert invoice'},
            {'name': 'duplicate bill'},
            {'name': 'manage proposal'},
            {'name': 'create proposal'},
            {'name': 'edit proposal'},
            {'name': 'delete proposal'},
            {'name': 'duplicate proposal'},
            {'name': 'show proposal'},
            {'name': 'send proposal'},
            {'name': 'delete proposal product'},
            {'name': 'manage goal'},
            {'name': 'create goal'},
            {'name': 'edit goal'},
            {'name': 'delete goal'},
            {'name': 'manage assets'},
            {'name': 'create assets'},
            {'name': 'edit assets'},
            {'name': 'delete assets'},
            {'name': 'statement report'},
            {'name': 'manage constant custom field'},
            {'name': 'create constant custom field'},
            {'name': 'edit constant custom field'},
            {'name': 'delete constant custom field'},
            {'name': 'manage chart of account'},
            {'name': 'create chart of account'},
            {'name': 'edit chart of account'},
            {'name': 'delete chart of account'},
            {'name': 'manage journal entry'},
            {'name': 'create journal entry'},
            {'name': 'edit journal entry'},
            {'name': 'delete journal entry'},
            {'name': 'show journal entry'},
            {'name': 'balance sheet report'},
            {'name': 'ledger report'},
            {'name': 'trial balance report'},
            {'name': 'manage client'},
            {'name': 'create client'},
            {'name': 'edit client'},
            {'name': 'delete client'},
            {'name': 'manage lead'},
            {'name': 'create lead'},
            {'name': 'view lead'},
            {'name': 'edit lead'},
            {'name': 'delete lead'},
            {'name': 'move lead'},
            {'name': 'create lead call'},
            {'name': 'edit lead call'},
            {'name': 'delete lead call'},
            {'name': 'create lead email'},
            {'name': 'manage pipeline'},
            {'name': 'create pipeline'},
            {'name': 'edit pipeline'},
            {'name': 'delete pipeline'},
            {'name': 'manage lead stage'},
            {'name': 'create lead stage'},
            {'name': 'edit lead stage'},
            {'name': 'delete lead stage'},
            {'name': 'convert lead to deal'},
            {'name': 'manage source'},
            {'name': 'create source'},
            {'name': 'edit source'},
            {'name': 'delete source'},
            {'name': 'manage label'},
            {'name': 'create label'},
            {'name': 'edit label'},
            {'name': 'delete label'},
            {'name': 'manage deal'},
            {'name': 'create deal'},
            {'name': 'view task'},
            {'name': 'create task'},
            {'name': 'edit task'},
            {'name': 'delete task'},
            {'name': 'edit deal'},
            {'name': 'view deal'},
            {'name': 'delete deal'},
            {'name': 'move deal'},
            {'name': 'create deal call'},
            {'name': 'edit deal call'},
            {'name': 'delete deal call'},
            {'name': 'create deal email'},
            {'name': 'manage stage'},
            {'name': 'create stage'},
            {'name': 'edit stage'},
            {'name': 'delete stage'},
            {'name': 'manage employee'},
            {'name': 'create employee'},
            {'name': 'view employee'},
            {'name': 'edit employee'},
            {'name': 'delete employee'},
            {'name': 'manage employee profile'},
            {'name': 'show employee profile'},
            {'name': 'manage department'},
            {'name': 'create department'},
            {'name': 'view department'},
            {'name': 'edit department'},
            {'name': 'delete department'},
            {'name': 'manage designation'},
            {'name': 'create designation'},
            {'name': 'view designation'},
            {'name': 'edit designation'},
            {'name': 'delete designation'},
            {'name': 'manage branch'},
            {'name': 'create branch'},
            {'name': 'edit branch'},
            {'name': 'delete branch'},
            {'name': 'manage document type'},
            {'name': 'create document type'},
            {'name': 'edit document type'},
            {'name': 'delete document type'},
            {'name': 'manage document'},
            {'name': 'create document'},
            {'name': 'edit document'},
            {'name': 'manage payslip type'},
            {'name': 'create payslip type'},
            {'name': 'edit payslip type'},
            {'name': 'delete payslip type'},
            {'name': 'create allowance'},
            {'name': 'edit allowance'},
            {'name': 'delete allowance'},
            {'name': 'create commission'},
            {'name': 'edit commission'},
            {'name': 'delete commission'},
            {'name': 'manage allowance option'},
            {'name': 'create allowance option'},
            {'name': 'edit allowance option'},
            {'name': 'delete allowance option'},
            {'name': 'manage loan option'},
            {'name': 'create loan option'},
            {'name': 'edit loan option'},
            {'name': 'delete loan option'},
            {'name': 'manage deduction option'},
            {'name': 'create deduction option'},
            {'name': 'edit deduction option'},
            {'name': 'delete deduction option'},
            {'name': 'create loan'},
            {'name': 'edit loan'},
            {'name': 'delete loan'},
            {'name': 'create saturation deduction'},
            {'name': 'edit saturation deduction'},
            {'name': 'delete saturation deduction'},
            {'name': 'create other payment'},
            {'name': 'edit other payment'},
            {'name': 'delete other payment'},
            {'name': 'create overtime'},
            {'name': 'edit overtime'},
            {'name': 'delete overtime'},
            {'name': 'manage set salary'},
            {'name': 'edit set salary'},
            {'name': 'manage pay slip'},
            {'name': 'create set salary'},
            {'name': 'create pay slip'},
            {'name': 'manage company policy'},
            {'name': 'create company policy'},
            {'name': 'edit company policy'},
            {'name': 'delete document'},
            {'name': 'manage appraisal'},
            {'name': 'create appraisal'},
            {'name': 'edit appraisal'},
            {'name': 'show appraisal'},
            {'name': 'delete appraisal'},
            {'name': 'manage goal tracking'},
            {'name': 'create goal tracking'},
            {'name': 'edit goal tracking'},
            {'name': 'delete goal tracking'},
            {'name': 'manage goal type'},
            {'name': 'create goal type'},
            {'name': 'edit goal type'},
            {'name': 'delete goal type'},
            {'name': 'manage indicator'},
            {'name': 'create indicator'},
            {'name': 'edit indicator'},
            {'name': 'show indicator'},
            {'name': 'delete indicator'},
            {'name': 'manage event'},
            {'name': 'create event'},
            {'name': 'edit event'},
            {'name': 'delete event'},
            {'name': 'manage meeting'},
            {'name': 'create meeting'},
            {'name': 'edit meeting'},
            {'name': 'delete meeting'},
            {'name': 'manage training'},
            {'name': 'create training'},
            {'name': 'edit training'},
            {'name': 'delete training'},
            {'name': 'show training'},
            {'name': 'manage trainer'},
            {'name': 'create trainer'},
            {'name': 'edit trainer'},
            {'name': 'delete trainer'},
            {'name': 'manage training type'},
            {'name': 'create training type'},
            {'name': 'edit training type'},
            {'name': 'delete training type'},
            {'name': 'manage award'},
            {'name': 'create award'},
            {'name': 'edit award'},
            {'name': 'delete award'},
            {'name': 'manage award type'},
            {'name': 'create award type'},
            {'name': 'edit award type'},
            {'name': 'delete award type'},
            {'name': 'manage resignation'},
            {'name': 'create resignation'},
            {'name': 'edit resignation'},
            {'name': 'delete resignation'},
            {'name': 'manage travel'},
            {'name': 'create travel'},
            {'name': 'edit travel'},
            {'name': 'delete travel'},
            {'name': 'manage promotion'},
            {'name': 'create promotion'},
            {'name': 'edit promotion'},
            {'name': 'delete promotion'},
            {'name': 'manage complaint'},
            {'name': 'create complaint'},
            {'name': 'edit complaint'},
            {'name': 'delete complaint'},
            {'name': 'manage warning'},
            {'name': 'create warning'},
            {'name': 'edit warning'},
            {'name': 'delete warning'},
            {'name': 'manage termination'},
            {'name': 'create termination'},
            {'name': 'edit termination'},
            {'name': 'delete termination'},
            {'name': 'manage termination type'},
            {'name': 'create termination type'},
            {'name': 'edit termination type'},
            {'name': 'delete termination type'},
            {'name': 'manage job application'},
            {'name': 'create job application'},
            {'name': 'show job application'},
            {'name': 'delete job application'},
            {'name': 'move job application'},
            {'name': 'add job application skill'},
            {'name': 'add job application note'},
            {'name': 'delete job application note'},
            {'name': 'manage job onBoard'},
            {'name': 'manage job category'},
            {'name': 'create job category'},
            {'name': 'edit job category'},
            {'name': 'delete job category'},
            {'name': 'manage job'},
            {'name': 'create job'},
            {'name': 'edit job'},
            {'name': 'show job'},
            {'name': 'delete job'},
            {'name': 'manage job stage'},
            {'name': 'create job stage'},
            {'name': 'edit job stage'},
            {'name': 'delete job stage'},
            {'name': 'Manage Competencies'},
            {'name': 'Create Competencies'},
            {'name': 'Edit Competencies'},
            {'name': 'Delete Competencies'},
            {'name': 'manage custom question'},
            {'name': 'create custom question'},
            {'name': 'edit custom question'},
            {'name': 'delete custom question'},
            {'name': 'create interview schedule'},
            {'name': 'edit interview schedule'},
            {'name': 'delete interview schedule'},
            {'name': 'show interview schedule'},
            {'name': 'create estimation'},
            {'name': 'view estimation'},
            {'name': 'edit estimation'},
            {'name': 'delete estimation'},
            {'name': 'edit holiday'},
            {'name': 'create holiday'},
            {'name': 'delete holiday'},
            {'name': 'manage holiday'},
            {'name': 'create overtime'},
            {'name': 'edit overtime'},
            {'name': 'delete overtime'},
            {'name': 'show career'},
            {'name': 'manage transfer'},
            {'name': 'create transfer'},
            {'name': 'edit transfer'},
            {'name': 'delete transfer'},
            {'name': 'manage announcement'},
            {'name': 'create announcement'},
            {'name': 'edit announcement'},
            {'name': 'delete announcement'},
            {'name': 'manage leave'},
            {'name': 'create leave'},
            {'name': 'edit leave'},
            {'name': 'delete leave'},
            {'name': 'manage leave type'},
            {'name': 'create leave type'},
            {'name': 'edit leave type'},
            {'name': 'delete leave type'},
            {'name': 'manage attendance'},
            {'name': 'create attendance'},
            {'name': 'edit attendance'},
            {'name': 'delete attendance'},
            {'name': 'manage report'},
            {'name': 'manage project'},
            {'name': 'create project'},
            {'name': 'view project'},
            {'name': 'edit project'},
            {'name': 'delete project'},
            {'name': 'share project'},
            {'name': 'create milestone'},
            {'name': 'edit milestone'},
            {'name': 'delete milestone'},
            {'name': 'view milestone'},
            {'name': 'view grant chart'},
            {'name': 'manage project stage'},
            {'name': 'create project stage'},
            {'name': 'edit project stage'},
            {'name': 'delete project stage'},
            {'name': 'view timesheet'},
            {'name': 'view expense'},
            {'name': 'manage project task'},
            {'name': 'create project task'},
            {'name': 'edit project task'},
            {'name': 'view project task'},
            {'name': 'delete project task'},
            {'name': 'view activity'},
            {'name': 'view CRM activity'},
            {'name': 'manage project task stage'},
            {'name': 'create project task stage'},
            {'name': 'edit project task stage'},
            {'name': 'delete project task stage'},
            {'name': 'manage timesheet'},
            {'name': 'create timesheet'},
            {'name': 'edit timesheet'},
            {'name': 'delete timesheet'},
            {'name': 'manage bug report'},
            {'name': 'create bug report'},
            {'name': 'edit bug report'},
            {'name': 'delete bug report'},
            {'name': 'move bug report'},
            {'name': 'manage bug status'},
            {'name': 'create bug status'},
            {'name': 'edit bug status'},
            {'name': 'delete bug status'},
            {'name': 'manage print settings'},
            {'name': 'manage company plan'},
            {'name': 'buy plan'},
            {'name': 'copy invoice'},
            {'name': 'manage plan'},
            {'name': 'manage form builder'},
            {'name': 'create form builder'},
            {'name': 'edit form builder'},
            {'name': 'delete form builder'},
            {'name': 'manage performance type'},
            {'name': 'create performance type'},
            {'name': 'edit performance type'},
            {'name': 'delete performance type'},
            {'name': 'manage form field'},
            {'name': 'create form field'},
            {'name': 'edit form field'},
            {'name': 'delete form field'},
            {'name': 'view form response'},
            {'name': 'manage budget plan'},
            {'name': 'create budget plan'},
            {'name': 'edit budget plan'},
            {'name': 'delete budget plan'},
            {'name': 'view budget plan'},
            {'name': 'manage warehouse'},
            {'name': 'create warehouse'},
            {'name': 'edit warehouse'},
            {'name': 'show warehouse'},
            {'name': 'delete warehouse'},
            {'name': 'manage purchase'},
            {'name': 'create purchase'},
            {'name': 'edit purchase'},
            {'name': 'show purchase'},
            {'name': 'delete purchase'},
            {'name': 'send purchase'},
            {'name': 'create payment purchase'},
            {'name': 'delete payment purchase'},
            {'name': 'manage pos'},
            {'name': 'manage contract type'},
            {'name': 'create contract type'},
            {'name': 'edit contract type'},
            {'name': 'delete contract type'},
            {'name': 'manage contract'},
            {'name': 'create contract'},
            {'name': 'edit contract'},
            {'name': 'delete contract'},
            {'name': 'show contract'},
            {'name': 'create barcode'},
            {'name': 'create webhook'},
            {'name': 'edit webhook'},
            {'name': 'delete webhook'},
        ]
        assign_permissions(company_role, company_permissions)
        self.stdout.write("Assigned permissions to Company role.")

        company = User.objects.create(
            name='company',
            email='company@example.com',
            password=make_password('1234'),
            type='company',
            default_pipeline=1,
            plan=1,
            lang='en',
            avatar='',
            created_by=1,
            email_verified_at=now,
        )
        company.roles.add(company_role)
        self.stdout.write("Created Company user.")

        # --- Accountant Role & User ---
        accountant_role = Role.objects.create(name='accountant', created_by=company.id)
        accountant_permissions = [
            {'name': 'show account dashboard'},
            {'name': 'manage expense'},
            {'name': 'create expense'},
            {'name': 'edit expense'},
            {'name': 'delete expense'},
            {'name': 'manage invoice'},
            {'name': 'create invoice'},
            {'name': 'edit invoice'},
            {'name': 'delete invoice'},
            {'name': 'show invoice'},
            {'name': 'convert invoice'},
            {'name': 'manage product & service'},
            {'name': 'create product & service'},
            {'name': 'delete product & service'},
            {'name': 'edit product & service'},
            {'name': 'manage constant tax'},
            {'name': 'create constant tax'},
            {'name': 'edit constant tax'},
            {'name': 'delete constant tax'},
            {'name': 'manage constant category'},
            {'name': 'create constant category'},
            {'name': 'edit constant category'},
            {'name': 'delete constant category'},
            {'name': 'manage constant unit'},
            {'name': 'create constant unit'},
            {'name': 'edit constant unit'},
            {'name': 'delete constant unit'},
            {'name': 'manage customer'},
            {'name': 'create customer'},
            {'name': 'edit customer'},
            {'name': 'delete customer'},
            {'name': 'show customer'},
            {'name': 'manage vendor'},
            {'name': 'create vendor'},
            {'name': 'edit vendor'},
            {'name': 'delete vendor'},
            {'name': 'show vendor'},
            {'name': 'manage bank account'},
            {'name': 'create bank account'},
            {'name': 'edit bank account'},
            {'name': 'delete bank account'},
            {'name': 'manage bank transfer'},
            {'name': 'create bank transfer'},
            {'name': 'edit bank transfer'},
            {'name': 'delete bank transfer'},
            {'name': 'manage revenue'},
            {'name': 'create revenue'},
            {'name': 'edit revenue'},
            {'name': 'delete revenue'},
            {'name': 'manage bill'},
            {'name': 'create bill'},
            {'name': 'edit bill'},
            {'name': 'delete bill'},
            {'name': 'show bill'},
            {'name': 'manage payment'},
            {'name': 'create payment'},
            {'name': 'edit payment'},
            {'name': 'delete payment'},
            {'name': 'delete invoice product'},
            {'name': 'delete bill product'},
            {'name': 'send invoice'},
            {'name': 'create payment invoice'},
            {'name': 'delete payment invoice'},
            {'name': 'send bill'},
            {'name': 'create payment bill'},
            {'name': 'delete payment bill'},
            {'name': 'income report'},
            {'name': 'expense report'},
            {'name': 'income vs expense report'},
            {'name': 'invoice report'},
            {'name': 'bill report'},
            {'name': 'stock report'},
            {'name': 'tax report'},
            {'name': 'loss & profit report'},
            {'name': 'manage transaction'},
            {'name': 'manage credit note'},
            {'name': 'create credit note'},
            {'name': 'edit credit note'},
            {'name': 'delete credit note'},
            {'name': 'manage debit note'},
            {'name': 'create debit note'},
            {'name': 'edit debit note'},
            {'name': 'delete debit note'},
            {'name': 'manage proposal'},
            {'name': 'create proposal'},
            {'name': 'edit proposal'},
            {'name': 'delete proposal'},
            {'name': 'duplicate proposal'},
            {'name': 'send proposal'},
            {'name': 'show proposal'},
            {'name': 'delete proposal product'},
            {'name': 'manage goal'},
            {'name': 'create goal'},
            {'name': 'edit goal'},
            {'name': 'delete goal'},
            {'name': 'manage assets'},
            {'name': 'create assets'},
            {'name': 'edit assets'},
            {'name': 'delete assets'},
            {'name': 'statement report'},
            {'name': 'manage constant custom field'},
            {'name': 'create constant custom field'},
            {'name': 'edit constant custom field'},
            {'name': 'delete constant custom field'},
            {'name': 'manage chart of account'},
            {'name': 'create chart of account'},
            {'name': 'edit chart of account'},
            {'name': 'delete chart of account'},
            {'name': 'manage journal entry'},
            {'name': 'create journal entry'},
            {'name': 'edit journal entry'},
            {'name': 'delete journal entry'},
            {'name': 'show journal entry'},
            {'name': 'balance sheet report'},
            {'name': 'ledger report'},
            {'name': 'trial balance report'},
            {'name': 'manage print settings'},
            {'name': 'manage budget plan'},
            {'name': 'create budget plan'},
            {'name': 'edit budget plan'},
            {'name': 'delete budget plan'},
            {'name': 'view budget plan'},
            {'name': 'create barcode'},
            {'name': 'create webhook'},
            {'name': 'edit webhook'},
            {'name': 'delete webhook'},
        ]
        assign_permissions(accountant_role, accountant_permissions)
        self.stdout.write("Created Accountant role and assigned permissions.")

        accountant = User.objects.create(
            name='accountant',
            email='accountant@example.com',
            password=make_password('1234'),
            type='accountant',
            default_pipeline=1,
            lang='en',
            avatar='',
            created_by=company.id,
            email_verified_at=now,
        )
        accountant.roles.add(accountant_role)
        self.stdout.write("Created Accountant user.")

        # --- Bank Account for the Company ---
        BankAccount.objects.create(
            holder_name='cash',
            bank_name='',
            account_number='-',
            opening_balance='0.00',
            contact_number='-',
            bank_address='-',
            created_by=company.id,
        )
        self.stdout.write("Created default bank account.")

        # --- Client Role & User ---
        client_role = Role.objects.create(name='client', created_by=company.id)
        client_permissions = [
            {'name': 'manage client dashboard'},
            {'name': 'manage bug report'},
            {'name': 'create bug report'},
            {'name': 'edit bug report'},
            {'name': 'delete bug report'},
            {'name': 'move bug report'},
            {'name': 'view deal'},
            {'name': 'manage deal'},
            {'name': 'manage project'},
            {'name': 'view project'},
            {'name': 'view grant chart'},
            {'name': 'view timesheet'},
            {'name': 'manage timesheet'},
            {'name': 'manage project task'},
            {'name': 'create project task'},
            {'name': 'edit project task'},
            {'name': 'view project task'},
            {'name': 'delete project task'},
            {'name': 'view activity'},
            {'name': 'view task'},
            {'name': 'manage pipeline'},
            {'name': 'manage lead stage'},
            {'name': 'manage label'},
            {'name': 'manage source'},
            {'name': 'move deal'},
            {'name': 'manage stage'},
            {'name': 'manage contract'},
            {'name': 'show contract'},
        ]
        assign_permissions(client_role, client_permissions)
        self.stdout.write("Created Client role and assigned permissions.")

        client = User.objects.create(
            name='client',
            email='client@example.com',
            password=make_password('1234'),
            type='client',
            default_pipeline=1,
            lang='en',
            avatar='',
            created_by=company.id,
            email_verified_at=now,
        )
        client.roles.add(client_role)
        self.stdout.write("Created Client user.")

        # --- Utility Calls ---
        Utility.employee_details(accountant.id, company.id)
        Utility.chart_of_account_type_data(company.id)
        Utility.chart_of_account_data(company)
        Utility.pipeline_lead_deal_stage(company.id)
        Utility.project_task_stages(company.id)
        Utility.labels(company.id)
        Utility.sources(company.id)
        Utility.job_stage(company.id)
        company.default_email()
        User.user_default_data()
        User.user_default_warehouse()
        GeneratedOfferLetter.default_offer_letter()
        ExperienceCertificate.default_exp_certificate()
        JoiningLetter.default_joining_letter()
        Noc.default_Noc_certificate()
        Utility.language_create()
        self.stdout.write("Utility functions executed.")

        # --- Insert Settings Data ---
        settings_data = [
            {
                'name': 'local_storage_validation',
                'value': 'jpg,jpeg,png,xlsx,xls,csv,pdf',
                'created_by': 1,
                'created_at': now,
                'updated_at': now,
            },
            {
                'name': 'wasabi_storage_validation',
                'value': 'jpg,jpeg,png,xlsx,xls,csv,pdf',
                'created_by': 1,
                'created_at': now,
                'updated_at': now,
            },
            {
                'name': 's3_storage_validation',
                'value': 'jpg,jpeg,png,xlsx,xls,csv,pdf',
                'created_by': 1,
                'created_at': now,
                'updated_at': now,
            },
            {
                'name': 'local_storage_max_upload_size',
                'value': 2048000,
                'created_by': 1,
                'created_at': now,
                'updated_at': now,
            },
            {
                'name': 'wasabi_max_upload_size',
                'value': 2048000,
                'created_by': 1,
                'created_at': now,
                'updated_at': now,
            },
            {
                'name': 's3_max_upload_size',
                'value': 2048000,
                'created_by': 1,
                'created_at': now,
                'updated_at': now,
            },
        ]
        settings_objs = [Settings(**data) for data in settings_data]
        Settings.objects.bulk_create(settings_objs)
        self.stdout.write("Settings data inserted.")

        self.stdout.write(self.style.SUCCESS("Users and related data seeded successfully."))