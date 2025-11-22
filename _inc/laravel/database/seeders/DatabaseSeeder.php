<?php

namespace Database\Seeders;

use App\Models\Utility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Artisan, Log, Route};

class DatabaseSeeder extends Seeder
{
    private const LP = 'LandingPage';
    public function run(): void
    {
        $this->call(NotificationSeeder::class);
        Artisan::call('module:migrate ' . self::LP);
        Artisan::call('module:seed ' . self::LP);
        if ($this->shouldSeedStandardTables()) {
            foreach ([UsersTableSeeder::class, PlansTableSeeder::class, AiTemplateSeeder::class] as $seeder)
                $this->call($seeder);
            $this->runMocks();
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
                    BasicFavoritesSeeder::class
                ] as $mockSeeder
            ) {
                $this->call($mockSeeder);
                $lastSeeder = $mockSeeder;
                Log::notice('Mock Seeder ' . $mockSeeder . ' executed successfully.');
            }
        } catch (\Exception $e) {
            Log::warning('Seeding mocks failed: ', ['message' => $e->getMessage(), 'seeder' => $lastSeeder]);
        }
    }
}
