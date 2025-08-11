from django.contrib import admin
from django.urls import path, re_path

# Exports
from .Exports.views.balance_sheet_export_api_view import BalanceSheetExportAPIView
from .Exports.views.bill_export_api_view import BillExportAPIView
from .Exports.views.customer_export_api_view import CustomerExportAPIView
from .Exports.views.employee_export_api_view import EmployeeExportAPIView
from .Exports.views.leave_report_export_api_view import LeaveReportExportAPIView
from .Exports.views.payroll_export_api_view import PayrollExportAPIView
from .Exports.views.product_service_export_api_view import ProductServiceExportAPIView
from .Exports.views.product_stock_export_api_view import ProductStockExportAPIView
from .Exports.views.profit_loss_export_api_view import ProfitLossExportAPIView
from .Exports.views.proposal_export_api_view import ProposalExportAPIView
from .Exports.views.sales_report_export_api_view import SalesReportExportAPIView
from .Exports.views.task_report_export_api_view import TaskReportExportAPIView
from .Exports.views.transaction_export_api_view import TransactionExportAPIView
from .Exports.views.trial_balance_export_api_view import TrialBalanceExportAPIView
from .Exports.views.vendor_export_api_view import VendorExportAPIView

# Activity Controllers
from .Http.Controllers.activity.activity_controller import ActivityController
from .Http.Controllers.activity.appraisal_controller import AppraisalController
from .Http.Controllers.activity.award_controller import AwardController
from .Http.Controllers.activity.award_type_controller import AwardTypeController
from .Http.Controllers.activity.commission_controller import CommissionController
from .Http.Controllers.activity.complaint_controller import ComplaintController
from .Http.Controllers.activity.deal_controller import DealController
from .Http.Controllers.activity.event_controller import EventController
from .Http.Controllers.activity.lead_controller import LeadController
from .Http.Controllers.activity.lead_stage_controller import LeadStageController
from .Http.Controllers.activity.meeting_controller import MeetingController
from .Http.Controllers.activity.overtime_controller import OvertimeController
from .Http.Controllers.activity.performance_type_controller import PerformanceTypeController
from .Http.Controllers.activity.pos_controller import PosController

# Auth Controllers
from .Http.Controllers.auth.authenticated_session_controller import AuthenticatedSessionController
from .Http.Controllers.auth.confirmable_password_controller import ConfirmablePasswordController
from .Http.Controllers.auth.email_verification_notification_controller import EmailVerificationNotificationController
from .Http.Controllers.auth.email_verification_prompt_controller import EmailVerificationPromptController
from .Http.Controllers.auth.new_password_controller import NewPasswordController
from .Http.Controllers.auth.password_reset_link_controller import PasswordResetLinkController
from .Http.Controllers.auth.registered_user_controller import RegisteredUserController
from .Http.Controllers.auth.verify_email_controller import VerifyEmailController

# Bills Controllers
from .Http.Controllers.bills.allowance_controller import AllowanceController
from .Http.Controllers.bills.allowance_option_controller import AllowanceOptionController
from .Http.Controllers.bills.bank_transfer_controller import BankTransferController
from .Http.Controllers.bills.bank_transfer_payment_controller import BankTransferPaymentController
from .Http.Controllers.bills.benefit_payment_controller import BenefitPaymentController
from .Http.Controllers.bills.bill_controller import BillController
from .Http.Controllers.bills.budget_controller import BudgetController
from .Http.Controllers.bills.cashfree_controller import CashfreeController
from .Http.Controllers.bills.coupon_controller import CouponController
from .Http.Controllers.bills.credit_note_controller import CreditNoteController
from .Http.Controllers.bills.debit_note_controller import DebitNoteController
from .Http.Controllers.bills.deduction_option_controller import DeductionOptionController
from .Http.Controllers.bills.expense_controller import ExpenseController
from .Http.Controllers.bills.loan_controller import LoanController
from .Http.Controllers.bills.loan_option_controller import LoanOptionController
from .Http.Controllers.bills.invoice_controller import InvoiceController
from .Http.Controllers.bills.other_payment_controller import OtherPaymentController
from .Http.Controllers.bills.payment_controller import PaymentController
from .Http.Controllers.bills.payslip_controller import PayslipController
from .Http.Controllers.bills.payslip_type_controller import PayslipTypeController

# Bugs Controllers
from .Http.Controllers.bugs.bug_status_controller import BugStatusController

# Chart Controllers
from .Http.Controllers.charts.chart_of_account_controller import ChartOfAccountController

# Companies Controllers
from .Http.Controllers.companies.bank_account_controller import BankAccountController
from .Http.Controllers.companies.branch_controller import BranchController
from .Http.Controllers.companies.company_policy_controller import CompanyPolicyController
from .Http.Controllers.companies.department_controller import DepartmentController
from .Http.Controllers.companies.job_controller import JobController
from .Http.Controllers.companies.job_category_controller import JobCategoryController

# Configs Controllers
from .Http.Controllers.configs.api_controller import ApiController
from .Http.Controllers.configs.permission_controller import PermissionController
from .Http.Controllers.configs.pipeline_controller import PipelineController

# Individuals Controllers
from .Http.Controllers.individuals.employee_attendance_controller import EmployeeAttendanceController
from .Http.Controllers.individuals.client_controller import ClientController
from .Http.Controllers.individuals.competencies_controller import CompetenciesController
from .Http.Controllers.individuals.customer_controller import CustomerController
from .Http.Controllers.individuals.employee_controller import EmployeeController
from .Http.Controllers.individuals.designation_controller import DesignationController
from .Http.Controllers.individuals.job_application_controller import JobApplicationController
from .Http.Controllers.individuals.job_stage_controller import JobStageController
from .Http.Controllers.individuals.user_controller import UserController

# Info Controllers
from .Http.Controllers.info.announcement_controller import AnnouncementController
from .Http.Controllers.info.notification_templates_controller import NotificationTemplatesController

# Planning Controllers
from .Http.Controllers.planning.contract_controller import ContractController
from .Http.Controllers.planning.contract_type_controller import ContractTypeController
from .Http.Controllers.planning.goal_controller import GoalController
from .Http.Controllers.planning.goal_tracking_controller import GoalTrackingController
from .Http.Controllers.planning.goal_type_controller import GoalTypeController
from .Http.Controllers.planning.holiday_controller import HolidayController
from .Http.Controllers.planning.indicator_controller import IndicatorController
from .Http.Controllers.planning.interview_schedule_controller import InterviewScheduleController
from .Http.Controllers.planning.leave_controller import LeaveController
from .Http.Controllers.planning.leave_type_controller import LeaveTypeController
from .Http.Controllers.planning.plan_controller import PlanController
from .Http.Controllers.planning.plan_request_controller import PlanRequestController
from .Http.Controllers.planning.project_controller import ProjectController
from .Http.Controllers.planning.promotion_controller import PromotionController

# Products Controllers
from .Http.Controllers.products.product_service_controller import ProductServiceController
from .Http.Controllers.products.product_service_category_controller import ProductServiceCategoryController
from .Http.Controllers.products.product_service_unit_controller import ProductServiceUnitController
from .Http.Controllers.products.product_stock_controller import ProductStockController

# Shapes Controllers
from .Http.Controllers.shapes.asset_controller import AssetController
from .Http.Controllers.shapes.custom_field_controller import CustomFieldController
from .Http.Controllers.shapes.custom_question_controller import CustomQuestionController
from .Http.Controllers.shapes.dashboard_controller import DashboardController
from .Http.Controllers.shapes.document_controller import DocumentController
from .Http.Controllers.shapes.document_upload_controller import DocumentUploadController
from .Http.Controllers.shapes.email_template_controller import EmailTemplateController
from .Http.Controllers.shapes.form_builder_controller import FormBuilderController
from .Http.Controllers.shapes.journal_entry_controller import JournalEntryController
from .Http.Controllers.shapes.label_controller import LabelController
from .Http.Controllers.shapes.language_controllerr import LanguageController

# SSR Controllers
from .Http.Controllers.ssr.ai_template_controller import AiTemplateController

# Imports
from .Imports.views.attendance_import_api_view import AttendanceImportAPIView
from .Imports.views.customer_import_api_view import CustomerImportAPIView
from .Imports.views.product_service_import_api_view import ProductServiceImportAPIView
from .Imports.views.vendor_import_api_view import VendorImportAPIView

# Mail
from .Mail.views.common_email_template_api_view import CommonEmailTemplateAPIView
from .Mail.views.send_deal_email_api_view import SendDealEmailAPIView
from .Mail.views.send_lead_email_api_view import SendLeadEmailAPIView
from .Mail.views.test_mail_api_view import TestMailAPIView

# Landing Page
from .Modules.LandingPage.Http.Controllers.custom_page_controller import CustomPageController
from .Modules.LandingPage.Http.Controllers.discover_controller import DiscoverController
from .Modules.LandingPage.Http.Controllers.faq_controller import FaqController
from .Modules.LandingPage.Http.Controllers.features_controller import FeaturesController
from .Modules.LandingPage.Http.Controllers.home_controller import HomeController
from .Modules.LandingPage.Http.Controllers.join_us_controller import JoinUsController
from .Modules.LandingPage.Http.Controllers.landing_page_controller import LandingPageController
from .Modules.LandingPage.Http.Controllers.pricing_plan_controller import PricingPlanController
from .Modules.LandingPage.Http.Controllers.screenshots_controller import ScreenshotsController
from .Modules.LandingPage.Http.Controllers.testimonials_controller import TestimonialsController

from .verification import system_check_api

# ------------------------------------------------------------------
# Consolidated URL definitions
# ------------------------------------------------------------------

allowance_controller = AllowanceController.as_view()

