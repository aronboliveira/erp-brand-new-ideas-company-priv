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
    ViewsConstants as VW
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
            VW::HM,
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
Route::get(VW::CST . '/' . VW::INV . '/{id}/', [InvoiceController::class, InvoiceController::IV_LK])->name(VW::INV . '.link.copy');
Route::get(VW::CST . '/' . VW::PPS . '/{id}/', [ProposalController::class, ProposalController::IV_LK])->name(VW::PPS . '.link.copy');
Route::get(VW::PPS . '/pdfs/{id}', [ProposalController::class, 'proposal'])->name(VW::PPS . '.pdf')
    ->middleware([MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
Route::get(VW::VND . '/' . VW::BIL . '/{id}/', [BillController::class, BillController::IV_LK])->name(VW::BIL . '.link.copy');
Route::get(VW::VND . '/' . VW::PRC . '/{id}/', [PurchaseController::class, PurchaseController::PRC_LK])->name(VW::PRC . '.link.copy');
#endregion
//================================= Invoice Payment Gateways  ====================================//
#region
Route::post(VW::CST . '/pay-with-bank', [BankTransferPaymentController::class, BankTransferPaymentController::CST_PAY_BNK])->name(VW::CST . '.pay.with.bank')
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(VW::INV . '/{id}/action', [BankTransferPaymentController::class, BankTransferPaymentController::INV_ACT])->name(VW::INV . '.action');
Route::post(VW::INV . '/{id}/change-action', [BankTransferPaymentController::class, BankTransferPaymentController::INV_CG_STT])->name(VW::INV . '.change.status');
Route::any(VW::INV . '/with-benefit', [BenefitPaymentController::class, BenefitPaymentController::INV_PAY_BF])->name(VW::INV . '.benefit.initiate');
Route::any(VW::INV . '/benefit/{invoice_id}/{amount}', [BenefitPaymentController::class, BenefitPaymentController::GET_INV_PAY_STT])->name(VW::INV . '.benefit.callback');
Route::post(VW::INV . '/with-cashfree/payment', [CashfreeController::class, CashfreeController::INV_PAY_CF])->name(VW::CST . '.pay.with.cashfree');
Route::any(VW::INV . '/with-cashfree/status', [CashfreeController::class, CashfreeController::GET_INV_PAY_STT])->name(VW::INV . '.cashfree.payment.success');
#endregion
//================================= Career Page  ====================================//
#region
Route::get(VW::CRR . '/{id}/{lang}', [JobController::class, 'career'])->name(VW::CRR)
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(VW::JB . '/requirement/{code}/{lang}', [JobController::class, JobController::JB_RQ])->name(VW::JB . '.requirement')
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(VW::JB . '/apply/{code}/{lang}', [JobController::class, JobController::JB_AP])->name(VW::JB . '.apply')->middleware([MiddlewaresConstants::XSS]);
Route::post(VW::JB . '/apply/data/{code}', [JobController::class, JobController::JB_AP_DT])->name(VW::JB . '.apply.data')->middleware([MiddlewaresConstants::XSS]);
#endregion
//================================= Project Copy Module  ====================================//
#region
Route::get(VW::PRJ . '/copy-link/{id}', [ProjectController::class, 'projectCopyLink'])->name(VW::PRJ . '.copy_link');
Route::any(VW::PRJ . '/link/{id}/{lang?}', [ProjectController::class, 'projectlink'])->name(VW::PRJ . '.link')->middleware([MiddlewaresConstants::XSS]);
Route::get(VW::TMS . '/table-view', [TimesheetController::class, TimesheetController::FT_TMS_TBL])->name(VW::TMS . '.filters.table.view')
    ->middleware([MiddlewaresConstants::XSS]);
Route::get(VW::INV . '/pdf/{id}', [InvoiceController::class, 'invoice'])->name(VW::INV . '.pdf')
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
        ->name(VW::PRJ . '.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get('/hrm-dashboard', [DashboardController::class, DashboardController::HRM_DSB_IDX])->name('hrm.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get('/crm-dashboard', [DashboardController::class, DashboardController::CRM_DSB_IDX])->name('crm.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get('/pos-dashboard', [DashboardController::class, DashboardController::POS_DSB_IDX])->name(VW::POS . '.dashboard')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::get('profile', [UserController::class, 'profile'])->name('profile')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::any('edit-profile', [UserController::class, UserController::EDT_PRF])->name('update.account')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource(DatabaseConstants::TABLE_USERS, UserController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::post('change-password', [UserController::class, UserController::UPD_PSW])
        ->name('update.password');

    Route::any('user-reset-password/{id}', [UserController::class, UserController::USR_PSW])->name(VW::USR . '.reset');

    Route::post('user-reset-password/{id}', [UserController::class, UserController::USR_PSW_RST])->name(VW::USR . '.password.update');

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
            Route::get('change-language/{lang}', [LanguageController::class, LanguageController::CHG_LNG])->name(VW::LNG . '.change');
            Route::get('manage-language/{lang}', [LanguageController::class, LanguageController::MNG_LNG])->name(VW::LNG . '.manage');
            Route::post('store-language-data/{lang}', [LanguageController::class, LanguageController::STR_LNG_DT])->name(VW::LNG . '.store.data');
            Route::get('create-language', [LanguageController::class, LanguageController::CR_LNG])->name(VW::LNG . '.create');
            Route::any('store-language', [LanguageController::class, LanguageController::STR_LNG])->name(VW::LNG . '.store');
            Route::delete('/lang/{lang}', [LanguageController::class, LanguageController::DEL_LNG])->name(VW::LNG . '.destroy');
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
            Route::post('email-settings', [SystemController::class, SystemController::SV_EM_ST])->name(VW::EML . '.settings');
            Route::post('company-email-settings', [SystemController::class, SystemController::SV_CP_EM_ST])->name(VW::CP . '.email.settings');
            Route::post('company-settings', [SystemController::class, SystemController::SV_CP_ST])->name(VW::CP . '.settings');
            Route::post('system-settings', [SystemController::class, SystemController::SV_SYS_ST])->name(VW::SYS . '.settings');
            Route::post('zoom-settings', [SystemController::class, SystemController::SV_ZM_ST])->name('zoom.settings');
            Route::post('tracker-settings', [SystemController::class, SystemController::SV_TK_ST])->name(VW::TMT . '.settings');
            Route::post('slack-settings', [SystemController::class, SystemController::SV_SLK_ST])->name('slack.settings');
            Route::post('telegram-settings', [SystemController::class, SystemController::SV_TLG_ST])->name('telegram.settings');
            Route::post('twilio-settings', [SystemController::class, SystemController::SV_TWL_ST])->name('twilio.setting');
            Route::get('print-setting', [SystemController::class, SystemController::PRT])->name('print.setting');
            Route::get('settings', [SystemController::class, SystemController::CP])->name('settings');
            Route::post('business-setting', [SystemController::class, SystemController::SV_BS_ST])->name('business.setting');
            Route::post('company-payment-setting', [SystemController::class, SystemController::SV_CP_PAY_ST])
                ->name(VW::CP . '.payment.settings');
            Route::get('test-mail', [SystemController::class, SystemController::TT_MAIL])->name(VW::TT . '.mail');
            Route::post('test-mail', [SystemController::class, SystemController::TT_MAIL])->name(VW::TT . '.mail');
            Route::post('test-mail/send', [SystemController::class, SystemController::TT_SMAIL])->name(VW::TT . '.send.mail');
            Route::post('stripe-settings', [SystemController::class, SystemController::SV_PAY_ST])->name(VW::PAY . '.settings');
            Route::post('pusher-setting', [SystemController::class, SystemController::SV_PSR_ST])->name(VW::SET . '.pusher');
            Route::post('recaptcha-settings', [SystemController::class, SystemController::RCP_ST_STR])->name('settings.recaptcha.store')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('seo-settings', [SystemController::class, SystemController::SEO_ST])->name(VW::SET . '.seo.store')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::any('webhook-settings', [SystemController::class, 'webhook'])->name(VW::WBH . '.settings')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::get('webhook-settings/create', [SystemController::class, SystemController::WHK_CRT])->name(VW::WBH . '.create')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('webhook-settings/store', [SystemController::class, SystemController::WHK_STR])->name(VW::WBH . '.store');
            Route::get('webhook-settings/{wid}/edit', [SystemController::class, SystemController::WHK_EDT])->name(VW::WBH . '.edit')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('webhook-settings/{wid}/edit', [SystemController::class, SystemController::WHK_UPD])->name(VW::WBH . '.update')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::delete('webhook-settings/{wid}', [SystemController::class, SystemController::WHK_DST])->name(VW::WBH . '.destroy')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('cookie-setting', [SystemController::class, SystemController::SV_CK_ST])->name(VW::SET . '.cookies.store');
            Route::post('cache-settings', [SystemController::class, SystemController::CC_ST_STR])->name('cache.settings.store')
                ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
        }
        #endregion
    );

    //================================= Product Services ====================================//
    #region
    Route::get(VW::PRD_SV . '/index', [ProductServiceController::class, 'index'])
        ->name(VW::PRD_SV . '.index');
    Route::get(VW::PRD_SV . '/{id}/detail', [ProductServiceController::class, ProductServiceController::WRH_DTL])
        ->name(VW::PRD_SV . '.detail');
    Route::post('empty-cart', [ProductServiceController::class, ProductServiceController::EMP_CRT])
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('warehouse-empty-cart', [ProductServiceController::class, ProductServiceController::WRH_EMP_CRT])
        ->name('warehouse-empty-cart')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::PRD_SV, ProductServiceController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    #endregion

    //================================= Product Stock ====================================//
    #region
    Route::resource(VW::PRD_STK, ProductStockController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
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
            Route::get(VW::CST . '/{id}/show', [CustomerController::class, 'show'])
                ->name(VW::CST . '.show');
            Route::resource(VW::CST, CustomerController::class);
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
            Route::get(VW::VND . '/{id}/show', [VendorController::class, 'show'])
                ->name(VW::VND . '.show');
            Route::resource(VW::VND, VendorController::class);
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
            Route::resource(VW::BNK_ACC, BankAccountController::class);
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
            Route::get(VW::BNK_TRF . '/index', [BankTransferController::class, 'index'])->name(VW::BNK_TRF . '.index');
            Route::resource(VW::BNK_TRF, BankTransferController::class);
        }
        #endregion
    );

    //================================= Product Service Categories ====================================//
    #region
    Route::resource(VW::TX, TaxController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::resource(VW::PRD_SV_CAT, ProductServiceCategoryController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::post(VW::PRD_SV_CAT . '/get-account', [ProductServiceCategoryController::class, ProductServiceCategoryController::GET_ACC])
        ->name(VW::PRD_SV_CAT . '.get_account')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::resource(VW::PRD_SV_UNT, ProductServiceUnitController::class)
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
            Route::get(VW::INV . '/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name(VW::INV . '.duplicate');
            Route::get(VW::INV . '/{id}/shipping/print', [InvoiceController::class, InvoiceController::SHP_DSP])->name(VW::INV . '.shipping.print');
            Route::get(VW::INV . '/{id}/payment/reminder', [InvoiceController::class, InvoiceController::PAY_RMD])->name(VW::INV . '.payment.reminder');
            Route::get(VW::INV . '/index', [InvoiceController::class, 'index'])->name(VW::INV . '.index');
            Route::post(VW::INV . '/product/destroy', [InvoiceController::class, InvoiceController::PRD_DST])->name(VW::INV . '.product.destroy');
            Route::post(VW::INV . '/product', [InvoiceController::class, 'product'])->name(VW::INV . '.product');
            Route::post(VW::INV . '/customer', [InvoiceController::class, 'customer'])->name(VW::INV . '.customer');
            Route::get(VW::INV . '/{id}/sent', [InvoiceController::class, 'sent'])->name(VW::INV . '.sent');
            Route::get(VW::INV . '/{id}/resent', [InvoiceController::class, 'resent'])->name(VW::INV . '.resent');
            Route::get(VW::INV . '/{id}/payment', [InvoiceController::class, 'payment'])->name(VW::INV . '.payment');
            Route::post(VW::INV . '/{id}/payment', [InvoiceController::class, InvoiceController::PAY_CRT])->name(VW::INV . '.payment');
            Route::post(VW::INV . '/{id}/payment/{pid}/destroy', [InvoiceController::class, InvoiceController::PAY_DST])
                ->name(VW::INV . '.payment.destroy');
            Route::get(VW::INV . '/items', [InvoiceController::class, 'items'])->name(VW::INV . '.items');
            Route::resource(VW::INV, InvoiceController::class);
            Route::get(VW::INV . '/create/{cid}', [InvoiceController::class, 'create'])->name(VW::INV . '.create');
        }
        #endregion
    );
    Route::get(VW::INV . '/preview/{template}/{color}', [InvoiceController::class, InvoiceController::INV_PRV])->name(VW::INV . '.preview');
    Route::post(VW::INV . '/template/setting', [InvoiceController::class, InvoiceController::SV_IV_TMP])
        ->name(VW::INV_TMP . 'settings');

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
            Route::get(str_replace('_', '-', VW::CRD_NT), [CreditNoteController::class, 'index'])->name('credit.note');
            Route::get('custom-credit-note', [CreditNoteController::class, 'customCreate'])->name(VW::INV . '.custom.credit.note');
            Route::post('custom-credit-note', [CreditNoteController::class, 'customStore'])->name(VW::INV . '.custom.credit.note');
            Route::get(VW::CRD_NT . '/invoice', [CreditNoteController::class, 'getInvoice'])->name(VW::INV . '.get');
            Route::get(VW::INV . '/{id}/credit-note', [CreditNoteController::class, 'create'])->name(VW::INV . '.credit.note');
            Route::post(VW::INV . '/{id}/credit-note', [CreditNoteController::class, 'store'])->name(VW::INV . '.credit.note');
            Route::get(VW::INV . '/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'edit'])->name(VW::INV . '.edit.credit.note');
            Route::post(VW::INV . '/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'update'])
                ->name(VW::INV . '.edit.credit.note');
            Route::delete(VW::INV . '/{id}/credit-note/delete/{cn_id}', [CreditNoteController::class, 'destroy'])
                ->name(VW::INV . '.delete.credit.note');
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
            Route::get(VW::DBT_NT, [DebitNoteController::class, 'index'])->name('debit.note');
            Route::get('custom-debit-note', [DebitNoteController::class, 'customCreate'])->name(VW::BIL . '.custom.debit.note');
            Route::post('custom-debit-note', [DebitNoteController::class, 'customStore'])->name(VW::BIL . '.custom.debit.note');
            Route::get(VW::DBT_NT . '/bill', [DebitNoteController::class, 'getbill'])->name(VW::BIL . '.get');
            Route::get(VW::BIL . '{id}/debit-note', [DebitNoteController::class, 'create'])->name(VW::BIL . '.debit.note');
            Route::post(VW::BIL . '{id}/debit-note', [DebitNoteController::class, 'store'])->name(VW::BIL . '.debit.note');
            Route::get(VW::BIL . '{id}/' . VW::DBT_NT . '/edit/{cn_id}', [DebitNoteController::class, 'edit'])->name(VW::BIL . '.edit.debit.note');
            Route::post(VW::BIL . '{id}/' . VW::DBT_NT . '/edit/{cn_id}', [DebitNoteController::class, 'update'])->name(VW::BIL . '.edit.debit.note');
            Route::delete(VW::BIL . '{id}/' . VW::DBT_NT . '/delete/{cn_id}', [DebitNoteController::class, 'destroy'])->name(VW::BIL . '.delete.debit.note');
        }
    );

    Route::get(VW::BIL . 'preview/{template}/{color}', [BillController::class, 'previewBill'])->name(VW::BIL . '.preview')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::BIL . 'template/setting', [BillController::class, BillController::SV_BIL_TMP])
        ->name(VW::BIL_TMP . 'setting');

    Route::resource(VW::TX, TaxController::class)
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::get(VW::RVN . '/index', [RevenueController::class, 'index'])->name(VW::RVN . '.index')->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    Route::resource(VW::RVN, RevenueController::class)->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    Route::get(VW::BIL . 'pdf/{id}', [BillController::class, 'bill'])->name(VW::BIL . '.pdf')->middleware([
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
            Route::get(VW::BIL . '{id}/duplicate', [BillController::class, 'duplicate'])->name(VW::BIL . '.duplicate');
            Route::get(VW::BIL . '{id}/shipping/print', [BillController::class, 'shippingDisplay'])->name(VW::BIL . '.shipping.print');
            Route::get(VW::BIL . 'index', [BillController::class, 'index'])->name(VW::BIL . '.index');
            Route::post(VW::BIL . 'product/destroy', [BillController::class, 'productDestroy'])->name(VW::BIL . '.product.destroy');
            Route::post(VW::BIL . 'product', [BillController::class, 'product'])->name(VW::BIL . '.product');
            Route::post(VW::BIL . 'vendor', [BillController::class, 'vendor'])->name(VW::BIL . '.vendor');
            Route::get(VW::BIL . '{id}/sent', [BillController::class, 'sent'])->name(VW::BIL . '.sent');
            Route::get(VW::BIL . '{id}/resent', [BillController::class, 'resent'])->name(VW::BIL . '.resent');
            Route::get(VW::BIL . '{id}/payment', [BillController::class, 'payment'])->name(VW::BIL . '.payment');
            Route::post(VW::BIL . '{id}/payment', [BillController::class, 'createPayment'])->name(VW::BIL . '.payment');
            Route::post(VW::BIL . '{id}/payment/{pid}/destroy', [BillController::class, 'paymentDestroy'])->name(VW::BIL . '.payment.destroy');
            Route::get(VW::BIL . 'items', [BillController::class, 'items'])->name(VW::BIL . '.items');
            Route::resource(VW::BIL, BillController::class);
            Route::get(VW::BIL . 'create/{cid}', [BillController::class, 'create'])->name(VW::BIL . '.create');
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
            Route::get(VW::RPT . '/transaction', [TransactionController::class, 'index'])->name('transaction.index');
        }
    );

    Route::group(
        //================================= Reports ====================================//
        #region
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get(VW::RPT . '/income-summary', [ReportController::class, ReportController::INC_SM])->name(VW::RPT . '.income.summary');
            Route::get(VW::RPT . '/expense-summary', [ReportController::class, ReportController::EXP_SM])->name(VW::RPT . '.expense.summary');
            Route::get(VW::RPT . '/income-vs-expense-summary', [ReportController::class, ReportController::INC_EXP_SM])->name(VW::RPT . '.income.vs.expense.summary');
            Route::get(VW::RPT . '/tax-summary', [ReportController::class, ReportController::TX_SM])->name(VW::RPT . '.tax.summary');
            // Route::get(VW::RPT . '/profit-loss-summary', [ReportController::class, ReportController::PROFIT_LOSS_SM])->name(VW::RPT . '.profit.loss.summary');
            Route::get(VW::RPT . '/invoice-summary', [ReportController::class, ReportController::INV_SM])->name(VW::RPT . '.invoice.summary');
            Route::get(VW::RPT . '/bill-summary', [ReportController::class, ReportController::BL_SM])->name(VW::RPT . '.bill.summary');
            Route::get(VW::RPT . '/product-stock-report', [ReportController::class, ReportController::PRD_STK])->name(VW::RPT . '.product.stock.report');
            Route::get(VW::RPT . '/invoice-report', [ReportController::class, ReportController::INV_SM])->name(VW::RPT . '.invoice');
            Route::get(VW::RPT . '/account-statement-report', [ReportController::class, ReportController::ACC_STT])->name(VW::RPT . '.account.statement');
            Route::get(VW::RPT . '/balance-sheet/{view?}', [ReportController::class, ReportController::BL_SHT])->name(VW::RPT . '.balance.sheet');
            Route::get(VW::RPT . '/profit-loss/{view?}', [ReportController::class, ReportController::PRF_LS])->name(VW::RPT . '.profit.loss');
            Route::get(VW::RPT . '/ledger/{account?}', [ReportController::class, ReportController::LDG_SM])->name(VW::RPT . '.ledger');
            Route::get(VW::RPT . '/trial-balance', [ReportController::class, ReportController::TRL_BL_SUM])->name(VW::RPT . '.trial.balance');
            Route::get(VW::RPT . '-monthly-cashflow', [ReportController::class, ReportController::MLY_CSH_FLW])->name(VW::RPT . '.monthly.cashflow')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::get(VW::RPT . '-quarterly-cashflow', [ReportController::class, ReportController::QLY_CSH_FLW])->name(VW::RPT . '.quarterly.cashflow')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post('export/trial-balance', [ReportController::class, ReportController::TRL_BLC_EXP])->name(VW::RPT . '.trial.balance.export');
            Route::post('export/balance-sheet', [ReportController::class, ReportController::BLC_SHT_EXP])->name(VW::RPT . '.balance.sheet.export');
            Route::post('print/balance-sheet/{view?}', [ReportController::class, ReportController::BLC_SHT_PRT])->name(VW::RPT . '.balance.sheet.print');
            Route::post('print/trial-balance', [ReportController::class, ReportController::TRL_BLC_PRT])->name('trial.balance.print');
            Route::post('export/profit-loss', [ReportController::class, ReportController::PRF_LS_EXP])->name(VW::RPT . '.profit.loss.export');
            Route::post('print/profit-loss/{view?}', [ReportController::class, ReportController::PRF_LS_PRT])->name(VW::RPT . '.profit.loss.print');
            Route::get(VW::RPT . '/sales', [ReportController::class, ReportController::SLS_RPT])->name(VW::RPT . '.sales');
            Route::post('export/sales', [ReportController::class, ReportController::SLS_RPT_EXP])->name(VW::RPT . '.sales.export');
            Route::post('print/sales-report', [ReportController::class, ReportController::SLS_RPT_PRT])->name(VW::RPT . '.sales.report.print');
            Route::get(VW::RPT . '/receivables', [ReportController::class, ReportController::RCV_RPT])->name(VW::RPT . '.receivables');
            Route::post('export/receivables', [ReportController::class, ReportController::RCV_EXP])->name('receivables.export');
            Route::post('print/receivables', [ReportController::class, ReportController::RCV_PRT])->name(VW::RPT . '.receivables.print');
            Route::get(VW::RPT . '/payables', [ReportController::class, ReportController::PAY_RPT])->name(VW::RPT . '.payables');
            Route::post('print/payables', [ReportController::class, ReportController::PAY_PRT])->name(VW::RPT . '.payables.print');
        }
        #endregion
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
            Route::get(VW::PPS . '/{id}/status/change', [ProposalController::class, ProposalController::STT_CHG])->name(VW::PPS . '.status.change');
            Route::get(VW::PPS . '/{id}/convert', [ProposalController::class, 'convert'])->name(VW::PPS . '.convert');
            Route::get(VW::PPS . '/{id}/duplicate', [ProposalController::class, 'duplicate'])->name(VW::PPS . '.duplicate');
            Route::post(VW::PPS . '/product/destroy', [ProposalController::class, ProposalController::PRD_DST])->name(VW::PPS . '.product.destroy');
            Route::post(VW::PPS . '/customer', [ProposalController::class, 'customer'])->name(VW::PPS . '.customer');
            Route::post(VW::PPS . '/product', [ProposalController::class, 'product'])->name(VW::PPS . '.product');
            Route::get(VW::PPS . '/items', [ProposalController::class, 'items'])->name(VW::PPS . '.items');
            Route::get(VW::PPS . '/{id}/sent', [ProposalController::class, 'sent'])->name(VW::PPS . '.sent');
            Route::get(VW::PPS . '/{id}/resent', [ProposalController::class, 'resent'])->name(VW::PPS . '.resent');
            Route::resource('proposal', ProposalController::class);
            Route::get(VW::PPS . '/create/{cid}', [ProposalController::class, 'create'])->name(VW::PPS . '.create');
        }
    );

    Route::get(VW::PPS . '/preview/{template}/{color}', [ProposalController::class, ProposalController::PV_PPS])->name(VW::PPS . '.preview');
    Route::post(VW::PPS . '/templates/settings', [ProposalController::class, ProposalController::SV_PPS_TMP])
        ->name(VW::PPS . 'settings');

    Route::resource('goal', GoalController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    //Budget Planner //
    Route::resource('budget', BudgetController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource('account_assets', AssetController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::resource(VW::CST_FD, CustomFieldController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::post(VW::COA . '/subtype', [ChartOfAccountController::class, 'getSubType'])->name(VW::COA . '.sub_type')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::resource(VW::COA, ChartOfAccountController::class);
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

    Route::post(VW::DL . '/user', [DealController::class, 'jsonUser'])->name(VW::DL . '.user.json');
    Route::post(VW::DL . '/order', [DealController::class, 'order'])->name(VW::DL . '.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/change-pipeline', [DealController::class, 'changePipeline'])->name(VW::DL . '.change.pipeline')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/change-deal-status/{id}', [DealController::class, 'changeStatus'])->name(VW::DL . '.change.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/labels', [DealController::class, 'labels'])->name(VW::DL . '.labels')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/{id}/labels', [DealController::class, 'labelStore'])->name(VW::DL . '.labels.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/users', [DealController::class, 'userEdit'])->name(VW::DL . '.users.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(VW::DL . '/{id}/users', [DealController::class, 'userUpdate'])->name(VW::DL . '.users.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::DL . '/{id}/users/{uid}', [DealController::class, 'userDestroy'])->name(VW::DL . '.users.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/clients', [DealController::class, 'clientEdit'])->name(VW::DL . '.clients.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(VW::DL . '/{id}/clients', [DealController::class, 'clientUpdate'])->name(VW::DL . '.clients.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::DL . '/{id}/clients/{uid}', [DealController::class, 'clientDestroy'])->name(VW::DL . '.clients.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/products', [DealController::class, 'productEdit'])->name(VW::DL . '.products.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(VW::DL . '/{id}/products', [DealController::class, 'productUpdate'])->name(VW::DL . '.products.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::DL . '/{id}/products/{uid}', [DealController::class, 'productDestroy'])->name(VW::DL . '.products.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/sources', [DealController::class, 'sourceEdit'])->name(VW::DL . '.sources.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(VW::DL . '/{id}/sources', [DealController::class, 'sourceUpdate'])->name(VW::DL . '.sources.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::DL . '/{id}/sources/{uid}', [DealController::class, 'sourceDestroy'])->name(VW::DL . '.sources.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/{id}/file', [DealController::class, 'fileUpload'])->name(VW::DL . '.file.upload')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/file/{fid}', [DealController::class, 'fileDownload'])->name(VW::DL . '.file.download')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::DL . '/{id}/file/delete/{fid}', [DealController::class, 'fileDelete'])->name(VW::DL . '.file.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/{id}/note', [DealController::class, 'noteStore'])->name(VW::DL . '.note.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get(VW::DL . '/{id}/' . VW::TSK, [DealController::class, 'taskCreate'])->name(VW::DL . '.tasks.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/{id}/' . VW::TSK, [DealController::class, 'taskStore'])->name(VW::DL . '.tasks.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/' . VW::TSK . '/{tid}/show', [DealController::class, 'taskShow'])->name(VW::DL . '.tasks.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/' . VW::TSK . '/{tid}/edit', [DealController::class, 'taskEdit'])->name(VW::DL . '.tasks.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(VW::DL . '/{id}/' . VW::TSK . '/{tid}', [DealController::class, 'taskUpdate'])->name(VW::DL . '.tasks.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(VW::DL . '/{id}/task_status/{tid}', [DealController::class, 'taskUpdateStatus'])->name(VW::DL . '.tasks.update_status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::DL . '/{id}/' . VW::TSK . '/{tid}', [DealController::class, 'taskDestroy'])->name(VW::DL . '.tasks.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/discussions', [DealController::class, 'discussionCreate'])->name(VW::DL . '.discussions.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/{id}/discussions', [DealController::class, 'discussionStore'])->name(VW::DL . '.discussion.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/{id}/permission/{cid}', [DealController::class, 'permission'])->name(VW::DL . '.client.permission')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put(VW::DL . '/{id}/permission/{cid}', [DealController::class, 'permissionStore'])->name(VW::DL . '.client.permissions.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::DL . '/list', [DealController::class, 'deal_list'])->name(VW::DL . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Deal Calls

    Route::get(VW::DL . '/{id}/call', [DealController::class, 'callCreate'])->name(VW::DL . '.calls.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/{id}/call', [DealController::class, 'callStore'])->name(VW::DL . '.calls.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get(VW::DL . '/{id}/call/{cid}/edit', [DealController::class, 'callEdit'])->name(VW::DL . '.calls.edit')->middleware([MiddlewaresConstants::AUTH]);
    Route::put(VW::DL . '/{id}/call/{cid}', [DealController::class, 'callUpdate'])->name(VW::DL . '.calls.update')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete(VW::DL . '/{id}/call/{cid}', [DealController::class, 'callDestroy'])->name(VW::DL . '.calls.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Deal Email

    Route::get(VW::DL . '/{id}/email', [DealController::class, 'emailCreate'])->name(VW::DL . '.emails.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::DL . '/{id}/email', [DealController::class, 'emailStore'])->name(VW::DL . '.emails.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(VW::DL, DealController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

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

    Route::post('/leads/json', [LeadController::class, 'json'])->name(VW::LD . '.json');
    Route::post('/leads/order', [LeadController::class, 'order'])->name(VW::LD . '.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/list', [LeadController::class, 'lead_list'])->name(VW::LD . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name(VW::LD . '.file.upload')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name(VW::LD . '.file.download')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name(VW::LD . '.file.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name(VW::LD . '.note.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name(VW::LD . '.labels')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name(VW::LD . '.labels.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name(VW::LD . '.users.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name(VW::LD . '.users.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name(VW::LD . '.users.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/products', [LeadController::class, 'productEdit'])->name(VW::LD . '.products.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/products', [LeadController::class, 'productUpdate'])->name(VW::LD . '.products.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/products/{uid}', [LeadController::class, 'productDestroy'])->name(VW::LD . '.products.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name(VW::LD . '.sources.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name(VW::LD . '.sources.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name(VW::LD . '.sources.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name(VW::LD . '.discussions.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name(VW::LD . '.discussion.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name(VW::LD . '.convert.deal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name(VW::LD . '.convert.to.deal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Lead Calls
    Route::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name(VW::LD . '.calls.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name(VW::LD . '.calls.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name(VW::LD . '.calls.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name(VW::LD . '.calls.update')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name(VW::LD . '.calls.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Lead Email

    Route::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name(VW::LD . '.emails.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name(VW::LD . '.emails.store')->middleware([MiddlewaresConstants::AUTH]);

    Route::resource('leads', LeadController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // end Leads Module

    Route::get(VW::USR . '/{id}/plan', [UserController::class, UserController::UPG_PLN])->name(VW::PLN . '.upgrade')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::USR . '/{id}/plan/{pid}', [UserController::class, UserController::ACT_PLN])->name(VW::PLN . '.active')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/{uid}/notification/seen', [UserController::class, 'notificationSeen'])->name('notification.seen');

    // Email Templates
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('email_template_store', [EmailTemplateController::class, 'updateStatus'])->name(VW::EMLS . '.status.language')->middleware([MiddlewaresConstants::AUTH]);
    Route::any('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name(VW::EMLS . '.store.language')->middleware([MiddlewaresConstants::AUTH]);
    Route::resource('email_template', EmailTemplateController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // End Email Templates

    // HRM
    Route::resource('user', UserController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::EMP . '/json', [EmployeeController::class, 'json'])->name(VW::EMP . '.json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('branchVW::EMP.//json', [EmployeeController::class, 'employeeJson'])->name(VW::BRC . '.employee.json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('employee-profile', [EmployeeController::class, 'profile'])->name(VW::EMP . '.profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('show-employee-profile/{id}', [EmployeeController::class, 'profileShow'])->name('show.employee.profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get('last-login', [EmployeeController::class, 'lastLogin'])->name('last_login')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(VW::EMP, EmployeeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(VW::EMP . '/getdepartment', [EmployeeController::class, 'getDepartment'])->name(VW::EMP . '.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(VW::DPT, DepartmentController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::DSG, DesignationController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::DOC, DocumentController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::BRC, BranchController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Hrm EmployeeController

    Route::get(VW::EMP . '/salary/{eid}', [SetSalaryController::class, 'employeeBasicSalary'])->name(VW::EMP . '.salary.basic')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //payslip

    Route::resource(VW::ALW, AllowanceController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::ALW_OPT, AllowanceOptionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::COM, CommissionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::DDT_OPT, DeductionOptionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::LN_OPT, LoanOptionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::LN, LoanController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::PY_SLP, PayslipTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::STR_DD, SaturationDeductionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::OT_PAY, OtherPaymentController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::OVT, OvertimeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::EMP . '/salary/{eid}', [SetSalaryController::class, SetSalaryController::EMP_SL_BASIC])->name(VW::EMP . '.salary.basic')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::EMP . '/update/sallary/{id}', [SetSalaryController::class, SetSalaryController::EMP_SL_UPDATE])->name(VW::EMP . '.salary.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::EMP . '/salary', [SetSalaryController::class, SetSalaryController::EMP_SL])->name(VW::EMP . '.salary')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::S_SLR, SetSalaryController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::ALW . '/create/{eid}', [AllowanceController::class, AllowanceController::ALW_CR])->name(VW::ALW . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::COM . '/create/{eid}', [CommissionController::class, CommissionController::COM_CR])->name(VW::COM . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('loans/create/{eid}', [LoanController::class, 'loanCreate'])->name('loans.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::STR_DD . '/create/{eid}', [SaturationDeductionController::class, SaturationDeductionController::STR_DD_CR])->name(VW::STR_DD . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::OT_PAY . '/create/{eid}', [OtherPaymentController::class, OtherPaymentController::OT_PAY_CR])->name(VW::OT_PAY . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('overtimes/create/{eid}', [OvertimeController::class, 'overtimeCreate'])->name('overtimes.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/paysalary/{id}/{date}', [PayslipController::class, 'paysalary'])->name(VW::PY_SLP . '.paysalary')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/bulk_pay_create/{date}', [PayslipController::class, 'bulkPayCreate'])->name(VW::PY_SLP . '.bulk_pay_create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PY_SLP . '/bulkpayment/{date}', [PayslipController::class, 'bulkPayment'])->name(VW::PY_SLP . '.bulkpayment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PY_SLP . '/search_json', [PayslipController::class, 'searchJson'])->name(VW::PY_SLP . '.search_json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/employeepayslip', [PayslipController::class, 'employeePayslip'])->name(VW::PY_SLP . '.employeepayslip')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/show/{id}', [PayslipController::class, 'showEmployee'])->name(VW::PY_SLP . '.showemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/edit/{id}', [PayslipController::class, 'editEmployee'])->name(VW::PY_SLP . '.editemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PY_SLP . '/update/{id}', [PayslipController::class, 'updateEmployee'])->name(VW::PY_SLP . '.updateemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/pdf/{id}/{m}', [PayslipController::class, 'pdf'])->name(VW::PY_SLP . '.pdf')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/payslipPdf/{id}', [PayslipController::class, 'payslipPdf'])->name(VW::PY_SLP . '.payslipPdf')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/send/{id}/{m}', [PayslipController::class, 'send'])->name(VW::PY_SLP . '.send')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PY_SLP . '/delete/{id}', [PayslipController::class, 'destroy'])->name(VW::PY_SLP . '.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::PY_SLP, PayslipController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(VW::CPN_PL, CompanyPolicyController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::IND, IndicatorController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::APR, AppraisalController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(VW::BRC . '/' . VW::EMP . '/json', [EmployeeController::class, 'employeeJson'])->name(VW::BRC . '.employee.json')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(VW::GL_TP, GoalTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::GL_TRC, GoalTrackingController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
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

    Route::resource(VW::AWD_TP, AwardTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::AWD, AwardController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
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
    Route::post('job-stage/order', [JobStageController::class, 'order'])->name(VW::JB . '.stage.order');

    Route::resource('job', JobController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get('candidates-job-applications', [JobApplicationController::class, 'candidate'])->name(VW::JB . '.application.candidate')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('job-application', JobApplicationController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/order', [JobApplicationController::class, 'order'])->name(VW::JB . '.application.order')->middleware([MiddlewaresConstants::XSS]);
    Route::post('job-application/{id}/rating', [JobApplicationController::class, 'rating'])->name(VW::JB . '.application.rating')->middleware([MiddlewaresConstants::XSS]);
    Route::delete('job-application/{id}/archive', [JobApplicationController::class, 'archive'])->name(VW::JB . '.application.archive')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/{id}/skill/store', [JobApplicationController::class, 'addSkill'])->name(VW::JB . '.application.skill.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/{id}/note/store', [JobApplicationController::class, 'addNote'])->name(VW::JB . '.application.note.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('job-application/{id}/note/destroy', [JobApplicationController::class, 'destroyNote'])->name(VW::JB . '.application.note.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/getByJob', [JobApplicationController::class, 'getByJob'])->name('get.job.application')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('job-onboard', [JobApplicationController::class, 'jobOnBoard'])->name(VW::JB . '.on.board')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::JB_OB . '/create/{id}', [JobApplicationController::class, 'jobBoardCreate'])->name(VW::JB . '.on.board.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::JB_OB . '/store/{id}', [JobApplicationController::class, 'jobBoardStore'])->name(VW::JB . '.on.board.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::JB_OB . '/edit/{id}', [JobApplicationController::class, 'jobBoardEdit'])->name(VW::JB . '.on.board.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::JB_OB . '/update/{id}', [JobApplicationController::class, 'jobBoardUpdate'])->name(VW::JB . '.on.board.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::JB_OB . '/delete/{id}', [JobApplicationController::class, 'jobBoardDelete'])->name(VW::JB . '.on.board.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::JB_OB . '/convert/{id}', [JobApplicationController::class, 'jobBoardConvert'])->name(VW::JB . '.on.board.convert')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::JB_OB . '/convert/{id}', [JobApplicationController::class, 'jobBoardConvertData'])->name(VW::JB . '.on.board.convert')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('job-application/stage/change', [JobApplicationController::class, 'stageChange'])->name(VW::JB . '.application.stage.change')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('custom-question', CustomQuestionController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('interview-schedule', InterviewScheduleController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('interview-schedule/create/{id?}', [InterviewScheduleController::class, 'create'])->name(VW::ITV_SCD . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('task-board/{view?}', [ProjectTaskController::class, ProjectTaskController::TSK_BD])->name(VW::TSKB . '.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('task-board-view', [ProjectTaskController::class, ProjectTaskController::TSK_BD_VW])->name(VW::PRJ . '.taskboard.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(VW::DOC_UP, DocumentUploadController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('transfer', TransferController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::EMP_ATD . '/' . EmployeeAttendanceController::BK_ATD, [EmployeeAttendanceController::class, EmployeeAttendanceController::BK_ATD])->name(VW::EMP_ATD . '.' . EmployeeAttendanceController::BK_ATD)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::EMP_ATD . '/' . EmployeeAttendanceController::BK_ATD, [EmployeeAttendanceController::class, EmployeeAttendanceController::BK_ATD_DT])->name(VW::EMP_ATD . '.' . EmployeeAttendanceController::BK_ATD)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::EMP_ATD . '/attendance', [EmployeeAttendanceController::class, 'attendance'])->name(VW::EMP_ATD . '.attendance')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource(VW::EMP_ATD . '', EmployeeAttendanceController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource(VW::LV_TP, LeaveTypeController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::RPT . '/leave', [ReportController::class, 'leave'])->name(VW::RPT . '.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::EMP . '/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name(VW::RPT . '.employee.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::LV . '/{id}/action', [LeaveController::class, 'action'])->name(VW::LV . '.action')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::LV . '/changeaction', [LeaveController::class, 'changeaction'])->name(VW::LV . '.change_action')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::LV . '/jsoncount', [LeaveController::class, 'jsoncount'])->name(VW::LV . '.jsoncount')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('leave', LeaveController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::RPT . '-leave', [ReportController::class, 'leave'])->name(VW::RPT . '.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::EMP . '/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name(VW::RPT . '.employee.leave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::RPT . '-payroll', [ReportController::class, 'payroll'])->name(VW::RPT . '.payroll')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::RPT . '-payroll/getdepartment', [ReportController::class, 'getPayrollDepartment'])->name(VW::RPT . '.payroll.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::RPT . '-payroll/getemployee', [ReportController::class, 'getPayrollEmployee'])->name(VW::RPT . '.payroll.getemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::RPT . '-monthly-attendance', [ReportController::class, 'monthlyAttendance'])->name(VW::RPT . '.monthly.attendance')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::RPT . '/attendance/{month}/{branch}/{department}', [ReportController::class, 'exportCsv'])->name(VW::RPT . '.attendance')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //crm report
    Route::get(VW::RPT . '-lead', [ReportController::class, 'leadReport'])->name(VW::RPT . '.lead')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::RPT . '-deal', [ReportController::class, 'dealReport'])->name(VW::RPT . '.deal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //pos report
    Route::get(VW::RPT . '-warehouse', [ReportController::class, 'warehouseReport'])->name(VW::RPT . '.warehouse')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::RPT . '-daily-purchase', [ReportController::class, 'purchaseDailyReport'])->name(VW::RPT . '.daily.purchase')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::RPT . '-monthly-purchase', [ReportController::class, 'purchaseMonthlyReport'])->name(VW::RPT . '.monthly.purchase')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::RPT . '-daily-pos', [ReportController::class, 'posDailyReport'])->name(VW::RPT . '.daily.pos')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::RPT . '-monthly-pos', [ReportController::class, 'posMonthlyReport'])->name(VW::RPT . '.monthly.pos')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::get(VW::RPT . '-pos-vs-purchase', [ReportController::class, 'posVsPurchaseReport'])->name(VW::RPT . '.pos.vs.purchase')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // User Module

    Route::get('users/{view?}', [UserController::class, 'index'])->name(DatabaseConstants::TABLE_USERS)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('users-view', [UserController::class, 'filterUserView'])->name('filter.user.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('checkuserexists', [UserController::class, 'checkUserExists'])->name(VW::USR . '.exists')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('update.profile')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::USR . '/info/{id}', [UserController::class, 'userInfo'])->name(VW::USR . '.info')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::USR . '/{id}/info/{type}', [UserController::class, 'getProjectTask'])->name(VW::USR . '.info.popup')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name(VW::USR . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // End User Module

    // Search
    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    // end

    //================================= Project Milestones  ====================================//
    #region
    Route::get(VW::PRJ . '/{id}/' . VW::MLS, [ProjectController::class, 'milestone'])->name(VW::ML)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{id}/' . VW::MLS, [ProjectController::class, ProjectController::ML_STR])->name(VW::ML . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/' . VW::MLS . '/{id}/edit', [ProjectController::class, ProjectController::ML_ED])->name(VW::ML . '.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/' . VW::MLS . '/{id}', [ProjectController::class, ProjectController::ML_UPD])->name(VW::ML . '.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::PRJ . '/' . VW::MLS . '/{id}', [ProjectController::class, ProjectController::ML_DST])->name(VW::ML . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/' . VW::MLS . '/{id}/show', [ProjectController::class, ProjectController::ML_SHW])->name(VW::ML . '.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //Route::delete(
    //    '/'.VW::PRJ.'/{id}/users/{uid}', [
    //                                    'as' => VW::PRJ.'.'.VW::USR.'s.destroy',
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

    Route::get('invite-project-member/{id}', [ProjectController::class, 'inviteMemberView'])->name(VW::PRJ . '.invite.member.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('invite-project-user-member', [ProjectController::class, 'inviteProjectUserMember'])->name(VW::PRJ . '.invite.user.member')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::delete(VW::PRJ . '/{id}/users/{uid}', [ProjectController::class, 'destroyProjectUser'])->name(VW::PRJ . '.' . VW::USR . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('project/{view?}', [ProjectController::class, 'index'])->name(VW::PRJ . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('projects-view', [ProjectController::class, 'filterProjectView'])->name('filter.project.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{id}/store-stages/{slug}', [ProjectController::class, 'storeProjectTaskStages'])->name(VW::PRJ . '.stages.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::patch('remove-user-from-project/{project_id}/{user_id}', [ProjectController::class, 'removeUserFromProject'])->name('remove.user.from.project')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('projects-users', [ProjectController::class, 'loadUser'])->name(VW::PRJ . '.user')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/{id}/gantt/{duration?}', [ProjectController::class, 'gantt'])->name(VW::PRJ . '.gantt')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{id}/gantt', [ProjectController::class, 'ganttPost'])->name(VW::PRJ . '.gantt.post')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('projects', ProjectController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // User Permission
    Route::get(VW::PRJ . '/{id}/' . VW::USR . '/{uid}/permission', [ProjectController::class, 'userPermission'])->name(VW::PRJ . '.' . VW::USR . '.permission')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{id}/' . VW::USR . '/{uid}/permission', [ProjectController::class, 'userPermissionStore'])->name(VW::PRJ . '.' . VW::USR . '.' . VW::PMS . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // End Project Module

    // Task Module

    Route::get('stage/{id}/tasks', [ProjectTaskController::class, ProjectTaskController::GET_STG_TSK])->name(VW::PRJ_TSK_C . '.stage')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // Project Task Module

    Route::get(VW::PRJ . '/{id}/' . VW::TSK, [ProjectTaskController::class, 'index'])->name(VW::PRJ_TSK_C . '.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/{pid}/' . VW::TSK . '/{sid}', [ProjectTaskController::class, 'create'])->name(VW::PRJ_TSK_C . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{pid}/' . VW::TSK . '/{sid}', [ProjectTaskController::class, 'store'])->name(VW::PRJ_TSK_C . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/{id}/' . VW::TSK . '/{tid}/show', [ProjectTaskController::class, 'show'])->name(VW::PRJ_TSK_C . '.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/{id}/' . VW::TSK . '/{tid}/edit', [ProjectTaskController::class, 'edit'])->name(VW::PRJ_TSK_C . '.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{id}/' . VW::TSK . '/update/{tid}', [ProjectTaskController::class, 'update'])->name(VW::PRJ_TSK_C . '.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::PRJ . '/{id}/' . VW::TSK . '/{tid}', [ProjectTaskController::class, 'destroy'])->name(VW::PRJ_TSK_C . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::patch(VW::PRJ . '/{id}/' . VW::TSK . '/order', [ProjectTaskController::class, ProjectTaskController::TSK_OD_UPD])->name('tasks.update.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::patch('update-task-priority-color', [ProjectTaskController::class, ProjectTaskController::UPD_TSK_PR_CL])->name('update.task.priority.color')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(VW::PRJ . '/{id}/comment/{tid}/file', [ProjectTaskController::class, ProjectTaskController::CM_STR_F])->name(VW::PRJ_TSK_C . '.comment.store.file')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::PRJ . '/{id}/comment/{tid}/file/{fid}', [ProjectTaskController::class, ProjectTaskController::CM_DST_F])->name(VW::PRJ_TSK_C . '.comment.destroy.file');
    Route::post(VW::PRJ . '/{id}/comment/{tid}', [ProjectTaskController::class, ProjectTaskController::CM_STR])->name(VW::PRJ_TSK_C . '.comment.store');
    Route::delete(VW::PRJ . '/{id}/comment/{tid}/{cid}', [ProjectTaskController::class, ProjectTaskController::CM_DST])->name(VW::PRJ_TSK_C . '.comment.destroy');
    Route::post(VW::PRJ . '/{id}/checklist/{tid}', [ProjectTaskController::class, ProjectTaskController::CHKL_STR])->name(VW::PRJ_TSK_C . '.checklist.store');
    Route::post(VW::PRJ . '/{id}/checklist/update/{cid}', [ProjectTaskController::class, ProjectTaskController::CHKL_UPD])->name(VW::PRJ_TSK_C . '.checklist.update');
    Route::delete(VW::PRJ . '/{id}/checklist/{cid}', [ProjectTaskController::class, ProjectTaskController::CHKL_DST])->name(VW::PRJ_TSK_C . '.checklist.destroy');
    Route::post(VW::PRJ . '/{id}/change/{tid}/fav', [ProjectTaskController::class, ProjectTaskController::CG_FAV])->name(VW::PRJ_TSK_C . '.change.fav');
    Route::post(VW::PRJ . '/{id}/change/{tid}/complete', [ProjectTaskController::class, ProjectTaskController::CG_COM])->name(VW::PRJ_TSK_C . '.change.complete');
    Route::post(VW::PRJ . '/{id}/change/{tid}/progress', [ProjectTaskController::class, ProjectTaskController::CG_PRG])->name(VW::PRJ_TSK_C . 'change.progress');
    Route::get(VW::PRJ . '/' . VW::TSK . '/{id}/get', [ProjectTaskController::class, ProjectTaskController::GET_TSK])->name(VW::PRJ_TSK_C . '.get')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/calendar/{id}/show', [ProjectTaskController::class, ProjectTaskController::CLD_SHW])->name(VW::PRJ_TSK_C . '.calendar.show')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/calendar/{id}/drag', [ProjectTaskController::class, ProjectTaskController::CLD_DRG])->name(VW::PRJ_TSK_C . '.calendar.drag');
    Route::get('calendar/{task}/{pid?}', [ProjectTaskController::class, ProjectTaskController::CLD_VW])->name(VW::PRJ_TSK_C . '.calendar')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::resource('project-task-stages', TaskStageController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project-task-stages/order', [TaskStageController::class, 'order'])->name('project-task-stages.order');

    Route::post(VW::PRJ_TSK_STG . '-new', [TaskStageController::class, 'storingValue'])->name(VW::PRJ_TSK_STG . '.new')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // End Task Module

    //================================= Project Expenses  ====================================//
    #region
    Route::get(VW::PRJ . '/{id}/expenses', [ExpenseController::class, 'index'])->name(VW::PRJ_EXP . '.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/{pid}/' . VW::EXP . '/create', [ExpenseController::class, 'create'])->name(VW::PRJ_EXP . '.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{pid}/' . VW::EXP . '/store', [ExpenseController::class, 'store'])->name(VW::PRJ_EXP . '.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::PRJ . '/{id}/' . VW::EXP . '/{eid}/edit', [ExpenseController::class, 'edit'])->name(VW::PRJ_EXP . '.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::PRJ . '/{id}/' . VW::EXP . '/{eid}', [ExpenseController::class, 'update'])->name(VW::PRJ_EXP . '.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::PRJ . '/{eid}/' . VW::EXP . '/', [ExpenseController::class, 'destroy'])->name(VW::PRJ_EXP . '.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // TODO missing method
    Route::get('/expense-list', [ExpenseController::class, 'expenseList'])->name(VW::EXP . '.list')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
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
            Route::resource(VW::CTC_TP, ContractTypeController::class);
        }
    );

    // Project Timesheet
    Route::get('append-timesheet-task-html', [TimesheetController::class, 'appendTimesheetTaskHTML'])->name('append.timesheet.task.html')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //    Route::get(VW::TMS.'/table-view', [TimesheetController::class, 'filterTimesheetTableView'])->name(VW::TMS.'.filters.table.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('timesheet-view', [TimesheetController::class, 'filterTimesheetView'])->name(VW::TMS . '.filters.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
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
            Route::resource(VW::PRJ_STG, ProjectStagesController::class);
            Route::post(VW::PRJ_STG . '/order', [ProjectStagesController::class, 'order'])->name(VW::PRJ_STG . '.order')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
            Route::post(VW::PRJ . '/' . VW::BUG . '/kanban/order', [ProjectController::class, ProjectController::BUG_KB_OD])->name(VW::PRJ_BUG . '.kanban.order');
            Route::get(VW::PRJ . '/{id}/' . VW::BUG . '/kanban', [ProjectController::class, ProjectController::BUG_KB])->name(VW::PRJ_TSK_BUG . '.kanban');
            Route::get(VW::PRJ . '/{id}/' . VW::BUG, [ProjectController::class, 'bug'])->name(VW::PRJ_TSK_BUG);
            Route::get(VW::PRJ . '/{id}/' . VW::BUG . '/create', [ProjectController::class, ProjectController::BUG_CRT])->name(VW::PRJ_TSK_BUG . '.create');
            Route::post(VW::PRJ . '/{id}/' . VW::BUG . '/store', [ProjectController::class, ProjectController::BUG_ST])->name(VW::PRJ_TSK_BUG . '.store');
            Route::get(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/edit', [ProjectController::class, ProjectController::BUG_EDT])->name(VW::PRJ_TSK_BUG . '.edit');
            Route::post(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/update', [ProjectController::class, ProjectController::BUG_UPD])->name(VW::PRJ_TSK_BUG . '.update');
            Route::delete(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/destroy', [ProjectController::class, ProjectController::BUG_DST])->name(VW::PRJ_TSK_BUG . '.destroy');
            Route::get(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/show', [ProjectController::class, ProjectController::BUG_SHW])->name(VW::PRJ_TSK_BUG . '.show');
            Route::post(VW::PRJ . '/{id}/' . VW::BUG . '/{bid}/comment', [ProjectController::class, ProjectController::BUG_CMT_STR])->name(VW::PRJ_BUG_CM . '.store');
            Route::post(VW::PRJ . '/' . VW::BUG . '/{bid}/file', [ProjectController::class, ProjectController::BUG_CMT_STR_F])->name(VW::PRJ_BUG_CM . '.file.store');
            Route::delete(VW::PRJ . '/' . VW::BUG . '/comment/{id}', [ProjectController::class, ProjectController::BUG_CMT_DST])->name(VW::PRJ_BUG_CM . '.destroy');
            Route::delete(VW::PRJ . '/' . VW::BUG . '/file/{id}', [ProjectController::class, ProjectController::BUG_CMT_DST_F])->name(VW::PRJ_BUG_CM . '.file.destroy');
            Route::resource(VW::BUG_STT, BugStatusController::class);
            Route::post(VW::BUG_STT . '/order', [BugStatusController::class, 'order'])->name(VW::BUG_STT . '.order');
            Route::get(VW::BUG_RPT . '/{view?}', [ProjectTaskController::class, ProjectTaskController::ALL_BUG])->name(VW::PRJ_BUG . '.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
        }
        #endregion
    );

    Route::post(VW::TD . '/create', [UserController::class, UserController::TD_STR])->name(VW::TD . '.store')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::TD . '/{id}/update', [UserController::class, UserController::TD_UPD])->name(VW::TD . '.update')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::TD . '/{id}/delete', [UserController::class, UserController::TD_DEL])->name(VW::TD . '.destroy')
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
            Route::get('contract/{id}/description', [ContractController::class, 'description'])->name(VW::CTC . '.description');
            Route::get('contract/grid', [ContractController::class, 'grid'])->name(VW::CTC . '.grid');
            Route::resource('contract', ContractController::class);
        }
    );
    Route::post('/contract/{id}/file', [ContractController::class, 'fileUpload'])->name(VW::CTC . '.file.upload')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('contract/pdf/{id}', [ContractController::class, 'pdfFromContract'])->name(VW::CTC . '.download.pdf')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('contract/{id}/get_contract', [ContractController::class, 'printContract'])->name(VW::CTC . '.get')->middleware([MiddlewaresConstants::AUTH]);
    Route::post('/contract_status_edit/{id}', [ContractController::class, 'contractStatusEdit'])->name(VW::CTC . '.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('contract/{id}/contract_description', [ContractController::class, 'contractDescriptionStore'])->name(VW::CTC . '.contract_description.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::get('/contract/{id}/file/{fid}', [ContractController::class, 'fileDownload'])->name(VW::CTC . '.file.download')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('/contract/{id}/file/delete/{fid}', [ContractController::class, 'fileDelete'])->name(VW::CTC . '.file.delete')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/contract/copy/{id}', [ContractController::class, 'copyContract'])->name(VW::CTC . '.copy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/contract/copy/store', [ContractController::class, 'copyContractStore'])->name(VW::CTC . '.copy.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('/contract/{id}/mail', [ContractController::class, 'sendmailContract'])->name(VW::CTC . '.send.mail');
    Route::get('/signature/{id}', [ContractController::class, 'signature'])->name(VW::CTC . '.signature')->middleware([MiddlewaresConstants::AUTH]);
    Route::post('/signature-store', [ContractController::class, 'signatureStore'])->name(VW::CTC . '.signature.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/contract/{id}/comment', [ContractController::class, 'commentStore'])->name(VW::CTC . '.comment.store');
    Route::post('/contract/{id}/notes', [ContractController::class, 'noteStore'])->name(VW::CTC . '.note.store')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete('/contract/{id}/notes', [ContractController::class, 'noteDestroy'])->name(VW::CTC . '.note.destroy')->middleware([MiddlewaresConstants::AUTH]);
    Route::delete('/contract/{id}/comment', [ContractController::class, 'commentDestroy'])->name(VW::CTC . '.comment.destroy');
    Route::get('get-projects/{client_id}', [ContractController::class, 'clientByProject'])->name(VW::PRJ . '.by.user.id')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    // client wise project show in modal

    Route::any('/contract/clients/select/{bid}', [ContractController::class, 'clientwiseproject'])->name(VW::CTC . '.clients.select');

    // copy contract

    Route::get('/contract/copy/{id}', [ContractController::class, 'copycontract'])->name(VW::CTC . '.copy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('contract/copy/store', [ContractController::class, 'copycontractstore'])->name(VW::CTC . '.copy.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

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
    Route::post('plan-pay-with-bank', [BankTransferPaymentController::class, 'planPayWithBank'])->name(VW::PLN . '.pay.with.bank')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

    Route::any('/payment/initiate', [BenefitPaymentController::class, 'initiatePayment'])->name(VW::PLN . '.pay.with.benefit');
    Route::any('callBack', [BenefitPaymentController::class, 'callBack'])->name('benefit.callback');

    Route::post('cashfree/payments/store', [CashfreeController::class, 'cashfreePaymentStore'])->name(VW::PLN . '.pay.with.cashfree');
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
            Route::get(VW::SPT . '/{id}/reply', [SupportController::class, 'reply'])->name(VW::SPT . '.reply');
            Route::post(VW::SPT . '/{id}/reply', [SupportController::class, 'replyAnswer'])->name(VW::SPT . '.reply.answer');
            Route::get(VW::SPT . '/grid', [SupportController::class, 'grid'])->name(VW::SPT . '.grid');
            Route::resource(VW::SPT, SupportController::class);
        }
    );
    #endregion

    Route::resource(VW::CPT, CompetenciesController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
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
            Route::resource(VW::PFM_TP, PerformanceTypeController::class);
        }
    );
    #endregion

    //================================= Plan Requests ====================================//
    Route::get(VW::PLN_RQ, [PlanRequestController::class, 'index'])->name(VW::PLN_RQ . '.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_frequency/{id}', [PlanRequestController::class, PlanRequestController::RQ_VW])->name(VW::PLN_RQ . '.request.view')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_send/{id}', [PlanRequestController::class, PlanRequestController::USR_RQ])->name(VW::PLN_RQ . '.request.send')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_response/{id}/{response}', [PlanRequestController::class, PlanRequestController::AC_RQ])->name(VW::PLN_RQ . '.request.response')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('request_cancel/{id}', [PlanRequestController::class, PlanRequestController::CC_RQ])->name(VW::PLN_RQ . '.request.cancel')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    //QR Code Module

    // Import/Export Data Route

    Route::get('export/productservice', [ProductServiceController::class, 'export'])->name(VW::PRD_SV . '.export');
    // Route::get('import'.VW::PRD_SV.//file', [ProductServiceController::class, 'importFile'])->name(VW::PRD_SV . '.file.import');
    Route::post('import/productservice', [ProductServiceController::class, 'import'])->name(VW::PRD_SV . '.import');
    Route::get('export/customer', [CustomerController::class, 'export'])->name(VW::CST . '.export');
    Route::get('import/customer/file', [CustomerController::class, 'importFile'])->name(VW::CST . '.file.import');
    Route::post('import/customer', [CustomerController::class, 'import'])->name(VW::CST . '.import');
    Route::get('export/vendor', [VendorController::class, 'export'])->name('vendor.export');
    Route::get('import/vendor/file', [VendorController::class, 'importFile'])->name('vendor.file.import');
    Route::post('import/vendor', [VendorController::class, 'import'])->name('vendor.import');
    Route::get('export/invoice', [InvoiceController::class, 'export'])->name(VW::INV . '.export');
    Route::get('export/proposal', [ProposalController::class, 'export'])->name(VW::PPS . '.export');
    Route::get('export/bill', [BillController::class, 'export'])->name(VW::BIL . '.export');

    Route::get('export/employee', [EmployeeController::class, 'export'])->name(VW::EMP . '.export');
    Route::get('importVW::EMP.//file', [EmployeeController::class, 'importFile'])->name(VW::EMP . '.file.import');
    Route::post('import/employee', [EmployeeController::class, 'import'])->name(VW::EMP . '.import');

    Route::get('import/attendance/file', [EmployeeAttendanceController::class, 'importFile'])->name('attendance.file.import');
    Route::post('import/attendance', [EmployeeAttendanceController::class, 'import'])->name('attendance.import');

    Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::get(VW::ACC_STT . '/export', [ReportController::class, 'export'])->name(VW::ACC_STT . '.export');
    Route::get(VW::PRD_STK . '/export', [ReportController::class, 'stock_export'])->name(VW::PRD_STK . '.export');
    Route::get('export/payroll', [ReportController::class, 'PayrollReportExport'])->name(VW::RPT . '.payroll.export');
    Route::get('export/leave', [ReportController::class, 'LeaveReportExport'])->name(VW::LV . '.export');

    Route::post('export/payslip', [PayslipController::class, 'export'])->name(VW::PY_SLP . '.export');

    // Time-Tracker
    Route::post('stop-tracker', [DashboardController::class, DashboardController::STP_TRK])->name('stop.tracker')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('time-tracker', [TimeTrackerController::class, 'index'])->name('time.tracker')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('tracker/{tid}/destroy', [TimeTrackerController::class, 'destroy'])->name(VW::TMT . '.destroy');
    Route::post('tracker/image-view', [TimeTrackerController::class, 'getTrackerImages'])->name(VW::TMT . '.image.view');
    Route::delete('tracker/image-remove', [TimeTrackerController::class, 'removeTrackerImages'])->name(VW::TMT . '.image.remove');
    Route::get(VW::PRJ . '/time-tracker/{id}', [ProjectController::class, 'tracker'])->name(VW::PRJ . '.time.tracker')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

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
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/items', [PurchaseController::class, 'items'])->name(VW::PRC . '.items');
            Route::resource(DatabaseConstants::TABLE_PURCHASES, PurchaseController::class);

            //    Route::get('/'.VW::BIL.'{id}/', 'PurchaseController@purchaseLink')->name(VW::PRC.'.link.copy');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment', [PurchaseController::class, 'payment'])
                ->name(VW::PRC . '.payment');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment', [PurchaseController::class, 'createPayment'])
                ->name(VW::PRC . '.payment');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/{id}/payment/{pid}/destroy', [
                PurchaseController::class,
                'paymentDestroy'
            ])->name(VW::PRC . '.payment.destroy');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/product/destroy', [
                PurchaseController::class,
                'productDestroy'
            ])->name(VW::PRC . '.product.destroy');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/vendor', [PurchaseController::class, 'vendor'])
                ->name(VW::PRC . '.vendor');
            Route::post(DatabaseConstants::TABLE_PURCHASES . '/product', [PurchaseController::class, 'product'])
                ->name(VW::PRC . '.product');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/create/{cid}', [PurchaseController::class, 'create'])
                ->name(VW::PRC . '.create');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/sent', [PurchaseController::class, 'sent'])
                ->name(VW::PRC . '.sent');
            Route::get(DatabaseConstants::TABLE_PURCHASES . '/{id}/resent', [PurchaseController::class, 'resent'])
                ->name(VW::PRC . '.resent');
        }

    );
    Route::get('pos-print-setting', [SystemController::class, 'posPrintIndex'])->name(VW::POS . '.print.setting')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(DatabaseConstants::TABLE_PURCHASES . '/preview/{template}/{color}', [PurchaseController::class, PurchaseController::PV_PRC])
        ->name(VW::PRC . '.preview')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::POS . '/preview/{template}/{color}', [PosController::class, PosController::PV_POS])->name(VW::POS . '.preview')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(VW::PRC . '/templates/settings', [PurchaseController::class, PurchaseController::SV_PCR_TMP_STG])
        ->name(VW::PRC_TMP . 'settings');
    Route::post('/pos/template/setting', [PosController::class, PosController::SV_POS_TMP])
        ->name(VW::PRC_TMP . 'settings');

    Route::get(DatabaseConstants::TABLE_PURCHASES . '/pdf/{id}', [PurchaseController::class, 'purchase'])
        ->name(DatabaseConstants::TABLE_PURCHASES . '.pdf')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::get(VW::POS . '/pdf/{id}', [PosController::class, 'pos'])->name(VW::POS . '.pdf')->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);
    Route::get(VW::POS . '/data/store', [PosController::class, 'store'])->name(VW::POS . '.data.store')->middleware([
        MiddlewaresConstants::AUTH,
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::REV
    ]);

    //for pos print
    Route::get('printview/pos', [PosController::class, PosController::PRT_VW])->name(VW::POS . '.printview')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

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
    Route::any(VW::RPT . '/pos', [PosController::class, 'report'])->name(VW::POS . '.report')->middleware([
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
    Route::get('barcode/pos', [PosController::class, 'barcode'])->name(VW::POS . '.barcode')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::SET . '/pos', [PosController::class, 'setting'])->name(VW::POS . '.setting')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('barcode/settings', [PosController::class, 'BarcodesettingStore'])->name('barcode.setting');
    Route::get('print/pos', [PosController::class, 'printBarcode'])->name(VW::POS . '.print')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::POS . '/getproduct', [PosController::class, 'getproduct'])->name(VW::POS . '.getproduct')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('pos-receipt', [PosController::class, 'receipt'])->name(VW::POS . '.receipt')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/cartdiscount', [PosController::class, 'cartdiscount'])->name('cartdiscount')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Storage Setting

    Route::post('storage-settings', [SystemController::class, 'storageSettingStore'])->name(VW::SET . '.storage.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //appricalStar

    Route::post(VW::APR, [AppraisalController::class, 'empByStar'])->name('empByStar')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::APR . '1', [AppraisalController::class, 'empByStar1'])->name('empByStar1')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/getemployee', [AppraisalController::class, 'getEmployee'])->name('getemployee');

    //================================= Offer Letters ====================================//
    #region
    Route::post(VW::SET . '/offer-letter/{lang?}', [SystemController::class, SystemController::OF_LTR_UPD])->name('offer_letter.update');
    Route::get(VW::SET . '/offer-letter', [SystemController::class, SystemController::CP])->name(VW::SET . '.offer_letter.language');
    Route::get(VW::JB_OB . '/pdf/{id}', [JobApplicationController::class, JobApplicationController::OFL_PDF])->name('offer_letter.download.pdf');
    Route::get(VW::JB_OB . '/doc/{id}', [JobApplicationController::class, JobApplicationController::OFL_DC])->name('offer_letter.download.doc');
    #endregion

    //================================= Joining Letters ====================================//
    #region
    Route::post(VW::SET . '/joining-letter/{lang?}', [SystemController::class, SystemController::JN_LTR_UPD])->name('joining_letter.update');
    Route::get(VW::SET . '/joining-letter', [SystemController::class, SystemController::CP])->name(VW::SET . 'joining_letter.language');
    Route::get(VW::EMP . '/pdf/{id}', [EmployeeController::class, EmployeeController::JNL_PDF])->name('joining_letter.download.pdf');
    Route::get(VW::EMP . '/doc/{id}', [EmployeeController::class, EmployeeController::JNL_DOC])->name('joining_letter.download.doc');
    #endregion

    //================================= Experience Certificates ====================================//
    #region
    Route::post(VW::SET . '/exp/{lang?}', [SystemController::class, SystemController::EXP_CT_UPD])->name('experience_certificate.update');
    Route::get(VW::SET . '/exp', [SystemController::class, SystemController::CP])->name(VW::SET . '.experience_certificate.language');
    Route::get(VW::EMP . '/exp-pdf/{id}', [EmployeeController::class, EmployeeController::EC_PDF])->name('exp.download.pdf');
    Route::get(VW::EMP . '/exp-doc/{id}', [EmployeeController::class, EmployeeController::EC_DOC])->name('exp.download.doc');
    #endregion

    //================================= Nocs ====================================//
    #region
    Route::post(VW::SET . '/noc/{lang?}', [SystemController::class, SystemController::NOC_UPD])->name('noc.update');
    Route::get(VW::SET . '/noc', [SystemController::class, SystemController::CP])->name(VW::SET . '.noc.language');
    Route::get(VW::EMP . '/noc-pdf/{id}', [EmployeeController::class, EmployeeController::NOC_PDF])->name('noc.download.pdf');
    Route::get(VW::EMP . '/noc-doc/{id}', [EmployeeController::class, EmployeeController::NOC_DOC])->name('noc.download.doc');
    #endregion


    //Project Reports

    Route::resource('/project_report', ProjectReportController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project_report_data', [ProjectReportController::class, 'ajax_data'])->name(VW::PRJ . '.ajax')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project_report/tasks/{id}', [ProjectReportController::class, 'ajax_tasks_report'])->name('tasks.report.ajaxdata')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('export/task_report/{id}', [ProjectReportController::class, 'export'])->name('project_report.export');

    //project copy module
    Route::get('/project/copy/{id}', [ProjectController::class, 'copyproject'])->name(VW::PRJ . '.copy')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('/project/copy/store/{id}', [ProjectController::class, 'copyprojectstore'])->name(VW::PRJ . '.copy.store')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Google Calendar
    Route::any('event/get_event_data', [EventController::class, 'get_event_data'])->name('event.get_event_data')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::post(VW::SET . '/google-calendar', [SystemController::class, 'saveGooglecalendarSettings'])->name(VW::SET . 'google.calendar');
    Route::any('holiday/get_holiday_data', [HolidayController::class, 'get_holiday_data'])->name('holiday.get_holiday_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('interview-schedule/get_interview_data', [InterviewScheduleController::class, 'get_interview_data'])->name('holiday.get_interview_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('calendar/get_task_data', [ProjectTaskController::class, ProjectTaskController::GET_TSK_D])->name(VW::PRJ_TSK_C . '.calendar.get_task_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::any('zoom-meeting/get_zoom_meeting_data', [ZoomMeetingController::class, 'get_zoom_meeting_data'])->name('zoom-meeting.get_zoom_meeting_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::any('meeting/get_meeting_data', [MeetingController::class, 'get_meeting_data'])->name('meeting.get_meeting_data')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('meeting-calendar', [MeetingController::class, 'calendar'])->name('meeting.calendar')
        ->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    Route::any('event/get_dashboard_event_data', [EventController::class, 'get_dashboard_event_data'])->name('event.get_dashboard_event_data')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //branch wise department get in attendance report
    Route::post(VW::RPT . '-monthly-attendance/getdepartment', [ReportController::class, ReportController::GET_DPT])->name(VW::RPT . '.attendance.getdepartment')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::RPT . '-monthly-attendance/getemployee', [ReportController::class, ReportController::GET_EMP])->name(VW::RPT . '.attendance.getemployee')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //shared project & copy link
    Route::any(VW::PRJ . '/copy/link/{id}', [ProjectController::class, ProjectController::CP_LNK_ST])->name(VW::PRJ . '.copy.link');
    Route::any(VW::PRJ . '/{id}/setting-create', [ProjectController::class, ProjectController::CP_LNK_ST_CRT])->name(VW::PRJ . '.copy_link.setting.create');
    // TODO missing method
    Route::get('share-project/{lang?}', [ProjectController::class, 'shareProject'])->name('share.project');

    //User Log
    Route::get('/userlogs', [UserController::class, 'userLog'])->name(VW::USR . '.' . VW::USR . 'log')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get('userlogs/{id}', [UserController::class, 'userLogView'])->name(VW::USR . '.' . VW::USR . 'logview')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete('userlogs/{id}', [UserController::class, 'userLogDestroy'])->name(VW::USR . '.' . VW::USR . 'logdestroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //notification Template
    Route::get('notification_templates/{id?}/{lang?}', [NotificationTemplatesController::class, 'index'])->name('notification_templates.index')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::resource('notification-templates', NotificationTemplatesController::class)->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Proposal/Invoice/'.VW::BIL.'Purchase/POS - footer notes
    Route::post('system-settings/note', [SystemController::class, 'footerNoteStore'])->name(VW::SYS . '.settings.footernote')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //AI module
    Route::post('chatgpt-settings', [SystemController::class, 'chatgptSetting'])->name(VW::SET . '.chatgpt.settings');
    Route::get('generate/{template_name}', [AiTemplateController::class, 'create'])->name('generate');
    Route::post('generate/keywords/{id}', [AiTemplateController::class, 'getKeywords'])->name('generate.keywords');
    Route::post('generate/response', [AiTemplateController::class, 'AiGenerate'])->name('generate.response');
    Route::get('grammar/{template}', [AiTemplateController::class, 'grammar'])->name('grammar')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post('grammar/response', [AiTemplateController::class, 'grammarProcess'])->name('grammar.response')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //================================= IP Controls ====================================//
    #region
    Route::get(VW::SYS . '/create/ip', [SystemController::class, SystemController::CR_IP])->name(VW::SYS . '.ip.create')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::SYS . '/create/ip', [SystemController::class, SystemController::STR_IP])->name(VW::SYS . '.ip.store')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::get(VW::SYS . '/edit/ip/{id}', [SystemController::class, SystemController::ED_IP])->name(VW::SYS . '.ip.edit')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::post(VW::SYS . '/edit/ip/{id}', [SystemController::class, SystemController::UPD_IP])->name(VW::SYS . '.ip.update')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    Route::delete(VW::SYS . '/destroy/ip/{id}', [SystemController::class, SystemController::DST_IP])->name(VW::SYS . '.ip.destroy')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    #endregion

    //lang enable / disable
    Route::post('disable-language', [LanguageController::class, LanguageController::DSB_LNG])->name('language.disable')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);

    //Expense Module
    Route::get('expense/pdf/{id}', [ExpenseController::class, 'expense'])->name(VW::EXP . '.pdf')->middleware([MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    Route::group(
        [
            'middleware' => [
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::REV,
            ],
        ],
        function () {
            Route::get('expense/index', [ExpenseController::class, 'index'])->name(VW::EXP . '.index');
            Route::any('expense/customer', [ExpenseController::class, 'customer'])->name(VW::EXP . '.customer');
            Route::post('expense/vendor', [ExpenseController::class, 'vendor'])->name(VW::EXP . '.vendor');
            Route::post('expense/employee', [ExpenseController::class, 'employee'])->name(VW::EXP . '.employee');

            Route::post('expense/product/destroy', [ExpenseController::class, 'productDestroy'])->name(VW::EXP . '.product.destroy');

            Route::post('expense/product', [ExpenseController::class, 'product'])->name(VW::EXP . '.product');
            Route::get('expense/{id}/payment', [ExpenseController::class, 'payment'])->name(VW::EXP . '.payment');
            Route::get('expense/items', [ExpenseController::class, 'items'])->name(VW::EXP . '.items');

            Route::resource(VW::EXP, ExpenseController::class);
            Route::get('expense/create/{cid}', [ExpenseController::class, 'create'])->name(VW::EXP . '.create');
        }
    );
});

Route::any('/cookie-consent', [SystemController::class, 'CookieConsent'])->name('cookie-consent');


    // Route::post('{id}/pay-with-paypal', [PaypalController::class, 'customerPayWithPaypal'])->name(VW::CST.'.pay.with.paypal');
    // Route::get('{id}/get-payment-status/{amount}', [PaypalController::class, 'customerGetPaymentStatus'])->name(VW::CST.'.get.payment.status')
    //     ->middleware([MiddlewaresConstants::XSS]);
    // Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name(VW::PLN.'.pay.with.paypal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    // Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name(VW::PLN.'.get.payment.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);


// AUT, XSS, REV GROUP

    //    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name(VW::PLN.'.pay.with.paypal')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);
    //    Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name(VW::PLN.'.get.payment.status')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS, MiddlewaresConstants::REV]);

//     Route::post('invoice-with-aamarpay', [AamarpayController::class, 'invoicepaywithaamarpay'])->name(VW::CST.'.pay.with.aamarpay');
//     Route::any('aamarpay-invoice/success/{data}', [AamarpayController::class, 'getInvoicePaymentStatus'])->name(VW::INV . '.pay.aamarpay.success');
    
//     Route::post('/customer-pay-with-coingate', [CoingatePaymentController::class, 'customerPayWithCoingate'])->name(VW::CST.'.pay.with.coingate')->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/coingate/{invoice}/{amount}', [CoingatePaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.coingate');
    
//     Route::post('/customer-pay-with-paytm', [PaytmPaymentController::class, 'customerPayWithPaytm'])->name(VW::CST.'.pay.with.paytm')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::post('/customer/paytm/{invoice}/{amount}', [PaytmPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.paytm');
    
//     Route::post('/customer-pay-with-flutterwave', [FlutterwavePaymentController::class, 'customerPayWithFlutterwave'])->name(VW::CST.'.pay.with.flutterwave')->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/flutterwave/{txref}/{invoice_id}', [FlutterwavePaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.flutterwave');
    
//     Route::post('/customer-pay-with-razorpay', [RazorpayPaymentController::class, 'customerPayWithRazorpay'])->name(VW::CST.'.pay.with.razorpay')->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/razorpay/{txref}/{invoice_id}', [RazorpayPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.razorpay');
    
//     Route::post('/customer-pay-with-mercado', [MercadoPaymentController::class, 'customerPayWithMercado'])->name(VW::CST.'.pay.with.mercado')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/mercado/{invoice}', [MercadoPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.mercado');
    
//     Route::post('/customer-pay-with-mollie', [MolliePaymentController::class, 'customerPayWithMollie'])->name(VW::CST.'.pay.with.mollie')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/mollie/{invoice}/{amount}', [MolliePaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.mollie');
    
//     Route::post('/customer-pay-with-skrill', [SkrillPaymentController::class, 'customerPayWithSkrill'])->name(VW::CST.'.pay.with.skrill')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::get('/customer/skrill/{invoice}/{amount}', [SkrillPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.skrill');
    
//     Route::post('/paymentwall', [PaymentWallPaymentController::class, 'invoicepaymentwall'])->name(VW::INV . '.paymentwallpayment')
//         ->middleware([MiddlewaresConstants::XSS]);
//     Route::post('/invoice-pay-with-paymentwall/{invoice}', [PaymentWallPaymentController::class, 'invoicePayWithPaymentwall'])
//         ->name(VW::INV . '.pay.with.paymentwall')->middleware([MiddlewaresConstants::XSS]);
//     Route::get(VW::INV.'/{flag}/{invoice}', [PaymentWallPaymentController::class, 'invoiceerror'])->name('error.invoice.show');
    
//     Route::post('/customer-pay-with-toyyibpay', [ToyyibpayController::class, 'invoicepaywithtoyyibpay'])->name(VW::CST.'.pay.with.toyyibpay');
//     Route::get('/customer/toyyibpay/{invoice}/{amount}', [ToyyibpayController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.toyyibpay');
    
//     Route::post('invoice-with-payfast', [PayFastController::class, 'invoicePayWithPayFast'])->name(VW::INV . '.with.payfast');
//     Route::get('invoice-payfast-status/{success}', [PayFastController::class, 'invoicepayfaststatus'])->name(VW::INV . '.payfast.status');
    
//     Route::post('/customer-pay-with-iyzipay', [IyziPayController::class, 'invoicepaywithiyzipay'])->name(VW::CST.'.pay.with.iyzipay');
//     Route::post('iyzipay/callback/{invoice}/{amount}', [IyzipayController::class, 'getInvoiceiyzipayCallback'])
//         ->name('iyzipay.invoicepayment.callback');
    
//     Route::post('/customer-pay-with-sspay', [SspayController::class, 'invoicepaywithsspaypay'])->name(VW::CST.'.pay.with.sspay');
//     Route::get('/customer/sspay/{invoice}/{amount}', [SspayController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.sspay');
    
//     Route::post('/invoice-pay-with-paytab', [PaytabController::class, 'invoicePayWithpaytab'])->name(VW::CST.'.pay.with.paytab');
//     Route::any('/invoice-paytab-success/{invoice}', [PaytabController::class, 'getInvoicePaymentStatus'])->name(VW::INV . '.paytab.success');
    
//     Route::post('/invoice-with-paytr', [PaytrController::class, 'invoicepaywithpaytr'])->name(VW::CST.'.pay.with.paytr');
//     Route::get('/invoice/paytr/status', [PaytrController::class, 'getInvoicePaymentStatus'])->name(VW::INV . '.paytr');
    
//     Route::post('invoice-with-yookassa/', [YooKassaController::class, 'invoicePayWithYookassa'])->name(VW::CST.'.with.yookassa');
//     Route::any('invoice-yookassa-status/', [YooKassaController::class, 'getInvociePaymentStatus'])->name(VW::INV . '.yookassa.status');
    
//     Route::any('invoice-with-midtrans/', [MidtransPaymentController::class, 'invoicePayWithMidtrans'])->name(VW::CST.'.with.midtrans');
//     Route::any('invoice-midtrans-status/', [MidtransPaymentController::class, 'getInvociePaymentStatus'])->name(VW::INV . '.midtrans.status');
    
//     Route::any('/invoice-with-xendit', [XenditPaymentController::class, 'invoicePayWithXendit'])->name(VW::CST.'.with.xendit');
//     Route::any('/invoice-xendit-status', [XenditPaymentController::class, 'getInvociePaymentStatus'])->name(VW::INV . '.xendit.status');
// // Invoice Payment Gateways
// Route::post('customer/{id}/payment', [StripePaymentController::class, 'addpayment'])->name(VW::CST.'.payment');

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

// Route::post('/customer-pay-with-paystack', [PaystackPaymentController::class, 'customerPayWithPaystack'])->name(VW::CST.'.pay.with.paystack')->middleware([MiddlewaresConstants::XSS]);
// Route::get('/customer/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvoicePaymentStatus'])->name(VW::CST.'.paystack');

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

    // Route::post('/aamarpay/payment', [AamarpayController::class, 'pay'])->name(VW::PLN.'.pay.with.aamarpay');
    // Route::any('/aamarpay/success/{data}', [AamarpayController::class, 'aamarpaysuccess'])->name('pay.aamarpay.success');

    // Route::post('/paytr/payment/{plan_id}', [PaytrController::class, 'PlanpayWithPaytr'])->name(VW::PLN.'.pay.with.paytr');
    // Route::get('/paytr/sussess/', [PaytrController::class, 'paytrsuccess'])->name('pay.paytr.success');

    // Route::post('/plan/yookassa/payment', [YooKassaController::class, 'planPayWithYooKassa'])->name(VW::PLN.'.pay.with.yookassa');
    // Route::get('/plan/yookassa/{plan}', [YooKassaController::class, 'planGetYooKassaStatus'])->name(VW::PLN.'.yookassa.status');

    // Route::any('/midtrans', [MidtransPaymentController::class, 'planPayWithMidtrans'])->name(VW::PLN.'.pay.with.midtrans');
    // Route::any('/midtrans/callback', [MidtransPaymentController::class, 'planGetMidtransStatus'])->name(VW::PLN.'.get.midtrans.status');

    // Route::any('/xendit/payment', [XenditPaymentController::class, 'planPayWithXendit'])->name(VW::PLN.'.pay.with.xendit');
    // Route::any('/xendit/payment/status', [XenditPaymentController::class, 'planGetXenditStatus'])->name(VW::PLN.'.xendit.status');

    // Route::post('/plan-pay-with-flutterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name(VW::PLN.'.pay.with.flutterwave')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/flutterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.flutterwave');

    // Route::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->name(VW::PLN.'.pay.with.razorpay')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/razorpay/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.razorpay');

    // Route::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->name(VW::PLN.'.pay.with.paytm')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::post('/plan/paytm/{plan}', [PaytmPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.paytm');

    // Route::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->name(VW::PLN.'.pay.with.mercado')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/mercado/{plan}/{amount}', [MercadoPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.mercado');

    // Route::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name(VW::PLN.'.pay.with.mollie')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.mollie');

    // Route::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name(VW::PLN.'.pay.with.skrill')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/skrill/{plan}', [SkrillPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.skrill');

    // Route::post('/plan-pay-with-coingate', [CoingatePaymentController::class, 'planPayWithCoingate'])->name(VW::PLN.'.pay.with.coingate')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/coingate/{plan}', [CoingatePaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.coingate');

    // Route::post('/toyyibpay', [ToyyibpayController::class, 'planPayWithToyyibpay'])->name(VW::PLN.'.toyyibpaypayment');
    // Route::get('/plan-pay-with-toyyibpay/{id}/{status}/{coupon}', [ToyyibpayController::class, 'getPaymentStatus'])->name(VW::PLN.'.status');

    // Route::post('payfast-plan', [PayFastController::class, 'planPayWithPayfast'])->name('payfast.payment');
    // Route::get('payfast-plan/{success}', [PayFastController::class, 'getPaymentStatus'])->name('payfast.payment.success');

    // Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
    // Route::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

    // Route::post('/sspay', [SspayController::class, 'SspayPaymentPrepare'])->name(VW::PLN.'.sspaypayment');
    // Route::get('sspay-payment-plan/{plan_id}/{amount}/{couponCode}', [SspayController::class, 'SspayPlanGetPayment'])->middleware([MiddlewaresConstants::AUTH])->name(VW::PLN.'.sspay.callback');

    // Route::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithpaytab'])->middleware([MiddlewaresConstants::AUTH])->name(VW::PLN.'.pay.with.paytab');
    // Route::any('paytab-success/plan', [PaytabController::class, 'PaytabGetPayment'])->middleware([MiddlewaresConstants::AUTH])->name(VW::PLN.'.paytab.success');

    // Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name(VW::PLN.'.pay.with.paystack')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name(VW::PLN.'.paystack');

    // // PaymentWall

    // Route::post('/paymentwalls', [PaymentWallPaymentController::class, 'paymentwall'])->name(VW::PLN.'.paymentwallpayment')->middleware([MiddlewaresConstants::XSS]);
    // Route::post('/plan-pay-with-paymentwall/{plan}', [PaymentWallPaymentController::class, 'planPayWithPaymentWall'])->name(VW::PLN.'.pay.with.paymentwall')->middleware([MiddlewaresConstants::AUTH, MiddlewaresConstants::XSS]);
    // Route::get('/plan/{flag}', [PaymentWallPaymentController::class, 'planeerror'])->name('error.plan.show');