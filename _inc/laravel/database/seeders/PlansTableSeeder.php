<?php

namespace Database\Seeders;

use App\Config\Constants\{
    DatabaseConstants,
    PlansConstants,
    SeedersTemplating
};
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\{Facades\Log, Str};
use Symfony\Component\Console\Output\ConsoleOutput;

class PlansTableSeeder extends Seeder
{
    public $planId = '';
    public function run(): void
    {
        $uuids = [];
        $output = new ConsoleOutput();
        $output->writeln('');
        $output->writeln('<question>------------- ### Plans Seeding ### ------------- </question>');
        $output->writeln('<info> Seeding Plans with ' . static::class . '</info>');
        $planAcc = 0;
        do {
            if ($planAcc > 100_000) {
                Log::warning(SeedersTemplating::SCAPE_MSG);
                break;
            }
            $planId = (string) Str::uuid();
            $planAcc += 1;
        } while (
            in_array($planId, $uuids, true) ||
            in_array($planId, [
                DatabaseConstants::DEFAULT_PLAN,
                DatabaseConstants::DEFAULT_PIPELINE,
                DatabaseConstants::DEFAULT_UUID
            ], true)
        );
        Plan::create(
            [
                'id' => $planId,
                PlansConstants::COL_NM => 'Free Plan',
                PlansConstants::COL_PC => 0,
                PlansConstants::COL_DUR => 'lifetime',
                PlansConstants::COL_MAX_U => 5,
                PlansConstants::COL_MAX_CR => 5,
                PlansConstants::COL_MAX_V => 5,
                PlansConstants::COL_MAX_CL => 5,
                PlansConstants::COL_SL => 1024,
                PlansConstants::COL_CRM => 1,
                PlansConstants::COL_HRM => 1,
                PlansConstants::COL_ACC => 1,
                PlansConstants::COL_PJ => 1,
                PlansConstants::COL_POS => 1,
                PlansConstants::COL_GPT => 1,
                PlansConstants::COL_IMG => 'free_plan.png',
            ]
        );
        $output->writeln('<info>                                 Done creating Plans!</info>');
        $output->writeln('<question>----------- End of Plans Seeding ------------</question>');
        $output->writeln('');
    }
}
