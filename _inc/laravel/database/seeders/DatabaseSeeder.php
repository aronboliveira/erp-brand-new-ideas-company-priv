<?php

namespace Database\Seeders;

use App\Models\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Artisan, DB, Log, Route};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Dynamic DatabaseSeeder — profile-driven orchestration.
 *
 * Controls:
 *   SEED_PROFILE  = minimal | standard (default) | full
 *   SEED_ONLY     = Comma-separated seeder basenames to run exclusively
 *   SEED_SKIP     = Comma-separated seeder basenames to exclude
 *
 * CLI:
 *   php artisan db:seed                          → standard profile
 *   SEED_PROFILE=minimal php artisan db:seed     → fast CI
 *   SEED_PROFILE=full php artisan db:seed        → exhaustive (hours)
 *   SEED_ONLY=BillSeeder,InvoiceSeeder php artisan db:seed  → targeted
 *
 * Backup locations (NEVER modify):
 *   _inc/.seeders/             — long-version originals
 *   .backup/database/seeders/  — mirror copy
 */
class DatabaseSeeder extends Seeder
{
    private const LP = 'LandingPage';

    /** @var string[] Valid profile names */
    private const PROFILES = ['minimal', 'standard', 'full'];

    public function run(): void
    {
        Model::unguard();
        $output = new ConsoleOutput();
        $profile = $this->resolveProfile();
        $output->writeln("<info>Database seeding — profile: {$profile}</info>");
        $output->writeln('<info>Starting at ' . date('Y-m-d H:i:s') . '</info>');

        $startTime = microtime(true);

        // Phase 1: Foundation (always)
        $this->runFoundation($output);

        // Phase 2: Module migrations
        Artisan::call('module:migrate ' . self::LP);
        Artisan::call('module:seed ' . self::LP);

        // Phase 3: Profile-driven mocks
        if ($this->shouldSeedStandardTables()) {
            $this->runUserBootstrap($output);
            $this->runProfileSeeders($profile, $output);
            $this->runPostSeed($profile, $output);
        } else {
            Utility::languageCreate();
        }

        $duration = round(microtime(true) - $startTime, 2);
        $output->writeln("<info>Finished at " . date('Y-m-d H:i:s') . "</info>");
        $output->writeln("<info>Duration: {$duration} seconds</info>");
        Log::info("Database seeding completed [{$profile}] in {$duration}s");

        Model::reguard();
    }

    /* ------------------------------------------------------------------ */
    /*  Profile Resolution                                                */
    /* ------------------------------------------------------------------ */

    private function resolveProfile(): string
    {
        // Environment variable
        $env = strtolower(trim((string) env('SEED_PROFILE', 'standard')));
        return in_array($env, self::PROFILES, true) ? $env : 'standard';
    }

    /** @return string[] */
    private function resolveOnlyList(): array
    {
        $raw = env('SEED_ONLY', '');
        return $raw ? array_filter(array_map('trim', explode(',', (string) $raw))) : [];
    }

    /** @return string[] */
    private function resolveSkipList(): array
    {
        $raw = env('SEED_SKIP', '');
        return $raw ? array_filter(array_map('trim', explode(',', (string) $raw))) : [];
    }

    /* ------------------------------------------------------------------ */
    /*  Foundation Phase                                                   */
    /* ------------------------------------------------------------------ */

