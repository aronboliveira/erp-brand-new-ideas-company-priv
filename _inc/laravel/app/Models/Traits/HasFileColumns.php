<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC};
use App\Enums\{MimeType};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log};

trait HasFileColumns
{
	protected function addFileColumns(Blueprint $table): void
	{
	    try {
    		$table->string(DC::COL_FL_PT)->nullable();
    		$table->string('url', 254)->nullable()->index();
    		$table->string('name', 1024)->nullable()->index();
    		$table->string('extension')->nullable()->index();
    		$table->enum(DC::COL_MM_TP, array_column(MimeType::cases(), 'value'))->default(MimeType::OTHER->value)->nullable();
    		$table->timestamp(DC::COL_LA)->nullable();
    		$table->unsignedBigInteger('size')->nullable();
    		$table->text('description')->nullable();
    		$table->text('notes')->nullable();
    		$table->decimal(DC::COL_DL_CT, 10, 0)->default(0)->nullable();
    		$table->decimal(DC::COL_FL_SZ, 16, 4)->default(0.0000)->nullable();
    		$table->string(DC::COL_PERM_RLS)->default('776444')->nullable();
    		$table->text('executors')->nullable();
    		$table->text('editors')->nullable();
    		$table->text('viewers')->nullable();
    		$table->datetime(DC::COL_EXP_DT)->nullable();
    		$table->string('type')->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addFileColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
