<?php

namespace Database\Seeders;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PlansConstants as PLC,
    SeedersTemplating
};
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\{Facades\Log, Str};
use Symfony\Component\Console\Output\ConsoleOutput;

class PlansTableSeeder extends Seeder
{
    protected string $planId = '';
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
                DC::DEFAULT_PLAN,
                DC::DEFAULT_PIPELINE,
                DC::DEFAULT_UUID
            ], true)
        );
        Plan::create(
            [
                'query_key' => $planId,
                PLC::COL_NM => 'Free Plan',
                PLC::COL_PC => 0,
                PLC::COL_DUR => 'lifetime',
                PLC::COL_MAX_U => 5,
                PLC::COL_MAX_CR => 5,
                PLC::COL_MAX_V => 5,
                PLC::COL_MAX_CL => 5,
                PLC::COL_SL => 1024,
                PLC::COL_CRM => 1,
                PLC::COL_HRM => 1,
                PLC::COL_ACC => 1,
                PLC::COL_PJ => 1,
                PLC::COL_POS => 1,
                PLC::COL_GPT => 1,
                PLC::COL_IMG => 'free_plan.png',
            ]
        );
        $output->writeln('<info>                                 Done creating Plans!</info>');
        $output->writeln('<question>----------- End of Plans Seeding ------------</question>');
        $output->writeln('');
    }
}
