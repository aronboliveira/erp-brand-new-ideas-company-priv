<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;

abstract class ColorableSchema extends BaseSchema
{
    protected function addFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'addFields')) parent::addFields($table);
        $table->uuid('id')->primary();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('color', 50)->default('gray');
    }

    protected function dropFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'dropFields')) parent::dropFields($table);
        $table->dropColumn(['id', 'created_by', 'color']);
    }
}
