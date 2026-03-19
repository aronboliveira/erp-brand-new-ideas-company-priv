<?php

use App\Http\Controllers\{
    Ssr\AiTemplateController,
    AllowanceController,
    AllowanceOptionController,
    AnnouncementController,
    AppraisalController,
    Shapes\AssetController,
    AwardController,
    AwardTypeController,
    BankAccountController,
    Bills\BankTransferController,
    BankTransferPaymentController,
    BenefitPaymentController,
    Bills\BillController,
    BranchController,
    Bills\BudgetController,
    BugStatusController,
    Bills\CashfreeController,
    ChartOfAccountController,
    Individuals\ClientController,
    CommissionController,
    CompanyPolicyController,
    Individuals\CompetenciesController,
    ComplaintController,
    ContractController,
    Planning\ContractTypeController,
    Bills\CouponController,
    Bills\CreditNoteController,
    Individuals\CustomerController,
    Shapes\CustomFieldController,
    CustomQuestionController,
    Shapes\DashboardController as DSBC,
    DealController,
    DebitNoteController,
    DeductionOptionController,
    DepartmentController,
    DesignationController,
    Shapes\DocumentController,
    Ssr\DocumentUploadController,
    Shapes\EmailTemplateController,
    Individuals\EmployeeAttendanceController,
    EmployeeController,
    EventController,
    ExpenseController,
    Shapes\FormBuilderController,
    Planning\GoalController,
    GoalTrackingController,
    GoalTypeController,
    HolidayController,
    IndicatorController,
    Planning\InterviewScheduleController,
    InvoiceController,
    JobApplicationController,
    JobCategoryController,
    JobController,
    Individuals\JobStageController,
    JournalEntryController,
    LabelController,
    LanguageController,
    LeadController,
    LeadStageController,
    Planning\LeaveController,
    Planning\LeaveTypeController,
    LoanController,
    LoanOptionController,
    MeetingController,
    Info\NotificationTemplateController,
    OtherPaymentController,
    OvertimeController,
    PaymentController,
    PayslipController,
    PayslipTypeController,
    PerformanceTypeController,
    PermissionController,
    Configs\PipelineController,
    Planning\PlanController,
    Planning\PlanRequestController,
    PosController,
    Products\ProductServiceCategoryController,
    Products\ProductServiceController,
    Products\ProductServiceUnitController,
    ProductStockController,
    ProjectController,
    ProjectReportController as PRPC,
    Planning\ProjectStagesController,
    ProjectTaskController,
    PromotionController,
    ProposalController,
    PurchaseController,
    ReportController as RPC,
    ResignationController,
    RevenueController,
    Individuals\RoleController,
    SaturationDeductionController,
    Planning\SetSalaryController,
    SourceController,
    StageController,
    SupportController,
    SystemController,
    TaskStageController,
    TaxController,
    Planning\TerminationController,
    Planning\TerminationTypeController,
    TimesheetController,
    Planning\TimeTrackerController,
    TrainerController,
    TrainingController,
    TrainingTypeController,
    TransactionController,
    TransferController,
    TravelController,
    Individuals\UserController,
    VendorController,
    Companies\WarehouseController,
    WarehouseTransferController,
    WarningController,
    ZoomMeetingController,
    StripePaymentController,
};
use App\Config\Constants\{
    DatabaseConstants,
    MiddlewaresConstants as MWC,
    ViewsConstants as VW
};
use Modules\LandingPage\Config\Constants\RoutesResourcesConstants as RRC;
use Illuminate\Support\Facades\Route as R;
use Symfony\Component\Console\Output\ConsoleOutput;

// R::get('/', function () {
//     return view('welcome');
// });

// R::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware([MWC::AUTH])->name('dashboard');

require __DIR__ . '/auth.php';

// TEMP: $output = new ConsoleOutput();
$msg = 'Mapping web main routes...';
// TEMP: app()->runningInConsole() ?
// TEMP:     $output->writeln('<question> ' . $msg . ' </question>') : $output->writeln($msg);

//================================= Home ====================================//
#region
R::middleware([MWC::WEB, MWC::AUTH])
    ->group(function () {
        R::get(
            '/',
            [DSBC::class, DSBC::ACC_DSB_IDX]
        )->name(RRC::HM . '.index');
    });
//R::get('/register/{lang?}', function () {
//    $settings = Utility::settings();
//    $lang = $settings[SettingsConstants::DEF_LNG];
//
//    if($settings[SettingsConstants::ENB_SGU] == 'on'){
//        return view("auth.register", compact('lang'));
//       // R::get('/register', 'Auth\RegisteredUserController@showRegistrationForm')->name('register');
//    }else{
//        return Redirect::to('login');
//    }
//
//});
//
#endregion
//================================= Copy Link  ====================================//
#region
R::get(VW::CST . '/' . VW::INV . '/{id}/', [InvoiceController::class, InvoiceController::IV_LK])->name(VW::INV . '.link.copy');
R::get(VW::CST . '/' . VW::PPS . '/{id}/', [ProposalController::class, ProposalController::IV_LK])->name(VW::PPS . '.link.copy');
R::get(VW::PPS . '/pdfs/{id}', [ProposalController::class, 'proposal'])->name(VW::PPS . '.pdf')
    ->middleware([MWC::XSS, MWC::REV]);
R::get(VW::VND . '/' . VW::BIL . '/{id}/', [BillController::class, BillController::IV_LK])->name(VW::BIL . '.link.copy');
R::get(VW::VND . '/' . VW::PRC . '/{id}/', [PurchaseController::class, PurchaseController::PRC_LK])->name(VW::PRC . '.link.copy');
#endregion
//================================= Invoice Payment Gateways  ====================================//
#region
R::post(VW::CST . '/pay-with-bank', [BankTransferPaymentController::class, BankTransferPaymentController::CST_PAY_BNK])->name(VW::CST . '.pay.with.bank')
    ->middleware([MWC::XSS]);
R::get(VW::INV . '/{id}/action', [BankTransferPaymentController::class, BankTransferPaymentController::INV_ACT])->name(VW::INV . '.action');
R::post(VW::INV . '/{id}/change-action', [BankTransferPaymentController::class, BankTransferPaymentController::INV_CG_STT])->name(VW::INV . '.change.status');
R::any(VW::INV . '/with-benefit', [BenefitPaymentController::class, BenefitPaymentController::INV_PAY_BF])->name(VW::INV . '.benefit.initiate');
R::any(VW::INV . '/benefit/{invoice_id}/{amount}', [BenefitPaymentController::class, BenefitPaymentController::GET_INV_PAY_STT])->name(VW::INV . '.benefit.callback');
R::post(VW::INV . '/with-cashfree/payment', [CashfreeController::class, CashfreeController::INV_PAY_CF])->name(VW::CST . '.pay.with.cashfree');
R::any(VW::INV . '/with-cashfree/status', [CashfreeController::class, CashfreeController::GET_INV_PAY_STT])->name(VW::INV . '.cashfree.payment.success');
#endregion
//================================= Career Page  ====================================//
#region
R::get(VW::CRR . '/{id}/{lang}', [JobController::class, 'career'])->name(VW::CRR)
    ->middleware([MWC::XSS]);
R::get(VW::JB . '/requirement/{code}/{lang}', [JobController::class, JobController::JB_RQ])->name(VW::JB . '.requirement')
    ->middleware([MWC::XSS]);
R::get(VW::JB . '/apply/{code}/{lang}', [JobController::class, JobController::JB_AP])->name(VW::JB . '.apply')->middleware([MWC::XSS]);
R::post(VW::JB . '/apply/data/{code}', [JobController::class, JobController::JB_AP_DT])->name(VW::JB . '.apply.data')->middleware([MWC::XSS]);
#endregion
//================================= Project Copy Module  ====================================//
#region
R::get(VW::PRJ . '/copy-link/{id}', [ProjectController::class, 'projectCopyLink'])->name(VW::PRJ . '.copy_link');
R::any(VW::PRJ . '/link/{id}/{lang?}', [ProjectController::class, 'projectlink'])->name(VW::PRJ . '.link')->middleware([MWC::XSS]);
R::get(VW::PRJ . '.' . VW::TMS . '/table-view', [TimesheetController::class, TimesheetController::FT_TMS_TBL])->name(VW::PRJ . '.' . VW::TMS . '.filters.table.view')
    ->middleware([MWC::XSS]);
R::get(VW::INV . '/pdf/{id}', [InvoiceController::class, 'invoice'])->name(VW::INV . '.pdf')
    ->middleware([MWC::XSS, MWC::REV]);
//================================= Dashboards ====================================//
#region
R::get('/dashboard', [DSBC::class, DSBC::ACC_DSB_IDX])
    ->name(DSBC::ENTITY)
    ->middleware([MWC::XSS, MWC::REV]);

