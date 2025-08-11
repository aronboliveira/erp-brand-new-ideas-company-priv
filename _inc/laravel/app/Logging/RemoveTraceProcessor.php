<?php
// app/Logging/RemoveTraceProcessor.php

namespace App\Logging;

use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\{
	Formatter\LineFormatter,
	Handler\FormattableHandlerInterface,
	Logger,
	LogRecord
};

class RemoveTraceProcessor
{
	public function __invoke(IlluminateLogger $logger): void
	{
		$monolog = $logger->getLogger();
		if ($monolog instanceof Logger) {
			$monolog->pushProcessor(function (LogRecord $record): LogRecord {
				if (isset($record->context['trace'])) {
					$ctx = $record->context;
					unset($ctx['trace']);
					$record = $record->with(context: $ctx);
				}
				return $record;
			});
			foreach ($monolog->getHandlers() as $handler) {
				if (!$handler instanceof FormattableHandlerInterface)
					continue;
				$formatter = $handler->getFormatter();
				if ($formatter instanceof LineFormatter)
					$formatter->includeStacktraces(false);
			}
		}
	}
}
