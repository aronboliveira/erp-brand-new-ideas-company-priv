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
            foreach ([ClientSeeder::class, ProjectSeeder::class, ProjectStagesSeeder::class, SourceSeeder::class, StageSeeder::class] as $mockSeeder) {
                $this->call($mockSeeder);
                $lastSeeder = $mockSeeder;
                Log::notice('Mock Seeder ' . $mockSeeder . ' executed successfully.');
            }
        } catch (\Exception $e) {
            Log::warning('Seeding mocks failed: ', ['message' => $e->getMessage(), 'seeder' => $lastSeeder]);
        }
    }
}
