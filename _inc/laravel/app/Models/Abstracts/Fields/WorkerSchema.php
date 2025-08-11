<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;

abstract class WorkerSchema extends PersonSchema
{
    protected function addFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'addFields')) parent::addFields($table);
        $table->date('dob')->nullable();
        $table->text('skill')->nullable();
    }

    protected function dropFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'dropFields')) parent::dropFields($table);
        $table->dropColumn(['dob', 'skill']);
    }
}
