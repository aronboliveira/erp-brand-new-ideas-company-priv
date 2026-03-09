<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\PermissionsConstants;
use App\Config\Constants\UsersConstants;
use App\Models\User;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash, Log};
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Idempotent seeder that fills empty "catalog" tables with default data and
 * ensures at least one user of each essential type exists.
 *
 * Run standalone:  php artisan db:seed --class=ContentValidationSeeder
 *
 * Design principles:
 *  - Every table check uses `count() === 0` before delegating to the proper seeder.
 *  - Uses `firstOrCreate`/`updateOrCreate` inside child seeders for row-level idempotency.
 *  - Failures in one section are caught and logged — they do not abort the rest.
 */
final class ContentValidationSeeder extends Seeder
{
    use EnsuresSystemUser;

    private ConsoleOutput $out;

    // ── Catalog tables → seeder class ────────────────────────────────────
    private const CATALOG_SEEDERS = [
        // Foundation / org structure
        'plans'                       => PlansTableSeeder::class,
        'schedules'                   => ScheduleSeeder::class,
        'branches'                    => BranchSeeder::class,
        'departments'                 => DepartmentSeeder::class,
        'designations'                => DesignationSeeder::class,
        'pipelines'                   => PipelineSeeder::class,
        'sources'                     => SourceSeeder::class,
        'stages'                      => StageSeeder::class,
        'labels'                      => LabelSeeder::class,

        // Type / category tables
        'leave_types'                 => LeaveTypeSeeder::class,
        'bug_statuses'                => BugStatusSeeder::class,
        'payslip_types'               => PayslipTypeSeeder::class,
        'contract_types'              => ContractTypeSeeder::class,
        'termination_types'           => TerminationTypeSeeder::class,
        'award_types'                 => AwardTypeSeeder::class,
        'training_types'              => TrainingTypeSeeder::class,
        'goal_types'                  => GoalTypesSeeder::class,
        'performance_types'           => PerformanceTypeSeeder::class,
        'allowance_options'           => AllowanceOptionSeeder::class,
        'loan_options'                => LoanOptionSeeder::class,
        'deduction_options'           => DeductionOptionSeeder::class,
        'job_stages'                  => JobStageSeeder::class,
        'lead_stages'                 => LeadStageSeeder::class,
        'taxes'                       => TaxSeeder::class,

        // Chart of accounts
        'chart_of_account_types'      => ChartOfAccountTypeSeeder::class,
        'chart_of_account_sub_types'  => ChartOfAccountSubTypeSeeder::class,
        'chart_of_accounts'           => ChartOfAccountSeeder::class,

        // Product / service catalogs
        'product_service_categories'  => ProductServiceCategorySeeder::class,
        'product_service_units'       => ProductServiceUnitSeeder::class,

        // Templates
        'notification_templates'      => NotificationTemplatesSeeder::class,
        'notification_template_langs' => NotificationTemplateLangsSeeder::class,
        'email_templates'             => EmailTemplatesSeeder::class,
        'email_template_langs'        => EmailTemplateLangsSeeder::class,
        'custom_fields'               => CustomFieldsSeeder::class,
    ];

    // ── Required user types ──────────────────────────────────────────────
    private const REQUIRED_TYPES = [
        PermissionsConstants::SA,
        PermissionsConstants::CPN,
    ];

    // ─────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->out = new ConsoleOutput();
        $this->info('ContentValidationSeeder — starting');

        $this->ensureSystemUserExists();
        $this->seedEmptyCatalogs();
        $this->ensureRequiredUserTypes();

        $this->info('ContentValidationSeeder — done');
    }

    // ── Private helpers ──────────────────────────────────────────────────

    /**
     * Guarantee the fallback system user exists (uses trait).
     */
    private function ensureSystemUserExists(): void
    {
        try {
            $id = $this->ensureSystemUser();
            $this->info("  System user ensured: {$id}");
        } catch (\Throwable $e) {
            $this->warn("  System user check failed: {$e->getMessage()}");
        }
    }

    /**
     * For each catalog table, run its seeder only when the table is empty.
     */
    private function seedEmptyCatalogs(): void
    {
        $seeded  = 0;
        $skipped = 0;

        foreach (self::CATALOG_SEEDERS as $table => $seederClass) {
            try {
                if (!$this->tableExists($table)) {
                    $this->warn("  Table '{$table}' does not exist — skipped");
                    continue;
                }
                if (DB::table($table)->count() > 0) {
                    $skipped++;
                    continue;
                }
                $this->info("  Seeding empty table: {$table}");
                $this->call($seederClass);
                $seeded++;
            } catch (\Throwable $e) {
                $this->warn("  Failed seeding {$table}: {$e->getMessage()}");
                Log::warning("ContentValidationSeeder: {$table} — {$e->getMessage()}");
            }
        }

        $this->info("  Catalogs — seeded: {$seeded}, skipped (non-empty): {$skipped}");
    }

    /**
     * Ensure at least one user of each required type exists.
     */
    private function ensureRequiredUserTypes(): void
    {
        $creatorId = DC::DEFAULT_UUID;

        foreach (self::REQUIRED_TYPES as $type) {
            try {
                $exists = User::where(UsersConstants::COL_TP, $type)->exists();
                if ($exists) {
                    $this->info("  User type '{$type}' — exists");
                    continue;
                }
                User::firstOrCreate(
                    [UsersConstants::COL_TP => $type, UsersConstants::COL_EM => strtolower($type) . '@contentvalidation.local'],
                    [
                        UsersConstants::COL_NM => ucfirst($type) . ' Validation User',
                        'password'             => Hash::make('ContentVal!d@t10n'),
                        DC::COL_TABLE_CREATOR  => $creatorId,
                        'lang'                 => DC::DEFAULT_LANG,
                    ]
                );
                $this->info("  User type '{$type}' — created placeholder");
            } catch (\Throwable $e) {
                $this->warn("  User type '{$type}' check failed: {$e->getMessage()}");
                Log::warning("ContentValidationSeeder: user type {$type} — {$e->getMessage()}");
            }
        }
    }

    // ── Console helpers ──────────────────────────────────────────────────

    private function info(string $msg): void
    {
        $this->out->writeln("<info>{$msg}</info>");
        Log::info($msg);
    }

    private function warn(string $msg): void
    {
        $this->out->writeln("<comment>{$msg}</comment>");
        Log::warning($msg);
    }

    private function tableExists(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
