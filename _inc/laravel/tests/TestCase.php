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
        // Skip migrate:fresh when database is already migrated.
        // Must be set BEFORE parent::setUp() because setUpTraits() triggers
        // RefreshDatabase::refreshTestDatabase() inside parent::setUp().
        if (! RefreshDatabaseState::$migrated) {
            RefreshDatabaseState::$migrated = true;
        }

        parent::setUp();

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
     * Assert that each key in $data matches the model's raw attribute value.
     * Handles date-only vs datetime expansion, backed enum casting,
     * and skips guarded attributes that weren't mass-assigned.
     */
    protected function assertFillableMatches(array $data, \Illuminate\Database\Eloquent\Model $model): void
    {
        $attrs = $model->getAttributes();
        foreach ($data as $key => $value) {
            // Skip attributes that weren't actually stored (guarded fields)
            if (!array_key_exists($key, $attrs)) {
                continue;
            }
            $actual = $attrs[$key];
            // Handle backed enums
            if ($actual instanceof \BackedEnum) {
                $actual = $actual->value;
            }
            // Handle date-only vs datetime expansion (e.g. '2025-05-22' vs '2025-05-22 00:00:00')
            if (
                is_string($value) && is_string($actual)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
                && str_starts_with($actual, $value)
            ) {
                $actual = $value;
            }
            // Handle decimal precision (e.g. 123.456 stored as 123.46)
            if (is_numeric($value) && is_numeric($actual)) {
                $this->assertEqualsWithDelta($value, $actual, 0.01, "Fillable attribute [{$key}] numeric mismatch");
                continue;
            }
            $this->assertEquals($value, $actual, "Fillable attribute [{$key}] mismatch");
        }
    }
}
