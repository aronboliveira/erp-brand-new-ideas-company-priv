<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Models\OperationalEvent;
use App\Services\Reliability\{CompensationExecutorService, DispatchOrchestrationService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use Tests\TestCase;

#[CoversClass(DispatchOrchestrationService::class)]
#[Group('services')]
#[Group('reliability')]
class DispatchOrchestrationServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function orchestrator_runs_selected_domains_and_compensation(): void
    {
        $seenLimits = [];
        $service = new DispatchOrchestrationService(
            compensation: new class extends CompensationExecutorService {
                public function executePending(int $limit = 50, ?string $domain = null): array
                {
                    return [
                        'processed' => $limit,
                        'compensated' => $domain === 'finance' ? 1 : 0,
                        'failed' => 0,
                        'skipped' => 0,
                        'reports' => [$domain ?? 'all'],
                    ];
                }
            },
            dispatchers: [
                'finance' => function (int $limit) use (&$seenLimits): array {
                    $seenLimits['finance'] = $limit;

                    return [
                        'processed' => 2,
                        'dispatched' => 2,
                        'failed' => 0,
                        'dead_letter' => 0,
                        'skipped' => 0,
                        'reports' => [],
                    ];
                },
                'hrm' => function (int $limit) use (&$seenLimits): array {
                    $seenLimits['hrm'] = $limit;

                    return [
                        'processed' => 1,
                        'dispatched' => 1,
                        'failed' => 0,
                        'dead_letter' => 0,
                        'skipped' => 0,
                        'reports' => [],
                    ];
                },
            ],
        );

        $report = $service->run([
            'domains' => ['finance', 'hrm'],
            'limit' => 7,
            'compensation_limit' => 3,
        ]);

        $this->assertSame('completed', $report['status']);
        $this->assertSame(['finance', 'hrm'], $report['domains']);
        $this->assertSame(['finance' => 7, 'hrm' => 7], $seenLimits);
        $this->assertSame(3, $report['summary']['processed']);
        $this->assertSame(3, $report['summary']['dispatched']);
        $this->assertSame(6, $report['summary']['compensation_processed']);
        $this->assertSame(0, $report['summary']['compensation_failed']);
        $this->assertTrue(OperationalEvent::where('event_type', 'reliability.dispatch_orchestration.completed')->exists());
    }

    #[Test]
    public function orchestrator_can_stop_after_attention_report(): void
    {
        $called = [];
        $service = new DispatchOrchestrationService(
            dispatchers: [
                'finance' => function () use (&$called): array {
                    $called[] = 'finance';

                    return [
                        'processed' => 1,
                        'dispatched' => 0,
                        'failed' => 1,
                        'dead_letter' => 0,
                        'skipped' => 0,
                        'reports' => [],
                    ];
                },
                'hrm' => function () use (&$called): array {
                    $called[] = 'hrm';

                    return [
                        'processed' => 1,
                        'dispatched' => 1,
                        'failed' => 0,
                        'dead_letter' => 0,
                        'skipped' => 0,
                        'reports' => [],
                    ];
                },
            ],
        );

        $report = $service->run([
            'domains' => ['finance', 'hrm'],
            'include_compensation' => false,
            'stop_on_failure' => true,
        ]);

        $this->assertSame('completed_with_attention', $report['status']);
        $this->assertSame(['finance'], $called);
        $this->assertSame(['finance'], $report['domains']);
        $this->assertSame(1, $report['summary']['failed']);
    }

    #[Test]
    public function command_passes_options_to_orchestrator(): void
    {
        $fake = new class extends DispatchOrchestrationService {
            public array $receivedOptions = [];

            public function __construct()
            {
            }

            public function run(array $options = []): array
            {
                $this->receivedOptions = $options;

                return [
                    'status' => 'completed',
                    'summary' => [],
                    'reports' => [],
                ];
            }
        };
        $this->app->instance(DispatchOrchestrationService::class, $fake);

        $exitCode = Artisan::call('reliability:orchestrate-dispatch', [
            '--domain' => ['finance', 'warehouse'],
            '--limit' => 9,
            '--compensation-limit' => 4,
            '--skip-compensation' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(['finance', 'warehouse'], $fake->receivedOptions['domains']);
        $this->assertSame(9, $fake->receivedOptions['limit']);
        $this->assertSame(4, $fake->receivedOptions['compensation_limit']);
        $this->assertFalse($fake->receivedOptions['include_compensation']);
        $this->assertStringContainsString('completed', Artisan::output());
    }
}