urlpatterns = [
    # --------------------------------------------------------------------------------
    # Admin
    # --------------------------------------------------------------------------------
    path('admin/', admin.site.urls, name='admin_site'),

    # --------------------------------------------------------------------------------
    # System/health check
    # --------------------------------------------------------------------------------
    path('system-check-api/', system_check_api, name='system_check_api'),

    # --------------------------------------------------------------------------------
    # Export
    # --------------------------------------------------------------------------------
    path('exports/balance-sheet/', BalanceSheetExportAPIView.as_view(), name='export_balance_sheet'),
    path('exports/bills/', BillExportAPIView.as_view(), name='bill_export'),
    path('exports/stock-report/', ProductStockExportAPIView.as_view(), name='product_stock_export'),
    path('exports/customers/', CustomerExportAPIView.as_view(), name='customer_export'),
    path('exports/employees/', EmployeeExportAPIView.as_view(), name='employee_export'),
    path('exports/payroll/', PayrollExportAPIView.as_view(), name='payroll_export'),
    path('exports/products/', ProductServiceExportAPIView.as_view(), name='product_export'),
    path('exports/leaves/', LeaveReportExportAPIView.as_view(), name='leave_report_export'),
    path('exports/profit-loss/', ProfitLossExportAPIView.as_view(), name='profit_loss_export'),
    path('exports/proposals/', ProposalExportAPIView.as_view(), name='proposal_export'),
    path('exports/sales-report/', SalesReportExportAPIView.as_view(), name='sales_report_export'),
    path('exports/projects/<uuid:project_id>/tasks/', TaskReportExportAPIView.as_view(), name='task_report_export'),
    path('exports/transactions/', TransactionExportAPIView.as_view(), name='transaction_export'),
    path('exports/trial-balance/', TrialBalanceExportAPIView.as_view(), name='trial_balance_export'),
    path('exports/vendors/', VendorExportAPIView.as_view(), name='vendor_export'),

    # --------------------------------------------------------------------------------
    # Import
    # --------------------------------------------------------------------------------
    path('imports/attendance/', AttendanceImportAPIView.as_view(), name='attendance_import'),
    path('imports/customers/', CustomerImportAPIView.as_view(), name='customer_import'),
    path('imports/products/', ProductServiceImportAPIView.as_view(), name='product_import'),
    path('imports/vendors/', VendorImportAPIView.as_view(), name='vendor_import'),

    # --------------------------------------------------------------------------------
    # Mail
    # --------------------------------------------------------------------------------
    path('emails/common-template/', CommonEmailTemplateAPIView.as_view(), name='common_email'),
    path('emails/deals/', SendDealEmailAPIView.as_view(), name='send_deal'),
    path('emails/leads/', SendLeadEmailAPIView.as_view(), name='send_lead'),
    path('emails/test/', TestMailAPIView.as_view(), name='test_email'),

    # Feature sub-routes
    path('discover/feature/create/', DiscoverController.discover_create, name='discover_create'),
    path('discover/feature/store/', DiscoverController.discover_store, name='discover_store'),
    path('discover/feature/<int:key>/edit/', DiscoverController.discover_edit, name='discover_edit'),
    path('discover/feature/<int:key>/update/', DiscoverController.discover_update, name='discover_update'),
    path('discover/feature/<int:key>/delete/', DiscoverController.discover_delete, name='discover_delete'),

    # Sub-routes for FAQ items
    path('faq/create/', FaqController.faq_create, name='faq_item_create'), # * MATCHED
    path('faq/store/', FaqController.faq_store, name='faq_item_store'), # * MATCHED
    path('faq/edit/<int:key>', FaqController.faq_edit, name='faq_item_edit'), # * MATCHED
    path('faq/update/<int:key>', FaqController.faq_update, name='faq_item_update'), # * MATCHED
    path('faq/delete/<int:key>', FaqController.faq_delete, name='faq_item_delete'), # * MATCHED

    # Feature sub-routes
    path('features/feature/create/', FeaturesController.feature_create, name='feature_create'),
    path('features/feature/store/', FeaturesController.feature_store, name='feature_store'),
    path('features/feature/edit/<int:key>/', FeaturesController.feature_edit, name='feature_edit'),
    path('features/feature/update/<int:key>/', FeaturesController.feature_update, name='feature_update'),
    path('features/feature/delete/<int:key>/', FeaturesController.feature_delete, name='feature_delete'),
    path('features/feature/highlight/create/', FeaturesController.feature_highlight_create, name='feature_highlight_create'),

    # Additional “other features”
    path('features/others/create/', FeaturesController.features_create, name='other_features_create'),
    path('features/others/store/', FeaturesController.features_store, name='other_features_store'),
    path('features/others/edit/<int:key>/', FeaturesController.features_edit, name='other_features_edit'),
    path('features/others/update/<int:key>/', FeaturesController.features_update, name='other_features_update'),
    path('features/others/delete/<int:key>/', FeaturesController.features_delete, name='other_features_delete'),

    # Screenshots “entry” sub-routes
    path('screenshots/entry/create/', ScreenshotsController.screenshots_create, name='screenshots_entry_create'),
    path('screenshots/entry/store/', ScreenshotsController.screenshots_store, name='screenshots_entry_store'),
    path('screenshots/entry/edit/<int:key>/', ScreenshotsController.screenshots_edit, name='screenshots_entry_edit'),
    path('screenshots/entry/update/<int:key>/', ScreenshotsController.screenshots_update, name='screenshots_entry_update'),
    path('screenshots/entry/delete/<int:key>/', ScreenshotsController.screenshots_delete, name='screenshots_entry_delete'),

    # Testimonials “entry” sub-routes
    path('testimonials/entry/create/', TestimonialsController.testimonials_create, name='testimonials_entry_create'),
    path('testimonials/entry/store/', TestimonialsController.testimonials_store, name='testimonials_entry_store'),
    path('testimonials/entry/edit/<int:key>/', TestimonialsController.testimonials_edit, name='testimonials_entry_edit'),
    path('testimonials/entry/update/<int:key>/', TestimonialsController.testimonials_update, name='testimonials_entry_update'),
    path('testimonials/entry/delete/<int:key>/', TestimonialsController.testimonials_delete, name='testimonials_entry_delete'),
    
    # Activity Controller
    path('activity/', ActivityController, name='activity'), # ? Missing + No endpoints in Laravel
    
    # AiTemplateController
    path('generate/response/', AiTemplateController.ai_generate, name='generate.response'), # * MATCHED
    path('grammar/response/', AiTemplateController.grammar_process, name='grammar.response'), # * MATCHED
    path('grammar/<str:template>/', AiTemplateController.grammar, name='grammar'), # * MATCHED
    path('generate/<str:template_name>/', AiTemplateController.create, name='generate'), # * MATCHED
    path('generate/keywords/<uuid:id>/', AiTemplateController.get_keywords, name='generate.keywords'), # * MATCHED
        
    # AllowanceController
    path('allowance/store/', allowance_controller, name='allowance_store'), # * MATCHED
    path('allowance/create/<uuid:employee_id>/', allowance_controller, name='allowance_create'), # * MATCHED
    path('allowance/edit/<uuid:allowance_id>/', allowance_controller, name='allowance_edit'), # * NEW
    path('allowance/show/<uuid:allowance_id>/', allowance_controller, name='allowance_show'), # * NEW
    path('allowance/destroy/<uuid:allowance_id>/', allowance_controller, name='allowance_destroy'), # * NEW
    path('allowance/update/<uuid:allowance_id>/', allowance_controller, name='allowance_update'), # * NEW
    
    # AllowanceOptionController
    path('allowanceoption/', AllowanceOptionController.index, name='allowanceoption_index'), # * MATCHED
    path('allowanceoption/create/', AllowanceOptionController.create, name='allowanceoption_create'), # * NEW
    path('allowanceoption/store/', AllowanceOptionController.store, name='allowanceoption_store'), # * NEW
    path('allowanceoption/<uuid:allowanceoption_id>/delete/', AllowanceOptionController.destroy, name='allowanceoption_destroy'), # * NEW
    path('allowanceoption/<uuid:allowanceoption_id>/edit/', AllowanceOptionController.edit, name='allowanceoption_edit'), # * NEW
    path('allowanceoption/<uuid:allowanceoption_id>/show/', AllowanceOptionController.show, name='allowanceoption_show'), # * NEW
    path('allowanceoption/<uuid:allowanceoption_id>/update/', AllowanceOptionController.update, name='allowanceoption_update'), # * NEW
    
    # AnnouncementsController
    path('announcement/', AnnouncementController.index, name='announcement_index'), # * MATCHED
    path('announcement/create/', AnnouncementController.create, name='announcement_create'), # * NEW
    path('announcement/getdepartment/', AnnouncementController.get_department, name='announcement_getdepartment'), # * MATCHED
    path('announcement/getemployee/', AnnouncementController.get_employee, name='announcement_getemployee'), # * MATCHED
    path('announcement/store/', AnnouncementController.store, name='announcement_store'), # * NEW
    path('announcement/<uuid:announcement_id>/destroy/', AnnouncementController.destroy, name='announcement_destroy'), # * NEW
    path('announcement/<uuid:announcement_id>/edit/', AnnouncementController.edit, name='announcement_edit'), # * NEW
    path('announcement/<uuid:announcement_id>/show/', AnnouncementController.show, name='announcement_show'), # * NEW
    path('announcement/<uuid:announcement_id>/update/', AnnouncementController.update, name='announcement_update'), # * NEW
    
    # ApiController
    path('add-tracker/', ApiController.AddTrackerView.as_view(), name='api_add_tracker'), # * MATCHED
    path('get-projects/', ApiController.GetProjectsView.as_view(), name='api_get_projects'), # * MATCHED
    path('login/', ApiController.LoginView.as_view(), name='api_login'), # * MATCHED
    path('logout/', ApiController.LogoutView.as_view(), name='api_logout'), # * MATCHED
    path('upload-photos/', ApiController.UploadImageView.as_view(), name='api_upload_image'), # * MATCHED
    path('stop-tracker/', ApiController.stop_tracker, name="api_stop_tracker"), # * MATCHED
    
    # AppraisalController
    path('appraisals/', AppraisalController.index, name='appraisal_emp_by_star'), # * MATCHED
    path('appraisals1/', AppraisalController.emp_by_star1, name='appraisal_emp_by_star1'), # * MATCHED
    path('getemployee/', AppraisalController.get_employee, name='appraisal_get_employee'), # * MATCHED
    path('appraisals/create/', AppraisalController.create, name='appraisal_create'), # * NEW
    path('appraisals/store/', AppraisalController.store, name='appraisal_store'), # * NEW
    path('appraisals/<uuid:appraisal_id>/delete/', AppraisalController.destroy, name='appraisal_delete'), # * NEW
    path('appraisals/<uuid:appraisal_id>/edit/', AppraisalController.edit, name='appraisal_edit'), # * NEW
    path('appraisals/<uuid:appraisal_id>/show/', AppraisalController.show, name='appraisal_show'), # ! REPLACING /appraisal
    path('appraisals/<uuid:appraisal_id>/update/', AppraisalController.update, name='appraisal_update'), # * NEW
    
    # AssetsController
    path('account-assets/', AssetController.index, name='account-assets.index'), # * MATCHED
    path('account-assets/create/', AssetController.create, name='account-assets.create'), # * NEW
    path('account-assets/store/', AssetController.store, name='account-assets.store'), # * NEW
    path('account-assets/<uuid:id>/delete/', AssetController.destroy, name='account-assets.delete'), # * NEW
    path('account-assets/<uuid:id>/edit/', AssetController.edit, name='account-assets.edit'), # * NEW
    path('account-assets/<uuid:id>/show/', AssetController.show, name='account-assets.show'), # * NEW
    path('account-assets/<uuid:id>/update/', AssetController.update, name='account-assets.update'), # * NEW
    
    # EmployeeAttendanceController
    path('EmployeeAttendance/', EmployeeAttendanceController.index, name='EmployeeAttendance.index'), # * MATCHED
    path('EmployeeAttendance/bulkattendance', EmployeeAttendanceController.bulk_attendance, name='EmployeeAttendance.bulkAttendance'), # * MATCHED
    path('EmployeeAttendance/clock/', EmployeeAttendanceController.attendance, name='EmployeeAttendance.attendance'), # * NEW
    path('EmployeeAttendance/create/', EmployeeAttendanceController.create, name='EmployeeAttendance.create'), # * NEW
    path('EmployeeAttendance/show/', EmployeeAttendanceController.show, name='EmployeeAttendance.show'), # * MATCHED
    path('EmployeeAttendance/store/', EmployeeAttendanceController.store, name='EmployeeAttendance.store'), # * NEW
    path('attendance/import', EmployeeAttendanceController.import_data, name='EmployeeAttendance.import_data'), # ! REPLACED import/attendance
    path('EmployeeAttendance/bulk/store/', EmployeeAttendanceController.bulk_attendance_data, name='EmployeeAttendance.bulkAttendanceData'), # * NEW
    path('attendance/import/file', EmployeeAttendanceController.import_file, name='EmployeeAttendance.importFile'), #  ! REPLACE import/attendance/FILE
    path('EmployeeAttendance/<uuid:id>/delete/', EmployeeAttendanceController.destroy, name='EmployeeAttendance.destroy'), # * NEW
    path('EmployeeAttendance/<uuid:id>/edit/', EmployeeAttendanceController.edit, name='EmployeeAttendance.edit'), # * MATCHED
    path('EmployeeAttendance/<uuid:id>/update/', EmployeeAttendanceController.update, name='EmployeeAttendance.update'), # * NEW

    # AuthenticatedSessionController
    path('login/', AuthenticatedSessionController.store, name='login'), # * MATCHED
    path('logout/', AuthenticatedSessionController.destroy, name='logout'), # * MATCHED
    path('forgot-password/vendor/', AuthenticatedSessionController.show_vendor_link_request_form, name='vendor_password_reset'), # * NEW
    path('reset-password/vendor', AuthenticatedSessionController.update_vendor_password, name='update_vendor_password'), # * NEW
    path('forgot-password/<str:lang>', AuthenticatedSessionController.show_link_request_form, name='forgot_password'), # * MATCHED
    path('login/<str:lang>', AuthenticatedSessionController.customer_login, name='customer_login'), # * MATCHED
    path('reset-password/<str:token>/', AuthenticatedSessionController.show_reset_form, name='shared_reset_password'), # * NEW
    path('forgot-password/customer/create/', AuthenticatedSessionController.post_customer_email, name='customer_password_reset_post'), # * NEW
    path('forgot-password/vendor/create/', AuthenticatedSessionController.post_vendor_email, name='vendor_password_reset_post'), # * NEW
    path('reset-password/customer/create/', AuthenticatedSessionController.update_customer_password, name='update_customer_password'), # * NEW
    path('forgot-password/customer/<str:lang>', AuthenticatedSessionController.show_customer_link_request_form, name='customer_password_reset'), # * NEW
    path('login/vendor/<str:lang>', AuthenticatedSessionController.vendor_login, name='vendor_login'), # * NEW
    path('reset-password/customer/<str:token>/', AuthenticatedSessionController.get_customer_password, name='customer_reset_password'), # * NEW
    path('reset-password/vendor/<str:token>/', AuthenticatedSessionController.get_vendor_password, name='vendor_reset_password'), # * NEW
    
    # AwardController
    path('award/', AwardController.index, name='award.index'), # * MATCHED
    path('award/create/', AwardController.create, name='award.create'), # * NEW
    path('award/store/', AwardController.store, name='award.store'), # * NEW
    path('award/<int:pk>/delete/', AwardController.destroy, name='award.destroy'), # * NEW
    path('award/<int:pk>/edit/', AwardController.edit, name='award.edit'), # * NEW
    path('award/<int:pk>/update/', AwardController.update, name='award.update'), # * NEW
    
    # AwardTypeController
    path('awardtype/', AwardTypeController.index, name='awardtype.index'), # * MATCHED
    path('awardtype/create/', AwardTypeController.create, name='awardtype.create'), # * NEW
    path('awardtype/store/', AwardTypeController.store, name='awardtype.store'), # * NEW
    path('awardtype/<uuid:id>/delete/', AwardTypeController.destroy, name='awardtype.destroy'), # * NEW
    path('awardtype/<uuid:id>/edit/', AwardTypeController.edit, name='awardtype.edit'), # * NEW
    path('awardtype/<uuid:id>/show/', AwardTypeController.show, name='awardtype.show'), # * NEW
    path('awardtype/<uuid:id>/update/', AwardTypeController.update, name='awardtype.update'), # * NEW
    
    # BankAccountController
    path('bank-account/', BankAccountController().index, name='bank_account_index'), # * MATCHED
    path('bank-account/create', BankAccountController().create, name='bank_account_create'), # * NEW
    path('bank-account/store', BankAccountController().store, name='bank_account_store'), # * NEW
    path('bank-account/delete/<uuid:id>', BankAccountController().destroy, name='bank_account_delete'), # * NEW
    path('bank-account/edit/<uuid:id>', BankAccountController().edit, name='bank_account_edit'), # * NEW
    path('bank-account/update/<uuid:id>', BankAccountController().update, name='bank_account_update'), # * NEW
    
    # BankTransferController
    path('bank-transfer/', BankTransferController.as_view(), name='bank_transfer_view'), # * MATCHED
    path('bank-transfer/create', BankTransferController.create, name='bank_transfer_create'), # * NEW
    path('bank-transfer/index', BankTransferController.index, name='bank_transfer_index'), # * NEW
    path('bank-transfer/store', BankTransferController.store, name='bank_transfer_store'), # * NEW
    path('bank-transfer/delete/<uuid:id>', BankTransferController.destroy, name='bank_transfer_delete'), # * NEW
    path('bank-transfer/edit/<uuid:id>', BankTransferController.edit, name='bank_transfer_edit'), # * NEW
    path('bank-transfer/update/<uuid:id>', BankTransferController.update, name='bank_transfer_update'), # * NEW
    
    # BankTransferPaymentController
    path("invoice/action", BankTransferPaymentController.invoice_action, name="invoice_action"), # * MATCHED
    path("payment/plan", BankTransferPaymentController.plan_pay_with_bank, name="plan_pay_with_bank"), # ! REPLACED plan-pay-with-bank
    path("payment/customer", BankTransferPaymentController.customer_pay_with_bank, name="invoice_pay_with_bank"), # ! REPLACED customer-pay-with-bank
    path("order/<uuid:id>", BankTransferPaymentController.order_destroy, name="order_destroy"), # * MATCHED
    path("invoice/<uuid:id>/change-status", BankTransferPaymentController.invoice_change_status, name="invoice_change_status"), # * MATCHED
    path("order/<uuid:id>/action", BankTransferPaymentController.action, name="order_action"), # * MATCHED
    path("order/<uuid:order_id>/changeaction", BankTransferPaymentController.change_status, name="order_change_status"), # * MATCHED
    
    # BenefitPaymentController
    path('benefit/callback', BenefitPaymentController.call_back, name='benefit_call_back'), # ! REPLACED call_back
    path('benefit/invoice', BenefitPaymentController.invoice_pay_with_benefit, name='benefit_invoice_pay'), # ! REPLACED invoice-with-benefit
    path('benefit/payment-initiate', BenefitPaymentController.initiate_payment, name='benefit_initiate_payment'), # ! REPLACED /payment/initiate
    path('benefit/invoice/<uuid:invoice_id>/<str:amount>', BenefitPaymentController.get_invoice_payment_status, name='benefit_invoice_status'), # ! REPLACED /invoice/benefit/{invoice_id}/{amount}
    
    # BillController
    path('bill/', BillController.as_view(), name='bill.view'), # * MATCHED
    path('bill/export', BillController.export, name='bill.export'), # ! REPLACED export/bill
    path('bill/index/', BillController.index, name='bill.index'), # * MATCHED
    path('bill/items/', BillController.items, name='bill.items'), # * MATCHED
    path('bill/product/', BillController.product, name='bill.product'), # * MATCHED
    path('bill/vendor/', BillController.vendor, name='bill.vendor'), # * MATCHED
    path('bill/product/destroy/', BillController.product_destroy, name='bill.product.destroy'), # * MATCHED
    path('bill/template/setting/', BillController.save_bill_template_settings, name='bill.template.setting'), # * MATCHED
    path('bill/create/<uuid:cid>/', BillController.create, name='bill.create'), # * MATCHED
    path('bill/pdf/<uuid:id>/', BillController.bill, name='bill.pdf'), # * MATCHED
    path('bill/vendor/<uuid:id>', BillController.vendor_bill_show, name="bill.vendor.link.copy"), # ! REPLACED /vendor/bill/{id}/
    path('bill/<uuid:id>/duplicate/', BillController.duplicate, name='bill.duplicate'), # * MATCHED
    path('bill/<uuid:id>/payment/', BillController.payment, name='bill.payment'), # * MATCHED (GET)
    path('bill/<uuid:id>/payment/', BillController.create_payment, name='bill.payment.create'), # * MATCHED (POST)
    path('bill/<uuid:id>/resend/', BillController.resend, name='bill.resend'), # * MATCHED 
    path('bill/<uuid:id>/send/', BillController.send, name='bill.send'), # * MATCHED
    path('bill/preview/<str:template>/<str:color>/', BillController.preview_bill, name='bill.preview'), # * MATCHED
    path('bill/<uuid:id>/payment/<uuid:pid>/destroy/', BillController.payment_destroy, name='bill.payment.destroy'), # * MATCHED
    path('bill/<uuid:id>/shipping/print/', BillController.shipping_display, name='bill.shipping.print'), # * MATCHED
    
    # BranchController
    path('branch/', BranchController.index, name='branch_index'), # * MATCHED
    path('branch/create/', BranchController.create, name='branch_create'), # * NEW
    path('branch/getdepartment/', BranchController.get_department, name='branch_getdepartment'), # * NEW
    path('branch/getemployee/', BranchController.get_employee, name='branch_getemployee'), # * NEW
    path('branch/store/', BranchController.store, name='branch_store'), # * NEW
    path('branch/<uuid:branch_id>/show/', BranchController.show, name='branch_show'), # * NEW
    path('branch/<uuid:branch_id>/edit/', BranchController.edit, name='branch_edit'), # * NEW
    path('branch/<uuid:branch_id>/update/', BranchController.update, name='branch_update'), # * NEW
    path('branch/<uuid:branch_id>/destroy/', BranchController.destroy, name='branch_destroy'), # * NEW
    
    # BudgetController
    path('budget/', BudgetController.as_view(), name='budget_index'), # * MATCHED GET list, POST create
    path('budget/create/', BudgetController.as_view({'get': 'create'}), name='budget_create'), # * NEW
    path('budget/<uuid:id>/', BudgetController.as_view({'get': 'show', 'put': 'update', 'delete': 'destroy'}), name='budget_detail'), # * NEW
    path('budget/<uuid:id>/edit/', BudgetController.as_view({'get': 'edit'}), name='budget_edit'), # * NEW
    
    # BugStatusController
    path('bugstatus/', BugStatusController().index, name='bugstatus_index'), # * MATCHED
    path('bugstatus/create/', BugStatusController().create, name='bugstatus_create'), # * NEW
    path('bugstatus/order/', BugStatusController().order, name='bugstatus_order'), # * MATCHED
    path('bugstatus/<uuid:id>/delete/', BugStatusController().destroy, name='bugstatus_destroy'), # * NEW
    path('bugstatus/<uuid:id>/edit/', BugStatusController().edit, name='bugstatus_edit'), # * NEW
    path('bugstatus/<uuid:id>/update/', BugStatusController().update, name='bugstatus_update'), # * NEW
    
    # CashfreeController
    path('cashfree/invoice/pay', CashfreeController().invoice_pay_with_cashfree, name='customer_pay_with_cashfree'), # ! REPLACED invoice-with-cashfree/
    path('cashfree/invoice/status', CashfreeController().get_invoice_payment_status, name='invoice_cashfreePayment_success'), # ! REPLACED invoice-with-cashfree/cashfree/
    path('cashfree/payments/store/', CashfreeController().cashfree_payment_store, name='plan_pay_with_cashfree'), # * MATCHED
    path('cashfree/payments/success/', CashfreeController().cashfree_payment_success, name='cashfreePayment_success'), # * MATCHED
    
    # ChartOfAccountController
    path('chart-of-account/', ChartOfAccountController.index, name='chart-of-account_index'), # * MATCHED
    path('chart-of-account/create/', ChartOfAccountController.create, name='chart-of-account_create'), # * NEW
    path('chart-of-account/store/', ChartOfAccountController.store, name='chart-of-account_store'), # * NEW
    path('chart-of-account/subtype/', ChartOfAccountController.get_subtype, name='charofAccount_subType'), # * MATCHED
    path('chart-of-account/<uuid:id>/', ChartOfAccountController.show, name='chart-of-account_show'), # * NEW
    path('chart-of-account/<uuid:id>/delete/', ChartOfAccountController.destroy, name='chart-of-account_destroy'), # * NEW
    path('chart-of-account/<uuid:id>/edit/', ChartOfAccountController.edit, name='chart-of-account_edit'), # * NEW
    path('chart-of-account/<uuid:id>/update/', ChartOfAccountController.update, name='chart-of-account_update'), # * NEW
    
    # ClientController
    path('clients/', ClientController.index, name='clients_index'), # * MATCHED
    path('clients/create/', ClientController.create, name='clients_create'), # * NEW
    path('clients/store/', ClientController.store, name='clients_store'), # * NEW
    path('clients/<uuid:client_id>/', ClientController.show, name='clients_show'), # * NEW
    path('clients/<uuid:client_id>/edit/', ClientController.edit, name='clients_edit'), # * NEW
    path('clients/<uuid:client_id>/update/', ClientController.update, name='clients_update'), # * NEW
    path('clients/<uuid:client_id>/delete/', ClientController.destroy, name='clients_destroy'), # * NEW
    path('client-reset-password/<uuid:client_id>/update/', ClientController.client_password_reset, name='client_password_update'), # * NEW
    path('client-reset-password/<uuid:id>/', ClientController.client_password, name='clients_reset'), # * MATCHED
    
    # ComissionController
    path('comission/', CommissionController.as_view(), name="comission_view"), # * MATCHED
    path('commission/store/', CommissionController.store, name='commission_store'), # * NEW
    path('commission/create/<uuid:eid>/', CommissionController.commission_create,name='commission_create'), # * MATCHED
    path('commission/<uuid:commission_id>/delete/', CommissionController.destroy,name='commission_destroy'), # * NEW
    path('commission/<uuid:commission_id>/edit/', CommissionController.edit,name='commission_edit'), # * NEW
    path('commission/<uuid:commission_id>/show/', CommissionController.show,name='commission_show'), # * NEW
    path('commission/<uuid:commission_id>/update/', CommissionController.update,name='commission_update'), # * NEW
    
    # CompanyPolicyController
    path('company-policy/', CompanyPolicyController.index, name='company-policy_index'), # * MATCHED
    path('company-policy/create/', CompanyPolicyController.create, name='company-policy_create'), # * NEW
    path('company-policy/store/', CompanyPolicyController.store, name='company-policy_store'), # * NEW
    path('company-policy/<uuid:id>/', CompanyPolicyController.show, name='company-policy_show'), # * NEW
    path('company-policy/<uuid:id>/delete/', CompanyPolicyController.destroy, name='company-policy_destroy'), # * NEW
    path('company-policy/<uuid:id>/edit/', CompanyPolicyController.edit, name='company-policy_edit'), # * NEW
    path('company-policy/<uuid:id>/update/', CompanyPolicyController.update, name='company-policy_update'), # * NEW
    
    # CompetenciesController
    path('competencies/', CompetenciesController.index, name='competencies_index'), # * MATCHED
    path('competencies/create/', CompetenciesController.create, name='competencies_create'), # * NEW
    path('competencies/store/', CompetenciesController.store, name='competencies_store'), # * NEW
    path('competencies/<uuid:id>/', CompetenciesController.show, name='competencies_show'), # * NEW
    path('competencies/<uuid:id>/delete/', CompetenciesController.destroy, name='competencies_destroy'), # * NEW
    path('competencies/<uuid:id>/edit/', CompetenciesController.edit, name='competencies_edit'), # * NEW
    path('competencies/<uuid:id>/update/', CompetenciesController.update, name='competencies_update'), # * NEW
    
    # ComplaintController
    path('complaint/', ComplaintController.index, name='complaint_index'), # * MATCHED
    path('complaint/create/', ComplaintController.create, name='complaint_create'), # * NEW
    path('complaint/store/', ComplaintController.store, name='complaint_store'), # * NEW
    path('complaint/<uuid:complaint_id>/', ComplaintController.show, name='complaint_show'), # * NEW
    path('complaint/<uuid:complaint_id>/delete/', ComplaintController.destroy, name='complaint_destroy'), # * NEW
    path('complaint/<uuid:complaint_id>/edit/', ComplaintController.edit, name='complaint_edit'), # * NEW
    path('complaint/<uuid:complaint_id>/update/', ComplaintController.update, name='complaint_update'), # * NEW
    
    # ConfirmablePasswordController
    path('confirm-password/', ConfirmablePasswordController().get, name='confirm_password'), # * MATCHED (GET + POST)
    
    # ContractController
    path('contract/', ContractController.index, name='contract_index'), # * MATCHED
    path('signature-store/', ContractController.signature_store, name='signaturestore'), # ! REPLACED /signaturestore
    path('contract/create/', ContractController.create, name='contract_create'), # * NEW
    path('contract/grid/', ContractController.grid, name='contract_grid'), # * MATCHED
    path('contract/store/', ContractController.store, name='contract_store'), # * NEW
    path('contract/<uuid:id>/', ContractController.show, name='contract_show'), # * NEW
    path('signature/<uuid:id>/', ContractController.signature, name='signature'), # * MATCHED
    path('contract/copy/store/', ContractController.copy_contract_store, name='contract_copy_store'), # * MATCHED
    path('contract/copy/<uuid:id>/', ContractController.copy_contract, name='contract_copy'), # * MATCHED
    path('contract/clients/<uuid:bid>', ContractController.clientwise_project, name="clientwise_project"), # ! REPLACED /contract/clients/select/{bid} && get-projects/{client_id}
    path('contract/pdf/<uuid:id>/', ContractController.pdf_from_contract, name='contract_download_pdf'), # * MATCHED
    path('contract/status-edit/<uuid:id>/', ContractController.contract_status_edit, name='contract_status'), # ! REPLACED /contract_status_edit/{id}
    path('contract/<uuid:id>/comment/', ContractController.comment_store, name='comment_store'), # * MATCHED
    path('contract/<uuid:id>/delete/', ContractController.destroy, name='contract_destroy'), # * NEW
    path('contract/<uuid:id>/edit/', ContractController.edit, name='contract_edit'), # * NEW
    path('contract/<uuid:id>/file/', ContractController.file_upload, name='contract_file_upload'), # * MATCHED
    path('contract/<uuid:id>/mail/', ContractController.send_mail_contract, name='send_mail_contract'), # * MATCHED
    path('contract/<uuid:id>/notes/', ContractController.note_store, name='note_store_store'), # * MATCHED
    path('contract/<uuid:id>/update/', ContractController.update, name='contract_update'), # * NEW
    path('contract/<uuid:id>/view/', ContractController.print_contract, name='get_contract'), # ! REPLACED /contract/{id}/get_contract
    path('contract/<uuid:id>/comment/destroy/', ContractController.comment_destroy, name='comment_store_destroy'), # ! /contract/{id}/comments (for DELETE)
    path('contract/<uuid:id>/description/create/', ContractController.contract_description_store, name='contract_contract_description_store'), # ! REPLACED /contract/{id}/contract_description
    path('contract/<uuid:id>/description/view/', ContractController.description, name='contract_description'), # ! REPLACED contract/{id}/description
    path('contract/<uuid:id>/file/<uuid:file_id>/', ContractController.file_download, name='contracts_file_download'), # * MATCHED
    path('contract/<uuid:id>/notes/destroy/', ContractController.note_destroy, name='note_store_destroy'), # ! REPLACED /contract/{id}/notes (for DELETE)
    path('contract/<uuid:id>/file/<uuid:file_id>/destroy', ContractController.file_delete, name='contracts_file_delete'), # ! REPLACED contract/{id}/file/delete/{fid}

    # ContractTypeController 
    path('contract-type/', ContractTypeController.index, name='contract_type_index'), # ! REPLACED contractType
    path('contract-type/create/', ContractTypeController.create, name='contract_type_create'), # * NEW
    path('contract-type/store/', ContractTypeController.store, name='contract_type_store'), # * NEW
    path('contract-type/<uuid:id>/', ContractTypeController.show, name='contract_type_show'), # * NEW
    path('contract-type/<uuid:id>/delete/', ContractTypeController.destroy, name='contract_type_destroy'), # * NEW
    path('contract-type/<uuid:id>/edit/', ContractTypeController.edit, name='contract_type_edit'), # * NEW
    path('contract-type/<uuid:id>/update/', ContractTypeController.update, name='contract_type_update'), # * NEW
    
    # CouponController
    path('coupons/', CouponController.index, name='coupons_index'), # * MATCHED
    path('coupons/apply/', CouponController.apply_coupon, name='coupons_apply'), # ! REPLACED /apply-coupon
    path('coupons/create/', CouponController.create, name='coupons_create'), # * NEW
    path('coupons/store/', CouponController.store, name='coupons_store'), # * NEW
    path('coupons/<uuid:coupon_id>/delete/', CouponController.destroy, name='coupons_destroy'), # * NEW
    path('coupons/<uuid:coupon_id>/edit/', CouponController.edit, name='coupons_edit'), # * NEW
    path('coupons/<uuid:coupon_id>/update/', CouponController.update, name='coupons_update'), # * NEW
    path('coupons/<uuid:coupon_id>/view/', CouponController.show, name='coupons_show'), # * NEW
    
    # CreditNoteController
    path('credit-note/', CreditNoteController.index, name='credit_note_index'), # ! REPLACED credit_note
    path('credit-note/custom/view/', CreditNoteController.custom_show, name='credit_note_custom_show'), # ! REPLACED custom-credit-note for GET
    path('credit-note/custom/store/', CreditNoteController.custom_store, name='credit_note_custom_store'), # ! REPLACED custom-credit-note for POST
    path('credit-note/invoice/view/', CreditNoteController.get_invoice, name='credit_note_getinvoice'), # ! REPLACED credit-note/invoice
    path('credit-note/show/<uuid:invoice_id>/', CreditNoteController.show, name='credit_note_show'), # ! REPLACED invoice/{id}/credit-note for GET
    path('credit-note/store/<uuid:invoice_id>/', CreditNoteController.store, name='credit_note_store'), # ! REPLACED invoice/{id}/credit-note for POST
    path('credit-note/<uuid:credit_note_id>/destroy/<uuid:invoice_id>/', CreditNoteController.destroy, name='credit_note_destroy'), # ! REPLACED invoice/{id}/credit-note/delete/{cn_id}
    path('credit-note/<uuid:credit_note_id>/edit/<uuid:invoice_id>', CreditNoteController.show_invoice, name='credit_note_show_invoice'), # ! REPLACED invoice/{id}/credit-note/edit/{cn_id} for GET
    path('credit-note/<uuid:credit_note_id>/update/<uuid:invoice_id>/', CreditNoteController.update, name='credit_note_update'), # ! REPLACED invoice/{id}/credit-note/edit/{cn_id} for POST
    
    # CustomPageController (menubar, custom pages)
    path('home/', CustomPageController.index, name='landingpage_index'), # ! REPLACED custom_page
    path('home/create/', CustomPageController.create, name='landingpage_create'), # * NEW
    path('home/store/', CustomPageController.store, name='landingpage_store'), # * NEW
    path('custom/store/', CustomPageController.custom_store, name='landingpage_customStore'), # ! REPLACED custom_store/
    path('custom/pages/<slug:slug>/', CustomPageController.custom_page, name='landingpage_customPage'), # ! REPLACED pages/{slug}
    path('home/destroy/<int:key>/', CustomPageController.destroy, name='landingpage_destroy'), # * NEW
    path('home/edit/<int:key>/', CustomPageController.edit, name='landingpage_edit'), # * NEW
    path('home/update/<int:key>/', CustomPageController.update, name='landingpage_update'), # * NEW
    
    # CustomerController
    path('customer/', CustomerController.index, name='customer_index'), # * MATCHED
    path('customer/create/', CustomerController.create, name='customer_create'), # * NEW
    path('customer/dashboard/', CustomerController.dashboard, name='customer_dashboard'), # * NEW
    path('customer/editbilling/', CustomerController.edit_billing, name='customer_edit_billing'), # * NEW
    path('customer/editprofile/', CustomerController.editprofile, name='customer_editprofile'), # * NEW
    path('customer/editshipping/', CustomerController.edit_shipping, name='customer_edit_shipping'), # * NEW
    path('customer/export/', CustomerController.export, name='customer_export'), # ! REPLACED export/customer
    path('customer/import/', CustomerController.import_customers, name='customer_import'), # ! REPLACED import/customer
    path('customer/import/file/', CustomerController.import_file, name='customer_file_import'), # ! REPLACED import/customer/file
    path('customer/logout/', CustomerController.customer_logout, name='customer_logout'),  # * NEW
    path('customer/payment/', CustomerController.payment, name='customer_payment'),  # * NEW
    path('customer/profile/', CustomerController.profile, name='customer_profile'),  # * NEW
    path('customer/search/', CustomerController.search_customers, name='customer_search'),  # * NEW
    path('customer/store/', CustomerController.store, name='customer_store'),  # * NEW
    path('customer/transaction/', CustomerController.transaction, name='customer_transaction'),  # * NEW
    path('customer/changelanguage/<str:lang>/', CustomerController.change_language, name='customer_change_language'),
    path('customer/update/<uuid:customer_id>/', CustomerController.update, name='customer_update'), # * NEW
    path('customer/<uuid:customer_id>/delete/', CustomerController.destroy, name='customer_destroy'), # * NEW
    path('customer/<uuid:id>/edit/', CustomerController.edit, name='customer_edit'), # * NEW
    path('customer/<str:ids>/show/', CustomerController.show, name='customer_show'), # * MATCHED
    
    # CustomFieldController
    path('custom-field/', CustomFieldController.index, name='custom_field_index'), # * MATCHED
    path('custom-field/create/', CustomFieldController.create, name='custom_field_create'),  # * NEW
    path('custom-field/store/', CustomFieldController.store, name='custom_field_store'),  # * NEW
    path('custom-field/<uuid:custom_field_id>/', CustomFieldController.show, name='custom_field_show'),  # * NEW
    path('custom-field/<uuid:custom_field_id>/destroy/', CustomFieldController.destroy, name='custom_field_destroy'),  # * NEW
    path('custom-field/<uuid:custom_field_id>/edit/', CustomFieldController.edit, name='custom_field_edit'),  # * NEW
    path('custom-field/<uuid:custom_field_id>/update/', CustomFieldController.update, name='custom_field_update'), # * NEW
    
    # CustomQuestionController
    path('custom-question/', CustomQuestionController.index, name='custom_question_index'), # * MATCHED
    path('custom-question/create/', CustomQuestionController.create, name='custom_question_create'),  # * NEW
    path('custom-question/store/', CustomQuestionController.store, name='custom_question_store'),  # * NEW
    path('custom-question/<uuid:custom_question_id>/', CustomQuestionController.show, name='custom_question_show'),  # * NEW
    path('custom-question/<uuid:custom_question_id>/destroy/', CustomQuestionController.destroy, name='custom_question_destroy'),  # * NEW
    path('custom-question/<uuid:custom_question_id>/edit/', CustomQuestionController.edit, name='custom_question_edit'),  # * NEW
    path('custom-question/<uuid:custom_question_id>/update/', CustomQuestionController.update, name='custom_question_update'),  # * NEW
    
    # DashboardController
    path('dashboard/account/', DashboardController.account_dashboard_index, name='account_dashboard'), # ! REPLACED /
    path('dashboard/client/', DashboardController.client_view, name='client_dashboard_view'), # ! REPLACED dashboard
    path('dashboard/crm/', DashboardController.crm_dashboard_index, name='crm_dashboard'),  # ! REPLACED /crm-dashboard
    path('dashboard/filter-view/', DashboardController.filter_view, name='dashboard_filter_view'), # ! REPLACED dashboard-view
    # getOrderChart is used internally, if needed, it can be mapped:
    path('dashboard/getOrderChart/', DashboardController.get_order_chart, name='get_order_chart'), # * NEW
    path('dashboard/hrm/', DashboardController.hrm_dashboard_index, name='hrm_dashboard'), # ! REPLACED /hrm-dashboard
    path('dashboard/pos/', DashboardController.pos_dashboard_index, name='pos_dashboard'), # ! REPLACED /pos-dashboard
    path('dashboard/project/', DashboardController.project_dashboard_index, name='project_dashboard'), # ! REPLACED /project-dashboard
    path('dashboard/tracker/', DashboardController.stop_tracker, name='stop_tracker'), # ! REPLACED stop-tracker
    
    # DealContoller
    path('deals/', DealController.index, name='deals.index'),  # * MATCHED
    path('deals/change-pipeline', DealController.change_pipeline, name='deals.change.pipeline'), # * MATCHED
    path('deals/create', DealController.create, name='deals.create'),
    path('deals/list', DealController.deal_list, name='deals.list'), # * MATCHED
    path('deals/order', DealController.order, name='deals.order'), # * MATCHED
    path('deals/user', DealController.json_user, name='deal.user.json'), # * MATCHED
    path('deals/change-deal-status/<uuid:id>', DealController.change_status, name='deals.change.status'), # * MATCHED
    path('deals/<uuid:id>/destroy', DealController.destroy, name='deals.destroy'),
    path('deals/<uuid:id>/edit', DealController.edit, name='deals.edit'),
    path('deals/<uuid:id>/file', DealController.file_upload, name='deals.file.upload'), # * MATCHED
    path('deals/<uuid:id>/note', DealController.note_store, name='deals.note.store'), # * MATCHED
    path('deals/<uuid:id>/update', DealController.update, name='deals.update'),
    path('deals/<uuid:id>/view', DealController.show, name='deals.show'),
    path('deals/<uuid:id>/call/show-edit', DealController.call_create, name='deals.calls.create'), # ! REPLACED  /deals/{id}/call
    path('deals/<uuid:id>/call/update', DealController.call_store, name='deals.calls.store'), # ! REPLACED  /deals/{id}/call
    path('deals/<uuid:id>/clients/show-edit', DealController.client_edit, name='deals.clients.edit'), # ! REPLACED  /deals/{id}/clients/
    path('deals/<uuid:id>/clients/update', DealController.client_update, name='deals.clients.update'), # ! REPLACED  /deals/{id}/clients/
    path('deals/<uuid:id>/discussions/show-edit', DealController.discussion_create, name='deals.discussions.create'), # ! REPLACED  /deals/{id}/discussions
    path('deals/<uuid:id>/discussions/update', DealController.discussion_store, name='deals.discussion.store'), # ! REPLACED  /deals/{id}/discussions
    path('deals/<uuid:id>/email/show-edit', DealController.email_create, name='deals.emails.create'), # ! REPLACED  /deals/{id}/email
    path('deals/<uuid:id>/email/update', DealController.email_store, name='deals.emails.store'), # ! REPLACED  /deals/{id}/email
    path('deals/<uuid:id>/products/show-edit', DealController.product_edit, name='deals.products.edit'), # ! REPLACED  /deals/{id}/products
    path('deals/<uuid:id>/products/update', DealController.product_update, name='deals.products.update'), # ! REPLACED  /deals/{id}/products
    path('deals/<uuid:id>/sources/show-view', DealController.source_edit, name='deals.sources.edit'), # ! REPLACED  /deals/{id}/sources/
    path('deals/<uuid:id>/sources/update', DealController.source_update, name='deals.sources.update'), # ! REPLACED  /deals/{id}/sources/
    path('deals/<uuid:id>/task/show-edit', DealController.task_create, name='deals.tasks.create'), # ! REPLACED  /deals/{id}/task
    path('deals/<uuid:id>/task/update', DealController.task_store, name='deals.tasks.store'), # ! REPLACED  /deals/{id}/task
    path('deals/<uuid:id>/users/show-edit', DealController.user_edit, name='deals.users.edit'), # ! REPLACED  /deals/{id}/users/
    path('deals/<uuid:id>/users/update', DealController.user_update, name='deals.users.update'), # ! REPLACED  /deals/{id}/users/
    path('deals/<uuid:id>/clients/<uuid:uid>', DealController.client_destroy, name='deals.clients.destroy'), # * MATCHED
    path('deals/<uuid:id>/file/<uuid:fid>', DealController.file_download, name='deals.file.download'), # * MATCHED
    path('deals/<uuid:id>/products/<uuid:uid>', DealController.product_destroy, name='deals.products.destroy'), # * MATCHED
    path('deals/<uuid:id>/sources/<uuid:uid>', DealController.source_destroy, name='deals.sources.destroy'), # * MATCHED
    path('deals/<uuid:id>/task/<uuid:tid>', DealController.task_update, name='deals.tasks.update'), # * MATCHED
    path('deals/<uuid:id>/task-status/<uuid:tid>', DealController.task_update_status, name='deals.tasks.update_status'), # ! REPLACED '/deals/{id}/task_status/{tid}'
    path('deals/<uuid:id>/users/<uuid:uid>', DealController.user_destroy, name='deals.users.destroy'), # * MATCHED
    path('deals/<uuid:id>/call/<uuid:cid>/destroy', DealController.call_destroy, name='deals.calls.destroy'), # ! REPLACED /deals/{id}/call/{cid}
    path('deals/<uuid:id>/call/<uuid:cid>/edit', DealController.call_edit, name='deals.calls.edit'), # * MATCHED
    path('deals/<uuid:id>/call/<uuid:cid>/update', DealController.call_update, name='deals.calls.update'), # ! REPLACED /deals/{id}/call/{cid}
    path('deals/<uuid:id>/file/delete/<uuid:fid>', DealController.file_delete, name='deals.file.delete'), # * MATCHED
    path('deals/<uuid:id>/permission/<uuid:cid>/show', DealController.permission, name='deals.client.permission'), # ! REPLACED  /deals/{id}/permission/{cid}
    path('deals/<uuid:id>/permission/<uuid:cid>/store', DealController.permission_store, name='deals.client.permissions.store'), # ! REPLACED  /deals/{id}/permission/{cid}
    path('deals/<uuid:id>/task/<uuid:tid>/destroy', DealController.task_destroy, name='deals.tasks.destroy'), # ! REPLACED /deals/{id}/task/{tid}
    path('deals/<uuid:id>/task/<uuid:tid>/edit', DealController.task_edit, name='deals.tasks.edit'), # * MATCHED
    path('deals/<uuid:id>/task/<uuid:tid>/show', DealController.task_show, name='deals.tasks.show'), # * MATCHED
    
    # DebitNoteController
    path('debit-note', DebitNoteController.index, name='debit.note'),  # * MATCHED
    path('debit-note/bill', DebitNoteController.get_bill, name='bill.get'),  # * MATCHED
    path('debit-note/custom/view', DebitNoteController.custom_create, name='bill.custom.debit.note'),  # ! REPLACED custom-debit-note
    path('debit-note/custom/store/', DebitNoteController.custom_store, name='bill.custom.debit.note'), # ! REPLACED custom-debit-note
    path('debit-note/bill/<uuid:id>/show-create', DebitNoteController.create, name='bill.debit.note'), # ! REPLACED custom-debit-note bill/{id}/debit-note
    path('debit-note/bill/<uuid:id>/store', DebitNoteController.store, name='bill.debit.note'), # ! REPLACED custom-debit-note bill/{id}/debit-note
    path('debit-note/bill/<uuid:id>/delete/<uuid:cn_id>', DebitNoteController.destroy, name='bill.delete.debit.note'),  # ! REPLACED bill/{id}/debit-note/delete/{cn_id}
    path('debit-note/bill/<uuid:id>/show-edit/<uuid:cn_id>', DebitNoteController.edit, name='bill.edit.debit.note'), # ! REPLACED bill/{id}/debit-note/show/{cn_id}
    path('debit-note/bill/<uuid:id>/edit/<uuid:cn_id>', DebitNoteController.update, name='bill.edit.debit.note'),  # ! REPLACED bill/{id}/debit-note/edit/{cn_id}
    
    # DeductionOptionController
    path('deduction-option/', DeductionOptionController.index, name='deductionoption.index'), # * MATCHED
    path('deduction-option/create', DeductionOptionController.create, name='deductionoption.create'), # * NEW
    path('deduction-option/store', DeductionOptionController.store, name='deductionoption.store'), # * NEW
    path('deduction-option/<uuid:deduction_option_id>/destroy', DeductionOptionController.destroy, name='deductionoption.destroy'), # * NEW
    path('deduction-option/<uuid:deduction_option_id>/edit', DeductionOptionController.edit, name='deductionoption.edit'), # * NEW
    path('deduction-option/<uuid:deduction_option_id>/show', DeductionOptionController.show, name='deductionoption.show'), # * NEW
    path('deduction-option/<uuid:deduction_option_id>/update', DeductionOptionController.update, name='deductionoption.update'), # * NEW
    
    # DepartmentController
    path('department/', DepartmentController.index, name='department_index'), # * MATCHED
    path('department/create/', DepartmentController.create, name='department_create'), # * NEW
    path('department/store/', DepartmentController.store, name='department_store'), # * NEW
    path('department/<uuid:department_id>/destroy/', DepartmentController.destroy, name='department_destroy'), # * NEW
    path('department/<uuid:department_id>/edit/', DepartmentController.edit, name='department_edit'), # * NEW
    path('department/<uuid:department_id>/show/', DepartmentController.show, name='department_show'), # * NEW
    path('department/<uuid:department_id>/update/', DepartmentController.update, name='department_update'), # * NEW
    
    # DesignationController
    path('designation/', DesignationController.index, name='designation.index'), # * MATCHED
    path('designation/create', DesignationController.create, name='designation.create'), # * NEW
    path('designation/store/', DesignationController.store, name='designation.store'), # * NEW
    path('designation/<uuid:designation_id>/destroy', DesignationController.destroy, name='designation.destroy'), # * NEW
    path('designation/<uuid:designation_id>/edit', DesignationController.edit, name='designation.edit'), # * NEW
    path('designation/<uuid:designation_id>/show', DesignationController.show, name='designation.show'), # * NEW
    path('designation/<uuid:designation_id>/update', DesignationController.update, name='designation.update'), # * NEW
    
    # DiscoverController
    path('discover/', DiscoverController.discover_index, name='discover_index'), # * MATCHED
    path('discover/create/', DiscoverController.create, name="discover_create"), # * MATCHED
    path("discover/store/", DiscoverController.store, name="discover_store"), # * MATCHED
    path('discover/view/create/', DiscoverController.discover_create_view, name='discover_create_view'), # * NEW
    path('discover/view/store/', DiscoverController.discover_store_view, name='discover_store_view'), # * NEW
    path("discover/delete/<str:key>", DiscoverController.delete, name="discover_delete"), # * MATCHED
    path("discover/edit/<str:key>", DiscoverController.edit, name="discover_edit"), # * MATCHED
    path("discover/update/<str:key>", DiscoverController.update, name="discover_update"), # * MATCHED
    
    # DocumentController
    path('document/', DocumentController.index, name='document.index'), # * MATCHED
    path('document/create/', DocumentController.create, name='document.create'), # * NEW
    path('document/store/', DocumentController.store, name='document.store'), # * NEW
    path('document/<uuid:document_id>/destroy', DocumentController.destroy, name='document.destroy'), # * NEW
    path('document/<uuid:document_id>/edit', DocumentController.edit, name='document.edit'), # * NEW
    path('document/<uuid:document_id>/show', DocumentController.show, name='document.show'), # * NEW
    path('document/<uuid:document_id>/update', DocumentController.update, name='document.update'), # * NEW
    
    # DocumentUploadController
    path('document-upload/', DocumentUploadController.index, name='document-upload.index'), # * MATCHED
    path('document-upload/create/', DocumentUploadController.create, name='document-upload.create'), # * NEW
    path('document-upload/store/', DocumentUploadController.store, name='document-upload.store'), # * NEW
    path('document-upload/<uuid:id>/destroy', DocumentUploadController.destroy, name='document-upload.destroy'), # * NEW
    path('document-upload/<uuid:id>/edit', DocumentUploadController.edit, name='document-upload.edit'), # * NEW
    path('document-upload/<uuid:id>/show', DocumentUploadController.show, name='document-upload.show'), # * NEW
    path('document-upload/<uuid:id>/update', DocumentUploadController.update, name='document-upload.update'), # * NEW
    
    # EmailTemplateController
    path('email-template/', EmailTemplateController.index, name='email_template.index'), # * MATCHED
    path('email-template/create/', EmailTemplateController.create, name='email_template.create'), # * NEW
    path('email-template/store/', EmailTemplateController.store, name='email_template.store'), # * NEW
    path('email-template/<uuid:id>/update/', EmailTemplateController.update, name='email_template.update'), # * NEW
    path('email-template/<uuid:id>/manage/<str:lang>/', EmailTemplateController.manage_email_lang, name='manage.email.language'), # ! REPLACED email_template_lang/{id}/{lang?}
    path('email-template/<uuid:id>/store-lang/', EmailTemplateController.store_email_lang, name='store.email.language'), # ! REPLACED email_template_store/{pid}
    path('email-template/status/update/', EmailTemplateController.update_status, name='email_template.update_status'), # ! REPLACED email_template_store
    
    # EmailVerificationNotificationController
    path('email/verification-notification/', EmailVerificationNotificationController.as_view(), name='email_verification_notification'), # * MATCHED
    
    # EmailVerificationPromptController
    path('verify/', EmailVerificationPromptController.get, name='email_verify_prompt'), # * MATCHED
    path('verify/<str:lang>/', EmailVerificationPromptController.show_verify_form, name='email_verify_prompt_lang'), # * MATCHED
    
    #EmployeeController
    path('employee/', EmployeeController.index, name='employee_index'), # * MATCHED
    path('employee/create/', EmployeeController.create, name='employee_create'), # * NEW
    path('employee/export/', EmployeeController.export, name='employee_export'), # ! REPLACED export/employee
    path('employee/get-department/', EmployeeController.getdepartment, name='employee_getdepartment'), # ! REPLACED employee/getdepartment
    path('employee/import/', EmployeeController.import_employees, name='employee_import'), # ! REPLACED import/employee
    path('employee/json/', EmployeeController.json, name='employee_json'), # * MATCHED
    path('employee/last-login/', EmployeeController.last_lLogin, name='employee_lastlogin'), # ! REPLACED lastlogin
    path('employee/profile/', EmployeeController.profile, name='employee_profile'), # ! REPLACED employee-profile
    path('employee/store/', EmployeeController.store, name='employee_store'), # * NEW
    path('employee/json/branch/', EmployeeController.employee_json, name='employee_employeejson'), # ! REPLACED branch/employee/json
    path('employee/import/file/', EmployeeController.import_file, name='employee_import_file'), # ! REPLACED import/employee/file
    path('employee/destroy/<uuid:emp_id>/', EmployeeController.destroy, name='employee_destroy'), # * NEW
    path('employee/edit/<uuid:encrypted_id>/', EmployeeController.edit, name='employee_edit'), # * NEW
    path('employee/exp-doc/<uuid:emp_id>/', EmployeeController.exp_certificate_doc, name='employee_expcertificate_doc'), # ! REPLACED employee/expdoc/{id}
    path('employee/exp-pdf/<uuid:emp_id>/', EmployeeController.exp_certificate_pdf, name='employee_expcertificate_pdf'), # ! REPLACED employee/exppdf/{id}
    path('employee/joining-doc/<uuid:emp_id>/', EmployeeController.joining_letter_doc, name='employee_joiningletter_doc'), # ! REPLACED employee/doc/{id} 
    path('employee/joining-pdf/<uuid:emp_id>/', EmployeeController.joining_letter_pdf, name='employee_joiningletter_pdf'), # ! REPLACED employee/pdf/{id}
    path('employee/noc-doc/<uuid:emp_id>/', EmployeeController.noc_doc, name='employee_noc_doc'), # ! REPLACED employee/nocdoc/{id}
    path('employee/noc-pdf/<uuid:emp_id>/', EmployeeController.noc_pdf, name='employee_noc_pdf'), # ! REPLACED employee/nocpdf/{id}
    path('employee/profile-show/<uuid:encrypted_id>/', EmployeeController.profile_show, name='employee_profile_show'), # ! REPLACED show-employee-profile/{id}
    path('employee/show/<uuid:encrypted_id>/', EmployeeController.show, name='employee_show'), # ! REPLACED employee-profile
    path('employee/update/<uuid:emp_id>/', EmployeeController.update, name='employee_update'), # * NEW
    
    # EventController
    path('event/',EventController.index,name='event.index'), # * MATCHED
    path('event/create/',EventController.create,name='event.create'), # * NEW
    path('event/get-dashboard-event-data', EventController.get_dashboard_event_data, name='event.dashboard_data'), # ! REPLACED event/get_dashboard_event_data
    path('event/get-department/',EventController.get_department,name='event.get_department'), # ! REPLACED event/getdepartment
    path('event/get-employee/',EventController.get_employee,name='event.get_employee'), # ! REPLACED event/getemployee
    path('event/get-event-data/',EventController.get_event_data,name='event.get_event_data'), # ! REPLACED event/get_event_data
    path('event/store/',EventController.store,name='event.store'), # * NEW
    path('event/destroy/<uuid:event_id>/',EventController.destroy,name='event.destroy'), # * NEW
    path('event/edit/<uuid:event_id>/',EventController.edit,name='event.edit'), # * NEW
    path('event/show/<uuid:event_id>/',EventController.show,name='event.show'), # * NEW
    path('event/update/<uuid:event_id>/',EventController.update,name='event.update'), # * NEW
    
    # ExpenseController
    path('expense/', ExpenseController.as_view(), name='expense_resource'), # * MATCHED
    path('expense/customer/', ExpenseController.customer, name='expense_customer'), # * MATCHED
    path('expense/employee/', ExpenseController.employee, name='expense_employee'), # * MATCHED
    path('expense/index', ExpenseController.index, name='expense_index'), # * MATCHED
    path('expense/items/', ExpenseController.items, name='expense_items'), # * MATCHED
    path('expense/list', ExpenseController.expense_list, name='expense_list'), # ! REPLACED /expense-list
    path('expense/product/', ExpenseController.product, name='expense_product'), # * MATCHED
    path('expense/vendor/', ExpenseController.vendor, name='expense_vendor'), # * MATCHED
    path('expense/product/destroy/', ExpenseController.product_destroy,name='expense_product_destroy'), # * MATCHED
    path('expense/create/<uuid:id>/', ExpenseController.create,name='expense_create'), # * MATCHED
    path('expense/<uuid:id>/payment', ExpenseController.payment, name='expense_payment'), # * MATCHED
    path('expense/projects/<uuid:id>', ExpenseController.index, name='expense_project'), # ! REPLACED /projects/{id}/expense
    path('expense/projects/<uuid:id>/create', ExpenseController.create, name="expense_project_create"), # ! REPLACED /projects/{pid}/expense/create
    path('expense/projects/<uuid:id>/destroy/', ExpenseController.destroy,name='expense_destroy'), # ! REPLACED /projects/{eid}/expense/
    path('expense/projects/<uuid:id>/store/', ExpenseController.store, name='expense_store'), # ! REPLACED /projects/{pid}/expense/store
    path('expense/projects/<uuid:id>/update', ExpenseController.update, name='expense_update'), # ! REPLACED /projects/{id}/expense/{eid}
    path('expense/projects/<uuid:id>/edit/<uuid:eid>/', ExpenseController.edit, name='expense_edit'), # ! REPLACED /projects/{id}/expense/{eid}/edit
    path('expense/show/<str:ids>/', ExpenseController.show, name='expense_show'), # * NEW
    path('expense/view/<uuid:expense_id>/', ExpenseController.view,name='expense_view'), # ! REPLACED expense/pdf/{id}
    
    # FormBuilderController
    path('form-builder/', FormBuilderController.index, name='form_builder_index'), # ! REPLACED form_builder
    path('form-builder/create/', FormBuilderController.create, name='form_builder_create'), # * NEW
    path('form-builder/store/', FormBuilderController.store, name='form_builder_store'), # * NEW
    path('form-builder/view-store/', FormBuilderController.form_view_store, name='form_view_store'), # ! REPLACED /form_view_store 
    path('form-builder/destroy/<uuid:form_builder_id>/', FormBuilderController.destroy, name='form_builder_destroy'), # * NEW
    path('form-builder/edit/<uuid:form_builder_id>/', FormBuilderController.edit, name='form_builder_edit'), # * NEW
    path('form-builder/show/<uuid:form_builder_id>/', FormBuilderController.show, name='form_builder_show'), # * NEW
    path('form-builder/update/<uuid:form_builder_id>/', FormBuilderController.update, name='form_builder_update'), # * NEW
    path('form-builder/view/<str:code>/', FormBuilderController.form_view, name='form_view'), # ! REPLACED /form/{code}
    path('form-builder/response-detail/<uuid:response_id>/', FormBuilderController.response_detail, name='form_builder_response_detail'), # ! REPLACED /response/{id}
    path('form-builder/response-view/<uuid:form_id>/', FormBuilderController.view_response, name='form_builder_view_response'), # ! REPLACED /form_response/{id}
    path('form-builder/field/bind/<uuid:form_id>/', FormBuilderController.form_field_bind, name='form_field_bind'), # ! REPLACED /form_field/{id}
    path('form-builder/field/bind-store/<uuid:form_id>/', FormBuilderController.bind_store, name='form_field_store'), # ! REPLACED /form_field_store/{id}
    path('form-builder/field/create/<uuid:form_id>/', FormBuilderController.field_create, name='form_field_create'), # ! REPLACED /form_builder/{id}/field
    path('form-builder/field/store/<uuid:form_id>/', FormBuilderController.field_store, name='form_field_store'), # ! REPLACED /form_builder/{id}/field
    path('form-builder/field/<uuid:form_id>/destroy/<uuid:field_id>/', FormBuilderController.field_destroy, name='form_field_destroy'), # ! REPLACED /form_builder/{id}/field/{fid}'
    path('form-builder/field/<uuid:form_id>/edit/<uuid:field_id>/', FormBuilderController.field_edit, name='form_field_edit'), # ! REPLACED /form_builder/{id}/field/{fid}/edit
    path('form-builder/field/<uuid:form_id>/show/<uuid:field_id>', FormBuilderController.field_show, name='form_field_show'), # ! REPLACED /form_builder/{id}/field/{fid}/show
    path('form-builder/field/<uuid:form_id>/update/<uuid:field_id>/', FormBuilderController.field_update, name='form_field_update'), # ! REPLACED /form_builder/{id}/field/{fid}
    
    # GoalController
    path('goal/', GoalController.index, name='goal_index'), # * MATCHED
    path('goal/create/', GoalController.create, name='goal_create'), # * NEW
    path('goal/store/', GoalController.store, name='goal_store'), # * NEW
    path('goal/<uuid:goal_id>/destroy/', GoalController.destroy, name='goal_destroy'), # * NEW
    path('goal/<uuid:goal_id>/edit/', GoalController.edit, name='goal_edit'), # * NEW
    path('goal/<uuid:goal_id>/show/', GoalController.show, name='goal_show'), # * NEW
    path('goal/<uuid:goal_id>/update/', GoalController.update, name='goal_update'), # * NEW
    
    # GoalTrackingController
    path('goaltracking/', GoalTrackingController.index, name='goaltracking_index'), # * MATCHED
    path('goaltracking/create/', GoalTrackingController.create, name='goaltracking_create'), # * NEW
    path('goaltracking/store/', GoalTrackingController.store, name='goaltracking_store'), # * NEW
    path('goaltracking/<uuid:goaltracking_id>/destroy/', GoalTrackingController.destroy, name='goaltracking_destroy'), # * NEW
    path('goaltracking/<uuid:goaltracking_id>/edit/', GoalTrackingController.edit, name='goaltracking_edit'), # * NEW
    path('goaltracking/<uuid:goaltracking_id>/show/', GoalTrackingController.show, name='goaltracking_show'), # * NEW
    path('goaltracking/<uuid:goaltracking_id>/update/', GoalTrackingController.update, name='goaltracking_update'), # * NEW
    
    # GoalTypeController
    path('goaltype/', GoalTypeController.index, name='goaltype.index'), # * MATCHED
    path('goaltype/create/', GoalTypeController.create, name='goaltype.create'), # * NEW
    path('goaltype/store/', GoalTypeController.store, name='goaltype.store'), # * NEW
    path('goaltype/<uuid:id>/show/', GoalTypeController.show, name='goaltype.show'), # * NEW
    path('goaltype/<uuid:id>/edit/', GoalTypeController.edit, name='goaltype.edit'), # * NEW
    path('goaltype/<uuid:id>/update/', GoalTypeController.update, name='goaltype.update'), # * NEW
    path('goaltype/<uuid:id>/delete/', GoalTypeController.destroy, name='goaltype.destroy'), # * NEW
    
    # HolidayController
    path('holiday/', HolidayController().index, name='holiday_index'), # * MATCHED
    path('holiday/create/', HolidayController().create, name='holiday_create'), # * NEW
    path('holiday/store/', HolidayController().store, name='holiday_store'), # * NEW
    path('holiday/<uuid:holiday_id>/destroy/', HolidayController().destroy, name='holiday_destroy'), # * NEW
    path('holiday/<uuid:holiday_id>/edit/', HolidayController().edit, name='holiday_edit'), # * NEW
    path('holiday/<uuid:holiday_id>/update/', HolidayController().update, name='holiday_update'), # * NEW
    path('holiday/<uuid:holiday_id>/show/', HolidayController().show, name='holiday_show'), # * NEW
    path('holiday/calendar/data/', HolidayController().get_holiday_data, name='holiday_data'), # ! REPLACED holiday/get_holiday_data
    path('holiday/calendar/show/', HolidayController().calendar, name='holiday_calendar'), # ! REPLACED holiday-calendar
    
    # IndicatorController
    path('indicator/', IndicatorController().index, name='indicator.index'), # * MATCHED
    path('indicator/create/', IndicatorController().create, name='indicator.create'), # * NEW
    path('indicator/store/', IndicatorController().store, name='indicator.store'), # * NEW
    path('indicator/<uuid:indicator_id>/delete/', IndicatorController().destroy, name='indicator.delete'), # * NEW
    path('indicator/<uuid:indicator_id>/edit/', IndicatorController().edit, name='indicator.edit'), # * NEW
    path('indicator/<uuid:indicator_id>/show', IndicatorController().show, name='indicator.show'), # * NEW
    path('indicator/<uuid:indicator_id>/update/', IndicatorController().update, name='indicator.update'), # * NEW
    
    # InterviewScheduleController
    path('interview-schedule/', InterviewScheduleController().index, name='interview_schedule_index'), # * MATCHED
    path('interview-schedule/data/', InterviewScheduleController().get_interview_data, name='interview_schedule_data'), # ! REPLACED interview-schedule/get_interview_data
    path('interview-schedule/store/', InterviewScheduleController().store, name='interview_schedule_store'), # * NEW
    path('interview-schedule/create/<uuid:candidate>/', InterviewScheduleController().create, name='interview_schedule_create'), # * MATCHED
    path('interview-schedule/<uuid:interview_schedule_id>/destroy/', InterviewScheduleController().destroy, name='interview_schedule_destroy'), # * NEW
    path('interview-schedule/<uuid:interview_schedule_id>/edit/', InterviewScheduleController().edit, name='interview_schedule_edit'), # * NEW
    path('interview-schedule/<uuid:interview_schedule_id>/show/', InterviewScheduleController().show, name='interview_schedule_show'), # * NEW
    path('interview-schedule/<uuid:interview_schedule_id>/update/', InterviewScheduleController().update, name='interview_schedule_update'), # * NEW
    
    # InvoiceController
    path('invoice/', InvoiceController.as_view(), name='invoice_view'),  # * MATCHED
    path('invoice/customer/', InvoiceController.customer, name='invoice_customer'), # * MATCHED
    path('invoice/customer-invoice/', InvoiceController.customer_invoice, name='invoice_customer_invoice'), # * NEW
    path('invoice/export/', InvoiceController.export, name='invoice_export'), # ! REPLACED export/invoice
    path('invoice/index/', InvoiceController.index, name='invoice_index'), # * MATCHED
    path('invoice/items/', InvoiceController.items, name='invoice_items'), # * MATCHED
    path('invoice/store/', InvoiceController.store, name='invoice_store'), # * NEW
    path('invoice/template-settings/', InvoiceController.save_template_settings, name='invoice_template_settings'), # ! REPLACED invoices/template/setting
    path('invoice/<str:encrypted_id>/', InvoiceController.show, name='invoice_show'), # * MATCHED
    path('invoice/products/show/', InvoiceController.product, name='invoice_product'), # ! REPLACED invoice/product
    path('invoice/products/destroy/', InvoiceController.product_destroy, name='invoice_product_destroy'),  # ! REPLACED invoice/product/destroy
    path('invoice/create/<uuid::customer_id>/', InvoiceController.create, name='invoice_create'), # * MATCHED
    path('invoice/customer-invoice/<uuid:invoice_id>/', InvoiceController.customer_invoice_show, name='invoice_customer_invoice_show'), # * NEW
    path('invoice/link/<str:encrypted_id>/', InvoiceController.invoice_link, name='invoice_link'), # ! REPLACED /customer/invoice/{id}/
    path('invoice/pdf/<str:encrypted_id>/', InvoiceController.pdf, name='invoice_pdf'), # * MATCHED
    path('invoice/customer-invoice/send/<uuid:invoice_id>/', InvoiceController.customer_invoice_send, name='invoice_customer_invoice_send'), # * NEW
    path('invoice/customer-invoice/send-mail/<uuid:invoice_id>/', InvoiceController.customer_invoice_send_mail, name='invoice_customer_invoice_send_mail'), # * NEW
    path('invoice/<uuid:invoice_id>/destroy/', InvoiceController.destroy, name='invoice_destroy'), # * NEW
    path('invoice/<uuid:invoice_id>/duplicate/', InvoiceController.duplicate, name='invoice_duplicate'), # * MATCHED
    path('invoice/<str:encrypted_id>/edit/', InvoiceController.edit, name='invoice_edit'), # * NEW
    path('invoice/<uuid:invoice_id>/payment/', InvoiceController.payment, name='invoice_payment'), # * MATCHED
    path('invoice/<uuid:invoice_id>/resend/', InvoiceController.resend, name='invoice_resend'), # * MATCHED
    path('invoice/<uuid:invoice_id>/send/', InvoiceController.send, name='invoice_send'), # * MATCHED,
    path('invoice/<uuid:invoice_id>/update/', InvoiceController.update, name='invoice_update'), # * NEW
    path('invoice/<uuid:invoice_id>/payment/create/', InvoiceController.create_payment, name='invoice_payment_create'), # ! REPLACED /invoice/{id}/payment
    path('invoice/<uuid:invoice_id>/payment/reminder/', InvoiceController.payment_reminder, name='invoice_payment_reminder'), # * MATCHED
    path('invoice/<uuid:invoice_id>/shipping/display/', InvoiceController.shipping_display, name='invoice_shipping_display'), # ! REPLACED invoice/{id}/shipping/print
    path('invoice/preview/<str:template>/<str:color>/', InvoiceController.preview_invoice, name='invoice_preview'), # * MATCHED
    path('invoice/<uuid:invoice_id>/payment/<uuid:payment_id>/destroy/', InvoiceController.payment_destroy, name='invoice_payment_destroy'), # * MATCHED
    
    
    # JobController
    path('job/', JobController.index, name='job_index'), # * MATCHED
    path('job/create/', JobController.create, name='job_create'), # * NEW
    path('job/store/', JobController.store, name='job_store'), # * NEW
    path('job/apply-data/<str:code>/', JobController.job_apply_data, name='job_apply_data'), # ! REPLACED job/apply/data{code}
    path('job/<uuid:job_id>/show/', JobController.show, name='job_show'), # * NEW
    path('job/<uuid:job_id>/edit/', JobController.edit, name='job_edit'), # * NEW
    path('job/<uuid:job_id>/update/', JobController.update, name='job_update'), # * NEW
    path('job/<uuid:job_id>/destroy/', JobController.destroy, name='job_destroy'), # * NEW
    path('job/apply/<str:code>/<str:lang>/', JobController.job_apply, name='job_apply'), # * MATCHED
    path('job/requirement/<str:code>/<str:lang>/', JobController.job_requirement, name='job_requirement'), # * MATCHED
    path('job/career/<uuid:job_id>/<str:lang>'. JobController.career, name='job_career'), # ! REPLACED career/{id}/{lang}
    
    # JobApplicationController
    path('job-application/', JobApplicationController.index, name='job-application.index'), # * MATCHED
    path('job-application/candidate/', JobApplicationController.candidate, name='job.application.candidate'), # ! REPLACED candidates-job-applications
    path('job-application/get-by-job/', JobApplicationController.get_by_job, name='job.get_by_job'), # ! REPLACED job-application/getByJob
    path('job-application/job-on-board/', JobApplicationController.job_on_board, name='job.on.board'), # ! REPLACED job-onboard
    path('job-application/order/', JobApplicationController.order, name='job-application.order'), # ! REPLACED job-application/order
    path('job-application/stage-change/', JobApplicationController.stage_change, name='job.stage_change'), # ! REPLACED job-application/stage/change
    path('job-application/store/', JobApplicationController.store, name='job-application.store'), # * NEW
    path('job-application/rating/<uuid:application_id>/', JobApplicationController.rating, name='job-application.rating'), # ! REPLACED job-application/{id}/rating
    path('job-application/show/<str:ids>/', JobApplicationController.show, name='job-application.show'), # * NEW
    path('job-application/<uuid:application_id>/archive', JobApplicationController.archive, name='job-application.archive'), # * MATCHED
    path('job-application/<uuid:application_id>/destroy', JobApplicationController.destroy, name='job-application.destroy'), # * NEW
    path('job-application/<uuid:application_id>/note/destroy', JobApplicationController.destroy_note, name='job-application.destroy_note'), # * MATCHED
    path('job-application/<uuid:application_id>/note/store', JobApplicationController.add_note, name='job-application.add_note'), # * MATCHED
    path('job-application/<uuid:application_id>/skill/store', JobApplicationController.add_skill, name='job-application.add_skill'), # * MATCHED
    path('job-application/job-board/convert/<uuid:board_id>/', JobApplicationController.job_board_convert, name='job.board.convert'), # ! REPLACED job-onboard/convert/{id}
    path('job-application/job-board/convert-data/<uuid:board_id>/', JobApplicationController.job_board_convert_data, name='job.board.convert_data'), # ! REPLACED  job-onboard/convert/{id}
    path('job-application/job-board/create/<uuid:application_id>/', JobApplicationController.job_board_create, name='job.board.create'), # ! REPLACED job-onboard/create/{id}
    path('job-application/job-board/delete/<uuid:board_id>/', JobApplicationController.job_board_delete, name='job.board.delete'), # ! REPLACED job-onboard/delete/{id}
    path('job-application/job-board/edit/<uuid:board_id>/', JobApplicationController.job_board_edit, name='job.board.edit'), # ! REPLACED job-onboard/edit/{id}
    path('job-application/job-board/store/<uuid:application_id>/', JobApplicationController.job_board_store, name='job.board.store'), # ! REPLACED job-onboard/store/{id}
    path('job-application/job-board/update/<uuid:board_id>/', JobApplicationController.job_board_update, name='job.board.update'), # ! REPLACED job-onboard/update/{id}
    path('job-application/offerletter-doc/<uuid:application_id>/', JobApplicationController.offerletter_doc, name='job.offerletter_doc'), # ! REPLACED job-onboard/doc/{id}
    path('job-application/offerletter-pdf/<uuid:application_id>/', JobApplicationController.offerletter_pdf, name='job.offerletter_pdf'), # ! REPLACED job-onboard/pdf/{id}
    
    # JobCategoryController
    path('jobcategory/', JobCategoryController.index, name='jobcategory_index'), # * MATCHED
    path('jobcategory/create/', JobCategoryController.create, name='jobcategory_create'), # * NEW
    path('jobcategory/store/', JobCategoryController.store, name='jobcategory_store'), # * NEW
    path('jobcategory/show/<uuid:jobcategory_id>/', JobCategoryController.show, name='jobcategory_show'), # * NEW
    path('jobcategory/edit/<uuid:jobcategory_id>/', JobCategoryController.edit, name='jobcategory_edit'), # * NEW
    path('jobcategory/update/<uuid:jobcategory_id>/', JobCategoryController.update, name='jobcategory_update'), # * NEW
    path('jobcategory/destroy/<uuid:jobcategory_id>/', JobCategoryController.destroy, name='jobcategory_destroy'), # * NEW
    
    # JobStageController
    path('job-stage/', JobStageController.index, name='job_stage_index'), # * MATCHED
    path('job-stage/create/', JobStageController.create, name='job_stage_create'), # * NEW  
    path('job-stage/order/', JobStageController.order, name='job_stage_order'), # * NEW
    path('job-stage/store/', JobStageController.store, name='job_stage_store'), # * MATCHED
    path('job-stage/<uuid:job_stage_id>/destroy/', JobStageController.destroy, name='job_stage_destroy'), # * NEW
    path('job-stage/<uuid:job_stage_id>/edit/', JobStageController.edit, name='job_stage_edit'), # * NEW
    path('job-stage/<uuid:job_stage_id>/show/', JobStageController.show, name='job_stage_show'), # * NEW
    path('job-stage/<uuid:job_stage_id>/update/', JobStageController.update, name='job_stage_update'), # * NEW
    
    # JournalEntryController
    path('journal-entry/', JournalEntryController.index, name='journal_entry_index'), # * MATCHED
    path('journal-entry/create/', JournalEntryController.create, name='journal_entry_create'), # * NEW
    path('journal-entry/store/', JournalEntryController.store, name='journal_entry_store'), # * NEW
    path('journal-entry/<uuid:journal_entry_id>/', JournalEntryController.show, name='journal_entry_show'), # * NEW
    path('journal-entry/account/destroy/', JournalEntryController.account_destroy, name='journal_entry_account_destroy'), # * MATCHED
    path('journal-entry/<uuid:journal_entry_id>/destroy/', JournalEntryController.destroy, name='journal_entry_destroy'), # * NEW
    path('journal-entry/<uuid:journal_entry_id>/edit/', JournalEntryController.edit, name='journal_entry_edit'), # * NEW
    path('journal-entry/<uuid:journal_entry_id>/update/', JournalEntryController.update, name='journal_entry_update'), # * NEW
    path('journal-entry/journal/destroy/<uuid:item_id>/', JournalEntryController.journal_destroy, name='journal_entry_journal_destroy'), # * MATCHED
    
    # LabelController
    path('labels/', LabelController().get, name='labels.index'), # * MATCHED
    path('labels/create/', LabelController().create, name='labels.create'), # * NEW
    path('labels/store/',  LabelController().store,  name='labels.store'), # * NEW
    path('labels/<uuid:id>/edit/', LabelController().edit, name='labels.edit'), # * NEW
    path('labels/<uuid:id>/update/', LabelController().update, name='labels.update'), # * NEW
    path('labels/<uuid:id>/destroy/', LabelController().destroy, name='labels.destroy'), # * NEW
    
    # LanguageController
    path('language/create/', LanguageController.create_language, name='create.language'), # ! REPLACED create-language
    path('language/disable/', LanguageController.disable_lang, name='disablelanguage'), # ! REPLACED disable-language
    path('language/store/', LanguageController.store_language, name='store.language'), # ! REPLACED store-language
    path('language/<str:lang>/change/', LanguageController.change_language, name='change.language'), # ! REPLACED change-language/{lang}
    path('language/<str:lang>/destroy/', LanguageController.destroy_lang, name='lang.destroy'), # ! REPLACED /lang/{lang}
    path('language/<str:lang>/manage/', LanguageController.manage_language, name='manage.language'), # ! REPLACED manage-language/{lang}
    path('language/<str:lang>/store/', LanguageController.store_language_data, name='store.language.data'), # ! REPLACED store-language-data/{lang}
    
    path('leads/',LeadController.index,name='leads_index'), # * MATCHED
    path('leads/json/',LeadController.json,name='leads_json'), # * MATCHED
    path('leads/list/',LeadController.lead_list,name='leads_list'), # * MATCHED
    path('leads/order/',LeadController.order,name='leads_order'), # * MATCHED
    path('leads/<uuid:lead_id>/convert/',LeadController.convert_to_deal,name='leads_convert_store'), # * MATCHED
    path('leads/<uuid:lead_id>/show-convert/',LeadController.show_convert_to_deal,name='leads_convert'), # ! REPLACED /leads/{id}/show_convert
    path('leads/<uuid:lead_id>/calls/create/',LeadController.call_create,name='leads_calls_create'), # ! REPLACED /leads/{id}/call
    path('leads/<uuid:lead_id>/calls/store/',LeadController.call_store,name='leads_calls_store'), # ! REPLACED /leads{id}/call
    path('leads/<uuid:lead_id>/calls/<uuid:call_id>/destroy/',LeadController.call_destroy,name='leads_calls_destroy'), # ! REPLACED /leads/{id}/call/{cid}
    path('leads/<uuid:lead_id>/calls/<uuid:call_id>/edit/',LeadController.call_edit,name='leads_calls_edit'), # ! REPLACED /leads/{id}/call/{cid}/edit
    path('leads/<uuid:lead_id>/calls/<uuid:call_id>/update/',LeadController.call_update,name='leads_calls_update'), # ! REPLACED /leads/{id}/call/{cid}
    path('leads/<uuid:lead_id>/discussions/create/',LeadController.discussion_create,name='leads_discussions_create'), # ! REPLACED /leads/{id}/discussions
    path('leads/<uuid:lead_id>/discussions/store/',LeadController.discussion_store,name='leads_discussions_store'), # ! REPLACED /leads/{id}/discussions
    path('leads/<uuid:lead_id>/emails/create/',LeadController.email_create,name='leads_emails_create'), # ! REPLACED /leads/{id}/email
    path('leads/<uuid:lead_id>/emails/store/',LeadController.email_store,name='leads_emails_store'), # ! REPLACED /leads/{id}/email
    path('leads/<uuid:lead_id>/files/upload/',LeadController.file_upload,name='leads_upload'), # ! REPLACED /leads/{id}/file
    path('leads/<uuid:lead_id>/files/delete/<uuid:file_id>/',LeadController.file_delete,name='leads_file_delete'), # ! REPLACED /leads/{id}/file/delete/{fid}
    path('leads/<uuid:lead_id>/files/download/<uuid:file_id>/',LeadController.file_download,name='leads_file_download'), # ! REPLACED /leads/{id}/file/{fid}
    path('leads/<uuid:lead_id>/labels/',LeadController.labels,name='leads_labels'), # * MATCHED
    path('leads/<uuid:lead_id>/labels/store/',LeadController.label_store,name='leads_labels_store'), # ! REPLACED /leads/{id}/labels
    path('leads/<uuid:lead_id>/notes/store/',LeadController.note_store,name='leads_note_store'), # ! REPLACED /leads/{id}/note
    path('leads/<uuid:lead_id>/products/',LeadController.product_edit,name='leads_products'), # * MATCHED
    path('leads/<uuid:lead_id>/products/update/',LeadController.product_update,name='leads_products_update'), # ! REPLACED /leads/{id}/products
    path('leads/<uuid:lead_id>/products/<uuid:product_id>/destroy/',LeadController.product_destroy,name='leads_products_destroy'), # ! REPLACED /leads/{id}/product/{uid}
    path('leads/<uuid:lead_id>/sources/',LeadController.source_edit,name='leads_sources'), # * MATCHED
    path('leads/<uuid:lead_id>/sources/update/',LeadController.source_update,name='leads_sources_update'), # ! REPLACED /leads/{id}/sources
    path('leads/<uuid:lead_id>/sources/<uuid:source_id>/destroy/',LeadController.source_destroy,name='leads_sources_destroy'), # ! REPLACED /leads/{id}/sources/{uid}
    path('leads/<uuid:lead_id>/users/',LeadController.user_edit_show,name='leads_users'), # * MATCHED
    path('leads/<uuid:lead_id>/users/update/',LeadController.user_update,name='leads_users_update'), # ! REPLACED /leads/{id}/users
    path('leads/<uuid:lead_id>/users/<uuid:user_id>/destroy/',LeadController.user_destroy,name='leads_users_destroy'), # ! REPLACED /leads/{id}/users/{uid}
    
    path('leads/create/',LeadController.create,name='leads_create'), # * NEW
    path('leads/store/',LeadController.store,name='leads_store'), # * NEW
    path('leads/<uuid:lead_id>/',LeadController.show,name='leads_show'), # * NEW

    path('leads/<uuid:lead_id>/edit/',LeadController.edit,name='leads_edit'), # * NEW
    path('leads/<uuid:lead_id>/update/',LeadController.update,name='leads_update'), # * NEW
    path('leads/<uuid:lead_id>/destroy/',LeadController.destroy,name='leads_destroy'), # * NEW
    
    # LeadStageController
    path('lead-stages/', LeadStageController.index, name='lead_stages_index'),  # ! REPLACED lead_stages
    path('lead-stages/create/', LeadStageController.create, name='lead_stages_create'),  # * NEW
    path('lead-stages/order/', LeadStageController.order, name='lead_stages_order'),  # ! REPLACED /lead_stages/order
    path('lead-stages/store/', LeadStageController.store, name='lead_stages_store'),  # * NEW
    path('lead-stages/<uuid:lead_stage_id>/destroy/', LeadStageController.destroy, name='lead_stages_destroy'),  # * NEW
    path('lead-stages/<uuid:lead_stage_id>/edit/', LeadStageController.edit, name='lead_stages_edit'),  # * NEW
    path('lead-stages/<uuid:lead_stage_id>/update/', LeadStageController.update, name='lead_stages_update'),  # * NEW
    
    # LeaveController
    path('leave/', LeaveController.index, name='leave_index'),  # * MATCHED
    path('leave/change-action/', LeaveController.changeaction, name='leave_changeaction'),  # ! REPLACED leave/changeaction
    path('leave/create/', LeaveController.create, name='leave_create'),  # * NEW
    path('leave/json-count/', LeaveController.jsoncount, name='leave_jsoncount'),  # ! REPLACED leave/jsoncount
    path('leave/store/', LeaveController.store, name='leave_store'),  # * NEW
    path('leave/<uuid:leave_id>/action/', LeaveController.action, name='leave_action'),  # * MATCHED
    path('leave/<uuid:leave_id>/destroy/', LeaveController.destroy, name='leave_destroy'),  # * NEW
    path('leave/<uuid:leave_id>/edit/', LeaveController.edit, name='leave_edit'),  # * NEW
    path('leave/<uuid:leave_id>/update/', LeaveController.update, name='leave_update'),  # * NEW
    
    # LeaveTypeController
    path('leavetype/',LeaveTypeController.index,name='leavetype_index'),  # * MATCHED
    path('leavetype/create/',LeaveTypeController.create,name='leavetype_create'),  # * NEW
    path('leavetype/store/',LeaveTypeController.store,name='leavetype_store'),  # * NEW
    path('leavetype/<int:leavetype_id>/',LeaveTypeController.show,name='leavetype_show'),  # * NEW
    path('leavetype/<int:leavetype_id>/destroy/',LeaveTypeController.destroy,name='leavetype_destroy'),  # * NEW
    path('leavetype/<int:leavetype_id>/edit/',LeaveTypeController.edit,name='leavetype_edit'),  # * NEW
    path('leavetype/<int:leavetype_id>/update/',LeaveTypeController.update,name='leavetype_update'),  # * NEW
    
    # LoanController
    path('loan/', LoanController.as_view(), name="loan_view"), # * NEW
    path('loan/store/',LoanController.store,name='loan_store'),  # * NEW
    path('loan/<uuid:employee_id>/create/',LoanController.loan_create,name='loan_create'),  # * NEW
    path('loan/<uuid:loan_id>/destroy/',LoanController.destroy,name='loan_destroy'),  # * NEW
    path('loan/<uuid:loan_id>/edit/',LoanController.edit,name='loan_edit'),  # * NEW
    path('loan/<uuid:loan_id>/show/',LoanController.show,name='loan_show'),  # * NEW
    path('loan/<uuid:loan_id>/update/',LoanController.update,name='loan_update'),  # * NEW
    
    # LoanOptionController
    path('loanoption/',LoanOptionController.index,name='loanoption_index'),  # * MATCHED
    path('loanoption/create/',LoanOptionController.create,name='loanoption_create'),  # * NEW
    path('loanoption/store/',LoanOptionController.store,name='loanoption_store'),  # * NEW
    path('loanoption/<int:loanoption_id>/destroy/',LoanOptionController.destroy,name='loanoption_destroy'),  # * NEW
    path('loanoption/<int:loanoption_id>/edit/',LoanOptionController.edit,name='loanoption_edit'),  # * NEW
    path('loanoption/<int:loanoption_id>/show/',LoanOptionController.show,name='loanoption_show'),  # * NEW
    path('loanoption/<int:loanoption_id>/update/',LoanOptionController.update,name='loanoption_update'),  # * NEW
    
    # MeetingController
    path('meeting/', MeetingController.index, name='meeting.index'), # * MATCHED
    path('meeting/calendar', MeetingController.calendar, name='meeting.calendar'), # ! REPLACED meeting-calendar
    path('meeting/create', MeetingController.create, name='meeting.create'), # * NEW
    path('meeting/data', MeetingController.get_meeting_data, name='meeting.get_meeting_data'), # ! REPLACED meeting/get_meeting_data
    path('meeting/department', MeetingController.get_department, name='meeting.getdepartment'), # ! REPLACED meeting/getdepartment
    path('meeting/employee', MeetingController.get_employee, name='meeting.getemployee'), # ! REPLACED meeting/getemployee
    path('meeting/store', MeetingController.store, name='meeting.store'), # * NEW
    path('meeting/<int:meeting_id>/', MeetingController.show, name='meeting.show'), # * NEW
    path('meeting/<int:meeting_id>/destroy', MeetingController.destroy, name='meeting.destroy'), # * NEW
    path('meeting/<int:meeting_id>/edit', MeetingController.edit, name='meeting.edit'), # * NEW
    path('meeting/<int:meeting_id>/update', MeetingController.update, name='meeting.update'), # * NEW
        
    # NewPasswordController
    path('reset-password/', NewPasswordController.store, name='password_reset_store'), # * MATCHED
    path('reset-password/<str:token>', NewPasswordController.create, name='password_reset_create'), # * MATCHED
    
    # NotifcationTemplatesController
    path('notification_templates/', NotificationTemplatesController.index, name='notification_templates_index'), # * MATCHED
    path('notification_templates/create/', NotificationTemplatesController.create, name='notification_templates_create'),  # * NEW
    path('notification_templates/store/', NotificationTemplatesController.store, name='notification_templates_store'),  # * NEW
    path('notification_templates/<uuid:id>/', NotificationTemplatesController.show, name='notification_templates_show'),  # * NEW
    path('notification_templates/<uuid:id>/delete/', NotificationTemplatesController.destroy, name='notification_templates_destroy'),  # * NEW
    path('notification_templates/<uuid:id>/edit/', NotificationTemplatesController.edit, name='notification_templates_edit'),  # * NEW
    path('notification_templates/<uuid:id>/update/', NotificationTemplatesController.update, name='notification_templates_update'),  # * NEW
    path('notification_templates/<uuid:id>/<str:lang>/', NotificationTemplatesController.index, name='notification_templates_index_lang'), # * MATCHED
    
    # OtherPaymentController
    path('otherpayment/', OtherPaymentController.index, name='otherpayment_index'), # * MATCHED
    path('otherpayment/store/', OtherPaymentController.store, name='otherpayment_store'),   # * NEW
    path('otherpayment/<uuid:employee_id>/create', OtherPaymentController.otherpayment_create, name='otherpayment_create'),  # ! REPLACED otherpayments/create/{eid}
    path('otherpayment/<uuid:otherpayment_id>/destroy/', OtherPaymentController.destroy, name='otherpayment_destroy'), # * NEW
    path('otherpayment/<uuid:otherpayment_id>/edit/', OtherPaymentController.edit, name='otherpayment_edit'), # * NEW
    path('otherpayment/<uuid:otherpayment_id>/show/', OtherPaymentController.show, name='otherpayment_show'),    # * NEW
    path('otherpayment/<uuid:otherpayment_id>/update/', OtherPaymentController.update, name='otherpayment_update'),  # * NEW
    
    # OvertimeController
    path('overtime/', OvertimeController.index, name='overtime_index'), # * MATCHED
    path('overtime/store/', OvertimeController.store, name='overtime_store'),  # * NEW
    path('overtimes/create/<uuid:eid>/', OvertimeController.overtime_create, name='overtimes_create'), # * MATCHED
    path('overtime/<uuid:overtime_id>/', OvertimeController.show, name='overtime_show'),  # * NEW
    path('overtime/<uuid:overtime_id>/destroy/', OvertimeController.destroy, name='overtime_destroy'),  # * NEW
    path('overtime/<uuid:overtime_id>/edit/', OvertimeController.edit, name='overtime_edit'),  # * NEW
    path('overtime/<uuid:overtime_id>/update/', OvertimeController.update, name='overtime_update'),  # * NEW
    
    # PasswordResetLinkController
    path('forgot-password/', PasswordResetLinkController.as_view(), name='password_reset_link'), # * MATCHED
    
    # PaymentController
    re_path(r'^payments?/$', PaymentController.index, name='payment_index'), # * MATCHED
    re_path(r'^payments?/create/$', PaymentController.create, name='payment_create'),  # * NEW
    re_path(r'^payments?/index/$', PaymentController.index, name='payment_index'), # * MATCHED
    re_path(r'^payments?/<uuid:payment_id>/', PaymentController.show, name='payment_show'),  # * NEW
    re_path(r'^payments?/<uuid:payment_id>/delete/', PaymentController.destroy, name='payment_destroy'),  # * NEW
    re_path(r'^payments?/<uuid:payment_id>/edit/', PaymentController.edit, name='payment_edit'),  # * NEW
    re_path(r'^payments?/<uuid:payment_id>/update/', PaymentController.update, name='payment_update'),  # * NEW
    
    # PayslipController
    path('payslip/', PayslipController.index, name='payslip_index'), # * MATCHED
    path('payslip/create/', PayslipController.create, name='payslip_create'), # * NEW
    path('payslip/employee/', PayslipController.employee_payslip, name='payslip_employeepayslip'), # ! REPLACED payslip/employeepayslip
    path('payslip/export', PayslipController.export, name='payslip_export'), # ! REPLACED export/payslip
    path('payslip/store/', PayslipController.store, name='payslip_store'), # ! REPLACED payslip/
    path('payslip/search/', PayslipController.search_json, name='payslip_search_json'), # ! REPLACED payslip/search_json
    path('payslip/employee/show/', PayslipController.show_employee, name='payslip_showemployee'),
    path('payslip/delete/<uuid:id>/',  PayslipController.destroy, name='payslip_delete'), # ! REPLACED payslip/delete/{id}
    path('payslip/bulk-pay-create/<str:date>/', PayslipController.bulk_pay_create, name='payslip_bulk_pay_create'), # ! REPLACED payslip/bulk_pay_create/{date}
    path('payslip/bulk-payment/<str:date>/', PayslipController.bulk_payment, name='payslip_bulkpayment'), # ! REPLACED payslip/bulkpayment/{date}
    path('payslip/pdf/<uuid:id>/', PayslipController.payslip_pdf, ame='payslip_pdf'), # ! REPLACED payslip/payslipPdf/{id}
    path('payslip/employee/show-edit/<uuid:id>/', PayslipController.edit_employee, name='payslip_editemployee'), # ! REPLACED payslip/editemployee/{id}
    path('payslip/employee/update/<uuid:id>/', PayslipController.update_employee, name='payslip_updateemployee'), # ! REPLACED payslip/editemployee/{id}
    path('payslip/pay-salary/<uuid:id>/<str:date>/', PayslipController.pay_salary, name='payslip_paysalary'), # ! REPLACED payslip/pay-salary/{id}/{date}
    path('payslip/send/<uuid:id>/<str:month>/', PayslipController.send, name='payslip_send'), # * MATCHED
    path('payslip/pdf/<uuid:id>/date/<str:month>/', PayslipController.pdf, name='payslip_pdf'), # ! REPLACED payslip/payslipPdf/{id}
    
    # PayslipTypeController
    path('paysliptype/', PayslipTypeController.index, name='paysliptype_index'),
    path('paysliptype/create/', PayslipTypeController.create, name='paysliptype_create'),
    path('paysliptype/store/', PayslipTypeController.store, name='paysliptype_store'),
    path('paysliptype/<uuid:paysliptype_id>/destroy/', PayslipTypeController.destroy, name='paysliptype_destroy'),
    path('paysliptype/<uuid:paysliptype_id>/edit/', PayslipTypeController.edit, name='paysliptype_edit'),
    path('paysliptype/<uuid:paysliptype_id>/show/', PayslipTypeController.show, name='paysliptype_show'),
    path('paysliptype/<uuid:paysliptype_id>/update/', PayslipTypeController.update, name='paysliptype_update'),
    
    # PerformanceTypeController
    path('performance-type/', PerformanceTypeController.index, name='performance_type_index'), # ! REPLACED performanceType
    path('performance-type/create/', PerformanceTypeController.create, name='performance_type_create'), # * NEW
    path('performance-type/store/', PerformanceTypeController.store, name='performance_type_store'), # * NEW
    path('performance-type/<uuid:performance_type_id>/', PerformanceTypeController.show, name='performance_type_show'), # * NEW
    path('performance-type/<uuid:performance_type_id>/delete/', PerformanceTypeController.destroy, name='performance_type_destroy'), # * NEW
    path('performance-type/<uuid:performance_type_id>/edit/', PerformanceTypeController.edit, name='performance_type_edit'), # * NEW
    path('performance-type/<uuid:performance_type_id>/update/', PerformanceTypeController.update, name='performance_type_update'), # * NEW
    
    # PermissionController
    path('permission/', PermissionController.index, name='permission_index'),             # * MATCHED
    path('permission/create/', PermissionController.create, name='permission_create'),     # * NEW
    path('permission/store/', PermissionController.store, name='permission_store'),       # * NEW
    path('permission/<uuid:permission_id>/delete/', PermissionController.destroy, name='permission_destroy'), # * NEW
    path('permission/<uuid:permission_id>/edit/', PermissionController.edit, name='permission_edit'),         # * NEW
    path('permission/<uuid:permission_id>/update/', PermissionController.update, name='permission_update'),    # * NEW
    
    # PipelineController
    path('pipelines/', PipelineController.index, name='pipelines.index'), # * MATCHED
    path('pipelines/create', PipelineController.create, name='pipelines.create'),  # * NEW
    path('pipelines/store', PipelineController.store, name='pipelines.store'),  # * NEW
    path('pipelines/<uuid:pipeline_id>/', PipelineController.show, name='pipelines.show'),  # * NEW
    path('pipelines/<uuid:pipeline_id>/destroy', PipelineController.destroy, name='pipelines.destroy'),  # * NEW
    path('pipelines/<uuid:pipeline_id>/edit', PipelineController.edit, name='pipelines.edit'),  # * NEW
    path('pipelines/<uuid:pipeline_id>/update', PipelineController.update, name='pipelines.update'),  # * NEW
    
    # PlanController
    path('plans/', PlanController.index, name='plans_index'),
    path('plans/create/', PlanController.create, name='plans_create'),
    path('plans/store/', PlanController.store, name='plans_store'),
    path('plans/user-plan/', PlanController.user_plan, name='plans_user_plan'),
    path('plans/<uuid:plan_id>/edit/', PlanController.edit, name='plans_edit'),
    path('plans/<uuid:plan_id>/update/', PlanController.update, name='plans_update'),
    
    # PlanRequestController
    path('plan-request/', PlanRequestController.index, name='plan_request_index'), # ! REPLACED plan_request/
    path('plan-request/cancel/<uuid:id>/', PlanRequestController.cancel_request, name='request_cancel'), # ! REPLACE request_cancel/{id}
    path('plan-request/frequency/<uuid:plan_id>/', PlanRequestController.request_view, name='request_view'), # ! REPLACED request_frequency/{id}
    path('plan-request/send/<uuid:plan_id>/', PlanRequestController.user_request, name='send_request'), # ! REPLACED request_send/{id}/
    path('plan-request/<uuid:id>/response/<uuid:response>/', PlanRequestController.accept_request, name='response_request'), # ! REPLACED request_response/{id}/{response}
    
    # PosController
    path('pos/', PosController.index, name='pos_index'), # * MATCHED
    path('pos/barcode/', PosController.barcode, name='pos_barcode'), # ! REPLACED barcode/pos
    path('pos/cart-discount/', PosController.cart_discount, name='pos_cart_discount'), # ! REPLACED /cartdiscount
    path('pos/create/', PosController.create, name='pos_create'), # * NEW
    path('pos/product/', PosController.get_product, name='pos_getproduct'), # ! REPLACED pos/getproduct
    path('pos/receipt/', PosController.receipt, name='pos_receipt'), # ! REPLACED pos-receipt
    path('pos/report/', PosController.report, name='pos_report'), # ! * REPLACED report/pos
    path('pos/settings/', PosController.get_settings, name="pos_settings"), # ! REPLACED setting/pos
    path('pos/store/', PosController.store, name='pos_data_store'), # ! REPLACED pos/data/store
    path('pos/<str:ids>/', PosController.show, name='pos_show'), # * NEW
    path('pos/barcode/print/', PosController.print_barcode, name='pos_print'), # ! REPLACED print/pos
    path('pos/barcode/settings/', PosController.barcode_settings_store, name="pos_barcode_settings_store"), # ! REPLACED barcode/settings
    path('pos/print/view/', PosController.print_view, name='pos_printview'), # ! REPLACED printview/pos
    path('pos/template/setting/', PosController.save_pos_template_settings, name='pos_template_setting'), # * MATCHED
    path('pos/pdf/<uuid:id>/', PosController.pos, name='pos_pdf'), # * MATCHED
    path('pos/<str:ids>/delete/', PosController.destroy, name='pos_destroy'), # * NEW
    path('pos/<str:ids>/edit/', PosController.edit, name='pos_edit'), # * NEW
    path('pos/<str:ids>/update/', PosController.update, name='pos_update'), # * NEW
    path('pos/preview/<str:template>/<str:color>/', PosController.preview_pos, name='pos_preview'), # * MATCHED
        
    # ProductServiceController
    path('product-service/', ProductServiceController.index, name='product_service_index'), # ! REPLACED productservice
    path('product-service/create/', ProductServiceController.create, name='product_service_create'), # * NEW
    path('product-service/index/', ProductServiceController.index, name='product_service_index'), # * NEW
    path('product-service/export/', ProductServiceController.export, name='product_service_export'), # ! REPLACED export/productservice
    path('product-service/search/', ProductServiceController.search_products, name='search_products'), # ! REPLACED search-products
    path('product-service/<uuid:product_id>/', ProductServiceController.show, name='product_service_show'), # * NEW
    path('product-service/cart/empty/', ProductServiceController.empty_cart, name='empty_cart'), # ! REPLACED empty-cart
    path('product-service/cart/remove/', ProductServiceController.remove_from_cart, name='remove_from_cart'), # ! REPLACED remove-from-cart
    path('product-service/cart/update/', ProductServiceController.update_cart, name='update_cart'), # ! REPLACED update-cart
    path('product-service/cart/warehouse-empty/', ProductServiceController.warehouse_empty_cart, name='warehouse_empty_cart'), # ! REPLACED warehouse-empty-cart
    path('product-service/import/data/', ProductServiceController.import_data, name='product_service_import'), # ! REPLACED import/productservice
    path('product-service/import/file/', ProductServiceController.import_file, name='product_service_file_import'), # ! REPLACED import/productservice/file
    path('product-service/<uuid:product_id>/delete/', ProductServiceController.destroy, name='product_service_destroy'), # * NEW
    path('product-service/<uuid:product_id>/detail/', ProductServiceController.warehouse_detail, name='product_service_detail'), # ! REPLACED productservice/{id}/detail
    path('product-service/<uuid:product_id>/edit/', ProductServiceController.edit, name='product_service_edit'), # * NEW
    path('product-service/<uuid:product_id>/update/', ProductServiceController.update, name='product_service_update'), # * NEW
    path('product-service/cart/add/<uuid:product_id>/<str:session_key>/', ProductServiceController.add_to_cart, name='add_to_cart'), # ! REPLACED add-to-cart/{id}/{session}
    
    # ProductServiceCategoryController
    path('product-category/',ProductServiceCategoryController.index,name='product-category.index'), # * NEW
    path('product-category/account/',ProductServiceCategoryController.get_account,name='productServiceCategory.getaccount'), # ! REPLACED product-category/getaccount
    path('product-category/create/',ProductServiceCategoryController.create,name='product-category.create'), # * NEW
    path('product-category/list/',ProductServiceCategoryController.get_product_categories,name='product.categories'), # ! REPLACED product-categories
    path('product-category/search/',ProductServiceCategoryController.search_products_by_name,name='name.search.products'), # ! REPLACED name-search-products
    path('product-category/store/',ProductServiceCategoryController.store,name='product-category.store'), # * NEW
    path('product-category/<uuid:pk>',ProductServiceCategoryController.show,name='product-category.show'), # * NEW
    path('product-category/<uuid:pk>/delete/',ProductServiceCategoryController.destroy,name='product-category.destroy'), # * NEW
    path('product-category/<uuid:pk>/edit',ProductServiceCategoryController.edit,name='product-category.edit'), # * NEW
    path('product-category/<uuid:pk>/update/',ProductServiceCategoryController.update,name='product-category.update'), # * NEW
    
    # ProductServiceUnitController
    path('product-unit/',ProductServiceUnitController.index,name='product-unit.index'), # * MATCHED
    path('product-unit/create/',ProductServiceUnitController.create,name='product-unit.create'), # * NEW
    path('product-unit/store/',ProductServiceUnitController.store,name='product-unit.store'), # * NEW
    path('product-unit/<uuid:id>/destroy/',ProductServiceUnitController.destroy,name='product-unit.destroy'), # * NEW
    path('product-unit/<uuid:id>/edit/',ProductServiceUnitController.edit,name='product-unit.edit'), # * NEW
    path('product-unit/<uuid:id>/update/',ProductServiceUnitController.update,name='product-unit.update'), # * NEW
    
    # ProductStockController
    path('product-stock/', ProductStockController.index, name='productstock_index'), # ! REPLACED productstock
    path('product-stock/create/', ProductStockController.create, name='productstock_create'), # * NEW
    path('product-stock/store/', ProductStockController.store, name='productstock_store'), # * NEW
    path('product-stock/<uuid:id>/destroy/', ProductStockController.destroy, name='productstock_destroy'), # * NEW
    path('product-stock/<uuid:id>/edit/', ProductStockController.edit, name='productstock_edit'), # * NEW
    path('product-stock/<uuid:id>/show/', ProductStockController.show, name='productstock_show'), # * NEW
    path('product-stock/<uuid:id>/update/', ProductStockController.update, name='productstock_update'), # * NEW
    
    # ProjectController
    path('projects/', ProjectController.index, name='projects.list'), # * MATCHED
    path('projects/chart/', ProjectController.get_project_chart, name='projects.chart'), # * NEW
    path('projects/create/', ProjectController.create, name='projects.create'), # * NEW
    path('projects/store/', ProjectController.store, name='projects.store'), # * NEW
    path('projects/users/', ProjectController.load_user, name='project.user'), # ! REPLACED projects-users
    path('projects/view/', ProjectController.filter_project_view, name='filter.project.view'), # ! REPLACED projects-view
    path('projects/<uuid:project_id>/', ProjectController.show, name='projects.show'), # * NEW
    path('projects/copy/<uuid:project_id>/', ProjectController.copy_project, name='project.copy'), # ! REPLACED project/copy/{id}
    path('projects/copy/link/<uuid:project_id>/', ProjectController.copy_link_setting, name='projects.copy.link'), # * MATCHED
    path('projects/copy/store/<uuid:project_id>/', ProjectController.copy_project_store, name='projects.copy_store'), # * MATCHED
    path('projects/invite/user/', ProjectController.invite_project_user_member, name='invite.project.user.member'), # ! REPLACED invice-project-user-member
    path('projects/invite/view/<uuid:project_id>/', ProjectController.invite_member_view, name='invite.project.member.view'), # ! REPLACED invite-project-member/{id}
    path('projects/link/setting-create', ProjectController.copy_link_setting_create, name='project.copy_link_setting_create'), # ! REPLACED /projects{id}/settingcreate
    path('projects/link/copy/<uuid:project_id>/', ProjectController.project_copy_link, name='project.copy_link'), # ! REPLACED /projects/copylink/{id}
    path('projects/link/<str:project_id>/<str:lang>', ProjectController.project_link, name='projects.link'), # * MATCHED
    path('projects/<uuid:project_id>/bug/', ProjectController.bug, name='task.bug'), # * MATCHED
    path('projects/<uuid:project_id>/bug/create/', ProjectController.bug_create, name='task.bug.create'), # * MATCHED
    path('projects/bug/kanban/order/', ProjectController.bug_kanban_order, name='bug.kanban.order'), # * MATCHED
    path('projects/<uuid:project_id>/bug/kanban/', ProjectController.bug_kanban, name='bug.kanban'), # * MATCHED
    path('projects/<uuid:project_id>/bug/store/', ProjectController.bug_store, name='task.bug.store'), # * MATCHED
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/comment-store/', ProjectController.bug_comment_store, name='bug.comment.store'), # ! REPLACED projects/{id}/bug/{bid}/comment
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/destroy/', ProjectController.bug_destroy, name='task.bug.destroy'), # * MATCHED 
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/edit/', ProjectController.bug_edit, name='task.bug.edit'), # * MATCHED
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/show/', ProjectController.bug_show, name='task.bug.show'), # * MATCHED
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/update/', ProjectController.bug_update, name='task.bug.update'), # * MATCHED
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/comment/<uuid:comment_id>/destroy/', ProjectController.bug_comment_destroy, name='bug.comment.destroy'), # ! REPLACED projects/bug/comments/{id}
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/file/store/', ProjectController.bug_file_store, name='bug.comment.file.store'), # ! REPLACED projects/bug/{bid}/file
    path('projects/<uuid:project_id>/bug/<uuid:bug_id>/file/<uuid:file_id>/destroy/', ProjectController.bug_comment_destroy_file, name='bug.comment.file.destroy'), # ! REPLACED projects/bug/file/{id}
    path('projects/<uuid:project_id>/destroy/', ProjectController.destroy, name='projects.destroy'), # * NEW
    path('projects/<uuid:project_id>/edit/', ProjectController.edit, name='projects.edit'), # * NEW
    path('projects/<uuid:project_id>/gantt/store/', ProjectController.gantt_post, name='projects.gantt.post'), # ! REPLACED projects/{id}/gantt
    path('projects/<uuid:project_id>/gantt/<str:duration>', ProjectController.gantt, name='projects.gantt'), # ! REPLACED projects/{id}/gantt/{duration?}
    path('projects/<uuid:project_id>/milestone/', ProjectController.milestone, name='project.milestone'), # * MATCHED
    path('projects/<uuid:project_id>/milestone/store/', ProjectController.milestone_store, name='project.milestone.store'), # ! REPLACED projects/{id}/milestone
    path('projects/<uuid:project_id>/milestone/<uuid:milestone_id>/destroy/', ProjectController.milestone_destroy, name='project.milestone.destroy'), # ! REPLACED projects/milestone/{id}
    path('projects/<uuid:project_id>/milestone/<uuid:milestone_id>/edit/', ProjectController.milestone_edit, name='project.milestone.edit'), # ! REPLACED projects/nilestone/{id}/edit
    path('projects/<uuid:project_id>/milestone/<uuid:milestone_id>/show/', ProjectController.milestone_show, name='project.milestone.show'), # ! REPLACED projects/milestone/{id}/show
    path('projects/<uuid:project_id>/milestone/<uuid:milestone_id>/update/', ProjectController.milestone_update, name='project.milestone.update'), # ! # REPLACED projects/milestone/{id}
    path('projects/<uuid:project_id>/task/store/<str:slug>', ProjectController.store_project_task_stage, name='project.store.task-stage'), # ! REPLACED projects/{id}/store-stages/{slug}
    path('projects/<uuid:project_id>/tracker/', ProjectController.tracker, name='project.tracker'), # ! REPLACED projects/time-trakcer/{ìd}
    path('projects/<uuid:project_id>/users/<uuid:user_id>/destroy/', ProjectController.destroy_project_user, name='projects.user.destroy'),  # ! REPLACED projecest/{id}/users/{uid}
    path('projects/<uuid:project_id>/users/<uuid:user_id>/permission/', ProjectController.user_permssion, name="project.user.permission"), # * MATCHED
    path('projects/<uuid:project_id>/users/<uuid:user_id>/permission/store/', ProjectController.user_permission_store, name='project.user.permission.store'), # ! REPLACED projects/{id}/user/{uid}/permission
    path('projects/<uuid:project_id>/users/<uuid:user_id>/remove', name='project.user.remove'), # ! REPLACED remove-user-from-project/{project_id}/{user_id}
    path('projects/share/<uuid:project_id>/<str:lang>', ProjectController.share_project, name="projects.share"), # ! REPLACED /shareproject/{lang?}
    
    # ProjectReportController
    path("project-report/", ProjectReportController.index, name="project_report_index"),
    path("project-report/<uuid:project_id>/", ProjectReportController.show, name="project_report_show"),
    path('project-report/<uuid:project_id/chart/')
    path("project-report/<uuid:project_id>/export/", ProjectReportController.export, name="project_report_export"),
    
    # PromotionController
    path('promotion/', PromotionController().index, name='promotion_index'), # * MATCHED
    path('promotion/create/', PromotionController().create, name='promotion_create'), # * NEW
    path('promotion/store/', PromotionController().store, name='promotion_store'), # * NEW
    path('promotion/<uuid:promotion_id>/delete/',PromotionController().destroy,name='promotion_destroy'), # * NEW
    path('promotion/<uuid:promotion_id>/edit/',PromotionController().edit,name='promotion_edit'), # * NEW
    path('promotion/<uuid:promotion_id>/show/',PromotionController().show,name='promotion_show'), # * NEW
    path('promotion/<uuid:promotion_id>/update/',PromotionController().update,name='promotion_update'), # * NEW
    
    # RegisteredUserController
    path('register/', RegisteredUserController().store, name='register_store'),
    path('register/<str:lang>/', RegisteredUserController().show_registration_form, name='register_show'),
    
    # UserController
    path('user/', UserController.index, name='users_index'), # * MATCHED
    path('users/', UserController.as_view(), name="users_view"), # * MATCHED
    path('user/change-mode/', UserController.change_mode, name='change_mode'), # ! REPLACED /change/mode
    path('users/check-exists', UserController.check_user_exists, name="users_check_exists"), # ! REPLACED checkuserexists
    path('user/create/', UserController.create, name='user_create'), # * NEW
    path('user/profile/', UserController.profile, name='user_profile'), # ! REPLACED profile
    path('users/search', UserController.search, name="user_search"), # ! REPLACED /search
    path('user/show/', UserController.show, name='user_show'), # * NEW
    path('user/store/', UserController.store, name='user_store'), # * NEW
    path('user/update-password/', UserController.update_password, name='update_password'), # ! REPLACED change-password
    path('user/user-logs/', UserController.user_logs, name='user_log'), # ! REPLACED /userlogs
    path('users/view', UserController.filter_user_view, name="users_view"),  # ! REPLACED /users-view
    path('user/profile/create', UserController.update_profile, name="update_profile"),  # ! REPLACED /profile
    path('user/profile/edit', UserController.edit_profile, name='edit_profile'), # ! REPLACED edit-profile
    path('user/destroy/<uuid:user_id>/', UserController.destroy, name='user_destroy'), # ! REPLACED user/{id}
    path('user/edit/<uuid:user_id>/', UserController.edit, name='user_edit'), # * NEW
    path('user/notifications-seen/<uuid:user_id>/', UserController.notifications_seen, name="notifications_seen"), # ! REPLACED /{uid}/notification/seen
    path('user/update/<uuid:user_id>/', UserController.update, name='user_update'), # * NEW
    path('user/user-logs-destroy/<uuid:user_id>/', UserController.user_logs_destroy, name='user_log_destroy'), # ! REPLACED userlogs/{id} for DELETE
    path('user/<uuid:user_id>/plan', UserController.upgrade_plan, name='upgrade_plan'), # * MATCHED
    path('user/reset-password/create/<uuid:user_id>/', UserController.user_password_recreate, name='user_password_reset'), # ! REPLACED user-reset-password/{id} for POST
    path('user/reset-password/view/<uuid:encrypted_id>/', UserController.user_password_view, name='user_password'), # ! REPLACED user-reset-password/{id} for GET
    path('user/task-info/<uuid:type>/<uuid:user_id>', UserController.get_project_task), # ! REPLACE user/{id}/info/{type}
    path('user/user-logs/view/<uuid:log_id>/', UserController.user_logs_view, name='user_log_view'), # ! REPLACED userlogs/{id} for GET
    path('user/<uuid:user_id>/plan/<uuid:plan_id>/', UserController.active_plan, name='active_plan'), # * MATCHED
    path('todo/create', UserController.todo_store, name='todo_store'), # * MATCHED
    path('todo/<uuid:todo_id>/destroy', UserController.todo_destroy, name='todo_destroy'), # * MATCHED
    path('todo/<uuid:todo_id>/update', UserController.todo_update, name='todo_update'), # * MATCHED
    
     # VerifyEmailController
    path('verify/<uuid:id>/<str:hash>', VerifyEmailController.as_view(), name='verify_email'), # * MATCHED
    
        # ! END OF SORTED
        
    # FaqController
    path('faq/', FaqController.index, name='faq_index'),
    path('faq/create/', FaqController.create, name='faq_create'),
    path('faq/store/', FaqController.store, name='faq_store'),
    path('faq/show/<uuid:id>/', FaqController.show, name='faq_show'),
    path('faq/edit/<uuid:id>/', FaqController.edit, name='faq_edit'),
    path('faq/update/<uuid:id>/', FaqController.update, name='faq_update'),
    path('faq/destroy/<uuid:id>/', FaqController.destroy, name='faq_destroy'),
    
    # FeaturesController
    path('features/', FeaturesController.index, name='features_index'),
    path('features/create/', FeaturesController.create, name='features_create'),
    path('features/store/', FeaturesController.store, name='features_store'),
    path('features/show/<uuid:id>/', FeaturesController.show, name='features_show'),
    path('features/edit/<uuid:id>/', FeaturesController.edit, name='features_edit'),
    path('features/update/<uuid:id>/', FeaturesController.update, name='features_update'),
    path('features/destroy/<uuid:id>/', FeaturesController.destroy, name='features_destroy'),
    
    # HomeController
    path('home/', HomeController.index, name='home_index'),
    path('home/create/', HomeController.create, name='home_create'),
    path('home/store/', HomeController.store, name='home_store'),
    path('home/show/<uuid:id>/', HomeController.show, name='home_show'),
    path('home/edit/<uuid:id>/', HomeController.edit, name='home_edit'),
    path('home/update/<uuid:id>/', HomeController.update, name='home_update'),
    path('home/destroy/<uuid:id>/', HomeController.destroy, name='home_destroy'),
    
    # JoinUsController
    path('joinus/', JoinUsController.index, name='joinus_index'),
    path('joinus/create/', JoinUsController.create, name='joinus_create'),
    path('joinus/store/', JoinUsController.store, name='joinus_store'),
    path('joinus/show/<uuid:id>/', JoinUsController.show, name='joinus_show'),
    path('joinus/edit/<uuid:id>/', JoinUsController.edit, name='joinus_edit'),
    path('joinus/update/<uuid:id>/', JoinUsController.update, name='joinus_update'),
    path('joinus/destroy/<uuid:id>/', JoinUsController.destroy, name='joinus_destroy'),
    path('joinus/userstore/', JoinUsController.join_us_user_store, name='joinus_user_store'),
    
    # LandingPageController
    path('landingpage/', LandingPageController.index, name='landingpage_index'),
    path('landingpage/create/', LandingPageController.create, name='landingpage_create'),
    path('landingpage/store/', LandingPageController.store, name='landingpage_store'),
    path('landingpage/show/<uuid:id>/', LandingPageController.show, name='landingpage_show'),
    path('landingpage/edit/<uuid:id>/', LandingPageController.edit, name='landingpage_edit'),
    path('landingpage/update/<uuid:id>/', LandingPageController.update, name='landingpage_update'),
    path('landingpage/destroy/<uuid:id>/', LandingPageController.destroy, name='landingpage_destroy'),
    
    # PricingPlanController
    path('pricingplan/', PricingPlanController.index, name='pricing_plan_index'),
    path('pricingplan/create/', PricingPlanController.create, name='pricing_plan_create'),
    path('pricingplan/store/', PricingPlanController.store, name='pricing_plan_store'),
    path('pricingplan/show/<uuid:id>/', PricingPlanController.show, name='pricing_plan_show'),
    path('pricingplan/edit/<uuid:id>/', PricingPlanController.edit, name='pricing_plan_edit'),
    path('pricingplan/update/<uuid:id>/', PricingPlanController.update, name='pricing_plan_update'),
    path('pricingplan/destroy/<uuid:id>/', PricingPlanController.destroy, name='pricing_plan_destroy'),
    
    # ScreenshotsController
    path('screenshots/', ScreenshotsController.index, name='screenshots_index'),
    path('screenshots/create/', ScreenshotsController.create, name='screenshots_create'),
    path('screenshots/store/', ScreenshotsController.store, name='screenshots_store'),
    path('screenshots/show/<uuid:id>/', ScreenshotsController.show, name='screenshots_show'),
    path('screenshots/edit/<uuid:id>/', ScreenshotsController.edit, name='screenshots_edit'),
    path('screenshots/update/<uuid:id>/', ScreenshotsController.update, name='screenshots_update'),
    path('screenshots/destroy/<uuid:id>/', ScreenshotsController.destroy, name='screenshots_destroy'),
    
    # TestimonialsController
    path('testimonials/', TestimonialsController.index, name='testimonials_index'),
    path('testimonials/create/', TestimonialsController.create, name='testimonials_create'),
    path('testimonials/store/', TestimonialsController.store, name='testimonials_store'),
    path('testimonials/show/<uuid:id>/', TestimonialsController.show, name='testimonials_show'),
    path('testimonials/edit/<uuid:id>/', TestimonialsController.edit, name='testimonials_edit'),
    path('testimonials/update/<uuid:id>/', TestimonialsController.update, name='testimonials_update'),
    path('testimonials/destroy/<uuid:id>/', TestimonialsController.destroy, name='testimonials_destroy'),
    
]

