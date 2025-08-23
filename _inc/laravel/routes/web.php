<?php

use App\Http\Controllers\{
    AiTemplateController,
    AllowanceController,
    AllowanceOptionController,
    AnnouncementController,
    AppraisalController,
    AssetController,
    Auth\AuthenticatedSessionController,
    Auth\EmailVerificationNotificationController,
    Auth\EmailVerificationPromptController,
    Auth\RegisteredUserController,
    Auth\VerifyEmailController,
    AwardController,
    AwardTypeController,
    BankAccountController,
    BankTransferController,
    BankTransferPaymentController,
    BenefitPaymentController,
    BillController,
    BranchController,
    BudgetController,
    BugStatusController,
    CashfreeController,
    ChartOfAccountController,
    ClientController,
    CommissionController,
    CompanyPolicyController,
    CompetenciesController,
    ComplaintController,
    ContractController,
    ContractTypeController,
    CouponController,
    CreditNoteController,
    CustomerController,
    CustomFieldController,
    CustomQuestionController,
    DashboardController,
    DealController,
    DebitNoteController,
    DeductionOptionController,
    DepartmentController,
    DesignationController,
    DocumentController,
    DocumentUploadController,
    EmailTemplateController,
    EmployeeAttendanceController,
    EmployeeController,
    EventController,
    ExpenseController,
    FormBuilderController,
    GoalController,
    GoalTrackingController,
    GoalTypeController,
    HolidayController,
    IndicatorController,
    InterviewScheduleController,
    InvoiceController,
    JobApplicationController,
    JobCategoryController,
    JobController,
    JobStageController,
    JournalEntryController,
    LabelController,
    LanguageController,
    LeadController,
    LeadStageController,
    LeaveController,
    LeaveTypeController,
    LoanController,
    LoanOptionController,
    MeetingController,
    NotificationTemplatesController,
    OtherPaymentController,
    OvertimeController,
    PaymentController,
    PayslipController,
    PayslipTypeController,
    PerformanceTypeController,
    PermissionController,
    PipelineController,
    PlanController,
    PlanRequestController,
    PosController,
    ProductServiceCategoryController,
    ProductServiceController,
    ProductServiceUnitController,
    ProductStockController,
    ProjectController,
    ProjectReportController,
    ProjectStagesController,
    ProjectTaskController,
    PromotionController,
    ProposalController,
    PurchaseController,
    ReportController,
    ResignationController,
    RevenueController,
    RoleController,
    SaturationDeductionController,
    SetSalaryController,
    SourceController,
    StageController,
    SupportController,
    SystemController,
    TaskStageController,
    TaxController,
    TerminationController,
    TerminationTypeController,
    TimesheetController,
    TimeTrackerController,
    TrainerController,
    TrainingController,
    TrainingTypeController,
    TransactionController,
    TransferController,
    TravelController,
    UserController,
    VendorController,
    WarehouseController,
    WarehouseTransferController,
    WarningController,
    ZoomMeetingController,
    // AamarpayController,
    // CoingatePaymentController,
    // FlutterwavePaymentController,
    // IyziPayController,
    // MercadoPaymentController,
    // MidtransPaymentController,
    // MolliePaymentController,
    // PayFastController,
    // PaymentWallPaymentController,
    // PaypalController,
    // PaystackPaymentController,
    // PaytabController,
    // PaytmPaymentController,
    // PaytrController,
    // RazorpayPaymentController,
    // SkrillPaymentController,
    // SspayController,
    // StripePaymentController,
    // ToyyibpayController,
    // XenditPaymentController,
    // YooKassaController,
};
use App\Config\Constants\{
    BaseRoutesConstants,
    DatabaseConstants,
    MiddlewaresConstants,
    PermissionsConstants,
    ViewsConstants
};
use Modules\LandingPage\{
    Config\Constants\RoutesResourcesConstants,
    Http\Controllers\HomeController
};
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Output\ConsoleOutput;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware([MiddlewaresConstants::AUTH])->name('dashboard');

require __DIR__ . '/auth.php';

$output = new ConsoleOutput();
$msg = 'Mapping web main routes...';
app()->runningInConsole() ?
    $output->writeln('<question> ' . $msg . ' </question>') : $output->writeln($msg);

//================================= Home ====================================//
#region
Route::middleware([MiddlewaresConstants::WEB, MiddlewaresConstants::AUTH])
    ->group(function () {
        Route::get(
            '/',
            [HomeController::class, 'index']
        )->name(RoutesResourcesConstants::HM . '.index');
        Route::get(
            ViewsConstants::HM,
            [HomeController::class, 'index']
        )->name(RoutesResourcesConstants::HM . '.index.alt');
    });
