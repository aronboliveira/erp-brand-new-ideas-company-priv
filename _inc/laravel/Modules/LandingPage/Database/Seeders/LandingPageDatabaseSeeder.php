<?php

namespace Modules\LandingPage\Database\Seeders;

use Illuminate\Database\{Eloquent\Model, Seeder};
use Nwidart\Modules\Facades\Module;
use Modules\LandingPage\Entities\LandingPageSetting;

class LandingPageDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(LandingPageDataTableSeeder::class);
    }
}
