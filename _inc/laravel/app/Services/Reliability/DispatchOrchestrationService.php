<?php

namespace App\Services\Reliability;

use InvalidArgumentException;
use Throwable;

class DispatchOrchestrationService
{
    public const DOMAIN_FINANCE = 'finance';
    public const DOMAIN_HRM = 'hrm';
    public const DOMAIN_WAREHOUSE = 'warehouse';
    public const DOMAIN_CRM = 'crm';
    public const DOMAIN_PLANNING = 'planning';
    public const DOMAIN_HEAVY_IO = 'heavy_io';

    public const DOMAINS = [
        self::DOMAIN_FINANCE,
        self::DOMAIN_HRM,
        self::DOMAIN_WAREHOUSE,
        self::DOMAIN_CRM,
        self::DOMAIN_PLANNING,
        self::DOMAIN_HEAVY_IO,
    ];

    private CompensationExecutorService $compensation;

    private OperationalEventService $events;

    /**
     * @var array<string, callable(int): array<string, mixed>>
     */
    private array $dispatchers;

    /**
     * @param array<string, callable(int): array<string, mixed>> $dispatchers
     */
    public function __construct(
        ?CompensationExecutorService $compensation = null,
        ?OperationalEventService $events = null,
        array $dispatchers = [],
    ) {
        $this->compensation = $compensation ?? new CompensationExecutorService();
        $this->events = $events ?? new OperationalEventService();
        $this->dispatchers = array_merge($this->defaultDispatchers(), $dispatchers);
    }

