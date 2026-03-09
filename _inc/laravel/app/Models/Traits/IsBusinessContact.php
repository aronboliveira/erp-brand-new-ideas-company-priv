<?php

namespace App\Traits;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\{CallType};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait IsBusinessContact
{
	protected function addBasicBusinessContactColumns(Blueprint $table, bool $nullableUser = false, bool $cascade = true): void
	{
	    try {
    		$nullableUser ? $table->uuid(UC::COL_USER_ID)->nullable()->index() : $table->uuid(UC::COL_USER_ID)->nullable()->index();
    		$table->string('from', 254)->index();
    		$table->uuid(AC::COL_TO_ID, 254)->nullable()->index();
    		$table->string('to', 254)->index();
    		$table->uuid(AC::COL_FRM_ID)->nullable()->index();
    		$table->string('subject', 255)->index();
    		$table->text('description')->nullable();
    		$table->text('notes')->nullable();
    		$nullableUser ? $table->foreign(UC::COL_USER_ID)
    			->references('id')
    			->on(DC::TABLE_USERS)
    			->nullOnDelete() : ($cascade ? $table->foreign(UC::COL_USER_ID)->references('id')->on(DC::TABLE_USERS)->cascadeOnDelete() : $table->foreign(UC::COL_USER_ID)->references('id')->on(DC::TABLE_USERS)->restrictOnDelete());
    		foreach (
    			[
    				AC::COL_FRM_ID => DC::TABLE_USERS,
    				AC::COL_TO_ID  => DC::TABLE_USERS,
    			] as $col => $refTable
    		)
    			$table->foreign($col)
    				->references('id')
    				->on($refTable)
    				->nullOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addBasicBusinessContactColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function dropBasicBusinessContactColumnForeigns(Blueprint $table): void
	{
	    try {
    		foreach (
    			[
    				UC::COL_USER_ID,
    				AC::COL_FRM_ID,
    				AC::COL_TO_ID,
    			] as $col
    		) {
    			try {
    				Schema::hasColumn($table->getTable(), $col)
    					&&
    					$table->dropForeign([$col]);
    			} catch (\Exception $e) {
    				Log::warning(
    					'Failed to drop foreign key for '
    						. $col
    						. ': '
    						. $e->getMessage()
    				);
    			}
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropBasicBusinessContactColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function addBusinessCallColumns(Blueprint $table): void
	{
	    try {
    		$table->enum(AC::COL_CL_TP, CallType::values())->default(CallType::Other->value)->index();
    		$table->string('phone', 32)->nullable();
    		$table->dateTime(AC::COL_CL_DT)->nullable();
    		$table->time(AC::COL_CL_DUR)->nullable();
    		$table->text(AC::COL_CL_RS)->nullable();
    		$table->string('duration', 20);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addBusinessCallColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function addBussinessEmailColumns(Blueprint $table): void
	{
	    try {
    		$table->unsignedInteger('counter')->default(1)->nullable();
    		$table->boolean(PJC::COL_IS_FUP)->default(false)->nullable();
    		$table->json('attachments')->nullable();
    		$table->json(PJC::COL_ATC_FRULES)->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addBussinessEmailColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
