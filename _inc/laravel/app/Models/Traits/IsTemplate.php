<?php

namespace App\Traits;

use App\Config\Constants\{ActivitiesConstants as AC, MessagesConstants as MC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait IsTemplate
{
	public function addTemplateColumns(Blueprint $table): void
	{
	    try {
    		$table->string('slug')->nullable();
    		$table->text('description')->nullable();
    		$table->datetime(AC::COL_AV_FROM)->nullable()->useCurrent();
    		$table->boolean(AC::COL_DSB)->nullable()->default(false);
    		$table->json('categories')->nullable();
    		$table->json(MC::COL_EX_PLN)->nullable();
    		$table->json('rules')->nullable();
    		$table->json(MC::COL_AV_LG)->nullable();
    		$table->json('tags')->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addTemplateColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	public function dropTemplateColumns(Blueprint $table, ?string $tableName): void
	{
	    try {
    		$columns = [
    			'slug',
    			'description',
    			AC::COL_AV_FROM,
    			AC::COL_DSB,
    			'categories',
    			MC::COL_EX_PLN,
    			'rules',
    			MC::COL_AV_LG,
    			'tags',
    		];
    		$tableName ??= $table->getTable();
    		foreach ($columns as $column) {
    			try {
    				Schema::hasColumn($tableName, $column)
    					&& $table->dropColumn($column);
    			} catch (\Exception $e) {
    				Log::warning(
    					'Failed to drop column '
    						. $column
    						. ' on table '
    						. $tableName
    						. ': '
    						. $e->getMessage()
    				);
    			}
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropTemplateColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
