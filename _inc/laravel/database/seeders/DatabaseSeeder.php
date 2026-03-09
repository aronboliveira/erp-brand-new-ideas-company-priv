<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\Indicator;
use App\Models\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Artisan, Log, Route};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class DatabaseSeeder extends Seeder
{
    private const LP = 'LandingPage';
    public function run(): void
    {
        // Allow seeders to mass-assign guarded attributes (created_by, id, etc.)
        Model::unguard();
        $output = new ConsoleOutput();
        $output->writeln('<info>Starting the database seeding process</info>');
        $this->call(NotificationSeeder::class);
        Artisan::call('module:migrate ' . self::LP);
        Artisan::call('module:seed ' . self::LP);
        $startTime = microtime(true);
        if ($this->shouldSeedStandardTables()) {
            $output->writeln('<info>Starting at ' . date('Y-m-d H:i:s') . '</info>');
            foreach ([PlansTableSeeder::class, UsersTableSeeder::class, AiTemplateSeeder::class] as $seeder)
                $this->call($seeder);
            $this->runMocks();
            $finishedIn = round(microtime(true) - $startTime, 2);
            $finishedAt = date('Y-m-d H:i:s');
            $output->writeln('<info>Finished at ' . $finishedAt . '</info>');
            $output->writeln('<info>Duration: ' . $finishedIn . ' seconds</info>');
            Log::info('Database seeding completed in ' . $finishedIn . ' seconds, at ' . $finishedAt . ', taking a total of ' . ($finishedIn * 1000) . ' milliseconds');
        } else Utility::languageCreate();
        Model::reguard();
    }
    private function shouldSeedStandardTables(): bool
    {
        if (app()->runningInConsole()) return true;
        return Route::currentRouteName() !== 'LaravelUpdater::database';
    }
    private function runMocks(): void
    {
        $lastSeeder = null;
        $output = new ConsoleOutput();
        try {
            try {
                $output->writeln('<info>Seeding: UserSeeder (initial phase)</info>');
                $startTime = microtime(true);
                $this->call(UserSeeder::class, silent: false, parameters: ['phase' => 'initial']);
                $lastSeeder = UserSeeder::class;
                $output->writeln('<info>Seeding mocks for: ' . UserSeeder::class . '</info>');
                $endTime = microtime(true);
                $duration = $endTime - $startTime;
                Log::warning('Seeder ' . UserSeeder::class . ' (initial) completed in ' . round($duration, 2) . ' segundos.');
                Log::notice('Mock Seeder ' . UserSeeder::class . ' executed successfully.');
                sleep(1);
            } catch (\Exception $e) {
                $output->writeln('<error>Seeding mocks for UserSeeder (initial phase) failed: ' . substr($e->getMessage(), 0, 1024) . '</error>');
                Log::warning('Seeding mocks for UserSeeder (initial phase) failed: ', ['message' => substr($e->getMessage(), 0, 1024)]);
            }
            foreach (
                [
                    BranchSeeder::class,
                    DepartmentSeeder::class,
                    DesignationSeeder::class,
                ] as $mockSeeder
            ) {
                try {
                    $output->writeln('<info>Seeding: ' . $mockSeeder . '</info>');
                    $startTime = microtime(true);
                    $this->call($mockSeeder);
                    $lastSeeder = $mockSeeder;
                    $output->writeln('<info>Seeding mocks for: ' . $mockSeeder . '</info>');
                    Log::notice('Mock Seeder ' . $mockSeeder . ' executed successfully.');
                    $endTime = microtime(true);
                    $duration = $endTime - $startTime;
                    Log::warning('Seeder ' . $mockSeeder . ' completed in ' . round($duration, 2) . ' segundos.');
                    sleep(1);
                } catch (\Exception $e) {
                    $output->writeln('<error>Seeding mocks for ' . $mockSeeder . ' failed: ' . substr($e->getMessage(), 0, 1024) . '</error>');
                    Log::warning('Seeding mocks for ' . $mockSeeder . ' failed: ', ['message' => substr($e->getMessage(), 0, 1024)]);
                }
            }
            try {
                $output->writeln('<info>Seeding: ' . UserSeeder::class . ' (additional phase)</info>');
                $startTime = microtime(true);
                $this->call(UserSeeder::class, silent: false, parameters: ['phase' => 'additional']);
                $lastSeeder = UserSeeder::class;
                $output->writeln('<info>Seeding mocks for: ' . UserSeeder::class . '</info>');
                Log::notice('Mock Seeder ' . UserSeeder::class . ' executed successfully.');
                $endTime = microtime(true);
                $duration = $endTime - $startTime;
                Log::warning('Seeder ' . UserSeeder::class . ' (late) completed in ' . round($duration, 2) . ' segundos.');
                sleep(1);
            } catch (\Exception $e) {
                $output->writeln('<error>Seeding mocks for UserSeeder (additional phase) failed: ' . substr($e->getMessage(), 0, 1024) . '</error>');
                Log::warning('Seeding mocks for UserSeeder (additional phase) failed: ', ['message' => substr($e->getMessage(), 0, 1024)]);
            }
            foreach (
                [
                    ScheduleSeeder::class,
                    BranchSeeder::class,
                    DepartmentSeeder::class,
                    ClientSeeder::class,
                    PasswordResetsSeeder::class,
                    PipelineSeeder::class,
                    PlanSeeder::class,
                    DocumentSeeder::class,
                    NotificationTemplatesSeeder::class,
                    NotificationTemplateLangsSeeder::class,
                    NotificationsLateSeeder::class,
                    EmailTemplatesSeeder::class,
                    EmailTemplateLangsSeeder::class,
                    UserEmailTemplatesSeeder::class,
                    CustomFieldsSeeder::class,
                    CustomFieldValuesSeeder::class,
                    CustomQuestionSeeder::class,
                    ProjectSeeder::class,
                    ProjectStagesSeeder::class,
                    ProjectUserSeeder::class,
                    ProjectEmailTemplateSeeder::class,
                    SourceSeeder::class,
                    StageSeeder::class,
                    LabelSeeder::class,
                    AppraisalSeeder::class,
                    IndicatorSeeder::class,
                    CompanyPolicySeeder::class,
                    GoalTypesSeeder::class,
                    GoalsSeeder::class,
                    GoalTrackingsSeeder::class,
                    JobCategoriesSeeder::class,
                    DealSeeder::class,
                    DealFileSeeder::class,
                    DealDiscussionSeeder::class,
                    UserDealSeeder::class,
                    ClientDealSeeder::class,
                    TaxSeeder::class,
                    BugSeeder::class,
                    BugFileSeeder::class,
                    BugCommentSeeder::class,
                    BugStatusSeeder::class,
                    PayslipTypeSeeder::class,
                    EmployeeSeeder::class,
                    EmployeeDocumentSeeder::class,
                    NoteSeeder::class,
                    EmailSeeder::class,
                    UserContactSeeder::class,
                    MilestoneSeeder::class,
                    TaskSeeder::class,
                    DealTaskSeeder::class,
                    TaskStageSeeder::class,
                    TaskChecklistSeeder::class,
                    TaskCommentSeeder::class,
                    TaskFileSeeder::class,
                    UserToDoSeeder::class,
                    TrainingTypeSeeder::class,
                    TrainerSeeder::class,
                    TrainingSeeder::class,
                    AwardTypeSeeder::class,
                    AwardSeeder::class,
                    TerminationTypeSeeder::class,
                    TerminationSeeder::class,
                    ResignationSeeder::class,
                    TravelSeeder::class,
                    PromotionSeeder::class,
                    TransferSeeder::class,
                    WarningSeeder::class,
                    ComplaintSeeder::class,
                    JobStageSeeder::class,
                    JobSeeder::class,
                    JobApplicationSeeder::class,
                    JobApplicationNoteSeeder::class,
                    JobOnBoardSeeder::class,
                    CompetencySeeder::class,
                    AllowanceOptionSeeder::class,
                    LoanOptionSeeder::class,
                    DeductionOptionSeeder::class,
                    SetSalarySeeder::class,
                    AllowanceSeeder::class,
                    LoanSeeder::class,
                    SaturationDeductionSeeder::class,
                    OvertimeSeeder::class,
                    EmployeeAttendanceSeeder::class,
                    OtherPaymentSeeder::class,
                    ContractTypeSeeder::class,
                    ContractSeeder::class,
                    ContractAttachmentSeeder::class,
                    ContractCommentSeeder::class,
                    ContractNoteSeeder::class,
                    PerformanceTypeSeeder::class,
                    PayslipSeeder::class,
                    ProjectTaskSeeder::class,
                    TimesheetSeeder::class,
                    ChartOfAccountTypeSeeder::class,
                    ChartOfAccountSubTypeSeeder::class,
                    ChartOfAccountSeeder::class,
                    ProductServiceCategorySeeder::class,
                    ProductCategorySeeder::class,
                    ProductServiceSeeder::class,
                    ProductServiceUnitSeeder::class,
                    ProductSeeder::class,
                    CustomerSeeder::class,
                    BankAccountSeeder::class,
                    BankTransferSeeder::class,
                    VendorSeeder::class,
                    ClientPermissionSeeder::class,
                    AnnouncementSeeder::class,
                    InterviewScheduleSeeder::class,
                    OrderSeeder::class,
                    CouponSeeder::class,
                    UserCouponSeeder::class,
                    PlanRequestSeeder::class,
                    LeaveTypeSeeder::class,
                    LeaveSeeder::class,
                    MeetingSeeder::class,
                    MeetingEmployeeSeeder::class,
                    ZoomMeetingSeeder::class,
                    EventSeeder::class,
                    EventEmployeeSeeder::class,
                    BillSeeder::class,
                    InvoiceSeeder::class,
                    PaymentSeeder::class,
                    BillAccountSeeder::class,
                    BillPaymentSeeder::class,
                    BillProductSeeder::class,
                    InvoicePaymentSeeder::class,
                    ProjectInvoiceSeeder::class,
                    RevenueSeeder::class,
                    WarehouseSeeder::class,
                    PosSeeder::class,
                    PosPaymentSeeder::class,
                    TransactionSeeder::class,
                    AssetsSeeder::class,
                    CreditNoteSeeder::class,
                    DebitNoteSeeder::class,
                    InvoiceBankTransferSeeder::class,
                    ExpenseSeeder::class,
                    LeadStageSeeder::class,
                    LeadSeeder::class,
                    LeadActivityLogSeeder::class,
                    LeadDiscussionSeeder::class,
                    UserLeadSeeder::class,
                    LeadEmailSeeder::class,
                    LeadFileSeeder::class,
                    LeadCallSeeder::class,
                    DealEmailSeeder::class,
                    DealCallSeeder::class,
                    EstimationSeeder::class,
                    ProposalSeeder::class,
                    ProposalProductSeeder::class,
                    PurchaseSeeder::class,
                    PurchasePaymentSeeder::class,
                    InvoiceProductSeeder::class,
                    PosProductSeeder::class,
                    WarehouseProductSeeder::class,
                    HolidaySeeder::class,
                    TimeTrackerSeeder::class,
                    PlanningScheduleSeeder::class,
                    WarehouseTransferSeeder::class,
                    BasicFavoritesSeeder::class,
                    JournalEntrySeeder::class,
                    JournalItemSeeder::class,
                    BudgetSeeder::class,
                    FormBuilderSeeder::class,
                    FormFieldSeeder::class,
                    FormFieldResponseSeeder::class,
                    FormResponseSeeder::class,
                    SupportSeeder::class,
                    SupportReplySeeder::class,
                    TrackPhotoSeeder::class,
                    LogActivitySeeder::class,
                    ActivityLogSeeder::class,
                    StockReportSeeder::class,
                    DashboardSeeder::class,
                    AccountStatementSeeder::class,
                ] as $mockSeeder
            ) {
                try {
                    $output->writeln('<info>Seeding: ' . $mockSeeder . '</info>');
                    $startTime = microtime(true);
                    $this->call($mockSeeder);
                    $lastSeeder = $mockSeeder;
                    $output->writeln('<info>Seeding mocks for: ' . $mockSeeder . '</info>');
                    Log::notice('Mock Seeder ' . $mockSeeder . ' executed successfully.');
                    $endTime = microtime(true);
                    $duration = $endTime - $startTime;
                    $rowCount = '#NULL';
                    try {
                        $modelName = Str::singular(str_replace('Late', '', preg_replace('/Seeder$/', '', class_basename($lastSeeder))));
                        $modelName = match (class_basename($lastSeeder)) {
                            'PosSeeder' => 'Pos',
                            default => $modelName,
                        };
                        $modelClass = "\\App\\Models\\{$modelName}";
                        if (!class_exists($modelClass)) {
                            $rowCount = '#MODEL_NOT_FOUND';
                            Log::debug('Model class not found after seeding', [
                                'seeder' => $lastSeeder,
                                'model' => $modelName,
                            ]);
                        } else {
                            $modelInstance = new $modelClass;
                            if (!method_exists($modelInstance, 'getTable')) {
                                try {
                                    $rowCount = $modelClass::count();
                                } catch (\Throwable $e) {
                                    $rowCount = '#MODEL_COUNT_UNAVAILABLE';
                                }
                            } else {
                                try {
                                    $tableName = $modelInstance->getTable();
                                    $rowCount = DB::table($tableName)->count();
                                } catch (\Throwable $e) {
                                    try {
                                        $rowCount = $modelClass::count();
                                    } catch (\Throwable $e2) {
                                        $rowCount = '#TABLE_COUNT_UNAVAILABLE';
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::debug('Failed to get row count for model after seeding', [
                            'seeder' => $lastSeeder,
                            'model' => $modelName ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);
                        $rowCount = '#ERROR';
                    }
                    Log::warning('Seeder ' . $mockSeeder . ' completed in ' . round($duration, 2) . ' segundos, criando ' . Str::singular(preg_replace('/Seeder$/', '', class_basename($lastSeeder))) . ' records. Count: ' . $rowCount);
                    sleep(1);
                } catch (\Throwable $e) {
                    $output->writeln('<error>Seeding mocks for ' . $mockSeeder . ' failed: ' . substr($e->getMessage(), 0, 1024) . '</error>');
                    Log::warning('Seeding mocks for ' . $mockSeeder . ' failed: ', ['message' => substr($e->getMessage(), 0, 1024)]);
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Seeding mocks failed: ' . $e->getMessage() . ' in ' . $lastSeeder . '</error>');
            Log::warning('Seeding mocks failed: ', ['message' => $e->getMessage(), 'seeder' => $lastSeeder]);
        }
        // Re-seed leads: they get wiped during seeding by an unidentified cascade
        try {
            $output->writeln('<comment>Re-seeding leads (post-seed fix)...</comment>');
            $this->call(LeadSeeder::class);
        } catch (\Throwable $e) {
            Log::warning('Post-seed LeadSeeder re-run failed: ' . $e->getMessage());
        }

        // Final pass: ensure every content-validated route has at least some data
        try {
            $output->writeln('<info>Running ContentValidationSeeder (gap-fill)...</info>');
            $this->call(ContentValidationSeeder::class);
        } catch (\Throwable $e) {
            Log::warning('ContentValidationSeeder failed: ' . $e->getMessage());
        }
    }
}