//Route::get('/register/{lang?}', function () {
//    $settings = Utility::settings();
//    $lang = $settings[SettingsConstants::DEF_LNG];
//
//    if($settings[SettingsConstants::ENB_SGU] == 'on'){
//        return view("auth.register", compact('lang'));
//       // Route::get('/register', 'Auth\RegisteredUserController@showRegistrationForm')->name('register');
//    }else{
//        return Redirect::to('login');
//    }
//
//});
//
#endregion
//================================= Copy Link  ====================================//
#region
Route::get(ViewsConstants::CST . '/' . ViewsConstants::INV . '/{id}/', [InvoiceController::class, InvoiceController::IV_LK])->name(ViewsConstants::INV . '.link.copy');
Route::get(ViewsConstants::CST . '/' . ViewsConstants::PPS . '/{id}/', [ProposalController::class, ProposalController::IV_LK])->name(ViewsConstants::PPS . '.link.copy');
Route::get(ViewsConstants::PPS . '/pdfs/{id}', [ProposalController::class, 'proposal'])->name(ViewsConstants::PPS . '.pdf')
    ->middleware([MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
Route::get(ViewsConstants::VND . '/' . ViewsConstants::BIL . '/{id}/', [BillController::class, BillController::IV_LK])->name(ViewsConstants::BIL . '.link.copy');
Route::get(ViewsConstants::VND . '/' . ViewsConstants::PRC . '/{id}/', [PurchaseController::class, PurchaseController::PRC_LK])->name(ViewsConstants::PRC . '.link.copy');
#endregion
//================================= Invoice Payment Gateways  ====================================//
#region
Route::post(ViewsConstants::CST . '/pay-with-bank', [BankTransferPaymentController::class, BankTransferPaymentController::CST_PAY_BNK])->name(ViewsConstants::CST . '.pay.with.bank')
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(ViewsConstants::INV . '/{id}/action', [BankTransferPaymentController::class, BankTransferPaymentController::INV_ACT])->name(ViewsConstants::INV . '.action');
Route::post(ViewsConstants::INV . '/{id}/change-action', [BankTransferPaymentController::class, BankTransferPaymentController::INV_CG_STT])->name(ViewsConstants::INV . '.change.status');
Route::any(ViewsConstants::INV . '/with-benefit', [BenefitPaymentController::class, BenefitPaymentController::INV_PAY_BF])->name(ViewsConstants::INV . '.benefit.initiate');
Route::any(ViewsConstants::INV . '/benefit/{invoice_id}/{amount}', [BenefitPaymentController::class, BenefitPaymentController::GET_INV_PAY_STT])->name(ViewsConstants::INV . '.benefit.callback');
Route::post(ViewsConstants::INV . '/with-cashfree/payment', [CashfreeController::class, CashfreeController::INV_PAY_CF])->name(ViewsConstants::CST . '.pay.with.cashfree');
Route::any(ViewsConstants::INV . '/with-cashfree/status', [CashfreeController::class, CashfreeController::GET_INV_PAY_STT])->name(ViewsConstants::INV . '.cashfree.payment.success');
#endregion
//================================= Career Page  ====================================//
#region
Route::get(ViewsConstants::CRR . '/{id}/{lang}', [JobController::class, 'career'])->name(ViewsConstants::CRR)
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(ViewsConstants::JB . '/requirement/{code}/{lang}', [JobController::class, JobController::JB_RQ])->name(ViewsConstants::JB . '.requirement')
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(ViewsConstants::JB . '/apply/{code}/{lang}', [JobController::class, JobController::JB_AP])->name(ViewsConstants::JB . '.apply')->middleware([MiddlewaresConstants::XSS]);
Route::post(ViewsConstants::JB . '/apply/data/{code}', [JobController::class, JobController::JB_AP_DT])->name(ViewsConstants::JB . '.apply.data')->middleware([MiddlewaresConstants::XSS]);
#endregion
//================================= Project Copy Module  ====================================//
#region
Route::get(ViewsConstants::PRJ . '/copy-link/{id}', [ProjectController::class, 'projectCopyLink'])->name(ViewsConstants::PRJ . '.copy_link');
Route::any(ViewsConstants::PRJ . '/link/{id}/{lang?}', [ProjectController::class, 'projectlink'])->name(ViewsConstants::PRJ . '.link')->middleware([MiddlewaresConstants::XSS]);
Route::get(ViewsConstants::TMS . '/table-view', [TimesheetController::class, TimesheetController::FT_TMS_TBL])->name(ViewsConstants::TMS . '.filters.table.view')
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(ViewsConstants::INV . '/pdf/{id}', [InvoiceController::class, 'invoice'])->name(ViewsConstants::INV . '.pdf')
    ->middleware([MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
Route::get('/dashboard', [DashboardController::class, DashboardController::ACC_DSB_IDX])
    ->name(DashboardController::ENTITY)
    ->middleware([MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
#endregion
//================================= Invoice Payment Gateways  ====================================//

Route::group(['middleware' => [MiddlewaresConstants::VF]], function () {
    //================================= Dashboard root  ====================================//
    #region
    Route::get('/account-dashboard', [DashboardController::class, DashboardController::ACC_DSB_IDX])
        ->name(DashboardController::ENTITY)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get('/project-dashboard', [DashboardController::class, DashboardController::PRJ_DSB_IDX])
        ->name(ViewsConstants::PRJ . '.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get('/hrm-dashboard', [DashboardController::class, DashboardController::HRM_DSB_IDX])->name('hrm.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get('/crm-dashboard', [DashboardController::class, DashboardController::CRM_DSB_IDX])->name('crm.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get('/pos-dashboard', [DashboardController::class, DashboardController::POS_DSB_IDX])->name(ViewsConstants::POS . '.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::get('profile', [UserController::class, 'profile'])->name('profile')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::any('edit-profile', [UserController::class, UserController::EDT_PRF])->name('update.account')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource(DatabaseConstants::TABLE_USERS, UserController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::post('change-password', [UserController::class, UserController::UPD_PSW])
        ->name('update.password');

    Route::any('user-reset-password/{id}', [UserController::class, UserController::USR_PSW])->name(ViewsConstants::USR . '.reset');

    Route::post('user-reset-password/{id}', [UserController::class, UserController::USR_PSW_RST])->name(ViewsConstants::USR . '.password.update');

    Route::get('/change/mode', [UserController::class, UserController::CHG_MD])->name('change.mode');

    Route::resource(DatabaseConstants::TABLE_ROLES, RoleController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource(DatabaseConstants::TABLE_PERMISSIONS, PermissionController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    #endregion
    Route::group(
        //================================= Languages ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get('change-language/{lang}', [LanguageController::class, LanguageController::CHG_LNG])->name(ViewsConstants::LNG . '.change');
            Route::get('manage-language/{lang}', [LanguageController::class, LanguageController::MNG_LNG])->name(ViewsConstants::LNG . '.manage');
            Route::post('store-language-data/{lang}', [LanguageController::class, LanguageController::STR_LNG_DT])->name(ViewsConstants::LNG . '.store.data');
            Route::get('create-language', [LanguageController::class, LanguageController::CR_LNG])->name(ViewsConstants::LNG . '.create');
            Route::any('store-language', [LanguageController::class, LanguageController::STR_LNG])->name(ViewsConstants::LNG . '.store');
            Route::delete('/lang/{lang}', [LanguageController::class, LanguageController::DEL_LNG])->name(ViewsConstants::LNG . '.destroy');
        }
        #endregion
    );

    Route::group(
        //================================= System Settings  ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::resource('systems', SystemController::class);
            Route::post('email-settings', [SystemController::class, SystemController::SV_EM_ST])->name(ViewsConstants::EML . '.settings');
            Route::post('company-email-settings', [SystemController::class, SystemController::SV_CP_EM_ST])->name(ViewsConstants::CP . '.email.settings');
            Route::post('company-settings', [SystemController::class, SystemController::SV_CP_ST])->name(ViewsConstants::CP . '.settings');
            Route::post('system-settings', [SystemController::class, SystemController::SV_SYS_ST])->name(ViewsConstants::SYS . '.settings');
            Route::post('zoom-settings', [SystemController::class, SystemController::SV_ZM_ST])->name('zoom.settings');
            Route::post('tracker-settings', [SystemController::class, SystemController::SV_TK_ST])->name('time_trackers.settings');
            Route::post('slack-settings', [SystemController::class, SystemController::SV_SLK_ST])->name('slack.settings');
            Route::post('telegram-settings', [SystemController::class, SystemController::SV_TLG_ST])->name('telegram.settings');
            Route::post('twilio-settings', [SystemController::class, SystemController::SV_TWL_ST])->name('twilio.setting');
            Route::get('print-setting', [SystemController::class, SystemController::PRT])->name('print.setting');
            Route::get('settings', [SystemController::class, SystemController::CP])->name('settings');
            Route::post('business-setting', [SystemController::class, SystemController::SV_BS_ST])->name('business.setting');
            Route::post('company-payment-setting', [SystemController::class, SystemController::SV_CP_PAY_ST])
                ->name(ViewsConstants::CP . '.payment.settings');
            Route::get('test-mail', [SystemController::class, SystemController::TT_MAIL])->name(ViewsConstants::TT . '.mail');
            Route::post('test-mail', [SystemController::class, SystemController::TT_MAIL])->name(ViewsConstants::TT . '.mail');
            Route::post('test-mail/send', [SystemController::class, SystemController::TT_SMAIL])->name(ViewsConstants::TT . '.send.mail');
            Route::post('stripe-settings', [SystemController::class, SystemController::SV_PAY_ST])->name(ViewsConstants::PAY . '.settings');
            Route::post('pusher-setting', [SystemController::class, SystemController::SV_PSR_ST])->name(ViewsConstants::SET . '.pusher');
            Route::post('recaptcha-settings', [SystemController::class, SystemController::RCP_ST_STR])->name('settings.recaptcha.store')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('seo-settings', [SystemController::class, SystemController::SEO_ST])->name(ViewsConstants::SET . '.seo.store')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::any('webhook-settings', [SystemController::class, 'webhook'])->name(ViewsConstants::WBH . '.settings')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::get('webhook-settings/create', [SystemController::class, SystemController::WHK_CRT])->name(ViewsConstants::WBH . '.create')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('webhook-settings/store', [SystemController::class, SystemController::WHK_STR])->name(ViewsConstants::WBH . '.store');
            Route::get('webhook-settings/{wid}/edit', [SystemController::class, SystemController::WHK_EDT])->name(ViewsConstants::WBH . '.edit')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('webhook-settings/{wid}/edit', [SystemController::class, SystemController::WHK_UPD])->name(ViewsConstants::WBH . '.update')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::delete('webhook-settings/{wid}', [SystemController::class, SystemController::WHK_DST])->name(ViewsConstants::WBH . '.destroy')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('cookie-setting', [SystemController::class, SystemController::SV_CK_ST])->name(ViewsConstants::SET . '.cookies.store');
            Route::post('cache-settings', [SystemController::class, SystemController::CC_ST_STR])->name('cache.settings.store')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
        }
        #endregion
    );

    //================================= Product Services ====================================//
    #region
    Route::get(ViewsConstants::PRD_SV . '/index', [ProductServiceController::class, 'index'])
        ->name(ViewsConstants::PRD_SV . '.index');
    Route::get(ViewsConstants::PRD_SV . '/{id}/detail', [ProductServiceController::class, ProductServiceController::WRH_DTL])
        ->name(ViewsConstants::PRD_SV . '.detail');
    Route::post('empty-cart', [ProductServiceController::class, ProductServiceController::EMP_CRT])
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('warehouse-empty-cart', [ProductServiceController::class, ProductServiceController::WRH_EMP_CRT])
        ->name('warehouse-empty-cart')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::PRD_SV, ProductServiceController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    #endregion

    //================================= Product Stock ====================================//
    #region
    Route::resource(ViewsConstants::PRD_STK, ProductStockController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    #endregion

    Route::group(
        //================================= Customers ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::CST . '/{id}/show', [CustomerController::class, 'show'])
                ->name(ViewsConstants::CST . '.show');
            Route::resource(ViewsConstants::CST, CustomerController::class);
        }
        #endregion
    );

    //Vendor
    Route::group(
        //================================= Vendors ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::VND . '/{id}/show', [VendorController::class, 'show'])
                ->name(ViewsConstants::VND . '.show');
            Route::resource(ViewsConstants::VND, VendorController::class);
        }
        #endregion
    );

    Route::group(
        //================================= Bank Accounts ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::resource(ViewsConstants::BNK_ACC, BankAccountController::class);
        }
        #endregion
    );

    Route::group(
        //================================= Bank Transfers ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::BNK_TRF . '/index', [BankTransferController::class, 'index'])->name(ViewsConstants::BNK_TRF . '.index');
            Route::resource(ViewsConstants::BNK_TRF, BankTransferController::class);
        }
        #endregion
    );

    //================================= Product Service Categories ====================================//
    #region
    Route::resource(ViewsConstants::TX, TaxController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::resource(ViewsConstants::PRD_SV_CAT, ProductServiceCategoryController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::post(ViewsConstants::PRD_SV_CAT . '/get-account', [ProductServiceCategoryController::class, ProductServiceCategoryController::GET_ACC])
        ->name(ViewsConstants::PRD_SV_CAT . '.get_account')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::resource(ViewsConstants::PRD_SV_UNT, ProductServiceUnitController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    #endregion

    //================================= Invoices ====================================//
    #region
    Route::group(
        //================================= Invoices Procedures ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::INV . '/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name(ViewsConstants::INV . '.duplicate');
            Route::get(ViewsConstants::INV . '/{id}/shipping/print', [InvoiceController::class, InvoiceController::SHP_DSP])->name(ViewsConstants::INV . '.shipping.print');
            Route::get(ViewsConstants::INV . '/{id}/payment/reminder', [InvoiceController::class, InvoiceController::PAY_RMD])->name(ViewsConstants::INV . '.payment.reminder');
            Route::get(ViewsConstants::INV . '/index', [InvoiceController::class, 'index'])->name(ViewsConstants::INV . '.index');
            Route::post(ViewsConstants::INV . '/product/destroy', [InvoiceController::class, InvoiceController::PRD_DST])->name(ViewsConstants::INV . '.product.destroy');
            Route::post(ViewsConstants::INV . '/product', [InvoiceController::class, 'product'])->name(ViewsConstants::INV . '.product');
            Route::post(ViewsConstants::INV . '/customer', [InvoiceController::class, 'customer'])->name(ViewsConstants::INV . '.customer');
            Route::get(ViewsConstants::INV . '/{id}/sent', [InvoiceController::class, 'sent'])->name(ViewsConstants::INV . '.sent');
            Route::get(ViewsConstants::INV . '/{id}/resent', [InvoiceController::class, 'resent'])->name(ViewsConstants::INV . '.resent');
            Route::get(ViewsConstants::INV . '/{id}/payment', [InvoiceController::class, 'payment'])->name(ViewsConstants::INV . '.payment');
            Route::post(ViewsConstants::INV . '/{id}/payment', [InvoiceController::class, InvoiceController::PAY_CRT])->name(ViewsConstants::INV . '.payment');
            Route::post(ViewsConstants::INV . '/{id}/payment/{pid}/destroy', [InvoiceController::class, InvoiceController::PAY_DST])
                ->name(ViewsConstants::INV . '.payment.destroy');
            Route::get(ViewsConstants::INV . '/items', [InvoiceController::class, 'items'])->name(ViewsConstants::INV . '.items');
            Route::resource(ViewsConstants::INV, InvoiceController::class);
            Route::get(ViewsConstants::INV . '/create/{cid}', [InvoiceController::class, 'create'])->name(ViewsConstants::INV . '.create');
        }
        #endregion
    );
    Route::get(ViewsConstants::INV . '/preview/{template}/{color}', [InvoiceController::class, InvoiceController::INV_PRV])->name(ViewsConstants::INV . '.preview');
    Route::post(ViewsConstants::INV . '/template/setting', [InvoiceController::class, InvoiceController::SV_IV_TMP])
        ->name(ViewsConstants::INV_TMP . 'settings');

    Route::group(
        //================================= Credit Invoices ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(str_replace('_', '-', ViewsConstants::CRD_NT), [CreditNoteController::class, 'index'])->name('credit.note');
            Route::get('custom-credit-note', [CreditNoteController::class, 'customCreate'])->name(ViewsConstants::INV . '.custom.credit.note');
            Route::post('custom-credit-note', [CreditNoteController::class, 'customStore'])->name(ViewsConstants::INV . '.custom.credit.note');
            Route::get(ViewsConstants::CRD_NT . '/invoice', [CreditNoteController::class, 'getInvoice'])->name(ViewsConstants::INV . '.get');
            Route::get(ViewsConstants::INV . '/{id}/credit-note', [CreditNoteController::class, 'create'])->name(ViewsConstants::INV . '.credit.note');
            Route::post(ViewsConstants::INV . '/{id}/credit-note', [CreditNoteController::class, 'store'])->name(ViewsConstants::INV . '.credit.note');
            Route::get(ViewsConstants::INV . '/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'edit'])->name(ViewsConstants::INV . '.edit.credit.note');
            Route::post(ViewsConstants::INV . '/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'update'])
                ->name(ViewsConstants::INV . '.edit.credit.note');
            Route::delete(ViewsConstants::INV . '/{id}/credit-note/delete/{cn_id}', [CreditNoteController::class, 'destroy'])
                ->name(ViewsConstants::INV . '.delete.credit.note');
        }
        #endregion
    );
    #endregion

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::DBT_NT, [DebitNoteController::class, 'index'])->name('debit.note');
            Route::get('custom-debit-note', [DebitNoteController::class, 'customCreate'])->name(ViewsConstants::BIL . '.custom.debit.note');
            Route::post('custom-debit-note', [DebitNoteController::class, 'customStore'])->name(ViewsConstants::BIL . '.custom.debit.note');
            Route::get(ViewsConstants::DBT_NT . '/bill', [DebitNoteController::class, 'getbill'])->name(ViewsConstants::BIL . '.get');
            Route::get(ViewsConstants::BIL . '{id}/debit-note', [DebitNoteController::class, 'create'])->name(ViewsConstants::BIL . '.debit.note');
            Route::post(ViewsConstants::BIL . '{id}/debit-note', [DebitNoteController::class, 'store'])->name(ViewsConstants::BIL . '.debit.note');
            Route::get(ViewsConstants::BIL . '{id}/' . ViewsConstants::DBT_NT . '/edit/{cn_id}', [DebitNoteController::class, 'edit'])->name(ViewsConstants::BIL . '.edit.debit.note');
            Route::post(ViewsConstants::BIL . '{id}/' . ViewsConstants::DBT_NT . '/edit/{cn_id}', [DebitNoteController::class, 'update'])->name(ViewsConstants::BIL . '.edit.debit.note');
            Route::delete(ViewsConstants::BIL . '{id}/' . ViewsConstants::DBT_NT . '/delete/{cn_id}', [DebitNoteController::class, 'destroy'])->name(ViewsConstants::BIL . '.delete.debit.note');
        }
    );

    Route::get(ViewsConstants::BIL . 'preview/{template}/{color}', [BillController::class, 'previewBill'])->name(ViewsConstants::BIL . '.preview')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::BIL . 'template/setting', [BillController::class, BillController::SV_BIL_TMP])
        ->name(ViewsConstants::BIL_TMP . 'setting');

    Route::resource(ViewsConstants::TX, TaxController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::get(ViewsConstants::RVN . '/index', [RevenueController::class, 'index'])->name(ViewsConstants::RVN . '.index')->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    Route::resource(ViewsConstants::RVN, RevenueController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    Route::get(ViewsConstants::BIL . 'pdf/{id}', [BillController::class, 'bill'])->name(ViewsConstants::BIL . '.pdf')->middleware([
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::BIL . '{id}/duplicate', [BillController::class, 'duplicate'])->name(ViewsConstants::BIL . '.duplicate');
            Route::get(ViewsConstants::BIL . '{id}/shipping/print', [BillController::class, 'shippingDisplay'])->name(ViewsConstants::BIL . '.shipping.print');
            Route::get(ViewsConstants::BIL . 'index', [BillController::class, 'index'])->name(ViewsConstants::BIL . '.index');
            Route::post(ViewsConstants::BIL . 'product/destroy', [BillController::class, 'productDestroy'])->name(ViewsConstants::BIL . '.product.destroy');
            Route::post(ViewsConstants::BIL . 'product', [BillController::class, 'product'])->name(ViewsConstants::BIL . '.product');
            Route::post(ViewsConstants::BIL . 'vendor', [BillController::class, 'vendor'])->name(ViewsConstants::BIL . '.vendor');
            Route::get(ViewsConstants::BIL . '{id}/sent', [BillController::class, 'sent'])->name(ViewsConstants::BIL . '.sent');
            Route::get(ViewsConstants::BIL . '{id}/resent', [BillController::class, 'resent'])->name(ViewsConstants::BIL . '.resent');
            Route::get(ViewsConstants::BIL . '{id}/payment', [BillController::class, 'payment'])->name(ViewsConstants::BIL . '.payment');
            Route::post(ViewsConstants::BIL . '{id}/payment', [BillController::class, 'createPayment'])->name(ViewsConstants::BIL . '.payment');
            Route::post(ViewsConstants::BIL . '{id}/payment/{pid}/destroy', [BillController::class, 'paymentDestroy'])->name(ViewsConstants::BIL . '.payment.destroy');
            Route::get(ViewsConstants::BIL . 'items', [BillController::class, 'items'])->name(ViewsConstants::BIL . '.items');
            Route::resource(ViewsConstants::BIL, BillController::class);
            Route::get(ViewsConstants::BIL . 'create/{cid}', [BillController::class, 'create'])->name(ViewsConstants::BIL . '.create');
        }
    );

    Route::get('payment/index', [PaymentController::class, 'index'])->name('payment.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource('payment', PaymentController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::RPT . '/transaction', [TransactionController::class, 'index'])->name('transaction.index');
        }
    );

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::RPT . '/income-summary', [ReportController::class, 'incomeSummary'])->name(ViewsConstants::RPT . '.income.summary');
            Route::get(ViewsConstants::RPT . '/expense-summary', [ReportController::class, 'expenseSummary'])->name(ViewsConstants::RPT . '.expense.summary');
            Route::get(ViewsConstants::RPT . '/income-vs-expense-summary', [ReportController::class, 'incomeVsExpenseSummary'])->name(ViewsConstants::RPT . '.income.vs.expense.summary');
            Route::get(ViewsConstants::RPT . '/tax-summary', [ReportController::class, 'taxSummary'])->name(ViewsConstants::RPT . '.tax.summary');
            //        Route::get(ViewsConstants::RPT.'/profit-loss-summary', [ReportController::class, 'profitLossSummary'])->name(ViewsConstants::RPT.'.profit.loss.summary');
            Route::get(ViewsConstants::RPT . '/invoice-summary', [ReportController::class, 'invoiceSummary'])->name(ViewsConstants::RPT . '.invoice.summary');
            Route::get(ViewsConstants::RPT . '/bill-summary', [ReportController::class, 'billSummary'])->name(ViewsConstants::RPT . '.bill.summary');
            Route::get(ViewsConstants::RPT . '/product-stock-report', [ReportController::class, 'productStock'])->name(ViewsConstants::RPT . '.product.stock.report');
            Route::get(ViewsConstants::RPT . '/invoice-report', [ReportController::class, 'invoiceReport'])->name(ViewsConstants::RPT . '.invoice');
            Route::get(ViewsConstants::RPT . '/account-statement-report', [ReportController::class, 'accountStatement'])->name(ViewsConstants::RPT . '.account.statement');
            Route::get(ViewsConstants::RPT . '/balance-sheet/{view?}', [ReportController::class, 'balanceSheet'])->name(ViewsConstants::RPT . '.balance.sheet');
            Route::get(ViewsConstants::RPT . '/profit-loss/{view?}', [ReportController::class, 'profitLoss'])->name(ViewsConstants::RPT . '.profit.loss');

            Route::get(ViewsConstants::RPT . '/ledger/{account?}', [ReportController::class, 'ledgerSummary'])->name(ViewsConstants::RPT . '.ledger');
            Route::get(ViewsConstants::RPT . '/trial-balance', [ReportController::class, 'trialBalanceSummary'])->name('trial.balance');

            Route::get(ViewsConstants::RPT . '-monthly-cashflow', [ReportController::class, 'monthlyCashflow'])->name(ViewsConstants::RPT . '.monthly.cashflow')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::get(ViewsConstants::RPT . '-quarterly-cashflow', [ReportController::class, 'quarterlyCashflow'])->name(ViewsConstants::RPT . '.quarterly.cashflow')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('export/trial-balance', [ReportController::class, 'trialBalanceExport'])->name('trial.balance.export');
            Route::post('export/balance-sheet', [ReportController::class, 'balanceSheetExport'])->name(ViewsConstants::RPT . '.balance.sheet.export');
            Route::post('print/balance-sheet/{view?}', [ReportController::class, 'balanceSheetPrint'])->name(ViewsConstants::RPT . '.balance.sheet.print');
            Route::post('print/trial-balance', [ReportController::class, 'trialBalancePrint'])->name('trial.balance.print');
            Route::post('export/profit-loss', [ReportController::class, 'profitLossExport'])->name('profit.loss.export');
            Route::post('print/profit-loss/{view?}', [ReportController::class, 'profitLossPrint'])->name('profit.loss.print');
            Route::get(ViewsConstants::RPT . '/sales', [ReportController::class, 'salesReport'])->name(ViewsConstants::RPT . '.sales');
            Route::post('export/sales', [ReportController::class, 'salesReportExport'])->name('sales.export');
            Route::post('print/sales-report', [ReportController::class, 'salesReportPrint'])->name('sales.report.print');
            Route::get(ViewsConstants::RPT . '/receivables', [ReportController::class, 'ReceivablesReport'])->name(ViewsConstants::RPT . '.receivables');
            Route::post('export/receivables', [ReportController::class, 'ReceivablesExport'])->name('receivables.export');
            Route::post('print/receivables', [ReportController::class, 'ReceivablesPrint'])->name('receivables.print');
            Route::get(ViewsConstants::RPT . '/payables', [ReportController::class, 'PayablesReport'])->name(ViewsConstants::RPT . '.payables');
            Route::post('print/payables', [ReportController::class, 'PayablesPrint'])->name('payables.print');
        }
    );

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::PPS . '/{id}/status/change', [ProposalController::class, ProposalController::STT_CHG])->name(ViewsConstants::PPS . '.status.change');
            Route::get(ViewsConstants::PPS . '/{id}/convert', [ProposalController::class, 'convert'])->name(ViewsConstants::PPS . '.convert');
            Route::get(ViewsConstants::PPS . '/{id}/duplicate', [ProposalController::class, 'duplicate'])->name(ViewsConstants::PPS . '.duplicate');
            Route::post(ViewsConstants::PPS . '/product/destroy', [ProposalController::class, ProposalController::PRD_DST])->name(ViewsConstants::PPS . '.product.destroy');
            Route::post(ViewsConstants::PPS . '/customer', [ProposalController::class, 'customer'])->name(ViewsConstants::PPS . '.customer');
            Route::post(ViewsConstants::PPS . '/product', [ProposalController::class, 'product'])->name(ViewsConstants::PPS . '.product');
            Route::get(ViewsConstants::PPS . '/items', [ProposalController::class, 'items'])->name(ViewsConstants::PPS . '.items');
            Route::get(ViewsConstants::PPS . '/{id}/sent', [ProposalController::class, 'sent'])->name(ViewsConstants::PPS . '.sent');
            Route::get(ViewsConstants::PPS . '/{id}/resent', [ProposalController::class, 'resent'])->name(ViewsConstants::PPS . '.resent');
            Route::resource('proposal', ProposalController::class);
            Route::get(ViewsConstants::PPS . '/create/{cid}', [ProposalController::class, 'create'])->name(ViewsConstants::PPS . '.create');
        }
    );

    Route::get(ViewsConstants::PPS . '/preview/{template}/{color}', [ProposalController::class, ProposalController::PV_PPS])->name(ViewsConstants::PPS . '.preview');
    Route::post(ViewsConstants::PPS . '/templates/settings', [ProposalController::class, ProposalController::SV_PPS_TMP])
        ->name(ViewsConstants::PPS . 'settings');

    Route::resource('goal', GoalController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    //Budget Planner //
    Route::resource('budget', BudgetController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource('account_assets', AssetController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource(ViewsConstants::CST_FD, CustomFieldController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::post(ViewsConstants::COA . '/subtype', [ChartOfAccountController::class, 'getSubType'])->name(ViewsConstants::COA . '.sub_type')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::resource(ViewsConstants::COA, ChartOfAccountController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {

            Route::post('journal-entry/account/destroy', [JournalEntryController::class, 'accountDestroy'])->name('journal.account.destroy');

            Route::delete('journal-entry/journal/destroy/{item_id}', [JournalEntryController::class, 'journalDestroy'])->name('journal.destroy');
            Route::resource('journal-entry', JournalEntryController::class);
        }
    );

    // Client Module

    Route::resource('clients', ClientController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::any('client-reset-password/{id}', [ClientController::class, 'clientPassword'])->name('clients.reset');
    Route::post('client-reset-password/{id}', [ClientController::class, 'clientPasswordReset'])->name('client.password.update');

    // Deal Module

    Route::post(ViewsConstants::DL . '/user', [DealController::class, 'jsonUser'])->name(ViewsConstants::DL . '.user.json');
    Route::post(ViewsConstants::DL . '/order', [DealController::class, 'order'])->name(ViewsConstants::DL . '.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/change-pipeline', [DealController::class, 'changePipeline'])->name(ViewsConstants::DL . '.change.pipeline')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/change-deal-status/{id}', [DealController::class, 'changeStatus'])->name(ViewsConstants::DL . '.change.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/labels', [DealController::class, 'labels'])->name(ViewsConstants::DL . '.labels')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/{id}/labels', [DealController::class, 'labelStore'])->name(ViewsConstants::DL . '.labels.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/users', [DealController::class, 'userEdit'])->name(ViewsConstants::DL . '.users.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(ViewsConstants::DL . '/{id}/users', [DealController::class, 'userUpdate'])->name(ViewsConstants::DL . '.users.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::DL . '/{id}/users/{uid}', [DealController::class, 'userDestroy'])->name(ViewsConstants::DL . '.users.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/clients', [DealController::class, 'clientEdit'])->name(ViewsConstants::DL . '.clients.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(ViewsConstants::DL . '/{id}/clients', [DealController::class, 'clientUpdate'])->name(ViewsConstants::DL . '.clients.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::DL . '/{id}/clients/{uid}', [DealController::class, 'clientDestroy'])->name(ViewsConstants::DL . '.clients.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/products', [DealController::class, 'productEdit'])->name(ViewsConstants::DL . '.products.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(ViewsConstants::DL . '/{id}/products', [DealController::class, 'productUpdate'])->name(ViewsConstants::DL . '.products.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::DL . '/{id}/products/{uid}', [DealController::class, 'productDestroy'])->name(ViewsConstants::DL . '.products.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/sources', [DealController::class, 'sourceEdit'])->name(ViewsConstants::DL . '.sources.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(ViewsConstants::DL . '/{id}/sources', [DealController::class, 'sourceUpdate'])->name(ViewsConstants::DL . '.sources.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::DL . '/{id}/sources/{uid}', [DealController::class, 'sourceDestroy'])->name(ViewsConstants::DL . '.sources.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/{id}/file', [DealController::class, 'fileUpload'])->name(ViewsConstants::DL . '.file.upload')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/file/{fid}', [DealController::class, 'fileDownload'])->name(ViewsConstants::DL . '.file.download')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::DL . '/{id}/file/delete/{fid}', [DealController::class, 'fileDelete'])->name(ViewsConstants::DL . '.file.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/{id}/note', [DealController::class, 'noteStore'])->name(ViewsConstants::DL . '.note.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get(ViewsConstants::DL . '/{id}/' . ViewsConstants::TSK, [DealController::class, 'taskCreate'])->name(ViewsConstants::DL . '.tasks.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/{id}/' . ViewsConstants::TSK, [DealController::class, 'taskStore'])->name(ViewsConstants::DL . '.tasks.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/' . ViewsConstants::TSK . '/{tid}/show', [DealController::class, 'taskShow'])->name(ViewsConstants::DL . '.tasks.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/' . ViewsConstants::TSK . '/{tid}/edit', [DealController::class, 'taskEdit'])->name(ViewsConstants::DL . '.tasks.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(ViewsConstants::DL . '/{id}/' . ViewsConstants::TSK . '/{tid}', [DealController::class, 'taskUpdate'])->name(ViewsConstants::DL . '.tasks.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(ViewsConstants::DL . '/{id}/task_status/{tid}', [DealController::class, 'taskUpdateStatus'])->name(ViewsConstants::DL . '.tasks.update_status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::DL . '/{id}/' . ViewsConstants::TSK . '/{tid}', [DealController::class, 'taskDestroy'])->name(ViewsConstants::DL . '.tasks.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/discussions', [DealController::class, 'discussionCreate'])->name(ViewsConstants::DL . '.discussions.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/{id}/discussions', [DealController::class, 'discussionStore'])->name(ViewsConstants::DL . '.discussion.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/{id}/permission/{cid}', [DealController::class, 'permission'])->name(ViewsConstants::DL . '.client.permission')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(ViewsConstants::DL . '/{id}/permission/{cid}', [DealController::class, 'permissionStore'])->name(ViewsConstants::DL . '.client.permissions.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::DL . '/list', [DealController::class, 'deal_list'])->name(ViewsConstants::DL . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Deal Calls

    Route::get(ViewsConstants::DL . '/{id}/call', [DealController::class, 'callCreate'])->name(ViewsConstants::DL . '.calls.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/{id}/call', [DealController::class, 'callStore'])->name(ViewsConstants::DL . '.calls.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get(ViewsConstants::DL . '/{id}/call/{cid}/edit', [DealController::class, 'callEdit'])->name(ViewsConstants::DL . '.calls.edit')->middleware([MiddlewaresConstants::AUTH]);
    Route::put(ViewsConstants::DL . '/{id}/call/{cid}', [DealController::class, 'callUpdate'])->name(ViewsConstants::DL . '.calls.update')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete(ViewsConstants::DL . '/{id}/call/{cid}', [DealController::class, 'callDestroy'])->name(ViewsConstants::DL . '.calls.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Deal Email

    Route::get(ViewsConstants::DL . '/{id}/email', [DealController::class, 'emailCreate'])->name(ViewsConstants::DL . '.emails.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::DL . '/{id}/email', [DealController::class, 'emailStore'])->name(ViewsConstants::DL . '.emails.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(ViewsConstants::DL, DealController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // end Deal Module

    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    Route::post('/stages/order', [StageController::class, 'order'])->name('stages.order');
    Route::post('/stages/json', [StageController::class, 'json'])->name('stages.json');

    Route::resource('stages', StageController::class);
    Route::resource('pipelines', PipelineController::class);
    Route::resource('labels', LabelController::class);
    Route::resource('sources', SourceController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('custom_fields', CustomFieldController::class);

    // Leads Module

    Route::post('/lead_stages/order', [LeadStageController::class, 'order'])->name('lead_stages.order');

    Route::resource('lead_stages', LeadStageController::class)->middleware([MiddlewaresConstants::AUTH]);

    Route::post('/leads/json', [LeadController::class, 'json'])->name(ViewsConstants::LD . '.json');
    Route::post('/leads/order', [LeadController::class, 'order'])->name(ViewsConstants::LD . '.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/list', [LeadController::class, 'lead_list'])->name(ViewsConstants::LD . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name(ViewsConstants::LD . '.file.upload')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name(ViewsConstants::LD . '.file.download')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name(ViewsConstants::LD . '.file.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name(ViewsConstants::LD . '.note.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name(ViewsConstants::LD . '.labels')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name(ViewsConstants::LD . '.labels.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name(ViewsConstants::LD . '.users.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name(ViewsConstants::LD . '.users.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name(ViewsConstants::LD . '.users.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/products', [LeadController::class, 'productEdit'])->name(ViewsConstants::LD . '.products.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/products', [LeadController::class, 'productUpdate'])->name(ViewsConstants::LD . '.products.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/products/{uid}', [LeadController::class, 'productDestroy'])->name(ViewsConstants::LD . '.products.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name(ViewsConstants::LD . '.sources.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name(ViewsConstants::LD . '.sources.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name(ViewsConstants::LD . '.sources.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name(ViewsConstants::LD . '.discussions.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name(ViewsConstants::LD . '.discussion.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name(ViewsConstants::LD . '.convert.deal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name(ViewsConstants::LD . '.convert.to.deal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Lead Calls
    Route::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name(ViewsConstants::LD . '.calls.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name(ViewsConstants::LD . '.calls.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name(ViewsConstants::LD . '.calls.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name(ViewsConstants::LD . '.calls.update')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name(ViewsConstants::LD . '.calls.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Lead Email

    Route::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name(ViewsConstants::LD . '.emails.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name(ViewsConstants::LD . '.emails.store')->middleware([MiddlewaresConstants::AUTH]);

    Route::resource('leads', LeadController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // end Leads Module

    Route::get(ViewsConstants::USR . '/{id}/plan', [UserController::class, UserController::UPG_PLN])->name(ViewsConstants::PLN . '.upgrade')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::USR . '/{id}/plan/{pid}', [UserController::class, UserController::ACT_PLN])->name(ViewsConstants::PLN . '.active')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/{uid}/notification/seen', [UserController::class, 'notificationSeen'])->name('notification.seen');

    // Email Templates
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('email_template_store', [EmailTemplateController::class, 'updateStatus'])->name(ViewsConstants::EMLS . '.status.language')->middleware([MiddlewaresConstants::AUTH]);
    Route::any('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name(ViewsConstants::EMLS . '.store.language')->middleware([MiddlewaresConstants::AUTH]);
    Route::resource('email_template', EmailTemplateController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // End Email Templates

    // HRM
    Route::resource('user', UserController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::EMP . '/json', [EmployeeController::class, 'json'])->name(ViewsConstants::EMP . '.json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('branchViewsConstants::EMP.//json', [EmployeeController::class, 'employeeJson'])->name(ViewsConstants::BRC . '.employee.json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('employee-profile', [EmployeeController::class, 'profile'])->name(ViewsConstants::EMP . '.profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('show-employee-profile/{id}', [EmployeeController::class, 'profileShow'])->name('show.employee.profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get('last-login', [EmployeeController::class, 'lastLogin'])->name('last_login')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(ViewsConstants::EMP, EmployeeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(ViewsConstants::EMP . '/getdepartment', [EmployeeController::class, 'getDepartment'])->name(ViewsConstants::EMP . '.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(ViewsConstants::DPT, DepartmentController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::DSG, DesignationController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::DOC, DocumentController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::BRC, BranchController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Hrm EmployeeController

    Route::get(ViewsConstants::EMP . '/salary/{eid}', [SetSalaryController::class, 'employeeBasicSalary'])->name(ViewsConstants::EMP . '.salary.basic')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //payslip

    Route::resource(ViewsConstants::ALW, AllowanceController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::ALW_OPT, AllowanceOptionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::COM, CommissionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::DDT_OPT, DeductionOptionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::LN_OPT, LoanOptionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::LN, LoanController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::PY_SLP, PayslipTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::STR_DD, SaturationDeductionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::OT_PAY, OtherPaymentController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::OVT, OvertimeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::EMP . '/salary/{eid}', [SetSalaryController::class, SetSalaryController::EMP_SL_BASIC])->name(ViewsConstants::EMP . '.salary.basic')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::EMP . '/update/sallary/{id}', [SetSalaryController::class, SetSalaryController::EMP_SL_UPDATE])->name(ViewsConstants::EMP . '.salary.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::EMP . '/salary', [SetSalaryController::class, SetSalaryController::EMP_SL])->name(ViewsConstants::EMP . '.salary')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::S_SLR, SetSalaryController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::ALW . '/create/{eid}', [AllowanceController::class, AllowanceController::ALW_CR])->name(ViewsConstants::ALW . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::COM . '/create/{eid}', [CommissionController::class, CommissionController::COM_CR])->name(ViewsConstants::COM . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('loans/create/{eid}', [LoanController::class, 'loanCreate'])->name('loans.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::STR_DD . '/create/{eid}', [SaturationDeductionController::class, SaturationDeductionController::STR_DD_CR])->name(ViewsConstants::STR_DD . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::OT_PAY . '/create/{eid}', [OtherPaymentController::class, OtherPaymentController::OT_PAY_CR])->name(ViewsConstants::OT_PAY . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('overtimes/create/{eid}', [OvertimeController::class, 'overtimeCreate'])->name('overtimes.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/paysalary/{id}/{date}', [PayslipController::class, 'paysalary'])->name(ViewsConstants::PY_SLP . '.paysalary')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/bulk_pay_create/{date}', [PayslipController::class, 'bulkPayCreate'])->name(ViewsConstants::PY_SLP . '.bulk_pay_create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PY_SLP . '/bulkpayment/{date}', [PayslipController::class, 'bulkPayment'])->name(ViewsConstants::PY_SLP . '.bulkpayment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PY_SLP . '/search_json', [PayslipController::class, 'searchJson'])->name(ViewsConstants::PY_SLP . '.search_json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/employeepayslip', [PayslipController::class, 'employeePayslip'])->name(ViewsConstants::PY_SLP . '.employeepayslip')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/show/{id}', [PayslipController::class, 'showEmployee'])->name(ViewsConstants::PY_SLP . '.showemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/edit/{id}', [PayslipController::class, 'editEmployee'])->name(ViewsConstants::PY_SLP . '.editemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PY_SLP . '/update/{id}', [PayslipController::class, 'updateEmployee'])->name(ViewsConstants::PY_SLP . '.updateemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/pdf/{id}/{m}', [PayslipController::class, 'pdf'])->name(ViewsConstants::PY_SLP . '.pdf')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/payslipPdf/{id}', [PayslipController::class, 'payslipPdf'])->name(ViewsConstants::PY_SLP . '.payslipPdf')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/send/{id}/{m}', [PayslipController::class, 'send'])->name(ViewsConstants::PY_SLP . '.send')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PY_SLP . '/delete/{id}', [PayslipController::class, 'destroy'])->name(ViewsConstants::PY_SLP . '.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::PY_SLP, PayslipController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(ViewsConstants::CPN_PL, CompanyPolicyController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::IND, IndicatorController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::APR, AppraisalController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(ViewsConstants::BRC . '/' . ViewsConstants::EMP . '/json', [EmployeeController::class, 'employeeJson'])->name(ViewsConstants::BRC . '.employee.json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(ViewsConstants::GL_TP, GoalTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::GL_TRC, GoalTrackingController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('account_assets', AssetController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post('event/getdepartment', [EventController::class, 'getDepartment'])->name('event.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('event/getemployee', [EventController::class, 'getEmployee'])->name('event.getemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('event', EventController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post('meeting/getdepartment', [MeetingController::class, 'getDepartment'])->name('meeting.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('meeting/getemployee', [MeetingController::class, 'getEmployee'])->name('meeting.getemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('meeting', MeetingController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('trainingtype', TrainingTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('trainer', TrainerController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post('training/status', [TrainingController::class, 'updateStatus'])->name('training.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('training', TrainingController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // HRM - HR Module

    Route::resource(ViewsConstants::AWD_TP, AwardTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::AWD, AwardController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('resignation', ResignationController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('travel', TravelController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('promotion', PromotionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('complaint', ComplaintController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('warning', WarningController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('termination', TerminationController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('termination/{id}/description', [TerminationController::class, 'description'])->name('termination.description');
    Route::resource('terminationtype', TerminationTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post('announcement/getdepartment', [AnnouncementController::class, 'getdepartment'])->name('announcement.getdepartment');
    Route::post('announcement/getemployee', [AnnouncementController::class, 'getemployee'])->name('announcement.getemployee');
    Route::resource('announcement', AnnouncementController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('holiday', HolidayController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('holiday-calendar', [HolidayController::class, 'calendar'])->name('holiday.calendar');

    // Recruitement

    Route::resource('job-category', JobCategoryController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('job-stage', JobStageController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-stage/order', [JobStageController::class, 'order'])->name(ViewsConstants::JB . '.stage.order');

    Route::resource('job', JobController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get('candidates-job-applications', [JobApplicationController::class, 'candidate'])->name(ViewsConstants::JB . '.application.candidate')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('job-application', JobApplicationController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/order', [JobApplicationController::class, 'order'])->name(ViewsConstants::JB . '.application.order')->middleware([MiddlewaresConstants::XSS]);
    Route::post('job-application/{id}/rating', [JobApplicationController::class, 'rating'])->name(ViewsConstants::JB . '.application.rating')->middleware([MiddlewaresConstants::XSS]);
    Route::delete('job-application/{id}/archive', [JobApplicationController::class, 'archive'])->name(ViewsConstants::JB . '.application.archive')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/{id}/skill/store', [JobApplicationController::class, 'addSkill'])->name(ViewsConstants::JB . '.application.skill.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/{id}/note/store', [JobApplicationController::class, 'addNote'])->name(ViewsConstants::JB . '.application.note.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('job-application/{id}/note/destroy', [JobApplicationController::class, 'destroyNote'])->name(ViewsConstants::JB . '.application.note.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/getByJob', [JobApplicationController::class, 'getByJob'])->name('get.job.application')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('job-onboard', [JobApplicationController::class, 'jobOnBoard'])->name(ViewsConstants::JB . '.on.board')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::JB_OB . '/create/{id}', [JobApplicationController::class, 'jobBoardCreate'])->name(ViewsConstants::JB . '.on.board.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::JB_OB . '/store/{id}', [JobApplicationController::class, 'jobBoardStore'])->name(ViewsConstants::JB . '.on.board.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::JB_OB . '/edit/{id}', [JobApplicationController::class, 'jobBoardEdit'])->name(ViewsConstants::JB . '.on.board.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::JB_OB . '/update/{id}', [JobApplicationController::class, 'jobBoardUpdate'])->name(ViewsConstants::JB . '.on.board.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::JB_OB . '/delete/{id}', [JobApplicationController::class, 'jobBoardDelete'])->name(ViewsConstants::JB . '.on.board.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::JB_OB . '/convert/{id}', [JobApplicationController::class, 'jobBoardConvert'])->name(ViewsConstants::JB . '.on.board.convert')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::JB_OB . '/convert/{id}', [JobApplicationController::class, 'jobBoardConvertData'])->name(ViewsConstants::JB . '.on.board.convert')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/stage/change', [JobApplicationController::class, 'stageChange'])->name(ViewsConstants::JB . '.application.stage.change')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('custom-question', CustomQuestionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('interview-schedule', InterviewScheduleController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('interview-schedule/create/{id?}', [InterviewScheduleController::class, 'create'])->name(ViewsConstants::ITV_SCD . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('taskboard/{view?}', [ProjectTaskController::class, 'taskBoard'])->name('task_boards.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('taskboard-view', [ProjectTaskController::class, 'taskboardView'])->name('projects.taskboard.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(ViewsConstants::DOC_UP, DocumentUploadController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('transfer', TransferController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::EMP_ATD . '/' . EmployeeAttendanceController::BK_ATD, [EmployeeAttendanceController::class, EmployeeAttendanceController::BK_ATD])->name(ViewsConstants::EMP_ATD . '.' . EmployeeAttendanceController::BK_ATD)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::EMP_ATD . '/' . EmployeeAttendanceController::BK_ATD, [EmployeeAttendanceController::class, EmployeeAttendanceController::BK_ATD_DT])->name(ViewsConstants::EMP_ATD . '.' . EmployeeAttendanceController::BK_ATD)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::EMP_ATD . '/attendance', [EmployeeAttendanceController::class, 'attendance'])->name(ViewsConstants::EMP_ATD . '.attendance')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(ViewsConstants::EMP_ATD . '', EmployeeAttendanceController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(ViewsConstants::LV_TP, LeaveTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::RPT . '/leave', [ReportController::class, 'leave'])->name(ViewsConstants::RPT . '.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::EMP . '/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name(ViewsConstants::RPT . '.employee.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::LV . '/{id}/action', [LeaveController::class, 'action'])->name(ViewsConstants::LV . '.action')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::LV . '/changeaction', [LeaveController::class, 'changeaction'])->name(ViewsConstants::LV . '.changeaction')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::LV . '/jsoncount', [LeaveController::class, 'jsoncount'])->name(ViewsConstants::LV . '.jsoncount')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('leave', LeaveController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::RPT . '-leave', [ReportController::class, 'leave'])->name(ViewsConstants::RPT . '.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::EMP . '/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name(ViewsConstants::RPT . '.employee.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::RPT . '-payroll', [ReportController::class, 'payroll'])->name(ViewsConstants::RPT . '.payroll')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::RPT . '-payroll/getdepartment', [ReportController::class, 'getPayrollDepartment'])->name(ViewsConstants::RPT . '.payroll.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::RPT . '-payroll/getemployee', [ReportController::class, 'getPayrollEmployee'])->name(ViewsConstants::RPT . '.payroll.getemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::RPT . '-monthly-attendance', [ReportController::class, 'monthlyAttendance'])->name(ViewsConstants::RPT . '.monthly.attendance')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::RPT . '/attendance/{month}/{branch}/{department}', [ReportController::class, 'exportCsv'])->name(ViewsConstants::RPT . '.attendance')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //crm report
    Route::get(ViewsConstants::RPT . '-lead', [ReportController::class, 'leadReport'])->name(ViewsConstants::RPT . '.lead')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::RPT . '-deal', [ReportController::class, 'dealReport'])->name(ViewsConstants::RPT . '.deal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //pos report
    Route::get(ViewsConstants::RPT . '-warehouse', [ReportController::class, 'warehouseReport'])->name(ViewsConstants::RPT . '.warehouse')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::RPT . '-daily-purchase', [ReportController::class, 'purchaseDailyReport'])->name(ViewsConstants::RPT . '.daily.purchase')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::RPT . '-monthly-purchase', [ReportController::class, 'purchaseMonthlyReport'])->name(ViewsConstants::RPT . '.monthly.purchase')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::RPT . '-daily-pos', [ReportController::class, 'posDailyReport'])->name(ViewsConstants::RPT . '.daily.pos')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::RPT . '-monthly-pos', [ReportController::class, 'posMonthlyReport'])->name(ViewsConstants::RPT . '.monthly.pos')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(ViewsConstants::RPT . '-pos-vs-purchase', [ReportController::class, 'posVsPurchaseReport'])->name(ViewsConstants::RPT . '.pos.vs.purchase')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // User Module

    Route::get('users/{view?}', [UserController::class, 'index'])->name(DatabaseConstants::TABLE_USERS)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('users-view', [UserController::class, 'filterUserView'])->name('filter.user.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('checkuserexists', [UserController::class, 'checkUserExists'])->name(ViewsConstants::USR . '.exists')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('update.profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::USR . '/info/{id}', [UserController::class, 'userInfo'])->name(ViewsConstants::USR . '.info')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::USR . '/{id}/info/{type}', [UserController::class, 'getProjectTask'])->name(ViewsConstants::USR . '.info.popup')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name(ViewsConstants::USR . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // End User Module

    // Search
    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    // end

    //================================= Project Milestones  ====================================//
    #region
    Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::MLS, [ProjectController::class, 'milestone'])->name(ViewsConstants::ML)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::MLS, [ProjectController::class, ProjectController::ML_STR])->name(ViewsConstants::ML . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/' . ViewsConstants::MLS . '/{id}/edit', [ProjectController::class, ProjectController::ML_ED])->name(ViewsConstants::ML . '.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/' . ViewsConstants::MLS . '/{id}', [ProjectController::class, ProjectController::ML_UPD])->name(ViewsConstants::ML . '.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::PRJ . '/' . ViewsConstants::MLS . '/{id}', [ProjectController::class, ProjectController::ML_DST])->name(ViewsConstants::ML . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/' . ViewsConstants::MLS . '/{id}/show', [ProjectController::class, ProjectController::ML_SHW])->name(ViewsConstants::ML . '.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //Route::delete(
    //    '/'.ViewsConstants::PRJ.'/{id}/users/{uid}', [
    //                                    'as' => ViewsConstants::PRJ.'.'.ViewsConstants::USR.'s.destroy',
    //                                    'uses' => 'ProjectController@userDestroy',
    //                                ]
    //)->middleware(
    //    [
    //        MiddlewaresConstants::AUTH,
    //        MiddlewaresConstants::XSS,
    //    ]
    //);
    // End Milestone
    #endregion

    // Project Module

    Route::get('invite-project-member/{id}', [ProjectController::class, 'inviteMemberView'])->name(ViewsConstants::PRJ . '.invite.member.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('invite-project-user-member', [ProjectController::class, 'inviteProjectUserMember'])->name(ViewsConstants::PRJ . '.invite.user.member')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::delete(ViewsConstants::PRJ . '/{id}/users/{uid}', [ProjectController::class, 'destroyProjectUser'])->name(ViewsConstants::PRJ . '.' . ViewsConstants::USR . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('project/{view?}', [ProjectController::class, 'index'])->name(ViewsConstants::PRJ . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('projects-view', [ProjectController::class, 'filterProjectView'])->name('filter.project.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{id}/store-stages/{slug}', [ProjectController::class, 'storeProjectTaskStages'])->name(ViewsConstants::PRJ . '.stages.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::patch('remove-user-from-project/{project_id}/{user_id}', [ProjectController::class, 'removeUserFromProject'])->name('remove.user.from.project')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('projects-users', [ProjectController::class, 'loadUser'])->name(ViewsConstants::PRJ . '.user')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/{id}/gantt/{duration?}', [ProjectController::class, 'gantt'])->name(ViewsConstants::PRJ . '.gantt')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{id}/gantt', [ProjectController::class, 'ganttPost'])->name(ViewsConstants::PRJ . '.gantt.post')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('projects', ProjectController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // User Permission
    Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::USR . '/{uid}/permission', [ProjectController::class, 'userPermission'])->name(ViewsConstants::PRJ . '.' . ViewsConstants::USR . '.permission')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::USR . '/{uid}/permission', [ProjectController::class, 'userPermissionStore'])->name(ViewsConstants::PRJ . '.' . ViewsConstants::USR . '.' . ViewsConstants::PMS . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // End Project Module

    // Task Module

    Route::get('stage/{id}/tasks', [ProjectTaskController::class, 'getStageTasks'])->name('stage.tasks')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Project Task Module

    Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::TSK, [ProjectTaskController::class, 'index'])->name(ViewsConstants::PRJ_TSK_C . '.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/{pid}/' . ViewsConstants::TSK . '/{sid}', [ProjectTaskController::class, 'create'])->name(ViewsConstants::PRJ_TSK_C . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{pid}/' . ViewsConstants::TSK . '/{sid}', [ProjectTaskController::class, 'store'])->name(ViewsConstants::PRJ_TSK_C . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::TSK . '/{tid}/show', [ProjectTaskController::class, 'show'])->name(ViewsConstants::PRJ_TSK_C . '.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::TSK . '/{tid}/edit', [ProjectTaskController::class, 'edit'])->name(ViewsConstants::PRJ_TSK_C . '.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::TSK . '/update/{tid}', [ProjectTaskController::class, 'update'])->name(ViewsConstants::PRJ_TSK_C . '.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::TSK . '/{tid}', [ProjectTaskController::class, 'destroy'])->name(ViewsConstants::PRJ_TSK_C . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::patch(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::TSK . '/order', [ProjectTaskController::class, 'taskOrderUpdate'])->name('tasks.update.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::patch('update-task-priority-color', [ProjectTaskController::class, 'updateTaskPriorityColor'])->name('update.task.priority.color')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(ViewsConstants::PRJ . '/{id}/comment/{tid}/file', [ProjectTaskController::class, 'commentStoreFile'])->name(ViewsConstants::PRJ_TSK_C . '.comment.store.file')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::PRJ . '/{id}/comment/{tid}/file/{fid}', [ProjectTaskController::class, 'commentDestroyFile'])->name(ViewsConstants::PRJ_TSK_C . '.comment.destroy.file');
    Route::post(ViewsConstants::PRJ . '/{id}/comment/{tid}', [ProjectTaskController::class, 'commentStore'])->name(ViewsConstants::PRJ_TSK_C . '.comment.store');
    Route::delete(ViewsConstants::PRJ . '/{id}/comment/{tid}/{cid}', [ProjectTaskController::class, 'commentDestroy'])->name(ViewsConstants::PRJ_TSK_C . '.comment.destroy');
    Route::post(ViewsConstants::PRJ . '/{id}/checklist/{tid}', [ProjectTaskController::class, 'checklistStore'])->name(ViewsConstants::PRJ_TSK_C . '.checklist.store');
    Route::post(ViewsConstants::PRJ . '/{id}/checklist/update/{cid}', [ProjectTaskController::class, 'checklistUpdate'])->name(ViewsConstants::PRJ_TSK_C . '.checklist.update');
    Route::delete(ViewsConstants::PRJ . '/{id}/checklist/{cid}', [ProjectTaskController::class, 'checklistDestroy'])->name(ViewsConstants::PRJ_TSK_C . '.checklist.destroy');
    Route::post(ViewsConstants::PRJ . '/{id}/change/{tid}/fav', [ProjectTaskController::class, 'changeFav'])->name('change.fav');
    Route::post(ViewsConstants::PRJ . '/{id}/change/{tid}/complete', [ProjectTaskController::class, 'changeCom'])->name('change.complete');
    Route::post(ViewsConstants::PRJ . '/{id}/change/{tid}/progress', [ProjectTaskController::class, 'changeProg'])->name(ViewsConstants::PRJ_TSK_C . 'change.progress');
    Route::get(ViewsConstants::PRJ . '/' . ViewsConstants::TSK . '/{id}/get', [ProjectTaskController::class, 'taskGet'])->name(ViewsConstants::PRJ_TSK_C . '.get')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/calendar/{id}/show', [ProjectTaskController::class, 'calendarShow'])->name('task.calendar.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/calendar/{id}/drag', [ProjectTaskController::class, 'calendarDrag'])->name('task.calendar.drag');
    Route::get('calendar/{task}/{pid?}', [ProjectTaskController::class, 'calendarView'])->name('task.calendar')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('project-task-stages', TaskStageController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project-task-stages/order', [TaskStageController::class, 'order'])->name('project-task-stages.order');

    Route::post('project-task-new-stage', [TaskStageController::class, 'storingValue'])->name('new-task-stage')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // End Task Module

    //================================= Project Expenses  ====================================//
    #region
    Route::get(ViewsConstants::PRJ . '/{id}/expenses', [ExpenseController::class, 'index'])->name(ViewsConstants::PRJ_EXP . '.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/{pid}/' . ViewsConstants::EXP . '/create', [ExpenseController::class, 'create'])->name(ViewsConstants::PRJ_EXP . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{pid}/' . ViewsConstants::EXP . '/store', [ExpenseController::class, 'store'])->name(ViewsConstants::PRJ_EXP . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::EXP . '/{eid}/edit', [ExpenseController::class, 'edit'])->name(ViewsConstants::PRJ_EXP . '.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::EXP . '/{eid}', [ExpenseController::class, 'update'])->name(ViewsConstants::PRJ_EXP . '.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::PRJ . '/{eid}/' . ViewsConstants::EXP . '/', [ExpenseController::class, 'destroy'])->name(ViewsConstants::PRJ_EXP . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // TODO missing method
    Route::get('/expense-list', [ExpenseController::class, 'expenseList'])->name(ViewsConstants::EXP . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    #endregion

    // contract type
    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::resource(ViewsConstants::CTC_TP, ContractTypeController::class);
        }
    );

    // Project Timesheet
    Route::get('append-timesheet-task-html', [TimesheetController::class, 'appendTimesheetTaskHTML'])->name('append.timesheet.task.html')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //    Route::get(ViewsConstants::TMS.'/table-view', [TimesheetController::class, 'filterTimesheetTableView'])->name(ViewsConstants::TMS.'.filters.table.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('timesheet-view', [TimesheetController::class, 'filterTimesheetView'])->name(ViewsConstants::TMS . '.filters.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('timesheet-list', [TimesheetController::class, 'timesheetList'])->name('timesheet.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('timesheet-list-get', [TimesheetController::class, 'timesheetListGet'])->name('timesheet.list.get')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/project/{id}/timesheet', [TimesheetController::class, 'timesheetView'])->name('timesheet.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/project/{id}/timesheet/create', [TimesheetController::class, 'timesheetCreate'])->name('timesheet.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project/timesheet', [TimesheetController::class, 'timesheetStore'])->name('timesheet.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/project/timesheet/{project_id}/edit/{timesheet_id}', [TimesheetController::class, 'timesheetEdit'])->name('timesheet.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('/project/timesheet/update/{timesheet_id}', [TimesheetController::class, 'timesheetUpdate'])->name('timesheet.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::delete('/project/timesheet/{timesheet_id}', [TimesheetController::class, 'timesheetDestroy'])->name('timesheet.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::group(
        //================================= Project Bugs ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
            ],
        ],
        function () {
            Route::resource(ViewsConstants::PRJ_STG, ProjectStagesController::class);
            Route::post(ViewsConstants::PRJ_STG . '/order', [ProjectStagesController::class, 'order'])->name(ViewsConstants::PRJ_STG . '.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post(ViewsConstants::PRJ . '/' . ViewsConstants::BUG . '/kanban/order', [ProjectController::class, ProjectController::BUG_KB_OD])->name(ViewsConstants::PRJ_BUG . '.kanban.order');
            Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/kanban', [ProjectController::class, ProjectController::BUG_KB])->name(ViewsConstants::PRJ_TSK_BUG . '.kanban');
            Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG, [ProjectController::class, 'bug'])->name(ViewsConstants::PRJ_TSK_BUG);
            Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/create', [ProjectController::class, ProjectController::BUG_CRT])->name(ViewsConstants::PRJ_TSK_BUG . '.create');
            Route::post(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/store', [ProjectController::class, ProjectController::BUG_ST])->name(ViewsConstants::PRJ_TSK_BUG . '.store');
            Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/{bid}/edit', [ProjectController::class, ProjectController::BUG_EDT])->name(ViewsConstants::PRJ_TSK_BUG . '.edit');
            Route::post(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/{bid}/update', [ProjectController::class, ProjectController::BUG_UPD])->name(ViewsConstants::PRJ_TSK_BUG . '.update');
            Route::delete(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/{bid}/destroy', [ProjectController::class, ProjectController::BUG_DST])->name(ViewsConstants::PRJ_TSK_BUG . '.destroy');
            Route::get(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/{bid}/show', [ProjectController::class, ProjectController::BUG_SHW])->name(ViewsConstants::PRJ_TSK_BUG . '.show');
            Route::post(ViewsConstants::PRJ . '/{id}/' . ViewsConstants::BUG . '/{bid}/comment', [ProjectController::class, ProjectController::BUG_CMT_STR])->name(ViewsConstants::PRJ_BUG_CM . '.store');
            Route::post(ViewsConstants::PRJ . '/' . ViewsConstants::BUG . '/{bid}/file', [ProjectController::class, ProjectController::BUG_CMT_STR_F])->name(ViewsConstants::PRJ_BUG_CM . '.file.store');
            Route::delete(ViewsConstants::PRJ . '/' . ViewsConstants::BUG . '/comment/{id}', [ProjectController::class, ProjectController::BUG_CMT_DST])->name(ViewsConstants::PRJ_BUG_CM . '.destroy');
            Route::delete(ViewsConstants::PRJ . '/' . ViewsConstants::BUG . '/file/{id}', [ProjectController::class, ProjectController::BUG_CMT_DST_F])->name(ViewsConstants::PRJ_BUG_CM . '.file.destroy');
            Route::resource(ViewsConstants::BUG_STT, BugStatusController::class);
            Route::post(ViewsConstants::BUG_STT . '/order', [BugStatusController::class, 'order'])->name(ViewsConstants::BUG_STT . '.order');
            Route::get(ViewsConstants::BUG_RPT . '/{view?}', [ProjectTaskController::class, ProjectTaskController::ALL_BUG])->name(ViewsConstants::PRJ_BUG . '.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
        }
        #endregion
    );

    Route::post(ViewsConstants::TD . '/create', [UserController::class, UserController::TD_STR])->name(ViewsConstants::TD . '.store')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::TD . '/{id}/update', [UserController::class, UserController::TD_UPD])->name(ViewsConstants::TD . '.update')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::TD . '/{id}/delete', [UserController::class, UserController::TD_DEL])->name(ViewsConstants::TD . '.destroy')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get('/change/mode', [UserController::class, UserController::CHG_MD])->name('change.mode')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get('dashboard-view', [DashboardController::class, DashboardController::FT_VW])->name('dashboard.view')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('dashboard', [DashboardController::class, DashboardController::CL_VW])->name('client.dashboard.view')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // saas
    Route::resource(DatabaseConstants::TABLE_USERS, UserController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);
    Route::resource(DatabaseConstants::TABLE_PLANS, PlanController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);
    Route::resource('coupons', CouponController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    //================================= Form Builder ====================================//

    // Form Builder
    Route::resource('form_builder', FormBuilderController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Form link base view
    Route::get('/form/{code}', [FormBuilderController::class, 'formView'])->name('form.view')->middleware([MiddlewaresConstants::XSS]);
    Route::post('/form_view_store', [FormBuilderController::class, 'formViewStore'])->name('form.view.store')->middleware([MiddlewaresConstants::XSS]);

    // Form Field
    Route::get('/form_builder/{id}/field', [FormBuilderController::class, 'fieldCreate'])->name('form.field.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/form_builder/{id}/field', [FormBuilderController::class, 'fieldStore'])->name('form.field.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/form_builder/{id}/field/{fid}/show', [FormBuilderController::class, 'fieldShow'])->name('form.field.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/form_builder/{id}/field/{fid}/edit', [FormBuilderController::class, 'fieldEdit'])->name('form.field.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldUpdate'])->name('form.field.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldDestroy'])->name('form.field.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Form Response
    Route::get('/form_response/{id}', [FormBuilderController::class, 'viewResponse'])->name('form.response')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/response/{id}', [FormBuilderController::class, 'responseDetail'])->name('response.detail')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Form Field Bind
    Route::get('/form_field/{id}', [FormBuilderController::class, 'formFieldBind'])->name('form.field.bind')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/form_field_store/{id}}', [FormBuilderController::class, 'bindStore'])->name('form.bind.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // contract

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get('contract/{id}/description', [ContractController::class, 'description'])->name(ViewsConstants::CTC . '.description');
            Route::get('contract/grid', [ContractController::class, 'grid'])->name(ViewsConstants::CTC . '.grid');
            Route::resource('contract', ContractController::class);
        }
    );
    Route::post('/contract/{id}/file', [ContractController::class, 'fileUpload'])->name(ViewsConstants::CTC . '.file.upload')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('contract/pdf/{id}', [ContractController::class, 'pdfFromContract'])->name(ViewsConstants::CTC . '.download.pdf')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('contract/{id}/get_contract', [ContractController::class, 'printContract'])->name(ViewsConstants::CTC . '.get')->middleware([MiddlewaresConstants::AUTH]);
    Route::post('/contract_status_edit/{id}', [ContractController::class, 'contractStatusEdit'])->name(ViewsConstants::CTC . '.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('contract/{id}/contract_description', [ContractController::class, 'contractDescriptionStore'])->name(ViewsConstants::CTC . '.contract_description.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('/contract/{id}/file/{fid}', [ContractController::class, 'fileDownload'])->name(ViewsConstants::CTC . '.file.download')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/contract/{id}/file/delete/{fid}', [ContractController::class, 'fileDelete'])->name(ViewsConstants::CTC . '.file.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/contract/copy/{id}', [ContractController::class, 'copyContract'])->name(ViewsConstants::CTC . '.copy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/contract/copy/store', [ContractController::class, 'copyContractStore'])->name(ViewsConstants::CTC . '.copy.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/contract/{id}/mail', [ContractController::class, 'sendmailContract'])->name(ViewsConstants::CTC . '.send.mail');
    Route::get('/signature/{id}', [ContractController::class, 'signature'])->name(ViewsConstants::CTC . '.signature')->middleware([MiddlewaresConstants::AUTH]);
    Route::post('/signature-store', [ContractController::class, 'signatureStore'])->name(ViewsConstants::CTC . '.signature.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/contract/{id}/comment', [ContractController::class, 'commentStore'])->name(ViewsConstants::CTC . '.comment.store');
    Route::post('/contract/{id}/notes', [ContractController::class, 'noteStore'])->name(ViewsConstants::CTC . '.note.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete('/contract/{id}/notes', [ContractController::class, 'noteDestroy'])->name(ViewsConstants::CTC . '.note.destroy')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete('/contract/{id}/comment', [ContractController::class, 'commentDestroy'])->name(ViewsConstants::CTC . '.comment.destroy');
    Route::get('get-projects/{client_id}', [ContractController::class, 'clientByProject'])->name(ViewsConstants::PRJ . '.by.user.id')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // client wise project show in modal

    Route::any('/contract/clients/select/{bid}', [ContractController::class, 'clientwiseproject'])->name(ViewsConstants::CTC . '.clients.select');

    // copy contract

    Route::get('/contract/copy/{id}', [ContractController::class, 'copycontract'])->name(ViewsConstants::CTC . '.copy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('contract/copy/store', [ContractController::class, 'copycontractstore'])->name(ViewsConstants::CTC . '.copy.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Custom Landing Page

    //    Route::get('/landingpage', [LandingPageSectionController::class, 'index'])->name('custom_landing_page.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //    Route::get('/LandingPage/show/{id}', [LandingPageSectionController::class, 'show']);
    //
    //    Route::post('/LandingPage/setConetent', [LandingPageSectionController::class, 'setConetent'])->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //
    //
    //    Route::get(
    //        '/get_landing_page_section/{name}', function ($name) {
    //        $plans = \DB::table(DatabaseConstants::TABLE_PLANS)->get();
    //
    //        return view('custom_landing_page.' . $name, compact(DatabaseConstants::TABLE_PLANS));
    //    }
    //    );
    //
    //    Route::post('/LandingPage/removeSection/{id}', [LandingPageSectionController::class, 'removeSection'])->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //    Route::post('/LandingPage/setOrder', [LandingPageSectionController::class, 'setOrder'])->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //    Route::post('/LandingPage/copySection', [LandingPageSectionController::class, 'copySection'])->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Plan Payment Gateways
    Route::post('plan-pay-with-bank', [BankTransferPaymentController::class, 'planPayWithBank'])->name(ViewsConstants::PLN . '.pay.with.bank')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::any('/payment/initiate', [BenefitPaymentController::class, 'initiatePayment'])->name(ViewsConstants::PLN . '.pay.with.benefit');
    Route::any('callBack', [BenefitPaymentController::class, 'callBack'])->name('benefit.callback');

    Route::post('cashfree/payments/store', [CashfreeController::class, 'cashfreePaymentStore'])->name(ViewsConstants::PLN . '.pay.with.cashfree');
    Route::any('cashfree/payments/success', [CashfreeController::class, 'cashfreePaymentSuccess'])->name('cashfree.payment.success');

    //plan-order
    Route::post('order/{id}/changeaction', [BankTransferPaymentController::class, 'changeStatus'])->name('order.change.status');
    Route::delete('order/{id}', [BankTransferPaymentController::class, 'orderDestroy'])->name('order.destroy');
    Route::get('order/{id}/action', [BankTransferPaymentController::class, 'action'])->name('order.action');

    //================================= Supports ====================================//
    #region
    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(ViewsConstants::SPT . '/{id}/reply', [SupportController::class, 'reply'])->name(ViewsConstants::SPT . '.reply');
            Route::post(ViewsConstants::SPT . '/{id}/reply', [SupportController::class, 'replyAnswer'])->name(ViewsConstants::SPT . '.reply.answer');
            Route::get(ViewsConstants::SPT . '/grid', [SupportController::class, 'grid'])->name(ViewsConstants::SPT . '.grid');
            Route::resource(ViewsConstants::SPT, SupportController::class);
        }
    );
    #endregion

    Route::resource(ViewsConstants::CPT, CompetenciesController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //================================= Perfomance Types ====================================//
    #region
    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::resource(ViewsConstants::PFM_TP, PerformanceTypeController::class);
        }
    );
    #endregion

    //================================= Plan Requests ====================================//
    Route::get(ViewsConstants::PLN_RQ, [PlanRequestController::class, 'index'])->name(ViewsConstants::PLN_RQ . '.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_frequency/{id}', [PlanRequestController::class, PlanRequestController::RQ_VW])->name(ViewsConstants::PLN_RQ . '.request.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_send/{id}', [PlanRequestController::class, PlanRequestController::USR_RQ])->name(ViewsConstants::PLN_RQ . '.request.send')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_response/{id}/{response}', [PlanRequestController::class, PlanRequestController::AC_RQ])->name(ViewsConstants::PLN_RQ . '.request.response')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_cancel/{id}', [PlanRequestController::class, PlanRequestController::CC_RQ])->name(ViewsConstants::PLN_RQ . '.request.cancel')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //QR Code Module

    // Import/Export Data Route

    Route::get('export/productservice', [ProductServiceController::class, 'export'])->name(ViewsConstants::PRD_SV . '.export');
    // Route::get('import'.ViewsConstants::PRD_SV.//file', [ProductServiceController::class, 'importFile'])->name(ViewsConstants::PRD_SV . '.file.import');
    Route::post('import/productservice', [ProductServiceController::class, 'import'])->name(ViewsConstants::PRD_SV . '.import');
    Route::get('export/customer', [CustomerController::class, 'export'])->name(ViewsConstants::CST . '.export');
    Route::get('import/customer/file', [CustomerController::class, 'importFile'])->name(ViewsConstants::CST . '.file.import');
    Route::post('import/customer', [CustomerController::class, 'import'])->name(ViewsConstants::CST . '.import');
    Route::get('export/vendor', [VendorController::class, 'export'])->name('vendor.export');
    Route::get('import/vendor/file', [VendorController::class, 'importFile'])->name('vendor.file.import');
    Route::post('import/vendor', [VendorController::class, 'import'])->name('vendor.import');
    Route::get('export/invoice', [InvoiceController::class, 'export'])->name(ViewsConstants::INV . '.export');
    Route::get('export/proposal', [ProposalController::class, 'export'])->name(ViewsConstants::PPS . '.export');
    Route::get('export/bill', [BillController::class, 'export'])->name(ViewsConstants::BIL . '.export');

    Route::get('export/employee', [EmployeeController::class, 'export'])->name(ViewsConstants::EMP . '.export');
    Route::get('importViewsConstants::EMP.//file', [EmployeeController::class, 'importFile'])->name(ViewsConstants::EMP . '.file.import');
    Route::post('import/employee', [EmployeeController::class, 'import'])->name(ViewsConstants::EMP . '.import');

    Route::get('import/attendance/file', [EmployeeAttendanceController::class, 'importFile'])->name('attendance.file.import');
    Route::post('import/attendance', [EmployeeAttendanceController::class, 'import'])->name('attendance.import');

    Route::get('export/transaction', [TransactionController::class, 'export'])->name('transaction.export');
    Route::get('export/accountstatement', [ReportController::class, 'export'])->name('accountstatement.export');
    Route::get('export/productstock', [ReportController::class, 'stock_export'])->name('productstock.export');
    Route::get('export/payroll', [ReportController::class, 'PayrollReportExport'])->name('payroll.export');
    Route::get('export/leave', [ReportController::class, 'LeaveReportExport'])->name(ViewsConstants::LV . '.export');

    Route::post('export/payslip', [PayslipController::class, 'export'])->name(ViewsConstants::PY_SLP . '.export');

    // Time-Tracker
    Route::post('stop-tracker', [DashboardController::class, DashboardController::STP_TRK])->name('stop.tracker')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('time-tracker', [TimeTrackerController::class, 'index'])->name('time.tracker')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('tracker/{tid}/destroy', [TimeTrackerController::class, 'Destroy'])->name('time_trackers.destroy');
    Route::post('tracker/image-view', [TimeTrackerController::class, 'getTrackerImages'])->name('time_trackers.image.view');
    Route::delete('tracker/image-remove', [TimeTrackerController::class, 'removeTrackerImages'])->name('time_trackers.image.remove');
    Route::get(ViewsConstants::PRJ . '/time-tracker/{id}', [ProjectController::class, 'tracker'])->name(ViewsConstants::PRJ . '.time.tracker')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Zoom Meeting
    Route::resource('zoom-meeting', ZoomMeetingController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('/zoom-meeting/projects/select/{bid}', [ZoomMeetingController::class, 'projectwiseuser'])->name('zoom-meeting.projects.select');
    Route::get('zoom-meeting-calendar', [ZoomMeetingController::class, 'calendar'])->name('zoom-meeting.calendar')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //POS System

    Route::resource(DatabaseConstants::TABLE_WHS, WarehouseController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);
    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/items', [PurchaseController::class, 'items'])->name(ViewsConstants::PRC . '.items');
            Route::resource(DatabaseConstants::TABLE_PURCHASES, PurchaseController::class);

            //    Route::get('/'.ViewsConstants::BIL.'{id}/', 'PurchaseController@purchaseLink')->name(ViewsConstants::PRC.'.link.copy');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment', [PurchaseController::class, 'payment'])
                ->name(ViewsConstants::PRC . '.payment');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment', [PurchaseController::class, 'createPayment'])
                ->name(ViewsConstants::PRC . '.payment');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment/{pid}/destroy', [
                PurchaseController::class,
                'paymentDestroy'
            ])->name(ViewsConstants::PRC . '.payment.destroy');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/product/destroy', [
                PurchaseController::class,
                'productDestroy'
            ])->name(ViewsConstants::PRC . '.product.destroy');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/vendor', [PurchaseController::class, 'vendor'])
                ->name(ViewsConstants::PRC . '.vendor');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/product', [PurchaseController::class, 'product'])
                ->name(ViewsConstants::PRC . '.product');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/create/{cid}', [PurchaseController::class, 'create'])
                ->name(ViewsConstants::PRC . '.create');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/sent', [PurchaseController::class, 'sent'])
                ->name(ViewsConstants::PRC . '.sent');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/resent', [PurchaseController::class, 'resent'])
                ->name(ViewsConstants::PRC . '.resent');
        }

    );
    Route::get('pos-print-setting', [SystemController::class, 'posPrintIndex'])->name(ViewsConstants::POS . '.print.setting')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(DatabaseConstants::TABLE_PURCHASES . '/preview/{template}/{color}', [PurchaseController::class, PurchaseController::PV_PRC])
        ->name(ViewsConstants::PRC . '.preview')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::POS . '/preview/{template}/{color}', [PosController::class, PosController::PV_POS])->name(ViewsConstants::POS . '.preview')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(ViewsConstants::PRC . '/templates/settings', [PurchaseController::class, PurchaseController::SV_PCR_TMP_STG])
        ->name(ViewsConstants::PRC_TMP . 'settings');
    Route::post('/pos/template/setting', [PosController::class, PosController::SV_POS_TMP])
        ->name(ViewsConstants::PRC_TMP . 'settings');

    Route::get(DatabaseConstants::TABLE_PURCHASES . '/pdf/{id}', [PurchaseController::class, 'purchase'])
        ->name(DatabaseConstants::TABLE_PURCHASES . '.pdf')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get(ViewsConstants::POS . '/pdf/{id}', [PosController::class, 'pos'])->name(ViewsConstants::POS . '.pdf')->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);
    Route::get(ViewsConstants::POS . '/data/store', [PosController::class, 'store'])->name(ViewsConstants::POS . '.data.store')->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    //for pos print
    Route::get('printview/pos', [PosController::class, PosController::PRT_VW])->name(ViewsConstants::POS . '.printview')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource(DatabaseConstants::TABLE_POS, PosController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    Route::get('product-categories', [ProductServiceCategoryController::class, 'getProductCategories'])
        ->name('product.categories')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('add-to-cart/{id}/{session}', [ProductServiceController::class, 'addToCart'])->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS
    ]);
    Route::patch('update-cart', [ProductServiceController::class, 'updateCart'])->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS
    ]);
    Route::delete('remove-from-cart', [ProductServiceController::class, 'removeFromCart'])->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS
    ]);

    Route::get('name-search-products', [ProductServiceCategoryController::class, 'searchProductsByName'])->name('name.search.products')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('search-products', [ProductServiceController::class, 'searchProducts'])->name('search.products')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any(ViewsConstants::RPT . '/pos', [PosController::class, 'report'])->name(ViewsConstants::POS . '.report')->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS
    ]);

    //warehouse-transfer
    Route::resource('warehouse-transfers', WarehouseTransferController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);
    Route::post('warehouse-transfers/getproduct', [WarehouseTransferController::class, 'getproduct'])->name('warehouse-transfer.getproduct')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('warehouse-transfers/getquantity', [WarehouseTransferController::class, 'getquantity'])
        ->name('warehouse-transfer.getquantity')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //pos barcode
    Route::get('barcode/pos', [PosController::class, 'barcode'])->name(ViewsConstants::POS . '.barcode')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::SET . '/pos', [PosController::class, 'setting'])->name(ViewsConstants::POS . '.setting')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('barcode/settings', [PosController::class, 'BarcodesettingStore'])->name('barcode.setting');
    Route::get('print/pos', [PosController::class, 'printBarcode'])->name(ViewsConstants::POS . '.print')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::POS . '/getproduct', [PosController::class, 'getproduct'])->name(ViewsConstants::POS . '.getproduct')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('pos-receipt', [PosController::class, 'receipt'])->name(ViewsConstants::POS . '.receipt')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/cartdiscount', [PosController::class, 'cartdiscount'])->name('cartdiscount')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Storage Setting

    Route::post('storage-settings', [SystemController::class, 'storageSettingStore'])->name(ViewsConstants::SET . '.storage.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //appricalStar

    Route::post(ViewsConstants::APR, [AppraisalController::class, 'empByStar'])->name('empByStar')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::APR . '1', [AppraisalController::class, 'empByStar1'])->name('empByStar1')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/getemployee', [AppraisalController::class, 'getEmployee'])->name('getemployee');

    //================================= Offer Letters ====================================//
    #region
    Route::post(ViewsConstants::SET . '/offer-letter/{lang?}', [SystemController::class, SystemController::OF_LTR_UPD])->name('offer_letter.update');
    Route::get(ViewsConstants::SET . '/offer-letter', [SystemController::class, SystemController::CP])->name(ViewsConstants::SET . '.offer_letter.language');
    Route::get(ViewsConstants::JB_OB . '/pdf/{id}', [JobApplicationController::class, JobApplicationController::OFL_PDF])->name('offer_letter.download.pdf');
    Route::get(ViewsConstants::JB_OB . '/doc/{id}', [JobApplicationController::class, JobApplicationController::OFL_DC])->name('offer_letter.download.doc');
    #endregion

    //================================= Joining Letters ====================================//
    #region
    Route::post(ViewsConstants::SET . '/joining-letter/{lang?}', [SystemController::class, SystemController::JN_LTR_UPD])->name('joining_letter.update');
    Route::get(ViewsConstants::SET . '/joining-letter', [SystemController::class, SystemController::CP])->name(ViewsConstants::SET . 'joining_letter.language');
    Route::get(ViewsConstants::EMP . '/pdf/{id}', [EmployeeController::class, EmployeeController::JNL_PDF])->name('joining_letter.download.pdf');
    Route::get(ViewsConstants::EMP . '/doc/{id}', [EmployeeController::class, EmployeeController::JNL_DOC])->name('joining_letter.download.doc');
    #endregion

    //================================= Experience Certificates ====================================//
    #region
    Route::post(ViewsConstants::SET . '/exp/{lang?}', [SystemController::class, SystemController::EXP_CT_UPD])->name('experience_certificate.update');
    Route::get(ViewsConstants::SET . '/exp', [SystemController::class, SystemController::CP])->name(ViewsConstants::SET . '.experience_certificate.language');
    Route::get(ViewsConstants::EMP . '/exp-pdf/{id}', [EmployeeController::class, EmployeeController::EC_PDF])->name('exp.download.pdf');
    Route::get(ViewsConstants::EMP . '/exp-doc/{id}', [EmployeeController::class, EmployeeController::EC_DOC])->name('exp.download.doc');
    #endregion

    //================================= Nocs ====================================//
    #region
    Route::post(ViewsConstants::SET . '/noc/{lang?}', [SystemController::class, SystemController::NOC_UPD])->name('noc.update');
    Route::get(ViewsConstants::SET . '/noc', [SystemController::class, SystemController::CP])->name(ViewsConstants::SET . '.noc.language');
    Route::get(ViewsConstants::EMP . '/noc-pdf/{id}', [EmployeeController::class, EmployeeController::NOC_PDF])->name('noc.download.pdf');
    Route::get(ViewsConstants::EMP . '/noc-doc/{id}', [EmployeeController::class, EmployeeController::NOC_DOC])->name('noc.download.doc');
    #endregion


    //Project Reports

    Route::resource('/project_report', ProjectReportController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project_report_data', [ProjectReportController::class, 'ajax_data'])->name(ViewsConstants::PRJ . '.ajax')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project_report/tasks/{id}', [ProjectReportController::class, 'ajax_tasks_report'])->name('tasks.report.ajaxdata')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('export/task_report/{id}', [ProjectReportController::class, 'export'])->name('project_report.export');

    //project copy module
    Route::get('/project/copy/{id}', [ProjectController::class, 'copyproject'])->name(ViewsConstants::PRJ . '.copy')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project/copy/store/{id}', [ProjectController::class, 'copyprojectstore'])->name(ViewsConstants::PRJ . '.copy.store')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Google Calendar
    Route::any('event/get_event_data', [EventController::class, 'get_event_data'])->name('event.get_event_data')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(ViewsConstants::SET . '/google-calendar', [SystemController::class, 'saveGooglecalendarSettings'])->name(ViewsConstants::SET . 'google.calendar');
    Route::any('holiday/get_holiday_data', [HolidayController::class, 'get_holiday_data'])->name('holiday.get_holiday_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('interview-schedule/get_interview_data', [InterviewScheduleController::class, 'get_interview_data'])->name('holiday.get_interview_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('calendar/get_task_data', [ProjectTaskController::class, 'get_task_data'])->name('task.calendar.get_task_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('zoom-meeting/get_zoom_meeting_data', [ZoomMeetingController::class, 'get_zoom_meeting_data'])->name('zoom-meeting.get_zoom_meeting_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::any('meeting/get_meeting_data', [MeetingController::class, 'get_meeting_data'])->name('meeting.get_meeting_data')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('meeting-calendar', [MeetingController::class, 'calendar'])->name('meeting.calendar')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::any('event/get_dashboard_event_data', [EventController::class, 'get_dashboard_event_data'])->name('event.get_dashboard_event_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //branch wise department get in attendance report
    Route::post(ViewsConstants::RPT . '-monthly-attendance/getdepartment', [ReportController::class, 'getdepartment'])->name(ViewsConstants::RPT . '.attendance.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::RPT . '-monthly-attendance/getemployee', [ReportController::class, 'getemployee'])->name(ViewsConstants::RPT . '.attendance.getemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //shared project & copy link
    Route::any(ViewsConstants::PRJ . '/copy/link/{id}', [ProjectController::class, ProjectController::CP_LNK_ST])->name(ViewsConstants::PRJ . '.copy.link');
    Route::any(ViewsConstants::PRJ . '/{id}/setting-create', [ProjectController::class, ProjectController::CP_LNK_ST_CRT])->name(ViewsConstants::PRJ . '.copy_link.setting.create');
    // TODO missing method
    Route::get('share-project/{lang?}', [ProjectController::class, 'shareProject'])->name('share.project');

    //User Log
    Route::get('/userlogs', [UserController::class, 'userLog'])->name(ViewsConstants::USR . '.' . ViewsConstants::USR . 'log')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('userlogs/{id}', [UserController::class, 'userLogView'])->name(ViewsConstants::USR . '.' . ViewsConstants::USR . 'logview')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('userlogs/{id}', [UserController::class, 'userLogDestroy'])->name(ViewsConstants::USR . '.' . ViewsConstants::USR . 'logdestroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //notification Template
    Route::get('notification_templates/{id?}/{lang?}', [NotificationTemplatesController::class, 'index'])->name('notification_templates.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('notification-templates', NotificationTemplatesController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Proposal/Invoice/'.ViewsConstants::BIL.'Purchase/POS - footer notes
    Route::post('system-settings/note', [SystemController::class, 'footerNoteStore'])->name(ViewsConstants::SYS . '.settings.footernote')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //AI module
    Route::post('chatgpt-settings', [SystemController::class, 'chatgptSetting'])->name(ViewsConstants::SET . '.chatgpt.settings');
    Route::get('generate/{template_name}', [AiTemplateController::class, 'create'])->name('generate');
    Route::post('generate/keywords/{id}', [AiTemplateController::class, 'getKeywords'])->name('generate.keywords');
    Route::post('generate/response', [AiTemplateController::class, 'AiGenerate'])->name('generate.response');
    Route::get('grammar/{template}', [AiTemplateController::class, 'grammar'])->name('grammar')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('grammar/response', [AiTemplateController::class, 'grammarProcess'])->name('grammar.response')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //================================= IP Controls ====================================//
    #region
    Route::get(ViewsConstants::SYS . '/create/ip', [SystemController::class, SystemController::CR_IP])->name(ViewsConstants::SYS . '.ip.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::SYS . '/create/ip', [SystemController::class, SystemController::STR_IP])->name(ViewsConstants::SYS . '.ip.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(ViewsConstants::SYS . '/edit/ip/{id}', [SystemController::class, SystemController::ED_IP])->name(ViewsConstants::SYS . '.ip.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(ViewsConstants::SYS . '/edit/ip/{id}', [SystemController::class, SystemController::UPD_IP])->name(ViewsConstants::SYS . '.ip.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(ViewsConstants::SYS . '/destroy/ip/{id}', [SystemController::class, SystemController::DST_IP])->name(ViewsConstants::SYS . '.ip.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    #endregion

    //lang enable / disable
    Route::post('disable-language', [LanguageController::class, LanguageController::DSB_LNG])->name('language.disable')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Expense Module
    Route::get('expense/pdf/{id}', [ExpenseController::class, 'expense'])->name(ViewsConstants::EXP . '.pdf')->middleware([MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get('expense/index', [ExpenseController::class, 'index'])->name(ViewsConstants::EXP . '.index');
            Route::any('expense/customer', [ExpenseController::class, 'customer'])->name(ViewsConstants::EXP . '.customer');
            Route::post('expense/vendor', [ExpenseController::class, 'vendor'])->name(ViewsConstants::EXP . '.vendor');
            Route::post('expense/employee', [ExpenseController::class, 'employee'])->name(ViewsConstants::EXP . '.employee');

            Route::post('expense/product/destroy', [ExpenseController::class, 'productDestroy'])->name(ViewsConstants::EXP . '.product.destroy');

            Route::post('expense/product', [ExpenseController::class, 'product'])->name(ViewsConstants::EXP . '.product');
            Route::get('expense/{id}/payment', [ExpenseController::class, 'payment'])->name(ViewsConstants::EXP . '.payment');
            Route::get('expense/items', [ExpenseController::class, 'items'])->name(ViewsConstants::EXP . '.items');

            Route::resource(ViewsConstants::EXP, ExpenseController::class);
            Route::get('expense/create/{cid}', [ExpenseController::class, 'create'])->name(ViewsConstants::EXP . '.create');
        }
    );
});

Route::any('/cookie-consent', [SystemController::class, 'CookieConsent'])->name('cookie-consent');


    // Route::post('{id}/pay-with-paypal', [PaypalController::class, 'customerPayWithPaypal'])->name(ViewsConstants::CST.'.pay.with.paypal');
    // Route::get('{id}/get-payment-status/{amount}', [PaypalController::class, 'customerGetPaymentStatus'])->name(ViewsConstants::CST.'.get.payment.status')
    //     ->middleware([MiddlewaresConstants::XSS]);
    // Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name(ViewsConstants::PLN.'.pay.with.paypal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    // Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name(ViewsConstants::PLN.'.get.payment.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);


// AUT, XSS, REV GROUP

    //    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name(ViewsConstants::PLN.'.pay.with.paypal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    //    Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name(ViewsConstants::PLN.'.get.payment.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

//     Route::post('invoice-with-aamarpay', [AamarpayController::class, 'invoicepaywithaamarpay'])->name(ViewsConstants::CST.'.pay.with.aamarpay');
//     Route::any('aamarpay-invoice/success/{data}', [AamarpayController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::INV . '.pay.aamarpay.success');
    
//     Route::post('/customer-pay-with-coingate', [CoingatePaymentController::class, 'customerPayWithCoingate'])->name(ViewsConstants::CST.'.pay.with.coingate')->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/coingate/{invoice}/{amount}', [CoingatePaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.coingate');
    
//     Route::post('/customer-pay-with-paytm', [PaytmPaymentController::class, 'customerPayWithPaytm'])->name(ViewsConstants::CST.'.pay.with.paytm')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::post('/customer/paytm/{invoice}/{amount}', [PaytmPaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.paytm');
    
//     Route::post('/customer-pay-with-flutterwave', [FlutterwavePaymentController::class, 'customerPayWithFlutterwave'])->name(ViewsConstants::CST.'.pay.with.flutterwave')->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/flutterwave/{txref}/{invoice_id}', [FlutterwavePaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.flutterwave');
    
//     Route::post('/customer-pay-with-razorpay', [RazorpayPaymentController::class, 'customerPayWithRazorpay'])->name(ViewsConstants::CST.'.pay.with.razorpay')->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/razorpay/{txref}/{invoice_id}', [RazorpayPaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.razorpay');
    
//     Route::post('/customer-pay-with-mercado', [MercadoPaymentController::class, 'customerPayWithMercado'])->name(ViewsConstants::CST.'.pay.with.mercado')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/mercado/{invoice}', [MercadoPaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.mercado');
    
//     Route::post('/customer-pay-with-mollie', [MolliePaymentController::class, 'customerPayWithMollie'])->name(ViewsConstants::CST.'.pay.with.mollie')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/mollie/{invoice}/{amount}', [MolliePaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.mollie');
    
//     Route::post('/customer-pay-with-skrill', [SkrillPaymentController::class, 'customerPayWithSkrill'])->name(ViewsConstants::CST.'.pay.with.skrill')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/skrill/{invoice}/{amount}', [SkrillPaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.skrill');
    
//     Route::post('/paymentwall', [PaymentWallPaymentController::class, 'invoicepaymentwall'])->name(ViewsConstants::INV . '.paymentwallpayment')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::post('/invoice-pay-with-paymentwall/{invoice}', [PaymentWallPaymentController::class, 'invoicePayWithPaymentwall'])
//         ->name(ViewsConstants::INV . '.pay.with.paymentwall')->middleware([MiddlewaresConstants::XSS]);
//     Route::get(ViewsConstants::INV.'/{flag}/{invoice}', [PaymentWallPaymentController::class, 'invoiceerror'])->name('error.invoice.show');
    
//     Route::post('/customer-pay-with-toyyibpay', [ToyyibpayController::class, 'invoicepaywithtoyyibpay'])->name(ViewsConstants::CST.'.pay.with.toyyibpay');
//     Route::get('/customer/toyyibpay/{invoice}/{amount}', [ToyyibpayController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.toyyibpay');
    
//     Route::post('invoice-with-payfast', [PayFastController::class, 'invoicePayWithPayFast'])->name(ViewsConstants::INV . '.with.payfast');
//     Route::get('invoice-payfast-status/{success}', [PayFastController::class, 'invoicepayfaststatus'])->name(ViewsConstants::INV . '.payfast.status');
    
//     Route::post('/customer-pay-with-iyzipay', [IyziPayController::class, 'invoicepaywithiyzipay'])->name(ViewsConstants::CST.'.pay.with.iyzipay');
//     Route::post('iyzipay/callback/{invoice}/{amount}', [IyzipayController::class, 'getInvoiceiyzipayCallback'])
//         ->name('iyzipay.invoicepayment.callback');
    
//     Route::post('/customer-pay-with-sspay', [SspayController::class, 'invoicepaywithsspaypay'])->name(ViewsConstants::CST.'.pay.with.sspay');
//     Route::get('/customer/sspay/{invoice}/{amount}', [SspayController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.sspay');
    
//     Route::post('/invoice-pay-with-paytab', [PaytabController::class, 'invoicePayWithpaytab'])->name(ViewsConstants::CST.'.pay.with.paytab');
//     Route::any('/invoice-paytab-success/{invoice}', [PaytabController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::INV . '.paytab.success');
    
//     Route::post('/invoice-with-paytr', [PaytrController::class, 'invoicepaywithpaytr'])->name(ViewsConstants::CST.'.pay.with.paytr');
//     Route::get('/invoice/paytr/status', [PaytrController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::INV . '.paytr');
    
//     Route::post('invoice-with-yookassa/', [YooKassaController::class, 'invoicePayWithYookassa'])->name(ViewsConstants::CST.'.with.yookassa');
//     Route::any('invoice-yookassa-status/', [YooKassaController::class, 'getInvociePaymentStatus'])->name(ViewsConstants::INV . '.yookassa.status');
    
//     Route::any('invoice-with-midtrans/', [MidtransPaymentController::class, 'invoicePayWithMidtrans'])->name(ViewsConstants::CST.'.with.midtrans');
//     Route::any('invoice-midtrans-status/', [MidtransPaymentController::class, 'getInvociePaymentStatus'])->name(ViewsConstants::INV . '.midtrans.status');
    
//     Route::any('/invoice-with-xendit', [XenditPaymentController::class, 'invoicePayWithXendit'])->name(ViewsConstants::CST.'.with.xendit');
//     Route::any('/invoice-xendit-status', [XenditPaymentController::class, 'getInvociePaymentStatus'])->name(ViewsConstants::INV . '.xendit.status');
// // Invoice Payment Gateways
// Route::post('customer/{id}/payment', [StripePaymentController::class, 'addpayment'])->name(ViewsConstants::CST.'.payment');

// Route::group(
//     [
//         'middleware' => [
//             MiddlewaresConstants::AUTH,
//             MiddlewaresConstants::XSS,
//             MiddlewaresConstants::REV,
//         ],
//     ],
//     function () {
//         Route::get('order', [StripePaymentController::class, 'index'])->name('order.index');
//         Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
//         Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
//     }
// );

// Route::post('/customer-pay-with-paystack', [PaystackPaymentController::class, 'customerPayWithPaystack'])->name(ViewsConstants::CST.'.pay.with.paystack')->middleware([MiddlewaresConstants::XSS]);
// Route::get('/customer/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvoicePaymentStatus'])->name(ViewsConstants::CST.'.paystack');

    // Orders

    // Route::group(
    //     [
    //         'middleware' => [
    //             MiddlewaresConstants::AUTH,
    //             MiddlewaresConstants::XSS,
    //             MiddlewaresConstants::REV,
    //         ],
    //     ],
    //     function () {
    //         Route::get('/orders', [StripePaymentController::class, 'index'])->name('order.index');
    //         Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
    //         Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
    //     }
    // );

    // Route::post('/aamarpay/payment', [AamarpayController::class, 'pay'])->name(ViewsConstants::PLN.'.pay.with.aamarpay');
    // Route::any('/aamarpay/success/{data}', [AamarpayController::class, 'aamarpaysuccess'])->name('pay.aamarpay.success');

    // Route::post('/paytr/payment/{plan_id}', [PaytrController::class, 'PlanpayWithPaytr'])->name(ViewsConstants::PLN.'.pay.with.paytr');
    // Route::get('/paytr/sussess/', [PaytrController::class, 'paytrsuccess'])->name('pay.paytr.success');

    // Route::post('/plan/yookassa/payment', [YooKassaController::class, 'planPayWithYooKassa'])->name(ViewsConstants::PLN.'.pay.with.yookassa');
    // Route::get('/plan/yookassa/{plan}', [YooKassaController::class, 'planGetYooKassaStatus'])->name(ViewsConstants::PLN.'.yookassa.status');

    // Route::any('/midtrans', [MidtransPaymentController::class, 'planPayWithMidtrans'])->name(ViewsConstants::PLN.'.pay.with.midtrans');
    // Route::any('/midtrans/callback', [MidtransPaymentController::class, 'planGetMidtransStatus'])->name(ViewsConstants::PLN.'.get.midtrans.status');

    // Route::any('/xendit/payment', [XenditPaymentController::class, 'planPayWithXendit'])->name(ViewsConstants::PLN.'.pay.with.xendit');
    // Route::any('/xendit/payment/status', [XenditPaymentController::class, 'planGetXenditStatus'])->name(ViewsConstants::PLN.'.xendit.status');

    // Route::post('/plan-pay-with-flutterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name(ViewsConstants::PLN.'.pay.with.flutterwave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/flutterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.flutterwave');

    // Route::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->name(ViewsConstants::PLN.'.pay.with.razorpay')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/razorpay/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.razorpay');

    // Route::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->name(ViewsConstants::PLN.'.pay.with.paytm')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::post('/plan/paytm/{plan}', [PaytmPaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.paytm');

    // Route::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->name(ViewsConstants::PLN.'.pay.with.mercado')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/mercado/{plan}/{amount}', [MercadoPaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.mercado');

    // Route::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name(ViewsConstants::PLN.'.pay.with.mollie')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.mollie');

    // Route::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name(ViewsConstants::PLN.'.pay.with.skrill')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/skrill/{plan}', [SkrillPaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.skrill');

    // Route::post('/plan-pay-with-coingate', [CoingatePaymentController::class, 'planPayWithCoingate'])->name(ViewsConstants::PLN.'.pay.with.coingate')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/coingate/{plan}', [CoingatePaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.coingate');

    // Route::post('/toyyibpay', [ToyyibpayController::class, 'planPayWithToyyibpay'])->name(ViewsConstants::PLN.'.toyyibpaypayment');
    // Route::get('/plan-pay-with-toyyibpay/{id}/{status}/{coupon}', [ToyyibpayController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.status');

    // Route::post('payfast-plan', [PayFastController::class, 'planPayWithPayfast'])->name('payfast.payment');
    // Route::get('payfast-plan/{success}', [PayFastController::class, 'getPaymentStatus'])->name('payfast.payment.success');

    // Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
    // Route::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

    // Route::post('/sspay', [SspayController::class, 'SspayPaymentPrepare'])->name(ViewsConstants::PLN.'.sspaypayment');
    // Route::get('sspay-payment-plan/{plan_id}/{amount}/{couponCode}', [SspayController::class, 'SspayPlanGetPayment'])->middleware([MiddlewaresConstants::AUTH])->name(ViewsConstants::PLN.'.sspay.callback');

    // Route::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithpaytab'])->middleware([MiddlewaresConstants::AUTH])->name(ViewsConstants::PLN.'.pay.with.paytab');
    // Route::any('paytab-success/plan', [PaytabController::class, 'PaytabGetPayment'])->middleware([MiddlewaresConstants::AUTH])->name(ViewsConstants::PLN.'.paytab.success');

    // Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name(ViewsConstants::PLN.'.pay.with.paystack')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name(ViewsConstants::PLN.'.paystack');

    // // PaymentWall

    // Route::post('/paymentwalls', [PaymentWallPaymentController::class, 'paymentwall'])->name(ViewsConstants::PLN.'.paymentwallpayment')->middleware([MiddlewaresConstants::XSS]);
    // Route::post('/plan-pay-with-paymentwall/{plan}', [PaymentWallPaymentController::class, 'planPayWithPaymentWall'])->name(ViewsConstants::PLN.'.pay.with.paymentwall')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/{flag}', [PaymentWallPaymentController::class, 'planeerror'])->name('error.plan.show');