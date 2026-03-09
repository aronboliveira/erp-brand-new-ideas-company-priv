<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

abstract class BaseSchema extends Migration
{

	public function up(): void
	{
	    try {
    		Schema::table($this->tableName(), function (Blueprint $table): void {
    			$this->addFields($table);
    		});
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::up — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	public function down(): void
	{
	    try {
    		Schema::table($this->tableName(), function (Blueprint $table): void {
    			$this->dropFields($table);
    		});
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::down — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	abstract protected function tableName(): string;
	abstract protected function addFields(Blueprint $table): void;
	abstract protected function dropFields(Blueprint $table): void;
}
