<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log};

trait HasNfeColumns
{
	protected function addNfeColumns(Blueprint $table): void
	{
	    try {
    		$table->string(BC::COL_NFE_KEY, 44)->nullable()->unique()->index();
    		$table->string(BC::COL_NFE_NUMBER, 20)->nullable()->index();
    		$table->string(BC::COL_NFE_SERIES, 10)->nullable();
    		$table->text(BC::COL_NFE_XML_PATH)->nullable();
    		$table->string(BC::COL_NFE_PROTOCOL, 20)->nullable();
    		$table->timestamp(BC::COL_NFE_AUTH_AT)->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addNfeColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function dropNfeColumns(Blueprint $table, string $tableName): void
	{
	    try {
    		$cols = [
    			BC::COL_NFE_KEY,
    			BC::COL_NFE_NUMBER,
    			BC::COL_NFE_SERIES,
    			BC::COL_NFE_XML_PATH,
    			BC::COL_NFE_PROTOCOL,
    			BC::COL_NFE_AUTH_AT,
    		];
    		foreach ($cols as $col) {
    			try {
    				$table->dropColumn($col);
    			} catch (\Exception $e) {
    				Log::warning(
    					"[" . self::class . "]: " . "Tried to drop nfe column foreign {$col} on table {$tableName} but failed: {$e->getMessage()}",
    					[
    						'class' => static::class,
    						'method' => __METHOD__,
    						'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__,
    						'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? __LINE__,
    					]
    				);
    			}
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropNfeColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