// Footer public pages (stub routes to avoid ViewException in admin footer)
R::get('/terms-and-conditions', fn() => redirect('/dashboard'))->name('terms_and_conditions');
R::get('/privacy-policy', fn() => redirect('/dashboard'))->name('privacy_policy');
R::get('/about-us', fn() => redirect('/dashboard'))->name('about_us');
#endregion
#endregion
//================================= Invoice Payment Gateways  ====================================//
#region
R::group(['middleware' => [MWC::VF]], function () {
    //================================= Dashboard root  ====================================//
    #region
    R::get('/account-dashboard', [DSBC::class, DSBC::ACC_DSB_IDX])
        ->name(DSBC::ENTITY)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::get('/project-dashboard', [DSBC::class, DSBC::PRJ_DSB_IDX])
        ->name(VW::PRJ . '.dashboard')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::get('/hrm-dashboard', [DSBC::class, DSBC::HRM_DSB_IDX])->name('hrm.dashboard')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::get('/crm-dashboard', [DSBC::class, DSBC::CRM_DSB_IDX])->name('crm.dashboard')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::get('/pos-dashboard', [DSBC::class, DSBC::POS_DSB_IDX])->name(VW::POS . '.dashboard')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::get('profile', [UserController::class, 'profile'])->name('profile')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::any('edit-profile', [UserController::class, UserController::EDT_PRF])->name(VW::USR . '.account.update')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);


    R::post('change-password', [UserController::class, UserController::UPD_PSW])
        ->name(VW::USR . '.password.update');

    R::match(['GET', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], 'user-reset-password/{id}', [UserController::class, UserController::USR_PSW])->name(VW::USR . '.reset');

    R::post('user-reset-password/{id}', [UserController::class, UserController::USR_PSW_RST])->name(VW::USR . '.password.reset');

    R::get('/change/mode', [UserController::class, UserController::CHG_MD])->name('change.mode');
    R::resource(VW::USR, UserController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::resource(DatabaseConstants::TABLE_ROLES, RoleController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::resource(DatabaseConstants::TABLE_PERMISSIONS, PermissionController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    #endregion

    //================================= Languages ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get('change-language/{lang}', [LanguageController::class, LanguageController::CHG_LNG])->name(VW::LNG . '.change');
            R::get('manage-language/{lang}', [LanguageController::class, LanguageController::MNG_LNG])->name(VW::LNG . '.manage');
            R::post('store-language-data/{lang}', [LanguageController::class, LanguageController::STR_LNG_DT])->name(VW::LNG . '.store.data');
            R::get('create-language', [LanguageController::class, LanguageController::CR_LNG])->name(VW::LNG . '.create');
            R::any('store-language', [LanguageController::class, LanguageController::STR_LNG])->name(VW::LNG . '.store');
            R::delete('/lang/{lang}', [LanguageController::class, LanguageController::DEL_LNG])->name(VW::LNG . '.destroy');
        }
    );
    #endregion

    //================================= System Settings  ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::post('email-settings', [SystemController::class, SystemController::SV_EM_ST])->name(VW::EML . '.settings');
            R::post('company-email-settings', [SystemController::class, SystemController::SV_CP_EM_ST])->name(VW::CP . '.email.settings');
            R::post('company-settings', [SystemController::class, SystemController::SV_CP_ST])->name(VW::CP . '.settings');
            R::post('system-settings', [SystemController::class, SystemController::SV_SYS_ST])->name(VW::SYS . '.settings');
            R::post('zoom-settings', [SystemController::class, SystemController::SV_ZM_ST])->name('zoom.settings');
            R::post('tracker-settings', [SystemController::class, SystemController::SV_TK_ST])->name(VW::TMT . '.settings');
            R::post('slack-settings', [SystemController::class, SystemController::SV_SLK_ST])->name('slack.settings');
            R::post('telegram-settings', [SystemController::class, SystemController::SV_TLG_ST])->name('telegram.settings');
            R::post('twilio-settings', [SystemController::class, SystemController::SV_TWL_ST])->name('twilio.setting');
            R::get('print-setting', [SystemController::class, SystemController::PRT])->name('print.setting');
            R::get('settings', [SystemController::class, SystemController::CP])->name('settings');
            R::post('business-setting', [SystemController::class, SystemController::SV_BS_ST])->name('business.setting');
            R::post('company-payment-setting', [SystemController::class, SystemController::SV_CP_PAY_ST])
                ->name(VW::CP . '.payment.settings');
            R::get('test-mail', [SystemController::class, SystemController::TT_MAIL])->name(VW::TT . '.mail');
            R::post('test-mail', [SystemController::class, SystemController::TT_MAIL])->name(VW::TT . '.mail');
            R::post('test-mail/send', [SystemController::class, SystemController::TT_SMAIL])->name(VW::TT . '.send.mail');
            R::post('stripe-settings', [SystemController::class, SystemController::SV_PAY_ST])->name(VW::PAY . '.settings');
            R::post('pusher-setting', [SystemController::class, SystemController::SV_PSR_ST])->name(VW::SET . '.pusher');
            R::post('recaptcha-settings', [SystemController::class, SystemController::RCP_ST_STR])->name('settings.recaptcha.store')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::post('seo-settings', [SystemController::class, SystemController::SEO_ST])->name(VW::SET . '.seo.store')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::any('webhook-settings', [SystemController::class, 'webhook'])->name(VW::WBH . '.settings')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::get('webhook-settings/create', [SystemController::class, SystemController::WHK_CRT])->name(VW::WBH . '.create')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::post('webhook-settings/store', [SystemController::class, SystemController::WHK_STR])->name(VW::WBH . '.store');
            R::get('webhook-settings/{wid}/edit', [SystemController::class, SystemController::WHK_EDT])->name(VW::WBH . '.edit')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::post('webhook-settings/{wid}/edit', [SystemController::class, SystemController::WHK_UPD])->name(VW::WBH . '.update')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::delete('webhook-settings/{wid}', [SystemController::class, SystemController::WHK_DST])->name(VW::WBH . '.destroy')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::post('cookie-setting', [SystemController::class, SystemController::SV_CK_ST])->name(VW::SET . '.cookies.store');
            R::post('cache-settings', [SystemController::class, SystemController::CC_ST_STR])->name('cache.settings.store')
                ->middleware([MWC::AUTH, MWC::XSS]);
            R::resource('systems', SystemController::class)->only(['index', 'store']);
        }
    );
    #endregion

    //================================= Product Services ====================================//
    #region
    R::get(VW::PRD_SV . '/index', [ProductServiceController::class, 'index'])
        ->name(VW::PRD_SV . '.index');
    R::get(VW::PRD_SV . '/{id}/detail', [ProductServiceController::class, ProductServiceController::WRH_DTL])
        ->name(VW::PRD_SV . '.detail');
    R::get(VW::PRD_SV . '/export', [ProductServiceController::class, 'export'])->name(VW::PRD_SV . '.export');
    R::post(VW::PRD_SV . '/import', [ProductServiceController::class, 'import'])->name(VW::PRD_SV . '.import');
    // R::get('import'.VW::PRD_SV.//file', [ProductServiceController::class, 'importFile'])->name(VW::PRD_SV . '.file.import');
    R::resource(VW::PRD_SV, ProductServiceController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::post('empty-cart', [ProductServiceController::class, ProductServiceController::EMP_CRT])
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::post('warehouse-empty-cart', [ProductServiceController::class, ProductServiceController::WRH_EMP_CRT])
        ->name('warehouse-empty-cart')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Customers ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::CST . '/{id}/show', [CustomerController::class, 'show'])
                ->name(VW::CST . '.show');
            R::resource(VW::CST, CustomerController::class);
        }
    );
    #endregion

    //================================= Vendors ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::VND . '/{id}/show', [VendorController::class, 'show'])
                ->name(VW::VND . '.show');
            R::resource(VW::VND, VendorController::class);
        }
    );
    #endregion

    //================================= Bank Accounts ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::resource(VW::BNK_ACC, BankAccountController::class);
        }
    );
    #endregion

    //================================= Bank Transfers ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::BNK_TRF . '/index', [BankTransferController::class, 'index'])->name(VW::BNK_TRF . '.index');
            R::resource(VW::BNK_TRF, BankTransferController::class);
        }
    );
    #endregion

    //================================= Product Service Categories ====================================//
    #region
    R::resource(VW::TX, TaxController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::post(VW::PRD_SV_CAT . '/get-account', [ProductServiceCategoryController::class, ProductServiceCategoryController::GET_ACC])
        ->name(VW::PRD_SV_CAT . '.get_account')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::resource(VW::PRD_SV_CAT, ProductServiceCategoryController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::resource(VW::PRD_SV_UNT, ProductServiceUnitController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    #endregion

    //================================= Invoices ====================================//
    #region

    //================================= Invoices Procedures ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::INV . '/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name(VW::INV . '.duplicate');
            R::get(VW::INV . '/{id}/shipping/print', [InvoiceController::class, InvoiceController::SHP_DSP])->name(VW::INV . '.shipping.print');
            R::get(VW::INV . '/{id}/payment/reminder', [InvoiceController::class, InvoiceController::PAY_RMD])->name(VW::INV . '.payment.reminder');
            R::get(VW::INV . '/index', [InvoiceController::class, 'index'])->name(VW::INV . '.index');
            R::post(VW::INV . '/product/destroy', [InvoiceController::class, InvoiceController::PRD_DST])->name(VW::INV . '.product.destroy');
            R::post(VW::INV . '/product', [InvoiceController::class, 'product'])->name(VW::INV . '.product');
            R::post(VW::INV . '/customer', [InvoiceController::class, 'customer'])->name(VW::INV . '.customer');
            R::get(VW::INV . '/{id}/sent', [InvoiceController::class, 'sent'])->name(VW::INV . '.sent');
            R::get(VW::INV . '/{id}/resent', [InvoiceController::class, 'resent'])->name(VW::INV . '.resent');
            R::get(VW::INV . '/{id}/payment', [InvoiceController::class, 'payment'])->name(VW::INV . '.payment');
            R::post(VW::INV . '/{id}/payment', [InvoiceController::class, InvoiceController::PAY_CRT])->name(VW::INV . '.payment');
            R::post(VW::INV . '/{id}/payment/{pid}/destroy', [InvoiceController::class, InvoiceController::PAY_DST])
                ->name(VW::INV . '.payment.destroy');
            R::get(VW::INV . '/items', [InvoiceController::class, 'items'])->name(VW::INV . '.items');
            R::get(VW::INV . '/create/{cid}', [InvoiceController::class, 'create'])->name(VW::INV . '.create');
            R::resource(VW::INV, InvoiceController::class);
        }
    );
    R::get(VW::INV . '/preview/{template}/{color}', [InvoiceController::class, InvoiceController::INV_PRV])->name(VW::INV . '.preview');
    R::post(VW::INV . '/template/setting', [InvoiceController::class, InvoiceController::SV_IV_TMP])
        ->name(VW::INV_TMP . 'settings');
    #endregion

    //================================= Credit Invoices ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(str_replace('_', '-', VW::CRD_NT), [CreditNoteController::class, 'index'])->name('credit.note');
            R::get('custom-credit-note', [CreditNoteController::class, CreditNoteController::CST_CRT])->name(VW::INV . '.custom.credit.note');
            R::post('custom-credit-note', [CreditNoteController::class, CreditNoteController::CST_STR])->name(VW::INV . '.custom.credit.note');
            R::get(VW::CRD_NT . '/invoice', [CreditNoteController::class, CreditNoteController::GET_INV])->name(VW::INV . '.get');
            R::get(VW::INV . '/{id}/credit-note', [CreditNoteController::class, 'create'])->name(VW::INV . '.credit.note');
            R::post(VW::INV . '/{id}/credit-note', [CreditNoteController::class, 'store'])->name(VW::INV . '.credit.note');
            R::get(VW::INV . '/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'edit'])->name(VW::INV . '.edit.credit.note');
            R::post(VW::INV . '/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'update'])
                ->name(VW::INV . '.edit.credit.note');
            R::delete(VW::INV . '/{id}/credit-note/delete/{cn_id}', [CreditNoteController::class, 'destroy'])
                ->name(VW::INV . '.delete.credit.note');
        }
    );
    #endregion

    #endregion

    //================================= Bills ====================================//
    #region
    //================================= Debit Notes ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::DBT_NT, [DebitNoteController::class, 'index'])->name('debit.note');
            R::get('custom-debit-note', [DebitNoteController::class, DebitNoteController::CST_CRT])->name(VW::BIL . '.custom.debit.note');
            R::post('custom-debit-note', [DebitNoteController::class, DebitNoteController::CST_STR])->name(VW::BIL . '.custom.debit.note');
            R::get(VW::DBT_NT . '/bill', [DebitNoteController::class, DebitNoteController::GET_BIL])->name(VW::BIL . '.get');
            R::get(VW::BIL . '{id}/debit-note', [DebitNoteController::class, 'create'])->name(VW::BIL . '.debit.note');
            R::post(VW::BIL . '{id}/debit-note', [DebitNoteController::class, 'store'])->name(VW::BIL . '.debit.note');
            R::get(VW::BIL . '{id}/' . VW::DBT_NT . '/edit/{cn_id}', [DebitNoteController::class, 'edit'])->name(VW::BIL . '.edit.debit.note');
            R::post(VW::BIL . '{id}/' . VW::DBT_NT . '/edit/{cn_id}', [DebitNoteController::class, 'update'])->name(VW::BIL . '.edit.debit.note');
            R::delete(VW::BIL . '{id}/' . VW::DBT_NT . '/delete/{cn_id}', [DebitNoteController::class, 'destroy'])->name(VW::BIL . '.delete.debit.note');
        }
    );
    #endregion

    R::get(VW::BIL . '/preview/{template}/{color}', [BillController::class, BillController::PV_BIL])->name(VW::BIL . '.preview')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::BIL . '/template/setting', [BillController::class, BillController::SV_BIL_TMP])
        ->name(VW::BIL_TMP . 'setting');

    R::resource(VW::TX, TaxController::class)
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::get(VW::RVN . '/index', [RevenueController::class, 'index'])->name(VW::RVN . '.index')->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);

    R::get(VW::BIL . '/pdf/{id}', [BillController::class, 'bill'])->name(VW::BIL . '.pdf')->middleware([
        MWC::XSS,
        MWC::REV
    ]);

    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::BIL . '{id}/duplicate', [BillController::class, 'duplicate'])->name(VW::BIL . '.duplicate');
            R::get(VW::BIL . '{id}/shipping/print', [BillController::class, BillController::SHP_DSP])->name(VW::BIL . '.shipping.print');
            R::get(VW::BIL . 'index', [BillController::class, 'index'])->name(VW::BIL . '.index');
            R::post(VW::BIL . 'product/destroy', [BillController::class, BillController::PRD_DST])->name(VW::BIL . '.product.destroy');
            R::post(VW::BIL . 'product', [BillController::class, 'product'])->name(VW::BIL . '.product');
            R::post(VW::BIL . 'vendor', [BillController::class, 'vendor'])->name(VW::BIL . '.vendor');
            R::get(VW::BIL . '{id}/sent', [BillController::class, 'sent'])->name(VW::BIL . '.sent');
            R::get(VW::BIL . '{id}/resent', [BillController::class, 'resent'])->name(VW::BIL . '.resent');
            R::get(VW::BIL . '{id}/payment', [BillController::class, 'payment'])->name(VW::BIL . '.payment');
            R::post(VW::BIL . '{id}/payment', [BillController::class, BillController::PAY_CRT])->name(VW::BIL . '.payment');
            R::post(VW::BIL . '{id}/payment/{pid}/destroy', [BillController::class, BillController::PAY_DST])->name(VW::BIL . '.payment.destroy');
            R::get(VW::BIL . 'items', [BillController::class, 'items'])->name(VW::BIL . '.items');
            R::get(VW::BIL . 'create/{cid}', [BillController::class, 'create'])->name(VW::BIL . '.create');
            R::resource(VW::BIL, BillController::class);
        }
    );
    #endregion

    //================================= Revenues ====================================//
    #region
    R::resource(VW::RVN, RevenueController::class)->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);
    #endregion

    //================================= Payments ====================================//
    #region
    R::get(VW::PAY . '/index', [PaymentController::class, 'index'])->name(VW::PAY . '.index')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::resource(VW::PAY, PaymentController::class)->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    #endregion

    //================================= Transactions ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::RPT . '/transaction', [TransactionController::class, 'index'])->name('transactions.index');
        }
    );
    #endregion

    //================================= Reports ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::RPT . '/income-summary', [RPC::class, RPC::INC_SM])->name(VW::RPT . '.income.summary');
            R::get(VW::RPT . '/expense-summary', [RPC::class, RPC::EXP_SM])->name(VW::RPT . '.expense.summary');
            R::get(VW::RPT . '/income-vs-expense-summary', [RPC::class, RPC::INC_EXP_SM])->name(VW::RPT . '.income.vs.expense.summary');
            R::get(VW::RPT . '/tax-summary', [RPC::class, RPC::TX_SM])->name(VW::RPT . '.tax.summary');
            // R::get(VW::RPT . '/profit-loss-summary', [RPC::class, RPC::PROFIT_LOSS_SM])->name(VW::RPT . '.profit.loss.summary');
            R::get(VW::RPT . '/invoice-summary', [RPC::class, RPC::INV_SM])->name(VW::RPT . '.invoice.summary');
            R::get(VW::RPT . '/bill-summary', [RPC::class, RPC::BL_SM])->name(VW::RPT . '.bill.summary');
            R::get(VW::RPT . '/product-stock-report', [RPC::class, RPC::PRD_STK])->name(VW::RPT . '.product.stock.report');
            R::get(VW::RPT . '/invoice-report', [RPC::class, RPC::INV_SM])->name(VW::RPT . '.invoice');
            R::get(VW::RPT . '/account-statement-report', [RPC::class, RPC::ACC_STT])->name(VW::RPT . '.account.statement');
            R::get(VW::RPT . '/balance-sheet/{view?}', [RPC::class, RPC::BL_SHT])->name(VW::RPT . '.balance.sheet');
            R::get(VW::RPT . '/profit-loss/{view?}', [RPC::class, RPC::PRF_LS])->name(VW::RPT . '.profit.loss');
            R::get(VW::RPT . '/ledger/{account?}', [RPC::class, RPC::LDG_SM])->name(VW::RPT . '.ledger');
            R::get(VW::RPT . '/trial-balance', [RPC::class, RPC::TRL_BL_SUM])->name(VW::RPT . '.trial.balance');
            R::get(VW::RPT . '-monthly-cashflow', [RPC::class, RPC::MLY_CSH_FLW])->name(VW::RPT . '.monthly.cashflow')->middleware([MWC::AUTH, MWC::XSS]);
            R::get(VW::RPT . '-quarterly-cashflow', [RPC::class, RPC::QLY_CSH_FLW])->name(VW::RPT . '.quarterly.cashflow')->middleware([MWC::AUTH, MWC::XSS]);
            R::post('trial-balance/export', [RPC::class, RPC::TRL_BLC_EXP])->name(VW::RPT . '.trial.balance.export');
            R::post('balance-sheet/export', [RPC::class, RPC::BLC_SHT_EXP])->name(VW::RPT . '.balance.sheet.export');
            R::post('balance-sheet/print/{view?}', [RPC::class, RPC::BLC_SHT_PRT])->name(VW::RPT . '.balance.sheet.print');
            R::post('print/trial-balance', [RPC::class, RPC::TRL_BLC_PRT])->name('trial.balance.print');
            R::post('export/profit-loss', [RPC::class, RPC::PRF_LS_EXP])->name(VW::RPT . '.profit.loss.export');
            R::post('print/profit-loss/{view?}', [RPC::class, RPC::PRF_LS_PRT])->name(VW::RPT . '.profit.loss.print');
            R::get(VW::RPT . '/sales', [RPC::class, RPC::SLS_RPT])->name(VW::RPT . '.sales');
            R::post('sales/export', [RPC::class, RPC::SLS_RPT_EXP])->name(VW::RPT . '.sales.export');
            R::post('sales/report/print/', [RPC::class, RPC::SLS_RPT_PRT])->name(VW::RPT . '.sales.report.print');
            R::get(VW::RPT . '/receivables', [RPC::class, RPC::RCV_RPT])->name(VW::RPT . '.receivables');
            R::post('receivables/export', [RPC::class, RPC::RCV_EXP])->name('receivables.export');
            R::post('receivables/print', [RPC::class, RPC::RCV_PRT])->name(VW::RPT . '.receivables.print');
            R::get(VW::RPT . '/payables', [RPC::class, RPC::PAY_RPT])->name(VW::RPT . '.payables');
            R::post('payables/print', [RPC::class, RPC::PAY_PRT])->name(VW::RPT . '.payables.print');
        }
    );
    #endregion

    //================================= Proposals ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::PPS . '/{id}/status/change', [ProposalController::class, ProposalController::STT_CHG])->name(VW::PPS . '.status.change');
            R::get(VW::PPS . '/{id}/convert', [ProposalController::class, 'convert'])->name(VW::PPS . '.convert');
            R::get(VW::PPS . '/{id}/duplicate', [ProposalController::class, 'duplicate'])->name(VW::PPS . '.duplicate');
            R::post(VW::PPS . '/product/destroy', [ProposalController::class, ProposalController::PRD_DST])->name(VW::PPS . '.product.destroy');
            R::post(VW::PPS . '/customer', [ProposalController::class, 'customer'])->name(VW::PPS . '.customer');
            R::post(VW::PPS . '/product', [ProposalController::class, 'product'])->name(VW::PPS . '.product');
            R::get(VW::PPS . '/items', [ProposalController::class, 'items'])->name(VW::PPS . '.items');
            R::get(VW::PPS . '/{id}/sent', [ProposalController::class, 'sent'])->name(VW::PPS . '.sent');
            R::get(VW::PPS . '/{id}/resent', [ProposalController::class, 'resent'])->name(VW::PPS . '.resent');
            R::get(VW::PPS . '/create/{cid}', [ProposalController::class, 'create'])->name(VW::PPS . '.create');
            R::resource('proposal', ProposalController::class);
        }
    );
    R::get(VW::PPS . '/preview/{template}/{color}', [ProposalController::class, ProposalController::PV_PPS])->name(VW::PPS . '.preview');
    R::post(VW::PPS . '/templates/settings', [ProposalController::class, ProposalController::SV_PPS_TMP])
        ->name(VW::PPS . 'settings');
    #endregion

    //================================= Goals ====================================//
    #region
    R::resource(VW::GL, GoalController::class)->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    #endregion

    //================================= Budgets ====================================//
    #region
    R::resource(VW::BDG, BudgetController::class)->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    #endregion

    //================================= Planners ====================================//
    #region
    R::resource(VW::ACC_AST, AssetController::class)->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::resource(VW::CST_FD, CustomFieldController::class)->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::post(VW::COA . '/subtype', [ChartOfAccountController::class, ChartOfAccountController::GET_SBT])->name(VW::COA . '.sub_type')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::resource(VW::COA, ChartOfAccountController::class);
        }
    );
    //================================= Journal Entries ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::post(VW::JRN_ET . '/account/destroy', [JournalEntryController::class, JournalEntryController::ACC_DST])->name(VW::JRN . 'account.destroy');
            R::delete(VW::JRN_ET . '/journal/destroy/{item_id}', [JournalEntryController::class, JournalEntryController::JRN_DST])->name(VW::JRN . '.destroy');
            R::resource(VW::JRN_ET, JournalEntryController::class);
        }
    );
    #endregion
    //================================= Clients ====================================//
    #region
    R::resource(VW::CLT, ClientController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::any('client-reset-password/{id}', [ClientController::class, 'clientPassword'])->name(VW::CLT . '.reset');
    R::post('client-reset-password/{id}', [ClientController::class, 'clientPasswordReset'])->name(VW::CLT . '.password.update');
    #endregion
    //================================= Deals ====================================//
    // Main Deal Routes
    #region
    R::post(VW::DL . '/user', [DealController::class, 'jsonUser'])->name(VW::DL . '.user.json');
    R::post(VW::DL . '/order', [DealController::class, 'order'])->name(VW::DL . '.order')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/change-pipeline', [DealController::class, 'changePipeline'])->name(VW::DL . '.change.pipeline')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/change-deal-status/{id}', [DealController::class, 'changeStatus'])->name(VW::DL . '.change.status')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/labels', [DealController::class, 'labels'])->name(VW::DL . '.labels')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/{id}/labels', [DealController::class, 'labelStore'])->name(VW::DL . '.labels.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/users', [DealController::class, 'userEdit'])->name(VW::DL . '.users.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put(VW::DL . '/{id}/users', [DealController::class, 'userUpdate'])->name(VW::DL . '.users.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::DL . '/{id}/users/{uid}', [DealController::class, 'userDestroy'])->name(VW::DL . '.users.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/clients', [DealController::class, 'clientEdit'])->name(VW::DL . '.clients.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put(VW::DL . '/{id}/clients', [DealController::class, 'clientUpdate'])->name(VW::DL . '.clients.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::DL . '/{id}/clients/{uid}', [DealController::class, 'clientDestroy'])->name(VW::DL . '.clients.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/products', [DealController::class, 'productEdit'])->name(VW::DL . '.products.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put(VW::DL . '/{id}/products', [DealController::class, 'productUpdate'])->name(VW::DL . '.products.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::DL . '/{id}/products/{uid}', [DealController::class, 'productDestroy'])->name(VW::DL . '.products.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/sources', [DealController::class, 'sourceEdit'])->name(VW::DL . '.sources.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put(VW::DL . '/{id}/sources', [DealController::class, 'sourceUpdate'])->name(VW::DL . '.sources.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::DL . '/{id}/sources/{uid}', [DealController::class, 'sourceDestroy'])->name(VW::DL . '.sources.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/{id}/file', [DealController::class, 'fileUpload'])->name(VW::DL . '.file.upload')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/file/{fid}', [DealController::class, 'fileDownload'])->name(VW::DL . '.file.download')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::DL . '/{id}/file/delete/{fid}', [DealController::class, 'fileDelete'])->name(VW::DL . '.file.delete')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/{id}/note', [DealController::class, 'noteStore'])->name(VW::DL . '.note.store')->middleware([MWC::AUTH]);
    R::get(VW::DL . '/{id}/' . VW::TSK, [DealController::class, 'taskCreate'])->name(VW::DL . '.tasks.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/{id}/' . VW::TSK, [DealController::class, 'taskStore'])->name(VW::DL . '.tasks.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/' . VW::TSK . '/{tid}/show', [DealController::class, 'taskShow'])->name(VW::DL . '.tasks.show')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/' . VW::TSK . '/{tid}/edit', [DealController::class, 'taskEdit'])->name(VW::DL . '.tasks.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put(VW::DL . '/{id}/' . VW::TSK . '/{tid}', [DealController::class, 'taskUpdate'])->name(VW::DL . '.tasks.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::put(VW::DL . '/{id}/task_status/{tid}', [DealController::class, 'taskUpdateStatus'])->name(VW::DL . '.tasks.update_status')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::DL . '/{id}/' . VW::TSK . '/{tid}', [DealController::class, 'taskDestroy'])->name(VW::DL . '.tasks.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/discussions', [DealController::class, 'discussionCreate'])->name(VW::DL . '.discussions.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/{id}/discussions', [DealController::class, 'discussionStore'])->name(VW::DL . '.discussion.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/{id}/permission/{cid}', [DealController::class, 'permission'])->name(VW::DL . '.client.permission')->middleware([MWC::AUTH, MWC::XSS]);
    R::put(VW::DL . '/{id}/permission/{cid}', [DealController::class, 'permissionStore'])->name(VW::DL . '.client.permissions.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::DL . '/list', [DealController::class, 'dealList'])->name(VW::DL . '.list')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    // Deal Calls
    #region
    R::get(VW::DL . '/{id}/call', [DealController::class, 'callCreate'])->name(VW::DL . '.calls.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/{id}/call', [DealController::class, 'callStore'])->name(VW::DL . '.calls.store')->middleware([MWC::AUTH]);
    R::get(VW::DL . '/{id}/call/{cid}/edit', [DealController::class, 'callEdit'])->name(VW::DL . '.calls.edit')->middleware([MWC::AUTH]);
    R::put(VW::DL . '/{id}/call/{cid}', [DealController::class, 'callUpdate'])->name(VW::DL . '.calls.update')->middleware([MWC::AUTH]);
    R::delete(VW::DL . '/{id}/call/{cid}', [DealController::class, 'callDestroy'])->name(VW::DL . '.calls.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    // Deal Email
    #region
    R::get(VW::DL . '/{id}/email', [DealController::class, 'emailCreate'])->name(VW::DL . '.emails.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::DL . '/{id}/email', [DealController::class, 'emailStore'])->name(VW::DL . '.emails.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::DL, DealController::class)->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    #endregion

    R::get('/search', [UserController::class, 'search'])->name('search.json');
    R::post('/stages/order', [StageController::class, 'order'])->name('stages.order');
    R::post('/stages/json', [StageController::class, 'json'])->name('stages.json');

    R::resource('stages', StageController::class);
    R::resource('pipelines', PipelineController::class);
    R::resource('labels', LabelController::class);
    R::resource('sources', SourceController::class);
    R::resource('payments', PaymentController::class);
    R::resource('custom_fields', CustomFieldController::class);

    // Leads Module

    R::post('/lead_stages/order', [LeadStageController::class, 'order'])->name('lead_stages.order');

    R::resource('lead_stages', LeadStageController::class)->middleware([MWC::AUTH]);

    R::post('/leads/json', [LeadController::class, 'json'])->name(VW::LD . '.json');
    R::post('/leads/order', [LeadController::class, 'order'])->name(VW::LD . '.order')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/leads/list', [LeadController::class, 'leadList'])->name(VW::LD . '.list')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name(VW::LD . '.file.upload')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name(VW::LD . '.file.download')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name(VW::LD . '.file.delete')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name(VW::LD . '.note.store')->middleware([MWC::AUTH]);
    R::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name(VW::LD . '.labels')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name(VW::LD . '.labels.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name(VW::LD . '.users.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name(VW::LD . '.users.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name(VW::LD . '.users.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/leads/{id}/products', [LeadController::class, 'productEdit'])->name(VW::LD . '.products.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put('/leads/{id}/products', [LeadController::class, 'productUpdate'])->name(VW::LD . '.products.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete('/leads/{id}/products/{uid}', [LeadController::class, 'productDestroy'])->name(VW::LD . '.products.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name(VW::LD . '.sources.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name(VW::LD . '.sources.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name(VW::LD . '.sources.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name(VW::LD . '.discussions.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name(VW::LD . '.discussion.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name(VW::LD . '.convert.deal')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name(VW::LD . '.convert.to.deal')->middleware([MWC::AUTH, MWC::XSS]);

    // Lead Calls
    R::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name(VW::LD . '.calls.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name(VW::LD . '.calls.store')->middleware([MWC::AUTH]);
    R::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name(VW::LD . '.calls.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name(VW::LD . '.calls.update')->middleware([MWC::AUTH]);
    R::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name(VW::LD . '.calls.destroy')->middleware([MWC::AUTH, MWC::XSS]);

    // Lead Email

    R::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name(VW::LD . '.emails.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name(VW::LD . '.emails.store')->middleware([MWC::AUTH]);

    R::resource('leads', LeadController::class)->middleware([MWC::AUTH, MWC::XSS]);

    // end Leads Module

    R::get(VW::USR . '/{id}/plan', [UserController::class, UserController::UPG_PLN])->name(VW::PLN . '.upgrade')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::USR . '/{id}/plan/{pid}', [UserController::class, UserController::ACT_PLN])->name(VW::PLN . '.active')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENTED
    R::get('/{uid}/notifications/seen', [UserController::class, 'notificationSeen'])->name('notifications.seen');
    // Email Templates
    R::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, EmailTemplateController::MNG_EM_LNG])->name(VW::EMLS . '.manage.language')->middleware([MWC::AUTH, MWC::XSS]);
    R::any('email_template_store', [EmailTemplateController::class, EmailTemplateController::UPD_STT])->name(VW::EMLS . '.status.language')->middleware([MWC::AUTH]);
    R::any('email_template_store/{pid}', [EmailTemplateController::class, EmailTemplateController::STR_EM_LNG])->name(VW::EMLS . '.store.language')->middleware([MWC::AUTH]);
    R::resource('email_template', EmailTemplateController::class)->middleware([MWC::AUTH, MWC::XSS]);
    // End Email Templates

    // HRM
    R::resource(VW::USR, UserController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::EMP . '/json', [EmployeeController::class, 'json'])->name(VW::EMP . '.json')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::BRC . '/' . VW::EMP . '/json', [EmployeeController::class, EmployeeController::EMP_JSON])->name(VW::BRC . '.employee.json')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('employee-profile', [EmployeeController::class, 'profile'])->name(VW::EMP . '.profile')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('show-employee-profile/{id}', [EmployeeController::class, EmployeeController::PRF_SHW])->name(VW::EMP . '.show.profile')->middleware([MWC::AUTH, MWC::XSS]);

    R::get('last-login', [EmployeeController::class, EmployeeController::LST_LGN])->name('last_login')->middleware([MWC::AUTH, MWC::XSS]);

    R::get(VW::EMP . '/export', [EmployeeController::class, 'export'])->name(VW::EMP . '.export')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::EMP . '/import/file', [EmployeeController::class, EmployeeController::IMP_FL])->name(VW::EMP . '.file.import')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::EMP . '/import/index', [EmployeeController::class, 'import'])->name(VW::EMP . '.import')->middleware([MWC::AUTH, MWC::XSS]);

    R::resource(VW::EMP, EmployeeController::class)->middleware([MWC::AUTH, MWC::XSS]);

    R::post(VW::EMP . '/getdepartment', [EmployeeController::class, EmployeeController::GET_DPT])->name(VW::EMP . '.getdepartment')->middleware([MWC::AUTH, MWC::XSS]);

    R::resource(VW::DPT, DepartmentController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::DSG, DesignationController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::DOC, DocumentController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::BRC, BranchController::class)->middleware([MWC::AUTH, MWC::XSS]);

    //================================= HRM For Salary ====================================//
    #region
    R::get(VW::EMP . '/salary/{eid}', [SetSalaryController::class, SetSalaryController::EMP_SL_BASIC])->name(VW::EMP . '.salary.basic')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Payslips ====================================//
    R::get(VW::EMP . '/salary/{eid}', [SetSalaryController::class, SetSalaryController::EMP_SL_BASIC])->name(VW::EMP . '.salary.basic')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::EMP . '/update/sallary/{id}', [SetSalaryController::class, SetSalaryController::EMP_SL_UPDATE])->name(VW::EMP . '.salary.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::EMP . '/salary', [SetSalaryController::class, SetSalaryController::EMP_SL])->name(VW::EMP . '.salary')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::ALW . '/create/{eid}', [AllowanceController::class, AllowanceController::ALW_CR])->name(VW::ALW . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::COM . '/create/{eid}', [CommissionController::class, CommissionController::COM_CR])->name(VW::COM . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::LN . '/create/{eid}', [LoanController::class, LoanController::LN_CRT])->name(VW::LN . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::STR_DD . '/create/{eid}', [SaturationDeductionController::class, SaturationDeductionController::STR_DD_CR])->name(VW::STR_DD . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::OT_PAY . '/create/{eid}', [OtherPaymentController::class, OtherPaymentController::OT_PAY_CR])->name(VW::OT_PAY . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::OVT . '/create/{eid}', [OvertimeController::class, 'overtimeCreate'])->name(VW::OVT . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::S_SLR, SetSalaryController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::ALW, AllowanceController::class)->except(['create'])->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::ALW_OPT, AllowanceOptionController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::COM, CommissionController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::DDT_OPT, DeductionOptionController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::LN_OPT, LoanOptionController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::LN, LoanController::class)->except(['create'])->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::PY_SLP, PayslipTypeController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::STR_DD, SaturationDeductionController::class)->except(['index', 'create'])->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::OT_PAY, OtherPaymentController::class)->except(['create'])->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::OVT, OvertimeController::class)->except(['create'])->middleware([MWC::AUTH, MWC::XSS]);
    //================================= Payslips Controller ====================================//
    #region
    R::get(VW::PY_SLP . '/paysalary/{id}/{date}', [PayslipController::class, PayslipController::PAY_SLR])->name(VW::PY_SLP . '.paysalary')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/bulk_pay_create/{date}', [PayslipController::class, PayslipController::BLK_PAY_CRT])->name(VW::PY_SLP . '.bulk_pay_create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PY_SLP . '/bulk_payment/{date}', [PayslipController::class, PayslipController::BLK_PAY])->name(VW::PY_SLP . '.bulkpayment')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PY_SLP . '/search_json', [PayslipController::class, PayslipController::SRC_JSN])->name(VW::PY_SLP . '.search_json')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/employeepayslip', [PayslipController::class, PayslipController::EMP_PAY_SLP])->name(VW::PY_SLP . '.employeepayslip')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/show/{id}', [PayslipController::class, PayslipController::SHW_EMP])->name(VW::PY_SLP . '.showemployee')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/edit/{id}', [PayslipController::class, PayslipController::EDT_EMP])->name(VW::PY_SLP . '.editemployee')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PY_SLP . '/employee/update/{id}', [PayslipController::class, PayslipController::UPD_EMP])->name(VW::PY_SLP . '.updateEmployee')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/pdf/{id}/{m}', [PayslipController::class, 'pdf'])->name(VW::PY_SLP . '.pdf')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/payslipPdf/{id}', [PayslipController::class, PayslipController::PAY_SLP_PDF])->name(VW::PY_SLP . '.payslipPdf')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/send/{id}/{m}', [PayslipController::class, 'send'])->name(VW::PY_SLP . '.send')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PY_SLP . '/delete/{id}', [PayslipController::class, 'destroy'])->name(VW::PY_SLP . '.delete')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::PY_SLP, PayslipController::class)->except(['create'])->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    R::post(VW::BRC . '/' . VW::EMP . '/json', [EmployeeController::class, EmployeeController::EMP_JSON])->name(VW::BRC . '.employee.json')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::EVT . '/get-department', [EventController::class, EventController::GET_DPT])->name(VW::EVT . '.getdepartment')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::EVT . '/get-employee', [EventController::class, EventController::GET_EMP])->name(VW::EVT . '.getemployee')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::MT . '/get-department', [MeetingController::class, MeetingController::GET_DPT])->name(VW::MT . '.getdepartment')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::MT . '/get-employee', [MeetingController::class, MeetingController::GET_EMP])->name(VW::MT . '.getemployee')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::CPN_PL, CompanyPolicyController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::IND, IndicatorController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::APR, AppraisalController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::GL_TP, GoalTypeController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::GL_TRC, GoalTrackingController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::ACC_AST, AssetController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::EVT, EventController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::MT, MeetingController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::TNG_TP, TrainingTypeController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::TNR, TrainerController::class)->middleware([MWC::AUTH, MWC::XSS]);

    //================================= Training ====================================//
    #region
    R::post(VW::TNG . '/status', [TrainingController::class, TrainingController::UPD_STT])->name(VW::TNG . '.status')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::TNG, TrainingController::class)->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    // HRM - HR Module

    R::get(VW::TMN . '/{id}/description', [TerminationController::class, 'description'])->name(VW::TMN . '.description');
    R::post(VW::ANC . '/getdepartment', [AnnouncementController::class, 'getdepartment'])->name(VW::ANC . '.getdepartment');
    R::post(VW::ANC . '/getemployee', [AnnouncementController::class, 'getemployee'])->name(VW::ANC . '.getemployee');
    R::resource(VW::AWD_TP, AwardTypeController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::AWD, AwardController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::RSG, ResignationController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::TRV, TravelController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::PRM, PromotionController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('complaints', ComplaintController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::WRN, WarningController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::TMN, TerminationController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('terminationtype', TerminationTypeController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('announcement', AnnouncementController::class)->middleware([MWC::AUTH, MWC::XSS]);

    // Recruitement

    R::post('job-stage/order', [JobStageController::class, 'order'])->name(VW::JB . '.stage.order');
    R::get('candidates-job-applications', [JobApplicationController::class, 'candidate'])->name(VW::JB . '.application.candidate')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('job-application/order', [JobApplicationController::class, 'order'])->name(VW::JB . '.application.order')->middleware([MWC::XSS]);
    R::post('job-application/{id}/rating', [JobApplicationController::class, 'rating'])->name(VW::JB . '.application.rating')->middleware([MWC::XSS]);
    R::delete('job-application/{id}/archive', [JobApplicationController::class, 'archive'])->name(VW::JB . '.application.archive')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('job-application/{id}/skill/store', [JobApplicationController::class, 'addSkill'])->name(VW::JB . '.application.skill.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('job-application/{id}/note/store', [JobApplicationController::class, 'addNote'])->name(VW::JB . '.application.note.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete('job-application/{id}/note/destroy', [JobApplicationController::class, 'destroyNote'])->name(VW::JB . '.application.note.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('job-application/getByJob', [JobApplicationController::class, 'getByJob'])->name(VW::JB_APL . '.get')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('job-onboard', [JobApplicationController::class, 'jobOnBoard'])->name(VW::JB . '.on.board')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::JB_OB . '/create/{id}', [JobApplicationController::class, 'jobBoardCreate'])->name(VW::JB . '.on.board.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::JB_OB . '/store/{id}', [JobApplicationController::class, 'jobBoardStore'])->name(VW::JB . '.on.board.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::JB_OB . '/edit/{id}', [JobApplicationController::class, 'jobBoardEdit'])->name(VW::JB . '.on.board.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::JB_OB . '/update/{id}', [JobApplicationController::class, 'jobBoardUpdate'])->name(VW::JB . '.on.board.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::JB_OB . '/delete/{id}', [JobApplicationController::class, 'jobBoardDelete'])->name(VW::JB . '.on.board.delete')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::JB_OB . '/convert/{id}', [JobApplicationController::class, 'jobBoardConvert'])->name(VW::JB . '.on.board.convert')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::JB_OB . '/convert/{id}', [JobApplicationController::class, 'jobBoardConvertData'])->name(VW::JB . '.on.board.convert')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('job-application/stage/change', [JobApplicationController::class, 'stageChange'])->name(VW::JB . '.application.stage.change')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::ITV_SCD . '/create/{id?}', [InterviewScheduleController::class, 'create'])->name(VW::ITV_SCD . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('task-board/{view?}', [ProjectTaskController::class, ProjectTaskController::TSK_BD])->name(VW::TSKB . '.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('task-board-view', [ProjectTaskController::class, ProjectTaskController::TSK_BD_VW])->name(VW::PRJ . '.taskboard.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::EMP_ATD . '/' . EmployeeAttendanceController::BK_ATD, [EmployeeAttendanceController::class, EmployeeAttendanceController::BK_ATD])->name(VW::EMP_ATD . '.' . EmployeeAttendanceController::BK_ATD)->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::EMP_ATD . '/' . EmployeeAttendanceController::BK_ATD, [EmployeeAttendanceController::class, EmployeeAttendanceController::BK_ATD_DT])->name(VW::EMP_ATD . '.' . EmployeeAttendanceController::BK_ATD)->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::EMP_ATD . '/attendance', [EmployeeAttendanceController::class, 'attendance'])->name(VW::EMP_ATD . '.attendance')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '/leave', [RPC::class, 'leave'])->name(VW::RPT . '.leave')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::EMP . '/{id}/leave/{status}/{type}/{month}/{year}', [RPC::class, 'employeeLeave'])->name(VW::RPT . '.employee.leave')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::LV . '/{id}/action', [LeaveController::class, 'action'])->name(VW::LV . '.action')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::LV . '/changeaction', [LeaveController::class, LeaveController::CHG_ACT])->name(VW::LV . '.change_action')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::LV . '/jsoncount', [LeaveController::class, LeaveController::JSON_CT])->name(VW::LV . '.jsoncount')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-leave', [RPC::class, 'leave'])->name(VW::RPT . '.leave')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::EMP . '/{id}/leave/{status}/{type}/{month}/{year}', [RPC::class, 'employeeLeave'])->name(VW::RPT . '.employee.leave')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-payroll', [RPC::class, 'payroll'])->name(VW::RPT . '.payroll')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::RPT . '-payroll/getdepartment', [RPC::class, 'getPayrollDepartment'])->name(VW::RPT . '.payroll.getdepartment')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::RPT . '-payroll/getemployee', [RPC::class, 'getPayrollEmployee'])->name(VW::RPT . '.payroll.getemployee')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-monthly-attendance', [RPC::class, 'monthlyAttendance'])->name(VW::RPT . '.monthly.attendance')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '/attendance/{month}/{branch}/{department}', [RPC::class, 'exportCsv'])->name(VW::RPT . '.attendance')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('job-category', JobCategoryController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('job-stage', JobStageController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::JB, JobController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('job-application', JobApplicationController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('custom-question', CustomQuestionController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('interview-schedule', InterviewScheduleController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::DOC_UP, DocumentUploadController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::TRF, TransferController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::EMP_ATD . '', EmployeeAttendanceController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::LV_TP, LeaveTypeController::class)->middleware([MWC::AUTH, MWC::XSS]);
    R::resource('leave', LeaveController::class)->middleware([MWC::AUTH, MWC::XSS]);
    //crm report
    R::get(VW::RPT . '-lead', [RPC::class, 'leadReport'])->name(VW::RPT . '.lead')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-deal', [RPC::class, 'dealReport'])->name(VW::RPT . '.deal')->middleware([MWC::AUTH, MWC::XSS]);
    //pos report
    R::get(VW::RPT . '-warehouse', [RPC::class, 'warehouseReport'])->name(VW::RPT . '.warehouse')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-daily-purchase', [RPC::class, 'purchaseDailyReport'])->name(VW::RPT . '.daily.purchase')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-monthly-purchase', [RPC::class, 'purchaseMonthlyReport'])->name(VW::RPT . '.monthly.purchase')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-daily-pos', [RPC::class, 'posDailyReport'])->name(VW::RPT . '.daily.pos')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-monthly-pos', [RPC::class, 'posMonthlyReport'])->name(VW::RPT . '.monthly.pos')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::RPT . '-pos-vs-purchase', [RPC::class, 'posVsPurchaseReport'])->name(VW::RPT . '.pos.vs.purchase')->middleware([MWC::AUTH, MWC::XSS]);

    // User Module

    R::get('users/{view?}', [UserController::class, 'index'])->name(VW::USR)->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENTED
    R::get('users-view', [UserController::class, 'filterUserView'])->name('filter.user.view')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENETED
    R::get('checkuserexists', [UserController::class, 'checkUserExists'])->name(VW::USR . '.exists')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('profile', [UserController::class, 'profile'])->name('profile')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENETED
    R::post('/profile', [UserController::class, 'updateProfile'])->name('update.profile')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENETED
    R::get(VW::USR . '/info/{id}', [UserController::class, 'userInfo'])->name(VW::USR . '.info')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENETED
    R::get(VW::USR . '/{id}/info/{type}', [UserController::class, 'getProjectTask'])->name(VW::USR . '.info.popup')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete('users/{id}', [UserController::class, 'destroy'])->name(VW::USR . '.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    // End User Module

    // Search
    R::get('/search', [UserController::class, 'search'])->name('search.json');
    // end

    //================================= Project Milestones  ====================================//
    #region
    R::get(VW::PRJ . '/{id}/' . VW::MLS, [ProjectController::class, 'milestone'])->name(VW::ML)->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{id}/' . VW::MLS, [ProjectController::class, ProjectController::ML_STR])->name(VW::ML . '.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/' . VW::MLS . '/{id}/edit', [ProjectController::class, ProjectController::ML_ED])->name(VW::ML . '.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/' . VW::MLS . '/{id}', [ProjectController::class, ProjectController::ML_UPD])->name(VW::ML . '.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::PRJ . '/' . VW::MLS . '/{id}', [ProjectController::class, ProjectController::ML_DST])->name(VW::ML . '.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/' . VW::MLS . '/{id}/show', [ProjectController::class, ProjectController::ML_SHW])->name(VW::ML . '.show')->middleware([MWC::AUTH, MWC::XSS]);
    //R::delete(
    //    '/'.VW::PRJ.'/{id}/users/{uid}', [
    //                                    'as' => VW::PRJ.'.'.VW::USR.'s.destroy',
    //                                    'uses' => 'ProjectController@userDestroy',
    //                                ]
    //)->middleware(
    //    [
    //        MWC::AUTH,
    //        MWC::XSS,
    //    ]
    //);
    // End Milestone
    #endregion

    // Project Module

    R::get('invite-project-member/{id}', [ProjectController::class, 'inviteMemberView'])->name(VW::PRJ . '.invite.member.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('invite-project-user-member', [ProjectController::class, 'inviteProjectUserMember'])->name(VW::PRJ . '.invite.user.member')->middleware([MWC::AUTH, MWC::XSS]);

    R::delete(VW::PRJ . '/{id}/users/{uid}', [ProjectController::class, 'destroyProjectUser'])->name(VW::PRJ . '.' . VW::USR . '.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('project/{view?}', [ProjectController::class, 'index'])->name(VW::PRJ . '.list')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('projects-view', [ProjectController::class, 'filterProjectView'])->name('filter.project.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{id}/store-stages/{slug}', [ProjectController::class, 'storeProjectTaskStages'])->name(VW::PRJ . '.stages.store')->middleware([MWC::AUTH, MWC::XSS]);

    R::patch('remove-user-from-project/{project_id}/{user_id}', [ProjectController::class, 'removeUserFromProject'])->name('remove.user.from.project')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('projects-users', [ProjectController::class, 'loadUser'])->name(VW::PRJ . '.user')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/{id}/gantt/{duration?}', [ProjectController::class, 'gantt'])->name(VW::PRJ . '.gantt')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{id}/gantt', [ProjectController::class, 'ganttPost'])->name(VW::PRJ . '.gantt.post')->middleware([MWC::AUTH, MWC::XSS]);

    R::resource('projects', ProjectController::class)->middleware([MWC::AUTH, MWC::XSS]);

    // User Permission
    R::get(VW::PRJ . '/{id}/' . VW::USR . '/{uid}/permission', [ProjectController::class, 'userPermission'])->name(VW::PRJ . '.' . VW::USR . '.permission')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{id}/' . VW::USR . '/{uid}/permission', [ProjectController::class, 'userPermissionStore'])->name(VW::PRJ . '.' . VW::USR . '.' . VW::PMS . '.store')->middleware([MWC::AUTH, MWC::XSS]);

    // End Project Module

    // Task Module

    R::get('stage/{id}/tasks', [ProjectTaskController::class, ProjectTaskController::GET_STG_TSK])->name(VW::PRJ_TSK_C . '.stage')->middleware([MWC::AUTH, MWC::XSS]);

    // Project Task Module

    R::get(VW::PRJ . '/{id}/' . VW::TSK, [ProjectTaskController::class, 'index'])->name(VW::PRJ_TSK_C . '.index')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/{pid}/' . VW::TSK . '/{sid}', [ProjectTaskController::class, 'create'])->name(VW::PRJ_TSK_C . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{pid}/' . VW::TSK . '/{sid}', [ProjectTaskController::class, 'store'])->name(VW::PRJ_TSK_C . '.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/{id}/' . VW::TSK . '/{tid}/show', [ProjectTaskController::class, 'show'])->name(VW::PRJ_TSK_C . '.show')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/{id}/' . VW::TSK . '/{tid}/edit', [ProjectTaskController::class, 'edit'])->name(VW::PRJ_TSK_C . '.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{id}/' . VW::TSK . '/update/{tid}', [ProjectTaskController::class, 'update'])->name(VW::PRJ_TSK_C . '.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::PRJ . '/{id}/' . VW::TSK . '/{tid}', [ProjectTaskController::class, 'destroy'])->name(VW::PRJ_TSK_C . '.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    R::patch(VW::PRJ . '/{id}/' . VW::TSK . '/order', [ProjectTaskController::class, ProjectTaskController::TSK_OD_UPD])->name(VW::PRJ . '.tasks.update.order')->middleware([MWC::AUTH, MWC::XSS]);
    R::patch('update-task-priority-color', [ProjectTaskController::class, ProjectTaskController::UPD_TSK_PR_CL])->name(VW::PRJ . '.tasks.update.priority.color')->middleware([MWC::AUTH, MWC::XSS]);

    R::post(VW::PRJ . '/{id}/comment/{tid}/file', [ProjectTaskController::class, ProjectTaskController::CM_STR_F])->name(VW::PRJ_TSK_C . '.comment.store.file')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::PRJ . '/{id}/comment/{tid}/file/{fid}', [ProjectTaskController::class, ProjectTaskController::CM_DST_F])->name(VW::PRJ_TSK_C . '.comment.destroy.file');
    R::post(VW::PRJ . '/{id}/comment/{tid}', [ProjectTaskController::class, ProjectTaskController::CM_STR])->name(VW::PRJ_TSK_C . '.comment.store');
    R::delete(VW::PRJ . '/{id}/comment/{tid}/{cid}', [ProjectTaskController::class, ProjectTaskController::CM_DST])->name(VW::PRJ_TSK_C . '.comment.destroy');
    R::post(VW::PRJ . '/{id}/checklist/{tid}', [ProjectTaskController::class, ProjectTaskController::CHKL_STR])->name(VW::PRJ_TSK_C . '.checklist.store');
    R::post(VW::PRJ . '/{id}/checklist/update/{cid}', [ProjectTaskController::class, ProjectTaskController::CHKL_UPD])->name(VW::PRJ_TSK_C . '.checklist.update');
    R::delete(VW::PRJ . '/{id}/checklist/{cid}', [ProjectTaskController::class, ProjectTaskController::CHKL_DST])->name(VW::PRJ_TSK_C . '.checklist.destroy');
    R::post(VW::PRJ . '/{id}/change/{tid}/fav', [ProjectTaskController::class, ProjectTaskController::CG_FAV])->name(VW::PRJ_TSK_C . '.change.fav');
    R::post(VW::PRJ . '/{id}/change/{tid}/complete', [ProjectTaskController::class, ProjectTaskController::CG_COM])->name(VW::PRJ_TSK_C . '.change.complete');
    R::post(VW::PRJ . '/{id}/change/{tid}/progress', [ProjectTaskController::class, ProjectTaskController::CG_PRG])->name(VW::PRJ_TSK_C . 'change.progress');
    R::get(VW::PRJ . '/' . VW::TSK . '/{id}/get', [ProjectTaskController::class, ProjectTaskController::GET_TSK])->name(VW::PRJ_TSK_C . '.get')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('/calendar/{id}/show', [ProjectTaskController::class, ProjectTaskController::CLD_SHW])->name(VW::PRJ_TSK_C . '.calendar.show')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/calendar/{id}/drag', [ProjectTaskController::class, ProjectTaskController::CLD_DRG])->name(VW::PRJ_TSK_C . '.calendar.drag');
    R::get('calendar/{task}/{pid?}', [ProjectTaskController::class, ProjectTaskController::CLD_VW])->name(VW::PRJ_TSK_C . '.calendar')->middleware([MWC::AUTH, MWC::XSS]);

    //================================= Project Task Stages ====================================//
    #region
    R::post(VW::PRJ_TSK_STG . '/order', [TaskStageController::class, 'order'])->name(VW::PRJ_TSK_STG . '.order');
    R::post(VW::PRJ_TSK_STG . '-new', [TaskStageController::class, TaskStageController::STR_V])->name(VW::PRJ_TSK_STG . '.new')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::PRJ_TSK_STG, TaskStageController::class)->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Project Expenses ====================================//
    #region
    R::get(VW::PRJ . '/{id}/expenses', [ExpenseController::class, 'index'])->name(VW::PRJ_EXP . '.index')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/{pid}/' . VW::EXP . '/create', [ExpenseController::class, 'create'])->name(VW::PRJ_EXP . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{pid}/' . VW::EXP . '/store', [ExpenseController::class, 'store'])->name(VW::PRJ_EXP . '.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '/{id}/' . VW::EXP . '/{eid}/edit', [ExpenseController::class, 'edit'])->name(VW::PRJ_EXP . '.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '/{id}/' . VW::EXP . '/{eid}', [ExpenseController::class, 'update'])->name(VW::PRJ_EXP . '.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::PRJ . '/{eid}/' . VW::EXP . '/', [ExpenseController::class, 'destroy'])->name(VW::PRJ_EXP . '.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO missing method
    R::get('/expense-list', [ExpenseController::class, 'index'])->name(VW::EXP . '.list')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Contract Types ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::resource(VW::CTC_TP, ContractTypeController::class);
        }
    );
    #endregion

    //================================= Project Timesheets ====================================//
    #region
    R::get(VW::PRJ . '.' . VW::TMS . '/append-task', [TimesheetController::class, TimesheetController::APD_TMS_TSK])->name(VW::PRJ . '.' . VW::TMS . '.append.task')->middleware([MWC::AUTH, MWC::XSS]);
    //    R::get(VW::PRJ . '.' . VW::TMS.'/table-view', [TimesheetController::class, 'filterTimesheetTableView'])->name(VW::PRJ . '.' . VW::TMS.'.filters.table.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '.' . VW::TMS . '/view', [TimesheetController::class, TimesheetController::FT_TMS_TBL])->name(VW::PRJ . '.' . VW::TMS . '.filters.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '.' . VW::TMS . '/list', [TimesheetController::class, TimesheetController::TMS_LST])->name(VW::PRJ . '.' . VW::TMS . '.list')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '.' . VW::TMS . '/list-get', [TimesheetController::class, TimesheetController::GET_TMS_LST])->name(VW::PRJ . '.' . VW::TMS . '.list.get')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '.' . VW::TMS . '/' . VW::PRJ . '/{id}', [TimesheetController::class, TimesheetController::TMS_VW])->name(VW::PRJ . '.' . VW::TMS . '.index')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::PRJ . '.' . VW::TMS . '/' . VW::PRJ . '/{id}', [TimesheetController::class, TimesheetController::TMS_STR])->name(VW::PRJ . '.' . VW::TMS . '.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '.' . VW::TMS . '/' . VW::PRJ . '/{id}/create', [TimesheetController::class, TimesheetController::TMS_CRT])->name(VW::PRJ . '.' . VW::TMS . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ . '.' . VW::TMS . '/' . VW::PRJ . '/{project_id}/edit/{timesheet_id}', [TimesheetController::class, TimesheetController::TMS_ED])->name(VW::PRJ . '.' . VW::TMS . '.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::any(VW::PRJ . '.' . VW::TMS . '/' . VW::PRJ . '/update/{timesheet_id}', [TimesheetController::class, TimesheetController::TMS_UPD])->name(VW::PRJ . '.' . VW::TMS . '.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::PRJ . '.' . VW::TMS . '/' . VW::PRJ . '/{timesheet_id}', [TimesheetController::class, TimesheetController::TMS_DST])->name(VW::PRJ . '.' . VW::TMS . '.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Project Bugs ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
            ],
        ],
        function () {
            R::post(VW::PRJ_STG . '/order', [ProjectStagesController::class, 'order'])->name(VW::PRJ_STG . '.order')->middleware([MWC::AUTH, MWC::XSS]);
            R::post(VW::PRJ . '/' . VW::BUG . '/kanban/order', [ProjectController::class, ProjectController::BUG_KB_OD])->name(VW::PRJ_BUG . '.kanban.order');
            R::get(VW::PRJ . '/{id}/' . VW::BUG . '/kanban', [ProjectController::class, ProjectController::BUG_KB])->name(VW::PRJ_TSK_BUG . '.kanban');
            R::get(VW::PRJ . '/{id}/' . VW::BUG, [ProjectController::class, 'bug'])->name(VW::PRJ_TSK_BUG);
            R::get(VW::PRJ . '/{id}/' . VW::BUG . '/create', [ProjectController::class, ProjectController::BUG_CRT])->name(VW::PRJ_TSK_BUG . '.create');
            R::post(VW::PRJ . '/{id}/' . VW::BUG . '/store', [ProjectController::class, ProjectController::BUG_ST])->name(VW::PRJ_TSK_BUG . '.store');
            R::get(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/edit', [ProjectController::class, ProjectController::BUG_EDT])->name(VW::PRJ_TSK_BUG . '.edit');
            R::post(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/update', [ProjectController::class, ProjectController::BUG_UPD])->name(VW::PRJ_TSK_BUG . '.update');
            R::delete(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/destroy', [ProjectController::class, ProjectController::BUG_DST])->name(VW::PRJ_TSK_BUG . '.destroy');
            R::get(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/show', [ProjectController::class, ProjectController::BUG_SHW])->name(VW::PRJ_TSK_BUG . '.show');
            R::post(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/comment', [ProjectController::class, ProjectController::BUG_CMT_STR])->name(VW::PRJ_BUG_CM . '.store');
            R::post(VW::PRJ . '/' . VW::BUG . '/{bid}/file', [ProjectController::class, ProjectController::BUG_CMT_STR_F])->name(VW::PRJ_BUG_CM . '.file.store');
            R::delete(VW::PRJ . '/' . VW::BUG . '/comment/{id}', [ProjectController::class, ProjectController::BUG_CMT_DST])->name(VW::PRJ_BUG_CM . '.destroy');
            R::delete(VW::PRJ . '/' . VW::BUG . '/file/{id}', [ProjectController::class, ProjectController::BUG_CMT_DST_F])->name(VW::PRJ_BUG_CM . '.file.destroy');
            R::post(VW::BUG_STT . '/order', [BugStatusController::class, 'order'])->name(VW::BUG_STT . '.order');
            R::get(VW::BUG_RPT . '/{view?}', [ProjectTaskController::class, ProjectTaskController::ALL_BUG])->name(VW::PRJ_BUG . '.view')->middleware([MWC::AUTH, MWC::XSS]);
            R::resource(VW::PRJ_STG, ProjectStagesController::class);
            R::resource(VW::BUG_STT, BugStatusController::class);
        }
    );
    #endregion

    //================================= Project Todos ====================================//
    #region
    R::post(VW::TD . '/create', [UserController::class, UserController::TD_STR])->name(VW::TD . '.store')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::TD . '/{id}/update', [UserController::class, UserController::TD_UPD])->name(VW::TD . '.update')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::TD . '/{id}/delete', [UserController::class, UserController::TD_DEL])->name(VW::TD . '.destroy')
        ->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Dashboards Views ====================================//
    #region
    R::get('/change/mode', [UserController::class, UserController::CHG_MD])->name('change.mode')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::get('dashboard-view', [DSBC::class, DSBC::FT_VW])->name('dashboard.view')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::get('dashboard', [DSBC::class, DSBC::CL_VW])->name('client.dashboard.view')
        ->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= SaaS Base ====================================//
    #region
    R::resource(VW::USR, UserController::class)->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);
    R::resource(VW::PLN, PlanController::class)->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);
    #endregion
    //================================= Coupons ====================================//
    #region
    R::get(VW::CPN . '/apply', [CouponController::class, CouponController::AP_CPN])->name(VW::CPN . '.apply')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::resource(VW::CPN, CouponController::class)->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);
    #endregion
    //================================= Form Builder ====================================//
    #region
    R::get(VW::FM . '/{code}', [FormBuilderController::class, FormBuilderController::FM_VW])->name(VW::FM . '.view')->middleware([MWC::XSS]);
    R::post(VW::FM . '/view_store', [FormBuilderController::class, FormBuilderController::FM_VW_STR])->name(VW::FM . '.view.store')->middleware([MWC::XSS]);
    R::resource(VW::FM_BD, FormBuilderController::class)->middleware([MWC::AUTH, MWC::XSS]);
    //================================= Form Fields ====================================//
    #region
    R::get(VW::FM_BD . '/{id}/field', [FormBuilderController::class, FormBuilderController::FD_CRT])->name(VW::FM_FD . '.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::FM_BD . '/{id}/field', [FormBuilderController::class, FormBuilderController::FD_STR])->name(VW::FM_FD . '.store')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENTED
    R::get(VW::FM_BD . '/{id}/field/{fid}/show', [FormBuilderController::class, 'formFieldShow'])->name(VW::FM_FD . '.show')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::FM_BD . '/{id}/field/{fid}/edit', [FormBuilderController::class, FormBuilderController::FD_EDT])->name(VW::FM_FD . '.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::FM_BD . '/{id}/field/{fid}', [FormBuilderController::class, FormBuilderController::FD_UPD])->name(VW::FM_FD . '.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::FM_BD . '/{id}/field/{fid}', [FormBuilderController::class, FormBuilderController::FD_DST])->name(VW::FM_FD . '.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    //================================= Form Responses ====================================//
    #region
    R::get(VW::FM . '/responses/{id}', [FormBuilderController::class, FormBuilderController::VW_RES])->name(VW::FM . '.response')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::FM . '/responses/{id}/detail', [FormBuilderController::class, FormBuilderController::RES_DT])->name(VW::FM . '.response.detail')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    //================================= Form Binds ====================================//
    #region
    R::get(VW::FM . '/binds/{id}', [FormBuilderController::class, FormBuilderController::FD_BD])->name(VW::FM_FD . '.bind')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::FM . '/binds/{id}/store', [FormBuilderController::class, FormBuilderController::BD_STR])->name(VW::FM . '.bind.store')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    #endregion
    //================================= Contracts ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::CTC . '/{id}/description', [ContractController::class, 'description'])->name(VW::CTC . '.description');
            R::get(VW::CTC . '/grid', [ContractController::class, 'grid'])->name(VW::CTC . '.grid');
            R::resource(VW::CTC, ContractController::class);
        }
    );
    R::post(VW::CTC . '/{id}/file', [ContractController::class, 'fileUpload'])->name(VW::CTC . '.file.upload')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::CTC . '/pdf/{id}', [ContractController::class, 'pdfFromContract'])->name(VW::CTC . '.download.pdf')->middleware([MWC::AUTH]);
    R::get(VW::CTC . '/{id}/get_contract', [ContractController::class, 'printContract'])->name(VW::CTC . '.get')->middleware([MWC::AUTH]);
    R::post(VW::CTC . '/contract_status_edit/{id}', [ContractController::class, 'contractStatusEdit'])->name(VW::CTC . '.status')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::CTC . '/{id}/contract_description', [ContractController::class, 'contractDescriptionStore'])->name(VW::CTC . '.contract_description.store')->middleware([MWC::AUTH]);
    R::get(VW::CTC . '/{id}/file/{fid}', [ContractController::class, 'fileDownload'])->name(VW::CTC . '.file.download')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::CTC . '/{id}/file/delete/{fid}', [ContractController::class, 'fileDelete'])->name(VW::CTC . '.file.delete')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::CTC . '/copy/{id}', [ContractController::class, 'copyContract'])->name(VW::CTC . '.copy')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::CTC . '/copy/store', [ContractController::class, 'copyContractStore'])->name(VW::CTC . '.copy.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::CTC . '/{id}/mail', [ContractController::class, 'sendmailContract'])->name(VW::CTC . '.send.mail');
    R::get('/signature/{id}', [ContractController::class, 'signature'])->name(VW::CTC . '.signature')->middleware([MWC::AUTH]);
    R::post('/signature-store', [ContractController::class, 'signatureStore'])->name(VW::CTC . '.signature.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::CTC . '/{id}/comment', [ContractController::class, 'commentStore'])->name(VW::CTC . '.comment.store');
    R::post(VW::CTC . '/{id}/notes', [ContractController::class, 'noteStore'])->name(VW::CTC . '.note.store')->middleware([MWC::AUTH]);
    R::delete(VW::CTC . '/{id}/notes', [ContractController::class, 'noteDestroy'])->name(VW::CTC . '.note.destroy')->middleware([MWC::AUTH]);
    R::delete(VW::CTC . '/{id}/comment', [ContractController::class, 'commentDestroy'])->name(VW::CTC . '.comment.destroy');
    R::get('get-projects/{client_id}', [ContractController::class, 'clientByProject'])->name(VW::PRJ . '.by.user.id')->middleware([MWC::AUTH, MWC::XSS]);
    R::any(VW::CTC . '/clients/select/{bid}', [ContractController::class, 'clientwiseproject'])->name(VW::CTC . '.clients.select');
    R::get(VW::CTC . '/copy/{id}', [ContractController::class, 'copycontract'])->name(VW::CTC . '.copy')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::CTC . '/copy/store', [ContractController::class, 'copycontractstore'])->name(VW::CTC . '.copy.store')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    //================================= Custom Landing Pages ====================================//
    #region

    //    R::get('/landingpage', [LandingPageSectionController::class, 'index'])->name('custom_landing_page.index')->middleware([MWC::AUTH, MWC::XSS]);
    //    R::get('/LandingPage/show/{id}', [LandingPageSectionController::class, 'show']);
    //
    //    R::post('/LandingPage/setConetent', [LandingPageSectionController::class, 'setConetent'])->middleware([MWC::AUTH, MWC::XSS]);
    //
    //
    //    R::get(
    //        '/get_landing_page_section/{name}', function ($name) {
    //        $plans = \DB::table(VW::PLN)->get();
    //
    //        return view('custom_landing_page.' . $name, compact(VW::PLN));
    //    }
    //    );
    //
    //    R::post('/LandingPage/removeSection/{id}', [LandingPageSectionController::class, 'removeSection'])->middleware([MWC::AUTH, MWC::XSS]);
    //    R::post('/LandingPage/setOrder', [LandingPageSectionController::class, 'setOrder'])->middleware([MWC::AUTH, MWC::XSS]);
    //    R::post('/LandingPage/copySection', [LandingPageSectionController::class, 'copySection'])->middleware([MWC::AUTH, MWC::XSS]);
    #endregion
    //================================= Benefits for Gateways ====================================//
    #region
    R::any(VW::PAY . '/benefit/initiate', [BenefitPaymentController::class, BenefitPaymentController::INI_PAY])->name(VW::PLN . '.pay.with.benefit');
    R::any(VW::PAY . '/benefit/callback', [BenefitPaymentController::class, BenefitPaymentController::CB])->name('benefit.callback');
    #endregion
    //================================= Cashfree for Gateways ====================================//
    #region
    R::post('cashfree/payments/store', [CashfreeController::class, CashfreeController::CF_PAY_STR])->name(VW::PLN . '.pay.with.cashfree');
    R::any('cashfree/payments/success', [CashfreeController::class, CashfreeController::CF_PAY_SCS])->name('cashfree.payment.success');
    #endregion
    //================================= Bank Transfer Payments for Gateways ====================================//
    #region
    R::post('plan-pay-with-bank', [BankTransferPaymentController::class, BankTransferPaymentController::PL_PAY_BNK])->name(VW::PLN . '.pay.with.bank')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::post(VW::OD . '/{id}/changeaction', [BankTransferPaymentController::class, BankTransferPaymentController::CHG_STT])->name(VW::OD . '.change.status');
    R::delete(VW::OD . '/{id}', [BankTransferPaymentController::class, BankTransferPaymentController::OD_DST])->name(VW::OD . '.destroy');
    R::get(VW::OD . '/{id}/action', [BankTransferPaymentController::class, 'action'])->name(VW::OD . '.action');
    #endregion
    //================================= Supports ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::SPT . '/{id}/reply', [SupportController::class, 'reply'])->name(VW::SPT . '.reply');
            R::post(VW::SPT . '/{id}/reply', [SupportController::class, 'replyAnswer'])->name(VW::SPT . '.reply.answer');
            R::get(VW::SPT . '/grid', [SupportController::class, 'grid'])->name(VW::SPT . '.grid');
            R::resource(VW::SPT, SupportController::class);
        }
    );
    #endregion

    R::resource(VW::CPT, CompetenciesController::class)->middleware([MWC::AUTH, MWC::XSS]);
    //================================= Perfomance Types ====================================//
    #region
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::resource(VW::PFM_TP, PerformanceTypeController::class);
        }
    );
    #endregion

    //================================= Plan Requests ====================================//
    R::get(VW::PLN_RQ, [PlanRequestController::class, 'index'])->name(VW::PLN_RQ . '.index')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('request_frequency/{id}', [PlanRequestController::class, PlanRequestController::RQ_VW])->name(VW::PLN_RQ . '.request.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('request_send/{id}', [PlanRequestController::class, PlanRequestController::USR_RQ])->name(VW::PLN_RQ . '.request.send')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('request_response/{id}/{response}', [PlanRequestController::class, PlanRequestController::AC_RQ])->name(VW::PLN_RQ . '.request.response')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('request_cancel/{id}', [PlanRequestController::class, PlanRequestController::CC_RQ])->name(VW::PLN_RQ . '.request.cancel')->middleware([MWC::AUTH, MWC::XSS]);
    //QR Code Module

    // Import/Export Data Route
    R::get(VW::CST . '/export', [CustomerController::class, 'export'])->name(VW::CST . '.export');
    R::get(VW::CST . '/import/file', [CustomerController::class, CustomerController::IMP_F])->name(VW::CST . '.file.import');
    R::post(VW::CST . '/import/index', [CustomerController::class, 'import'])->name(VW::CST . '.import');
    R::get(VW::VND . '/export', [VendorController::class, 'export'])->name(VW::VND . '.export');
    R::get(VW::VND . '/import/file', [VendorController::class, VendorController::IMP_F])->name(VW::VND . '.file.import');
    R::post(VW::VND . '/import/index', [VendorController::class, 'import'])->name(VW::VND . '.import');
    R::get(VW::INV . '/export', [InvoiceController::class, 'export'])->name(VW::INV . '.export');
    R::get(VW::PPS . '/export', [ProposalController::class, 'export'])->name(VW::PPS . '.export');
    R::get(VW::BIL . '/export', [BillController::class, 'export'])->name(VW::BIL . '.export');

    R::get('attendance/import/file', [EmployeeAttendanceController::class, 'importFile'])->name('attendance.file.import');
    R::post('attendance/import/index', [EmployeeAttendanceController::class, 'import'])->name('attendance.import');

    R::get(VW::TST . '/export', [TransactionController::class, 'export'])->name(VW::TST . '.export');
    R::get(VW::ACC_STT . '/export', [RPC::class, 'export'])->name(VW::ACC_STT . '.export');
    //================================= Product Stock ====================================//
    #region
    R::group(['middleware' => [MWC::SET]], function () {
        R::get(VW::PRD_STK . '/export', [RPC::class, 'stockExport'])->name(VW::PRD_STK . '.export');
        R::resource(VW::PRD_STK, ProductStockController::class)->middleware([MWC::AUTH, MWC::XSS, 'check.mount']);
    });
    #endregion
    R::get(VW::RPT . '/payrolls/export', [RPC::class, 'PayrollReportExport'])->name(VW::RPT . '.payroll.export');
    R::get(VW::LV . '/export', [RPC::class, 'LeaveReportExport'])->name(VW::LV . '.export');
    R::post(VW::PY_SLP . '/export', [PayslipController::class, 'export'])->name(VW::PY_SLP . '.export');

    // Time-Tracker
    R::post('stop-tracker', [DSBC::class, DSBC::STP_TRK])->name('stop.tracker')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::TMT, [TimeTrackerController::class, 'index'])->name('time.tracker')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete('tracker/{tid}/destroy', [TimeTrackerController::class, 'destroy'])->name(VW::TMT . '.destroy');
    R::post('tracker/image-view', [TimeTrackerController::class, TimeTrackerController::GET_TRT_IMG])->name(VW::TMT . '.image.view');
    R::delete('tracker/image-remove', [TimeTrackerController::class, TimeTrackerController::RM_TRT_IMG])->name(VW::TMT . '.image.remove');
    R::get(VW::PRJ . '/time-tracker/{id}', [ProjectController::class, 'tracker'])->name(VW::PRJ . '.time.tracker')->middleware([MWC::AUTH, MWC::XSS]);

    // Zoom Meeting
    R::any(VW::ZMM . '/projects/select/{bid}', [ZoomMeetingController::class, ZoomMeetingController::PRJ_W_USR])->name(VW::ZMM . '.projects.select');
    R::get('zoom-meeting-calendar', [ZoomMeetingController::class, 'calendar'])->name(VW::ZMM . '.calendar')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::ZMM, ZoomMeetingController::class)->middleware([MWC::AUTH, MWC::XSS]);

    //POS System

    R::resource(DatabaseConstants::TABLE_WHS, WarehouseController::class)->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(DatabaseConstants::TABLE_PURCHASES . '/items', [PurchaseController::class, 'items'])->name(VW::PRC . '.items');

            //    R::get('/'.VW::BIL.'{id}/', 'PurchaseController@purchaseLink')->name(VW::PRC.'.link.copy');
            R::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment', [PurchaseController::class, 'payment'])
                ->name(VW::PRC . '.payment');
            R::post(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment', [PurchaseController::class, 'createPayment'])
                ->name(VW::PRC . '.payment');
            R::post(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment/{pid}/destroy', [
                PurchaseController::class,
                'paymentDestroy'
            ])->name(VW::PRC . '.payment.destroy');
            R::post(DatabaseConstants::TABLE_PURCHASES . '/product/destroy', [
                PurchaseController::class,
                'productDestroy'
            ])->name(VW::PRC . '.product.destroy');
            R::post(DatabaseConstants::TABLE_PURCHASES . '/vendor', [PurchaseController::class, 'vendor'])
                ->name(VW::PRC . '.vendor');
            R::post(DatabaseConstants::TABLE_PURCHASES . '/product', [PurchaseController::class, 'product'])
                ->name(VW::PRC . '.product');
            R::get(DatabaseConstants::TABLE_PURCHASES . '/create/{cid}', [PurchaseController::class, 'create'])
                ->name(VW::PRC . '.create');
            R::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/sent', [PurchaseController::class, 'sent'])
                ->name(VW::PRC . '.sent');
            R::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/resent', [PurchaseController::class, 'resent'])
                ->name(VW::PRC . '.resent');
            R::resource(DatabaseConstants::TABLE_PURCHASES, PurchaseController::class);
        }

    );
    R::get('pos-print-setting', [SystemController::class, 'posPrintIndex'])->name(VW::POS . '.print.setting')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::get(DatabaseConstants::TABLE_PURCHASES . '/preview/{template}/{color}', [PurchaseController::class, PurchaseController::PV_PRC])
        ->name(VW::PRC . '.preview')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::POS . '/preview/{template}/{color}', [PosController::class, PosController::PV_POS])->name(VW::POS . '.preview')
        ->middleware([MWC::AUTH, MWC::XSS]);

    R::post(VW::PRC . '/templates/settings', [PurchaseController::class, PurchaseController::SV_PCR_TMP_STG])
        ->name(VW::PRC_TMP . 'settings');
    R::post(VW::POS . '/template/setting', [PosController::class, PosController::SV_POS_TMP])
        ->name(VW::PRC_TMP . 'settings');

    R::get(DatabaseConstants::TABLE_PURCHASES . '/pdf/{id}', [PurchaseController::class, 'purchase'])
        ->name(DatabaseConstants::TABLE_PURCHASES . '.pdf')
        ->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
    R::get(VW::POS . '/pdf/{id}', [PosController::class, 'pos'])->name(VW::POS . '.pdf')->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);
    R::get(VW::POS . '/data/store', [PosController::class, 'store'])->name(VW::POS . '.data.store')->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);

    //for pos print
    R::get('printview/pos', [PosController::class, PosController::PRT_VW])->name(VW::POS . '.printview')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

    R::resource(DatabaseConstants::TABLE_POS, PosController::class)->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);

    R::get('product-categories', [ProductServiceCategoryController::class, ProductServiceCategoryController::GET_PRD_CAT])
        ->name(VW::PRD_SV_CAT . '.categories')->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENTED
    R::get('name-search-products', [ProductServiceController::class, 'searchProductsByName'])->name('name.search.products')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::get('search-products', [ProductServiceController::class, ProductServiceController::SRC_PRD])->name('search.products')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::any(VW::RPT . '/pos', [PosController::class, 'report'])->name(VW::POS . '.report')->middleware([
        MWC::AUTH,
        MWC::XSS
    ]);
    //================================= Cart ====================================//
    #region
    R::get('add-to-cart/{id}/{session}', [ProductServiceController::class, ProductServiceController::ADD_CRT])->middleware([
        MWC::AUTH,
        MWC::XSS
    ]);
    R::patch('update-cart', [ProductServiceController::class, ProductServiceController::UPD_CRT])->middleware([
        MWC::AUTH,
        MWC::XSS
    ]);
    R::delete('remove-from-cart', [ProductServiceController::class, ProductServiceController::RM_CRT])->middleware([
        MWC::AUTH,
        MWC::XSS
    ]);
    #endregion
    #endregion

    //================================= Warehouse Transfers ====================================//
    #region
    R::post(VW::WRH_TRF . '/get-product', [WarehouseTransferController::class, WarehouseTransferController::GET_PRD])->name(VW::WRH_TRF . '.get.product')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::WRH_TRF . '/get-quantity', [WarehouseTransferController::class, WarehouseTransferController::GET_QT])
        ->name(VW::WRH_TRF . '.get.quantity')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::WRH_TRF, WarehouseTransferController::class)->middleware([
        MWC::AUTH,
        MWC::XSS,
        MWC::REV
    ]);
    #endregion

    //================================= POS Barcode ====================================//
    #region
    R::get(VW::POS . '/barcode', [PosController::class, 'barcode'])->name(VW::POS . '.barcode')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::SET . '/pos', [PosController::class, 'setting'])->name(VW::POS . '.setting')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::SET . '/barcode', [PosController::class, PosController::BC_ST_STR])->name(VW::POS . '.barcode.setting');
    R::get(VW::POS . '/print', [PosController::class, PosController::BC_PRT])->name(VW::POS . '.print')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::POS . '/get-product', [PosController::class, PosController::GET_PRD])->name(VW::POS . '.get.product')->middleware([MWC::AUTH, MWC::XSS]);
    R::any('pos-receipt', [PosController::class, 'receipt'])->name(VW::POS . '.receipt')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::POS . '/cart-discount', [PosController::class, PosController::CRT_DSC])->name(VW::POS . '.cart.discount')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Storage Settings ====================================//
    #region
    R::post('storage-settings', [SystemController::class, SystemController::STG_ST_STR])->name(VW::SET . '.storage.store')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Appraisals for Employees ====================================//
    #region
    R::post(VW::APR, [AppraisalController::class, AppraisalController::EMP_BY_STR])->name(VW::APR . '.' . VW::EMP . '.star')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::APR . '1', [AppraisalController::class, AppraisalController::EMP_BY_STR1])->name(VW::APR . '.' . VW::EMP . '.star1')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::APR . '/get-employee', [AppraisalController::class, AppraisalController::GET_EMP])->name(VW::APR . '.get.employee');
    #endregion

    //================================= Offer Letters ====================================//
    #region
    R::post(VW::SET . '/offer-letter/{lang?}', [SystemController::class, SystemController::OF_LTR_UPD])->name('offer_letter.update');
    R::get(VW::SET . '/offer-letter', [SystemController::class, SystemController::CP])->name(VW::SET . '.offer_letter.language');
    R::get(VW::JB_OB . '/pdf/{id}', [JobApplicationController::class, JobApplicationController::OFL_PDF])->name('offer_letter.download.pdf');
    R::get(VW::JB_OB . '/doc/{id}', [JobApplicationController::class, JobApplicationController::OFL_DC])->name('offer_letter.download.doc');
    #endregion

    //================================= Joining Letters ====================================//
    #region
    R::post(VW::SET . '/joining-letter/{lang?}', [SystemController::class, SystemController::JN_LTR_UPD])->name('joining_letter.update');
    R::get(VW::SET . '/joining-letter', [SystemController::class, SystemController::CP])->name(VW::SET . 'joining_letter.language');
    R::get(VW::EMP . '/pdf/{id}', [EmployeeController::class, EmployeeController::JNL_PDF])->name('joining_letter.download.pdf');
    R::get(VW::EMP . '/doc/{id}', [EmployeeController::class, EmployeeController::JNL_DOC])->name('joining_letter.download.doc');
    #endregion

    //================================= Experience Certificates ====================================//
    #region
    R::post(VW::SET . '/exp/{lang?}', [SystemController::class, SystemController::EXP_CT_UPD])->name('experience_certificate.update');
    R::get(VW::SET . '/exp', [SystemController::class, SystemController::CP])->name(VW::SET . '.experience_certificate.language');
    R::get(VW::EMP . '/exp-pdf/{id}', [EmployeeController::class, EmployeeController::EC_PDF])->name('exp.download.pdf');
    R::get(VW::EMP . '/exp-doc/{id}', [EmployeeController::class, EmployeeController::EC_DOC])->name('exp.download.doc');
    #endregion

    //================================= Nocs ====================================//
    #region
    R::post(VW::SET . '/noc/{lang?}', [SystemController::class, SystemController::NOC_UPD])->name('noc.update');
    R::get(VW::SET . '/noc', [SystemController::class, SystemController::CP])->name(VW::SET . '.noc.language');
    R::get(VW::EMP . '/noc-pdf/{id}', [EmployeeController::class, EmployeeController::NOC_PDF])->name('noc.download.pdf');
    R::get(VW::EMP . '/noc-doc/{id}', [EmployeeController::class, EmployeeController::NOC_DOC])->name('noc.download.doc');
    #endregion

    //Project Reports

    // TODO METHOD NOT IMPLEMENTED
    R::post(VW::PRJ_RPT . '/data', [PRPC::class, 'ajax_data'])->name(VW::PRJ_RPT . '.ajax')
        ->middleware([MWC::AUTH, MWC::XSS]);
    // TODO METHOD NOT IMPLEMENTED
    R::post(VW::PRJ_RPT . '/tasks/{id}', [PRPC::class, 'ajax_tasks_report'])->name(VW::PRJ_RPT . '.tasks.ajaxdata')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::PRJ_RPT . '/export/{id}', [PRPC::class, 'export'])->name(VW::PRJ_RPT . '.export');
    R::resource(VW::PRJ_RPT, PRPC::class)->middleware([MWC::AUTH, MWC::XSS]);

    //project copy module
    R::get('/project/copy/{id}', [ProjectController::class, 'copyproject'])->name(VW::PRJ . '.copy')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::post('/project/copy/store/{id}', [ProjectController::class, 'copyprojectstore'])->name(VW::PRJ . '.copy.store')
        ->middleware([MWC::AUTH, MWC::XSS]);

    //Google Calendar
    R::any(VW::EVT . '/get_event_data', [EventController::class, EventController::GET_EV_D])->name(VW::EVT . '.get_event_data')
        ->middleware([MWC::AUTH, MWC::XSS]);

    R::post(VW::SET . '/google-calendar', [SystemController::class, 'saveGooglecalendarSettings'])->name(VW::SET . 'google.calendar');
    R::any(VW::HLD . '/data', [HolidayController::class, HolidayController::GET_HL_D])->name(VW::HLD . '.get_holiday_data')->middleware([MWC::AUTH, MWC::XSS]);
    R::get('holiday-calendar', [HolidayController::class, 'calendar'])->name(VW::HLD . '.calendar');
    R::any(VW::ITV_SCD . '/data', [InterviewScheduleController::class, InterviewScheduleController::GET_ITV_D])->name(VW::ITV_SCD . '.get_interview_data')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('calendar/get_task_data', [ProjectTaskController::class, ProjectTaskController::GET_TSK_D])->name(VW::PRJ_TSK_C . '.calendar.get_task_data')->middleware([MWC::AUTH, MWC::XSS]);
    R::any('zoom-meeting/get_zoom_meeting_data', [ZoomMeetingController::class, ZoomMeetingController::GET_ZMM_D])->name(VW::ZMM . '.get_zoom_meeting_data')->middleware([MWC::AUTH, MWC::XSS]);

    R::any(VW::MT . '/get_meeting_data', [MeetingController::class, MeetingController::GET_MT_D])->name(VW::MT . '.get_meeting_data')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::get('meeting-calendar', [MeetingController::class, 'calendar'])->name(VW::MT . '.calendar')
        ->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::HLD, HolidayController::class)->middleware([MWC::AUTH, MWC::XSS]);

    // TODO MISSING METHOD
    R::any(VW::EVT . '/get_dashboard_event_data', [EventController::class, 'getDashboardEventData'])->name(VW::EVT . '.get_dashboard_event_data')->middleware([MWC::AUTH, MWC::XSS]);

    //branch wise department get in attendance report
    R::post(VW::RPT . '-monthly-attendance/getdepartment', [RPC::class, RPC::GET_DPT])->name(VW::RPT . '.attendance.getdepartment')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::RPT . '-monthly-attendance/getemployee', [RPC::class, RPC::GET_EMP])->name(VW::RPT . '.attendance.getemployee')->middleware([MWC::AUTH, MWC::XSS]);

    //shared project & copy link
    R::any(VW::PRJ . '/copy/link/{id}', [ProjectController::class, ProjectController::CP_LNK_ST])->name(VW::PRJ . '.copy.link');
    R::any(VW::PRJ . '/{id}/setting-create', [ProjectController::class, ProjectController::CP_LNK_ST_CRT])->name(VW::PRJ . '.copy_link.setting.create');
    // TODO missing method
    R::get('share-project/{lang?}', [ProjectController::class, 'shareProject'])->name('share.project');

    //================================= User Logs ====================================//
    #region
    R::get(VW::USR . '/logs', [UserController::class, UserController::USR_LOG])->name(VW::USR . '.log')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::USR . '/logs/{id}', [UserController::class, UserController::USR_LOG_VIEW])->name(VW::USR . '.log.view')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::USR . '/logs/{id}', [UserController::class, UserController::USR_LOG_DSTR])->name(VW::USR . '.log.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Notification Templates ====================================//
    #region
    R::get(VW::NTF_TMP . '/{id?}/{lang?}', [NotificationTemplateController::class, 'index'])->name(VW::NTF_TMP . '.index')->middleware([MWC::AUTH, MWC::XSS]);
    R::resource(VW::NTF_TMP, NotificationTemplateController::class)->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Notification Templates ====================================//
    #region
    R::post('system-settings/note', [SystemController::class, SystemController::FT_NT_STR])->name(VW::SYS . '.settings.footernote')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= AI ====================================//
    #region
    R::post('chatgpt-settings', [SystemController::class, SystemController::CGPT_ST])->name(VW::SET . '.chatgpt.settings');
    R::get('generate/{template_name}', [AiTemplateController::class, 'create'])->name('generate');
    R::post('generate/keywords/{id}', [AiTemplateController::class, AiTemplateController::GET_KW])->name('generate.keywords');
    R::post('generate/response', [AiTemplateController::class, AiTemplateController::AIG])->name('generate.response');
    R::get('grammar/{template}', [AiTemplateController::class, 'grammar'])->name('grammar')->middleware([MWC::AUTH, MWC::XSS]);
    R::post('grammar/response', [AiTemplateController::class, AiTemplateController::GM_P])->name('grammar.response')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= IP Controls ====================================//
    #region
    R::get(VW::SYS . '/create/ip', [SystemController::class, SystemController::CR_IP])->name(VW::SYS . '.ip.create')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::SYS . '/create/ip', [SystemController::class, SystemController::STR_IP])->name(VW::SYS . '.ip.store')->middleware([MWC::AUTH, MWC::XSS]);
    R::get(VW::SYS . '/edit/ip/{id}', [SystemController::class, SystemController::ED_IP])->name(VW::SYS . '.ip.edit')->middleware([MWC::AUTH, MWC::XSS]);
    R::post(VW::SYS . '/edit/ip/{id}', [SystemController::class, SystemController::UPD_IP])->name(VW::SYS . '.ip.update')->middleware([MWC::AUTH, MWC::XSS]);
    R::delete(VW::SYS . '/destroy/ip/{id}', [SystemController::class, SystemController::DST_IP])->name(VW::SYS . '.ip.destroy')->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Language Toggles ====================================//
    #region
    R::post('disable-language', [LanguageController::class, LanguageController::DSB_LNG])->name('language.disable')
        ->middleware([MWC::AUTH, MWC::XSS]);
    #endregion

    //================================= Expenses ====================================//
    #region
    R::get(VW::EXP . '/pdf/{id}', [ExpenseController::class, 'expense'])->name(VW::EXP . '.pdf')->middleware([MWC::XSS, MWC::REV]);
    R::group(
        [
            'middleware' => [
                MWC::AUTH,
                MWC::XSS,
                MWC::REV,
            ],
        ],
        function () {
            R::get(VW::EXP . '/index', [ExpenseController::class, 'index'])->name(VW::EXP . '.index');
            R::any(VW::EXP . '/customer', [ExpenseController::class, 'customer'])->name(VW::EXP . '.customer');
            R::post(VW::EXP . '/vendor', [ExpenseController::class, 'vendor'])->name(VW::EXP . '.vendor');
            R::post(VW::EXP . '/employee', [ExpenseController::class, 'employee'])->name(VW::EXP . '.employee');
            R::post(VW::EXP . '/product/destroy', [ExpenseController::class, ExpenseController::PRD_DST])->name(VW::EXP . '.product.destroy');
            R::post(VW::EXP . '/product', [ExpenseController::class, 'product'])->name(VW::EXP . '.product');
            R::get(VW::EXP . '/{id}/payment', [ExpenseController::class, 'payment'])->name(VW::EXP . '.payment');
            R::get(VW::EXP . '/items', [ExpenseController::class, 'items'])->name(VW::EXP . '.items');
            R::get(VW::EXP . '/create/{cid}', [ExpenseController::class, 'create'])->name(VW::EXP . '.create');
            R::resource(VW::EXP, ExpenseController::class);
        }
    );
    #endregion
});
//================================= Cookies ====================================//
#region
R::any('/cookie-consent', [SystemController::class, SystemController::CK_CST])->name('cookie-consent');
#endregion
//================================= Orders ====================================//
R::group(
    [
        'middleware' => [
            MWC::AUTH,
            MWC::XSS,
            MWC::REV,
        ],
    ],
    function () {
        R::get('/orders', [StripePaymentController::class, 'index'])->name(VW::OD . '.index');
        R::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
        R::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
    }
);

