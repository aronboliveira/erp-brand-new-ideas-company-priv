<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class ErrorHandler
{
	protected static array $errorsArray = [];
	/**
	 * Evaluate if candidate error exists in data aggregator and log to appropriate channel
	 * 
	 * @param string $dataAggregatorKey Reference key to the error collection array
	 * @param array $candidate The candidate error to check and potentially add
	 * @param string $secondaryChannel Log level for duplicate messages (default: 'debug')
	 * @param string $mainChannel Log level for new unique errors (default: 'warning')
	 * @return void
	 */
	public static function evaluateExistenceToLogChannel(
		string $dataAggregatorKey,
		array $candidate,
		string $secondaryChannel = 'debug',
		string $mainChannel = 'warning',
		?string $seed = null
	): void {
		try {
			if (!isset($candidate['message'])) {
				Log::warning('ErrorHandler::evaluateExistenceToLogChannel - Candidate missing required "message" key', [
					'candidate' => $candidate,
				]);
				if (!(method_exists(Log::class, $mainChannel) && is_callable(Log::class, $mainChannel)))
					$mainChannel = 'warning';
				Log::$mainChannel(implode(' | ', self::recurseOnMessage($candidate)));
				return;
			}
			$context = $candidate['context'] ?? [];
			$message = $candidate['message'];
			if (\in_array($candidate, self::$errorsArray[$dataAggregatorKey] ?? [], true)) {
				!empty($seed) && Log::debug("{$seed} failed");
				return;
			}
			$existingMessages = \array_column(self::$errorsArray[$dataAggregatorKey] ?? [], 'message');
			$messageExists = \in_array($message, $existingMessages, true);
			if (!(method_exists(Log::class, $mainChannel) && is_callable(Log::class, $mainChannel)))
				$mainChannel = 'warning';
			if (!(method_exists(Log::class, $secondaryChannel) && is_callable(Log::class, $secondaryChannel)))
				$secondaryChannel = 'debug';
			if ($messageExists)
				Log::$secondaryChannel($message, $context);
			else
				Log::$mainChannel($message, $context);
			self::$errorsArray[$dataAggregatorKey][] = $candidate;
		} catch (\Throwable $e) {
			Log::error('ErrorHandler::evaluateExistenceToLogChannel - Failed to process error', [
				'error' => $e->getMessage(),
				'candidate' => $candidate,
			]);
		}
	}
	protected static function recurseOnMessage(mixed $value): array
	{
		return array_values(array_filter(array_map(function ($item) {
			if (is_array($item))
				return self::recurseOnMessage($item);
			return is_scalar($item) ? trim((string)$item) : null;
		}, $value), fn($s) => $s !== null && $s !== ''));
	}
}
