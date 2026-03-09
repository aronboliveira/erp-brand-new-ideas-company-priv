<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log};

abstract class WorkerSchema extends PersonSchema
{
    protected function addFields(Blueprint $table): void
    {
        try {
            parent::addFields($table);
            $table->text('skill')->nullable();
        } catch (\Throwable $e) {
            Log::error(static::class . '::addFields \u2014 ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    protected function dropFields(Blueprint $table): void
    {
        parent::dropFields($table);
        $table->dropColumn(['skill']);
    }
}
