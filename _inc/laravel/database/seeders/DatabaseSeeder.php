<?php

namespace Database\Seeders;

use App\Models\Indicator;
use App\Models\Utility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Artisan, Log, Route};
use Symfony\Component\Console\Output\ConsoleOutput;

class DatabaseSeeder extends Seeder
{
    private const LP = 'LandingPage';
    public function run(): void
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Starting the database seeding process</info>');
        $this->call(NotificationSeeder::class);
        Artisan::call('module:migrate ' . self::LP);
        Artisan::call('module:seed ' . self::LP);
        $startTime = microtime(true);
        if ($this->shouldSeedStandardTables()) {
            $output->writeln('<info>Starting at ' . date('Y-m-d H:i:s') . '</info>');
            foreach ([UsersTableSeeder::class, PlansTableSeeder::class, AiTemplateSeeder::class] as $seeder)
                $this->call($seeder);
            $this->runMocks();
            $finishedIn = round(microtime(true) - $startTime, 2);
            $finishedAt = date('Y-m-d H:i:s');
            $output->writeln('<info>Finished at ' . $finishedAt . '</info>');
            $output->writeln('<info>Duration: ' . $finishedIn . ' seconds</info>');
            Log::info('Database seeding completed in ' . $finishedIn . ' seconds, at ' . $finishedAt . ', taking a total of ' . ($finishedIn * 1000) . ' milliseconds');
        } else Utility::languageCreate();
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
                    BranchSeeder::class,
                    DepartmentSeeder::class,
                    ClientSeeder::class,
                    PasswordResetsSeeder::class,
                    DocumentSeeder::class,
                    PipelineSeeder::class,
                    PlanSeeder::class,
                    NotificationTemplatesSeeder::class,
                    NotificationTemplateLangsSeeder::class,
                    NotificationsLateSeeder::class,
                    EmailTemplatesSeeder::class,
                    EmailTemplateLangsSeeder::class,
                    UserEmailTemplatesSeeder::class,
                    CustomFieldsSeeder::class,
                    CustomFieldValuesSeeder::class,
                    ProjectSeeder::class,
                    ProjectStagesSeeder::class,
                    SourceSeeder::class,
                    StageSeeder::class,
                    LabelSeeder::class,
                    AppraisalSeeder::class,
                    IndicatorSeeder::class,
                    CompanyPolicySeeder::class,
                    GoalTypesSeeder::class,
                    GoalsSeeder::class,
                    GoalTrackingsSeeder::class,
                    DealSeeder::class,
                    DealFileSeeder::class,
                    DealTaskSeeder::class,
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
                    MilestoneSeeder::class,
                    TaskSeeder::class,
                    TaskStageSeeder::class,
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
                    PerformanceTypeSeeder::class,
                    PayslipSeeder::class,
                    ChartOfAccountTypeSeeder::class,
                    ChartOfAccountSubTypeSeeder::class,
                    ChartOfAccountSeeder::class,
                    ProductServiceCategorySeeder::class,
                    ProductServiceSeeder::class,
                    ProductServiceUnitSeeder::class,
                    CustomerSeeder::class,
                    BankAccountSeeder::class,
                    BankTransferSeeder::class,
                    VendorSeeder::class,
                    AnnouncementSeeder::class,
                    OrderSeeder::class,
                    CouponSeeder::class,
                    UserCouponSeeder::class,
                    LeaveTypeSeeder::class,
                    LeaveSeeder::class,
                    MeetingSeeder::class,
                    MeetingEmployeeSeeder::class,
                    EventSeeder::class,
                    EventEmployeeSeeder::class,
                    BillSeeder::class,
                    InvoiceSeeder::class,
                    BillProductSeeder::class,
                    InvoicePaymentSeeder::class,
                    RevenueSeeder::class,
                    WarehouseSeeder::class,
                    PosSeeder::class,
                    PaymentSeeder::class,
                    PosPaymentSeeder::class,
                    InvoiceProductSeeder::class,
                    BillPaymentSeeder::class,
                    TransactionSeeder::class,
                    AssetsSeeder::class,
                    CreditNoteSeeder::class,
                    DebitNoteSeeder::class,
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
                    BasicFavoritesSeeder::class,
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
        } catch (\Exception $e) {
            $output->writeln('<error>Seeding mocks failed: ' . $e->getMessage() . ' in ' . $lastSeeder . '</error>');
            Log::warning('Seeding mocks failed: ', ['message' => $e->getMessage(), 'seeder' => $lastSeeder]);
        }
    }
}
