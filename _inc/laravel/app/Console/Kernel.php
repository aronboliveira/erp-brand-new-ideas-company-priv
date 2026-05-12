<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('reliability.dispatch_orchestration.enabled', false)) {
            $parameters = [
                '--limit' => max(1, (int) config('reliability.dispatch_orchestration.limit', 50)),
                '--compensation-limit' => max(1, (int) config('reliability.dispatch_orchestration.compensation_limit', 25)),
            ];

            if (!config('reliability.dispatch_orchestration.include_compensation', true)) {
                $parameters['--skip-compensation'] = true;
            }

            $event = $schedule->command('reliability:orchestrate-dispatch', $parameters)
                ->everyMinute()
                ->withoutOverlapping(max(1, (int) config('reliability.dispatch_orchestration.without_overlapping_minutes', 10)))
                ->name('reliability.dispatch_orchestration');

            if (config('reliability.dispatch_orchestration.on_one_server', false)) {
                $event->onOneServer();
            }

            if (config('reliability.dispatch_orchestration.run_in_background', false)) {
                $event->runInBackground();
            }
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
