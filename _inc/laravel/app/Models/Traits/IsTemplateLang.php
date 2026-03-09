<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, MessagesConstants as MC};
use App\Enums\{AvailableLang};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait IsTemplateLang
{
	public function addTemplateLangColumns(Blueprint $table): void
	{
	    try {
    		$table->enum('lang', array_column(AvailableLang::cases(), 'value'))->default(DC::DEFAULT_LANG)->index();
    		$table->string('translator', 254)->nullable()->default(DC::DEFAULT_UUID);
    		$table->uuid(MC::COL_TRL_ID)->nullable()->default(DC::DEFAULT_UUID)->index();
    		$table->text('content');
    		$table->json('variables')->nullable();
    		$table->json('metadata')->nullable();
    		$table->foreign(MC::COL_TRL_ID)
    			->references('id')
    			->on(DC::TABLE_USERS)
    			->nullOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addTemplateLangColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	public function dropTemplateLangColumns(Blueprint $table, ?string $tableName): void
	{
	    try {
    		$columns = [
    			'lang',
    			'translator',
    			MC::COL_TRL_ID,
    			'content',
    			'variables',
    			'metadata',
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
	        Log::error(static::class . '::dropTemplateLangColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	public function dropTemplateLangColumnForeigns(Blueprint $table, ?string $tableName): void
	{
	    try {
    		$columns = [
    			MC::COL_TRL_ID,
    		];
    		$tableName ??= $table->getTable();
    		foreach ($columns as $column) {
    			try {
    				Schema::hasColumn($tableName, $column)
    					&& $table->dropForeign([$column]);
    			} catch (\Exception $e) {
    				Log::warning(
    					'Failed to drop foreign key for '
    						. $column
    						. ' on table '
    						. $tableName
    						. ': '
    						. $e->getMessage()
    				);
    			}
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropTemplateLangColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
