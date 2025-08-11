<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\Schema\Blueprint;

abstract class DeliverableSchema extends BaseSchema
{
    protected function addFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'addFields')) parent::addFields($table);
        $table->uuid('id')->primary();
        $table->string('lang', 20)->default(DatabaseConstants::DEFAULT_LANG);
        $table->string('name');
        $table->string('email', 254)->unique();
        $table->string('secondary_email', 254)->nullable();
        $table->string('contact')->nullable();
        $table->string('avatar')->nullable();
        $table->string('avatar_url', 200)->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->text('billing_address')->nullable();
        $table->string('billing_city')->nullable();
        $table->string('billing_country')->nullable();
        $table->string('billing_phone', 50);
        $table->timestamp('billing_phone_verified_at')->nullable();
        $table->string('billing_state')->nullable();
        $table->string('billing_zip', 20);
        $table->text('shipping_address')->nullable();
        $table->string('shipping_city')->nullable();
        $table->string('shipping_country')->nullable();
        $table->string('shipping_name')->nullable();
        $table->string('shipping_phone')->nullable();
        $table->timestamp('shipping_phone_verified_at')->nullable();
        $table->string('shipping_state')->nullable();
        $table->string('shipping_zip', 20)->nullable();
        $table->text('notes')->nullable();
        $table->uuid(DatabaseConstants::TABLE_CREATOR)->index();
    }

    protected function dropFields(Blueprint $table): void
    {
        if (method_exists(parent::class, 'dropFields')) parent::dropFields($table);
        $table->dropColumn([
            'id', 'lang', 'name', 'email', 'secondary_email', 'contact',
            'avatar', 'avatar_url', 'email_verified_at', 'is_active',
            'billing_address', 'billing_city', 'billing_country', 'billing_phone',
            'billing_phone_verified_at', 'billing_state', 'billing_zip',
            'shipping_address', 'shipping_city', 'shipping_country', 'shipping_name',
            'shipping_phone', 'shipping_phone_verified_at', 'shipping_state',
            'shipping_zip', 'notes', 'created_by'
        ]);
    }
}
