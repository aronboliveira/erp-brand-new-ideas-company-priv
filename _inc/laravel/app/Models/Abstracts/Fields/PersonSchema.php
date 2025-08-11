<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;

abstract class PersonSchema extends BaseSchema
{
    protected function addFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'addFields')) parent::addFields($table);
        $table->string('phone', 20)->nullable();
        $table->string('gender', 126)->default('other');
        $table->string('country')->nullable();
        $table->string('state')->nullable();
        $table->string('city')->nullable();
        $table->text('address')->default('No address defined');
    }

    protected function dropFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'dropFields')) parent::dropFields($table);
        $table->dropColumn([
            'phone', 'gender', 'country', 'state', 'city', 'address'
        ]);
    }
}
