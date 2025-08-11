<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class TabLogs
{
	public function __invoke($logger)
	{
		foreach ($logger->getHandlers() as $handler)
			$handler->setFormatter(new LineFormatter(format: null, dateFormat: null, allowInlineLineBreaks: true, ignoreEmptyContextAndExtra: true, includeStacktraces: true));
	}
}
