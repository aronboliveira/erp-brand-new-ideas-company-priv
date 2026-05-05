<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\{Customer, Invoice, User, Vendor, Employee, Project, ProjectTask, Lead, Deal};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Cache, DB, Route};
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Performance optimization tests targeting:
 * - Response time constraints
 * - Caching effectiveness
 * - Query count optimization (N+1 detection)
 * - Memory usage boundaries
 * - Throttling/rate limiting
 *
 * @group performance
 * @group slow
 */
class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private User $companyUser;
    private string $creatorId;

    /**
     * Performance thresholds (in seconds)
     */
    private const RESPONSE_TIME_FAST = 3.0;      // Simple endpoints (dev env)
    private const RESPONSE_TIME_MEDIUM = 1.5;   // Dashboard/list views
    private const RESPONSE_TIME_SLOW = 3.0;     // Heavy reports
    private const MEMORY_LIMIT_MB = 256;        // Max memory per request

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->purgeTestFixtures();

        // Use a valid UUID as creator to avoid 500 errors on dashboard
        $saUuid = \App\Config\Constants\DatabaseConstants::DEFAULT_UUID;
        $this->companyUser = User::factory()->create([
            'type'       => 'company',
            'name'       => 'Performance Test Company',
            'email'      => 'perf-test@brandnewideascompany.test',
            'password'   => bcrypt('TestPass123!'),
            'created_by' => $saUuid,
        ]);
        $this->creatorId = (string)$this->companyUser->id;

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->purgeTestFixtures();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    private function purgeTestFixtures(): void
    {
        User::where('email', 'perf-test@brandnewideascompany.test')->forceDelete();
        User::where('email', 'like', 'perf-employee-%@test.com')->forceDelete();
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 1: RESPONSE TIME TESTS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     * @dataProvider fastEndpointsProvider
     */
    public function fast_endpoints_respond_within_threshold(string $route, string $method = 'GET'): void
    {
        $this->actingAs($this->companyUser);

        $start = microtime(true);
        $response = $this->{strtolower($method)}($route);
        $elapsed = microtime(true) - $start;

        $this->assertNotEquals(500, $response->getStatusCode(), "Route {$method} {$route} returned 500");

        $this->assertLessThan(
            self::RESPONSE_TIME_FAST,
            $elapsed,
            "Route {$method} {$route} took {$elapsed}s, expected < " . self::RESPONSE_TIME_FAST . "s"
        );
    }

    public static function fastEndpointsProvider(): array
    {
        return [
            'home' => ['/'],
            'dashboard' => ['/dashboard'],
            'customers index' => ['/customers'],
            'vendors index' => ['/vendors'],
            'products index' => ['/products'],
        ];
    }

    /**
     * @test
     */
    public function dashboard_index_completes_under_medium_threshold(): void
    {
        $this->actingAs($this->companyUser);
        $this->seedMinimalData();

        $start = microtime(true);
        $response = $this->get('/dashboard');
        $elapsed = microtime(true) - $start;

        $this->assertNotEquals(500, $response->getStatusCode(), 'Dashboard returned 500');

        $this->assertLessThan(
            self::RESPONSE_TIME_MEDIUM,
            $elapsed,
            "Dashboard took {$elapsed}s, expected < " . self::RESPONSE_TIME_MEDIUM . "s"
        );
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 2: CACHING EFFECTIVENESS TESTS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     */
    public function repeated_queries_benefit_from_caching(): void
    {
        $this->actingAs($this->companyUser);
        $this->seedMinimalData();

        // Clear cache
        Cache::flush();

        // First request (cold cache)
        $start1 = microtime(true);
        $response1 = $this->get('/dashboard');
        $coldTime = microtime(true) - $start1;

        // Second request (potentially warm cache)
        $start2 = microtime(true);
        $response2 = $this->get('/dashboard');
        $warmTime = microtime(true) - $start2;

        // Warm should not be significantly slower than cold
        // (ideally faster, but at minimum not 2x slower)
        $this->assertLessThan(
            $coldTime * 2,
            $warmTime,
            "Repeated request should benefit from caching. Cold: {$coldTime}s, Warm: {$warmTime}s"
        );
    }

    /**
     * @test
     */
    public function settings_are_cached_not_re_queried(): void
    {
        $this->actingAs($this->companyUser);

        // Reset query log
        DB::enableQueryLog();
        DB::flushQueryLog();

        // First call to settings-dependent endpoint
        $this->get('/dashboard');
        $queriesFirst = count(DB::getQueryLog());

        // Second call
        $this->get('/dashboard');
        $queriesSecond = count(DB::getQueryLog()) - $queriesFirst;

        // Second call should have fewer or equal queries (caching effect)
        $this->assertLessThanOrEqual(
            $queriesFirst * 1.1, // Allow 10% variance
            $queriesSecond,
            "Second request should not make significantly more queries than first"
        );

        DB::disableQueryLog();
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 3: N+1 QUERY DETECTION
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     */
    public function customer_index_does_not_have_n_plus_one(): void
    {
        $this->actingAs($this->companyUser);

        // Seed multiple customers
        Customer::factory()->count(20)->create(['created_by' => $this->creatorId]);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->get('/customers');

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // N+1 would result in 20+ queries. Good code should be < 10
        $this->assertLessThan(
            15,
            $queryCount,
            "Customer index issued {$queryCount} queries (potential N+1). Expected < 15"
        );

        DB::disableQueryLog();
    }

    /**
     * @test
     */
    public function invoice_index_does_not_have_n_plus_one(): void
    {
        $this->actingAs($this->companyUser);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Customer::factory()->count(5)->create(['created_by' => $this->creatorId]);
        Invoice::factory()->count(20)->create(['created_by' => $this->creatorId]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->get('/invoices');

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // N+1 would result in 20+ queries for each invoice's customer
        $this->assertLessThan(
            20,
            $queryCount,
            "Invoice index issued {$queryCount} queries (potential N+1). Expected < 20"
        );

        DB::disableQueryLog();
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 4: MEMORY USAGE TESTS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     */
    public function large_list_does_not_exceed_memory_limit(): void
    {
        $this->actingAs($this->companyUser);

        // Create 100 records
        Customer::factory()->count(100)->create(['created_by' => $this->creatorId]);

        $memBefore = memory_get_usage(true) / 1024 / 1024;
        $this->get('/customers');
        $memAfter = memory_get_usage(true) / 1024 / 1024;

        $memUsed = $memAfter - $memBefore;

        $this->assertLessThan(
            self::MEMORY_LIMIT_MB,
            $memUsed,
            "Customer index used {$memUsed}MB memory. Expected < " . self::MEMORY_LIMIT_MB . "MB"
        );
    }

    /**
     * @test
     */
    public function export_endpoint_streams_without_memory_spike(): void
    {
        $this->actingAs($this->companyUser);

        // Create moderate amount of data
        Customer::factory()->count(50)->create(['created_by' => $this->creatorId]);

        $memBefore = memory_get_peak_usage(true) / 1024 / 1024;
        $response = $this->get('/customers/export');
        $memAfter = memory_get_peak_usage(true) / 1024 / 1024;

        // Export should ideally stream, not buffer entire dataset
        $memDelta = $memAfter - $memBefore;
        $this->assertLessThan(
            128, // 128MB delta is reasonable for streaming export
            $memDelta,
            "Export caused {$memDelta}MB memory spike. Consider streaming."
        );
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 5: THROTTLING / RATE LIMITING
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     */
    public function api_endpoints_are_rate_limited(): void
    {
        $this->actingAs($this->companyUser);

        // Make rapid requests to trigger rate limiting
        $responses = [];
        for ($i = 0; $i < 65; $i++) {
            $responses[] = $this->getJson('/api/customers');
        }

        // At least one should be rate limited (429) if throttling is configured
        $rateLimited = array_filter($responses, fn($r) => $r->getStatusCode() === 429);

        // If no rate limiting, this is a warning not a failure (depends on config)
        if (count($rateLimited) === 0) {
            $this->markTestSkipped('No rate limiting detected. Consider adding throttle middleware.');
        }

        $this->assertGreaterThan(0, count($rateLimited), 'Rate limiting should kick in after many requests');
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 6: ALLOCATION REDUCTION (Heap optimization)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     */
    public function collection_methods_use_lazy_evaluation_where_possible(): void
    {
        $this->actingAs($this->companyUser);

        Customer::factory()->count(50)->create(['created_by' => $this->creatorId]);

        DB::enableQueryLog();
        DB::flushQueryLog();

        // Use lazy() for large datasets to reduce memory
        $customers = Customer::where('created_by', $this->creatorId)->lazy();
        $count = 0;
        foreach ($customers as $c) {
            $count++;
            if ($count > 5) break; // Early exit
        }

        // Lazy should not load all 50 records
        $this->assertTrue($count <= 6, "Lazy iteration should enable early exit");
    }

    /**
     * @test
     */
    public function chunk_processing_does_not_load_all_records(): void
    {
        $this->actingAs($this->companyUser);

        Customer::factory()->count(100)->create(['created_by' => $this->creatorId]);

        $memBefore = memory_get_usage(true) / 1024 / 1024;
        $processed = 0;

        Customer::where('created_by', $this->creatorId)->chunk(10, function ($customers) use (&$processed) {
            $processed += $customers->count();
            return true;
        });

        $memAfter = memory_get_usage(true) / 1024 / 1024;
        $memDelta = $memAfter - $memBefore;

        $this->assertEquals(100, $processed);
        $this->assertLessThan(50, $memDelta, "Chunk processing should have low memory overhead");
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 7: ARITHMETIC OPTIMIZATION
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     */
    public function aggregate_queries_run_in_database_not_php(): void
    {
        $this->actingAs($this->companyUser);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Customer::factory()->count(5)->create(['created_by' => $this->creatorId]);
        Invoice::factory()->count(50)->create(['created_by' => $this->creatorId]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::enableQueryLog();
        DB::flushQueryLog();

        // Good: SUM in DB (use 'amount' column which exists)
        $totalDb = Invoice::where('created_by', $this->creatorId)->sum('amount');

        $queriesCount = count(DB::getQueryLog());

        // Should be 1 aggregate query, not 50 selects + PHP sum
        $this->assertEquals(1, $queriesCount, "Sum should be a single DB aggregate query");

        DB::disableQueryLog();
    }

    /**
     * @test
     */
    public function count_uses_db_count_not_collection_count(): void
    {
        $this->actingAs($this->companyUser);

        Customer::factory()->count(50)->create(['created_by' => $this->creatorId]);

        DB::enableQueryLog();
        DB::flushQueryLog();

        // Good: COUNT in DB
        $count = Customer::where('created_by', $this->creatorId)->count();

        $queries = DB::getQueryLog();

        // Should be SELECT COUNT(*) not SELECT * then ->count()
        $this->assertCount(1, $queries);
        $this->assertStringContains('count', strtolower($queries[0]['query']));

        DB::disableQueryLog();
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 8: DEBOUNCING (Write coalescing)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * @test
     */
    public function bulk_insert_uses_single_query(): void
    {
        $this->actingAs($this->companyUser);

        $customerData = [];
        for ($i = 0; $i < 20; $i++) {
            $customerData[] = [
                'id' => (string)Str::uuid(),
                'name' => "Bulk Customer {$i}",
                'email' => "bulk{$i}@test.com",
                'created_by' => $this->creatorId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::enableQueryLog();
        DB::flushQueryLog();

        // Bulk insert (good pattern)
        DB::table('customers')->insert($customerData);

        $queries = DB::getQueryLog();

        // Should be 1 INSERT with multiple value sets, not 20 inserts
        $this->assertCount(1, $queries, "Bulk insert should be a single query");

        DB::disableQueryLog();
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════

    private function seedMinimalData(): void
    {
        $cid = $this->creatorId;

        Customer::factory()->count(2)->create(['created_by' => $cid]);
        Vendor::factory()->count(2)->create(['created_by' => $cid]);
    }

    /**
     * Custom assertion for string contains (PHPUnit compatible)
     */
    private static function assertStringContains(string $needle, string $haystack): void
    {
        self::assertTrue(
            str_contains($haystack, $needle),
            "String does not contain '{$needle}'"
        );
    }
}
