<?php

namespace Database\Seeders;

use App\Models\Utility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Artisan, Log, Route};
use Symfony\Component\Console\Output\ConsoleOutput;

class DatabaseSeeder extends Seeder
{
    private const LP = 'LandingPage';
    public function run(): void
    {
        $this->call(NotificationSeeder::class);
        Artisan::call('module:migrate ' . self::LP);
        Artisan::call('module:seed ' . self::LP);
        $output = new ConsoleOutput();
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
            foreach (
                [
                    ClientSeeder::class,
                    PasswordResetsSeeder::class,
                    DocumentSeeder::class,
                    PipelineSeeder::class,
                    PlanSeeder::class,
                    ProjectSeeder::class,
                    ProjectStagesSeeder::class,
                    SourceSeeder::class,
                    StageSeeder::class,
                    LabelSeeder::class,
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
                    BranchSeeder::class,
                    DepartmentSeeder::class,
                    DesignationSeeder::class,
                    PayslipTypeSeeder::class,
                    EmployeeSeeder::class,
                    EmployeeDocumentSeeder::class,
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
                    CreditNoteSeeder::class,
                    DebitNoteSeeder::class,
                    BasicFavoritesSeeder::class
                ] as $mockSeeder
            ) {
                try {
                    $output->writeln('<info>Seeding: ' . $mockSeeder . '</info>');
                    $this->call($mockSeeder);
                    $lastSeeder = $mockSeeder;
                    $output->writeln('<info>Seeding mocks for: ' . $mockSeeder . '</info>');
                    Log::notice('Mock Seeder ' . $mockSeeder . ' executed successfully.');
                    sleep(2);
                } catch (\Exception $e) {
                    $output->writeln('<error>Seeding mocks for ' . $mockSeeder . ' failed: ' . $e->getMessage() . '</error>');
                    Log::warning('Seeding mocks for ' . $mockSeeder . ' failed: ', ['message' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Seeding mocks failed: ' . $e->getMessage() . ' in ' . $lastSeeder . '</error>');
            Log::warning('Seeding mocks failed: ', ['message' => $e->getMessage(), 'seeder' => $lastSeeder]);
        }
    }
}
