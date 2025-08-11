<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;

abstract class DefaultTimedSchema extends BaseSchema
{
    protected function addFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'addFields')) parent::addFields($table);
        $table->timestamps();
    }

    protected function dropFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'dropFields')) parent::dropFields($table);
        $table->dropTimestamps();
    }
}
