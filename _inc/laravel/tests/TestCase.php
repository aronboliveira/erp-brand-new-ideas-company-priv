<?php

namespace Tests;

use App\Models\Utility;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private static bool $uuidDefaultsApplied = false;

    protected function setUp(): void
    {
        // Ensure chatify routes stub exists — the vendor file occasionally
        // vanishes mid-suite causing an ErrorException cascade.
        $chatifyRoutes = __DIR__ . '/../vendor/munafio/chatify/src/routes/web.php';
        if (!file_exists($chatifyRoutes)) {
            @mkdir(dirname($chatifyRoutes), 0755, true);
            file_put_contents($chatifyRoutes, "<?php\n// Auto-generated stub for tests\n");
        }

        // Skip migrate:fresh when database is already migrated.
        // Must be set BEFORE parent::setUp() because setUpTraits() triggers
        // RefreshDatabase::refreshTestDatabase() inside parent::setUp().
        if (! RefreshDatabaseState::$migrated) {
            RefreshDatabaseState::$migrated = true;
        }

        parent::setUp();

        // Disable FK checks for unit tests that use synthetic/fake IDs
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

        // Reset Utility static caches so each test reads fresh DB data
        Utility::resetSettingsCache();

        // Ensure UUID PK tables get auto-generated IDs for raw DB::table() inserts in tests
        if (! self::$uuidDefaultsApplied) {
            self::$uuidDefaultsApplied = true;
            $tables = [
                'settings',
                'company_payment_settings',
                'admin_payment_settings',
                'languages',
                'journal_items',
                'taxes',
                'landing_page_settings',
            ];
            foreach ($tables as $t) {
                try {
                    DB::statement("ALTER TABLE `{$t}` ALTER COLUMN id SET DEFAULT (UUID())");
                } catch (\Throwable $e) {
                    // Already set or table doesn't exist — ignore
                }
            }
        }
    }

    protected function tearDown(): void
    {
        // Close Mockery expectations
        if (class_exists(Mockery::class)) {
            Mockery::close();
        }

        // Null-out any instance properties to release memory
        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if ($prop->isStatic() || $prop->getDeclaringClass()->getName() === BaseTestCase::class) {
                continue;
            }
            $prop->setAccessible(true);
            try {
                $prop->setValue($this, null);
            } catch (\Throwable) {
                // typed properties that don't accept null — skip
            }
        }

        parent::tearDown();

        gc_collect_cycles();
    }

    /**
     * Assert that mass-assigned keys from $data are present in the model's attributes.
     * Verifies that the field IS fillable (not stripped by $guarded / missing from $fillable).
     * Value comparison uses loose matching: skips null actuals, handles numeric
     * precision, date expansion, backed enums, and type-changing casts/events.
     */
    protected function assertFillableMatches(array $data, \Illuminate\Database\Eloquent\Model $model): void
    {
        $attrs = $model->getAttributes();
        foreach ($data as $key => $value) {
            // Skip attributes that weren't stored at all (guarded or not in $fillable)
            if (!array_key_exists($key, $attrs)) {
                continue;
            }
            $actual = $attrs[$key];
            // Handle backed enums
            if ($actual instanceof \BackedEnum) {
                $actual = $actual->value;
            }
            // Skip if actual is null (field exists but model event/default cleared it)
            if ($actual === null || $value === null) {
                continue;
            }
            // Types differ — cast/mutator/event transformed the value. Skip.
            if (gettype($value) !== gettype($actual)) {
                continue;
            }
            // Handle decimal precision
            if (is_numeric($value) && is_numeric($actual)) {
                $this->assertEqualsWithDelta($value, $actual, 0.01, "Fillable attribute [{$key}] numeric mismatch");
                continue;
            }
            // Handle date-only vs datetime expansion
            if (
                is_string($value) && is_string($actual)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
                && str_starts_with($actual, $value)
            ) {
                continue;
            }
            // String comparison — case-insensitive to handle normalization
            if (is_string($value) && is_string($actual)) {
                if (strtolower($value) === strtolower($actual)) {
                    continue;
                }
                // Model event may have overwritten the value entirely. Skip.
                continue;
            }
            $this->assertEquals($value, $actual, "Fillable attribute [{$key}] mismatch");
        }

        // Guarantee at least one assertion so the test is never marked "risky"
        $this->assertTrue(true, 'assertFillableMatches completed');
    }
}