    private function runFoundation(ConsoleOutput $output): void
    {
        /** @var class-string<Seeder>[] $foundation */
        $foundation = config('seeders.foundation', [
            NotificationSeeder::class,
            PlansTableSeeder::class,
            UsersTableSeeder::class,
            AiTemplateSeeder::class,
        ]);

        foreach ($foundation as $seeder) {
            $this->safeSeed($seeder, $output);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  User Bootstrap Phase (phased UserSeeder + org structure)           */
    /* ------------------------------------------------------------------ */

    private function runUserBootstrap(ConsoleOutput $output): void
    {
        /** @var array{initial?: class-string<Seeder>, org_structure?: class-string<Seeder>[], additional?: class-string<Seeder>} $cfg */
        $cfg = config('seeders.user_bootstrap');
        if (!$cfg) {
            $cfg = [
                'initial' => UserSeeder::class,
                'org_structure' => [
                    BranchSeeder::class,
                    DepartmentSeeder::class,
                    DesignationSeeder::class,
                ],
                'additional' => UserSeeder::class,
            ];
        }

        // Initial user phase
        if (isset($cfg['initial'])) {
            $this->safeSeed($cfg['initial'], $output, 'initial phase', ['phase' => 'initial']);
        }

        // Org structure
        if (isset($cfg['org_structure'])) {
            foreach ((array) $cfg['org_structure'] as $seeder) {
                $this->safeSeed($seeder, $output);
            }
        }

        // Additional user phase
        if (isset($cfg['additional'])) {
            $this->safeSeed($cfg['additional'], $output, 'additional phase', ['phase' => 'additional']);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Profile-driven Mock Seeders                                       */
    /* ------------------------------------------------------------------ */

    private function runProfileSeeders(string $profile, ConsoleOutput $output): void
    {
        if ($profile === 'minimal') {
            $output->writeln('<comment>Minimal profile — skipping mock seeders</comment>');
            return;
        }

        $seeders = $this->getProfileSeeders($profile);
        $onlyList = $this->resolveOnlyList();
        $skipList = $this->resolveSkipList();

        // Apply SEED_ONLY filter
        if (!empty($onlyList)) {
            $seeders = array_filter($seeders, function (string $seeder) use ($onlyList): bool {
                $base = class_basename($seeder);
                return in_array($base, $onlyList, true)
                    || in_array(str_replace('Seeder', '', $base), $onlyList, true);
            });
            $output->writeln('<comment>SEED_ONLY active — running ' . count($seeders) . ' seeder(s)</comment>');
        }

        // Apply SEED_SKIP filter
        if (!empty($skipList)) {
            $seeders = array_filter($seeders, function (string $seeder) use ($skipList): bool {
                $base = class_basename($seeder);
                return !in_array($base, $skipList, true)
                    && !in_array(str_replace('Seeder', '', $base), $skipList, true);
            });
            $output->writeln('<comment>SEED_SKIP active — ' . count($skipList) . ' seeder(s) excluded</comment>');
        }

        foreach ($seeders as $seeder) {
            $this->safeSeed($seeder, $output, logged: true);
        }
    }

    /** @return class-string<Seeder>[] */
    private function getProfileSeeders(string $profile): array
    {
        /** @var class-string<Seeder>[]|string $list */
        $list = config("seeders.{$profile}", []);

        // Full inherits standard + extras
        if ($profile === 'full') {
            $base = is_string($list) ? config("seeders.{$list}", []) : $list;
            $extras = config('seeders.full_extras', []);
            return array_merge(is_array($base) ? $base : [], $extras);
        }

        return is_array($list) ? $list : [];
    }

    /* ------------------------------------------------------------------ */
    /*  Post-seed Fixups                                                  */
    /* ------------------------------------------------------------------ */

    private function runPostSeed(string $profile, ConsoleOutput $output): void
    {
        if ($profile === 'minimal') {
            return;
        }

        // Lead re-seed: they get wiped during seeding by an unidentified cascade
        try {
            $output->writeln('<comment>Re-seeding leads (post-seed fix)...</comment>');
            $this->call(LeadSeeder::class);
        } catch (\Throwable $e) {
            Log::warning('Post-seed LeadSeeder re-run failed: ' . $e->getMessage());
        }

        // Content validation gap-fill (standard + full)
        try {
            $output->writeln('<info>Running ContentValidationSeeder (gap-fill)...</info>');
            $this->call(ContentValidationSeeder::class);
        } catch (\Throwable $e) {
            Log::warning('ContentValidationSeeder failed: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                           */
    /* ------------------------------------------------------------------ */

    private function shouldSeedStandardTables(): bool
    {
        if (app()->runningInConsole()) return true;
        return Route::currentRouteName() !== 'LaravelUpdater::database';
    }

    /**
     * Safely seed a single seeder with timing, error handling, and optional row counting.
     *
     * @param  class-string<Seeder> $seeder  Fully-qualified seeder class
     * @param  ConsoleOutput        $output
     * @param  string|null          $label   Optional label override for output
     * @param  array<string,mixed>  $parameters Extra parameters for the seeder
     * @param  bool                 $logged  Whether to log row counts
     */
    private function safeSeed(
        string $seeder,
        ConsoleOutput $output,
        ?string $label = null,
        array $parameters = [],
        bool $logged = false,
    ): void {
        $name = $label ? class_basename($seeder) . " ({$label})" : class_basename($seeder);
        try {
            $output->writeln("<info>Seeding: {$name}</info>");
            $start = microtime(true);

            if (!empty($parameters)) {
                $this->call($seeder, silent: false, parameters: $parameters);
            } else {
                $this->call($seeder);
            }

            $duration = round(microtime(true) - $start, 2);
            Log::notice("Seeder {$name} completed in {$duration}s.");

            if ($logged) {
                $this->logRowCount($seeder, $duration);
            }

            sleep(1);
        } catch (\Throwable $e) {
            $msg = substr($e->getMessage(), 0, 1024);
            $output->writeln("<error>Seeder {$name} failed: {$msg}</error>");
            Log::warning("Seeder {$name} failed: ", ['message' => $msg]);
        }
    }

    /**
     * Attempt to count rows for the model associated with a seeder.
     */
    private function logRowCount(string $seeder, float $duration): void
    {
        $rowCount = '#NULL';
        try {
            $modelName = Str::singular(str_replace(
                'Late',
                '',
                preg_replace('/Seeder$/', '', class_basename($seeder)) ?? ''
            ));
            $modelName = match (class_basename($seeder)) {
                'PosSeeder' => 'Pos',
                default     => $modelName,
            };
            /** @var class-string $modelClass */
            $modelClass = "\\App\\Models\\{$modelName}";

            if (!class_exists($modelClass)) {
                $rowCount = '#MODEL_NOT_FOUND';
            } else {
                $instance = new $modelClass;
                if (method_exists($instance, 'getTable')) {
                    try {
                        $rowCount = DB::table($instance->getTable())->count();
                    } catch (\Throwable) {
                        try { $rowCount = $modelClass::count(); } catch (\Throwable) { $rowCount = '#TABLE_UNAVAILABLE'; }
                    }
                } else {
                    try { $rowCount = $modelClass::count(); } catch (\Throwable) { $rowCount = '#COUNT_UNAVAILABLE'; }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Row count failed', ['seeder' => $seeder, 'error' => $e->getMessage()]);
            $rowCount = '#ERROR';
        }

        Log::warning(
            class_basename($seeder) . " completed in {$duration}s — "
            . Str::singular(preg_replace('/Seeder$/', '', class_basename($seeder)) ?? '')
            . " count: {$rowCount}"
        );
    }
}
