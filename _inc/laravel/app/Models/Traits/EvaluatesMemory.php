<?php

namespace App\Traits;

trait EvaluatesMemory
{
	protected const MEMORY_THRESHOLD = 0.5;

	protected function checkMemoryUsage(): void
	{
		try {
			$output = new \Symfony\Component\Console\Output\ConsoleOutput();
			$memoryLimit = $this->getMemoryLimit();
			if (!function_exists('memory_get_usage') || (!is_int($memoryLimit) && !is_float($memoryLimit)) || $memoryLimit <= 0) {
				return;
			}
			$memoryUsage = memory_get_usage(true);
			$usagePercent = $memoryUsage / $memoryLimit;
			if ($usagePercent > self::MEMORY_THRESHOLD) {
				$output->writeln(static::class . sprintf(': high memory usage detected (%.1f%%), initiating sleep.', $usagePercent * 100));
				$excessPercent = ($usagePercent - self::MEMORY_THRESHOLD) * 100;
				$sleepMicroseconds = (int) min(1000000, $excessPercent * 10000); // Max 1 second

				$this->command?->warn(
					static::class . sprintf(
						': high memory usage detected (%.1f%%), sleeping for %dms',
						$usagePercent * 100,
						$sleepMicroseconds / 1000
					)
				);

				usleep($sleepMicroseconds);
				gc_collect_cycles(); // Force garbage collection
			}
		} catch (\Exception $e) {
			// Silently continue if memory check fails
		}
	}

	protected function getMemoryLimit(): int
	{
		$memoryLimit = ini_get('memory_limit');

		if ($memoryLimit === '-1') {
			return -1; // Unlimited
		}

		$unit = strtolower(substr($memoryLimit, -1));
		$value = (int) substr($memoryLimit, 0, -1);

		return match ($unit) {
			'g' => $value * 1024 * 1024 * 1024,
			'm' => $value * 1024 * 1024,
			'k' => $value * 1024,
			default => (int) $memoryLimit,
		};
	}
}
