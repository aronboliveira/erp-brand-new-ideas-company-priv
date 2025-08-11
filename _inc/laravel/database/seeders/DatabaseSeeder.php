<?php

namespace Database\Seeders;

use App\Models\Utility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

class DatabaseSeeder extends Seeder
{
    private const LP = 'LandingPage';
    public function run(): void
    {
        $this->call(NotificationSeeder::class);
        Artisan::call('module:migrate ' . self::LP);
        Artisan::call('module:seed ' . self::LP);
        if ($this->shouldSeedStandardTables()) {
            $this->call(UsersTableSeeder::class);
            $this->call(PlansTableSeeder::class);
            $this->call(AiTemplateSeeder::class);
        } else Utility::languageCreate();
    }
    private function shouldSeedStandardTables(): bool
    {
        if (app()->runningInConsole()) return true;
        return Route::currentRouteName() !== 'LaravelUpdater::database';
    }
}