    /**
     * @return array<string, mixed>
     */
    public function run(array $options = []): array
    {
        $domains = $this->normalizeDomains($options['domains'] ?? []);
        $limit = max(1, (int) ($options['limit'] ?? 50));
        $compensationLimit = max(1, (int) ($options['compensation_limit'] ?? 25));
        $includeCompensation = (bool) ($options['include_compensation'] ?? true);
        $stopOnFailure = (bool) ($options['stop_on_failure'] ?? false);
        $startedAt = now();

        $this->events->record('reliability.dispatch_orchestration.started', 'Reliability dispatch orchestration started', [
            'domains' => $domains,
            'limit' => $limit,
            'compensation_limit' => $compensationLimit,
            'include_compensation' => $includeCompensation,
        ], $this->eventOptions('notice'));

        $reports = [];
        $summary = $this->emptySummary();

        foreach ($domains as $domain) {
            try {
                $report = ($this->dispatchers[$domain])($limit);
            } catch (Throwable $throwable) {
                $report = [
                    'processed' => 0,
                    'dispatched' => 0,
                    'failed' => 1,
                    'dead_letter' => 0,
                    'skipped' => 0,
                    'reports' => [[
                        'status' => 'failed',
                        'domain' => $domain,
                        'exception' => $throwable::class,
                        'message' => $throwable->getMessage(),
                    ]],
                ];
            }

            $reports[$domain] = $report;
            $summary = $this->mergeSummary($summary, $report);

            if ($stopOnFailure && $this->reportNeedsAttention($report)) {
                break;
            }
        }

        $compensationReport = null;
        if ($includeCompensation) {
            $compensationReport = $this->runCompensation($domains, $compensationLimit);
            $summary['compensation_processed'] = (int) ($compensationReport['processed'] ?? 0);
            $summary['compensation_failed'] = (int) ($compensationReport['failed'] ?? 0);
        }

        $status = $this->summaryNeedsAttention($summary) ? 'completed_with_attention' : 'completed';
        $result = [
            'status' => $status,
            'started_at' => $startedAt->toIso8601String(),
            'finished_at' => now()->toIso8601String(),
            'domains' => array_keys($reports),
            'limit' => $limit,
            'summary' => $summary,
            'reports' => $reports,
            'compensation' => $compensationReport,
        ];

        $this->events->record('reliability.dispatch_orchestration.' . $status, 'Reliability dispatch orchestration finished', $result, $this->eventOptions($status === 'completed' ? 'info' : 'warning'));

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public function domains(): array
    {
        return self::DOMAINS;
    }

    /**
     * @return array<string, callable(int): array<string, mixed>>
     */
    private function defaultDispatchers(): array
    {
        return [
            self::DOMAIN_FINANCE => fn(int $limit): array => (new FinanceOutboxDispatcher())->dispatchPending($limit),
            self::DOMAIN_HRM => fn(int $limit): array => (new HrmOutboxDispatcher())->dispatchPending($limit),
            self::DOMAIN_WAREHOUSE => fn(int $limit): array => (new WarehouseOutboxDispatcher())->dispatchPending($limit),
            self::DOMAIN_CRM => fn(int $limit): array => (new CrmOutboxDispatcher())->dispatchPending($limit),
            self::DOMAIN_PLANNING => fn(int $limit): array => (new PlanningOutboxDispatcher())->dispatchPending($limit),
            self::DOMAIN_HEAVY_IO => fn(int $limit): array => (new HeavyIoOutboxDispatcher())->dispatchPending($limit),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function normalizeDomains(mixed $domains): array
    {
        if ($domains === null || $domains === '' || $domains === []) {
            return self::DOMAINS;
        }

        if (is_string($domains)) {
            $domains = explode(',', $domains);
        }

        if (!is_array($domains)) {
            throw new InvalidArgumentException('Dispatch orchestration domains must be a string or array.');
        }

        $normalized = [];
        foreach ($domains as $domain) {
            $domain = strtolower(trim((string) $domain));
            if ($domain === '') {
                continue;
            }
            $domain = str_replace('-', '_', $domain);
            if (!in_array($domain, self::DOMAINS, true)) {
                throw new InvalidArgumentException("Unknown reliability dispatch domain [{$domain}].");
            }
            $normalized[] = $domain;
        }

        return array_values(array_unique($normalized)) ?: self::DOMAINS;
    }

    /**
     * @param array<int, string> $domains
     * @return array<string, mixed>
     */
    private function runCompensation(array $domains, int $limit): array
    {
        if (count($domains) === count(self::DOMAINS)) {
            return $this->compensation->executePending($limit);
        }

        $reports = [];
        $processed = 0;
        $compensated = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($domains as $domain) {
            $report = $this->compensation->executePending($limit, $domain);
            $reports[$domain] = $report;
            $processed += (int) ($report['processed'] ?? 0);
            $compensated += (int) ($report['compensated'] ?? 0);
            $failed += (int) ($report['failed'] ?? 0);
            $skipped += (int) ($report['skipped'] ?? 0);
        }

        return [
            'processed' => $processed,
            'compensated' => $compensated,
            'failed' => $failed,
            'skipped' => $skipped,
            'reports' => $reports,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptySummary(): array
    {
        return [
            'processed' => 0,
            'dispatched' => 0,
            'failed' => 0,
            'dead_letter' => 0,
            'skipped' => 0,
            'compensation_processed' => 0,
            'compensation_failed' => 0,
        ];
    }

    /**
     * @param array<string, int> $summary
     * @param array<string, mixed> $report
     * @return array<string, int>
     */
    private function mergeSummary(array $summary, array $report): array
    {
        foreach (['processed', 'dispatched', 'failed', 'dead_letter', 'skipped'] as $key) {
            $summary[$key] += (int) ($report[$key] ?? 0);
        }

        return $summary;
    }

    /**
     * @param array<string, mixed> $report
     */
    private function reportNeedsAttention(array $report): bool
    {
        return (int) ($report['failed'] ?? 0) > 0
            || (int) ($report['dead_letter'] ?? 0) > 0
            || ($report['status'] ?? null) === 'failed'
            || ($report['status'] ?? null) === 'dead_letter';
    }

    /**
     * @param array<string, int> $summary
     */
    private function summaryNeedsAttention(array $summary): bool
    {
        return $summary['failed'] > 0
            || $summary['dead_letter'] > 0
            || $summary['compensation_failed'] > 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function eventOptions(string $severity): array
    {
        return [
            'severity' => $severity,
            'criticality' => ReliabilityPolicy::CRITICALITY_MEDIUM,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => 'reliability.dispatch_orchestration',
            'source' => static::class,
        ];
    }
}