R::get('.well-known/appspecific/com.chrome.devtools.json', function () {
    return response()->json([
        'crx' => [
            'webstore' => null
        ]
    ]);
});

//=========================== IPN SAFETY WRAPPER ===========================//
// Override the vendor paytabs IPN route to catch \Throwable (not just
// \Exception) and prevent 500 errors when PayTabs config values are missing.
use Illuminate\Support\Facades\Route as R2;
use Illuminate\Support\Facades\Log as RL;

R2::post('/paymentIPN', function (\Illuminate\Http\Request $request) {
    try {
        $requiredKeys = ['paytabs.profile_id', 'paytabs.server_key', 'paytabs.region'];
        foreach ($requiredKeys as $key) {
            if (empty(config($key))) {
                RL::warning('PayTabs IPN rejected — missing config: ' . $key);
                return response()->json(['message' => 'Payment gateway not configured'], 503);
            }
        }
        /** @var mixed $controller */
        $controller = app('Paytabscom\\Laravel_paytabs\\PaytabsLaravelListenerApi');
        return $controller->paymentIPN($request);
    } catch (\Throwable $e) {
        RL::error('PayTabs IPN error: ' . get_class($e) . ' — ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        return response()->json(['message' => 'IPN processing failed'], 400);
    }
})->name('payment_ipn');

//================================= OUT ====================================//
            // R::post('{id}/pay-with-paypal', [PaypalController::class, 'customerPayWithPaypal'])->name(VW::CST.'.pay.with.paypal');
            // R::get('{id}/get-payment-status/{amount}', [PaypalController::class, 'customerGetPaymentStatus'])->name(VW::CST.'.get.payment.status')
            //     ->middleware([MWC::XSS]);
            // R::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name(VW::PLN.'.pay.with.paypal')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
            // R::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name(VW::PLN.'.get.payment.status')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);


        // AUT, XSS, REV GROUP

            //    R::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name(VW::PLN.'.pay.with.paypal')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);
            //    R::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name(VW::PLN.'.get.payment.status')->middleware([MWC::AUTH, MWC::XSS, MWC::REV]);

        //     R::post('invoice-with-aamarpay', [AamarpayController::class, 'invoicepaywithaamarpay'])->name(VW::CST.'.pay.with.aamarpay');
        //     R::any('aamarpay-invoice/success/{data}', [AamarpayController::class, 'getInvoicePaymentStatus'])->name(VW::INV . '.pay.aamarpay.success');
            
        //     R::post('/customer-pay-with-coingate', [CoingatePaymentController::class, 'customerPayWithCoingate'])->name(VW::CST.'.pay.with.coingate')->middleware([MWC::XSS]);
        //     R::get('/customer/coingate/{invoice}/{amount}', [CoingatePaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.coingate');
            
        //     R::post('/customer-pay-with-paytm', [PaytmPaymentController::class, 'customerPayWithPaytm'])->name(VW::CST.'.pay.with.paytm')
        //         ->middleware([MWC::XSS]);
        //     R::post('/customer/paytm/{invoice}/{amount}', [PaytmPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.paytm');
            
        //     R::post('/customer-pay-with-flutterwave', [FlutterwavePaymentController::class, 'customerPayWithFlutterwave'])->name(VW::CST.'.pay.with.flutterwave')->middleware([MWC::XSS]);
        //     R::get('/customer/flutterwave/{txref}/{invoice_id}', [FlutterwavePaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.flutterwave');
            
        //     R::post('/customer-pay-with-razorpay', [RazorpayPaymentController::class, 'customerPayWithRazorpay'])->name(VW::CST.'.pay.with.razorpay')->middleware([MWC::XSS]);
        //     R::get('/customer/razorpay/{txref}/{invoice_id}', [RazorpayPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.razorpay');
            
        //     R::post('/customer-pay-with-mercado', [MercadoPaymentController::class, 'customerPayWithMercado'])->name(VW::CST.'.pay.with.mercado')
        //         ->middleware([MWC::XSS]);
        //     R::get('/customer/mercado/{invoice}', [MercadoPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.mercado');
            
        //     R::post('/customer-pay-with-mollie', [MolliePaymentController::class, 'customerPayWithMollie'])->name(VW::CST.'.pay.with.mollie')
        //         ->middleware([MWC::XSS]);
        //     R::get('/customer/mollie/{invoice}/{amount}', [MolliePaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.mollie');
            
        //     R::post('/customer-pay-with-skrill', [SkrillPaymentController::class, 'customerPayWithSkrill'])->name(VW::CST.'.pay.with.skrill')
        //         ->middleware([MWC::XSS]);
        //     R::get('/customer/skrill/{invoice}/{amount}', [SkrillPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.skrill');
            
        //     R::post('/paymentwall', [PaymentWallPaymentController::class, 'invoicepaymentwall'])->name(VW::INV . '.paymentwallpayment')
        //         ->middleware([MWC::XSS]);
        //     R::post('/invoice-pay-with-paymentwall/{invoice}', [PaymentWallPaymentController::class, 'invoicePayWithPaymentwall'])
        //         ->name(VW::INV . '.pay.with.paymentwall')->middleware([MWC::XSS]);
        //     R::get(VW::INV.'/{flag}/{invoice}', [PaymentWallPaymentController::class, 'invoiceerror'])->name('error.invoice.show');
            
        //     R::post('/customer-pay-with-toyyibpay', [ToyyibpayController::class, 'invoicepaywithtoyyibpay'])->name(VW::CST.'.pay.with.toyyibpay');
        //     R::get('/customer/toyyibpay/{invoice}/{amount}', [ToyyibpayController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.toyyibpay');
            
        //     R::post('invoice-with-payfast', [PayFastController::class, 'invoicePayWithPayFast'])->name(VW::INV . '.with.payfast');
        //     R::get('invoice-payfast-status/{success}', [PayFastController::class, 'invoicepayfaststatus'])->name(VW::INV . '.payfast.status');
            
        //     R::post('/customer-pay-with-iyzipay', [IyziPayController::class, 'invoicepaywithiyzipay'])->name(VW::CST.'.pay.with.iyzipay');
        //     R::post('iyzipay/callback/{invoice}/{amount}', [IyzipayController::class, 'getInvoiceiyzipayCallback'])
        //         ->name('iyzipay.invoicepayment.callback');
            
        //     R::post('/customer-pay-with-sspay', [SspayController::class, 'invoicepaywithsspaypay'])->name(VW::CST.'.pay.with.sspay');
        //     R::get('/customer/sspay/{invoice}/{amount}', [SspayController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.sspay');
            
        //     R::post('/invoice-pay-with-paytab', [PaytabController::class, 'invoicePayWithpaytab'])->name(VW::CST.'.pay.with.paytab');
        //     R::any('/invoice-paytab-success/{invoice}', [PaytabController::class, 'getInvoicePaymentStatus'])->name(VW::INV . '.paytab.success');
            
        //     R::post('/invoice-with-paytr', [PaytrController::class, 'invoicepaywithpaytr'])->name(VW::CST.'.pay.with.paytr');
        //     R::get('/invoice/paytr/status', [PaytrController::class, 'getInvoicePaymentStatus'])->name(VW::INV . '.paytr');
            
        //     R::post('invoice-with-yookassa/', [YooKassaController::class, 'invoicePayWithYookassa'])->name(VW::CST.'.with.yookassa');
        //     R::any('invoice-yookassa-status/', [YooKassaController::class, 'getInvociePaymentStatus'])->name(VW::INV . '.yookassa.status');
            
        //     R::any('invoice-with-midtrans/', [MidtransPaymentController::class, 'invoicePayWithMidtrans'])->name(VW::CST.'.with.midtrans');
        //     R::any('invoice-midtrans-status/', [MidtransPaymentController::class, 'getInvociePaymentStatus'])->name(VW::INV . '.midtrans.status');
            
        //     R::any('/invoice-with-xendit', [XenditPaymentController::class, 'invoicePayWithXendit'])->name(VW::CST.'.with.xendit');
        //     R::any('/invoice-xendit-status', [XenditPaymentController::class, 'getInvociePaymentStatus'])->name(VW::INV . '.xendit.status');
        // // Invoice Payment Gateways
        // R::post('customer/{id}/payment', [StripePaymentController::class, 'addpayment'])->name(VW::CST.'.payment');

        // R::group(
        //     [
        //         'middleware' => [
        //             MWC::AUTH,
        //             MWC::XSS,
        //             MWC::REV,
        //         ],
        //     ],
        //     function () {
        //         R::get('order', [StripePaymentController::class, 'index'])->name(VW::OD.'.index');
        //         R::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
        //         R::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
        //     }
        // );

        // R::post('/customer-pay-with-paystack', [PaystackPaymentController::class, 'customerPayWithPaystack'])->name(VW::CST.'.pay.with.paystack')->middleware([MWC::XSS]);
        // R::get('/customer/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.paystack');

            // R::post('/aamarpay/payment', [AamarpayController::class, 'pay'])->name(VW::PLN.'.pay.with.aamarpay');
            // R::any('/aamarpay/success/{data}', [AamarpayController::class, 'aamarpaysuccess'])->name('pay.aamarpay.success');

            // R::post('/paytr/payment/{plan_id}', [PaytrController::class, 'PlanpayWithPaytr'])->name(VW::PLN.'.pay.with.paytr');
            // R::get('/paytr/sussess/', [PaytrController::class, 'paytrsuccess'])->name('pay.paytr.success');

            // R::post('/plan/yookassa/payment', [YooKassaController::class, 'planPayWithYooKassa'])->name(VW::PLN.'.pay.with.yookassa');
            // R::get('/plan/yookassa/{plan}', [YooKassaController::class, 'planGetYooKassaStatus'])->name(VW::PLN.'.yookassa.status');

            // R::any('/midtrans', [MidtransPaymentController::class, 'planPayWithMidtrans'])->name(VW::PLN.'.pay.with.midtrans');
            // R::any('/midtrans/callback', [MidtransPaymentController::class, 'planGetMidtransStatus'])->name(VW::PLN.'.get.midtrans.status');

            // R::any('/xendit/payment', [XenditPaymentController::class, 'planPayWithXendit'])->name(VW::PLN.'.pay.with.xendit');
            // R::any('/xendit/payment/status', [XenditPaymentController::class, 'planGetXenditStatus'])->name(VW::PLN.'.xendit.status');

            // R::post('/plan-pay-with-flutterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name(VW::PLN.'.pay.with.flutterwave')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/flutterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.flutterwave');

            // R::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->name(VW::PLN.'.pay.with.razorpay')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/razorpay/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.razorpay');

            // R::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->name(VW::PLN.'.pay.with.paytm')->middleware([MWC::AUTH, MWC::XSS]);
            // R::post('/plan/paytm/{plan}', [PaytmPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.paytm');

            // R::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->name(VW::PLN.'.pay.with.mercado')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/mercado/{plan}/{amount}', [MercadoPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.mercado');

            // R::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name(VW::PLN.'.pay.with.mollie')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.mollie');

            // R::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name(VW::PLN.'.pay.with.skrill')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/skrill/{plan}', [SkrillPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.skrill');

            // R::post('/plan-pay-with-coingate', [CoingatePaymentController::class, 'planPayWithCoingate'])->name(VW::PLN.'.pay.with.coingate')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/coingate/{plan}', [CoingatePaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.coingate');

            // R::post('/toyyibpay', [ToyyibpayController::class, 'planPayWithToyyibpay'])->name(VW::PLN.'.toyyibpaypayment');
            // R::get('/plan-pay-with-toyyibpay/{id}/{status}/{coupon}', [ToyyibpayController::class, 'getPaymentStatus'])->name(VW::PLN.'.status');

            // R::post('payfast-plan', [PayFastController::class, 'planPayWithPayfast'])->name('payfast.payment');
            // R::get('payfast-plan/{success}', [PayFastController::class, 'getPaymentStatus'])->name('payfast.payment.success');

            // R::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
            // R::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

            // R::post('/sspay', [SspayController::class, 'SspayPaymentPrepare'])->name(VW::PLN.'.sspaypayment');
            // R::get('sspay-payment-plan/{plan_id}/{amount}/{couponCode}', [SspayController::class, 'SspayPlanGetPayment'])->middleware([MWC::AUTH])->name(VW::PLN.'.sspay.callback');

            // R::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithpaytab'])->middleware([MWC::AUTH])->name(VW::PLN.'.pay.with.paytab');
            // R::any('paytab-success/plan', [PaytabController::class, 'PaytabGetPayment'])->middleware([MWC::AUTH])->name(VW::PLN.'.paytab.success');

            // R::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name(VW::PLN.'.pay.with.paystack')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.paystack');

            // // PaymentWall

            // R::post('/paymentwalls', [PaymentWallPaymentController::class, 'paymentwall'])->name(VW::PLN.'.paymentwallpayment')->middleware([MWC::XSS]);
            // R::post('/plan-pay-with-paymentwall/{plan}', [PaymentWallPaymentController::class, 'planPayWithPaymentWall'])->name(VW::PLN.'.pay.with.paymentwall')->middleware([MWC::AUTH, MWC::XSS]);
            // R::get('/plan/{flag}', [PaymentWallPaymentController::class, 'planeerror'])->name('error.plan.show');