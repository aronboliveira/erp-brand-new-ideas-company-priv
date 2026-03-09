<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log};

abstract class DescribableSchema extends BaseSchema
{
    protected function addFields(Blueprint $table): void
    {
        try {
            if (method_exists(parent::class, 'addFields')) parent::addFields($table);
            $table->string('title', 254)->nullable()->default('NO GIVEN TITLE');
            $table->text('description')->nullable()->default('NO GIVEN DESCRIPTION');
            $table->string('notes', 2048)->nullable()->default('No notes taken');
            $table->string('document_url', 200)->nullable();
            $table->string('attachments')->nullable();
            $table->timestamps();
        } catch (\Throwable $e) {
            Log::error(static::class . '::addFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    protected function dropFields(Blueprint $table): void
    {
        try {
            if (method_exists(parent::class, 'dropFields')) parent::dropFields($table);
            $table->dropColumn([
                'title',
                'description',
                'notes',
                'document_url',
                'attachments',
                'created_at',
                'updated_at'
            ]);
        } catch (\Throwable $e) {
            Log::error(static::class . '::dropFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }
}
