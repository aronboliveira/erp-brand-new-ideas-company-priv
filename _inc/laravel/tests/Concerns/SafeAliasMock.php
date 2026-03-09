<?php

namespace Tests\Concerns;

use Mockery;
use Mockery\MockInterface;

/**
 * Provides a safe wrapper for Mockery alias mocks.
 *
 * Alias mocks fail with "class already exists" when the real class
 * has already been autoloaded by another test in the same PHP process.
 * This trait catches that error and marks the test as skipped with a
 * clear message, instead of producing an unhelpful fatal error.
 */
trait SafeAliasMock
{
    /**
     * Create a Mockery alias mock, skipping the test when the real
     * class has already been loaded in this process.
     *
     * @param  string $class  Fully-qualified class name (e.g. App\Models\Contract::class)
     * @return MockInterface
     */
    protected function aliasMock(string $class): MockInterface
    {
        try {
            return Mockery::mock('alias:' . $class);
        } catch (\Mockery\Exception\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                $this->markTestSkipped(
                    "Alias mock for {$class} skipped: class already loaded in process."
                );
            }
            throw $e;
        }
    }
}
